<?php

namespace App\Console\Commands;

use App\Models\ColibriReport as Report;
use App\Models\User;
use App\Models\ColibriReportTemplate as ReportTemplate;
use App\Mail\NotifMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Excel;
use Illuminate\Support\Facades\Mail;
use Storage;
//use Maatwebsite\Excel\Excel;

class RunQuery extends Command
{

	protected $signature = 'run:template';


	protected $description = 'Running Query Template';
	private $enc_key = '59b6ab46d379b89d794c87b74a511fbd59b6ab46d379b89d794c87b74a511fbd';
	private $iv      = '0aaff094b6dc29742cc98a4bac8bc8f9';


	public function __construct()
	{
		parent::__construct();
	}

	private function getindex($row, $column = [])
	{
		$s = '';
		foreach ($column as $v) {
			$s .= ':' . $row[$v];
		}
		return md5($s);
	}
	private function __appendCompanyListQuery($temp, $dataInitialTable)
	{
		$query = $temp->templateSql;
		$companyList = '';
		if ($temp->username != 'cadmin') {
			$admin = DB::table('colibri_admin')->where('username', $temp->username)->first();
			if ($admin) {
				$companyList = trim($admin->compCodeList, ',');
				$tmp = explode(',', $companyList);
				$companyList = "'" . implode("','", $tmp) . "'";
			}
		}

		if (in_array('EM', $dataInitialTable)) {
			if (!empty($companyList)) {
				if (strpos($query, 'WHERE') !== false) {
					if (strpos($query, 'ORDER BY') !== false) {
						$query = str_replace('ORDER BY', " AND EM.compCode IN (" . $companyList . ") ORDER BY ", $query);
					} else {
						$query .= " AND EM.compCode IN (" . $companyList . ")";
					}
				} else {
					if (strpos($query, 'ORDER BY') !== false) {
						$query = str_replace('ORDER BY', " WHERE EM.compCode IN (" . $companyList . ") ORDER BY ", $query);
					} else {
						$query .= " WHERE EM.compCode IN (" . $companyList . ")";
					}
				}
			}
		}
		echo "<pre>Company List (" . $companyList . ")</pre>";
		return $query;
	}
	private function __externalWhereQuery($ext, $dataInitialTable, $rows)
	{
		$conditions = '';
		foreach ($ext->foreign_key as $k => $v) {
			if (in_array($ext->foreign_initial[$k], $dataInitialTable)) {
				$arr_foreign_value = array_column($rows, $v);
				$foreign_value = '"' . implode('","', array_unique($arr_foreign_value)) . '"';
				if ($k > 0) {
					$conditions .= " AND ";
				}
				$conditions .= "" . $ext->local_key[$k] . " IN (" . $foreign_value . ")";
				$group_by[] = $ext->local_key[$k];
			}
		}
		return ($conditions != '') ? ' WHERE ' . $conditions : '';
	}
	private function __getHeaderKey($row)
	{
		$header_key = [];
		foreach ($row as $key => $value) {
			$header_key[$key] = $key;
		}
		return $header_key;
	}
	private function __processExtraColumn($rows, $header_key)
	{

		$body = [];
		foreach ($rows as $row) {
			$row = (array)$row;
			$body_row = [];
			$c = 0;
			foreach ($header_key as $k => $v) {
				if (is_array($v)) {
					foreach ($v as $k2 => $v2) {
						if (isset($row[$k][$k2][0]) and $row[$k][$k2][0] == '0' and is_numeric($row[$k][$k2])) {
							$row[$k][$k2] = '="' . $row[$k][$k2] . '"';
						}
						$body_row[$k . ': ' . $k2] = isset($row[$k][$k2]) ? $row[$k][$k2] : '';
					}
				} else {
					if (isset($row[$k][0]) and $row[$k][0] == '0' and is_numeric($row[$k]) and ((int)$row[$k]) != 0) {
						$row[$k] = '="' . $row[$k] . '"';
					}
					$body_row[$k] = isset($row[$k]) ? $row[$k] : '';
				}
			}
			$body[] = $body_row;
		}
		return $body;
	}
	private function __appendExtraColumn($template, &$header_extra, $rows)
	{

		$external = json_decode($template->templateSqlExtra);
		if ($external) {
			$header_extra = [];
			foreach ($external as $ext) {

				$conditions_query = $this->__externalWhereQuery($ext, $dataInitialTable, $rows);
				if (empty($conditions_query)) {
					break;
				}

				$group_arr = [$ext->column_key];
				$group_query = implode(',', $group_arr);
				$group_query = ($group_query != '') ? $group_query . ',' : '';

				$query_extra = str_replace(['{where}', '{group}'], [$conditions_query, $group_query], $ext->query);

				$rows_extra_raw = DB::select($query_extra);
				$rows_extra = [];
				foreach ($rows_extra_raw as $res) {
					$res = (array)$res;
					$rows_extra[$this->getIndex($res, $ext->local_key)][$res[$ext->column_key]] = $res[$ext->value_key];
					if (!isset($header_extra[$ext->value_key_label][$res[$ext->column_key]])) {
						$header_extra[$ext->value_key_label][$res[$ext->column_key]] = $res[$ext->value_key];
					}
				}
				$old_rows = $rows;
				$rows = [];
				foreach ($old_rows as $row) {
					$row = (array)$row;
					if (isset($rows_extra[$this->getIndex($row, $ext->foreign_key)])) {
						$row[$ext->value_key_label] = $rows_extra[$this->getIndex($row, $ext->foreign_key)];
						$rows[] = $row;
					} else {
						$row[$ext->value_key_label] = [];
					}
				}
			}
		}
		return  $rows;
	}
	private function __formatSize($bytes)
	{
		if ($bytes >= 1073741824) {
			$bytes = number_format($bytes / 1073741824, 0) . ' GB';
		} elseif ($bytes >= 1048576) {
			$bytes = number_format($bytes / 1048576, 0) . ' MB';
		} elseif ($bytes >= 1024) {
			$bytes = number_format($bytes / 1024, 0) . ' KB';
		} else {
			$bytes = $bytes . ' bytes';
		}

		return $bytes;
	}

	private function __formatLength($bytes)
	{
		if ($bytes >= 1000000000) {
			$bytes = number_format($bytes / 1000000000, 0) . ' T';
		} elseif ($bytes >= 1000000) {
			$bytes = number_format($bytes / 1000000, 0) . ' M';
		} elseif ($bytes >= 1000) {
			$bytes = number_format($bytes / 1000, 0) . ' K';
		} else {
			$bytes = $bytes;
		}

		return $bytes;
	}
	public function encrypt_it($plain_text, $custom_enc_key = null, $custom_iv = null)
	{
		$custom_enc_key = is_null($custom_enc_key) ? $this->enc_key : $custom_enc_key;
		$custom_iv      = is_null($custom_iv) ? $this->iv : $custom_iv;

		$e = openssl_encrypt($plain_text, "AES-256-CBC", hex2bin($custom_enc_key), 0, hex2bin($custom_iv));
		return bin2hex(base64_decode($e));
	}
	public function handle(Excel $excel)
	{
		echo "<pre>break param</pre>";
		ini_set('memory_limit', '1024M');
		set_time_limit(0);

		$templates = ReportTemplate::whereIn('templateStatus', ['saved'])->orWhereRaw("(templateStatus='processing' AND last_executed < DATE_SUB(NOW(), INTERVAL 2 HOUR))")->get();
		echo "<pre>All waiting (" . count($templates) . ")</pre>";
		// die;
		if ($templates) {
			foreach ($templates as $temp) {
				echo "<pre>Processing waiting (" . $temp->templateName . ")</pre>";
				ReportTemplate::where('templateId', $temp->templateId)->update(['templateStatus' => 'processing']);

				// $wheres = json_decode($temp->templateField,true);
				// $dataInitialTable=array_column($wheres, 'dataInitialTable');

				// $query = $this->__appendCompanyListQuery($temp,$dataInitialTable);

				// if(empty($query)){
				// 	continue;
				// }
				$template = ReportTemplate::find($temp->templateId);

				if (file_exists(storage_path('exports/' . $template->filename))) {
					echo "<pre>old File exists, deleting... </pre>";
					@unlink(storage_path('exports/' . $template->filename));
				}
				try {
					/*
					$rows=[];
					$inc=0;
					$limit=request()->get('limit');
					$break=(int)request()->get('break');
					$limit=(int)$limit>0?$limit:500000;
					$limit=500000;
					$i=0;
					$totalrows = 0;
					if($limit && (int)$limit>0){
						$write_header=true;
						$filename =preg_replace("/[^\\w]/", '_', $template->templateName) .'-'. $this->generateCode(16);
						@mkdir(storage_path('exports'));
						$f = fopen(storage_path('exports/'.$filename.'.csv'), 'w');
						fputs($f,"sep=,\n");
						while(true){
							$limitquery = $query;
							$hasLimit = true;
							if (!strpos($query,'LIMIT')) {
								$limitquery = $query." LIMIT ".$limit.' OFFSET '.$inc;
								$hasLimit = false;
							}
							
    					 	echo "<pre>".$limitquery."</pre>";
							$rows = DB::select($limitquery);
							if(!$rows || count($rows)==0){
								break;
							}
							$totalrows+=count($rows);
							echo "<pre>Query Done , result=".count($rows)."</pre>";
							$header_extra = [];
							$header_key = $this->__getHeaderKey($rows[0]);
							$rows = $this->__appendExtraColumn($template,$header_extra,$rows);
							$header_key = array_merge($header_key, $header_extra);
							$rows = $this->__processExtraColumn($rows,$header_key);
							
							if($write_header){

								echo "<pre> Header Key";
								print_r($header_key);
								echo "</pre>";
								echo "<pre>Header write</pre>";
								fputcsv($f, array_keys($rows[0]),',');
								$write_header=false;
							}
							foreach ($rows as $col) {
								fputcsv($f, $col,',');
							}
							echo "<pre>Put CSV finish</pre>";
							//die;
							$i++;
							$inc+=$limit;
							if($break>0 && $break==$i){
								break;
							}
							if ($hasLimit) {
								break;
							}
						}

						fclose($f);
					}else{
						$rows = DB::select($query);

						if(count($rows)==0){
							continue;
						}
						$totalrows+=count($rows);
					}	
					*/

					$filename = preg_replace("/[^\\w]/", '_', $template->templateName) . '-' . $this->generateCode(16);
					$this->report_dump($temp->templateId, $filename);

					// $template->templateStatus = 'ready';
					$template->last_executed = date('Y-m-d H:i:s');
					$template->templateFile  = $filename . '.csv';
					// $template->info  = $this->__formatLength($totalrows)." rows - ".$this->__formatSize(filesize(storage_path('/exports/'.$filename.'.csv'))).' file size'; 
					$template->save();

					// $template = ReportTemplate::find($temp->templateId);
					/*
					$template = DB::select("
						SELECT templateId, templateName, crt.username, mu.id as userId, templateFile, requestDate  FROM colibri_report_template crt
						INNER JOIN mdl_user mu ON mu.username=crt.username
						WHERE templateId = ?
					",[$temp->templateId]); 
					$enc_template = $this->encrypt_it(json_encode($template[0]));
					$url = env("DAVIS_API_URL"). "download/". $enc_template;
					echo "<pre>$enc_template</pre>";

					$curl = curl_init($url);
					curl_setopt($curl, CURLOPT_URL, $url);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

					//for debug only!
					curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

					$resp = curl_exec($curl);
					curl_close($curl); 
					*/



					//Storage::put('export', base_path().$template2->templateFile);
				} catch (\Illuminate\Database\QueryException $ex) {
					// echo "here"; die;
					echo $ex->getMessage();
					$template->templateStatus = 'failed';
					$template->last_executed = date('Y-m-d H:i:s');
					$template->info = '';
					$template->save();
				} catch (Exception $ex) {
					echo $ex->getMessage();
					$template->templateStatus = 'failed';
					$template->last_executed = date('Y-m-d H:i:s');
					$template->info = '';
					$template->save();
				}

				$user = User::where('username', $temp->templateUserName)->first();
				if ($user) {
					Mail::to($user->email)->send(new NotifMail($temp));
				}
			}


			echo "SUCCESS";
		}
	}

	function whereCondition($operator, $value)
	{
		if ($operator == 'in') {
			$operator = 'IN';
			$explode_val = explode(',', $value);
			$new_val = [];
			foreach ($explode_val as $val) {
				$new_val[] = "'" . $val . "'";
			}
			$new_val = implode(',', $new_val);
			$value = '(' . $new_val . ')';
			//$value    = "'%$value%'";
		} else if ($operator == 'contain') {
			$operator = 'LIKE';
			$value    = "'%$value%'";
		} else if ($operator == 'not contain') {
			$operator = 'NOT LIKE';
			$value    = "'%$value%'";
		} else if ($operator == 'begin with') {
			$operator = 'LIKE';
			$value    = "'$value%'";
		} else if ($operator == 'end with') {
			$operator = 'LIKE';
			$value    = "'%$value'";
		} else {
			$operator = $operator;
			$value = $value;
		}

		return ['operator' => $operator, 'value' => $value];
	}

	function generateCode($limit)
	{
		$code = '';
		for ($i = 0; $i < $limit; $i++) {
			$code .= mt_rand(0, 9);
		}
		return $code;
	}

	public function report_dump($id, $filename)
	{
		$db_host = env("DB_HOST");
		$db_user = env("DB_USERNAME");
		$db_pass = env("DB_PASSWORD");
		$db_name = env("DB_DATABASE");
		$davis_url = env("DAVIS_API_URL");

		$report_template = DB::select("
            SELECT templateId, templateName, crt.username, mu.id as userId, templateFile, requestDate, templateField, templateSql 
			FROM colibri_report_template crt
            INNER JOIN mdl_user mu ON mu.username=crt.username
            WHERE templateId = ?
        ", [$id]);
		$report_template = $report_template[0];

		$templateField = json_decode($report_template->templateField);
		$tableName = 'colibri_' . $report_template->userId . '_' . $report_template->templateId . '_v' . time();
		$queryDrop = "DROP TABLE IF EXISTS $tableName;";
		$query = "CREATE TABLE $tableName (";
		$field = '';
		$fields = [];
		foreach ($templateField as $key => $value) {
			if ($value->dataField != 'show_active') {
				$field .= strtolower(preg_replace("/[^\\w]/", '_', $value->dataLabel ?? $value->dataField)) . ' ';
				$fields[] = strtolower(preg_replace("/[^\\w]/", '_', $value->dataLabel ?? $value->dataField));
				$datatype = $value->dataType ?? 'string';
				switch ($datatype) {
					case 'string':
						$field .= 'longtext, ';
						break;
					case 'number':
						$field .= 'int, ';
						break;
					case 'datetime':
						$field .= 'datetime, ';
						break;
					default:
						break;
				}
			}
		}
		$query .= rtrim($field, ', ');
		$query .= ");";
		$queryInsert = "INSERT INTO $tableName (" . rtrim(implode(',', $fields), ',') . ")" . $report_template->templateSql . ';';
		$newFields = [];
		foreach ($fields as $value) {
			$newFields[] = "COALESCE($value,'')";
		}
		$fieldList = '\'"\',' . rtrim(implode(',\'","\',', $newFields), ',') . ',\'"\'';

		
		$insertSql = "insert" . $report_template->templateId . ".sql";
		$myfile = fopen(storage_path("sh/" . $insertSql), "w") or die("Unable to open file!");
		fwrite($myfile, $queryDrop);
		fwrite($myfile, $query);
		fwrite($myfile, $queryInsert);
		fclose($myfile);

		$dump_csv = "SELECT GROUP_CONCAT(`COLUMN_NAME`) as 'sep=,'
		FROM `INFORMATION_SCHEMA`.`COLUMNS` 
		WHERE `TABLE_SCHEMA`='$db_name' AND `TABLE_NAME`='$tableName'
		union all
		select CONCAT($fieldList) as 'sep=,' from $tableName;";

		$dumpCsvSql = "dumpcsv" . $report_template->templateId . ".sql";
		$file_dump_csv = fopen(storage_path("sh/" . $dumpCsvSql), "w") or die("Unable to open file!");
		fwrite($file_dump_csv, $dump_csv);
		fclose($file_dump_csv);

		$csvName = $filename;

		unset($report_template->templateField);
		unset($report_template->templateSql);

		$report_template->tableName = $tableName;
		$report_template->link_file = url('public/uploads/' . $report_template->templateId . '.tar.gz');

		$enc_template = $this->encrypt_it(json_encode($report_template));
		
		$davis_restore_url = $davis_url . '/restore/' . $report_template->templateId;

		$path = public_path("uploads");
		$path_sh = storage_path("sh");
		$base_url = url('/');
		$bash = "cd $path_sh; ./colibriimport.sh -u $db_user -p'$db_pass' -h $db_host -d $db_name -b $tableName -t {$report_template->templateId} -c '$insertSql' -a '$path' -l '$davis_restore_url' -v $enc_template -s $dumpCsvSql -n '$csvName' -r '$base_url' &";
		exec($bash);

		return $bash;
	}
}
