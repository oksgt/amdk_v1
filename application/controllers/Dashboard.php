<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('form_validation'));
		$this->load->helper(array('url', 'language', 'app_helper','string', 'file'));

		$this->load->model(array('Product', 'Delivery', 'Transaction'));
		if ($this->session->userdata('status') !== 'loggedin') {
			redirect(base_url("login"));
		}
	}

	public function index(){
		// get data pengiriman hari ini
		$session_data = $this->session->userdata();
		// $sql = "select count(1) as total
		// -- d.id, d.delivery_code, d.delivery_status, d.notes,
		// -- d.delivery_date , d.batch 
		// from delivery d 
		// join delivery_staff ds on d.delivery_code = ds.delivery_code 
		// where d.delivery_status = 2 and ds.id_staff = ".$session_data['id'];
		$sql = "
		select 
		count(1) as total
		from delivery_details dd 
		join delivery_staff ds on dd.delivery_code = ds.delivery_code 
		join delivery d on d.delivery_code = dd.delivery_code 
		where ds.id_staff =  ".$session_data['id']."
		and d.delivery_status > 1
		and received_at is null ";
		$data['pending_pengiriman'] = $this->db->query($sql)->row_array();

		$sql = "
		select count(1) as total from delivery_details dd 
		join delivery_staff ds on dd.delivery_code = ds.delivery_code 
		where ds.id_staff =  ".$session_data['id']."
		and received_at is not null ";
		$data['history_pengiriman'] = $this->db->query($sql)->row_array();

		$this->template->load('template', 'view_dashboard_staff', $data);
	}

	public function pending(){
		$session_data = $this->session->userdata();

		$sql = "
		select 
		count(1) as total
		from delivery_details dd 
		join delivery_staff ds on dd.delivery_code = ds.delivery_code 
		join delivery d on d.delivery_code = dd.delivery_code 
		where ds.id_staff =  ".$session_data['id']."
		and d.delivery_status > 1
		and received_at is null";
		$pending_pengiriman = $this->db->query($sql)->row_array();
		if($pending_pengiriman['total'] == 0){
			redirect(base_url('dashboard'));
		}

		//get delivery code
		$data_delivery = [];
		$sql = "select
		d.id, d.delivery_code, d.delivery_status, d.notes,
		d.delivery_date , d.batch 
		from delivery d 
		join delivery_staff ds on d.delivery_code = ds.delivery_code 
		where d.delivery_status = 2 and ds.id_staff = ".$session_data['id'];
		$data_delivery_array = $this->db->query($sql)->result();

		foreach($data_delivery_array as $k => $value){
			$data_delivery[$value->delivery_code] = [];
		}

		// echo "<pre>";
		// print_r($data_delivery); die;

		// get delivery details list
		$data_delivery_detail_list = [];
		$list_trans_number = "";
		$sql = "
		select dd.id, dd.delivery_code, 
		t.name, t.address, t.phone, t.trans_number, dd.received_at
		from delivery_details dd 
		join delivery d on d.delivery_code = dd.delivery_code 
		join delivery_staff ds on ds.delivery_code = dd.delivery_code 
		join transactions t on t.trans_number = dd.trans_number 
		where d.delivery_status = 2 and ds.id_staff = ".$session_data['id'];
		$data_delivery_detail_array = $this->db->query($sql)->result_array();

		foreach ($data_delivery_detail_array as $key => $value) {
			$data_delivery_detail_list[$value['delivery_code']] = []; 
			$list_trans_number .= "'', '".$value['trans_number']."' ";
		}

		$data_trans_detail_list = [];
		$sql = "select td.*, p.name, p.unit from transaction_details td 
		join products p on td.id_product = p.id  
		where trans_number in (".$list_trans_number.")" ;
		$data_trans_detail_array = $this->db->query($sql)->result_array();

		foreach ($data_trans_detail_array as $key => $value) {
			$data_trans_detail_list[$value['trans_number']] = [];
		}

		foreach ($data_trans_detail_array as $key => $value) {
			$data_trans_detail_list[$value['trans_number']][] = $value;
		}
		
		foreach ($data_delivery_detail_array as $key => $value) {
			$value['detail'] = [];
			if(isset($data_trans_detail_list[$value['trans_number']])){
				$value['detail'] = $data_trans_detail_list[$value['trans_number']];
			}
			$data_delivery_detail_list[$value['delivery_code']][] = $value;
		}

		// echo "<pre>";
		// print_r($data_delivery_detail_list); die;

		$data['data_delivery_detail_list'] = $data_delivery_detail_list;

		$this->template->load('template', 'view_pending', $data);
	}

	public function detail_order($trans_number=""){
		$session_data = $this->session->userdata();
		
		$sql = "select dd.id, dd.delivery_code, 
		t.name, t.address, t.phone, t.trans_number, t.notes, dd.received_at, dd.notes as deliv_notes
		from delivery_details dd 
		join delivery d on d.delivery_code = dd.delivery_code 
		join delivery_staff ds on ds.delivery_code = dd.delivery_code 
		join transactions t on t.trans_number = dd.trans_number 
		where d.delivery_status = 2 and ds.id_staff = ".$session_data['id']." and t.trans_number ='".$trans_number."'";
		$data_trans = $this->db->query($sql)->row_array();
		$data['transaksi'] = $data_trans;

		$sql = "select td.*, p.name, p.unit  from transaction_details td 
		join products p on td.id_product = p.id  
		where trans_number  = '".$trans_number."'";
		$data_trans_detail = $this->db->query($sql)->result();
		$data['transaksi_detail'] = $data_trans_detail;

		// echo "<pre>";
		// print_r($data_trans); 
		// print_r($data_trans_detail); 
		// die;

		$this->template->load('template', 'view_detail_pending', $data);
	}

	public function submit_delivery(){
		$notes = $this->input->post('notes');
		$delivery_code = $this->input->post('delivery_code');
		$trans_number = $this->input->post('trans_number');

		$updated = false;
		
		$this->db->trans_start();

		$data = array(
			'notes' => $notes,
			'received_at' => Date('Y-m-d H:i:s')
		);
		$this->db->where([
			'delivery_code' => $delivery_code,
			'trans_number'	=> $trans_number
		]);
		$this->db->update('delivery_details', $data);

		$this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $updated = false;
        } else {
            $updated = true;
        }

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

		redirect(base_url('dashboard/pending'));

		// $file_name = uniqid();
		// $config['upload_path']          = FCPATH.'/assets/uploads/';
		// $config['allowed_types']        = 'gif|jpg|jpeg|png';
		// $config['file_name']            = $file_name;
		// $config['overwrite']            = true;

		// $this->load->library('upload', $config);

		// if (!$this->upload->do_upload('file-input')) {
		// 	$data['error'] = $this->upload->display_errors();
		// 	echo "<pre>";
		// 	print_r($data['error']); 
		// } else {
		// 	$uploaded_data = $this->upload->data();

		// 	$new_data = [
		// 		'file-input' => $uploaded_data['file_name'],
		// 	];

		// 	echo "<pre>";
		// 	print_r($new_data); 
	
		// }

		
	}

}