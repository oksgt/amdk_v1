<?php

function multidimensional_array_search($search_value,$array) {
  $mached = array();
  if(is_array($array) && count($array) > 0) {
    foreach($array as $key => $value) {
      if(is_array($value) && count($value) > 0) {
        multidimensional_array_search($search_value,$value);
      } else {
        return array_search($search_value,$array); exit;
      }
    }
  }
}

function cari_array($value, $key_search, $array) {
  foreach ($array as $key => $val) {
    if ($val[$key_search] === $value) {
      return $key;
    }
  }
  return null;
}

function bulan_sebelumnya($tahun, $bulan){
  if(intval($bulan) == 1){
    $tahun = strval(intval($tahun)-1);
    $bulan = '12';
    $periode = $tahun.'-'.$bulan;
  } else {
    $tahun = $tahun;
    $bulan = strval(intval($bulan)-1);
    $bulan = (strlen($bulan)==1) ? '0'.$bulan : $bulan ;
    $periode = $tahun.'-'.$bulan;
  }
  return $periode;
}


function rupiah($angka){
  $hasil_rupiah = "" . number_format($angka,0,'.','.');
  return $hasil_rupiah;
}

function formatTglIndo($stringDate){
  $pieces = explode("-", $stringDate);
  $bulan = $pieces[1];
  $namaBulan = bulan($bulan);
  return $pieces[2].' '.$namaBulan.' '.$pieces[0];
}

function formatTglIndo_3($stringDate){
  $pieces = explode("-", $stringDate);
  $bulan = $pieces[1];
  $namaBulan = bulan_1($bulan);
  return $pieces[2].' '.$namaBulan.' '.$pieces[0];
}

function formatTglIndo_2($stringDate){
  if($stringDate!='' || $stringDate!=null){
    if ($stringDate == "0000-00-00") {
      return " ";
    }else{
      $pieces = explode("-", $stringDate);
      $bulan = $pieces[1];
      return $pieces[2].'/'.$bulan.'/'.$pieces[0];
    }
  }else {
    return " ";
  }
}

function formatTglIndo_datetime($stringDate){
  $pieces_raw = explode(" ", $stringDate);
  $pieces = explode("-", $pieces_raw[0]);
  $bulan = $pieces[1];
  return $pieces[2].' '.bulan_1($bulan).' '.$pieces[0] . ' - ' .$pieces_raw[1];
}

function formatTglIndo_datetime_2($stringDate){
  $pieces_raw = explode(" - ", $stringDate);
  $pieces = explode(" ", $pieces_raw[0]);
  $bulan = $pieces[1];
  return $pieces[0].' '.$pieces[1] . ' ' . $pieces[2];
}

function formatTglIndo_datetime_3($stringDate){
  $pieces_raw = explode(" ", $stringDate);
  $pieces = explode("-", $pieces_raw[0]);
  $bulan = $pieces[1];

  $time_raw = explode(":", $pieces_raw[1]);

  return $pieces[2].' '.bulan($bulan).' '.$pieces[0] . ' - ' .$time_raw[0].':'.$time_raw[1];
}

function hari($day){
  switch ($day) {
    case 'Sunday': return 'Minggu';
    break;
    case 'Monday': return 'Senin';
    break;
    case 'Tuesday': return 'Selasa';
    break;
    case 'Wednesday': return 'Rabu';
    break;
    case 'Thursday': return 'Kamis';
    break;
    case 'Friday': return "Jumat";
    break;
    case 'Saturday': return 'Sabtu';
    break;

  }
}

function bulan_1($bulan){

  switch ($bulan) {
    case '01': return 'Januari';
    break;

    case '02': return 'Februari';
    break;

    case '03': return 'Maret';
    break;

    case '04': return 'April';
    break;

    case '05': return 'Mei';
    break;

    case '06': return 'Juni';
    break;

    case '07': return 'Juli';
    break;

    case '08': return 'Agustus';
    break;

    case '09': return 'September';
    break;

    case '10': return 'Oktober';
    break;

    case '11': return 'November';
    break;

    case '12': return 'Desember';
    break;
  }

}

function bulan($bulan){

  switch ($bulan) {
    case '01': return 'Jan';
    break;

    case '02': return 'Feb';
    break;

    case '03': return 'Mar';
    break;

    case '04': return 'Apr';
    break;

    case '05': return 'Mei';
    break;

    case '06': return 'Jun';
    break;

    case '07': return 'Jul';
    break;

    case '08': return 'Aug';
    break;

    case '09': return 'Sept';
    break;

    case '10': return 'Okt';
    break;

    case '11': return 'Nov';
    break;

    case '12': return 'Des';
    break;
  }

}

function Terbilang($x){
  if(is_numeric($x)){
    $ambil = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
    if ($x < 12)
    return " " . $ambil[$x];
    elseif ($x < 20)
    return Terbilang($x - 10) . " belas";
    elseif ($x < 100)
    return Terbilang($x / 10) . " puluh" . Terbilang($x % 10);
    elseif ($x < 200)
    return " seratus" . Terbilang($x - 100);
    elseif ($x < 1000)
    return Terbilang($x / 100) . " ratus" . Terbilang($x % 100);
    elseif ($x < 2000)
    return " seribu" . Terbilang($x - 1000);
    elseif ($x < 1000000)
    return Terbilang($x / 1000) . " ribu" . Terbilang($x % 1000);
    elseif ($x < 1000000000)
    return Terbilang($x / 1000000) . " juta" . Terbilang($x % 1000000);
  }else{
    return "Tidak diketahui";
  }
}

function cmb_dinamis_korporasi($name,$table,$field,$pk,$selected=null, $action=null){
  $ci = get_instance();
  $cmb = "<select id='$name' name='$name' class='form-control-sm form-control input-sm' $action>";
  $ci->db->where('is_del', 0);
  $ci->db->order_by($field, 'ASC');
  $data = $ci->db->get($table)->result();
  $cmb .= "<option value=''>--Pilih--</option>";
  foreach ($data as $d){
    $cmb .="<option value='".$d->$pk."'";
    $cmb .= $selected==$d->$pk?" selected='selected'":'';
    $cmb .=">".  strtoupper($d->$field)."</option>";
  }
  $cmb .="</select>";
  return $cmb;
}

function cmb_dinamis($name,$table,$field,$pk,$selected=null, $action=null){
  $ci = get_instance();
  $cmb = "<select id='$name' name='$name' class='form-control-sm form-control input-sm' $action>";
  $ci->db->order_by($field, 'ASC');
  $data = $ci->db->get($table)->result();
  $cmb .= "<option value='x'>--Pilih--</option>";
  foreach ($data as $d){
    $cmb .="<option value='".$d->$pk."'";
    $cmb .= $selected==$d->$pk?" selected='selected'":'';
    $cmb .=">".  strtoupper($d->$field)."</option>";
  }
  $cmb .="</select>";
  return $cmb;
}

function cmb_dinamis_2($pilih,$name,$table,$field,$order_by,$pk,$where,$selected=null, $action=null){
  $ci = get_instance();
  $cmb = "<select id='$name' name='$name' class='form-control-sm form-control input-sm' $action>";
  $ci->db->order_by($order_by, 'ASC');
  if($where != null){
    $ci->db->where($where);
  }
  $data = $ci->db->get($table)->result();
  $cmb .= "<option value='x'>".$pilih."</option>";
  foreach ($data as $d){
    $cmb .="<option value='".$d->$pk."'";
    $cmb .= $selected==$d->$pk?" selected='selected'":'';
    $cmb .=">". strtoupper($d->$field)."</option>";
  }
  $cmb .="</select>";
  return $cmb;
}

function cmb_dinamis_3($pilih,$name,$table,$field,$order_by,$pk,$where,$selected=null, $action=null){
  $ci = get_instance();
  $cmb = "<select id='$name' name='$name' class='form-control-sm form-control input-sm' $action>";
  $ci->db->order_by($order_by, 'ASC');
  if($where != null){
    $ci->db->where($where);
  }
  $data = $ci->db->get($table)->result();
  $cmb .= "<option value='x'>".$pilih."</option>";
  foreach ($data as $d){
    $cmb .="<option value='".$d->$pk."'";
    $cmb .= $selected==$d->$pk?" selected='selected'":'';
    $cmb .=">". $d->$order_by ." - ". strtoupper($d->$field)."</option>";
  }
  $cmb .="</select>";
  return $cmb;
}

function cmb_dinamis_4($name,$table,$field,$pk,$selected=null, $action=null){
  $ci = get_instance();
  $cmb = "<select id='$name' name='$name' class='form-control-sm form-control input-sm' $action>";
  // $ci->db->order_by($field, 'ASC');
  $data = $ci->db->get($table)->result();
  $cmb .= "<option value='x'>--Pilih--</option>";
  foreach ($data as $d){
    $cmb .="<option value='".$d->$pk."'";
    $cmb .= $selected==$d->$pk?" selected='selected'":'';
    $cmb .=">".  strtoupper($d->$field)."</option>";
  }
  $cmb .="</select>";
  return $cmb;
}

function data_korporasi($id){
  $ci = get_instance();
  $ci->db->where('id', $id);
  $data = $ci->db->get('tbl_klien_korporasi')->row_array();
  return $data;
}


if (!function_exists('nsi_round')) {
  function nsi_round($x) {
    //$x = ceil($x / 100) * 100;
    return $x;
  }
}

function jin_date_ina($date_sql, $tipe = 'full', $time = false) {
  $date = '';
  if($tipe == 'full') {
    $nama_bulan = array(1=>"Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
  } else {
    $nama_bulan = array(1=>"Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Ags", "Sep", "Okt", "Nov", "Des");
  }
  if($time) {
    $exp = explode(' ', $date_sql);
    $exp = explode('-', $exp[0]);
    if(count($exp) == 3) {
      $bln = $exp[1] * 1;
      $date = $exp[2].' '.$nama_bulan[$bln].' '.$exp[0];
    }
    $exp_time = $exp = explode(' ', $date_sql);
    $date .= ' jam ' . substr($exp_time[1], 0, 5);
  } else {
    $exp = explode('-', $date_sql);
    if(count($exp) == 3) {
      $bln = $exp[1] * 1;
      if($bln > 0) {
        $date = $exp[2].' '.$nama_bulan[$bln].' '.$exp[0];
      }
    }
  }
  return $date;
}

function jin_nama_bulan($bln, $tipe = 'full')
{
  $bln = $bln * 1;
  if ($tipe == 'full') {
    $nama_bulan = array(1 => "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
  } else {
    $nama_bulan = array(1 => "Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Ags", "Sep", "Okt", "Nov", "Des");
  }
  return $nama_bulan[$bln];
}

function id(){
  return base_convert(microtime(false), 12, 36);
}

function token(){
  return base_convert(microtime(false), 10, 36);
}

function get_string_between($string, $start, $end){
  $string = ' ' . $string;
  $ini = strpos($string, $start);
  if ($ini == 0) return '';
  $ini += strlen($start);
  $len = strpos($string, $end, $ini) - $ini;
  return substr($string, $ini, $len);
}

function MasaKerja($tgl_masuk,$tahun_sekarang,$bulan_sekarang,$tanggal_sekarang){
  if($tgl_masuk=='0000-00-00'){
    return 0;
  }else{
    $date1 = $tgl_masuk;
    $date2 = $tahun_sekarang.'-'.$bulan_sekarang.'-'.$tanggal_sekarang;

    $ts1 = strtotime($date1);
    $ts2 = strtotime($date2);

    $year1 = date('Y', $ts1);
    $year2 = date('Y', $ts2);

    $month1 = date('m', $ts1);
    $month2 = date('m', $ts2);

    $day1 = date('d', $ts1);
    $day2 = date('d', $ts2);

    $diff = (($year2 - $year1) * 12) + ($month2 - $month1);

    $tahun=round($diff/12);
    if(!is_integer($diff/12)){
      $tahun=$tahun-1;
    }
    if($tahun < 10){
      $tahun='0'.$tahun;
    }
    $sisabulan=$diff % 12;

    if($sisabulan < 10){
      $sisabulan='0'.$sisabulan;
    }
    $data['jumlah_bulan']=$diff;


    $d1 = new DateTime($date1);
    $d2 = new DateTime($date2);

    $diff = $d2->diff($d1);

    $data['masa_kerja']=$diff->y.','.$sisabulan;
    return $data;
  }
}

function sum_the_time($time1, $time2) {
  $times = array($time1, $time2);
  $seconds = 0;
  foreach ($times as $time)
  {
    list($hour,$minute,$second) = explode(':', $time);
    $seconds += $hour*3600;
    $seconds += $minute*60;
    $seconds += $second;
  }
  $hours = floor($seconds/3600);
  $seconds -= $hours*3600;
  $minutes  = floor($seconds/60);
  $seconds -= $minutes*60;
  // return "{$hours}:{$minutes}:{$seconds}";
  return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
}

?>
