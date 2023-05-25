<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Trans_detail extends CI_Model
{

    var $table = 'transaction_details';
    var $view = 'view_trans_detail';
    var $column_order = array(
        'name',
        'id',
        'trans_number',
        'id_product',
        'qty',
        'price',
        'sub_total_price',
        'notes',
        'input_by',
        'deleted_at',
        'created_at',
        'updated_at'
    );
    var $column_search = array(
        'name',
        'id',
        'trans_number',
        'id_product',
        'qty',
        'price',
        'sub_total_price',
        'notes',
        'input_by',
        'deleted_at',
        'created_at',
        'updated_at'
    );
    var $order = array('id' => 'desc');

    public function __construct()
    {
        parent::__construct();
    }

    private function _get_datatables_query($trans_number)
    {
        $this->db->where('trans_number', $trans_number);
        $this->db->where('input_by', $this->session->userdata('id'));
        $this->db->from($this->view);
        $i = 0;
        foreach ($this->column_search as $item) {
            if ($_POST['search']['value']) {
                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($item, $_POST['search']['value']);
                } else {
                    $this->db->or_like($item, $_POST['search']['value']);
                }

                if (count($this->column_search) - 1 == $i)
                    $this->db->group_end();
            }
            $i++;
        }

        if (isset($_POST['order'])) {
            $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function get_datatables($trans_number)
    {
        $this->_get_datatables_query($trans_number);
        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result();
    }

    function count_filtered($trans_number)
    {
        $this->_get_datatables_query($trans_number);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function count_all($trans_number)
    {
        $this->db->from($this->view);
        return $this->db->count_all_results();
    }


    public function cek_login($table, $where)
    {
        return $this->db->get_where($table, $where);
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

    public function detail_trans($trans_number)
    {
        $this->db->where('trans_number', $trans_number);
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
