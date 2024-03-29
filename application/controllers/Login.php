<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Login extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['user' => 'model']);
    }

    public function index()
    {

        if ($this->session->userdata('status') == 'loggedin') {
            redirect(base_url());
        }

        $this->load->view('login');
    }

    function action()
    {
        $username        = $this->input->post('username');
        $plain_password  = $this->input->post('password', true);
        $where = array(
            'username' => $username
        );
        $cek = $this->model->cek_login("users", $where);
        // print_r($cek->result()); die;
        if (!$cek->result()) {
            $this->session->set_flashdata('message', '
            <span class="login100-form-title text-danger bg-light" style="margin-bottom: 10px;
            border-radius: 12px; font-size: 20px; padding: 10px 0px 10px 0px; font-weight:bold;">
                Username not found!
            </span>
            ');
            redirect("login", "refresh");
        } else {
            if ($cek->num_rows() >= 1) {
                foreach ($cek->result() as $row) {
                    $verify = $this->hash_verify($plain_password, $row->password);
                    // echo $row->password;
                    // echo $verify; die;
                    if ($verify == TRUE) {
                        $login_data = $cek->row_array();
                        if (!empty($login_data)) {

                            // print_r($login_data); die;
                            if($login_data['status'] == null || $login_data['status'] == ""){
                                $this->session->set_flashdata('message', '<span class="login100-form-title text-danger bg-light" style="margin-bottom: 10px;
                                border-radius: 12px; font-size: 20px; padding: 10px 0px 10px 0px; font-weight:bold;">
                                    User is not active!
                                </span>');
                                redirect("login", "refresh");
                            } else {
                                $login_data['status'] = 'loggedin';
                                $this->session->set_userdata($login_data);
                                $session = $this->session->userdata();

                                $sql = "select role_name from user_roles where id = ".$session['role_id'];
                                $data = $this->db->query($sql)->row_array();
                                $login_data['role_name'] = $data['role_name'];
                                
                                $this->session->unset_userdata([
                                    'password', 'username'
                                ]);

                                if($login_data['role_id'] == 1){
                                    redirect('transactions');
                                } else {
                                    redirect('dashboard');
                                }
                            }
                        }
                    } else {
                        // echo "False";
                        $this->session->set_flashdata('message', '<span class="login100-form-title text-danger bg-light" style="margin-bottom: 10px;
                        border-radius: 12px; font-size: 20px; padding: 10px 0px 10px 0px; font-weight:bold;">
                            Wrong password!</span>');
                        redirect("login", "refresh");
                    }
                }
            } else {
                $this->session->set_flashdata('message', '<span class="login100-form-title text-danger bg-light" style="margin-bottom: 10px;
                border-radius: 12px; font-size: 20px">
                    Failed!
                </span>');
                redirect("login", "refresh");
            }
        }
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

    function logout()
    {
        $this->session->sess_destroy();
        redirect('login');
    }

    function test_hash(){
        echo $this->hash_string('staff_1');
    }

    public function add_user()
    {
        $data = array(
            'role_id'         => 1,
            'name'            => 'staff_1',
            'username'        => 'staff_1',
            'password'        => $this->hash_string('staff_1'),
        );
        $insertedId = $this->model->save($data);
        echo $insertedId;
    }
}
