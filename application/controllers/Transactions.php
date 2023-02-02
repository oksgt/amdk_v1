<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Transactions extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('form_validation'));
        $this->load->helper(array('url', 'language', 'app_helper', 'string', 'file'));

        $this->load->model(array('Product', 'Trans_detail', 'Transaction'));
        if ($this->session->userdata('status') !== 'loggedin') {
            redirect(base_url("login"));
        }
    }

    public function index()
    {
        $this->template->load('template', 'view_transactions');
        // echo Date('Ymd');
    }

    public function trans_list()
    {
        $list = $this->Transaction->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $r) {
            $no++;
            $row = array();

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
            
            $row[] = $r->trans_number;
            $row[] = formatTglIndo($r->trans_date);
            $row[] = "Name: ".$r->name."<br>Address: ".$r->address."<br>Phone: ".$r->phone;
            $row[] = formatTglIndo($r->delivery_date_plan);
            $row[] = ($r->delivery_date!=="") ? formatTglIndo_2($r->delivery_date) : "";
            $row[] = $status;
            $row[] = ($r->payment_type_id == 1)? "Paid" : "Pending";
            $row[] = rupiah($r->total_price);
            $row[] = $r->notes;
            $row[] = '
				<div class="btn-group-sm d-flex" role="group" aria-label="Action Button">
					<a role="button" class="btn btn-info btn-sm w-100 text-white" href="' . base_url('transactions/view/' . $r->trans_number) . '">
						<b class="ti-eye"></b> View
					</a>
				</div>
			';
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->Transaction->count_all(),
            "recordsFiltered" => $this->Transaction->count_filtered(),
            "data" => $data,
        );
        echo json_encode($output);
    }

    public function trans_details_list($trans_number)
    {
        $list = $this->Trans_detail->get_datatables($trans_number);
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $r) {
            $no++;
            $row = array();
            $row[] = $r->name;
            $row[] = $r->qty;
            $row[] = rupiah($r->price);
            $row[] = rupiah($r->sub_total_price);
            $row[] = '
				<div class="btn-group-sm d-flex" role="group" aria-label="Action Button">
					<button role="button" class="btn btn-warning btn-sm w-100 text-white" onclick="show_edit(' . $r->id . ')">
						<b class="ti-pencil-alt"></b> Edit
					</button>
				</div>
			';
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->Product->count_all($trans_number),
            "recordsFiltered" => $this->Product->count_filtered($trans_number),
            "data" => $data,
        );
        echo json_encode($output);
    }

    public function add()
    {
        // deleted unused trans number
        $sql = "delete from transaction_number where transaction_number in (
            select tn.transaction_number
            from transaction_number tn
            left join transactions t 
            on tn.transaction_number = t.trans_number
            where t.trans_number is null
        )";
        $del_unused_trans_number = $this->db->query($sql);

        $sql = "select max(number) as max from transaction_number where input_date = current_date()";
        $count_trans_number = $this->db->query($sql)->row_array();

        $max_number = ($count_trans_number['max'] == null || $count_trans_number['max'] == "") ? 0 : $count_trans_number['max'];

        $object = [
            'transaction_number' => Date('Ymd') . "_" . strval($max_number + 1),
            'input_date '        => Date('Ymd'),
            'number'             => $max_number + 1
        ];

        $create_trans_number = $this->db->insert('transaction_number', $object);

        if ($create_trans_number > 0) {
            $data['trans_number']   = $object['transaction_number'];
            $data['product']        = $this->Product->get_data()->result();
            $this->template->load('template', 'view_add_transaction', $data);
        }
    }

    public function view($transaction_number = "")
    {

        if ($transaction_number == "") {
            redirect('transactions');
        }
        $data['trans_number']   = $transaction_number;
        $this->template->load('template', 'view_trans_detail', $data);
    }

    // public function trans_details_list_view($trans_number){
    //     $list = $this->Trans_detail->get_datatables($trans_number);
    // 	$data = array();
    // 	$no = $_POST['start'];
    // 	foreach ($list as $r) {
    // 		$no++;
    // 		$row = array();
    // 		$row[] = $r->name;
    // 		$row[] = $r->qty;
    // 		$row[] = rupiah($r->price);
    //         $row[] = rupiah($r->sub_total_price);
    // 		$data[] = $row;
    // 	}

    // 	$output = array(
    // 		"draw" => $_POST['draw'],
    // 		"recordsTotal" => $this->Product->count_all($trans_number),
    // 		"recordsFiltered" => $this->Product->count_filtered($trans_number),
    // 		"data" => $data,
    // 	);
    // 	echo json_encode($output);
    // }

    public function validation()
    {
        $this->form_validation->set_rules('product', 'Product Item', 'callback_validasi_pilih');
        $this->form_validation->set_rules('input_qty', 'Quantity', 'required', array('required' => 'This Field Cannot Empty'));

        if ($this->form_validation->run()) {
            $array = array('success' => true);
        } else {
            $array = array(
                'error' => true,
                'input_qty_error_detail'            => form_error('input_qty', '<b class="fa fa-exclamation-triangle"></b> ', ' '),
                'product_error_detail'              => form_error('product', '', ''),
            );
        }
        echo json_encode($array);
    }

    public function validasi_pilih($str)
    {
        if ($str == 'x') {
            $this->form_validation->set_message('validasi_pilih', '<b class="fa fa-exclamation-triangle"></b> Please Choose One');
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function check_stock($id_product, $qty)
    {
        $sql = "select stock from products where id=" . $id_product;
        $product = $this->db->query($sql)->row_array();

        if ($product['stock'] >= $qty) {
            echo json_encode(['result' => true, 'message' => '']);
        } else {
            echo json_encode(['result' => false, 'message' => 'Stock Not Available']);
        }
    }

    public function save_item()
    {
        //cek apakah sudah ada barang yang serupa didalam list detail
        $existing = $this->Trans_detail->get_by([
            'trans_number'      => $this->input->post('trans_number'),
            'id_product'        => $this->input->post('product'),
        ])->row_array();

        if ($existing) {
            $object = [
                'qty'               => $this->input->post('input_qty') + $existing['qty'],
                'sub_total_price'   => $existing['price'] * ($this->input->post('input_qty') + $existing['qty']),
            ];

            $update_existing = $this->Trans_detail->update($object, [
                'trans_number'      => $this->input->post('trans_number'),
                'id_product'        => $this->input->post('product'),
            ]);

            if ($update_existing) {
                echo json_encode(['result' => true]);
            } else {
                echo json_encode(['result' => false]);
            }
        } else {
            //get product price
            $product = $this->Product->detail($this->input->post('product'))->row_array();

            $object = [
                'trans_number'      => $this->input->post('trans_number'),
                'id_product'        => $this->input->post('product'),
                'qty'               => $this->input->post('input_qty'),
                'price'             => $product['price'],
                'sub_total_price'   => $product['price'] * $this->input->post('input_qty'),
                'notes'             => '-',
                'trans_status'      => 1,
                'input_by'            => $this->session->userdata('id'),
                'created_at'        => Date('Y-m-d H:i:s')
            ];

            $insert_trans_detail = $this->Trans_detail->save($object);

            if ($insert_trans_detail > 0) {
                echo json_encode(['result' => true]);
            } else {
                echo json_encode(['result' => false]);
            }
        }
    }

    public function sum_transaction_detail($trans_number)
    {
        $sql = "select sum(sub_total_price) as grand_total  from view_trans_detail vtd where vtd.trans_number = '" . $trans_number . "'";
        $result = $this->db->query($sql)->row_array();
        $grand_total = ($result['grand_total'] == null || $result['grand_total'] == '') ? 0 : $result['grand_total'];
        echo json_encode(['result' => $grand_total]);
    }

    public function update_qty()
    {
        $qty   = $this->input->post("qty");
        $id    = $this->input->post("id");

        $product = $this->Trans_detail->detail($id)->row_array();

        $update = $this->Trans_detail->update([
            'qty' => $qty,
            'sub_total_price' => $qty * $product['price']
        ], ['id' => $id]);
        if ($update) {
            echo json_encode(['result' => true]);
        } else {
            echo json_encode(['result' => false]);
        }
    }

    public function checkout($trans_number)
    {
        $sql = "select * from view_trans_detail where trans_number = '" . $trans_number . "'";
        $cek = $this->db->query($sql)->result();
        if (empty($cek)) {
            redirect('transactions/add');
        }
        $total_price = 0;
        foreach ($cek as $key => $value) {
            $total_price += $value->sub_total_price;
        }
        $data['trans_number'] = $trans_number;
        $data['total_price']  = $total_price;
        $this->template->load('template', 'view_checkout', $data);
    }

    public function finish()
    {
        $name         = $this->input->post('input_name');
        $address      = $this->input->post('input_address');
        $phone        = $this->input->post('input_phone');
        $notes        = $this->input->post('notes');
        $trans_number = $this->input->post('trans_number');
        $deliv_date   = $this->input->post('input_delivery');
        $payment_status = $this->input->post('input_payment_status');

        if($payment_status == 0){
            $message = '
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<strong>Warning!</strong> Please select payment status!.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			';
            $this->session->set_flashdata('item', $message);
            $this->checkout($trans_number );
        }

        $trans_detail = $this->Trans_detail->detail_trans($trans_number)->result();

        $this->db->trans_begin();

        foreach ($trans_detail as $key => $value) {
            $prod = $this->Product->detail($value->id_product)->row_array();

            $this->Product->update(
                [
                    'stock' => intval($prod['stock']) - intval($value->qty)
                ],
                [
                    'id' => $value->id_product
                ]
            );
        }

        $arr_date   = explode("/", $deliv_date);
        $deliv_date = $arr_date['2']."-".$arr_date['1']."-".$arr_date['0'];

        $object = [
            'trans_number' => $trans_number,
            'trans_date'   => Date('Y-m-d'),
            'trans_status' => 3,
            'name'         => $name,
            'address'      => $address,
            'phone'        => $phone,
            'notes'        => $notes,
            'delivery_date_plan'    => $deliv_date,
            'payment_type_id'       => $payment_status,
            'input_by'      => $this->session->userdata('id'),
            'created_at'    => Date('Y-m-d H:i:s'),
            'total_price'   => $this->input->post('total_price')
        ];

        $insert = $this->Transaction->save($object);

        $obj = [
            'trans_status' => 3
        ];

        $update_trans_status = $this->Trans_detail->update($obj, ['trans_number', $trans_number]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $message = '
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<strong>Danger!</strong> Transaction Failed!
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
                    <strong>Success!</strong> Transaction Success!
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                ';
            $this->session->set_flashdata('item', $message);
        }

        redirect(base_url('transactions'));
    }
}
