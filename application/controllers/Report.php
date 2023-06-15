<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;


class Report extends CI_Controller {

    public function __construct()
	{
		parent::__construct();
		$this->load->library(array('form_validation'));
		$this->load->helper(array('url', 'language', 'app_helper','string', 'file'));

		$this->load->model(array('Product', 'Transaction'));
		if ($this->session->userdata('status') !== 'loggedin') {
			redirect(base_url("login"));
		}
	}

	public function index()
	{
        $this->template->load('template', 'view_report');
	}

	public function create_excel()
	{
		$post = $this->input->post();
		$date = $post['input_date'];
		$formatted_date = date('Y-m-d', strtotime($date));
		$label_date = $post['input_date'];
		// echo $formatted_date;
		// print_r($post); 
		// die;
		$spreadsheet = new Spreadsheet();
		
		// Set the worksheet's title
		$spreadsheet->getActiveSheet()->setTitle('My Worksheet');
		
		// Add some data to the worksheet
		$spreadsheet->getActiveSheet()->setCellValue('A1', 'LAPORAN HARIAN PENJUALAN KESELURUHAN AMDK TOYANIKI PERUMDAM TIRTA SATRIA');
		$spreadsheet->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->mergeCells('A1:Q1');
		$spreadsheet->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('A2', 'TANGGAL '.$label_date);
		$spreadsheet->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->mergeCells('A2:Q2');
		$spreadsheet->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('A4', 'No');
		$spreadsheet->getActiveSheet()->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('A4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
		$spreadsheet->getActiveSheet()->mergeCells('A4:A5');
		$spreadsheet->getActiveSheet()->getStyle('A4:A5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
		$spreadsheet->getActiveSheet()->getStyle('A4:A5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('A4:A5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('B4', 'Tanggal');
		$spreadsheet->getActiveSheet()->getStyle('B4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('B4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
		$spreadsheet->getActiveSheet()->mergeCells('B4:B5');
		$spreadsheet->getActiveSheet()->getStyle('B4:B5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
		$spreadsheet->getActiveSheet()->getStyle('B4:B5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('B4:B5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('C4', 'Pembelian');
		$spreadsheet->getActiveSheet()->getStyle('C4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('C4:E4')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
		$spreadsheet->getActiveSheet()->getStyle('C4:E4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('C4:E4')->getFont()->setBold(true);
		$spreadsheet->getActiveSheet()->mergeCells('C4:E4');

		$spreadsheet->getActiveSheet()->setCellValue('C5', 'Perumdam');
		$spreadsheet->getActiveSheet()->getStyle('C5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('C5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
		$spreadsheet->getActiveSheet()->getStyle('C5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('C5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('D5', 'SKPD');
		$spreadsheet->getActiveSheet()->getStyle('D5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('D5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
		$spreadsheet->getActiveSheet()->getStyle('D5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('D5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('E5', 'Pribadi');
		$spreadsheet->getActiveSheet()->getStyle('E5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('E5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
		$spreadsheet->getActiveSheet()->getStyle('E5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('E5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('F4', 'Jumlah Satuan');
		$spreadsheet->getActiveSheet()->getStyle('F4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('F4:I4')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
		$spreadsheet->getActiveSheet()->getStyle('F4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('F4:I4')->getFont()->setBold(true);
		$spreadsheet->getActiveSheet()->mergeCells('F4:I4');

		$spreadsheet->getActiveSheet()->setCellValue('F5', 'Galon 19lt');
		$spreadsheet->getActiveSheet()->getStyle('F5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('F5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
		$spreadsheet->getActiveSheet()->getStyle('F5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('F5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('G5', 'Galon Kran');
		$spreadsheet->getActiveSheet()->getStyle('G5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('G5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
		$spreadsheet->getActiveSheet()->getStyle('G5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('G5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('H5', 'Botol 330ml Dus');
		$spreadsheet->getActiveSheet()->getStyle('H5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('H5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
		$spreadsheet->getActiveSheet()->getStyle('H5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('H5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('I5', 'Cup 220ml Dus');
		$spreadsheet->getActiveSheet()->getStyle('I5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('I5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
		$spreadsheet->getActiveSheet()->getStyle('I5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('I5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('J4', 'Harga Satuan (Rp)');
		$spreadsheet->getActiveSheet()->getStyle('J4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('J4:M4')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
		$spreadsheet->getActiveSheet()->getStyle('J4:M4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('J4:M4')->getFont()->setBold(true);
		$spreadsheet->getActiveSheet()->mergeCells('J4:M4');

		$spreadsheet->getActiveSheet()->setCellValue('J5', 'Galon 19lt');
		$spreadsheet->getActiveSheet()->getStyle('J5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('J5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
		$spreadsheet->getActiveSheet()->getStyle('J5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('J5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('K5', 'Galon Kran');
		$spreadsheet->getActiveSheet()->getStyle('K5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('K5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
		$spreadsheet->getActiveSheet()->getStyle('K5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('K5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('L5', 'Botol 330ml Dus');
		$spreadsheet->getActiveSheet()->getStyle('L5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('L5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
		$spreadsheet->getActiveSheet()->getStyle('L5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('L5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('M5', 'Cup 220ml Dus');
		$spreadsheet->getActiveSheet()->getStyle('M5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('M5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
		$spreadsheet->getActiveSheet()->getStyle('M5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('M5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('N4', 'Total Harga');
		$spreadsheet->getActiveSheet()->getStyle('N4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('N4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
		$spreadsheet->getActiveSheet()->mergeCells('N4:N5');
		$spreadsheet->getActiveSheet()->getStyle('N4:N5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
		$spreadsheet->getActiveSheet()->getStyle('N4:N5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('N4:N5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('O4', 'Keterangan');
		$spreadsheet->getActiveSheet()->getStyle('O4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('O4:P4')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
		$spreadsheet->getActiveSheet()->getStyle('O4:P4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('O4:P4')->getFont()->setBold(true);
		$spreadsheet->getActiveSheet()->mergeCells('O4:P4');

		$spreadsheet->getActiveSheet()->setCellValue('O5', 'Debit');
		$spreadsheet->getActiveSheet()->getStyle('O5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('O5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
		$spreadsheet->getActiveSheet()->getStyle('O5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('O5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('P5', 'Piutang');
		$spreadsheet->getActiveSheet()->getStyle('P5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('P5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
		$spreadsheet->getActiveSheet()->getStyle('P5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('P5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('Q4', 'Tanggal Bayar');
		$spreadsheet->getActiveSheet()->getStyle('Q4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('Q4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
		$spreadsheet->getActiveSheet()->mergeCells('Q4:Q5');
		$spreadsheet->getActiveSheet()->getStyle('Q4:Q5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
		$spreadsheet->getActiveSheet()->getStyle('Q4:Q5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('Q4:Q5')->getFont()->setBold(true);

		$column_jp_char = array();

		// Loop through the range of characters from 'a' to 'q'
		foreach(range('A', 'Q') as $char) {
			$column_jp_char = $char;
			$spreadsheet->getActiveSheet()->getColumnDimension($char)->setAutoSize(true);
		}

		$data_transasksi = $this->Transaction->get_data()->result_array();
		$numrow = 6;
		$no = 1;
		$sums = array();

		$total_qty_galon_19lt = 0;
		$total_qty_galon_kran = 0;
		$total_qty_botol_330 = 0;
		$total_qty_cup_220 = 0;

		$total_price_galon_19lt = 0;
		$total_price_galon_kran = 0;
		$total_price_botol_330 = 0;
		$total_price_cup_220 = 0;

		$total_harga = 0;
		$total_debit = 0;
		$total_piutang = 0;

		foreach ($data_transasksi as $key => $value) {
			
			$spreadsheet->getActiveSheet()->setCellValue('A'.$numrow, $no);
			
			foreach(range('A', 'Q') as $char) {
				$spreadsheet->getActiveSheet()->getStyle($char.$numrow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
			}

			$spreadsheet->getActiveSheet()->setCellValue('B'. $numrow, formatTglIndo($value['trans_date']));

			$sql_jp_trans  = "select jp.jenis_pelanggan, t.name  from jenis_pelanggan jp 
				join transactions t on jp.id = t.jenis_pelanggan where 
				t.trans_number = '".$value['trans_number']."' and t.jenis_pelanggan = 1";
			$result_jp_trans = $this->db->query($sql_jp_trans)->row_array();

			if(empty($result_jp_trans)){
				$spreadsheet->getActiveSheet()->setCellValue('C'. $numrow, "");
			} else {
				$spreadsheet->getActiveSheet()->setCellValue('C'. $numrow, $result_jp_trans['name']);
			}

			$sql_jp_trans  = "select jp.jenis_pelanggan, t.name  from jenis_pelanggan jp 
				join transactions t on jp.id = t.jenis_pelanggan where 
				t.trans_number = '".$value['trans_number']."' and t.jenis_pelanggan = 2";
			$result_jp_trans = $this->db->query($sql_jp_trans)->row_array();
			if(empty($result_jp_trans)){
				$spreadsheet->getActiveSheet()->setCellValue('D'. $numrow, "");
			} else {
				$spreadsheet->getActiveSheet()->setCellValue('D'. $numrow, $result_jp_trans['name']);
			}
 
			$sql_jp_trans  = "select jp.jenis_pelanggan, t.name  from jenis_pelanggan jp 
				join transactions t on jp.id = t.jenis_pelanggan where 
				t.trans_number = '".$value['trans_number']."' and t.jenis_pelanggan = 3";
			$result_jp_trans = $this->db->query($sql_jp_trans)->row_array();
			if(empty($result_jp_trans)){
				$spreadsheet->getActiveSheet()->setCellValue('E'. $numrow, "");
			} else {
				$spreadsheet->getActiveSheet()->setCellValue('E'. $numrow, $result_jp_trans['name']);
			}

			// detail qty dan harga product id 1 Toyaniki Galon 19Lt
			$detail_item = $this->db->query("select qty, price from view_trans_detail vtd where trans_number = '".$value['trans_number']."' and id_product = 1")->row_array();
			if(empty($detail_item)){
				$spreadsheet->getActiveSheet()->setCellValue('F'. $numrow, "");
				$spreadsheet->getActiveSheet()->setCellValue('J'. $numrow, "");
			} else {
				$spreadsheet->getActiveSheet()->setCellValue('F'. $numrow, $detail_item['qty']);
				$spreadsheet->getActiveSheet()->setCellValue('J'. $numrow, $detail_item['price']);
			}
			$total_qty_galon_19lt += (empty($detail_item)) ? 0: $detail_item['qty'];
			$total_price_galon_19lt += (empty($detail_item)) ? 0: $detail_item['price'];

			// detail qty dan harga product id 22 GALON KRAN
			$detail_item = $this->db->query("select qty, price from view_trans_detail vtd where trans_number = '".$value['trans_number']."' and id_product = 22")->row_array();
			if(empty($detail_item)){
				$spreadsheet->getActiveSheet()->setCellValue('G'. $numrow, "");
				$spreadsheet->getActiveSheet()->setCellValue('K'. $numrow, "");
			} else {
				$spreadsheet->getActiveSheet()->setCellValue('G'. $numrow, $detail_item['qty']);
				$spreadsheet->getActiveSheet()->setCellValue('K'. $numrow, $detail_item['price']);
			}
			$total_qty_galon_kran += (empty($detail_item)) ? 0: $detail_item['qty'];
			$total_price_galon_kran += (empty($detail_item)) ? 0: $detail_item['price'];

			// detail qty dan harga product id 3 Toyaniki Botol 330ml Dus
			$detail_item = $this->db->query("select qty, price from view_trans_detail vtd where trans_number = '".$value['trans_number']."' and id_product = 3")->row_array();
			if(empty($detail_item)){
				$spreadsheet->getActiveSheet()->setCellValue('H'. $numrow, "");
				$spreadsheet->getActiveSheet()->setCellValue('L'. $numrow, "");
			} else {
				$spreadsheet->getActiveSheet()->setCellValue('H'. $numrow, $detail_item['qty']);
				$spreadsheet->getActiveSheet()->setCellValue('L'. $numrow, $detail_item['price']);
			}
			$total_qty_botol_330 += (empty($detail_item)) ? 0: $detail_item['qty'];
			$total_price_botol_330 += (empty($detail_item)) ? 0: $detail_item['price'];

			// detail qty dan harga product id 2 Toyaniki CUP
			$detail_item = $this->db->query("select qty, price from view_trans_detail vtd where trans_number = '".$value['trans_number']."' and id_product = 2")->row_array();
			if(empty($detail_item)){
				$spreadsheet->getActiveSheet()->setCellValue('I'. $numrow, "");
				$spreadsheet->getActiveSheet()->setCellValue('M'. $numrow, "");
			} else {
				$spreadsheet->getActiveSheet()->setCellValue('I'. $numrow, $detail_item['qty']);
				$spreadsheet->getActiveSheet()->setCellValue('M'. $numrow, $detail_item['price']);
			}
			$total_qty_cup_220 += (empty($detail_item)) ? 0: $detail_item['qty'];
			$total_price_cup_220 += (empty($detail_item)) ? 0: $detail_item['price'];

			$spreadsheet->getActiveSheet()->setCellValue('N'.$numrow, $value['total_price']);
			$total_harga += ($value['total_price'] == null) ? 0: $value['total_price'];

			$detail_item = $this->db->query("select total_price, payment_type_id from view_trans where trans_number = '".$value['trans_number']."' and payment_type_id = 1")->row_array();
			if(empty($detail_item)){
				$spreadsheet->getActiveSheet()->setCellValue('O'. $numrow, "");
			} else {
				$spreadsheet->getActiveSheet()->setCellValue('O'. $numrow, $detail_item['total_price']);
			}
			$total_debit += ($value['total_price'] == null) ? 0: $detail_item['total_price'];

			$detail_item = $this->db->query("select total_price, payment_type_id from view_trans where trans_number = '".$value['trans_number']."' and payment_type_id = 2")->row_array();
			if(empty($detail_item)){
				$spreadsheet->getActiveSheet()->setCellValue('P'. $numrow, "");
			} else {
				$spreadsheet->getActiveSheet()->setCellValue('P'. $numrow, $detail_item['total_price']);
			}
			$total_piutang += ($value['total_price'] == null) ? 0: $detail_item['total_price'];

			$detail_item = $this->db->query("select total_price, payment_type_id, trans_date from view_trans where trans_number = '".$value['trans_number']."'")->row_array();
			if($detail_item['payment_type_id'] == '2'){
				$spreadsheet->getActiveSheet()->setCellValue('Q'. $numrow, "");
			} else {
				$spreadsheet->getActiveSheet()->setCellValue('Q'. $numrow, $detail_item['trans_date']);
			}

			// $total_qty_galon_19lt = 0;
			// $total_qty_galon_kran = 0;
			// $total_qty_botol_330 = 0;
			// $total_qty_cup_220 = 0;

			// $total_price_galon_19lt = 0;
			// $total_price_galon_kran = 0;
			// $total_price_botol_330 = 0;
			// $total_price_cup_220 = 0;

			// $total_harga = 0;
			// $total_debit = 0;
			// $total_piutang = 0;

			$rowData = $spreadsheet->getActiveSheet()->rangeToArray('A' . $numrow . ':' . $spreadsheet->getActiveSheet()->getHighestColumn() . $numrow, null, true, false);
    		$sums[] = array_sum($rowData[0]);

			$no++;
			$numrow++;
		}

		// print_r($sums);
		// die;

		$highestRow = $spreadsheet->getActiveSheet()->getHighestRow()+1;

		$spreadsheet->getActiveSheet()->setCellValue('A'.$highestRow, 'Jumlah');
		$spreadsheet->getActiveSheet()->getStyle('A'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('A'.$highestRow.':E'.$highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
		$spreadsheet->getActiveSheet()->getStyle('A'.$highestRow.':E'.$highestRow)->getFont()->setBold(true);
		$spreadsheet->getActiveSheet()->mergeCells('A'.$highestRow.':E'.$highestRow);


		
		$spreadsheet->getActiveSheet()->setCellValue('F'.$highestRow, $total_qty_galon_19lt);
		$spreadsheet->getActiveSheet()->getStyle('F'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('F'.$highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);

		$spreadsheet->getActiveSheet()->setCellValue('G'.$highestRow, $total_qty_galon_kran);
		$spreadsheet->getActiveSheet()->getStyle('G'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('G'.$highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);

		$spreadsheet->getActiveSheet()->setCellValue('H'.$highestRow, $total_qty_botol_330);
		$spreadsheet->getActiveSheet()->getStyle('H'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('H'.$highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);

		$spreadsheet->getActiveSheet()->setCellValue('I'.$highestRow, $total_qty_cup_220);
		$spreadsheet->getActiveSheet()->getStyle('I'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('I'.$highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);

		$spreadsheet->getActiveSheet()->setCellValue('J'.$highestRow, $total_price_galon_19lt);
		$spreadsheet->getActiveSheet()->getStyle('J'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('J'.$highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);

		$spreadsheet->getActiveSheet()->setCellValue('K'.$highestRow, $total_price_galon_kran);
		$spreadsheet->getActiveSheet()->getStyle('K'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('K'.$highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);

		$spreadsheet->getActiveSheet()->setCellValue('L'.$highestRow, $total_price_botol_330);
		$spreadsheet->getActiveSheet()->getStyle('L'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('L'.$highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);

		$spreadsheet->getActiveSheet()->setCellValue('M'.$highestRow, $total_price_cup_220);
		$spreadsheet->getActiveSheet()->getStyle('M'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('M'.$highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);

		$spreadsheet->getActiveSheet()->setCellValue('N'.$highestRow, $total_harga);
		$spreadsheet->getActiveSheet()->getStyle('N'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('N'.$highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);

		$spreadsheet->getActiveSheet()->setCellValue('O'.$highestRow, $total_debit);
		$spreadsheet->getActiveSheet()->getStyle('O'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('O'.$highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);

		$spreadsheet->getActiveSheet()->setCellValue('P'.$highestRow, $total_piutang);
		$spreadsheet->getActiveSheet()->getStyle('P'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('P'.$highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);

		$spreadsheet->getActiveSheet()->setCellValue('Q'.$highestRow, "-");
		$spreadsheet->getActiveSheet()->getStyle('Q'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('Q'.$highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);


		$date = $post['input_date'];
		$formatted_date_filename = str_replace("_", "-", $date); //date('d-m-Y', strtotime($date));

		// Set the header information for the download
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="Laporan Harian AMDK - Periode '.$formatted_date_filename.'.xlsx"');
		header('Cache-Control: max-age=0');
		
		// Create a writer object and save the spreadsheet to the output
		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');
	}
}
