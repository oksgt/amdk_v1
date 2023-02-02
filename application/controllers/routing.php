<?php

$version = 'v1a';

#--------------------------------ACTION--------------------------------------#
Route::post('me/login', $version.'\action\AuthController@login');
Route::post('me/login_dev', $version.'\action\AuthController@login_dev');
Route::post('me/clear_jwt', $version.'\action\AuthController@clear_jwt');
Route::post('me/get_jwt', $version.'\action\AuthController@get_jwt');

Route::get('testmongodb', $version.'\data\LogController@testmongodb');

#IMPORT
Route::post('import/import_company', $version.'\action\ImportMasterController@import_company');
Route::post('import/import_organization', $version.'\action\ImportMasterController@import_organization');
Route::post('import/import_job_title_family', $version.'\action\ImportMasterController@import_job_title_family');
Route::post('import/import_job_title_function', $version.'\action\ImportMasterController@import_job_title_function');
Route::post('import/import_job_title', $version.'\action\ImportMasterController@import_job_title');
Route::post('import/import_job_level', $version.'\action\ImportMasterController@import_job_level');
Route::post('import/import_location', $version.'\action\ImportMasterController@import_location');
Route::post('import/import_pcn', $version.'\action\ImportMasterController@import_pcn');
Route::post('import/import_employee', $version.'\action\EmployeeController@import_employee');

Route::get('colibricore/attend_inclass', $version.'\action\MeController@attend_inclass');

#DAILY QUIZ
if(config('app.DAILY_QUIZ_MULTICOMPANY') == false){
	Route::post('daily_quiz/submit_answer', $version.'\action\DailyQuizController@submit_answer');
	Route::post('daily_quiz/get_question', $version.'\data\DailyQuizController@get_question');
	Route::post('daily_quiz/init_daily_question', $version.'\cron\DailyQuizController@init_daily_question');
	Route::get('daily_quiz/init_daily_question', $version.'\cron\DailyQuizController@init_daily_question');
}else{
	Route::post('daily_quiz/submit_answer', $version.'\action\DailyQuizController@submit_answer_per_bu');
	Route::post('daily_quiz/get_question', $version.'\data\DailyQuizController@get_question_per_bu');
	Route::post('daily_quiz/init_daily_question', $version.'\cron\DailyQuizController@init_daily_question_per_bu');
	Route::get('daily_quiz/init_daily_question', $version.'\cron\DailyQuizController@init_daily_question_per_bu');
}

Route::get('daily_quiz/init_daily_question_bu', $version.'\cron\DailyQuizController@init_daily_question_bu');

Route::post('colibricore/absen', $version.'\action\MeController@absen');
Route::post('moodle/absen', $version.'\action\MeController@absen');

Route::post('inbox/updateReadMessageStatus', $version.'\action\InboxController@updateReadMessageStatus');
Route::post('inbox/removeMessageAfter6Months', $version.'\action\InboxController@removeMessageAfter6Months');
Route::post('inbox/deleteMessage', $version.'\action\InboxController@deleteMessage');

Route::post('feedback/submit_feedback_answer', $version.'\action\FeedbackController@submit_feedback_answer');
Route::post('me/enroll_optional', $version.'\action\MeController@enroll_optional');
Route::post('me/unenroll_optional', $version.'\action\MeController@unenroll_optional');
Route::post('xls', $version.'\action\LearningHistoryController@import_learning_history');

Route::get('generate_lh', $version.'\cron\LearningHistoryController@generate_lh');
Route::get('learning_history/download/{file?}', $version.'\action\LearningHistoryController@download_lh');

Route::post('importMatrix', $version.'\action\MeController@import_matrix');
Route::post('importMatrixJTtoProgram', $version.'\action\MeController@import_matrix_jt_to_program');



#--------------------------------GET DATA------------------------------------#
// Route::post('colibricore/calender', $version.'\data\MeController@get_calendar');

Route::post('colibricore/calender', $version.'\data\MeController@get_calendar_new');

#WEBINAR
Route::post('webinar/list', $version.'\data\WebinarController@list');
Route::post('webinar/register', $version.'\data\WebinarController@register');
Route::post('webinar/join', $version.'\data\WebinarController@join');

#INBOX
Route::post('inbox/getMessage', $version.'\data\InboxController@getInboxMessage');
Route::post('inbox/getNews', $version.'\data\InboxController@getNews');
Route::post('inbox/getNewsDetail', $version.'\data\InboxController@getNewsDetail');
Route::post('inbox/getMessageById', $version.'\data\InboxController@getInboxMessageById');
Route::post('inbox/getInboxDetail', $version.'\data\InboxController@getInboxDetail');

#FEEDBACK
Route::post('feedback/get_employee_to_evaluate', $version.'\data\FeedbackController@get_employee_to_evaluate');
Route::post('feedback/get_feedback_question', $version.'\data\FeedbackController@get_feedback_question');

Route::post('me/my_course', $version.'\data\MeController@my_course');
Route::post('me/my_program', $version.'\data\MeController@my_program');
Route::post('me/my_profile', $version.'\data\MeController@my_profile');
Route::post('me/my_certificate', $version.'\data\MeController@my_certificate');
Route::post('me/my_dashboard_popup', $version.'\data\MeController@my_dashboard_summary');

Route::post('me/next_level', $version.'\data\DashboardController@next_level_info');

Route::post('me/get_course_content', $version.'\data\MeController@get_course_content');
Route::post('me/get_course_optional', $version.'\data\MeController@get_course_optional');
Route::post('me/get_course_ondemand', $version.'\data\MeController@get_course_ondemand');
Route::post('me/get_course_strengthening', $version.'\data\MeController@get_course_strengthen');
Route::post('me/allow_access_optional_course', $version.'\data\MeController@allow_access_optional_course');
Route::post('me/is_supervisor', $version.'\data\MeController@is_supervisor');
Route::post('me/get_employee_foto', $version.'\data\MeController@get_employee_foto');
Route::post('me/get_leaderboard', $version.'\data\MeController@get_leaderboard_prd');
Route::post('me/allow_access_prerequisite_course', $version.'\data\MeController@allow_access_prerequisite_course');
Route::post('me/get_program_enrolled', $version.'\data\MeController@get_program_enrolled');

Route::get('getserverLoad', $version.'\data\ServerController@get_serverLoad');
Route::post('company/get_style', $version.'\data\MeController@get_style');
Route::post('structure_course', $version.'\data\MeController@structure_course');
Route::post('log/search', $version.'\data\LogController@search');
Route::post('log/export', $version.'\data\LogController@export');
Route::post('dashboard/dashboard', $version.'\data\DashboardController@dashboard');
Route::post('dashboard/dashboard_v2', $version.'\data\DashboardController@dashboard_v2');

Route::post('dashboard/test_2', $version.'\data\DashboardController@test_2');

Route::post('dashboard/get_company', $version.'\data\DashboardController@get_company');
Route::post('dashboard/get_location', $version.'\data\DashboardController@get_location');
Route::post('dashboard/get_location_summary_by_comp', $version.'\data\DashboardController@get_location_summary_by_comp');
Route::post('dashboard/get_job_title_by_comp_and_loc', $version.'\data\DashboardController@get_job_title_by_comp_and_loc');
Route::post('dashboard/get_course_by_comp', $version.'\data\DashboardController@get_course_by_comp');
Route::post('dashboard/get_completion_status', $version.'\data\DashboardController@get_completion_status');
Route::post('dashboard/get_employee_sumary', $version.'\data\DashboardController@get_employee_sumary');
Route::post('dashboard/get_course_by_subcategory', $version.'\data\DashboardController@get_course_by_subcategory');

Route::post('dashboard/dashboard_alert', $version.'\data\DashboardController@dashboard_alert');

Route::post('dashboard/get_webinar', $version.'\data\DashboardController@get_webinar');

Route::post('analytic/data_by_employee', $version.'\data\AnalyticController@data_by_employee');
Route::post('analytic/gagal_kirim_lh', $version.'\data\AnalyticController@gagal_kirim_lh');
Route::post('analytic/skor_salah', $version.'\data\AnalyticController@skor_salah');
Route::post('analytic/data_employee', $version.'\data\AnalyticController@data_employee');
Route::post('analytic/insert_employee', $version.'\action\AnalyticController@insert_employee');
Route::post('analytic/update_employee', $version.'\action\AnalyticController@update_employee');
Route::post('analytic/data_master', $version.'\data\AnalyticController@data_master');

Route::post('analytic/data_course', $version.'\data\AnalyticController@data_course');
Route::post('analytic/delete_cache_activity', $version.'\action\AnalyticController@delete_cache_activity');

Route::post('quiz/quiz_get_user_attempts', $version.'\data\QuizController@quiz_get_user_attempts');
Route::post('quiz/quiz_get_attempt_review', $version.'\data\QuizController@quiz_get_attempt_review');
Route::post('me/my_team', $version.'\data\MeController@my_team');
Route::post('me/my_team_order', $version.'\data\MeController@my_team_order');
Route::post('me/my_team_detail', $version.'\data\MeController@my_team_detail');
Route::post('me/my_team_detail_order', $version.'\data\MeController@my_team_detail_order');
Route::post('me/hide_menu', $version.'\data\MeController@hide_menu');
Route::post('me/my_team_certificate', $version.'\data\MeController@my_team_certificate');
Route::post('me/trainer_area', $version.'\data\MeController@trainer_area');
Route::post('me/point_detail', $version.'\data\MeController@point_detail');
Route::post('me/course_detail', $version.'\data\MeController@course_detail');

Route::post('me/recert_accept_confirm', $version.'\action\MeController@recert_accept_confirm');

#----------------------------------CRON--------------------------------------#
Route::post('colibricore/register', $version.'\cron\UserController@reg_moodle_user');
Route::get('colibricore/register', $version.'\cron\UserController@reg_moodle_user');
Route::get('colibricore/update_pcn_employee', $version.'\cron\EmployeeController@update_pcn_employee');

#EXPORT HRIS
Route::get('export/saveTrainingType', $version.'\cron\LearningHistoryController@saveTrainingType');
Route::get('export/saveTrainingGroup', $version.'\cron\LearningHistoryController@saveTrainingGroup');
Route::get('export/saveTrainingMaster', $version.'\cron\LearningHistoryController@saveTrainingMaster');
Route::get('export/saveTrainingRecord', $version.'\cron\LearningHistoryController@saveTrainingRecord');

Route::get('export/saveTrainingRecordRecovery', $version.'\cron\LearningHistoryController@saveTrainingRecordRecovery');

Route::post('daily_quiz/push_quiz', $version.'\cron\PushController@push_quiz');
Route::get('daily_quiz/push_quiz', $version.'\cron\PushController@push_quiz');

Route::post('notif/push_cron_monitoring', $version.'\cron\PushController@push_cron_monitoring');
Route::get('notif/push_cron_monitoring', $version.'\cron\PushController@push_cron_monitoring');

if(config('app.VIRTUAL_NIP')){
	Route::post('colibricore/autoenroll/{offset}', $version.'\cron\AutoEnrollController@autoenroll_vnip');
	Route::get('colibricore/autoenroll/{offset}', $version.'\cron\AutoEnrollController@autoenroll_vnip');
	#Temporary
	Route::get('colibricore/autoenroll2', $version.'\cron\AutoEnrollController@autoenroll_vnip2');
} else {
	Route::post('colibricore/autoenroll', $version.'\cron\AutoEnrollController@autoenroll');
	Route::get('colibricore/autoenroll', $version.'\cron\AutoEnrollController@autoenroll');	
}
Route::get('colibricore/autobatch', $version.'\cron\AutoEnrollController@autobatch');

Route::get('colibricore/test_', $version.'\cron\AutoEnrollController@test_');

Route::get('recertification', $version.'\cron\RecertificationController@recertification');
Route::get('recertification_mandatory', $version.'\cron\RecertificationController@recertification_mandatory');
// recertification_mandatory
Route::get('recertification/confirmation/{id}', $version.'\cron\RecertificationController@confirmation');
Route::post('recertification/accept', $version.'\data\DashboardController@accept_confirm');
// Route::get('recertification/accept', $version.'\data\RecertificationController@accept');
// DashboardController

Route::get('colibricore/inclass_confirmation', $version.'\cron\ClassroomController@inclass_confirmation');
Route::get('colibricore/push_evaluation', $version.'\cron\PushController@push_evaluation');
Route::get('dashboard/insert_dashboard', $version.'\cron\DashboardController@insert_dashboard');

Route::get('dashboard/new_insert_dashboard', $version.'\cron\DashboardControllerNew@insert_dashboard');
Route::get('dashboard/new_exec_dashboard_cron', $version.'\cron\DashboardControllerNew@exec_dashboard_cron');


Route::get('dashboard/testInsertLeaderBoard', $version.'\cron\DashboardController@testInsertLeaderBoard');

Route::get('cleansing', $version.'\cron\DashboardController@cleanse_data_cron');


Route::get('dashboard/processdashboard', $version.'\cron\DashboardController_V2@process_dashboard');
Route::get('dashboard/normalize_dashboard', $version.'\cron\DashboardController@normalize_dashboard');
Route::get('dashboard/exec_dashboard_cron', $version.'\cron\DashboardController@exec_dashboard_cron');
Route::get('user_course_batch_cache', $version.'\cron\CacheController@user_course_batch_cache');
Route::get('insert_subcategory_cache', $version.'\cron\CacheController@insert_subcategory_cache');
Route::get('rank_subcategory_cache', $version.'\cron\CacheController@rank_subcategory_cache');
Route::get('insert_notifikasi_resertifikasi', $version.'\cron\EmployeeController@insert_notifikasi_resertifikasi');
Route::get('insert_notifikasi_course_complete', $version.'\cron\EmployeeController@insert_notifikasi_course_complete');
Route::get('insert_notifikasi_certificate_publish', $version.'\cron\EmployeeController@insert_notifikasi_certificate_publish');
Route::get('insert_notifikasi_course_enroll', $version.'\cron\EmployeeController@insert_notifikasi_course_enroll');
Route::get('insert_notifikasi_course_limited_time', $version.'\cron\EmployeeController@insert_notifikasi_course_limited_time');
Route::get('insert_notifikasi_activity_limited_time', $version.'\cron\EmployeeController@insert_notifikasi_activity_limited_time');
Route::get('send_email_notifikasi', $version.'\cron\EmployeeController@send_email_notifikasi');
#belum versioning
Route::post('dashboard/get_course_by_employee', 'DashboardController@get_course_by_employee');
Route::post('dashboard/get_summary_course_by_employee', 'DashboardController@get_summary_course_by_employee');
Route::post('dashboard/get_summary_daily_quiz', 'DashboardController@get_summary_daily_quiz');
Route::post('dashboard/get_summary_learning_hour', 'DashboardController@get_summary_learning_hour');
Route::post('dashboard/get_summary_course_learned', 'DashboardController@get_summary_course_learned');
#EMBOH
Route::view('FormLog', 'form');
Route::view('FormPush', 'push');

Route::post('report/list_report',  $version.'\data\ReportController@list_report');