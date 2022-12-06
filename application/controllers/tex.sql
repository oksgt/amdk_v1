select distinct 
v_course.*, 
colibri_employee.empCode as empCode, 
colibri_employee.empName as empName, 
colibri_employee.empEmail as empEmail, 
(select compName from colibri_company where compCode=colibri_employee.compCode) as compName, 
IFNULL(colibri_dashboard_admin_employee_summary.completionStatus,'Not Attempted') as completionStatus,

-- vucb.batchStartDate as batchStartDate, 
-- vucb.batchEndDate as batchEndDate, 
-- vucb.batchId as batchId, vucb.batchName as batchName

from v_course 
inner join mdl_enrol 
	on mdl_enrol.courseid = v_course.id 
inner join mdl_user_enrolments 
	on mdl_user_enrolments.enrolid = mdl_enrol.id 
inner join mdl_user 
	on mdl_user.id = mdl_user_enrolments.userid 
inner join colibri_virtual_nip 
	on colibri_virtual_nip.virtual_userid = mdl_user.id 
inner join colibri_employee 
	on colibri_employee.empCode = colibri_virtual_nip.empCode 
inner join mdl_context 
	on mdl_context.instanceid = v_course.id 
inner join mdl_role_assignments 
	on mdl_role_assignments.userid = mdl_user_enrolments.userid 
	and mdl_role_assignments.contextid = mdl_context.id 

left  join colibri_dashboard_admin_employee_summary 
	on colibri_dashboard_admin_employee_summary.empCode = colibri_employee.empCode 
	and colibri_dashboard_admin_employee_summary.courseId = v_course.id 
-- 	
-- left join v_user_course_batch vucb
-- 	on vucb.courseId = v_course.id 
-- 	and vucb.username = mdl_user.username 
-- 	
	where
	mdl_context.contextlevel = 50 and
	colibri_employee.empTermDate is null 
	and mdl_role_assignments.roleid in (5) 
	and (v_course.compCode = "14" ) 
	and (v_course.id = 67 ) 
-- 	and (vucb.batchId = "all" or 'all' = 'all') 
	and (IFNULL(colibri_dashboard_admin_employee_summary.completionStatus,'') = 'ongoing' or 'all' = '') 
	and (colibri_employee.empCode = "097325")  
	
	
	