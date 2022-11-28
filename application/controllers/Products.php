<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends CI_Controller {

    public function __construct()
	{
		parent::__construct();
		$this->load->library(array('form_validation'));
		$this->load->helper(array('url', 'language', 'app_helper','string', 'file'));

		$this->load->model(array('Product'));
		if ($this->session->userdata('status') !== 'loggedin') {
			redirect(base_url("login"));
		}
	}

	public function index()
	{
        $this->template->load('template', 'view_product');
	}

    public function ajax_list()
	{
		$list = $this->Product->get_datatables();
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $r) {
			$no++;
			$row = array();
			$row[] = $r->name;
			$row[] = rupiah($r->price);
			$row[] = $r->stock . " ". $r->unit;
			$row[] = '
				<div class="btn-group-sm d-flex" role="group" aria-label="Action Button">
					<a role="button" class="btn btn-warning btn-sm w-100 text-white" href="'.base_url('/products/edit/'.$r->id).'">
						<b class="ti-pencil-alt"></b> Edit
					</a>
					<button type="button" class="btn bg-white default btn-sm border-0 text-danger w-100" onclick="delete_data('.$r->id.')">
						<b class="ti-trash"></b>Delete
					</button>
				</div>
			';
			$data[] = $row;
		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->Product->count_all(),
			"recordsFiltered" => $this->Product->count_filtered(),
			"data" => $data,
		);
		echo json_encode($output);
	}

	public function add(){
		$this->template->load('template', 'view_add_product');
	}

	public function save(){
		$name 	= $this->input->post('name');
		$price 	= $this->input->post('price');
		$input_by = $this->session->userdata('id');
		$notes  = $this->input->post('notes');

		$price	= str_replace("Rp ", "", $price);
		$price	= str_replace(",", "", $price);
		$price	= str_replace(".00", "", $price);

		$object = [
			'name' 	=> $name,
			'unit' 	=> 'pcs',
			'price' => $price,
			'notes' => $notes,
			'input_by'		=> $input_by,
			'created_at'	=> Date('Y-m-d H:i:s')
		];

		$insert_id = $this->Product->save($object);

		if($insert_id > 0){
			$message = '
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				<strong>Success!</strong> Data saved successfully.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
		} else {
			$message = '
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<strong>Danger!</strong> Failed to save data.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
		}

		redirect(base_url('products'));

	}

	public function edit($id = null){
		if($id == null || $id == ""){
			redirect(base_url('products'));
		}

		$data = $this->Product->detail($id)->row_array();

		if(empty($data)){
			$message = '
			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				<strong>Warning!</strong> Product Not Found.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
			redirect(base_url('products'));
		}

		$data['detail'] = $data;

		$formated_price = "Rp ".rupiah($data['price']);
		$formated_price = str_replace(".", ",", $formated_price);
		$data['formated_price'] = $formated_price.".00";

		$this->template->load('template', 'view_edit_product', $data);
	}

	public function update(){
		$name 	= $this->input->post('name');
		$price 	= $this->input->post('price');
		$input_by = $this->session->userdata('id');
		$notes  = $this->input->post('notes');
		$id 	= $this->input->post('id');

		$price	= str_replace("Rp ", "", $price);
		$price	= str_replace(",", "", $price);
		$price	= str_replace(".00", "", $price);

		$object = [
			'name' 	=> $name,
			'unit' 	=> 'pcs',
			'price' => $price,
			'notes' => $notes,
			'updated_by'	=> $input_by,
			'updated_at'	=> Date('Y-m-d H:i:s')
		];

		$updated = $this->Product->update($object, ['id' => $id]);

		if($updated){
			$message = '
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				<strong>Success!</strong> Data updated successfully.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
		} else {
			$message = '
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<strong>Danger!</strong> Failed to update data.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
		}

		redirect(base_url('products'));

	}

	public function soft_delete(){
		$input_by = $this->session->userdata('id');
		$id 	= $this->input->post('id');
		
		$object = [
			'deleted_by'	=> $input_by,
			'deleted_at'	=> Date('Y-m-d H:i:s')
		];

		$updated = $this->Product->update($object, ['id' => $id]);

		echo json_encode(['result' => $updated]);
	}
}
