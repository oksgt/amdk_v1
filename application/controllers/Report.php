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

		$this->load->model(array('Product'));
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
		$spreadsheet->getActiveSheet()->getStyle('A4:A5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
		$spreadsheet->getActiveSheet()->getStyle('A4:A5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('A4:A5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('B4', 'Tanggal');
		$spreadsheet->getActiveSheet()->getStyle('B4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('B4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
		$spreadsheet->getActiveSheet()->mergeCells('B4:B5');
		$spreadsheet->getActiveSheet()->getStyle('B4:B5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
		$spreadsheet->getActiveSheet()->getStyle('B4:B5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('B4:B5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('C4', 'Pembelian');
		$spreadsheet->getActiveSheet()->getStyle('C4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('C4:E4')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
		$spreadsheet->getActiveSheet()->getStyle('C4:E4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('C4:E4')->getFont()->setBold(true);
		$spreadsheet->getActiveSheet()->mergeCells('C4:E4');

		$spreadsheet->getActiveSheet()->setCellValue('C5', 'Perumdam');
		$spreadsheet->getActiveSheet()->getStyle('C5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('C5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
		$spreadsheet->getActiveSheet()->getStyle('C5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('C5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('D5', 'SKPD');
		$spreadsheet->getActiveSheet()->getStyle('D5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('D5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
		$spreadsheet->getActiveSheet()->getStyle('D5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('D5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('E5', 'Pribadi');
		$spreadsheet->getActiveSheet()->getStyle('E5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('E5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
		$spreadsheet->getActiveSheet()->getStyle('E5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('E5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('F4', 'Jumlah Satuan');
		$spreadsheet->getActiveSheet()->getStyle('F4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('F4:I4')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
		$spreadsheet->getActiveSheet()->getStyle('F4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('F4:I4')->getFont()->setBold(true);
		$spreadsheet->getActiveSheet()->mergeCells('F4:I4');

		$spreadsheet->getActiveSheet()->setCellValue('F5', 'Galon 19lt');
		$spreadsheet->getActiveSheet()->getStyle('F5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('F5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
		$spreadsheet->getActiveSheet()->getStyle('F5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('F5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('G5', 'Galon Kran');
		$spreadsheet->getActiveSheet()->getStyle('G5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('G5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
		$spreadsheet->getActiveSheet()->getStyle('G5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('G5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('H5', 'Botol 330ml Dus');
		$spreadsheet->getActiveSheet()->getStyle('H5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('H5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
		$spreadsheet->getActiveSheet()->getStyle('H5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('H5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('I5', 'Cup 220ml Dus');
		$spreadsheet->getActiveSheet()->getStyle('I5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('I5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
		$spreadsheet->getActiveSheet()->getStyle('I5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('I5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('J4', 'Harga Satuan (Rp)');
		$spreadsheet->getActiveSheet()->getStyle('J4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('J4:M4')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
		$spreadsheet->getActiveSheet()->getStyle('J4:M4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('J4:M4')->getFont()->setBold(true);
		$spreadsheet->getActiveSheet()->mergeCells('J4:M4');

		$spreadsheet->getActiveSheet()->setCellValue('J5', 'Galon 19lt');
		$spreadsheet->getActiveSheet()->getStyle('J5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('J5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
		$spreadsheet->getActiveSheet()->getStyle('J5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('J5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('K5', 'Galon Kran');
		$spreadsheet->getActiveSheet()->getStyle('K5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('K5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
		$spreadsheet->getActiveSheet()->getStyle('K5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('K5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('L5', 'Botol 330ml Dus');
		$spreadsheet->getActiveSheet()->getStyle('L5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('L5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
		$spreadsheet->getActiveSheet()->getStyle('L5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('L5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('M5', 'Cup 220ml Dus');
		$spreadsheet->getActiveSheet()->getStyle('M5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('M5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
		$spreadsheet->getActiveSheet()->getStyle('M5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('M5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('N4', 'Total Harga');
		$spreadsheet->getActiveSheet()->getStyle('N4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('N4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
		$spreadsheet->getActiveSheet()->mergeCells('N4:N5');
		$spreadsheet->getActiveSheet()->getStyle('N4:N5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
		$spreadsheet->getActiveSheet()->getStyle('N4:N5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('N4:N5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('O4', 'Keterangan');
		$spreadsheet->getActiveSheet()->getStyle('O4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('O4:P4')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
		$spreadsheet->getActiveSheet()->getStyle('O4:P4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('O4:P4')->getFont()->setBold(true);
		$spreadsheet->getActiveSheet()->mergeCells('O4:P4');

		$spreadsheet->getActiveSheet()->setCellValue('O5', 'Debit');
		$spreadsheet->getActiveSheet()->getStyle('O5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('O5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
		$spreadsheet->getActiveSheet()->getStyle('O5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('O5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('P5', 'Piutang');
		$spreadsheet->getActiveSheet()->getStyle('P5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('P5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
		$spreadsheet->getActiveSheet()->getStyle('P5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('P5')->getFont()->setBold(true);

		$spreadsheet->getActiveSheet()->setCellValue('Q4', 'Total Harga');
		$spreadsheet->getActiveSheet()->getStyle('Q4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('Q4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
		$spreadsheet->getActiveSheet()->mergeCells('Q4:Q5');
		$spreadsheet->getActiveSheet()->getStyle('Q4:Q5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
		$spreadsheet->getActiveSheet()->getStyle('Q4:Q5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffb8cce4');
		$spreadsheet->getActiveSheet()->getStyle('Q4:Q5')->getFont()->setBold(true);

		$alphabet = array();

		// Loop through the range of characters from 'a' to 'q'
		foreach(range('a', 'q') as $char) {
			$spreadsheet->getActiveSheet()->getColumnDimension($char)->setAutoSize(true);
		}

		
		// Set the header information for the download
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="example.xlsx"');
		header('Cache-Control: max-age=0');
		
		// Create a writer object and save the spreadsheet to the output
		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');
	}
}
