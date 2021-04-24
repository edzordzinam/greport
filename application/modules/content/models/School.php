<?php

/**
 * School
 *
 * @author Edzordzinam
 * @version
 */

require_once 'Zend/Db/Table/Abstract.php';

class Content_Model_School extends Zend_Db_Table_Abstract {
	/**
	 * The default table name
	 */
	protected $_name = '';

	public static function listtermdates(){
        $TermTable = new self();
        $TermTable->_name = 'termdates';

        $result = $TermTable->fetchAll();

        return $result;
	}

	public static function newTermDates($term, $year, $start, $end, $holidays){
        $TermTable = new self();
        $TermTable->_name = 'termdates';

        $select = $TermTable->select();
        $select->where('term =?', $term)->where('year =?',$year);

        $found = $TermTable->fetchRow($select);

        if ($found){

            //delete old record
            $found->term = $term;
            $found->year = $year;
            $found->cl_startdate = date('Y-m-d 00:00:00',strtotime($start));
            $found->cl_enddate = date('Y-m-d 23:59:59',strtotime($end));
            $found->holidays = $holidays;
            $found->save();

        }else{
            $row = $TermTable->createRow();

            if ($row){
                $row->term = $term;
                $row->year = $year;
                $row->cl_startdate = date('Y-m-d 00:00:00',strtotime($start));
                $row->cl_enddate = date('Y-m-d 23:59:59',strtotime($end));
                $row->holidays = $holidays;
                $row->save();
            }
        }
	}

	public static function deleteTermDate($id){
	    $TermTable = new self();
	    $TermTable->_name = 'termdates';
	    $TermTable->_primary = 'cl_id';

        $row = $TermTable->find($id)->current();

        if ($row){
            $row->delete();
            return true;
        }
        else return false;
	}

	public static function loadConfigData(){
	    $optionTable = new self();
	    $optionTable->_name = 'options';
	    $results = $optionTable->fetchAll();

	    $configArray = array();

	    foreach ($results as $result) {
            $configArray[$result->optionname] = $result->optionvalue;
	    }

	    return $configArray;
	}

	public static function saveConfigData($data){
	    $optionTable = new self();
	    $optionTable->_name = 'options';

	    $select = $optionTable->select();

	    foreach ($data as $key => $value) {
           if ($key == 'licensestart' || $key == 'licenseend' || $key == 'licensekey' || $key == 'isValid')
               continue;
	        $select->reset();
	        $select->where("optionname = ?", $key);
	        $row = $optionTable->fetchRow($select);
	        if ($row){
                $row->optionvalue = $value;
                $row->save();
            }else{
            	//add new record;
            	$row = $optionTable->createRow();
            	$row->optionname = $key;
				$row->optionvalue = $value;
				$row->save();
            }
	    }
	}

	public static function getSchoolName(){
		try {
			$optionTable = new self();
			$optionTable->_name = 'options';
			//$optionTable->_primary = 'cl_id';

			$select = $optionTable->select();
			$select->where("optionname = 'schoolname'");

			$result = $optionTable->fetchRow($select);

			if($result){
				return $result->optionvalue;
			}
			else
				return "WELCOME";
		} catch (Exception $e) {
			return 'SETUP INCOMPLETE';
		}

	}

	public static function getSchoolLogo($direct = false){
		$optionTable = new self();
		$optionTable->_name = 'options';
		//$optionTable->_primary = 'cl_id';

		$select = $optionTable->select();
		$select->where("optionname = 'schoollogo'");

		$result = $optionTable->fetchRow($select);

		if($result){
			if (!$direct)
				return '/img/logos/'.$result->optionvalue;
			else
				return './img/logos/'.$result->optionvalue;
		}
		else
			return "Error in SchoolLogo";
	}

	public static function getSchoolTel(){
		$optionTable = new self();
		$optionTable->_name = 'options';
		//$optionTable->_primary = 'cl_id';

		$select = $optionTable->select();
		$select->where("optionname = 'schooltel'");

		$result = $optionTable->fetchRow($select);

		if($result){
			return $result->optionvalue;
		}
		else
			return "Error in SchoolTel";

	}

	public static function getSchoolEmail(){
		$optionTable = new self();
		$optionTable->_name = 'options';
		//$optionTable->_primary = 'cl_id';

		$select = $optionTable->select();
		$select->where("optionname = 'schoolemail'");

		$result = $optionTable->fetchRow($select);

		if($result){
			return $result->optionvalue;
		}
		else
			return "Error in SchoolEmail";
	}

	public static function getSchoolAddress(){
		$optionTable = new self();
		$optionTable->_name = 'options';
		//$optionTable->_primary = 'cl_id';

		$select = $optionTable->select();
		$select->where("optionname = 'schooladdress'");

		$result = $optionTable->fetchRow($select);

		if($result){
			return $result->optionvalue;
		}
		else
			return "Error in SchoolAddress";
	}

	public static function getSchoolWebsite(){
		$optionTable = new self();
		$optionTable->_name = 'options';
		//$optionTable->_primary = 'cl_id';

		$select = $optionTable->select();
		$select->where("optionname = 'schoolwebsite'");

		$result = $optionTable->fetchRow($select);

		if($result){
			return $result->optionvalue;
		}
		else
			return "Error in Schoolwebsite";
	}

	public static function getSchoolFax(){
		$optionTable = new self();
		$optionTable->_name = 'options';
		//$optionTable->_primary = 'cl_id';

		$select = $optionTable->select();
		$select->where("optionname = 'schoolfax'");

		$result = $optionTable->fetchRow($select);

		if($result){
			return $result->optionvalue;
		}
		else
			return "Error in SchoolFax";
	}

	public static function getReceiptPrinter(){
		$optionTable = new self();
		$optionTable->_name = 'options';
		//$optionTable->_primary = 'cl_id';

		$select = $optionTable->select();
		$select->where("optionname = 'receiptprinter'");

		$result = $optionTable->fetchRow($select);

		if($result){
			return $result->optionvalue;
		}
		else
			return "Error in ReceiptPrinter";
	}

	public static function getReportLayout(){
		$optionTable = new self();
		$optionTable->_name = 'options';

		$select = $optionTable->select();
		$select->where("optionname = 'reportlayout'");

		$result = $optionTable->fetchRow($select);

		if($result){
			return $result->optionvalue;
		}
		else
			return "Error in ReportLayout";
	}

	public static function getDuplicateReceipt(){
		$optionTable = new self();
		$optionTable->_name = 'options';

		$select = $optionTable->select();
		$select->where("optionname = 'duplicatereceipt'");

		$result = $optionTable->fetchRow($select);

		if($result){
			return $result->optionvalue;
		}
		else
			return "Error in ReceiptPrinter";
	}

	public static function getCurrentTermYear($ajax = true, $check = false){

        $schoolModel = new self();
        $schoolModel->_name = "termdates";

        $select = $schoolModel->select();

        $Auth = Zend_Auth::getInstance();
        $identity = $Auth->getIdentity();

        if(isset($identity->CurrentContext)){
        	$CurrentContext = $identity->CurrentContext;

        	$select->where('term =?',$CurrentContext['Term'])
        			->where('year =?',$CurrentContext['Year']);

        	$result = $schoolModel->fetchRow($select);

        	$periodData = new stdClass();
        	$periodData->term = $result->term;
        	$periodData->year = $result->year;

        	$periodData->startDate = $result->cl_startdate;
        	//shift start date to monday for appropriate calculation of weeks.
        	$dayofweek = date('N', strtotime($result->cl_startdate));
        	$adjustdate = (86400 * ($dayofweek - 1));
        	$periodData->adjustedStart = strtotime($result->cl_startdate) - $adjustdate;
        	$periodData->startDateAdj = date('Y-m-d',$periodData->adjustedStart);
        	$periodData->endDate = $result->cl_enddate;
        	$periodData->schooldays = self::getWorkingDays($result->cl_startdate, $result->cl_enddate, $result->holidays);
        	$periodData->weeks = self::getTermWeeks(date('Y-m-d',$periodData->adjustedStart), $result->cl_enddate);
        	$periodData->daysgone = self::getWorkingDays($result->cl_startdate, date('Y-m-d'), $result->holidays);

        	return $periodData;

        }
        else{
        	//load context from cache
        	$select->reset();

        	$select->where('cl_startdate <=?', date('Y-m-d 00:00:00'))
        			->where('cl_enddate >=?', date('Y-m-d 23:59:59'));

        	$result = $schoolModel->fetchRow($select);

        	if ($result) {
        		$periodData = new stdClass();
        		$periodData->term = $result->term;
        		$periodData->year = $result->year;

        		$periodData->startDate = $result->cl_startdate;
        		//shift start date to monday for appropriate calculation of weeks.
        		$dayofweek = date('N', strtotime($result->cl_startdate));
        		$adjustdate = (86400 * ($dayofweek - 1));
        		$periodData->adjustedStart = strtotime($result->cl_startdate) - $adjustdate;
        		$periodData->startDateAdj = date('Y-m-d',$periodData->adjustedStart);
        		$periodData->endDate = $result->cl_enddate;
        		$periodData->schooldays = self::getWorkingDays($result->cl_startdate, $result->cl_enddate, $result->holidays);
        		$periodData->weeks = self::getTermWeeks(date('Y-m-d',$periodData->adjustedStart), $result->cl_enddate);
        		$periodData->daysgone = self::getWorkingDays($result->cl_startdate, date('Y-m-d'), $result->holidays);
        		return $periodData;

        	}else {

/*         //return null;	return data from generic term dates

            $schoolModel->_name = 'tb_generic_termdates';

            $select = $schoolModel->select();
            $select->where('month(cl_startdate) <=?', date('m'))
            ->where('month(cl_enddate) >=?', date('m'));

            $result = $schoolModel->fetchRow($select);

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

            return $periodData; */
        	//throw new Zend_Exception('No term has been specified');

        		//debug_print_backtrace();

        		//exit();
        		if (!$check)
	 				header("HTTP/1.1 701 INVALID TERM DATES");
        	    if ($ajax)
	            	exit();
        	    else
        	    	return null;
        	}
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

	    return $workingDays - $holidays;
	}

	public static function getTermWeeks($start, $end, $check = null){
/*     	$sd = new Zend_Date($start, 'YYYY-MM-dd');
    	$ed = new Zend_Date($end, 'YYYY-MM-dd');
    	$diff = $ed->sub($sd);
	    return ($diff->toValue(Zend_Date::WEEK); */


	    $dateEnd = !empty($end) ? $end : Zend_Date::now()->toString('YYYY-MM-dd');

	    if (isset($check))
		    if (strtotime($check) <= strtotime($dateEnd)){
		      $dateEnd = ($check);
		    }

	    $dateStartZD = new Zend_Date($start, 'YYYY-MM-dd');
	    $dateEndZD = new Zend_Date($dateEnd, 'YYYY-MM-dd');
	    $dateEndZD->sub($dateStartZD);
	    return (round($dateEndZD->getTimestamp() / (60 * 60 * 24 * 7)));
	}

	public static function getPastYearsArray($desc = false){
		$schoolModel = new self();
		$schoolModel->_name = "termdates";

		$select = $schoolModel->select();
		$select->distinct(true)
			   ->from($schoolModel,array('year'));

		if ($desc)
		  $select->order('year DESC');

		$result = $schoolModel->fetchAll($select)->toArray();

		return $result;
	}

	public static function getPastTermYearsArray($reverse = false){
		$schoolModel = new self();
		$schoolModel->_name = "termdates";

		$select = $schoolModel->select();
		$select->distinct(true)
				->from($schoolModel,array('term','year','cl_startdate','cl_enddate'))
				->where('NOT (cl_startdate <="'.date('Y-m-d 00:00:00').'" and cl_enddate >=?)', date('Y-m-d 23:59:59'));

		if ($reverse)
			$select->order('year ASC')->order('term ASC');
		else
			$select->order('year DESC')->order('term DESC');

		$result = $schoolModel->fetchAll($select)->toArray();

		return $result;
	}

	public static function setPeriodContext($Term, $Year, $current = null){


	}

	public static function isCurrentPeriod($term, $year){
		$schoolModel = new self();
		$schoolModel->_name = "termdates";

		$select = $schoolModel->select();

		$select->where('cl_startdate <=?', date('Y-m-d 00:00:00'))
				->where('cl_enddate >=?', date('Y-m-d 23:59:59'))
				->where("term = $term")->where("year =?",$year);

		$result = $schoolModel->fetchRow($select);

		if ($result)
			return true;
		else
			return false;
	}

	public static function getWorkingDaysInWeek($date){
		$firstMondayThisWeek= new DateTime($date);
		$firstMondayThisWeek->modify('tomorrow');
		$firstMondayThisWeek->modify('last Monday');

		$nextFiveWeekDays = new DatePeriod(
				$firstMondayThisWeek,
				DateInterval::createFromDateString('+1 weekdays'),
				4
		);
 		return $nextFiveWeekDays;
	}

	public static function addGradingSystem($interpretation, $upperlimit, $lowerlimit){
		$giModel = new self();
		$giModel->_name = "gradeinterpretation";


	}

	public static function saveFile($filename){

	}

	public static function addCalendarEvent($instructor, $dateOfEvent, $timeOfEvent, $nameOfEvent, $endOfEvent){
		///
	}

	public static function getGradeDistribution(){
		$school = new self();
		$db = $school->getDefaultAdapter();

		try {
			$stmt = $db->query(
								'call sp_getgradedistro();',
								array()
			);

			$result = $stmt->fetchAll(Zend_Db::FETCH_OBJ);

			return $result;

		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
}

