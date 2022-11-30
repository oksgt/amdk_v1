<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User extends CI_Model
{

    var $table = 'users';
    var $view = 'view_user';
    var $column_order = array('id', 'identity', 'name', 'username', 'password', 'role_name');
    var $column_search = array('id', 'identity', 'name', 'username', 'password', 'role_name');
    var $order = array('id' => 'desc');

    public function __construct()
    {
        parent::__construct();
    }

    public function cek_login($table, $where)
    {
        return $this->db->get_where($this->view, $where);
    }

    public function query($sql)
    {
        return $this->db->query($sql);
    }
    public function get_data()
    {
        return $this->db->get($this->view);
    }

    public function save($object)
    {
        $this->db->insert($this->table, $object);
        return $this->db->insert_id();
    }

    public function detail($id)
    {
        $this->db->where('id', $id);
        return $this->db->get($this->table);
    }

    public function update($object, $where)
    {
        // return $this->db->affected_rows();
        $this->db->trans_start();
        $this->db->where($where);
        $this->db->update($this->table, $object);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return false;
        } else {
            return true;
        }
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete($this->table);
        return $this->db->affected_rows();
    }

    public function get_by($parameter)
    {
        $this->db->where($parameter);
        return $this->db->get($this->view);
    }
}
