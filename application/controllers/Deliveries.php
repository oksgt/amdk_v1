<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Deliveries extends CI_Controller {

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

	public function index()
	{
        $this->template->load('template', 'view_delivery');
	}

    public function ajax_list()
	{
		$list = $this->Delivery->get_datatables();
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $r) {

			$status = $r->delivery_status;
			
			if($r->delivery_status == 1){
				$status = "Drafted";
			} else if($r->delivery_status == 2){
				$status = "Assigned";
			} else if($r->delivery_status == 3){
				$status = "Sending";
			} else if($r->delivery_status == 4){
				$status = "Finish";
			}

			$sql = "select count(1) as total_order from delivery_details dd where delivery_code = '".$r->delivery_code."'";
			$total_order_arr = $this->db->query($sql)->row_array();
			$total_order = $total_order_arr['total_order'];
		
			$sql = "select count(1) as total_order_done from delivery_details dd where delivery_code = '".$r->delivery_code."' 
			and received_at is not null";
			$total_order_done_arr = $this->db->query($sql)->row_array();
			$total_order_done = $total_order_done_arr['total_order_done'];
			
			$sql = "select count(1) as total_order_pending from delivery_details dd where delivery_code = '".$r->delivery_code."' 
			and received_at is not null";
			$total_order_pending_arr = $this->db->query($sql)->row_array();
			$total_order_pending = $total_order_pending_arr['total_order_pending'];

			$no++;
			$row = array();
			$row[] = formatTglIndo($r->delivery_date);
			$row[] = $r->delivery_code;
			$row[] = $status;
			$row[] = "Total Order Item: ".$total_order."<br>Total Terkirim: ".$total_order_done."<br>Total Pending: ".$total_order_pending;
			$row[] = '
				<div class="btn-group-sm d-flex" role="group" aria-label="Action Button">
					<a role="button" class="btn btn-primary btn-sm w-100 text-white" href="'.base_url('/deliveries/edit/'.$r->id).'">
						<b class="ti-location-arrow"></b> Action
					</a>
				</div>
			';

			$data[] = $row;
		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->Delivery->count_all(),
			"recordsFiltered" => $this->Delivery->count_filtered(),
			"data" => $data,
		);
		echo json_encode($output);
	}

	public function add(){
        $avaliable_batch_today = $this->db->query("
            select 
            ifnull(max(batch), 0)  as latest_batch 
            from delivery d where delivery_date = current_date()         
        ")->row_array();

        $data['batch'] = intval($avaliable_batch_today['latest_batch']) + 1;

		$ready_to_deliver = $this->db->query("
			select dd.id, dd.delivery_code, t.trans_number, t.name, t.address, t.phone, t.delivery_date_plan
			from transactions t 
			join delivery_details dd 
			on t.trans_number = dd.trans_number 
			where dd.delivery_code = 'dlv".date('Ymd')."_".$data['batch']."'
		")->result();

		$data['ready_to_deliver'] = (!empty($ready_to_deliver) ? $ready_to_deliver : []);

		$selected_staff = $this->db->query("
			select ds.id, ds.delivery_code, ds.id_staff, u.name  
			from delivery_staff ds 
			join users u on ds.id_staff = u.id  
			where u.status = 1 and ds.delivery_code = 'dlv".date('Ymd')."_".$data['batch']."'
		")->result();

		$data['selected_staff'] = (!empty($selected_staff) ? $selected_staff : []);

		$this->template->load('template', 'view_add_delivery', $data);
	}

	public function order_item(){

		$batch = $this->input->post('batch');

		$id = $this->input->post('id');
		$delivery_code = $this->input->post('delivery_code');

		if($batch !== null){
			$data['batch'] = $batch;
			$data['id'] = $id;
			$data['delivery_code'] = $delivery_code;
		} else {
			$avaliable_batch_today = $this->db->query("
				select 
				ifnull(max(batch), 0)  as latest_batch 
				from delivery d where delivery_date = current_date()         
			")->row_array();

			$data['batch'] = intval($avaliable_batch_today['latest_batch']) + 1;
			$data['id'] = 0;
			$data['delivery_code'] = "dlv".date('Ymd')."_".$data['batch'];
		}

		$transactions = $this->db->query("
			select t.* from transactions t 
			left join delivery_details dd 
			on t.trans_number = dd.trans_number  
			where dd.trans_number is null 
			order by t.delivery_date_plan asc 
		")->result();

		$data['undeliver_transactions'] = $transactions;

		$this->template->load('template', 'view_order_item_deliv', $data);
	}

	public function save_delivery_order_list(){
		$delivery_code = $this->input->post('delivery_code');
		$trans_number = $this->input->post('trans_number');
		$object = [
			'trans_number'  => $trans_number,
			'delivery_code' => $delivery_code,
			'created_at'	=> Date('Y-m-d H:i:s')
		];

		$this->db->insert('delivery_details', $object);
        $inserted_id = $this->db->insert_id();

		if($inserted_id > 0){
			$message = '
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				<strong>Success!</strong> Data order added successfully.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
		} else {
			$message = '
			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				<strong>warning!</strong> Failed to save data.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
		}
	}

	public function cancel_delivery_item_list(){
		$id = $this->input->post('id');
		$this->db->where('id', $id);
		$this->db->delete('delivery_details');	
		$affeceted = $this->db->affected_rows();

		if($affeceted > 0){
			$message = '
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				<strong>Success!</strong> Cancelation order success.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
		} else {
			$message = '
			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				<strong>Warning!</strong> Cancelation order failed.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
		}
	}

	public function available_staff_list(){
		// $source = $this->input->post('source');
		// if($source == ""){
		// 	redirect(base_url());
		// }

		$data['type'] = 'add';

		$delivery_code = $this->input->post('delivery_code');
		$batch_ = $this->input->post('batch');
		$id = $this->input->post('id');

		if( $delivery_code !== null ){
			$data['type'] = 'edit';
		}

		if( $id !== null ){
			$data['id'] = $id;
		} else {
			$data['id'] = 0;
		}

		$avaliable_batch_today = $this->db->query("
            select 
            ifnull(max(batch), 0)  as latest_batch 
            from delivery d where delivery_date = current_date()         
        ")->row_array();

		if( $batch_ !== null ){
			$data['batch'] = $batch_;
		} else {
			$data['batch'] = intval($avaliable_batch_today['latest_batch']) + 1;
		}

		$staff = $this->db->query("
			select u.id, u.name from users u 
			left join delivery_staff ds on u.id = ds.id_staff 
			where u.role_id = 2 and u.status = 1 and ds.id_staff is null
		")->result();
		$data['staff'] = $staff;
		// $this->staff_delivery($data);
		// echo "<pre>";
		// print_r($data); die;
		$this->template->load('template', 'view_staff_list', $data);
	}

	// function staff_delivery($data){
	// 	$this->template->load('template', 'view_staff_list', $data);
	// }


	public function save_staff_order_list(){
		$delivery_code = $this->input->post('delivery_code');
		$id_staff = $this->input->post('id_staff');
		$object = [
			'id_staff'  => $id_staff,
			'delivery_code' => $delivery_code
		];

		$this->db->insert('delivery_staff', $object);
        $inserted_id = $this->db->insert_id();

		if($inserted_id > 0){
			$message = '
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				<strong>Success!</strong> Data added successfully.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
		} else {
			$message = '
			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				<strong>warning!</strong> Failed to save data.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
		}
	}

	public function cancel_delivery_staff_list(){
		$id = $this->input->post('id');
		$this->db->where('id', $id);
		$this->db->delete('delivery_staff');	
		$affeceted = $this->db->affected_rows();

		if($affeceted > 0){
			$message = '
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				<strong>Success!</strong> Cancel staff delivery success.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
		} else {
			$message = '
			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				<strong>Warning!</strong> Cancel staff delivery failed.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
		}
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
			'delivery_code' 	=> $this->input->post('delivery_code'),
			'batch'				=> $this->input->post('batch'),
			'delivery_status' 	=> 1,
			'delivery_date' 	=> Date('Y-m-d'),
			'input_by'			=> $this->session->userdata('id'),
			'created_at'		=> Date('Y-m-d H:i:s')
		];

		$insert_id = $this->Delivery->save($object);

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
	}
	
	public function edit($id=""){

		if($id == ""){
			redirect(base_url('deliveries'));
		}

		$sql = "select * from delivery where id = ".$id;
		$existing = $this->db->query($sql)->row_array();

		if(empty($existing)){
			redirect(base_url('deliveries'));
		}

		// echo $existing['delivery_code']; die;
		$data['delivery_code'] = $existing['delivery_code'];
		$data['delivery_status'] = $existing['delivery_status'];
		$data['delivery_date'] = $existing['delivery_date'];
        $data['batch'] = $existing['batch'];
		$data['id'] = $id;

		$ready_to_deliver = $this->db->query("
			select dd.id, dd.delivery_code, t.trans_number, t.name, t.address, t.phone, t.delivery_date_plan,
			dd.received_at, dd.notes as received_notes
			from transactions t 
			join delivery_details dd 
			on t.trans_number = dd.trans_number 
			where dd.delivery_code = '".$existing['delivery_code']."'
		")->result();

		$data['ready_to_deliver'] = (!empty($ready_to_deliver) ? $ready_to_deliver : []);

		$selected_staff = $this->db->query("
			select ds.id, ds.delivery_code, ds.id_staff, u.name  
			from delivery_staff ds 
			join users u on ds.id_staff = u.id  
			where u.status = 1 and ds.delivery_code = '".$existing['delivery_code']."'
		")->result();

		$data['selected_staff'] = (!empty($selected_staff) ? $selected_staff : []);

		$this->template->load('template', 'view_edit_delivery', $data);
	}

	public function assign(){
		$id = $this->input->post('id');
		$updated = $this->Delivery->update([
			'delivery_status' => 2,
			'assign_time' => Date('Y-m-d H:i:s')
  		],[
			'id' => $id
		]);

		if($updated){
			$message = '
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				<strong>Success!</strong> Assignment saved successfully.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
		} else {
			$message = '
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<strong>Danger!</strong> Failed to save Assignment.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
		}

		redirect('deliveries');
	}


	// 	redirect(base_url('products'));

	// }

	// public function edit($id = null){
	// 	if($id == null || $id == ""){
	// 		redirect(base_url('products'));
	// 	}

	// 	$data = $this->Product->detail($id)->row_array();

	// 	if(empty($data)){
	// 		$message = '
	// 		<div class="alert alert-warning alert-dismissible fade show" role="alert">
	// 			<strong>Warning!</strong> Product Not Found.
	// 			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
	// 				<span aria-hidden="true">&times;</span>
	// 			</button>
	// 		</div>
	// 		';
	// 		$this->session->set_flashdata('item', $message);
	// 		redirect(base_url('products'));
	// 	}

	// 	$data['detail'] = $data;

	// 	$formated_price = "Rp ".rupiah($data['price']);
	// 	$formated_price = str_replace(".", ",", $formated_price);
	// 	$data['formated_price'] = $formated_price.".00";

	// 	$this->template->load('template', 'view_edit_product', $data);
	// }

	// public function update(){
	// 	$name 	= $this->input->post('name');
	// 	$price 	= $this->input->post('price');
	// 	$input_by = $this->session->userdata('id');
	// 	$notes  = $this->input->post('notes');
	// 	$id 	= $this->input->post('id');

	// 	$price	= str_replace("Rp ", "", $price);
	// 	$price	= str_replace(",", "", $price);
	// 	$price	= str_replace(".00", "", $price);

	// 	$object = [
	// 		'name' 	=> $name,
	// 		'unit' 	=> 'pcs',
	// 		'price' => $price,
	// 		'notes' => $notes,
	// 		'updated_by'	=> $input_by,
	// 		'updated_at'	=> Date('Y-m-d H:i:s')
	// 	];

	// 	$updated = $this->Product->update($object, ['id' => $id]);

	// 	if($updated){
	// 		$message = '
	// 		<div class="alert alert-success alert-dismissible fade show" role="alert">
	// 			<strong>Success!</strong> Data updated successfully.
	// 			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
	// 				<span aria-hidden="true">&times;</span>
	// 			</button>
	// 		</div>
	// 		';
	// 		$this->session->set_flashdata('item', $message);
	// 	} else {
	// 		$message = '
	// 		<div class="alert alert-danger alert-dismissible fade show" role="alert">
	// 			<strong>Danger!</strong> Failed to update data.
	// 			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
	// 				<span aria-hidden="true">&times;</span>
	// 			</button>
	// 		</div>
	// 		';
	// 		$this->session->set_flashdata('item', $message);
	// 	}

	// 	redirect(base_url('products'));

	// }

	// public function soft_delete(){
	// 	$input_by = $this->session->userdata('id');
	// 	$id 	= $this->input->post('id');
		
	// 	$object = [
	// 		'deleted_by'	=> $input_by,
	// 		'deleted_at'	=> Date('Y-m-d H:i:s')
	// 	];

	// 	$updated = $this->Product->update($object, ['id' => $id]);

	// 	echo json_encode(['result' => $updated]);
	// }
}
