<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends CI_Controller {

    public function __construct()
	{
		parent::__construct();
		$this->load->library(array('form_validation'));
		$this->load->helper(array('url', 'language', 'app_helper','string', 'file'));

		$this->load->model(array('Product', 'User'));
		if ($this->session->userdata('status') !== 'loggedin') {
			redirect(base_url("login"));
		}
	}

    public function index(){
        // print_r($this->session->userdata()); die;
        $this->template->load('template', 'view_user');   
    }

    public function list_user()
	{
		$list = $this->User->get_datatables();
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $r) {
			$no++;
			$row = array();
			$row[] = $r->name;
			$row[] = $r->username;
			$row[] = $r->role_name;
            $row[] = ($r->status == 1) ? '<b class="ti-check text-success" style="font-weight: bold"> </b> Active' : '<b class="ti-close text-danger" style="font-weight: bold"> </b> Not Active';
			
            if($this->session->userdata('id') == $r->id){
                $row[] = '
                    <div class="btn-group-sm d-flex" role="group" aria-label="Action Button">
                        <a role="button" class="btn btn-warning btn-sm w-100 text-white" href="'.base_url('/users/edit/'.$r->id).'">
                            <b class="ti-pencil-alt"></b> Edit
                        </a>
                    </div>
                ';
            } else {
                $row[] = '
                    <div class="btn-group-sm d-flex" role="group" aria-label="Action Button">
                        <a role="button" class="btn btn-warning btn-sm w-100 text-white" href="'.base_url('/users/edit/'.$r->id).'">
                            <b class="ti-pencil-alt"></b> Edit
                        </a>
                        <button type="button" class="btn bg-white default btn-sm border-0 text-danger w-100" onclick="delete_data('.$r->id.')">
                            <b class="ti-trash"></b>Delete
                        </button>
                    </div>
                ';
            }
			$data[] = $row;
		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->User->count_all(),
			"recordsFiltered" => $this->User->count_filtered(),
			"data" => $data,
		);
		echo json_encode($output);
	}

	public function changepassword()
	{
        $this->template->load('template', 'view_change_password');
	}

    public function update_password(){
        $old_password = $this->input->post('old_password');
        $new_password = $this->input->post('new_password');
        $user_id      = $this->session->userdata('id');

        //$validasi old password
        $validasi_old_pass = $this->login_validasi($user_id, $old_password);

        if(!$validasi_old_pass['status']){
            $message = '
			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				<strong>Warning!</strong> '.$validasi_old_pass['message'].'
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
            redirect('users/changepassword');
        }

        $object = array(
            'password'        => $this->hash_string($new_password),
        );

        $update_password = $this->User->update($object, ['id' => $user_id]);
        
        if($update_password){
            $message = '
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				<strong>Success!</strong> '.$validasi_old_pass['message'].'
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
            redirect('users/changepassword');
        } else {
            $message = '
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<strong>Danger!</strong> Something is wrong!
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
            redirect('users/changepassword');
        }
    }

    private function login_validasi($id_user, $plain_password)
    {
        $result = [];

        $where = array(
            'id' => $id_user
        );
        $cek = $this->User->cek_login("users", $where);
        if (!$cek->result()) {
            $result = [
                'status' => false,
                'message'=> 'Wrong ID User!'
            ];
        } else {
            
            if ($cek->num_rows() >= 1) {
                foreach ($cek->result() as $row) {
                    $verify = $this->hash_verify($plain_password, $row->password);
                    if ($verify == TRUE) {
                        $login_data = $cek->row_array();
                        if (!empty($login_data)) {
                            $result = [
                                'status' => true,
                                'message'=> 'Password changed!'
                            ];
                        }
                    } else {
                        $result = [
                            'status' => false,
                            'message'=> 'Wrong old password!'
                        ];
                    }
                }
            } else {
                $result = [
                    'status' => false,
                    'message'=> 'Something is wrong'
                ];
            }
        }

        return $result;
    }

    public function hash_string($string)
    {
        $hashed_string = password_hash($string, PASSWORD_BCRYPT);
        return $hashed_string;
    }

    public function hash_verify($plain_text, $hashed_string)
    {
        $hashed_string = password_verify($plain_text, $hashed_string);
        return $hashed_string;
    }

    public function add(){
        $data['role'] = $this->db->get('user_roles')->result();
        $this->template->load('template', 'view_user_add', $data);           
    }

    public function save(){

        $role_id = $this->input->post('role');

        if($role_id == 0){
            $message = '
			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				<strong>Warning!</strong> Please select user role.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
            redirect('users/add');
        }

        $sql = "select username from users where username = '".$this->input->post('input_username')."'";
        $username_exist = $this->db->query($sql)->row_array();

        if(!empty($username_exist)){
            $message = '
			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				<strong>Warning!</strong> Username is already used, please use another one.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
            redirect('users/add');
        }

        $data = array(
            'role_id'         => $role_id,
            'name'            => $this->input->post('input_name'),
            'username'        => $this->input->post('input_username'),
            'password'        => $this->hash_string('amdk_123'),
            'status'          => 1
        );
        $insertedId = $this->User->save($data);

        if($insertedId > 0){
            $message = '
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				<strong>Success!</strong> Add user success.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
            redirect('users');
        } else {
            $message = '
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<strong>Danger!</strong> Failed to add user.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
            redirect('users');
        }
    }

    public function edit($id = ""){
        if($id == "" || $id == 0){
            redirect('users');
        }

        $user = $this->User->detail($id)->row_array();
        // print_r( $user ); die;
        if(empty($user)){
            $message = '
			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				<strong>Warning!</strong> User not found.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
            redirect('users');
        }

        $data['user'] = $user;
        $data['role'] = $this->db->get('user_roles')->result();
        $this->template->load('template', 'view_user_edit', $data); 
    }

    public function update(){

        $id      = $this->input->post('id');
        $role_id = $this->input->post('role');

        if($role_id == 0){
            $message = '
			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				<strong>Warning!</strong> Please select user role.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
            redirect('users/edit/'.$id);
        }

        $sql = "select username from users where username = '".$this->input->post('input_username')."'";
        $username_exist = $this->db->query($sql)->row_array();

        if(!empty($username_exist)){
            $message = '
			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				<strong>Warning!</strong> Username is already used, please use another one.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
            redirect('users/add');
        }

        $data = array(
            'role_id'         => $role_id,
            'name'            => $this->input->post('input_name'),
            'username'        => $this->input->post('input_username')
        );
        
        $insertedId = $this->User->update($data, ['id' => $id]);

        if($insertedId){
            $message = '
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				<strong>Success!</strong> Edit user success.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
            redirect('users');
        } else {
            $message = '
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<strong>Danger!</strong> Failed to edit user.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
			$this->session->set_flashdata('item', $message);
            redirect('users');
        }
    }
}
