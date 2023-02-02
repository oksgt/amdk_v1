<!-- <?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Orders extends CI_Controller {


	public function index()
	{
		$this->template->load('template', 'view_order');
	}

    public function generateCert(){
    	$limit = 200;
    	if(isset($_GET['limit']) && $_GET['limit'] > 0){
    		$limit = $_GET['limit'];
    	}

        if(!file_exists('public/uploads/certificate_template/certificate-default.jpg')){
            @copy('public/images/certificate-default.jpg', 'public/uploads/certificate_template/certificate-default.jpg');
        }
        if(!file_exists('public/uploads/certificate_thumbnail/certificate-thumbnail-default.png')){
            @copy('public/images/certificate-thumbnail-default.png', 'public/uploads/certificate_thumbnail/certificate-thumbnail-default.png');
        }
        echo "Executing cert generator".PHP_EOL;
        $selectedEmpCode = (!empty($_GET['empCode'])?$_GET['empCode']:'all');

        if($selectedEmpCode != 'all'){

            $dash_summary_completed = DB::select("
                select DISTINCT  SubCategoryId, empCode, vnip from colibri_dashboard_admin_employee_summary where completionStatus = 'completed' 
                and empCode = ?  and show_in_dashboard = 1
            ", [$_GET['empCode']]);

            // dump($dash_summary_completed); die;

            if($dash_summary_completed){
                foreach ($dash_summary_completed as $key => $value) {
                    // echo $value->vnip;
                    // die;
                    $file_cert = DB::select("
                        SELECT
                        id,filename
                        FROM `colibri_employee_certificate`
                        WHERE empCode = ? and subCategoryId = ? and virtual_nip = ?
                    ",[$selectedEmpCode, $value->SubCategoryId, $value->vnip ]);

                    if(count($file_cert) > 0){
                        // dump($file_cert);
                        // foreach ($file_cert as $key => $value) {
                        //     #File::delete($value->filename);
                        //     @unlink($value->filename);
                        //     DB::delete("
                        //         DELETE FROM colibri_employee_certificate WHERE id = ?
                        //     ",[$value->id]);
                        // }	
                    }
                }
            }

            // die;
        }

        $debug = (!empty($_GET['debug']))?true:false;
        set_time_limit(0);
        $completed = DB::table('colibri_employee_certificate')
        // ->select(DB::raw("CONCAT(empCode,'-',subCategoryId) as c")) 
        ->select(DB::raw("CONCAT(virtual_nip,'-',subCategoryId) as c"))
        ->where('expiryDate','<',date('Y-m-d'))
        ->get()->toArray();
        $in = array_column($completed,'c');

        // dump($completed);

        if($debug){;
            echo PHP_EOL."Data completed user :".PHP_EOL;
            print_r($in);
        }

        #sudah ada antrian?
        $antrian = DB::select("
            SELECT
			  COUNT(*) AS 'total'
			FROM `colibri_certificate_cron`
			WHERE tanggal = CURRENT_DATE()
        ");

        if($antrian[0]->total == '0'){
        	#masukkan tabel antrian 
	        DB::insert("
	            INSERT IGNORE INTO `colibri_certificate_cron` (`tanggal`, `empCode`, `is_exec`) 
				SELECT 
				  CURRENT_DATE(),
				  empCode,
				  '0' 
				FROM
				  colibri_employee 
	        ");	
        }         

        if($selectedEmpCode != 'all'){
        	$emp_array = DB::table('colibri_employee')->where('empCode',$selectedEmpCode)->get()->pluck('empCode')->toArray();
        }else{
        	$emp_array = DB::table('colibri_certificate_cron')->where('is_exec','0')->where('tanggal',date('Y-m-d'))->skip(0)->take($limit)->get()->pluck('empCode')->toArray();
            // echo 'all';
        }

        // dump($emp_array);
        //     die;
        
        $emp_s = array_chunk($emp_array, 100);
        

        foreach ($emp_s as $emp_a) {   
        
            $data_completion = DB::table(DB::raw('colibri_dashboard_admin_employee_summary A'))
                                // ->join(DB::raw('colibri_course_category B'),'B.courseId','=','A.courseId')
                                ->join(DB::raw('colibri_employee ce'),'ce.empCode','=','A.empCode')
                                ->where('completionStatus','completed')
                                ->where('show_in_dashboard', 1)
                                ->whereIn('A.empCode',$emp_a)
                                // ->whereNotIn(DB::raw("CONCAT(A.vnip,'-',B.categoryId)"),$in)
                                ->get()->toArray();
                                //echo vsprintf(str_replace(['?'], ['\'%s\''], $data_completion->toSql()), $data_completion->getBindings());
                                //exit();
                                //->get()->toArray();
            if($debug){
                echo PHP_EOL."Emp Code :".$selectedEmpCode.PHP_EOL;
                echo PHP_EOL."Data completion user :".PHP_EOL;
                print_r($data_completion);
            }

            // dump($data_completion); die;
            
            
            // $categoryId = array_column($data_completion, 'categoryId');
            $categoryId = array_column($data_completion, 'SubCategoryId');
            $data_sub = DB::table('colibri_course_category')
                ->select('colibri_course_category.categoryId',DB::raw('COUNT(colibri_course_category.courseId) as total'))
                ->join('mdl_course','mdl_course.id','=','colibri_course_category.courseId')
                ->whereIn('colibri_course_category.categoryId',$categoryId)
                ->groupBy('colibri_course_category.categoryId')->get();
            // print_r($data_sub);
            $sub_course_total = [];
            foreach ($data_sub as $row) {
                $sub_course_total[$row->categoryId]=$row->total;
            }
            // dump($sub_course_total); die;
            if($debug){;
                echo PHP_EOL."Sub course total :".PHP_EOL;
                print_r($sub_course_total);
            }
            $emp_score = [];
            $emp_course = [];
            $completionDate = [];
            foreach ($data_completion as $row) {
                // if(isset($emp_course[$row->empCode][$row->categoryId])){
                //     $emp_course[$row->empCode][$row->categoryId]++;
                // }else{
                //     $emp_course[$row->empCode][$row->categoryId]=1;
                // }
                // if(isset($emp_score[$row->empCode][$row->categoryId])){
                //     $emp_score[$row->empCode][$row->categoryId]=$emp_score[$row->empCode][$row->categoryId]+$row->grade;
                // }else{
                //     $emp_score[$row->empCode][$row->categoryId]=$row->grade;
                // }

                // $completionDate[$row->empCode][$row->categoryId] = $row->completionDate;
                if(isset($emp_course[$row->empCode."_".$row->empId][$row->SubCategoryId])){
                    $emp_course[$row->empCode."_".$row->empId][$row->SubCategoryId]++;
                }else{
                    $emp_course[$row->empCode."_".$row->empId][$row->SubCategoryId]=1;
                }

                if(isset($emp_score[$row->empCode][$row->SubCategoryId])){
                    $emp_score[$row->empCode][$row->SubCategoryId]=
                    $emp_score[$row->empCode][$row->SubCategoryId]+$row->grade;
                }else{
                    $emp_score[$row->empCode][$row->SubCategoryId]=$row->grade;
                }

                $completionDate[$row->empCode][$row->SubCategoryId] = $row->completionDate;
            }
            if($debug){
                echo PHP_EOL."Score:".PHP_EOL;
                print_r($emp_score);
                echo PHP_EOL."Course:".PHP_EOL;
                print_r($emp_course);
            }

            // dump($emp_course);
            // die;

            $process=[];
            $categoryIdList = [];
            $empCodeList = [];
            
            

            foreach ($emp_course as $empCode => $category) {
                foreach ($category as $categoryId => $c) {
                    // echo "ss ".$empCode;
                    if($c == $sub_course_total[$categoryId]){
                        $empCode_arr = explode("_", $empCode);
                        $process[] = [
                            'empId' => $empCode_arr[1],
                            'empCode'=> $empCode_arr[0],
                            'vnip'=> $empCode,
                            'categoryId'=>$categoryId
                        ];
                        $categoryIdList[]=$categoryId;
                        $empCodeList[] = $empCode_arr[0];
                    }
                }
            }
            if($debug){
                echo PHP_EOL."process:".PHP_EOL;
                print_r($process);
            }

            // dump($process);
            // die;

            $cert_data = DB::table('colibri_certificate')->whereIn('ownerId',$categoryIdList)->where('certEnabled',1)
            ->get();
            $category_data = DB::table('colibri_category')->whereIn('id',$categoryIdList)->get();
            $employee_data = DB::table('colibri_employee')->whereIn('empCode', $empCodeList)->get();
            // echo $categoryIdList;
            // dump($categoryIdList); die;

            $companyList = array_column($employee_data->toArray(), 'compCode');
            $company_data = DB::table('colibri_company')->whereIn('compCode',$companyList)->get();
            $company = [];
            $employee = [];
            $cert = [];
            $category=[];
            foreach ($category_data as $row) {
                $category[$row->id]=$row;
            }
            echo PHP_EOL."category_data ".PHP_EOL;
            foreach ($cert_data as $row) {
                if(!is_object($row->certProperties)){
                    $row->certProperties=json_decode($row->certProperties);
                    if(!$row->certProperties){
                        $row->certProperties = [];
                    }
                }
                $cert[$row->ownerId]=$row;
            }
            echo PHP_EOL."cert_data ".PHP_EOL;
            
            foreach ($company_data as $row) {
                $company[$row->compCode]=$row;
            }
            foreach ($employee_data as $row) {
                // $employee[$row->empCode]=$row;   
                $employee[$row->empId]=$row;   
            }

            // echo "<pre>";
            // dump($emp_course); 
            // dump($employee['138494']); 
            // die;
            // dump($cert); 

            foreach ($process as $row) {
                if($debug){
                    echo PHP_EOL."Executing ".PHP_EOL;
                }
                $empCode = $row['empCode'];
                $program = $row['categoryId'];

                $empId = $row['empId'];

                // dump($employee["138501adm"]); 
                // echo "xx ". isset($cert[$program]);
                // die;
                // echo $row['categoryId']." | ";
                // die;
                if(!empty($cert[$program])){
                    // echo $empCode ."<br>";
                    $certTmp = $cert[$program];
                    $emp = $employee[$empId];
                    $comp = $company[$emp->compCode];
                    $empScore = $emp_score[$empCode][$program]/$emp_course[$empCode."_".$empId][$program];
                    $empScore = round($empScore,1);
                    if(!json_decode($comp->compStyle)){
                        $companyThumbnail = url('public/images/company-default.jpg');
                    }else{
                        $compStyle = json_decode($comp->compStyle);
                        if($compStyle->company_thumbnail != '' && file_exists(base_path().'/public/images/company/'.$compStyle->company_thumbnail)){
                            $type = pathinfo(base_path().'/public/images/company/'.$compStyle->company_thumbnail, PATHINFO_EXTENSION);
                            $data = \File::get(base_path().'/public/images/company/'.$compStyle->company_thumbnail);
                            $base64 = "";
                            if ($type == "svg") {
                                $companyThumbnail = "data:image/svg+xml;base64,".base64_encode($data);
                            } else {
                                $companyThumbnail = "data:image/". $type .";base64,".base64_encode($data);
                            }
                            //$companyThumbnail=$base64;
                            //$companyThumbnail = url('public/images/company/'.$compStyle->company_thumbnail);
                        }else{
                            $companyThumbnail = url('public/images/company-default.jpg');
                        }
                    }

                    //$publishDate=date('d.m.Y');
                    $publishDate=date('d.m.Y',strtotime(
                    	$completionDate[$empCode][$program] ?? date('Y-m-d')
                    ));

                    if($certTmp->expirationTime == '0'){
                    	$expiryTime=strtotime("+1200 month",strtotime(
	                    	$completionDate[$empCode][$program] ?? date('Y-m-d')
	                    ));
                    }else{
                    	$expiryTime=strtotime("+".$certTmp->expirationTime." month",strtotime(
	                    	$completionDate[$empCode][$program] ?? date('Y-m-d')
	                    ));	
                    }
                    
                    $expiryDate=date("d.m.Y",$expiryTime);

                    $exist = DB::select("
                        SELECT * FROM colibri_employee_certificate where empCode =? AND expiryDate=? AND  subCategoryId=?
						",[
						$empCode,date('Y-m-d',$expiryTime),$program
					]);

                    if(!isset($exist[0])){
                    	$cert_no = DB::table('colibri_employee_certificate')->insertGetId([
	                        'empCode'=>$empCode,
	                        #'publishDate'=>date('Y-m-d'),
	                        'publishDate'=>$completionDate[$empCode][$program]??date('Y-m-d'),
	                        'filename'=>'',
	                        'certId'=>$certTmp->certId,
	                        'expiryDate'=>date('Y-m-d',$expiryTime),
	                        'subCategoryId'=>$program,
	                        'subCategoryName'=>$category[$program]->name,
	                    ]);
                        $cert_no = (string) $cert_no;
	                    echo PHP_EOL."insert cert_no ".$cert_no.PHP_EOL;
	                    $qr = QrCode::format('png')
	                            ->margin(1)
	                            ->size(500)
	                            ->generate($cert_no);
	                    echo PHP_EOL."generate QR ".$cert_no.PHP_EOL;

	                    $qr_code='data:image/png;base64,'.base64_encode($qr);
	                    
	                    $certTmp->certProp=[
	                        'cert_name'=>['type'=>'text','value'=>$category[$program]->name],
	                        'empName'=>['type'=>'text','value'=>$emp->empName],
	                        'empCode'=>['type'=>'text','value'=>$emp->empCode],
	                        'publishDate'=>['type'=>'text','value'=>$publishDate],
	                        'expiryDate'=>['type'=>'text','value'=>$expiryDate],
	                        'score'=>['type'=>'text','value'=>$empScore],
	                        'cert_no'=>['type'=>'text','value'=>$cert_no],
	                        'company_thumbnail'=>['type'=>'object','value'=>$companyThumbnail],
	                        'qr_code'=>['type'=>'object','value'=>$qr_code],
	                    ];
	                    $paperSize=[
	                        'a4'=>[
	                            'height'=>595,
	                            'width'=>842,
	                        ],
	                        'folio'=>[
	                            'height'=>595.4,
	                            'width'=>935.5
	                        ]
	                    ];
	                    
	                    $pdf = PDF::loadView('certTemplate', ['data'=>$certTmp,'paperSize'=>$paperSize[$certTmp->certPaperSize]])->setPaper([0,0,$paperSize[$certTmp->certPaperSize]['height'],$paperSize[$certTmp->certPaperSize]['width']], 'landscape');
	                    echo PHP_EOL."generate PDF ".$cert_no.PHP_EOL;
                        

                        $enc_compCode = md5($emp->compCode);
                        @mkdir(base_path().'/public/uploads/employee_certificate/'.$enc_compCode);
	                    $nama_program = str_replace('/', '-', $category[$program]->name);
	                    
	                    $filename='public/uploads/employee_certificate/'.$enc_compCode.'/Certificate-'.$emp->empCode.'-'.$nama_program.'.pdf';
	                    $path=base_path().'/'.$filename;
	                    
	                    file_put_contents($path, $pdf->output());
	                    echo PHP_EOL."save PDF ".$cert_no.PHP_EOL;
	                    DB::table('colibri_employee_certificate')->where('id',$cert_no)->update(['filename'=>$filename]);
	                    //return $pdf->download('cert.pdf');
	                    echo "Cert Printed for: ".$emp->empCode.PHP_EOL;
	                    echo "Cert Sub: ".$category[$program]->name.PHP_EOL;
	                    echo "File: ".$filename.PHP_EOL;
	                    echo "----------".PHP_EOL;
                    }

                    
                }
            }
            $list_employee = implode("','",$emp_a);
            $list_employee = "'".$list_employee."'";
            DB::update("
                UPDATE `colibri_certificate_cron`
				SET is_exec = '1',tanggal_exec = NOW()
				where tanggal = CURRENT_DATE()
				AND empCode IN (".$list_employee.")
            ");
        }

        $result = DB::select("
            SELECT COUNT(*) as total FROM colibri_certificate_cron where tanggal = CURRENT_DATE()
            AND is_exec = '0';
        ");

        echo "DONE, sisa ".$result[0]->total.PHP_EOL;

    }



	// public function import_learning_history(Request $req, Route $route)
    // {
    //     $validator = Validator::make($req->all(), [
    //         'data'   => 'required'
    //     ]);

    //     if ($validator->fails())
    //     {
    //         $this->res_obj->message = $validator->messages()->all();
    //         Controller::update_log(['result' => json_encode($this->res_obj)]);
    //         return $this->res_obj->fail();
    //     }        
       
    //     $file = $req->data;
    //     $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathName());
    //     // $writer = new Xlsx($spreadsheet);
    //     $worksheet = $spreadsheet->getSheet(0);
    //     $highestRow = $worksheet->getHighestRow(); // e.g. 10
    //     $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
    //     $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 5
    //     $list = [];$err = []; $succes = 0; $course = 0; $fail = 0;

    //     // ambil data program 
    //     $program_data = DB::select("
    //         SELECT subCategoryId,subCategoryName FROM `v_course_category`
    //     ");
    //     $program = array();
    //     if(count($program_data) > 0){
    //         foreach ($program_data as $key => $value) {
    //             $program[$value->subCategoryName] = $value->subCategoryId;
    //         }
    //     }    
        
    //     // ambil data BU 
    //     $bu_data = DB::select("
    //         select compCode, compName from colibri_company cc 
    //     ");
    //     $bisnis_unit = array();
    //     if(count($bu_data) > 0){
    //         foreach ($bu_data as $key => $value) {
    //             $bisnis_unit[$value->compName] = $value->compCode;
    //         }
    //     }    

    //     // ambil data course
    //     $course_data = $result = DB::select("
    //         SELECT idnumber,subCategoryName FROM v_course vc
    //         join `v_course_category` vcc ON vcc.courseId = vc.id
    //     ");

    //     $course_list = array();
    //     if(count($course_data) > 0){
    //         foreach ($course_data as $key => $value) {
    //             $course_list[$value->subCategoryName][$value->idnumber] = $value->idnumber;
    //         }
    //     }  

    //     // ambil data employee
    //     $employee_data = DB::select("
    //         SELECT empCode FROM colibri_employee
    //     ");
        
    //     $employee = array();
    //     if(count($employee_data) > 0){
    //         foreach ($employee_data as $key => $value) {
    //             $employee[$value->empCode] = $value->empCode;
    //         }
    //     }   

        
    //     $api = new \StdClass;

    //     $master_emp_problem = [];
    //     $master_program_problem = [];       
    //     $program_xls = [];
    //     $tanggal_salah = [];

    //     for ($row = 2; $row <= $highestRow; ++$row){
    //         if(is_numeric($worksheet->getCellByColumnAndRow(1, $row)->getValue())){

    //             $api->empCode = $worksheet->getCellByColumnAndRow(1, $row)->getValue() ; //ambil value nip
    //             $api->empCode = trim($api->empCode);

    //             $api->program = $worksheet->getCellByColumnAndRow(4, $row)->getValue(); // ambil value program
    //             $api->program = trim($api->program);
                
    //             if(!isset($employee[$api->empCode])){
    //                 $master_emp_problem[$api->empCode] = 'not found';
    //             }            
                
    //             if(!isset($program[$api->program])){
    //                 $master_program_problem[$api->program] = 'not found'; 
    //             }              

    //             $program_xls[$row][$api->program] = $api->program;                                
    //         }



    //         $completionDate = FomartDateString($worksheet->getCellByColumnAndRow(2, $row)->getFormattedValue()) ;

    //         // echo $completionDate."<br>";

    //         if($completionDate == '1970-01-01'){
    //             $completionDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date);
    //         }

    //         if($completionDate == '1970-01-01 00:00:00'){
    //             $tanggal_salah['row_'.$row] = $worksheet->getCellByColumnAndRow(2, $row)->getFormattedValue();
    //         }
    //     }

    //     // die;

    //     #header
    //     $header = array();
    //     $master_course_problem = [];
        
    //     #set header
    //     $i = 7;$j=8;
    //     while (
    //         $worksheet->getCellByColumnAndRow($i, 1)->getValue() != '' &&
    //         $worksheet->getCellByColumnAndRow($i, 1)->getValue() != '-' 
    //     ) {
    //         $header[$j] = $worksheet->getCellByColumnAndRow($j, 1)->getValue();
    //         $i+=2;
    //         $j+=2;
    //     } 

    //     #cek master course
    //     for ($row = 2; $row <= $highestRow; ++$row){
    //     	if(is_numeric($worksheet->getCellByColumnAndRow(1, $row)->getValue())){
    //     		$i = 7;$j=8;
    //             // $i = 8;$j=9;
	//         	while (
	// 	            $worksheet->getCellByColumnAndRow($i, 1)->getValue() != '' &&
	// 	            $worksheet->getCellByColumnAndRow($i, 1)->getValue() != '-' 
	// 	        ) {
	// 	            $api->trainingCode = $worksheet->getCellByColumnAndRow($i, $row)->getValue();
	// 	            $api->trainingCode = trim($api->trainingCode);
	// 	            foreach($program_xls[$row] as $nama_subcategory){
	// 	                if(!isset($course_list[$nama_subcategory][$api->trainingCode])){
	// 	                	if($api->trainingCode != ''){
	// 	                		$master_course_problem[$nama_subcategory][$api->trainingCode] = 'not found';
	// 	                	}		                    
	// 	                }
	// 	            }
		            
	// 	            $i+=2;
	// 	            $j+=2;
	// 	        }
    //     	}        	 
    //     }

    //     if(count($master_emp_problem) == '0' && count($master_program_problem) == '0' 
    //         && count($master_course_problem) == '0' && count($tanggal_salah) == '0'){

            
    //         for ($row = 2; $row <= $highestRow; ++$row){
    //             if(is_numeric($worksheet->getCellByColumnAndRow(1, $row)->getValue())){
    //                 $empCode = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
    //                 $empCode = trim($empCode);    
    //                 $finalScore = $worksheet->getCellByColumnAndRow(6, $row)->getCalculatedValue();
    //                 $trainingStatus = (strtolower($worksheet->getCellByColumnAndRow(5, $row)->getValue()) == 'lulus' ? 'pass' : 'fail');

    //                 // $finalScore = $worksheet->getCellByColumnAndRow(7, $row)->getCalculatedValue();
    //                 // $trainingStatus = (strtolower($worksheet->getCellByColumnAndRow(6, $row)->getValue()) == 'lulus' ? 'pass' : 'fail');

    //                 $date = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
    //                 $completionDate = FomartDateString($worksheet->getCellByColumnAndRow(2, $row)->getFormattedValue()) ;
    //                 if($completionDate == '1970-01-01'){
    //                     $completionDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date);
    //                 $completionDateUnformatted = $worksheet->getCellByColumnAndRow(2, $row)->getFormattedValue();
    //                 $tipe = $worksheet->getCellByColumnAndRow(3, $row)->getValue() ;
    //                 $program_nama = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
    //                 $program_nama = trim($program_nama);

    //                 // $subcategoryId_program_name = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
    //                 // $subcategoryId_program_name = trim($subcategoryId_program_name);

    //                 ++$succes;
    //                 $i = 7;$j=8;
    //                 while (
    //                     isset($header[$j]) && $header[$j] != '-'
    //                 ) {
                        
    //                     $trainingName = (isset($header[$j])?$header[$j]:'');
    //                     $trainingCode = $worksheet->getCellByColumnAndRow($i, $row)->getValue();
    //                     #$trainingCode = $worksheet->getCellByColumnAndRow($i, 1)->getValue();
    //                     $trainingCode = trim($trainingCode);
    //                     $nilai = $worksheet->getCellByColumnAndRow($j, $row)->getValue();
    //                     if($nilai == '-')$nilai = null;

    //                     #PATCH baru, kalau training code nya kosong per row tsb maka tidak perlu diproses
    //                     if(isset($program[$program_nama]) && isset($course_list[$program_nama][$trainingCode]) ){
    //                     	$subcategoryId = $program[$program_nama];
	//                         $courseId = $course_list[$program_nama][$trainingCode];

    //                         // $subcategoryId_program = $program[$subcategoryId_program_name];

	//                         $parameter_query[] = "(
	//                           '".$empCode."',
	//                           '".$trainingName."',
	//                           '".$trainingCode."',
	//                           '".$finalScore."',
	//                           '".$trainingStatus."',
	//                           '".$completionDate."',
	//                           '".$completionDateUnformatted."',
	//                           '".$tipe."',
	//                           '".$program_nama."',

	//                           '".$nilai."',
	//                           '".$subcategoryId."')";
    //                     }
                        
    //                     $i+=2;
    //                     $j+=2;
    //                 } 
    //             }               
    //         }
    //     } else{
    //         $this->res_obj->message = 
    //             [
    //                 '0' => [
    //                     'master_emp_problem' => $master_emp_problem,
    //                     'master_program_problem' => $master_program_problem,
    //                     'master_course_problem' => $master_course_problem,
    //                     'completion_date_problem' => $tanggal_salah,
    //                 ]
    //             ];
    //         Controller::update_log(['result' => json_encode($this->res_obj)]);
    //         // $this->res_obj->message = ['failed'];
    //         return $this->res_obj->fail();
    //     }

    //     if(count($parameter_query) > '0'){
    //         $index = 0;
    //         foreach($parameter_query as $key => $value){
    //           $parameter_pecah[$index][] = $value;
    //           if($key > 0 && fmod($key,'500') == '0'){
    //               $index++;

    //           }
    //     }

    //     // }  
    //     // dump($parameter_pecah); die;

    //     $insert = 0;
    //     DB::beginTransaction();
    //     foreach ($parameter_pecah as $key => $value) {
    //         $insert = DB::insert("REPLACE into `colibri_learning_history` (
    //             `empCode`,
    //             `trainingName`,
    //             `trainingCode`,
    //             `finalScore`,
    //             `trainingStatus`,
    //             `completionDate`,
    //             `completionDateUnformatted`,
    //             `tipe`,
    //             `program`,

    //             -- `subcategoryId_program`,
    //             -- `subcategoryId_program_name`,

    //             `nilai`,
    //             `subcategoryId`
    //             ) 
    //             VALUES ".implode(',',$value)."") ;
            
    //             if($insert == '0'){break;}
    //         }               
    //     }
 
    //     if($insert == 0)
    //     {
    //         DB::rollBack();
    //         $this->res_obj->message = ['failed'];
    //         Controller::update_log(['result' => 'failed']);
    //         return $this->res_obj->fail();
    //     }
    //     else
    //     {
    //         DB::commit();
    //         $this->res_obj->message = [
    //             'success' => count($parameter_query) . ' row imported.'
    //         ];
    //         Controller::update_log(['result' => json_encode($this->res_obj)]);
    //         return $this->res_obj->done();
    //     }
        
    // }

    // public function import_matrix(Request $req, Route $route){
    //     $validator = Validator::make($req->all(), [
    //         'data'   => 'required'
    //     ]);

    //     if ($validator->fails())
    //     {
    //         $this->res_obj->message = $validator->messages()->all();
    //         Controller::update_log(['result' => json_encode($this->res_obj)]);
    //         return $this->res_obj->fail();
    //     }        
       
    //     $file = $req->data;
    //     $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathName());
    //     // $writer = new Xlsx($spreadsheet);
    //     $worksheet = $spreadsheet->getSheet(0);
    //     $highestRow = $worksheet->getHighestRow(); // e.g. 10
    //     $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
    //     $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 5
    //     $list = [];$err = []; $succes = 0; $course = 0; $fail = 0;

    //     // ambil data course
    //     $course_data = $result = DB::select("
    //         SELECT idnumber,subCategoryName, vcc.courseId FROM v_course vc
    //         join `v_course_category` vcc ON vcc.courseId = vc.id
    //     ");

    //     $course_list = array();
    //     if(count($course_data) > 0){
    //         foreach ($course_data as $key => $value) {
    //             // $course_list[$value->subCategoryName][$value->idnumber] = $value->idnumber;
    //             $course_list[$value->idnumber] = $value->courseId;
    //         }
    //     }  

    //     // ambil data employee
    //     $employee_data = DB::select("
    //         SELECT empCode FROM colibri_employee
    //     ");
        
    //     $employee = array();
    //     if(count($employee_data) > 0){
    //         foreach ($employee_data as $key => $value) {
    //             $employee[$value->empCode] = $value->empCode;
    //         }
    //     } 
        
    //     // ambil data program 
    //     $program_data = DB::select("
    //         SELECT subCategoryId,subCategoryName FROM `v_course_category`
    //     ");
    //     $program = array();
    //     if(count($program_data) > 0){
    //         foreach ($program_data as $key => $value) {
    //             $program[$value->subCategoryName] = $value->subCategoryId;
    //         }
    //     }    
        
    //     // ambil data BU 
    //     $bu_data = DB::select("
    //         select compCode, compName from colibri_company cc 
    //     ");
    //     $bisnis_unit = array();
    //     if(count($bu_data) > 0){
    //         foreach ($bu_data as $key => $value) {
    //             $bisnis_unit[$value->compName] = $value->compCode;
    //         }
    //     }

    //     //ambil data jobtitle
    //     $jobTitleCode_data = DB::select("
    //         select cjt.jobTitleCode from colibri_job_title cjt
    //     ");
    //     $jobTitleCode = array();
    //     if(count($jobTitleCode_data) > 0){
    //         foreach ($jobTitleCode_data as $key => $value) {
    //             $jobTitleCode[$value->jobTitleCode] = $value->jobTitleCode;
    //         }
    //     }

    //     $api = new \StdClass;

    //     $master_emp_problem = [];
    //     $master_program_problem = [];      
    //     $master_course_problem = [];
    //     $master_bu_problem = []; 
    //     $master_jt_problem = []; 

    //     $program_xls = [];
    //     $tanggal_salah = [];
    //     $parameter_query = [];
    //     $parameter_pecah = [];

    //     $header_6 = $worksheet->getCellByColumnAndRow(6, 1)->getValue();
    //     $header_6 = trim($header_6);

    //     if($header_6 == 'Emp Code'){
    //         for ($row = 2; $row <= $highestRow; ++$row){
    //             if(is_numeric($worksheet->getCellByColumnAndRow(6, $row)->getValue())){
    
    //                 $api->empCode = $worksheet->getCellByColumnAndRow(6, $row)->getValue() ; //ambil value nip
    //                 $api->empCode = trim($api->empCode);
    
    //                 $api->trainingCode = $worksheet->getCellByColumnAndRow(4, $row)->getValue() ; //ambil value course number
    //                 $api->trainingCode = trim($api->trainingCode);
    
    //                 $api->bu = $worksheet->getCellByColumnAndRow(1, $row)->getValue(); // ambil value BU
    //                 $api->bu = trim($api->bu);
    
    //                 $api->program = $worksheet->getCellByColumnAndRow(2, $row)->getValue(); // ambil value program
    //                 $api->program = trim($api->program);
                    
    //                 if(!isset($employee[$api->empCode])){
    //                     $master_emp_problem[$api->empCode] = 'not found';
    //                 }    
                    
    //                 if(!isset($course_list[$api->trainingCode])){
    //                     if($api->trainingCode != ''){
    //                         $master_course_problem[$api->trainingCode] = 'not found';
    //                     }		                    
    //                 }
    
    //                 if(!isset($program[$api->program])){
    //                     if($api->program != ''){
    //                         $master_program_problem[$api->program] = 'not found'; 
    //                     }
                        
    //                 } 
    
    //                 if(!isset($bisnis_unit[$api->bu])){
    //                     if($api->bu != ''){
    //                         $master_bu_problem[$api->bu] = 'not found'; 
    //                     }
    //                 } 
    
    //             }
    //         }
    
    //         // dump($master_emp_problem);
    
    //         // echo count($master_emp_problem);
    
    //         #header
    //         $header = array();
    //         $jwt_data = Controller::jwt_data($req);
    //         if(count($master_emp_problem) == 0 && count($master_course_problem) == 0 && count($master_program_problem) == 0 && count($master_bu_problem) == 0 ){
    
    //             for ($row = 2; $row <= $highestRow; ++$row){
    //                 if(is_numeric($worksheet->getCellByColumnAndRow(6, $row)->getValue())){
    //                     $empCode = $worksheet->getCellByColumnAndRow(6, $row)->getValue() ; //ambil value nip
    //                     $empCode = trim($empCode);
    
    //                     $trainingCode = $worksheet->getCellByColumnAndRow(4, $row)->getValue() ; //ambil value course number
    //                     $courseId = $course_list[trim($trainingCode)];
    
    //                     $bu = $worksheet->getCellByColumnAndRow(1, $row)->getValue(); // ambil value BU
    //                     $bu = $bisnis_unit[trim($bu)];
    
    //                     $program_ = $worksheet->getCellByColumnAndRow(2, $row)->getValue(); // ambil value program
    //                     $program_ = $program[trim($program_)];
    
    //                     $parameter_query[] = "(
    //                         '".$bu."',
    //                         'c',
    //                         '".$empCode."',
    //                         'employee',
    //                         '".$courseId."',
    //                         '".$program_."',
    //                         '".Date('Y-m-d H:i:s')."',
    //                         'import_".$jwt_data['empCode']."'
    //                         )";
    //                 }
    //             }
    
    //             // dd($parameter_query);
    //         } else {
    //             $this->res_obj->message = 
    //                 [
    //                     '0' => [
    //                         'master_emp_problem' => $master_emp_problem,
    //                         'master_program_problem' => $master_program_problem,
    //                         'master_course_problem' => $master_course_problem,
    //                         'master_bu_problem' => $master_bu_problem,
    //                     ]
    //                 ];
    //             Controller::update_log(['result' => json_encode($this->res_obj)]);
    //             // $this->res_obj->message = ['failed'];
    //             return $this->res_obj->fail();
    //         }
    
    //         if(count($parameter_query) > '0'){
    //             $index = 0;
    //             foreach($parameter_query as $key => $value){
    //               $parameter_pecah[$index][] = $value;
    //               if($key > 0 && fmod($key,'500') == '0'){
    //                   $index++;
    
    //               }
    //             }  
    //         }
    
    //         // dump($parameter_query); die;
    
    //         $insert = 0;
    //         DB::beginTransaction();
    //         foreach ($parameter_pecah as $key => $value) {
    //             $insert = DB::insert("REPLACE into `colibri_matrix_import_test` (
    //                 `compCode`,
    //                 `matrix_type`,
    //                 `empCode`,
    //                 `type`,
    //                 `courseId`,
    //                 `programId`,
    //                 `created_at`,
    //                 `created_by`
    //                 ) 
    //                 VALUES ".implode(',',$value)."") ;
                
    //              if($insert == '0'){break;}
                               
    //         }
     
    //         if($insert == 0)
    //         {
    //             DB::rollBack();
    //             $this->res_obj->message = ['failed'];
    //             Controller::update_log(['result' => 'failed']);
    //             return $this->res_obj->fail();
    //         }
    //         else
    //         {
    //             DB::commit();
    //             $this->res_obj->message = [
    //                 'success' => count($parameter_query) . ' row imported.'
    //             ];
    //             Controller::update_log(['result' => json_encode($this->res_obj)]);
    //             return $this->res_obj->done();
    //         }
    //     } else {
    //         for ($row = 2; $row <= $highestRow; ++$row){
    //             if(is_numeric($worksheet->getCellByColumnAndRow(6, $row)->getValue())){
    
    //                 $api->jobTitleCode = $worksheet->getCellByColumnAndRow(6, $row)->getValue() ; //ambil value job title
    //                 $api->jobTitleCode = trim($api->jobTitleCode);
    
    //                 $api->trainingCode = $worksheet->getCellByColumnAndRow(4, $row)->getValue() ; //ambil value course number
    //                 $api->trainingCode = trim($api->trainingCode);
    
    //                 $api->bu = $worksheet->getCellByColumnAndRow(1, $row)->getValue(); // ambil value BU
    //                 $api->bu = trim($api->bu);
    
    //                 $api->program = $worksheet->getCellByColumnAndRow(2, $row)->getValue(); // ambil value program
    //                 $api->program = trim($api->program);
                    
    //                 if(!isset($jobTitleCode[$api->jobTitleCode])){
    //                     $master_jt_problem[$api->jobTitleCode] = 'not found';
    //                 }    
                    
    //                 if(!isset($course_list[$api->trainingCode])){
    //                     if($api->trainingCode != ''){
    //                         $master_course_problem[$api->trainingCode] = 'not found';
    //                     }		                    
    //                 }
    
    //                 if(!isset($program[$api->program])){
    //                     if($api->program != ''){
    //                         $master_program_problem[$api->program] = 'not found'; 
    //                     }
                        
    //                 } 
    
    //                 if(!isset($bisnis_unit[$api->bu])){
    //                     if($api->bu != ''){
    //                         $master_bu_problem[$api->bu] = 'not found'; 
    //                     }
    //                 } 
    
    //             }
    //         }
    
    //         // dump($master_emp_problem);
    
    //         // echo count($master_emp_problem);
    
    //         #header
    //         $header = array();
    //         $jwt_data = Controller::jwt_data($req);
    //         if(count($master_jt_problem) == 0 && count($master_course_problem) == 0 && count($master_program_problem) == 0 && count($master_bu_problem) == 0 ){
    
    //             for ($row = 2; $row <= $highestRow; ++$row){
    //                 if(is_numeric($worksheet->getCellByColumnAndRow(6, $row)->getValue())){
    //                     $jobTitleCode = $worksheet->getCellByColumnAndRow(6, $row)->getValue() ; //ambil value jobTitleCode
    //                     $jobTitleCode = trim($jobTitleCode);
    
    //                     $trainingCode = $worksheet->getCellByColumnAndRow(4, $row)->getValue() ; //ambil value course number
    //                     $courseId = $course_list[trim($trainingCode)];
    
    //                     $bu = $worksheet->getCellByColumnAndRow(1, $row)->getValue(); // ambil value BU
    //                     $bu = $bisnis_unit[trim($bu)];
    
    //                     $program_ = $worksheet->getCellByColumnAndRow(2, $row)->getValue(); // ambil value program
    //                     $program_ = $program[trim($program_)];
    
    //                     $parameter_query[] = "(
    //                         '".$bu."',
    //                         '".$jobTitleCode."',
    //                         'c',
    //                         null,
    //                         'jobtitle',
    //                         '".$courseId."',
    //                         '".$program_."',
    //                         '".Date('Y-m-d H:i:s')."',
    //                         'import_".$jwt_data['empCode']."'
    //                         )";
    //                 }
    //             }
    
    //             // dd($parameter_query);
    //         } else {
    //             $this->res_obj->message = 
    //                 [
    //                     '0' => [
    //                         'master_emp_problem' => $master_emp_problem,
    //                         'master_program_problem' => $master_program_problem,
    //                         'master_course_problem' => $master_course_problem,
    //                         'master_bu_problem' => $master_bu_problem,
    //                     ]
    //                 ];
    //             Controller::update_log(['result' => json_encode($this->res_obj)]);
    //             // $this->res_obj->message = ['failed'];
    //             return $this->res_obj->fail();
    //         }
    
    //         if(count($parameter_query) > '0'){
    //             $index = 0;
    //             foreach($parameter_query as $key => $value){
    //               $parameter_pecah[$index][] = $value;
    //               if($key > 0 && fmod($key,'500') == '0'){
    //                   $index++;
    
    //               }
    //             }  
    //         }
    
    //         // dump($parameter_query); die;
    
    //         $insert = 0;
    //         DB::beginTransaction();
    //         foreach ($parameter_pecah as $key => $value) {
    //             $insert = DB::insert("REPLACE into `colibri_matrix_import_test` (
    //                 `compCode`,
    //                 `jobTitleCode`,
    //                 `matrix_type`,
    //                 `empCode`,
    //                 `type`,
    //                 `courseId`,
    //                 `programId`,
    //                 `created_at`,
    //                 `created_by`
    //                 ) 
    //                 VALUES ".implode(',',$value)."") ;
                
    //              if($insert == '0'){break;}
                               
    //         }
     
    //         if($insert == 0)
    //         {
    //             DB::rollBack();
    //             $this->res_obj->message = ['failed'];
    //             Controller::update_log(['result' => 'failed']);
    //             return $this->res_obj->fail();
    //         }
    //         else
    //         {
    //             DB::commit();
    //             $this->res_obj->message = [
    //                 'success' => count($parameter_query) . ' row imported.'
    //             ];
    //             Controller::update_log(['result' => json_encode($this->res_obj)]);
    //             return $this->res_obj->done();
    //         }
    //     }
    // } 
} 
