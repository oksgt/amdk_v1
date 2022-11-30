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


}
