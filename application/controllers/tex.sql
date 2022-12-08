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
	
	
	


	CREATE DEFINER=`colibri`@`192.168.11.247` PROCEDURE `colibri`.`insert_dashboard`(_nip text)
BEGIN
	DECLARE APP_URL_IMAGE varchar(50) DEFAULT 'https://klamobile.klgsys.com/colibri/';

DECLARE APP_URL_IMAGE_LOCAL varchar(50) DEFAULT 'https://klamobile.klgsys.com/nest/public/';

SET SESSION group_concat_max_len = 150000;

IF (_nip = '\'all\'') THEN 
	TRUNCATE colibri_dashboard_admin_employee_summary;
	SET @where = '';
	SET @where1 = '';
	SET @where_lh = '';
ELSE 
	SET @delete = CONCAT('DELETE from colibri_dashboard_admin_employee_summary where empCode in (', _nip, ')');
	
	PREPARE stmt FROM @delete;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @where = CONCAT('WHERE cvn.empCode in (', _nip, ')');
	SET @where1 = CONCAT('AND cvn.empCode in (', _nip, ')');
	SET @where_lh = CONCAT('AND clh.empCode in (', _nip, ')');
END IF;

SET @sql = CONCAT('REPLACE INTO colibri_dashboard_admin_employee_summary  (
					insert_date,
					empCode,
					vnip,
					userid,
					empNama,
					superiorCode,
					superiorName,
					compCode,
					compName,
					jobTitleCode,
					jobTitleName,
					orgCode,
					orgName,
					locCode,
					locName,
					SubCategoryId,
					CategoryName,
					batchId,
					batchName,
					courseId,
					courseName,
					startEnroll,
					completionDate,
					completionStatus,
					percent_complete,
					grade,
					grade_source,
					learning_hour,
					learning_minute,
					prereqCourseId,
					prereqCourseStatus,
					prereqCourseCompletionStatus,
					learning_hour_formatted,
					statusProses,
					certificate_filename,
					certificate_cover_image,
					recert_version,
					show_in_dashboard
				)
				SELECT DISTINCT
					now(),
					cvn.empCode,
					cvn.virtual_empCode,
					cvn.virtual_userid,
					ve.empName,
					ve.empSuperiorCode,
					ve.empSuperiorName,
					ve.compCode,
					ve.compName,
					ve.jobTitleCode,
					ve.jobTitleName,
					ve.orgCode,
					ve.orgName,
					ve.locCode,
					ve.locName,
					cvn.programId,
					course_program.subCategoryName,
					(
					SELECT
						batchId
					FROM
						`colibri_course_batch` ccb
					WHERE
						courseId = cvn.courseId
						AND (mue.timecreated BETWEEN UNIX_TIMESTAMP(ccb.batchStartDate) AND UNIX_TIMESTAMP(ccb.batchEndDate))
					LIMIT 1) AS batchId,
					(
					SELECT
						batchName
					FROM
						`colibri_course_batch` ccb
					WHERE
						courseId = cvn.courseId
						AND (mue.timecreated BETWEEN UNIX_TIMESTAMP(ccb.batchStartDate) AND UNIX_TIMESTAMP(ccb.batchEndDate))
					LIMIT 1) AS batchName,
					cvn.courseId,
					mc.fullname,
					FROM_UNIXTIME(mue.timecreated) AS startEnroll,
					FROM_UNIXTIME(course_completions.timecompleted) as completionDate,
					IF(course_completions.timecompleted IS NOT NULL, \'completed\',  IF( compl.percent = 100,
						\'completed\',
						\'ongoing\' )) as completionStatus,
					IF(course_completions.timecompleted IS NOT NULL, 100, compl.percent) as percent_complete,
					IF(ROUND(gg.finalgrade, 2)>100, 100, CAST(ROUND(gg.finalgrade, 2) AS DECIMAL(5,2))) as grade,
					\' normal\',
					CAST((((HOUR(CONCAT(course_program.training_hours, \':00\'))* 60) + MINUTE(CONCAT(course_program.training_hours, \':00\')))/ 60) AS DECIMAL(10,
					2)) as learning_hour,
					((HOUR(CONCAT(course_program.training_hours, \':00\'))* 60) + MINUTE(CONCAT(course_program.training_hours, \':00\'))) as learning_minute,
					\'[]\' as prereqCourseId,
					\'[]\' as prereqCourseStatus,
					\' completed\' as prereqCourseCompletionStatus,
					(
					CASE
						WHEN LENGTH(course_program.training_hours)= 1 THEN CONCAT(\'0\', course_program.training_hours, \':00\')
						WHEN LENGTH(course_program.training_hours)= 2 THEN CONCAT(course_program.training_hours, \':00 \')
						WHEN LENGTH(SUBSTRING_INDEX(course_program.training_hours, \':\', 1))= 1 THEN CONCAT(\'0\', course_program.training_hours)
						WHEN LENGTH(SUBSTRING_INDEX(course_program.training_hours, \':\',-1))= 1 THEN CONCAT(course_program.training_hours, \'0\')
						ELSE course_program.training_hours
					END ) as learning_hour_formatted,
				 	1,
					cert.filename,
					cert.thumb,
					recert_version,
					show_in_dashboard
				FROM
					colibri_virtual_nip cvn
				INNER JOIN mdl_user mu 
				 	ON mu.id = cvn.virtual_userid
				INNER JOIN colibri_employee ce 
					ON ce.empCode = cvn.empCode
				INNER JOIN mdl_user_enrolments mue 
					ON mue.userid = mu.id
				INNER JOIN mdl_enrol me 
					ON me.id = mue.enrolid 
				INNER JOIN mdl_course mc ON 
					mc.id = me.courseId AND mc.id = cvn.courseId 
				INNER JOIN
					(
					select 
						sub.id as subCategoryId,
						sub.name as subCategoryName,
						ccc.courseId,
						cca.training_hours 
					from colibri_course_category ccc  
					left join colibri_category sub on sub.id = ccc.categoryId
					left join colibri_course_addon cca on cca.id = ccc.courseId
					group by sub.id, ccc.courseId 
					) course_program 
					ON (course_program.subCategoryId = cvn.programId AND course_program.courseId=cvn.courseId)
				left join v_employee ve on ve.empCode=cvn.empCode
				LEFT JOIN 
					(
					SELECT 
					  virtual_empCode,
					  userid,
					  course as courseId,
					  subCategoryId,
					  timecompleted 
					FROM
					  `mdl_course_completions` 
					INNER JOIN
					  (select 
							sub.id as subCategoryId,
							ccc.courseId
						from colibri_course_category ccc  
						left join colibri_category sub on sub.id = ccc.categoryId)
						vcc on vcc.courseId = course
					INNER JOIN 
					  colibri_virtual_nip cvn on (cvn.courseId = course and cvn.programId = vcc.subCategoryId)
					INNER JOIN
					  mdl_user mu on (mu.id = userid and mu.username = cvn.virtual_empCode) 
					WHERE (cvn.empCode IN (', _nip, ') OR \'all\'=', _nip, ')
					) as course_completions on (cvn.virtual_empCode =course_completions.virtual_empCode AND cvn.courseId=course_completions.courseId AND cvn.programId=course_completions.subcategoryId)
--				LEFT JOIN 
--					mdl_context ctx ON
--					ctx.instanceid = mc.id
--				LEFT JOIN mdl_role_assignments ra ON
--					 ra.contextid = ctx.id AND ra.userid = mu.id
				LEFT JOIN 
					(
					SELECT gg.userid, MAX(gg.finalgrade) as finalgrade, gi.courseid
						FROM mdl_grade_grades gg 
						JOIN mdl_grade_items AS gi ON
						gg.itemid = gi.id AND gi.itemtype=\'course\'
						where gg.userid in (select distinct virtual_userid from colibri_virtual_nip cvn ', @where,')
						group by gg.userid, gi.courseid 
					) gg ON gg.userid = mu.id and gg.courseid = mc.id
-- 				LEFT JOIN
-- 					prereq_temp as cvnPrereq on cvnPrereq.id = cvn.id 
				LEFT JOIN 
					(
						SELECT 
			                cec.subCategoryId,
			                cecv.courseId,
			                concat(\'',APP_URL_IMAGE_LOCAL,'\', filename) as filename,
			                cec.empCode,
			                (
			                CASE 
				                when NULlIF(ccr.certThumb,\'\') is null THEN (
				                	case 
					                	when (SELECT RAND()*(4-1)+1) = 1 then concat(\'', APP_URL_IMAGE_LOCAL, '\', ', '\'certificate/brom.png\'', ')
					                	when (SELECT RAND()*(4-1)+1) = 2 then concat(\'', APP_URL_IMAGE_LOCAL, '\', ', '\'certificate/gold.png\'', ')
					                	when (SELECT RAND()*(4-1)+1) = 3 then concat(\'', APP_URL_IMAGE_LOCAL, '\', ', '\'certificate/noth.png\'', ')
					                	when (SELECT RAND()*(4-1)+1) = 4 then concat(\'', APP_URL_IMAGE_LOCAL, '\', ', '\'certificate/silver.png\'', ')
					               	end 
				                )
				                when NULlIF(ccr.certThumb,\'\') is not null then concat(\'',APP_URL_IMAGE,'\',', '\'public/uploads/certificate_thumbnail/\'', ',ccr.certThumb)
				            END
			                )as thumb
		                FROM
		                	colibri_employee_certificate cec
		                LEFT JOIN colibri_employee_certificate_vnip cecv 
		                	on cecv.id_emp_certificate = cec.id
		                JOIN `colibri_certificate` ccr 
		                    ON ccr.`certId` = cec.`certId`
		                AND ccr.`certEnabled` = 1 
		                AND expiryDate >= CURRENT_DATE()
						AND (cec.empCode IN (', _nip, ') OR \' all\'=', _nip, ')
					) cert on cert.empCode = cvn.empCode and cert.subCategoryId=cvn.programId 
				LEFT JOIN (
					 SELECT 
			              mcm.course,
			              compl.userid,
			              IF(FLOOR((total_completed / COUNT(1)) * 100)>100,100,FLOOR((total_completed / COUNT(1)) * 100)) AS percent 
			            FROM
			              `mdl_course_modules` mcm 
			              JOIN 
			                (SELECT 
			                  m.`course`,
			                  mc.`userid`,
			                  COUNT(1) AS total_completed
			                FROM
			                  `mdl_course_modules_completion` mc 
			                  JOIN `mdl_course_modules` m 
			                    ON m.`id` = mc.`coursemoduleid`
			                  JOIN mdl_user mu ON mu.id = mc.`userid`
			                WHERE completionstate != \'0\' 
			                AND mc.userid IN (select distinct virtual_userid from colibri_virtual_nip cvn ', @where, ')
			                GROUP BY mc.userid,
			                  m.`course`) compl 
			                ON compl.course = mcm.`course` 
			            WHERE `completion` != \'0\' 
			              AND visible = \'1\' 
			              AND `deletioninprogress` = \'0\' 
			            GROUP BY mcm.`course`,
			          compl.userid ) compl on compl.course=cvn.courseId and compl.userid=cvn.virtual_userid 
				WHERE cvn.matrix_deleted IS NULL AND (cvn.empCode IN (', _nip,') OR \'all\'=', _nip,');');

PREPARE stmt
FROM
@sql;

EXECUTE stmt;

DEALLOCATE PREPARE stmt;

set
@update = CONCAT('UPDATE colibri_dashboard_admin_employee_summary cdaes, (
		SELECT DISTINCT
			cvn.virtual_empCode,
			cvn.courseId,
			CAST(CONCAT(\'[\', GROUP_CONCAT(DISTINCT JSON_OBJECT(ccp.requiredCourseId, (SELECT fullname FROM mdl_course mc WHERE mc.id = ccp.requiredCourseId))), \']\') AS JSON) as prereqCourseId,
			CAST(CONCAT(\'[\', GROUP_CONCAT(DISTINCT JSON_OBJECT(ccp.requiredCourseId, IFNULL(cdaes.completionStatus,\'ongoing\'))), \']\') AS JSON) as prereqCourseStatus,
			IF(GROUP_CONCAT( IFNULL(cdaes.completionStatus,\'ongoing\')) LIKE \'%ongoing%\', \'ongoing\', \'completed\') as prereqCourseCompletionStatus
		FROM colibri_dashboard_admin_employee_summary cdaes
		INNER JOIN colibri_course_prereq ccp on ccp.requiredCourseId = cdaes.courseId
		INNER JOIN colibri_virtual_nip cvn ON cvn.courseId = ccp.courseId
		where cvn.virtual_empCode is not null and cvn.courseId is not null ', @where1, '
		group by cvn.virtual_empCode, cvn.courseId
	) group_table
	SET
		cdaes.prereqCourseId = group_table.prereqCourseId,
		cdaes.prereqCourseStatus = group_table.prereqCourseStatus,
		cdaes.prereqCourseCompletionStatus = group_table.prereqCourseCompletionStatus
	where cdaes.vnip = group_table.virtual_empCode and cdaes.courseId = group_table.courseId;');

PREPARE stmt
FROM
@update;

EXECUTE stmt;

DEALLOCATE PREPARE stmt;

set
@lh = concat('UPDATE colibri_dashboard_admin_employee_summary cdaes 
	inner join mdl_course mc on mc.id=cdaes.courseId
	inner join colibri_learning_history clh on cdaes.empCode=clh.empCode and clh.trainingCode = mc.idnumber and cdaes.SubCategoryId = clh.subcategoryId	
	set cdaes.completionDate = clh.completionDate,
		cdaes.grade = clh.nilai,
		cdaes.grade_source = \'learning history\',
		cdaes.percent_complete = 100,
		cdaes.completionStatus = \'completed\'
	WHERE clh.completionDate >= DATE_SUB(
        CURRENT_DATE(),
        INTERVAL 
        (SELECT 
          settingValue 
        FROM
          `colibri_setting` 
        WHERE settingCode = \'learning_history_max_days\') DAY
     ) 
    AND clh.trainingStatus = \'pass\'
    AND clh.nilai > 0 ', @where_lh, ';');

PREPARE stmt
FROM
@lh;

EXECUTE stmt;

DEALLOCATE PREPARE stmt;
END