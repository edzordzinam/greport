<?php

/**
 * Attendance
 *
 * @author Edzordzinam
 * @version
 */

require_once 'Zend/Db/Table/Abstract.php';
class Content_Model_Attendance extends Zend_Db_Table_Abstract {
	/**
	 * The default table name
	 */
	protected $_name = 'student_attendance';


	public static function getCurrentTerm(){
		$termPeriod = new self();
		$termPeriod->_name = 'tb_termdates';

		$select = $termPeriod->select();
		$select->where('cl_startdate <=?', date('Y-m-d 00:00:00'))
				->where('cl_enddate >=?', date('Y-m-d 23:59:59'));

		$result = $termPeriod->fetchRow($select);

		if ($result) {

			$periodData = new stdClass();
			$periodData->term = $result->term;
			$periodData->year = $result->year;

			$periodData->startDate = $result->cl_startdate;
			//shift start date to monday for appropriate calculation of weeks.
			$dayofweek = date('N', strtotime($result->cl_startdate));
			$adjustdate = (86400 * ($dayofweek - 1));
			$periodData->adjustedStart = strtotime($result->cl_startdate) - $adjustdate;

			$periodData->endDate = $result->cl_enddate;
			$periodData->schooldays = self::getWorkingDays($result->cl_startdate, $result->cl_enddate, $result->holidays);
			//$periodData->weeks = self::getWeeks($result->cl_startdate, $result->cl_enddate);
			$periodData->weeks = self::getWeeks(date('Y-m-d',$periodData->adjustedStart), $result->cl_enddate);
			$periodData->daysgone = self::getWorkingDays($result->cl_startdate, date('Y-m-d'), $result->holidays);
			return $periodData;
		}
		else{
			//return null;	return data from generic term dates


			$termPeriod->_name = 'tb_generic_termdates';

			$select = $termPeriod->select();
			$select->where('month(cl_startdate) <=?', date('m'))
				->where('month(cl_enddate) >=?', date('m'));

			$result = $termPeriod->fetchRow($select);

			if ($result->term == 1) {
				$curyear = date('Y');
				$nxtyear = $curyear + 1;
				$year = $curyear.'/'.$nxtyear;
			}
			else{
				$curyear = date('Y');
				$prvyear = $curyear - 1;
				$year = $prvyear.'/'.$curyear;
			}


			$periodData = new stdClass();
			$periodData->term = $result->term;
			$periodData->year = $year;
			$periodData->startDate = $result->cl_startdate;
			$periodData->endDate = $result->cl_enddate;
			$periodData->schooldays = 0;
			$periodData->weeks = 0;
			$periodData->daysgone = 0;
			$periodData->adjustedStart = $result->cl_startdate;

			return $periodData;

		}
	}

	public static function getNextTerm(){
		$currentPeriod = Content_Model_School::getCurrentTermYear(false, true);

		if ($currentPeriod == null)
			return;

		$term = $currentPeriod->term;
		$year = $currentPeriod->year;
		$startdate = $currentPeriod->startDate;

		$termPeriod = new self();
		$termPeriod->_name = 'termdates';

		$select = $termPeriod->select();
		//SELECT * FROM termdates t WHERE t.cl_startdate > '2012-04-18' ORDER BY t.cl_startdate ASC LIMIT 1

		$select->where('cl_startdate > ?',date('Y-m-d',strtotime($startdate)))
				->order('cl_startdate ASC')->limit(1);

		$result = $termPeriod->fetchRow($select);
		if ($result)
			return $result;
		else
			return false;
	}

	public static function getPrevTerm(){
		$currentPeriod = Content_Model_School::getCurrentTermYear(false);
		$term = $currentPeriod->term;
		$year = $currentPeriod->year;
		$startdate = $currentPeriod->startDate;

		$termPeriod = new self();
		$termPeriod->_name = 'termdates';

		$select = $termPeriod->select();
		//SELECT * FROM termdates t WHERE t.cl_startdate < '2012-04-18' ORDER BY t.cl_startdate DESC LIMIT 1

		$select->where('cl_startdate < ?',date('Y-m-d',strtotime($startdate)))
		->order('cl_startdate DESC')->limit(1);

		$result = $termPeriod->fetchRow($select);
		if ($result)
			return $result;
		else
			return false;
	}

	public static function getTermDates($term, $year){
		$termPeriod = new self();
		$termPeriod->_name = 'termdates';

		$select = $termPeriod->select();
		$select->where('year =?', $year)
		->where('term =?', $term);

		$result = $termPeriod->fetchRow($select);

		if ($result) {

			$periodData = new stdClass();
			$periodData->startDate = $result->cl_startdate;
			$periodData->endDate = $result->cl_enddate;
			$periodData->weeks = self::getWeeks($result->cl_startdate, $result->cl_enddate);
			$periodData->days = self::getWorkingDays($result->cl_startdate, $result->cl_enddate, $result->holidays );
			return $periodData;
		}
		else{
			return null;
		}
	}

	public static function getWorkingDays($startDate,$endDate,$holidays){
		// do strtotime calculations just once
		$endDate = strtotime($endDate);
		$startDate = strtotime($startDate);


		//The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
		//We add one to inlude both dates in the interval.
		$days = ($endDate - $startDate) / 86400 + 1;

		$no_full_weeks = floor($days / 7);
		$no_remaining_days = fmod($days, 7);

		//It will return 1 if it's Monday,.. ,7 for Sunday
		$the_first_day_of_week = date("N", $startDate);
		$the_last_day_of_week = date("N", $endDate);

		//---->The two can be equal in leap years when february has 29 days, the equal sign is added here
		//In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
		if ($the_first_day_of_week <= $the_last_day_of_week) {
			if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
			if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
		}
		else {
			// (edit by Tokes to fix an edge case where the start day was a Sunday
			// and the end day was NOT a Saturday)

			// the day of the week for start is later than the day of the week for end
			if ($the_first_day_of_week == 7) {
				// if the start date is a Sunday, then we definitely subtract 1 day
				$no_remaining_days--;

				if ($the_last_day_of_week == 6) {
					// if the end date is a Saturday, then we subtract another day
					$no_remaining_days--;
				}
			}
			else {
				// the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
				// so we skip an entire weekend and subtract 2 days
				$no_remaining_days -= 2;
			}
		}

		//The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
		//---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
		$workingDays = $no_full_weeks * 5;
		if ($no_remaining_days > 0 )
		{
			$workingDays += $no_remaining_days;
		}

		//We subtract the holidays
/* 		foreach($holidays as $holiday){
			$time_stamp=strtotime($holiday);
			//If the holiday doesn't fall in weekend
			if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N",$time_stamp) != 6 && date("N",$time_stamp) != 7)
				$workingDays--;
		} */

		return round($workingDays - $holidays);
	}

	public static function getCurrentLatenessOverall(){
		$attendanceModel = new self();
		$attendanceModel->_name = 'get_attendanceview';
		$attendanceModel->_primary = array('cl_GPSN_ID','cl_AttendanceDate');

		$term = self::getCurrentTerm();


		$select = $attendanceModel->select();
		$select->distinct(true)
				->from($attendanceModel, array('*',"SUM(Late) as totalLateness" ))
				->where('cl_AttendanceDate >=?', $term->startDate)
				->where('cl_AttendanceDate <=?', $term->endDate)
				->where('Late > 0')
				->group('cl_GPSN_ID')
				->order('totalLateness DESC');

		$result = $attendanceModel->fetchAll($select);

		return $result;
	}

	public static function getCurrentStaffLatenessOverall(){
		$attendanceModel = new self();
		$attendanceModel->_name = 'get_staffattendanceview';
		$attendanceModel->_primary = array('cl_ecode','cl_logtime');

		$term = self::getCurrentTerm();

		$select = $attendanceModel->select();
		$select->distinct(true)
		->from($attendanceModel, array('*',"SUM(Late) as totalLateness" ))
		->where('cl_logtime >=?', $term->startDate)
		->where('cl_logtime <=?', $term->endDate)
		->where('Late > 0')
		->group('cl_ecode')
		->order('totalLateness DESC');

		$result = $attendanceModel->fetchAll($select);

		return $result;
	}

	public static function getWeekAttendance($wkstartDate, $wkendDate){
		$attendanceModel = new self();
		$attendanceModel->_name = 'get_attendanceview';
		$attendanceModel->_primary = array('cl_GPSN_ID','cl_AttendanceDate');

		$term = self::getCurrentTerm();


		$select = $attendanceModel->select();
		$select->distinct(true)
		->from($attendanceModel, array('cl_GPSN_ID',"SUM(Late) as totalLateness" ))
		->where('cl_AttendanceDate >=?', date('Y-m-d 00:00:00',$wkstartDate))
		->where('cl_AttendanceDate <=?', date('Y-m-d 23:59:59',$wkendDate))
		->where('Late > 0')
		->group('cl_GPSN_ID')->order('cl_GPSN_ID');


		$result = $attendanceModel->fetchAll($select);

		if ($result)
			return $result->toArray();
		else return null;

	}

	public static function getOneWeekAttendance($wkstartDate, $wkendDate){
		$attendanceModel = new self();
		$attendanceModel->_name = 'get_attendanceview';
		$attendanceModel->_primary = array('cl_GPSN_ID','cl_AttendanceDate');

		$term = self::getCurrentTerm();


		$select = $attendanceModel->select();
		$select->distinct(true)
		//->from($attendanceModel, array('cl_GPSN_ID',"SUM(Late) as totalLateness" ))
		->where('cl_AttendanceDate >=?', date('Y-m-d',$wkstartDate))
		->where('cl_AttendanceDate <=?', date('Y-m-d',$wkendDate))
		->where('Late > 0')
		->group('cl_GPSN_ID')->order('cl_GPSN_ID');


		$result = $attendanceModel->fetchAll($select);

		if ($result)
			return $result->toArray();
		else return null;

	}

	public static function getStaffOneWeekAttendance($ecode, $wkstartDate, $wkendDate){
		$attendanceModel = new self();
		$attendanceModel->_name = 'get_staffattendanceview';
		$attendanceModel->_primary = array('cl_ecode','cl_logtime');

		$term = self::getCurrentTerm();


		$select = $attendanceModel->select();
		$select->distinct(true)
		//->from($attendanceModel, array('cl_ecode',"SUM(Late) as totalLateness" ))
		->where('cl_ecode =?', $ecode)
		->where('cl_logtime >=?', date('Y-m-d 00:00:00',$wkstartDate))
		->where('cl_logtime <=?', date('Y-m-d 23:59:59',$wkendDate))
		->where('Late > 0')->order('cl_logtime DESC');

		$result = $attendanceModel->fetchAll($select);

		if ($result)
			return $result;//->toArray();
		else return null;

	}

	public static function getStaffWeekAttendance($wkstartDate, $wkendDate){
		$attendanceModel = new self();
		$attendanceModel->_name = 'get_staffattendanceview';
		$attendanceModel->_primary = array('cl_ecode','cl_logtime');

		$term = self::getCurrentTerm();


		$select = $attendanceModel->select();
		$select->distinct(true)
		->from($attendanceModel, array('cl_ecode',"SUM(Late) as totalLateness" ))
		->where('cl_logtime >=?', date('Y-m-d 00:00:00',$wkstartDate))
		->where('cl_logtime <=?', date('Y-m-d 23:59:59',$wkendDate))
		->where('Late > 0')
		->group('cl_ecode')->order('cl_ecode');


		$result = $attendanceModel->fetchAll($select);

		if ($result)
			return $result->toArray();
		else return null;

	}

	public static function getLateStudents(){
		$attendanceModel = new self();
		$attendanceModel->_name = 'get_attendanceview';
		$attendanceModel->_primary = array('cl_GPSN_ID','fullname','cl_AttendanceDate');

		$term = self::getCurrentTerm();


		$select = $attendanceModel->select();

		$select->distinct(true)
				->from($attendanceModel, array('cl_GPSN_ID','cl_GradeLevel','fullname'))
				->where('cl_AttendanceDate >=?', $term->startDate)
				->where('cl_AttendanceDate <=?', $term->endDate)
				->where('Late > 0')
				->group('cl_GPSN_ID')
				->order('Late DESC');

		$result = $attendanceModel->fetchAll($select);

		return $result;
	}

	public static function getLateStaff(){
		$attendanceModel = new self();
		$attendanceModel->_name = 'get_staffattendanceview';
		$attendanceModel->_primary = array('cl_ecode','cl_logtime');

		$term = self::getCurrentTerm();


		$select = $attendanceModel->select();

		$select->distinct(true)
		->from($attendanceModel, array('cl_ecode','fullname','cl_position'))
		->where('cl_logtime >=?', $term->startDate)
		->where('cl_logtime <=?', $term->endDate)
		->where('Late > 0')
		->group('cl_ecode')
		->order('Late DESC');

		$result = $attendanceModel->fetchAll($select);

		return $result;
	}

	public static function getCurrentDaysinSchool(){

		$attendanceModel = new self();
		$attendanceModel->_name = 'get_attendanceview';
		$attendanceModel->_primary = array('cl_GPSN_ID','cl_AttendanceDate');

		$term = self::getCurrentTerm();

		$select = $attendanceModel->select();
		$select->distinct(true)
		->from($attendanceModel, array('*',"count(cl_GPSN_ID) as DaysPresent"))
		->where('cl_AttendanceDate >=?', $term->startDate)
		->where('cl_AttendanceDate <=?', $term->endDate)
		->group('cl_GPSN_ID')
		->order('DaysPresent DESC');

		$result = $attendanceModel->fetchAll($select);

		return $result;
	}

	public static function getWeeks($startDate, $endDate){
		$time_passed = strtotime($endDate) - strtotime($startDate);

		// if the first day after startdate is in "Week 1" according to your count
		$weekcount_1 = ceil ( $time_passed / (86400*7));

		// if the first day after startdate is in "Week 0" according to your count
		//$weekcount_0 = floor ( $time_passed / (86400*7));
		return $weekcount_1;
	}

	public static function getStudentAttendanceDays($studentid){
		$attendanceModel = new self();
		$attendanceModel->_name = 'get_attendanceview';
		$attendanceModel->_primary = array('cl_GPSN_ID','cl_AttendanceDate');

		$term = self::getCurrentTerm();


		$select = $attendanceModel->select();
		$select->distinct(true)
			->from($attendanceModel, array('count(cl_GPSN_ID) as daysIn'))
			->where('cl_AttendanceDate >=?', $term->startDate)
			->where('cl_AttendanceDate <=?', $term->endDate)
			->where('cl_GPSN_ID =?', $studentid);

		$result = $attendanceModel->fetchRow($select);

		return $result;
	}

	public static function getStudentAttendanceLate($studentid){
		$attendanceModel = new self();
		$attendanceModel->_name = 'get_attendanceview';
		$attendanceModel->_primary = array('cl_GPSN_ID','cl_AttendanceDate');

		$term = self::getCurrentTerm();


		$select = $attendanceModel->select();
		$select->distinct(true)
			->from($attendanceModel, array("SUM(Late) as totalLateness" ))
			->where('cl_AttendanceDate >=?', $term->startDate)
			->where('cl_AttendanceDate <=?', $term->endDate)
			->where('cl_GPSN_ID =?', $studentid)
			->where('Late > 0')
			->group('cl_GPSN_ID')
			->order('totalLateness DESC');

		$result = $attendanceModel->fetchRow($select);

		return $result;
	}

	public static function updateAttendComment($studentid, $term, $year, $grade, $atcomment, $prcomment, $ctcomment){
		$attendanceModel = new self();
		$attendanceModel->_name = 'comments';

		$select = $attendanceModel->select();
		$select->where('cl_studentid =?', $studentid)
				->where('cl_term =?', $term)
				->where('cl_year =?', $year)
				->where('cl_grade =?', $grade);


		$commentRow = $attendanceModel->fetchRow($select);

		if ($commentRow){
			//it means a record has been located... then it must be updated with the new content
			$commentRow->cl_studentid = $studentid;
			$commentRow->cl_term = $term;
			$commentRow->cl_grade = $grade;
			$commentRow->cl_year = $year;
			$commentRow->cl_attendcomment = $atcomment;
			$commentRow->cl_prcomment = $prcomment;
			$commentRow->cl_ctcomment = $ctcomment;
			$commentRow->save();
			return $commentRow;
		}
		else{
			//no record was found and as such an entry must be made into the database for the selected period
			$newCommentRow = $attendanceModel->createRow();
			$newCommentRow->cl_studentid = $studentid;
			$newCommentRow->cl_term = $term;
			$newCommentRow->cl_year = $year;
			$newCommentRow->cl_grade = $grade;
			$newCommentRow->cl_attendcomment = $atcomment;
			$newCommentRow->cl_prcomment = $prcomment;
			$newCommentRow->cl_ctcomment = $ctcomment;
			$newCommentRow->save();
			return $newCommentRow;
		}


	}

	public static function loadAttendComment($studentid, $term, $year, $grade){
		$attendanceModel = new self();
		$attendanceModel->_name = 'comments';

		$select = $attendanceModel->select();
		$select->where('cl_studentid =?', $studentid)
				->where('cl_term =?', $term)
				->where('cl_year =?', $year)
				->where('cl_grade =?', $grade);

		$commentRow = $attendanceModel->fetchRow($select);

	    return $commentRow;

	}

	public static function loadWeekSummary($studentid, $startDate, $endDate){
		$attendanceModel = new self();
		$select = $attendanceModel->select();

		$select->from($attendanceModel, array('*',"time_to_sec(timediff(cast(`tb_attendance`.`cl_AttendanceDate` as time),'8:00:00')) AS diff" ))
				->where('cl_GPSN_ID =?', $studentid)
				->where('cl_AttendanceDate >=?', date('Y-m-d 00:00:00',$startDate))
				->where('cl_AttendanceDate <=?',  date('Y-m-d 23:59:59',$endDate ));

		$result = $attendanceModel->fetchAll($select);

		if ($result)
			return ($result);
		else
			return null;
	}

	//NEW FUNCTIONS BEING USED

	public static function bookAttendance($studentid){
		$aModel = new self();

		//check existence of student...
		$students = new Content_Model_Students();

		$select = $students->select();
		$select->where('cl_GPSN_ID =?', $studentid)
				->where('cl_Active = 1')
				->where('NOT (cl_GradeLevel = ?)',9999);

		//$student = $students->find($studentid)->current();
		$student = $students->fetchRow($select);

		if ($student)
		{
			//find if attendance has been booked;
			$select = $aModel->select();
			$select->where('studentid =?', $studentid)
					->where('date(checkedin) =?', date('Y-m-d'));
			$result = $aModel->fetchRow($select);

			if ($result){
				//attendance has been booked for student already
				return array(2,"$student->cl_FirstName $student->cl_LastName");
			}
			else
			{
				//book attendance
				$aRow = $aModel->createRow();
				if ($aRow){
					$aRow->studentid = $studentid;
					$aRow->gradelevel = $student->cl_GradeLevel;
					$aRow->save();
					return array(1,"$student->cl_FirstName $student->cl_LastName");
				}
			}
		}
		else
		{
			//student number does not exist on the system
			return 0;
		}
 	}

	public static function dailyAttendance($adate){
		$aModel = new self();
		$db = $aModel->getDefaultAdapter();

		try {
			$stmt = $db->query(
					'select fn_dailyattendance(?) as astatus',
					array(date('Y-m-d',strtotime($adate)))
			);

			$result = $stmt->fetch(Zend_Db::FETCH_OBJ);
			return $result->astatus;

		}catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public static function WeeklyAttendanceSummary($gradelevel, $weekdays, $time_expected){
		$aModel = new self();
		$select = $aModel->select();

		/* (sum( CASE WHEN TIME_TO_SEC( TIMEDIFF( time(checkedin), time('05:30'))) > 0 THEN TIME_TO_SEC( TIMEDIFF( time(checkedin), time('05:30'))) ELSE 0 END )) AS timelost,
		(sum( CASE WHEN TIME_TO_SEC( TIMEDIFF( time(checkedin), time('05:30'))) > 0 THEN 1 ELSE 0 END )) as laters,
		(sum( CASE WHEN TIME_TO_SEC( TIMEDIFF( time(checkedin), time('05:30'))) > 0 THEN TIME_TO_SEC( TIMEDIFF( time(checkedin), time('05:30'))) ELSE 0 END )/
				sum( CASE WHEN TIME_TO_SEC( TIMEDIFF( time(checkedin), time('05:30'))) > 0 THEN 1 ELSE 0 END )) as avgtimelost */

		$select->from($aModel, array("count(studentid) as cnt, date(checkedin) as weekday,
				( sum( CASE WHEN TIME_TO_SEC( TIMEDIFF( time(checkedin), time('$time_expected'))) > 0 THEN TIME_TO_SEC( TIMEDIFF( time(checkedin), time('$time_expected'))) ELSE 0 END )) as timelost,
				(sum( CASE WHEN TIME_TO_SEC( TIMEDIFF( time(checkedin), time('$time_expected'))) > 0 THEN 1 ELSE 0 END )) as laters,
				(sum( CASE WHEN TIME_TO_SEC( TIMEDIFF( time(checkedin), time('$time_expected'))) > 0 THEN TIME_TO_SEC( TIMEDIFF( time(checkedin), time('$time_expected'))) ELSE 0 END )/
				sum( CASE WHEN TIME_TO_SEC( TIMEDIFF( time(checkedin), time('$time_expected'))) > 0 THEN 1 ELSE 0 END )) as avgtimelost
				"))
				->where('date(checkedin) in (?)', $weekdays)
				->group('date(checkedin)')->order('date(checkedin) ASC');

		$results = $aModel->fetchAll($select);

		return $results;

	}

	public static function AttendanceDistribution($gradelevel, $weekdays, $time_expected){
		$aModel = new self();
		$select = $aModel->select();

		$select->from($aModel, array("gradelevel, date(checkedin) AS weekday,
				( sum( CASE WHEN TIME_TO_SEC( TIMEDIFF( time(checkedin), time('$time_expected'))) > 0 THEN 1 ELSE 0 END )) AS laters
				"))
				->where('date(checkedin) in (?)', $weekdays)
				->where("TIME_TO_SEC( TIMEDIFF( time(checkedin), time('$time_expected'))) > 0")
				->group('gradelevel')->order('laters DESC');

				$results = $aModel->fetchAll($select);

				return $results;

	}

	public static function AttendanceDetail($studentid, $weekdays, $time_expected){
		$aModel = new self();
		$select = $aModel->select();

		$select->from($aModel, array("count(studentid) as cnt, date(checkedin) as weekday,
				( sum( CASE WHEN TIME_TO_SEC( TIMEDIFF( time(checkedin), time('$time_expected'))) > 0 THEN TIME_TO_SEC( TIMEDIFF( time(checkedin), time('$time_expected'))) ELSE 0 END )) as timelost,
				(sum( CASE WHEN TIME_TO_SEC( TIMEDIFF( time(checkedin), time('$time_expected'))) > 0 THEN 1 ELSE 0 END )) as laters,
				(sum( CASE WHEN TIME_TO_SEC( TIMEDIFF( time(checkedin), time('$time_expected'))) > 0 THEN TIME_TO_SEC( TIMEDIFF( time(checkedin), time('$time_expected'))) ELSE 0 END )/
				sum( CASE WHEN TIME_TO_SEC( TIMEDIFF( time(checkedin), time('$time_expected'))) > 0 THEN 1 ELSE 0 END )) as avgtimelost
				"))
				->where('date(checkedin) in (?)', $weekdays)
				->where('studentid =?', $studentid)
				->group('date(checkedin)')
				->order('date(checkedin) ASC');

		$results = $aModel->fetchAll($select);

				return $results;

	}

}
