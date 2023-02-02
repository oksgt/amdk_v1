<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Stocks extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('form_validation'));
		$this->load->helper(array('url', 'language', 'app_helper', 'string', 'file'));

		$this->load->model(array('Stock', 'Product'));
		if ($this->session->userdata('status') !== 'loggedin') {
			redirect(base_url("login"));
		}
	}

	public function index()
	{
		$this->template->load('template', 'view_stock');
	}

	public function ajax_list()
	{
		$list = $this->Stock->get_datatables();
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $r) {
			$no++;
			$row = array();
			$row[] = formatTglIndo_datetime($r->created_at);
			$row[] = $r->product_name;
			$row[] = $r->input_stock . " " . $r->product_unit;
			$row[] = $r->notes;
			$row[] = $r->name;
			$row[] = '
				<div class="btn-group-sm d-flex" role="group" aria-label="Action Button">
					<a role="button" class="btn btn-warning btn-sm w-100 text-white" href="' . base_url('/stocks/edit/' . $r->id) . '">
						<b class="ti-pencil-alt"></b> Edit
					</a>
					<button type="button" class="btn bg-white default btn-sm border-0 text-danger w-100" onclick="delete_data(' . $r->id . ')">
						<b class="ti-trash"></b>Delete
					</button>
				</div>
			';
			$data[] = $row;
		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->Stock->count_all(),
			"recordsFiltered" => $this->Stock->count_filtered(),
			"data" => $data,
		);
		echo json_encode($output);
	}

	public function add()
	{
		$data['product'] = $this->Product->get_data()->result();
		$this->template->load('template', 'view_add_stock', $data);
	}

	public function save()
	{
		$id_product 	= $this->input->post('product');
		$input_stock 	= $this->input->post('input_stock');
		$input_by   = $this->session->userdata('id');
		$notes      = $this->input->post('notes');

		if ($id_product == 0) {
			$message = '
			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				<strong>Warning!</strong> Please select product name.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
			redirect(base_url('stocks'));
		}

		// get last stock
		$product = $this->Product->detail($id_product)->row_array();
		$product_last_stock = $product['stock'];

		$object = [
			'id_product' 	=> $id_product,
			'last_stock' 	=> 0, //$product_last_stock,
			'input_stock'   => $input_stock,
			'input_type'    => 1,
			'parent_trans_id' => 0,
			'trans_type'    => 1,
			'notes'         => $notes,
			'updated_stock' => 0, //$product_last_stock + $input_stock,
			'input_by'      => $input_by,
			'created_at'	=> Date('Y-m-d H:i:s')
		];


		$this->db->trans_begin();

		// insert stock
		$insert_id = $this->Stock->save($object);

		//get summary input stock
		$total_stock = 0;
		$product_stock = $this->Stock->summary_stock($id_product)->row_array();

		// update stock value in product table
		$product = $this->Product->update([
			'stock' => $product_stock['total']
		], [
			'id'    => $object['id_product']
		]);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$message = '
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Danger!</strong> Failed to add stock.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                ';
			$this->session->set_flashdata('item', $message);
		} else {
			$this->db->trans_commit();
			$message = '
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> Stock saved successfully.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                ';
			$this->session->set_flashdata('item', $message);
		}

		redirect(base_url('stocks'));
	}

	public function edit($id = null)
	{
		if ($id == null || $id == "") {
			redirect(base_url('stocks'));
		}

		$data = $this->Stock->detail($id)->row_array();

		if (empty($data)) {
			$message = '
			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				<strong>Warning!</strong> Data Not Found.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
			redirect(base_url('stocks'));
		}

		$data['detail'] = $data;
		$data['product'] = $this->Product->get_data()->result();
		$this->template->load('template', 'view_edit_stock', $data);
	}

	public function update()
	{
		$id_product 	= $this->input->post('product');
		$input_stock 	= $this->input->post('input_stock');
		$last_stock     = $this->input->post('last_stock');
		$updated_stock  = $this->input->post('updated_stock');
		$input_by       = $this->session->userdata('id');
		$notes          = $this->input->post('notes');
		$id 	        = $this->input->post('id');


		if ($id_product == 0) {
			$message = '
			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				<strong>Warning!</strong> Please select product name.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
			redirect(base_url('stocks'));
		}

		$object = [
			'id_product' 	=> $id_product,
			'input_stock'   => $input_stock,
			'notes'         => $notes,
			'input_by'      => $input_by,
			'created_at'	=> Date('Y-m-d H:i:s')
		];


		$this->db->trans_begin();

		// insert stock
		$insert_id = $this->Stock->update($object, ['id' => $id]);

		//get summary input stock
		$total_stock = 0;
		$product_stock = $this->Stock->summary_stock($id_product)->row_array();

		// update stock value in product table
		$product = $this->Product->update([
			'stock' => $product_stock['total']
		], [
			'id'    => $object['id_product']
		]);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$message = '
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Danger!</strong> Failed to add stock.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                ';
			$this->session->set_flashdata('item', $message);
		} else {
			$this->db->trans_commit();
			$message = '
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> Stock saved successfully.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                ';
			$this->session->set_flashdata('item', $message);
		}

		redirect(base_url('stocks'));
	}

	public function soft_delete()
	{
		$input_by = $this->session->userdata('id');
		$id 	= $this->input->post('id');

		echo json_encode(['result' => $updated]);

		$this->db->trans_begin();

		$object = [
			'deleted_by'	=> $input_by,
			'deleted_at'	=> Date('Y-m-d H:i:s')
		];
		$soft_deleted = $this->Stock->update($object, ['id' => $id]);

		$deleted_data = $this->Stock->get_by(['id' => $id])->row_array();
		
		$product = $this->Product->update([
			'stock' => $product_stock['total'] - $deleted_data['input_stock']
		], [
			'id'    => $id
		]);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$message = '
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Danger!</strong> Failed to add stock.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                ';
			$this->session->set_flashdata('item', $message);
		} else {
			$this->db->trans_commit();
			$message = '
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> Stock saved successfully.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                ';
			$this->session->set_flashdata('item', $message);
		}
	}
}
