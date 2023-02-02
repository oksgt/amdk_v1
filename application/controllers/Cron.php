<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cron extends CI_Controller
{

    public function generateCert()
    {
        $limit = 200;
        if (isset($_GET['limit']) && $_GET['limit'] > 0) {
            $limit = $_GET['limit'];
        }

        if (!file_exists('public/uploads/certificate_template/certificate-default.jpg')) {
            @copy('public/images/certificate-default.jpg', 'public/uploads/certificate_template/certificate-default.jpg');
        }
        if (!file_exists('public/uploads/certificate_thumbnail/certificate-thumbnail-default.png')) {
            @copy('public/images/certificate-thumbnail-default.png', 'public/uploads/certificate_thumbnail/certificate-thumbnail-default.png');
        }
        echo "Executing cert generator" . PHP_EOL;
        $selectedEmpCode = (!empty($_GET['empCode']) ? $_GET['empCode'] : 'all');

        if ($selectedEmpCode != 'all') {

            $dash_summary_completed = DB::select("
                select DISTINCT  SubCategoryId, empCode, vnip from colibri_dashboard_admin_employee_summary where completionStatus = 'completed' 
                and empCode = ?  and show_in_dashboard = 1
            ", [$_GET['empCode']]);

            // dump($dash_summary_completed); die;

            if ($dash_summary_completed) {
                foreach ($dash_summary_completed as $key => $value) {
                    // echo $value->vnip;
                    // die;
                    $file_cert = DB::select("
                        SELECT
                        id,filename
                        FROM `colibri_employee_certificate`
                        WHERE empCode = ? and subCategoryId = ? and virtual_nip = ?
                    ", [$selectedEmpCode, $value->SubCategoryId, $value->vnip]);

                    if (count($file_cert) > 0) {
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

        $debug = (!empty($_GET['debug'])) ? true : false;
        set_time_limit(0);
        $completed = DB::table('colibri_employee_certificate')
            // ->select(DB::raw("CONCAT(empCode,'-',subCategoryId) as c")) 
            ->select(DB::raw("CONCAT(virtual_nip,'-',subCategoryId) as c"))
            ->where('expiryDate', '<', date('Y-m-d'))
            ->get()->toArray();
        $in = array_column($completed, 'c');

        // dump($completed);

        if ($debug) {;
            echo PHP_EOL . "Data completed user :" . PHP_EOL;
            print_r($in);
        }

        #sudah ada antrian?
        $antrian = DB::select("
            SELECT
			  COUNT(*) AS 'total'
			FROM `colibri_certificate_cron`
			WHERE tanggal = CURRENT_DATE()
        ");

        if ($antrian[0]->total == '0') {
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

        if ($selectedEmpCode != 'all') {
            $emp_array = DB::table('colibri_employee')->where('empCode', $selectedEmpCode)->get()->pluck('empCode')->toArray();
        } else {
            $emp_array = DB::table('colibri_certificate_cron')->where('is_exec', '0')->where('tanggal', date('Y-m-d'))->skip(0)->take($limit)->get()->pluck('empCode')->toArray();
            // echo 'all';
        }

        // dump($emp_array);
        //     die;

        $emp_s = array_chunk($emp_array, 100);


        foreach ($emp_s as $emp_a) {

            $data_completion = DB::table(DB::raw('colibri_dashboard_admin_employee_summary A'))
                // ->join(DB::raw('colibri_course_category B'),'B.courseId','=','A.courseId')
                ->join(DB::raw('colibri_employee ce'), 'ce.empCode', '=', 'A.empCode')
                ->where('completionStatus', 'completed')
                ->where('show_in_dashboard', 1)
                ->whereIn('A.empCode', $emp_a)
                // ->whereNotIn(DB::raw("CONCAT(A.vnip,'-',B.categoryId)"),$in)
                ->get()->toArray();
            //echo vsprintf(str_replace(['?'], ['\'%s\''], $data_completion->toSql()), $data_completion->getBindings());
            //exit();
            //->get()->toArray();
            if ($debug) {
                echo PHP_EOL . "Emp Code :" . $selectedEmpCode . PHP_EOL;
                echo PHP_EOL . "Data completion user :" . PHP_EOL;
                print_r($data_completion);
            }

            // dump($data_completion); die;


            // $categoryId = array_column($data_completion, 'categoryId');
            $categoryId = array_column($data_completion, 'SubCategoryId');
            $data_sub = DB::table('colibri_course_category')
                ->select('colibri_course_category.categoryId', DB::raw('COUNT(colibri_course_category.courseId) as total'))
                ->join('mdl_course', 'mdl_course.id', '=', 'colibri_course_category.courseId')
                ->whereIn('colibri_course_category.categoryId', $categoryId)
                ->groupBy('colibri_course_category.categoryId')->get();
            // print_r($data_sub);
            $sub_course_total = [];
            foreach ($data_sub as $row) {
                $sub_course_total[$row->categoryId] = $row->total;
            }
            // dump($sub_course_total); die;
            if ($debug) {;
                echo PHP_EOL . "Sub course total :" . PHP_EOL;
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
                if (isset($emp_course[$row->empCode . "_" . $row->empId][$row->SubCategoryId])) {
                    $emp_course[$row->empCode . "_" . $row->empId][$row->SubCategoryId]++;
                } else {
                    $emp_course[$row->empCode . "_" . $row->empId][$row->SubCategoryId] = 1;
                }

                if (isset($emp_score[$row->empCode][$row->SubCategoryId])) {
                    $emp_score[$row->empCode][$row->SubCategoryId] =
                        $emp_score[$row->empCode][$row->SubCategoryId] + $row->grade;
                } else {
                    $emp_score[$row->empCode][$row->SubCategoryId] = $row->grade;
                }

                $completionDate[$row->empCode][$row->SubCategoryId] = $row->completionDate;
            }
            if ($debug) {
                echo PHP_EOL . "Score:" . PHP_EOL;
                print_r($emp_score);
                echo PHP_EOL . "Course:" . PHP_EOL;
                print_r($emp_course);
            }

            // dump($emp_course);
            // die;

            $process = [];
            $categoryIdList = [];
            $empCodeList = [];



            foreach ($emp_course as $empCode => $category) {
                foreach ($category as $categoryId => $c) {
                    // echo "ss ".$empCode;
                    if ($c == $sub_course_total[$categoryId]) {
                        $empCode_arr = explode("_", $empCode);
                        $process[] = [
                            'empId' => $empCode_arr[1],
                            'empCode' => $empCode_arr[0],
                            'vnip' => $empCode,
                            'categoryId' => $categoryId
                        ];
                        $categoryIdList[] = $categoryId;
                        $empCodeList[] = $empCode_arr[0];
                    }
                }
            }
            if ($debug) {
                echo PHP_EOL . "process:" . PHP_EOL;
                print_r($process);
            }

            // dump($process);
            // die;

            $cert_data = DB::table('colibri_certificate')->whereIn('ownerId', $categoryIdList)->where('certEnabled', 1)
                ->get();
            $category_data = DB::table('colibri_category')->whereIn('id', $categoryIdList)->get();
            $employee_data = DB::table('colibri_employee')->whereIn('empCode', $empCodeList)->get();
            // echo $categoryIdList;
            // dump($categoryIdList); die;

            $companyList = array_column($employee_data->toArray(), 'compCode');
            $company_data = DB::table('colibri_company')->whereIn('compCode', $companyList)->get();
            $company = [];
            $employee = [];
            $cert = [];
            $category = [];
            foreach ($category_data as $row) {
                $category[$row->id] = $row;
            }
            echo PHP_EOL . "category_data " . PHP_EOL;
            foreach ($cert_data as $row) {
                if (!is_object($row->certProperties)) {
                    $row->certProperties = json_decode($row->certProperties);
                    if (!$row->certProperties) {
                        $row->certProperties = [];
                    }
                }
                $cert[$row->ownerId] = $row;
            }
            echo PHP_EOL . "cert_data " . PHP_EOL;

            foreach ($company_data as $row) {
                $company[$row->compCode] = $row;
            }
            foreach ($employee_data as $row) {
                // $employee[$row->empCode]=$row;   
                $employee[$row->empId] = $row;
            }

            // echo "<pre>";
            // dump($emp_course); 
            // dump($employee['138494']); 
            // die;
            // dump($cert); 

            foreach ($process as $row) {
                if ($debug) {
                    echo PHP_EOL . "Executing " . PHP_EOL;
                }
                $empCode = $row['empCode'];
                $program = $row['categoryId'];

                $empId = $row['empId'];

                // dump($employee["138501adm"]); 
                // echo "xx ". isset($cert[$program]);
                // die;
                // echo $row['categoryId']." | ";
                // die;
                if (!empty($cert[$program])) {
                    // echo $empCode ."<br>";
                    $certTmp = $cert[$program];
                    $emp = $employee[$empId];
                    $comp = $company[$emp->compCode];
                    $empScore = $emp_score[$empCode][$program] / $emp_course[$empCode . "_" . $empId][$program];
                    $empScore = round($empScore, 1);
                    if (!json_decode($comp->compStyle)) {
                        $companyThumbnail = url('public/images/company-default.jpg');
                    } else {
                        $compStyle = json_decode($comp->compStyle);
                        if ($compStyle->company_thumbnail != '' && file_exists(base_path() . '/public/images/company/' . $compStyle->company_thumbnail)) {
                            $type = pathinfo(base_path() . '/public/images/company/' . $compStyle->company_thumbnail, PATHINFO_EXTENSION);
                            $data = \File::get(base_path() . '/public/images/company/' . $compStyle->company_thumbnail);
                            $base64 = "";
                            if ($type == "svg") {
                                $companyThumbnail = "data:image/svg+xml;base64," . base64_encode($data);
                            } else {
                                $companyThumbnail = "data:image/" . $type . ";base64," . base64_encode($data);
                            }
                            //$companyThumbnail=$base64;
                            //$companyThumbnail = url('public/images/company/'.$compStyle->company_thumbnail);
                        } else {
                            $companyThumbnail = url('public/images/company-default.jpg');
                        }
                    }

                    //$publishDate=date('d.m.Y');
                    $publishDate = date('d.m.Y', strtotime(
                        $completionDate[$empCode][$program] ?? date('Y-m-d')
                    ));

                    if ($certTmp->expirationTime == '0') {
                        $expiryTime = strtotime("+1200 month", strtotime(
                            $completionDate[$empCode][$program] ?? date('Y-m-d')
                        ));
                    } else {
                        $expiryTime = strtotime("+" . $certTmp->expirationTime . " month", strtotime(
                            $completionDate[$empCode][$program] ?? date('Y-m-d')
                        ));
                    }

                    $expiryDate = date("d.m.Y", $expiryTime);

                    $exist = DB::select("
                        SELECT * FROM colibri_employee_certificate where empCode =? AND expiryDate=? AND  subCategoryId=?
						", [
                        $empCode, date('Y-m-d', $expiryTime), $program
                    ]);

                    if (!isset($exist[0])) {
                        $cert_no = DB::table('colibri_employee_certificate')->insertGetId([
                            'empCode' => $empCode,
                            #'publishDate'=>date('Y-m-d'),
                            'publishDate' => $completionDate[$empCode][$program] ?? date('Y-m-d'),
                            'filename' => '',
                            'certId' => $certTmp->certId,
                            'expiryDate' => date('Y-m-d', $expiryTime),
                            'subCategoryId' => $program,
                            'subCategoryName' => $category[$program]->name,
                        ]);
                        $cert_no = (string) $cert_no;
                        echo PHP_EOL . "insert cert_no " . $cert_no . PHP_EOL;
                        $qr = QrCode::format('png')
                            ->margin(1)
                            ->size(500)
                            ->generate($cert_no);
                        echo PHP_EOL . "generate QR " . $cert_no . PHP_EOL;

                        $qr_code = 'data:image/png;base64,' . base64_encode($qr);

                        $certTmp->certProp = [
                            'cert_name' => ['type' => 'text', 'value' => $category[$program]->name],
                            'empName' => ['type' => 'text', 'value' => $emp->empName],
                            'empCode' => ['type' => 'text', 'value' => $emp->empCode],
                            'publishDate' => ['type' => 'text', 'value' => $publishDate],
                            'expiryDate' => ['type' => 'text', 'value' => $expiryDate],
                            'score' => ['type' => 'text', 'value' => $empScore],
                            'cert_no' => ['type' => 'text', 'value' => $cert_no],
                            'company_thumbnail' => ['type' => 'object', 'value' => $companyThumbnail],
                            'qr_code' => ['type' => 'object', 'value' => $qr_code],
                        ];
                        $paperSize = [
                            'a4' => [
                                'height' => 595,
                                'width' => 842,
                            ],
                            'folio' => [
                                'height' => 595.4,
                                'width' => 935.5
                            ]
                        ];

                        $pdf = PDF::loadView('certTemplate', ['data' => $certTmp, 'paperSize' => $paperSize[$certTmp->certPaperSize]])->setPaper([0, 0, $paperSize[$certTmp->certPaperSize]['height'], $paperSize[$certTmp->certPaperSize]['width']], 'landscape');
                        echo PHP_EOL . "generate PDF " . $cert_no . PHP_EOL;


                        $enc_compCode = md5($emp->compCode);
                        @mkdir(base_path() . '/public/uploads/employee_certificate/' . $enc_compCode);
                        $nama_program = str_replace('/', '-', $category[$program]->name);

                        $filename = 'public/uploads/employee_certificate/' . $enc_compCode . '/Certificate-' . $emp->empCode . '-' . $nama_program . '.pdf';
                        $path = base_path() . '/' . $filename;

                        file_put_contents($path, $pdf->output());
                        echo PHP_EOL . "save PDF " . $cert_no . PHP_EOL;
                        DB::table('colibri_employee_certificate')->where('id', $cert_no)->update(['filename' => $filename]);
                        //return $pdf->download('cert.pdf');
                        echo "Cert Printed for: " . $emp->empCode . PHP_EOL;
                        echo "Cert Sub: " . $category[$program]->name . PHP_EOL;
                        echo "File: " . $filename . PHP_EOL;
                        echo "----------" . PHP_EOL;
                    }
                }
            }
            $list_employee = implode("','", $emp_a);
            $list_employee = "'" . $list_employee . "'";
            DB::update("
                UPDATE `colibri_certificate_cron`
				SET is_exec = '1',tanggal_exec = NOW()
				where tanggal = CURRENT_DATE()
				AND empCode IN (" . $list_employee . ")
            ");
        }

        $result = DB::select("
            SELECT COUNT(*) as total FROM colibri_certificate_cron where tanggal = CURRENT_DATE()
            AND is_exec = '0';
        ");

        echo "DONE, sisa " . $result[0]->total . PHP_EOL;
    }

    public function reg_moodle_user(Request $req, Route $route)
    {

        $url_server = config('app.APP_URL_CORE');
        $token = config('app.APP_MOODLE_TOKEN');
        $MoodleRest = new MoodleRest($url_server . 'webservice/rest/server.php', $token);

        DB::select("
          DELETE
          FROM `colibri_employee_cron`
      ");
        // die;
        // NIP REGULER
        // DB::insert("
        //     INSERT INTO `colibri_employee_cron` (`empCode`, `aksi`, `tanggal`)             
        //     SELECT 
        //       empCode,
        //       'insert',
        //       NOW() 
        //     FROM
        //       `colibri_employee` 
        //     WHERE ".config('app.FK_EMPLOYEE_MDL')." NOT IN 
        //       (SELECT 
        //         username 
        //       FROM
        //         mdl_user)
        // ");

        // -- VNIP
        DB::select("
          INSERT INTO `colibri_employee_cron` (`empCode`, `aksi`, `tanggal`)             
          select * from(
            SELECT 
              concat(empCode,'_00000') as vnip,
              'insert',
              NOW() 
            FROM
              `colibri_employee` ) as emp
          WHERE vnip NOT IN 
              (SELECT 
                username 
              FROM
                mdl_user)
      ");



        $cron_list = DB::select("
          SELECT
            empCode,
            aksi,
            eksekusi_api,
            hasil_api,
            tanggal_eksekusi,
            tanggal
          FROM colibri_employee_cron
          WHERE eksekusi_api = '0'
          ORDER BY RAND()
          LIMIT 0, 100

      ");

        // dump($cron_list);

        $sukses = 0;
        $gagal = 0;
        $err_message = [];
        foreach ($cron_list as $cron) {
            // echo $cron->empCode."<br>";
            if ($cron->aksi == 'insert') {

                $arr_emp = explode("_", $cron->empCode);
                $emp_list = DB::table('colibri_employee')
                    ->where('empCode', $arr_emp[0])
                    ->get();
                if (isset($emp_list[0])) {
                    $emp =  $emp_list[0];

                    $username = $cron->empCode;
                    $email = 'fake' . strtolower($cron->empCode) . '@colibri.id';
                    if (request('using') == 'email') {
                        $username = $emp->empEmail;
                        $email = $emp->empEmail;
                    }

                    $parameter['users'][0] = array(
                        'username' => strtolower($username),
                        'password' => '13579-aZ',
                        'createpassword' => '0',
                        'firstname' => $emp->empName,
                        'lastname' => ' ',
                        'email' => $email,
                    );

                    $return = $MoodleRest->request('core_user_create_users', $parameter, MoodleRest::METHOD_POST);

                    if (isset($return[0])) {
                        $hasil_api = implode($return[0], ",");

                        DB::update("
                      UPDATE mdl_user SET `password` = ? WHERE username = ?
                  ", [md5(config('app.PASSWORD_DEFAULT_SSO')), $cron->empCode]);
                        $sukses++;
                        $this->sync_user($username, request('client'));
                    } else {
                        if (isset($return['debuginfo'])) {
                            $hasil_api = $return['debuginfo'];
                        } else if (isset($return['message'])) {
                            $hasil_api = $return['message'];
                        } else {
                            $hasil_api = $return['errorcode'];
                        }
                        $err_message['message'][] = $hasil_api;
                        $err_message['parameter'][] = $parameter;
                        $gagal++;
                    }

                    $insert = DB::table('colibri_employee_cron')->where('empCode', $cron->empCode)->where('aksi', 'insert')
                        ->update(['eksekusi_api' => '1', 'tanggal_eksekusi' => date("Y-m-d H:i:s"), 'hasil_api' => $hasil_api]);
                }
            } else if ($cron->aksi == 'delete') {
                $user_list = DB::table('mdl_user')
                    ->where('username', $cron->empCode)
                    ->get();
                if (isset($user_list[0])) {
                    $user =  $user_list[0];
                    $parameter['userids'] = $user->id;

                    $return = $MoodleRest->request('core_user_delete_users', $parameter, MoodleRest::METHOD_POST);
                    $insert = DB::table('colibri_employee_cron')->where('empCode', $cron->empCode)->where('aksi', 'delete')->update(['eksekusi_api' => '1', 'tanggal_eksekusi' => date("Y-m-d H:i:s"), 'hasil_api' => '']);
                }
            }
        }
        $sisa = DB::select("
          SELECT 
           count(*) as `total`
          FROM
            `colibri_employee` 
          WHERE concat(" . config('app.FK_EMPLOYEE_MDL') . ",'_00000') NOT IN 
            (SELECT 
              username 
            FROM
              mdl_user)
      ");

        $this->res_obj->message = 'Sukses ' . $sukses . '. Gagal = ' . $gagal . '. Sisa = ' . $sisa[0]->total;
        $this->res_obj->data = $err_message;
        Controller::update_log(['result' => json_encode($this->res_obj)]);
        return $this->res_obj->done();
    }

    protected function sync_user($username = '', $client_name = '')
    {
        if (config('app.APP_MULTICOMPANY_MODE') == 1) {
            $result = DB::connection('mysql_route')->select("
        	    INSERT INTO `colibri_user` (
				  `username`,
				  `client_id`
				) 
				VALUES
				  (
				    ?,
				    (SELECT `id` FROM `colibri_client` WHERE client_name = ?)
				  )
        	", [$username, $client_name]);
        }
    }

    public function push_cron_monitoring(Request $req)
    {

        // get data cron log dalam kurun waktu 10menit terakhir dg kriteria message !== null
        $cron_error_log = DB::select("
        select cron_name, status, insert_date, message,
        concat(status, ' - ', message) as err_message
        from colibri_cron_log ccl2 where message is not null 
        AND insert_date BETWEEN DATE_SUB(NOW() , INTERVAL 10 MINUTE) AND NOW()
        order by insert_date desc
    ");

        if (!empty($cron_error_log)) {
            foreach ($cron_error_log as $key => $value) {
                // echo $value->err_message."<br>";
                //kirim notif
                Telegram::sendMessage(
                    '<b>' . ucwords("Push Cron Log") . '</b>' . PHP_EOL . PHP_EOL
                        . 'start : ' . date('d M y H:i', strtotime($value->insert_date)) . PHP_EOL
                        . 'cron : ' . strip_tags($value->cron_name) . PHP_EOL
                        . 'result : ' . $value->err_message . PHP_EOL
                );
            }
        }

        //notif done autoenroll all
        $last_start_autoenrol = DB::select("
        select insert_date from colibri_cron_log ccl where 
        cron_name = 'autoenroll_all'
        and status = 'Start Autoenroll' 
        AND insert_date BETWEEN DATE_SUB(NOW() , INTERVAL 10 MINUTE) AND NOW()
        order by insert_date desc limit 1
    ");

        $last_done_autoenrol = DB::select("
        select insert_date from colibri_cron_log ccl where 
        cron_name = 'autoenroll_all'
        and status = 'Done Autoenroll 1-3'
        AND insert_date BETWEEN DATE_SUB(NOW() , INTERVAL 10 MINUTE) AND NOW()
        order by insert_date desc limit 1
    ");

        if (!empty($last_start_autoenrol) && !empty($last_done_autoenrol)) {
            if ($last_start_autoenrol[0]->insert_date < $last_done_autoenrol[0]->insert_date) {
                // kirim notif
                Telegram::sendMessage(
                    '<b>' . ucwords("SP Autoenroll All Done") . '</b>' . PHP_EOL . PHP_EOL
                        . 'start : ' . date('d M y H:i', strtotime($last_done_autoenrol[0]->insert_date)) . PHP_EOL
                        . 'cron : Autoenroll_all' . PHP_EOL
                        . 'result : Done' . PHP_EOL
                );
            }
        }

        // notif done autoenroll 4
        $last_start_autoenrol_4 = DB::select("
        select insert_date from colibri_cron_log ccl where 
        cron_name = 'autoenroll_4'
        and status = 'Start Autoenroll 4/4'
        AND insert_date BETWEEN DATE_SUB(NOW() , INTERVAL 10 MINUTE) AND NOW()
        order by insert_date desc limit 1
    ");

        $last_done_autoenrol_4 = DB::select("
        select insert_date from colibri_cron_log ccl where 
        cron_name = 'autoenroll_4'
        and status = 'Done Autoenroll 4/4'
        AND insert_date BETWEEN DATE_SUB(NOW() , INTERVAL 10 MINUTE) AND NOW()
         order by insert_date desc limit 1
    ");

        if (!empty($last_start_autoenrol_4) && !empty($last_done_autoenrol_4)) {
            if ($last_start_autoenrol_4[0]->insert_date < $last_done_autoenrol_4[0]->insert_date) {
                // kirim notif
                Telegram::sendMessage(
                    '<b>' . ucwords("SP Autoenroll_4 Done") . '</b>' . PHP_EOL . PHP_EOL
                        . 'start : ' . date('d M y H:i', strtotime($last_done_autoenrol_4[0]->insert_date)) . PHP_EOL
                        . 'cron : Autoenroll_4' . PHP_EOL
                        . 'result : Done' . PHP_EOL
                );
            }
        }

        // notif done sp dashboard
        $last_start_dashboard = DB::select("
        select insert_date from colibri_cron_log ccl where 
        cron_name = 'dashboard'
        and status = 'Start Dashboard'
        AND insert_date BETWEEN DATE_SUB(NOW() , INTERVAL 10 MINUTE) AND NOW()
         order by insert_date desc limit 1
    ");

        $last_done_dashboard = DB::select("
        select insert_date from colibri_cron_log ccl where 
        cron_name = 'dashboard'
        and status = 'Done Dashboard' 
        AND insert_date BETWEEN DATE_SUB(NOW() , INTERVAL 10 MINUTE) AND NOW()
        order by insert_date desc limit 1
    ");


        if (!empty($last_start_dashboard) && !empty($last_done_dashboard)) {
            if ($last_start_dashboard[0]->insert_date < $last_done_dashboard[0]->insert_date) {
                // kirim notif
                Telegram::sendMessage(
                    '<b>' . ucwords("SP Dashboard Done") . '</b>' . PHP_EOL . PHP_EOL
                        . 'start : ' . date('d M y H:i', strtotime($last_done_dashboard[0]->insert_date)) . PHP_EOL
                        . 'cron : Dashboard' . PHP_EOL
                        . 'result : Done' . PHP_EOL
                );
            }
        }


        // notif done sp leaderboard
        $last_start_leaderboard = DB::select("
        select insert_date from colibri_cron_log ccl where 
        cron_name = 'leaderboard' 
        and status = 'Start Leaderboard' 
        AND insert_date BETWEEN DATE_SUB(NOW() , INTERVAL 10 MINUTE) AND NOW()
        order by insert_date desc limit 1
    ");

        $last_done_leaderboard = DB::select("
        select insert_date from colibri_cron_log ccl where 
        cron_name = 'leaderboard'
        and status = 'Done SP Leaderboard'
        AND insert_date BETWEEN DATE_SUB(NOW() , INTERVAL 10 MINUTE) AND NOW()
        order by insert_date desc limit 1
    ");

        if (!empty($last_start_leaderboard) && !empty($last_done_leaderboard)) {
            if ($last_start_leaderboard[0]->insert_date < $last_done_leaderboard[0]->insert_date) {
                // kirim notif
                Telegram::sendMessage(
                    '<b>' . ucwords("SP Leaderboard Done") . '</b>' . PHP_EOL . PHP_EOL
                        . 'start : ' . date('d M y H:i', strtotime($last_done_leaderboard[0]->insert_date)) . PHP_EOL
                        . 'cron : Leaderboard' . PHP_EOL
                        . 'result : Done' . PHP_EOL
                );
            }
        }
        $this->res_obj->message = 'cron monitoring done';
        return $this->res_obj->done();
    }

    public function push_quiz(Request $req)
    {


        $quiz = DB::select("
        SELECT 
          `quizPublishTime`,
          FLOOR(`quizExpired` / 3600) AS 'duration' 
        FROM
          `colibri_daily_quiz` 
        WHERE quizDate = CURRENT_DATE()
    ");

        if (isset($quiz[0]->quizPublishTime)) {
            $app_id     = 'c22dd684-8d92-4438-8b0c-779fbc927691';
            $server_key = 'NjFlNWUzZjUtMTQ2My00M2E2LWJiMDUtZDFhMzZiOWUyNTgx';

            $title   = 'Daily quiz';
            $content = 'Hi, please join the KL academy daily quiz. It\'s fun and get your points! Quiz started at ' . $quiz[0]->quizPublishTime . ' for ' . $quiz[0]->duration . ' hours.';
            $form    = [
                'app_id'            => $app_id,
                'data'              => [
                    '__type' => 'daily_quiz', //menyesuaikan
                    '__id'   => 1, //menyesuaikan
                ],
                'included_segments' => ['Subscribed Users'],
                "small_icon"        => config('hris.url_image') . 'app-assets/images/ico/favicon.png',
                "headings"          => [
                    'en' => $title, // judul push
                ],
                "contents"          => [
                    'en' => $content, // content push
                ],
                "big_picture" => "https://images.pexels.com/photos/248797/pexels-photo-248797.jpeg?auto=compress&cs=tinysrgb&dpr=1&w=500",
                "adm_big_picture" => "https://images.pexels.com/photos/248797/pexels-photo-248797.jpeg?auto=compress&cs=tinysrgb&dpr=1&w=500",
                "chrome_big_picture" => "https://images.pexels.com/photos/248797/pexels-photo-248797.jpeg?auto=compress&cs=tinysrgb&dpr=1&w=500",

            ];
            $curl   = curl_init('https://onesignal.com/api/v1/notifications');
            $header = [
                'Content-Type: application/json; charset=utf-8',
                'Authorization: Basic ' . $server_key
            ];
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($form));
            //    curl_setopt($curl, CURLOPT_HEADER, true);
            //print_r(curl_error($curl));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_exec($curl);
            curl_close($curl);
            $this->res_obj->message = ' push notif quiz done';
            Controller::update_log(['result' => json_encode($this->res_obj)]);
            return $this->res_obj->done();
        } else {
            $this->res_obj->message = ' quiz not found';
            Controller::update_log(['result' => json_encode($this->res_obj)]);
            return $this->res_obj->fail();
        }
    }

    public function push_evaluation(Request $req)
    {

        $validator = Validator::make($req->all(), []);

        #list sub category
        $data_sub_category = DB::select("
        SELECT 
          subCategoryId,
          courseId 
        FROM
          `v_course_category` vcc 
          JOIN colibri_course_addon cca 
            ON cca.`id` = vcc.`courseId` 
        WHERE track_progress = '1' 
        ORDER BY subCategoryId,
          courseId 
    ");

        $sub_category = array();
        if (count($data_sub_category) > 0) {
            foreach ($data_sub_category as $key => $value) {
                $sub_category[$value->subCategoryId][] = $value->courseId;
            }
        }

        #list completed employee
        $data_completed_employee = DB::select("
        SELECT
          `empCode`,courseId
        FROM `colibri_dashboard_admin_employee_summary`
        WHERE completionStatus = 'completed'
        ORDER BY `empCode`,courseId
    ");

        $completed_employee = array();
        if (count($data_completed_employee) > 0) {
            foreach ($data_completed_employee as $key => $value) {
                $completed_employee[$value->empCode][] = $value->courseId;
            }
        }

        #cek mana sub category yang completed
        $completed = array();
        if (count($completed_employee) > 0) {
            foreach ($completed_employee as $empCode => $value_completed_employee) {
                foreach ($sub_category as $subCategoryId => $value_sub_category) {
                    if (count(array_intersect($value_completed_employee, $value_sub_category)) == count($value_sub_category)) {
                        $completed[$empCode] = $subCategoryId;
                    }
                }
            }
        }

        if (count($completed) > 0) {
            #get list supervisor
            $data_supervisor = DB::select("
            SELECT `empSuperiorCode`,`empCode` FROM colibri_employee
        ");
            $supervisor = array();
            if (count($data_supervisor) > 0) {
                foreach ($data_supervisor as $key => $value) {
                    $supervisor[$value->empCode] = $value->empSuperiorCode;
                }
            }

            #get list employee
            $data_employee = DB::select("
            SELECT `empCode`,empName FROM colibri_employee
        ");
            $employee = array();
            if (count($data_employee) > 0) {
                foreach ($data_employee as $key => $value) {
                    $employee[$value->empCode] = $value->empName;
                }
            }

            #get list subcategory
            $data_subcategory = DB::select("
            SELECT subCategoryId,subCategoryName
            FROM `v_course_category`
            GROUP BY subCategoryId
        ");
            $subcategory = array();
            if (count($data_subcategory) > 0) {
                foreach ($data_subcategory as $key => $value) {
                    $subcategory[$value->subCategoryId] = $value->subCategoryName;
                }
            }

            #send
            $send = array();
            foreach ($completed as $empCode => $subCategoryId) {
                #unik index = 
                $unik_index = md5($supervisor[$empCode] . '.' . $empCode . '.' . $subCategoryId);
                #cek inbox
                $inbox = DB::select("
                SELECT
                  COUNT(*) AS total
                FROM `colibri_inbox`
                WHERE msgUnikIndex = ?
            ", [
                    $unik_index
                ]);

                #cek course mana yang ada feedbackId nya
                $feedback = DB::select("
                SELECT 
                  courseId,
                  c.`fullname`,
                  feedbackId 
                FROM
                  `v_course_category` vcc 
                  JOIN colibri_course_addon cca 
                    ON vcc.`courseId` = cca.id 
                  JOIN mdl_course c ON c.id = cca.id
                WHERE subCategoryId = ? 
                  AND ! (ISNULL(feedbackId)) 
                  AND feedbackId != '' 
                LIMIT 1 
            ", [
                    $subCategoryId
                ]);

                $courseId = $feedback[0]->courseId;

                if ($inbox[0]->total == '0') {
                    $title = 'Team course completion';
                    $msgType = 'evaluation';
                    #kirim inbox
                    $templateMessage = 'Hi. Employee {{empCodeEvaluation}} has finished ' . $subcategory[$subCategoryId] . ' and ready to be evaluated.';

                    $inboxMsgCode = $msgType . '.' . $subCategoryId;

                    DB::insert("
                INSERT IGNORE INTO `colibri_inbox_message` (`inboxMsgCode`, `InboxMsgType`,`InboxMsgTitle`,`InboxMsgValue`) 
                VALUES
                  (?,?,?,?)
                ", [
                        $inboxMsgCode,
                        $msgType,
                        $title,
                        $templateMessage
                    ]);

                    $receiver = $supervisor[$empCode];

                    $result = DB::select("
                    INSERT INTO `colibri_inbox` (
                      `inboxMsgCode`,
                      `msgCourseId`,
                      `msgEmpCode`,
                      `msgReceiver`,
                      `msgStatus`,
                      `msgDate`,
                      `msgUnikIndex`
                    )
                    VALUES(
                        ?,?,?,?,'send',NOW(),?
                    )
                ", [
                        $inboxMsgCode,
                        $subCategoryId,
                        $empCode,
                        $receiver,
                        $unik_index
                    ]);

                    #push notif juga gengs
                    $app_id     = 'c22dd684-8d92-4438-8b0c-779fbc927691';
                    $server_key = 'NjFlNWUzZjUtMTQ2My00M2E2LWJiMDUtZDFhMzZiOWUyNTgx';

                    $title   = $title;
                    $content = $templateMessage;
                    $form    = [
                        'app_id'            => $app_id,
                        'data'              => [
                            '__type' => 'evaluation', //menyesuaikan
                            '__id'   => 1, //menyesuaikan
                        ],
                        'included_segments' => ['Subscribed Users'],
                        "small_icon"        => config('hris.url_image') . 'app-assets/images/ico/favicon.png',
                        "headings"          => [
                            'en' => $title, // judul push
                        ],
                        "contents"          => [
                            'en' => $content, // content push
                        ],
                        'filters'           => [
                            [
                                'field'    => 'tag',
                                'key'      => 'empCode', //target spesifik empCode
                                'relation' => '=',
                                'value'    => $receiver, // fungsinya seperti Where di mysql, sifatnya optional filter nya dihubungkan sperti AND
                            ],
                        ]
                    ];
                    $curl   = curl_init('https://onesignal.com/api/v1/notifications');
                    $header = [
                        'Content-Type: application/json; charset=utf-8',
                        'Authorization: Basic ' . $server_key
                    ];
                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($form));
                    //    curl_setopt($curl, CURLOPT_HEADER, true);
                    //print_r(curl_error($curl));
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_exec($curl);
                    curl_close($curl);
                }
            }

            Controller::update_log(['result' => json_encode($this->res_obj)]);

            echo "ok";
        }
        Controller::update_log(['result' => json_encode($this->res_obj)]);
    }

    public function push_notification($empCode, $title, $content, $type, $id)
    {

        $app_id     = 'c22dd684-8d92-4438-8b0c-779fbc927691';
        $server_key = 'NjFlNWUzZjUtMTQ2My00M2E2LWJiMDUtZDFhMzZiOWUyNTgx';
        //$type    = 'daily_quiz';
        //$title   = "TEST";
        //$content = "TEST";
        $client = new Client();
        $res    = $client->request('POST', 'https://onesignal.com/api/v1/notifications', [
            'verify'  => false,
            'headers' => [
                'Content-Type'  => 'application/json; charset=utf-8',
                'Authorization' => 'Basic ' . $server_key,
            ],
            'body'    => json_encode([
                'app_id'            => $app_id,
                'data'              => [
                    '__type' => 'daily_quiz', //menyesuaikan
                    '__id'   => $id, //menyesuaikan
                ],
                'included_segments' => ['Subscribed Users'],
                //"small_icon" => base_url("assets/img/studio.png"), //icon pushnya
                "headings"          => [
                    'en' => $title, // judul push
                ],
                "contents"          => [
                    'en' => $content, // content push
                ],
                'filters'           => [
                    /*[
                'field'    => 'tag',
                'key'      => 'type',
                'relation' => '=',
                'value'    => 'mobile', //menyesuaikan target pushnya kemana
                ],*/
                    [
                        'field'    => 'tag',
                        'key'      => 'empCode', //target spesifik empCode
                        'relation' => '=',
                        'value'    => $empCode, // fungsinya seperti Where di mysql, sifatnya optional filter nya dihubungkan sperti AND
                    ],
                    /*[
            'field'    => 'tag',
            'key'      => 'compCode',
            'relation' => '=',
            'value'    => $compCode,
            ],
            [
            'field'    => 'tag',
            'key'      => 'jobTitleCode',
            'relation' => '=',
            'value'    => $jobTitleCode,
            ],
            [
            'field'    => 'tag',
            'key'      => 'jobLevelCode',
            'relation' => '=',
            'value'    => $jobLevelCode,
            ],*/
                ],
            ]),
        ]);
        return true;
    }

    public function update_pcn_employee(Request $req)
    {

        $validator = Validator::make($req->all(), []);
        if ($validator->fails()) {
            $this->res_obj->message = $validator->messages()->all();
            return $this->res_obj->fail();
        }

        $default_sort = 'pcnEffectiveDate';
        if (isset($req->sort))
            $default_sort = $req->sort;

        $month = 13;
        if (isset($req->month))
            $month = (int)$req->month;

        $empCode = 'all';
        if (isset($req->empCode))
            $empCode = $req->empCode;

        /*$pcn = DB::select("
        SELECT
          `empCode`,
          `pcnEffectiveDate`,
          `compCode`,
          `locCode`,
          `orgCode`,
          `jobTitleCode`,
          `jobLevelCode`
        FROM `colibri_pcn`
        WHERE DATE(pcnEffectiveDate)<= CURRENT_DATE()
        AND DATE(pcnEffectiveDate) >= DATE(NOW() - INTERVAL ".$month." MONTH)
        AND  ( empCode = '".$empCode."' OR 'all' = '".$empCode."' ) 
        ORDER BY ".$default_sort." ASC, empCode ASC
    ");*/

        $pcn = DB::select("
        SELECT * FROM colibri_pcn WHERE pcnId IN (
            SELECT
                MAX(pcnId) AS MAXPCN
            FROM colibri_pcn
            WHERE DATE(pcnEffectiveDate)<= CURRENT_DATE()
            GROUP BY empCode
        )
    ");

        #tampung data pcn terakhir
        $result = array();
        if (count($pcn)) {
            foreach ($pcn as $key => $value) {
                $result[$value->empCode] = $value;
            }
        }

        #update data pcn nya
        $update = array();
        if (count($result)) {
            foreach ($result as $key => $value) {
                $update[] = DB::select("
                UPDATE `colibri_employee`
                SET `empEffectiveDate` = '" . $value->pcnEffectiveDate . "',
                  `jobLevelCode` = '" . $value->jobLevelCode . "',
                  `jobTitleCode` = '" . $value->jobTitleCode . "',
                  `orgCode` = '" . $value->orgCode . "',
                  `locCode` = '" . $value->locCode . "',
                  `compCode` = '" . $value->compCode . "'
                WHERE `empCode` = '" . $value->empCode . "';
            ");
            }
        }
        $this->res_obj->data = '';
        $this->res_obj->message = count($update) . ' data updated';
        Controller::update_log(['result' => json_encode($this->res_obj)]);
        return $this->res_obj->done();
    }

    public function init_daily_question(Request $req, Route $route)
    {

        $validator = Validator::make($req->all(), []);

        if ($validator->fails()) {
            $this->res_obj->message = $validator->messages()->all();
            return $this->res_obj->fail();
        }

        $sql = "
        SELECT 
          `settingCode`,
          `settingName`,
          `settingValue` 
        FROM
          `colibri_setting` 
        WHERE settingCode IN (
            'daily_quiz_publish_time',
            'daily_quiz_expired_time'
          )
        ";
        $setting = DB::select($sql);
        $daily_quiz_expired_time = '';
        $daily_quiz_publish_time = '';
        foreach ($setting as $col => $val) {
            if ($val->settingCode == 'daily_quiz_expired_time')
                $daily_quiz_expired_time = $val->settingValue;
            if ($val->settingCode == 'daily_quiz_publish_time')
                $daily_quiz_publish_time = $val->settingValue;
        }

        if ($daily_quiz_expired_time == '') {
            $this->res_obj->message = 'Daily quiz expired time Not Found';
            Controller::update_log(['result' => json_encode($this->res_obj)]);
            return $this->res_obj->fail();
        }

        if ($daily_quiz_publish_time == '') {
            $this->res_obj->message = 'Daily quiz publish Not Found';
            Controller::update_log(['result' => json_encode($this->res_obj)]);
            return $this->res_obj->fail();
        }

        // echo $daily_quiz_publish_time." ".$daily_quiz_expired_time;
        // die;

        $sql = DB::insert("
            INSERT IGNORE INTO colibri_daily_quiz 
			(`id`, `questionId`, `correctAnswerId`, `questionPoint`, `quizDate`, `quizPublishTime`, `quizExpired` )
            SELECT 
              q1.* 
            FROM
              (SELECT 
                0,
                mdl_question.id AS questionId,
                (SELECT 
                  GROUP_CONCAT(`id`) 
                FROM
                  `mdl_question_answers` 
                WHERE question = mdl_question.id 
                  AND fraction = 1) AS 'id jawaban yang benar',
                defaultmark,
                NOW(),
                ? a,
                ? d 
              FROM
                `mdl_question` 
              WHERE mdl_question.category = 
                (SELECT 
                  qc.id 
                FROM
                  mdl_question_categories qc 
                  JOIN mdl_context ctx 
                    ON ctx.id = qc.contextid 
                  JOIN mdl_course c 
                    ON (
                      ctx.contextlevel = 50 
                      AND c.id = ctx.instanceid
                    ) 
                WHERE c.idnumber = 
                  (SELECT 
                    `settingValue` 
                  FROM
                    `colibri_setting` 
                  WHERE settingCode = 'active_daily_quiz') 
                LIMIT 1) 
              ORDER BY mdl_question.id ASC) q1 
              LEFT JOIN 
                (SELECT 
                  questionId 
                FROM
                  colibri_daily_quiz 
                GROUP BY questionId) q2 
                ON q1.questionId = q2.questionId 
            WHERE q2.questionId IS NULL 
            ORDER BY RAND() 
            LIMIT 1 
        ", [
            $daily_quiz_publish_time, $daily_quiz_expired_time
        ]);

        $quiz = DB::select("SELECT 
            `questionId` 
            FROM
            `colibri_daily_quiz` 
            WHERE quizDate = ?
        ", [
            date("Y-m-d")
        ]);

        if (count($quiz) == 0) {
            $sql = DB::insert("
                INSERT INTO colibri_daily_quiz 
                SELECT 
                  0,
                  mdl_question.id AS questionId,
                  (SELECT 
                    GROUP_CONCAT(`id`) 
                  FROM
                    `mdl_question_answers` 
                  WHERE question = mdl_question.id 
                    AND fraction = 1) AS 'id jawaban yang benar',
                  defaultmark,
                  NOW(),
                  ? a,
                  ? d 
                FROM
                  `mdl_question` 
                WHERE mdl_question.category = 
                  (SELECT 
                    qc.id 
                  FROM
                    mdl_question_categories qc 
                    JOIN mdl_context ctx 
                      ON ctx.id = qc.contextid 
                    JOIN mdl_course c 
                      ON (
                        ctx.contextlevel = 50 
                        AND c.id = ctx.instanceid
                      ) 
                  WHERE c.idnumber = 
                    (SELECT 
                      `settingValue` 
                    FROM
                      `colibri_setting` 
                    WHERE settingCode = 'active_daily_quiz') 
                  LIMIT 1) 
                ORDER BY RAND() 
                LIMIT 1 
            ", [
                $daily_quiz_publish_time,
                $daily_quiz_expired_time
            ]);
        }

        $this->res_obj->message = "Init daily question successfully";

        $msgType = 'daily_quiz';

        $inbox = DB::select("
            SELECT COUNT(*) as total FROM `colibri_inbox`
            WHERE inboxMsgCode = ?
            AND DATE(msgDate) = CURRENT_DATE()
            ", [
            $msgType . '.' . date('ymd')
        ]);

        if ($inbox[0]->total == '0') {
            $quiz = DB::select("
                SELECT 
                `quizPublishTime`,
                FLOOR(`quizExpired` / 3600) AS 'duration' 
                FROM
                `colibri_daily_quiz` 
                WHERE quizDate = ?
                ", [
                date('Y-m-d')
            ]);

            if (count($quiz) > 0) {
                $templateMessage = 'Hi {{empName}} please join the KL academy daily quiz. It\'s fun and get your points! Quiz started at ' . $quiz[0]->quizPublishTime . ' for ' . $quiz[0]->duration . ' hours';

                $inboxMsgCode = $msgType . '.' . date('ymd');

                DB::insert("
                    INSERT IGNORE INTO `colibri_inbox_message` (`inboxMsgCode`, `InboxMsgType`,`InboxMsgTitle`,`InboxMsgValue`) 
                    VALUES
                      (?,?, 'Daily quiz',?)
                ", [
                    $inboxMsgCode,
                    $msgType,
                    $templateMessage
                ]);

                DB::select("
                    INSERT INTO `colibri_inbox`
                    (
                        `inboxMsgCode`,
                        `msgReceiver`,
                        `msgStatus`,
                        `msgDate`
                    )
                    SELECT
                        ?,
                        `empCode`,
                        'send',
                        ?
                    FROM `colibri_employee`
                    ", [
                    $inboxMsgCode,
                    date('Y-m-d H:i:s')
                ]);

                Controller::update_log(['result' => json_encode($this->res_obj)]);

                return $this->res_obj->done();
            } else {
                $this->res_obj->message = 'Daily quiz Not Found';
                Controller::update_log(['result' => json_encode($this->res_obj)]);
                return $this->res_obj->fail();
            }
        }
    }

    public function saveTrainingRecord(Request $request, Route $route){

        $validator = Validator::make($request->all(), []);
        if ($validator->fails()) {
            $this->res_obj->message = $validator->messages()->all();
            return $this->res_obj->fail();
        }

        $ins_trn = '0';
        if (isset($request->ins_trn)) {
            $ins_trn = $request->ins_trn;
        }

        if (isset($request->client)) {
            if ($request->client == 'waskita') {
                return $this->saveTrainingRecord_waskita($request, $route);
            }
        }

        #karena dijalankan pada pagi hari, maka mengambil data nya untuk hari kemarin
        $comp_date = date('Y-m-d', strtotime("yesterday"));
        if (isset($request->comp_date)) {
            $comp_date = $request->comp_date;
        }

        if ($ins_trn == '1') {
            #ANTRIAN NORMAL
            $antrian_normal = DB::select("
        	    SELECT 
				  c.`idnumber` AS TrnCode,
                  SubCategoryId,
				  -- jsonCourseSubCategoryId,
				  `empCode` AS EmpNIK,
				  DATE_FORMAT(cd.startEnroll, '%m/%d/%Y') AS TrRlzStartDate,
				  DATE_FORMAT(cd.completionDate, '%m/%d/%Y') AS TrRlzEndDate,
				  grade AS TrRecScore,
				  cd.courseName AS ResultDesc,
				  grade AS TrRecGrade,
				  cca.training_hours AS TrRecTotalHour 
				FROM
				  `colibri_dashboard_admin_employee_summary` cd 
				  JOIN mdl_course c 
				    ON c.id = courseId 
				  JOIN colibri_course_addon cca 
				    ON cca.id = c.id 
				WHERE (
				    DATE(cd.completionDate) = ?
				  ) 
				  AND grade > 0 
				  AND cd.`grade_source` = 'normal' 
        	", [$comp_date]);

            $antrian_normal_arr = array();
            $total_data_push = 0;
            if (count($antrian_normal) > 0) {
                foreach ($antrian_normal as $key => $value) {
                    // $program = json_decode($value->jsonCourseSubCategoryId,true);
                    // if(count($program) > 0){
                    // 	foreach ($program as $program_id) {
                    DB::insert("
        						INSERT IGNORE INTO `colibri_training_record_cron` (
        						`TrnCode`,
        						`SubCtgCode`,
        						`EmpNIK`,
        						`TrRlzStartDate`,
        						`TrRlzEndDate`,
        						`TrRecScore`,
        						`ResultDesc`,
        						`TrRecGrade`,
        						`TrRecTotalHour`,
        						`insert_date`
        						) 
        						VALUES(?,?,?,?,?,?,?,?,?,CURRENT_DATE())
        						", [
                        $value->TrnCode,
                        // $program_id,
                        $value->SubCategoryId,
                        $value->EmpNIK,
                        $value->TrRlzStartDate,
                        $value->TrRlzEndDate,
                        $value->TrRecScore,
                        $value->ResultDesc,
                        $value->TrRecGrade,
                        $value->TrRecTotalHour
                    ]);
                    $total_data_push++;
                    // }
                    // }

                }
                Telegram::sendMessage('insert antrian push training record ke HRIS, dari completion course employee untuk tanggal ' . $comp_date . ' sebanyak ' . $total_data_push . ' data.');
            }

            #ANTRIAN LH
            DB::insert("
	            INSERT IGNORE INTO `colibri_training_record_cron` (
				  `TrnCode`,
				  `SubCtgCode`,
				  `EmpNIK`,
				  `TrRlzStartDate`,
				  `TrRlzEndDate`,
				  `TrRecScore`,
				  `ResultDesc`,
				  `TrRecGrade`,
				  `TrRecTotalHour`,
				  `source`,
				  `id_lh`,
				  `insert_date`
				) 
				SELECT 
				  `trainingCode` AS 'TrnCode',
				  IFNULL(
				    (SELECT 
				      subCategoryId 
				    FROM
				      `v_course_category` vcc 
				    WHERE vcc.subCategoryName = program 
				      AND vcc.courseId = vc.id 
				    LIMIT 1),
				    '0'
				  ) AS 'SubCtgCode',
				  `empCode` AS 'EmpNIK',
				  DATE_FORMAT(completionDate, '%m/%d/%Y') AS TrRlzStartDate,
				  DATE_FORMAT(completionDate, '%m/%d/%Y') AS TrRlzEndDate,
				  nilai AS TrRecScore,
				  trainingName AS ResultDesc,
				  nilai AS TrRecGrade,
				  vc.`training_hours` AS TrRecTotalHour,
				  'learning_history' AS source,
				  clh.id AS id_lh,
				  CURRENT_DATE() 
				FROM
				  `colibri_learning_history` clh 
				  JOIN v_course vc 
				    ON vc.`idnumber` = trainingCode 
				WHERE is_sent = '0' 
				  AND nilai > '0' 
			");

            #CEK MANA YANG PASTI MENTAL, karena tidak ada subcat code nya - START
            DB::delete("
	            TRUNCATE colibri_training_record_cron_tmp
	        ");

            DB::insert("
	            INSERT INTO `colibri_training_record_cron_tmp` (`id`) 
				SELECT 
				  trc.id 
				FROM
				  `colibri_training_record_cron` trc 
				  JOIN `v_course` vc 
				    ON vc.`idnumber` = trc.`TrnCode` 
				  JOIN `v_course_category` vcc 
				    ON vcc.`courseId` = vc.id 
				    AND trc.`SubCtgCode` = vcc.`subCategoryId` 
				WHERE cron_exec IN ('0', '1') 
				  AND insert_date = CURRENT_DATE() 
				  AND TrRecScore > '0' 
				  GROUP BY trc.id
	        ");

            DB::update("
	            UPDATE 
				  `colibri_training_record_cron` 
				SET
				  cron_exec = '3' 
				WHERE id NOT IN 
				  (SELECT id FROM `colibri_training_record_cron_tmp`) 
			  	AND insert_date = CURRENT_DATE() 
	        ");
            #CEK MANA YANG PASTI MENTAL, karena tidak ada subcat code nya - END
            // die;
        }

        $limit = '1000';
        if (isset($request->limit)) {
            $limit = $request->limit;
        }

        $course = DB::select("
            SELECT 
			  *,trc.id AS id  
			FROM
			  `colibri_training_record_cron` trc 
			  JOIN `v_course` vc 
			    ON vc.`idnumber` = trc.`TrnCode` 
			  JOIN `v_course_category` vcc 
			    ON vcc.`courseId` = vc.id 
			    AND trc.`SubCtgCode` = vcc.`subCategoryId` 
			WHERE cron_exec = '0' 
			  AND source = 'normal' 
			  AND insert_date = CURRENT_DATE() 
			  AND TrRecScore > '0' 
            LIMIT ?
        ", [$limit]);

        $arr_course = array();
        $arr_course_update = [];
        if (count($course) > '0') {
            foreach ($course as $key => $value) {
                $duration = '0';
                if ($value->TrRecTotalHour != null) {
                    $TrRecTotalHour = explode(':', $value->TrRecTotalHour);
                    if (!isset($TrRecTotalHour[0])) $TrRecTotalHour[0] = 0;
                    if (!isset($TrRecTotalHour[1])) $TrRecTotalHour[1] = 0;

                    $duration = $TrRecTotalHour[0] + ($TrRecTotalHour[1] / 60);
                }

                $arr_course[] = array(
                    'TrnCode' => $value->TrnCode,
                    'TrnFamilyCode' => $value->SubCtgCode,
                    'EmpNIK' => $value->EmpNIK,
                    'TrRlzStartDate' => $value->TrRlzStartDate,
                    'TrRlzEndDate' => $value->TrRlzEndDate,
                    'TrRecScore' => $value->TrRecScore,
                    'ResultDesc' => $value->ResultDesc,
                    'TrRecGrade' => $value->TrRecGrade,
                    'TrRecTotalHour' => $duration,
                    'lastOperator' => 'colibri'
                );

                $arr_course_update[] = $value->id;
            }
        }

        $course_lh = DB::select("
            SELECT 
			  *,trc.id AS id
			FROM
			  `colibri_training_record_cron` trc 
			  JOIN `v_course` vc 
			    ON vc.`idnumber` = trc.`TrnCode` 
			  JOIN `v_course_category` vcc 
			    ON vcc.`courseId` = vc.id 
			    AND trc.`SubCtgCode` = vcc.`subCategoryId` 
			WHERE cron_exec = '0' 
			  AND source = 'learning_history' 
			  AND insert_date = CURRENT_DATE() 
			  AND TrRecScore > '0' 
            LIMIT ?
        ", [$limit]);
        $arr_course_lh = array();
        $arr_course_update_lh = [];
        $arr_course_update_tabel_lh = [];
        if (count($course_lh) > '0') {
            foreach ($course_lh as $key => $value) {
                $duration = '0';
                if ($value->TrRecTotalHour != null) {
                    $TrRecTotalHour = explode(':', $value->TrRecTotalHour);
                    if (!isset($TrRecTotalHour[0])) $TrRecTotalHour[0] = 0;
                    if (!isset($TrRecTotalHour[1])) $TrRecTotalHour[1] = 0;

                    $duration = $TrRecTotalHour[0] + ($TrRecTotalHour[1] / 60);
                }

                $arr_course_lh[] = array(
                    'TrnCode' => $value->TrnCode,
                    'TrnFamilyCode' => $value->SubCtgCode,
                    'EmpNIK' => $value->EmpNIK,
                    'TrRlzStartDate' => $value->TrRlzStartDate,
                    'TrRlzEndDate' => $value->TrRlzEndDate,
                    'TrRecScore' => $value->TrRecScore,
                    'ResultDesc' => $value->ResultDesc,
                    'TrRecGrade' => $value->TrRecGrade,
                    'TrRecTotalHour' => $duration,
                    'lastOperator' => 'colibri'
                );

                $arr_course_update_lh[] = $value->id;
                $arr_course_update_tabel_lh[] = $value->id_lh;
            }
        }

        if (count($arr_course) > 0) {
            $client = new Client(); //GuzzleHttp\Client
            $params = array(
                'xClientCode' => config('app.API_HRIS_CLIENT_CODE'),
                'xClientSecret' => config('app.API_HRIS_CLIENT_SECRET'),
                'xTrainingRecordList' => $arr_course
            );

            $filename = public_path() . '/hris/TrainingRecordCourse-' . date("Ymd") . '.csv';
            if (file_exists($filename)) {
                unlink($filename);
            }
            $f = fopen($filename, 'w');
            $cols = array_keys(get_object_vars((object) $arr_course[0]));
            fputcsv($f, $cols);
            foreach ((object)$arr_course as $d => $value) {
                $arr = [];
                foreach ($cols as $col) {
                    $arr[] = $value[$col];
                }
                fputcsv($f, $arr);
            }
            fclose($f);

            try {
                $response = $client->post(
                    config('app.API_HRIS_ENDPOINT') . '?page=saveTrainingRecord',
                    array(
                        'verify' => false,
                        'body' => json_encode($params)
                    )

                );
                $result_json = $response->getBody()->getContents();
                $result['code'] = $response->getStatusCode();
                $result['message'] = implode(',', $arr_course_update);
                $result['result'] = json_decode($result_json, true);

                if (isset($request->debug1)) {
                    echo "<pre>";
                    print_r($arr_course);
                    print_r($result['result']);
                    die;
                }

                #kalau ada kembalian xDataObject, maka jadikan status nya sebagai batal
                if (
                    isset($result['result']['xDataObject'])
                    && count($result['result']['xDataObject']) > 0
                ) {
                    DB::update("
                        UPDATE `colibri_training_record_cron`
                        SET 
                          `cron_exec` = '1',
                          cron_exec_time = '" . date('Y-m-d H:i:s') . "'
                        WHERE `id` IN (" . implode(',', $arr_course_update) . ")
                    ");

                    foreach ($result['result']['xDataObject'] as $key => $value) {
                        DB::update("
	                        UPDATE `colibri_training_record_cron`
	                        SET 
	                          `cron_exec` = '2',
	                          cron_exec_time = '" . date('Y-m-d H:i:s') . "'
	                        WHERE insert_date = CURRENT_DATE()
	                        AND TrnCode = ?
	                        AND SubCtgCode = ? 
	                        AND EmpNIK = ?
	                    ", [
                            $value[0], $value[1], $value[2]
                        ]);
                    }
                } else if (
                    isset($result['result']['xStatusCode']) &&
                    $result['result']['xStatusCode'] == '200'
                ) {
                    DB::update("
                        UPDATE `colibri_training_record_cron`
                        SET 
                          `cron_exec` = '1',
                          cron_exec_time = '" . date('Y-m-d H:i:s') . "'
                        WHERE `id` IN (" . implode(',', $arr_course_update) . ")
                    ");
                } else {
                    DB::update("
                        UPDATE `colibri_training_record_cron`
                        SET 
                          `cron_exec` = '2',
                          cron_exec_time = '" . date('Y-m-d H:i:s') . "'
                        WHERE `id` IN (" . implode(',', $arr_course_update) . ")
                    ");
                }
            } catch (ClientException  $e) {
                $response = json_decode($e->getResponse()->getBody(), true);
                $error_message = $response['message'];
                $result['code'] = $e->getResponse()->getStatusCode();
                $result['message'] = $error_message;
                $result['result'] = $e->getMessage();
            }

            Controller::update_log(['result' => json_encode($result)]);
        }

        if (count($arr_course_lh) > 0) {
            $client = new Client(); //GuzzleHttp\Client
            $params = array(
                'xClientCode' => config('app.API_HRIS_CLIENT_CODE'),
                'xClientSecret' => config('app.API_HRIS_CLIENT_SECRET'),
                'xTrainingRecordList' => $arr_course_lh
            );

            $filename = public_path() . '/hris/TrainingRecordCourseLearningHistory-' . date("Ymd") . '.csv';
            if (file_exists($filename)) {
                unlink($filename);
            }
            $f = fopen($filename, 'w');
            $cols = array_keys(get_object_vars((object) $arr_course_lh[0]));
            fputcsv($f, $cols);
            foreach ((object)$arr_course_lh as $d => $value) {
                $arr = [];
                foreach ($cols as $col) {
                    $arr[] = $value[$col];
                }
                fputcsv($f, $arr);
            }
            fclose($f);
            // die;
            try {
                $response = $client->post(
                    config('app.API_HRIS_ENDPOINT') . '?page=saveTrainingRecord',
                    array(
                        'verify' => false,
                        'body' => json_encode($params)
                    )

                );
                $result_json = $response->getBody()->getContents();
                $result['code'] = $response->getStatusCode();
                $result['message'] = implode(',', $arr_course_update_lh);
                $result['result'] = json_decode($result_json, true);

                if (isset($request->debug2)) {
                    echo "<pre>";
                    print_r($arr_course_lh);
                    print_r($result['result']);
                    die;
                }

                #kalau ada kembalian xDataObject, maka jadikan status nya sebagai batal
                if (
                    isset($result['result']['xDataObject'])
                    && count($result['result']['xDataObject']) > 0
                ) {
                    DB::update("
                        UPDATE `colibri_training_record_cron`
                        SET 
                          `cron_exec` = '1',
                          cron_exec_time = '" . date('Y-m-d H:i:s') . "'
                        WHERE `id` IN (" . implode(',', $arr_course_update_lh) . ")
                    ");

                    DB::update("
                        UPDATE `colibri_learning_history`
                        SET 
                          `is_sent` = '1'
                        WHERE `id` IN (" . implode(',', $arr_course_update_tabel_lh) . ")
                    ");

                    foreach ($result['result']['xDataObject'] as $key => $value) {
                        DB::update("
	                        UPDATE `colibri_training_record_cron`
	                        SET 
	                          `cron_exec` = '2',
	                          cron_exec_time = '" . date('Y-m-d H:i:s') . "'
	                        WHERE insert_date = CURRENT_DATE()
	                        AND TrnCode = ?
	                        AND SubCtgCode = ? 
	                        AND EmpNIK = ?
	                    ", [
                            $value[0], $value[1], $value[2]
                        ]);

                        #update tabel learning history
                        DB::update("
	                        UPDATE `colibri_learning_history`
	                        SET 
	                          `is_sent` = '0'
	                        WHERE trainingCode = ?
	                        AND subcategoryId = ?
	                        AND empCode = ?
	                    ", [
                            $value[0], $value[1], $value[2]
                        ]);
                    }
                } else if (
                    isset($result['result']['xStatusCode']) &&
                    $result['result']['xStatusCode'] == '200'
                ) {
                    DB::update("
                        UPDATE `colibri_training_record_cron`
                        SET 
                          `cron_exec` = '1',
                          cron_exec_time = '" . date('Y-m-d H:i:s') . "'
                        WHERE `id` IN (" . implode(',', $arr_course_update_lh) . ")
                    ");

                    #update tabel learning history
                    DB::update("
                        UPDATE `colibri_learning_history`
                        SET 
                          `is_sent` = '1'
                        WHERE `id` IN (" . implode(',', $arr_course_update_tabel_lh) . ")
                    ");
                } else {
                    #Kalau gagal dari API nya, maka update jadi gagal
                    DB::update("
                        UPDATE `colibri_training_record_cron`
                        SET 
                          `cron_exec` = '2',
                          cron_exec_time = '" . date('Y-m-d H:i:s') . "'
                        WHERE `id` IN (" . implode(',', $arr_course_update_lh) . ")
                    ");
                }
            } catch (ClientException  $e) {
                $response = json_decode($e->getResponse()->getBody(), true);
                $error_message = $response['message'];
                $result['code'] = $e->getResponse()->getStatusCode();
                $result['message'] = $error_message;
                $result['result'] = $e->getMessage();
            }

            Controller::update_log(['result' => json_encode($result)]);
        }

        $result = DB::select("
            SELECT COUNT(id) as 'total' FROM colibri_training_record_cron WHERE cron_exec = '0'
            AND insert_date = CURRENT_DATE() 
        ");

        echo "<pre>";
        print_r(number_format($result[0]->total) . ' data left to send');
        die;
    }

    public function user_course_batch_cache(Request $req, Route $route)
    {
        DB::delete("
    	    DELETE FROM colibri_user_course_batch_cache WHERE tanggal_insert != CURRENT_DATE();
    	");

        DB::insert("
    	    INSERT IGNORE INTO `colibri_user_course_batch_cache` (
			    `batchId`,
			    `courseId`,
			    `batchName`,
			    `batchStartDate`,
			    `batchEndDate`,
			    `batchPostPone`,
			    `cbRefId`,
			    `userId`,
			    `username`,
			    `roleid`,
			    `tanggal_insert`
			)
			SELECT
			    `colibri_course_batch`.`batchId`        AS `batchId`,
			    `colibri_course_batch`.`courseId`       AS `courseId`,
			    `colibri_course_batch`.`batchName`      AS `batchName`,
			    `colibri_course_batch`.`batchStartDate` AS `batchStartDate`,
			    `colibri_course_batch`.`batchEndDate`   AS `batchEndDate`,
			    `colibri_course_batch`.`batchPostPone`  AS `batchPostPone`,
			    `colibri_course_batch`.`cbRefId`        AS `cbRefId`,
			    `mdl_user`.`id`                         AS `userId`,
			    `mdl_user`.`username`                   AS `username`,
			    `mdl_role_assignments`.`roleid`         AS `roleid`,
			    CURRENT_DATE()
			FROM ((((((`colibri_course_batch`
			          JOIN `mdl_course`
			            ON ((`mdl_course`.`id` = `colibri_course_batch`.`courseId`)))
			         JOIN `mdl_enrol`
			           ON ((`mdl_enrol`.`courseid` = `mdl_course`.`id`)))
			        JOIN `mdl_user_enrolments`
			          ON ((`mdl_user_enrolments`.`enrolid` = `mdl_enrol`.`id`)))
			       JOIN `mdl_user`
			         ON ((`mdl_user`.`id` = `mdl_user_enrolments`.`userid`)))
			      JOIN `mdl_context`
			        ON (((`mdl_context`.`instanceid` = `mdl_enrol`.`courseid`)
			             AND (`mdl_context`.`contextlevel` = 50))))
			     JOIN `mdl_role_assignments`
			       ON (((`mdl_role_assignments`.`userid` = `mdl_user`.`id`)
			            AND (`mdl_role_assignments`.`contextid` = `mdl_context`.`id`))))
			WHERE ((`mdl_role_assignments`.`roleid` = 5)
			       AND (`mdl_user_enrolments`.`timecreated` BETWEEN UNIX_TIMESTAMP(`colibri_course_batch`.`batchStartDate`)
			            AND UNIX_TIMESTAMP(`colibri_course_batch`.`batchEndDate`)))
    	");

        $this->res_obj->data = '';
        $this->res_obj->message = 'finish';
        Controller::update_log(['result' => json_encode($this->res_obj)]);
        return $this->res_obj->done();
    }

    public function insert_subcategory_cache(Request $req, Route $route)
    {
        DB::delete("DELETE FROM colibri_rank_subcategory_cache;");
        DB::insert("    	    
			INSERT INTO `colibri_rank_subcategory_cache` (
			    `empCode`,
			    `grade`,
			    `subCategoryId`,
			    `subCategoryName`,
			    `percent_complete`,
			    `inserted_date`
			)
			SELECT
			    empCode,
			    AVG(IFNULL(grade, 0)) AS grade,
			    subCategoryId,
			    subCategoryName,
			    AVG(IFNULL(percent_complete, 0)) AS 'percent_complete',
			    NOW()
			FROM
			    colibri_dashboard_admin_employee_summary cda
			    JOIN `v_course_category` vcc
			        ON vcc.`courseId` = cda.`courseId`
			WHERE subCategoryId > 0
			GROUP BY subCategoryId,
			    empCode
    	");
    }

    public function rank_subcategory_cache(Request $req, Route $route)
    {
        $data = DB::select("
    	    SELECT subCategoryId FROM colibri_rank_subcategory_cache GROUP BY subCategoryId
    	");

        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                DB::insert("SET @rank = 0;");
                DB::insert("    	    
					INSERT INTO `colibri_rank_subcategory_cache` (
					    `empCode`,
					    `subCategoryId`,
					    `rank`
					)
					(SELECT
					    empCode,subCategoryId,@rank := @rank + 1 AS `rank`
					FROM
					    (SELECT
					        empCode,subCategoryId,sc.grade AS total_score
					    FROM
					        colibri_rank_subcategory_cache sc
					    WHERE subCategoryId = ?
					    ORDER BY total_score DESC,
					        empCode ASC) a)
					ON DUPLICATE KEY UPDATE  `rank` = values(`rank`)
		    	", [$value->subCategoryId]);
            }
        }
    }

    public function exec_dashboard_cron(Request $request)
    {
        if (!isset($request->limit)) {
            $limit = 100;
        } else {
            $limit = $request->limit;
        }
        // echo $limit;
        $start = $this->set_time();
        $exec = DB::select("
    		SELECT
    		`settingValue`
    		FROM `colibri_setting`
    		WHERE settingCode = 'cron_dashboard_completion'
    		");

        if (isset($exec[0]->settingValue) && $exec[0]->settingValue == '1') {
            $redundan = DB::select("
    			SELECT
    			ANY_VALUE(id) AS id
    			FROM `colibri_dashboard_cron`
    			WHERE is_exec = '0'
    			GROUP BY empCode
    			HAVING COUNT(1) > 1
    			");

            if (count($redundan) > 0) {
                $redundan_list = array();
                if (count($redundan) > 0) {
                    foreach ($redundan as $key => $value) {
                        $redundan_list[] = $value->id;
                    }
                }

                $redundan = implode(',', $redundan_list);
                DB::delete("
    				DELETE FROM colibri_dashboard_cron WHERE id IN (" . $redundan . ")
    				");
            }

            // $data = DB::select("
            // 	SELECT 
            // 	id,
            // 	empCode 
            // 	FROM
            // 	`colibri_dashboard_cron` 
            // 	WHERE is_exec = '0' 
            // 	AND DATE(insert_date) = CURRENT_DATE()
            // 	LIMIT ? 
            // 	",[$limit]);
            $data = DB::select("
    			SELECT 
    			id,
    			empCode 
    			FROM
    			`colibri_dashboard_cron` 
    			WHERE is_exec = '0' 
    			LIMIT ? 
    			", [$limit]);

            if (count($data) > 0) {
                $client = new Client(
                    ['base_uri' => config('app.APP_URL'), 'verify' => false]
                );

                $client_colibri = new Client(
                    ['base_uri' => config('app.URL_WEB'), 'verify' => false]
                );

                foreach ($data as $key => $value) {
                    try {
                        $params = [
                            'del_dsh' => '1',
                            'empCode' => $value->empCode,
                            // 'ins_ldr' => '1',
                            //'ins_loc' => '1',
                            'telegram_send' => false,
                            'source_function' => 'exec_dashboard_cron'
                        ];
                        $response = $client->request(
                            'GET',
                            'dashboard/new_insert_dashboard?' . http_build_query($params),
                            [
                                'headers' => ['secret' => config('app.API_SECRET')],
                            ]
                        );

                        dump($response);

                        DB::update("
    						UPDATE `colibri_dashboard_cron`
    						SET 
    						`is_exec` = '1',
    						`time_exec` = NOW(),
    						`result_exec` = ?
    						WHERE `id` = ?
    						", [$response->getStatusCode(), $value->id]);
                    } catch (ClientException  $e) {
                        DB::update("
    						UPDATE `colibri_dashboard_cron`
    						SET 
    						`is_exec` = '1',
    						`time_exec` = NOW(),
    						`result_exec` = ?
    						WHERE `id` = ?
    						", ['error : ' . $e->getResponse()->getStatusCode(), $value->id]);
                    }

                    if (config('app.GENERATE_SERTIFIKAT') == '1') {
                        $client_colibri->request(
                            'GET',
                            'cron/generateCert?empCode=' . $value->empCode,
                            []
                        );
                    }
                }
            } else {
                echo 'all data ok';
            }

            #generate sertifikat yang ga sempurna
            $cert_kosong = DB::select("
    			SELECT * FROM colibri_employee_certificate WHERE filename = '' LIMIT 5
    			");

            if (count($cert_kosong) > 0) {
                $client_colibri = new Client(
                    ['base_uri' => config('app.URL_WEB'), 'verify' => false]
                );
                foreach ($cert_kosong as $key => $value) {
                    if (config('app.GENERATE_SERTIFIKAT') == '1') {
                        $client_colibri->request(
                            'GET',
                            'cron/generateCert?empCode=' . $value->empCode,
                            []
                        );
                    }
                }
            }
        }
        $end = $this->set_time();
        echo $this->get_time_exec($start, $end);
    }

    public function insert_dashboard(Request $request){
    	echo "Proses dimulai pada ".date('d M Y H:i:s')."<br><br>";

        echo "KEY ME".PHP_EOL;
        $memory_limit = ini_get('memory_limit');
        // if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
        //     if ($matches[2] == 'M') {
        //         $memory_limit = $matches[1] * 1024 * 1024; // nnnM -> nnn MB
        //     } else if ($matches[2] == 'K') {
        //         $memory_limit = $matches[1] * 1024; // nnnK -> nnn KB
        //     }
        // }

        // $ok = ($memory_limit >= 64 * 1024 * 1024); // at least 64M?

        echo '<phpmem>';
        echo '<val>' . $memory_limit . '</val>';
        // echo '<ok>' . ($ok ? 1 : 0) . '</ok>';
        echo '</phpmem>'.PHP_EOL;
        $time_start = $this->set_time();
        
        $validator = Validator::make($request->all(), []);

        if ($validator->fails()) {
            $this->res_obj->message = $validator->messages()->all();
            return $this->res_obj->fail();
        }

        # ---------------------- INIT PARAMETER -------------------- #

        $del_dsh = '0';
        $ins_ldr = '0';
        $ins_loc = '0';
        $ins_rank = '0';
        $filter_empCode = 'all';
        $telegram_send = true;
        $limit = 200;

        if(isset($request->del_dsh)){
            $del_dsh = $request->del_dsh;
        }

        if(isset($request->ins_ldr)){
            $ins_ldr = $request->ins_ldr;
        }

        if(isset($request->ins_loc)){
            $ins_loc = $request->ins_loc;
        }

        if(!empty($request->empCode)){
            $filter_empCode = $request->empCode;
        }

        if(isset($request->telegram_send)){
            $telegram_send = $request->telegram_send;
        }

        if(isset($request->limit)){
            $limit = $request->limit;
        }

        if(isset($request->debug)){
            DB::enableQueryLog();            
        } 
        # ---------------------- INIT PARAMETER -------------------- #
        try{

        $bypass_proses = 0;
        if($filter_empCode != 'all'){
        	$bypass_proses = 1;
        }

        if($del_dsh == '1'){
        	$start = $this->set_time();
            DB::select("
                DELETE FROM colibri_dashboard_admin_employee_summary 
                WHERE (empCode = ? OR 'all' = ?)
                ",[$filter_empCode,$filter_empCode]);

            DB::delete("DELETE FROM `colibri_dashboard_prereq_program`
                WHERE (empCode = ? OR 'all' = ?)
                ",[$filter_empCode,$filter_empCode]);

            DB::delete("DELETE FROM `colibri_dashboard_program_completed`
                WHERE (empCode = ? OR 'all' = ?)
                ",[$filter_empCode,$filter_empCode]);

            DB::delete("
                DELETE FROM colibri_dashboard_proses
                WHERE (empCode = ? OR 'all' = ?)
                ",[$filter_empCode,$filter_empCode]);

            if($filter_empCode == 'all'){
            	DB::delete("
	                DELETE
					FROM `colibri_dashboard_cron`
					WHERE DATE(insert_date) != CURRENT_DATE()
	            ");
            }            

            #cek apakah sudah ada antrian untuk dieksekusi hari ini
	        $antrian_eksekusi = DB::select("
	            SELECT COUNT(*) AS 'total' FROM colibri_dashboard_proses
	            WHERE `date` = CURRENT_DATE()
	            AND (empCode = ? OR 'all' = ?)            
	        ",[$filter_empCode,$filter_empCode]);
	        echo "<pre>Antrian Eksekusi: ";
	        print_r($antrian_eksekusi);
	        echo "</pre>";
	        if($antrian_eksekusi[0]->total == '0'){
	            DB::insert("
	                INSERT INTO `colibri_dashboard_proses` (`date`, `empCode`, `proses`) 
	                SELECT 
	                  CURRENT_DATE(),
	                  empCode,
	                  '0' 
	                FROM
	                  `colibri_employee` 
	                WHERE (ISNULL(empTermDate) 
	                  OR DATE(empTermDate) >= CURRENT_DATE())
	                  AND (empCode = ? OR 'all' = ?)
	            ",[$filter_empCode,$filter_empCode]);
	        }

            $end = $this->set_time();
            $return['delete dashboard'] = ", Time : ".$this->get_time_exec($start,$end)."<br>";   

            echo"<pre>";
	        print_r($return);
	        echo"</pre>";
	        
	        Controller::update_log(['result' => json_encode($return)],'',false);   
	        if($bypass_proses === 0){
	        	Telegram::sendMessage('insert dashboard dimulai');
	        	die;
	        }
        }          
        
        $total_data_query = DB::select("
            SELECT 
              COUNT(*) AS 'total' 
            FROM
              `colibri_dashboard_proses`
        ");
        $total_data = $total_data_query[0]->total;
        echo "<pre>Total dashboard_proses: ".$total_data."</pre>";

        $proses_query = DB::select("
            SELECT
			  MIN(proses) as min_proses, 
              MAX(`proses`) AS max_proses
			FROM `colibri_dashboard_proses`
        ");
        echo "Proses Query";
        echo "<pre>";
        print_r($proses_query);
        echo "</pre>";
        $proses = '0';
        if(count($proses_query) > 0){
        	foreach ($proses_query as $key => $value) {
        		if($value->min_proses != $value->max_proses){
        			$proses = $value->min_proses;

        			#kalau proses insert status complete program, maka limit dipaksa besar
        			if($proses == '1')$limit = 2000;

        		}else if($value->min_proses == $value->max_proses){
        			$proses = $value->max_proses;
        			if($proses == '1'){
        				// Telegram::sendMessage('insert dashboard selesai');
        			}
        			if($proses == '2'){
        				// Telegram::sendMessage('insert status program selesai');
        			}
        			if($proses == '3'){
                        $limit=5000;
        				// Telegram::sendMessage('insert status prerequisite selesai');
        			}
        			if($proses == '4'){
        				// Telegram::sendMessage('insert leaderboard selesai');
        			}
        		}else{
        			$proses = '0';
        		}
        	}
        }
        if($proses=='0'){
            $limit=200;
        }

        if($proses=='3'){
            $limit=5000;
        }
        
        #get data user yang akan dieksekusi
        $start = $this->set_time();
		$emp_eksekusi = DB::select("
            SELECT empCode FROM `colibri_dashboard_proses` WHERE proses = ? 
            AND (empCode = ? OR 'all' = ?)
            LIMIT ?
        ",[$proses,$filter_empCode,$filter_empCode,$limit]);

        $emp_userid = DB::select("
            SELECT distinct virtual_userid FROM colibri_virtual_nip WHERE (empCode = ? OR 'all' = ?)
        ",[$filter_empCode,$filter_empCode]);

        $list_employee = "''";
        $list_employee_array = [];
        if(count($emp_eksekusi) > 0){
            foreach ($emp_eksekusi as $key => $value) {
                $list_employee_array[]=$value->empCode;
                // $value->empCode = "'".$value->empCode."'";
                // if($list_employee == '')$list_employee = $value->empCode;
                // else $list_employee = $list_employee.",".$value->empCode;
            }
            $list_employee = $this->array_to_where_in($list_employee_array);
        }

        $list_userid = "''";
        $list_userid_array = [];
        if (count($emp_userid)) {
            foreach ($emp_userid as $key => $value) {
                $list_userid_array[]=$value->virtual_userid;
            }
            $list_userid = $this->array_to_where_in($list_userid_array);
        }

        echo "<pre>Total emp akan diekseskusi: ".count($emp_eksekusi)."</pre>";

        $spv = DB::select("
            SELECT empSuperiorCode FROM `colibri_employee` WHERE 
            empCode IN (".$list_employee.")
        ");

        $list_spv = "''";
        if(count($spv) > 0){
            foreach ($spv as $key => $value) {
                $value->empSuperiorCode = "'".$value->empSuperiorCode."'";
                if($list_spv == '')$list_spv = $value->empSuperiorCode;
                else $list_spv = $list_spv.",".$value->empSuperiorCode;
            }
        }        

        $data_prereq = DB::select("
            SELECT 
            `id`,
            `courseId`,
            `requiredCourseId`,
            (SELECT 
            fullname 
            FROM
            mdl_course mc 
            WHERE mc.id = requiredCourseId) AS requiredCourseName 
            FROM
            `colibri_course_prereq` 
            ");

        $prereq = array();
        if(count($data_prereq) > 0){
            foreach ($data_prereq as $key => $value) {
                $prereq[$value->courseId][$value->requiredCourseId] = $value->requiredCourseName;
            }
        }       
        echo "<pre>Total emp prereq: ".count($data_prereq)."</pre>";

        #array employee
        $data_employee = DB::select("
            SELECT empCode,empName from colibri_employee
            WHERE empCode IN (".$list_employee.") OR empCode IN (".$list_spv.")
            ");

        $arr_employee = array();
        if(count($data_employee) > 0){
            foreach ($data_employee as $key => $value) {
                $arr_employee[$value->empCode] = $value->empName;
            }
        }

        

        

        #ARRAY ID DAN LABEL PROGRAM
        $v_course_category = DB::select("
            SELECT 
              courseId,
              subCategoryId,
              subCategoryName 
            FROM
              `v_course_category` vcc 
              JOIN v_course vc 
                on vcc.`courseId` = vc.`id` 
            ");
        $course_category = array();
        $course_category_label = array();

        if(count($v_course_category) > 0){
            foreach ($v_course_category as $key => $value) {
                $course_category[$value->subCategoryId][] = $value->courseId;
            }

            foreach ($v_course_category as $key => $value) {
                $course_category_label[$value->subCategoryId] = $value->subCategoryName;
            }
        }
        $end = $this->set_time();
		$return['fetch_data'] = ", Time : ".$this->get_time_exec($start,$end)."<br>";     

        $start = $this->set_time();
        $parameter_query = [];
        $jumlah_insert_dashboard = 0;

        if($proses == '0' || $bypass_proses === 1){
            echo "<pre>Mulai loop insert dashboard</pre>";
            // $jumlah_insert_dashboard=0;
            // $list_employee_chunk = array_chunk($list_employee_array, 1000);
            // echo "<pre>Split Chunk: ".count($list_employee_chunk)."</pre>";
            // foreach ($list_employee_chunk as $key => $list_employee_chunk_item) {
                // echo "<pre>Loop #".$key."</pre>";
                // $list_employee_chunk_query = $this->array_to_where_in($list_employee_chunk_item);
                $jumlah_insert_dashboard = $this->loop_insert_dashboard($course_category,$prereq,$course_category_label,$arr_employee,$list_employee,$list_userid);
            // }
            // echo "<pre>Selesai loop insert dashboard</pre>";
        }
        
        $end = $this->set_time();
		$return['insert_dashboard'] = $this->get_time_exec($start,$end).", Total : ".$jumlah_insert_dashboard." data <br>";     

        $start = $this->set_time();
        if($proses == '1' || $bypass_proses === 1){
            echo "<pre>Mulai insert completed program</pre>";
            $this->insert_program_completed($course_category,$list_employee);
        }
        $end = $this->set_time();
		$return['insert_status_program'] = ", Time : ".$this->get_time_exec($start,$end)."<br>";     

        #EXEC PREREQ Completion status
        $start = $this->set_time();
		$jumlah_update_prereq = '0';
        $jumlah_insert_prereq_program = '0';
        $prereq_comp_status = []; 
        if($proses == '2' || $bypass_proses === 1){   
            echo "<pre>Mulai insert prereq </pre>";
            $jumlah_update_prereq = $this->insert_prereq_course($list_employee);
            // $jumlah_insert_prereq_program = $this->insert_prereq_program($list_employee,$course_category_label);
            DB::update("
                UPDATE `colibri_dashboard_proses`
                SET 
                  `proses` = '3'
                WHERE `empCode` IN (".$list_employee.") 
            ");              
        }
        $end = $this->set_time();
		$return['process_prereq'] = 
			" Total prereq course  : ".$jumlah_update_prereq.
			", Total prereq program  : ".$jumlah_insert_prereq_program.
            ", Total time  : ".$this->get_time_exec($start, $this->set_time()).
			"<br>"
		;            

        $start = $this->set_time();
        if($proses == '3' || $ins_ldr === 1 ){
            $this->insert_colibri_leaderboard($list_employee);
        }
        $end = $this->set_time();
		$return['leaderboard'] = "";     
        $start = $this->set_time();

        if($proses == '4' || $ins_rank === 1 ){
            $this->insert_colibri_rank($list_employee);
        }
        
        if($proses == '5' || $ins_loc === 1 ){
            echo "<pre>Mulai insert location </pre>";
            try{
                $this->insert_location_summary_by_comp();
            }catch(Exception $e){
                Controller::update_log(['result' => json_encode($return)],'',false);
                Telegram::sendMessage('error insert location'.json_encode($return));
            }
            DB::update("
                UPDATE `colibri_dashboard_proses`
                SET 
                  `proses` = '5'
            ");  
        }
        $end = $this->set_time();
		$return['location_summary'] = ", Time : ".$this->get_time_exec($start,$end)."<br>";
        
        echo "<pre>Update data </pre>";
        $data_masuk = DB::select("
            SELECT 
              COUNT(*) AS total 
            FROM
              `colibri_dashboard_admin_employee_summary`
            WHERE (empCode = ? OR 'all' = ?) 
        ",[$filter_empCode,$filter_empCode]);

        /*$result = 'start to loop start = '.($loop_start - $time_start).' s<br>
        insert dashboard process = '.($loop_end - $loop_start).' s ( '.$jumlah_insert_dashboard.' data)<br>
        prereq start process = '.($prereq_end - $prereq_start).' s ( '.$jumlah_update_prereq.' data prereq course. '.$jumlah_insert_prereq_program.' data prereq program)<br>
        completed program process = '.($comp_program_end - $comp_program_start).' s<br>
        leaderboard process = '.($leaderboard_end - $leaderboard_start).' s<br>
        loc summary process = '.($location_summary_end - $location_summary_start).' s<br>
        Total  process = '.($time_end - $time_start).' s<br>
        Data sudah masuk = '.$data_masuk[0]->total.'<br>
        '
        ;*/

        echo"<pre>";
        print_r($return);
        echo"</pre>";

        if(isset($request->debug)){
            dd(DB::getQueryLog());            
        } 
        
        if(!in_array($proses, [5])){
        	Controller::update_log(['result' => json_encode($return)],'',false);
        }

        }catch(\Exception $e){
            var_dump($e->getMessage());
            Controller::update_log(['result' => substr($e->getMessage(),0,1000)],'',false);
        } catch(\Illuminate\Database\QueryException $ex){
            var_dump($ex->getMessage());
            Controller::update_log(['result' => substr($ex->getMessage(),0,1000)],'',false);
        }
        
    }
}
