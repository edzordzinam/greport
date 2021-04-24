<?php

/**
 * TermBill
 *
 * @author Edzordzinam
 * @version
 */

require_once 'Zend/Db/Table/Abstract.php';

class Content_Model_TermBill extends Zend_Db_Table_Abstract {

	/**
	 * The default table name
	 */
	protected $_name = 'termbills';

	public static function listBills(
						$displayStart,
						$displayLength,
						$search,
						$iSortCol,
						$sSortDir,
						$term,
						$year) {
		$termBillModel = new self ();

		// retrieving the total number of rows in the courts table
		$groupsTotal = $termBillModel->getAdapter ()->fetchOne (
							'SELECT COUNT(cl_id) AS count FROM termbills' );

		$select = $termBillModel->select ();
		$select->from (
							$termBillModel,
							array (
									new Zend_Db_Expr ( 'SQL_CALC_FOUND_ROWS cl_id' ),
									'*' ) )
			->where ( 'term =?', $term )
			->where ( 'year=?', $year );
		// paging
		if ($displayLength != - 1)
			$select->limit ( $displayLength, $displayStart );

			// column filtering
		if ($search != "")
			$select->where ( "description like '%$search%'" )->orWhere ( "year like '%$search%'" );

			// order by
		$columns = array (
				'description',
				'feegroup',
				'specificgrades',
				'amount',
				'term',
				'year',
				'type',
				'mandatory' );
		$select->order ( "$columns[$iSortCol] $sSortDir" );

		$termbill = $termBillModel->fetchAll ( $select );

		$db = $termBillModel->getDefaultAdapter ();
		$db->setFetchMode ( Zend_Db::FETCH_OBJ );
		$idisplayTotal = $db->fetchAll ( 'select FOUND_ROWS() as ct' );

		return array (
				'data' => $termbill,
				'iTotalRecords' => $groupsTotal,
				'iDisplayRecords' => $idisplayTotal [0]->ct );
	}

	public static function updateBills(
						$id,
						$description,
						$group,
						$grade,
						$amount,
						$mandatory,
						$type,
						$term,
						$year,
						$stream) {
		$billModel = new self ();

		if ($id == null) {
			$row = $billModel->createRow ();
			if ($row) {
				$row->description = $description;
				$row->feegroup = $group;
				$row->specificgrades = $grade;
				$row->amount = $amount;
				$row->mandatory = $mandatory;
				$row->term = $term;
				$row->year = $year;
				$row->type = $type;
				$row->stream = $stream;
				$row->save ();
			}
		} else {
			$row = $billModel->find ( $id )->current ();
			if ($row) {
				$row->description = $description;
				$row->feegroup = $group;
				$row->specificgrades = $grade;
				$row->amount = $amount;
				$row->mandatory = $mandatory;
				$row->term = $term;
				$row->year = $year;
				$row->type = $type;
				$row->stream = $stream;
				$row->save ();
			}
		}
	}

	public static function getGroupFee($group, $type, $mandatory, $term, $year, $stream = 0) {
		/*
		 * select SUM(amount) into termfee from termbills where (termbills.feegroup = `group` or termbills.feegroup = -1) and specificgrades = -1 and termbills.type = `type` and termbills.mandatory = `mandatory`;
		 */
		// type 1 -> term fee 0 -> entry fees, 2 - monthlyfee, 3 - yearly fee
		// mandatory -> 1 and 0 -> optional fee item

		$termBill = new self ();
		$select = $termBill->select ();

		$select->from ( $termBill, array (
				'SUM(amount) as Total' ) )
				->where ( "feegroup = $group or feegroup = -1" )
				->where ( "specificgrades = ?", - 1 )
				->where ( "mandatory = ?", $mandatory )
				->where ( 'term =?', $term )
				->where ( 'year =?', $year )
				->where('stream =?  or stream = -1', $stream);

		if ($term == 1) {
			$select->where ( "type = ? OR type = 3", $type ); // when 1st term charge yearly bill
		} else {
			$select->where ( "type = ?", $type );
		}

		$result = $termBill->fetchRow ( $select );
		return $result->Total;
	}

	public static function getGradeSpecificOnlyFee($grade, $type, $mandatory, $term, $year, $stream = 0) {
		/*
		 * select SUM(amount) into termfee from termbills where (termbills.feegroup = `group` or termbills.feegroup = -1) and specificgrades = -1 and termbills.type = `type` and termbills.mandatory = `mandatory`;
		 */
		// type 1 -> term fee 0 -> entry fees, 2 - monthlyfee, 3 - yearly fee
		// mandatory -> 1 and 0 -> optional fee item

		$termBill = new self ();
		$select = $termBill->select ();

		$select->from ( $termBill, array (
				'SUM(amount) as Total' ) )
			->where ( "feegroup = -2" )
			->where ( "specificgrades = ? ", $grade )
			->where ( "mandatory = ?", $mandatory )
			->where ( 'term =?', $term )
			->where ( 'year =?', $year )
			->where('stream =? or stream = -1', $stream);

		if ($term == 1) {
			$select->where ( "type = ? OR type = 3", $type ); // when 1st term charge yearly bill
		} else {
			$select->where ( "type = ?", $type );
		}

		$result = $termBill->fetchRow ( $select );
		return $result->Total;
	}

	public static function getBillforGroup($groupkey, $type, $mandatory, $term, $year, $stream = 0) {
		/*
		 * select SUM(amount) into termfee from termbills where (termbills.feegroup = `group` or termbills.feegroup = -1) and specificgrades = -1 and termbills.type = `type` and termbills.mandatory = `mandatory`;
		 */
		// type 1 -> term fee 0 -> entry fees
		// mandatory -> 1 and 0 -> optional fee item

		$termBill = new self ();
		$select = $termBill->select ();

		$select->where ( "feegroup = $groupkey or feegroup = -1" )
				->where ( "specificgrades = ?", - 1 )
				->where ( "mandatory = ?", $mandatory )
				->where ( 'term=?', $term )
				->where ( 'year=?', $year )
				->where('stream =? or stream = -1', $stream);

		if ($type != - 100) {
			if ($term == 1) {
				$select->where ( "type = ? OR type = 3", $type ); // when 1st term charge yearly bill
			} else {
				$select->where ( "type = ?", $type );
			}
		} else {
			$select->order ( 'type ASC' );
		}

		$result = $termBill->fetchAll ( $select );

		if ($result)
			return $result;
		else
			return null;
	}

	public static function getBillforGrade($grade, $type, $mandatory, $term, $year, $stream = 0) {
		/*
		 * select SUM(amount) into termfee from termbills where (termbills.feegroup = `group` or termbills.feegroup = -1) and specificgrades = -1 and termbills.type = `type` and termbills.mandatory = `mandatory`;
		*/
		// type 1 -> term fee 0 -> entry fees
		// mandatory -> 1 and 0 -> optional fee item

		$termBill = new self ();
		$select = $termBill->select ();

		$select->where ( "feegroup = -2" )
				->where ( "specificgrades = ?", $grade )
				->where ( "mandatory = ?", $mandatory )
				->where ( 'term=?', $term )
				->where ( 'year=?', $year )
				->where('stream =? or stream = -1', $stream);

		if ($type != - 100) {
			if ($term == 1) {
				$select->where ( "type = ? OR type = 3", $type ); // when 1st term charge yearly bill
			} else {
				$select->where ( "type = ?", $type );
			}
		} else {
			$select->order ( 'type ASC' );
		}

		$result = $termBill->fetchAll ( $select );

		if ($result)
			return $result;
		else
			return null;
	}

	public static function getAdditionalBillforGroup($groupkey, $type, $mandatory, $term, $year, $stream = 0) {
		/*
		 * select SUM(amount) into termfee from termbills where (termbills.feegroup = `group` or termbills.feegroup = -1) and specificgrades = -1 and termbills.type = `type` and termbills.mandatory = `mandatory`;
		 */
		// type 1 -> term fee 0 -> entry fees
		// mandatory -> 1 and 0 -> optional fee item

		$termBill = new self ();
		$select = $termBill->select ();

		$select->where ( "feegroup = $groupkey or feegroup = -1" )
			->where ( "specificgrades = ?", - 1 )
			->where ( "type = 2 OR type = 3" )
			->where ( "mandatory = ?", $mandatory )
			->where ( 'term=?', $term )
			->where ( 'year=?', $year )
			->where('stream =? or stream = -1', $stream);

		$result = $termBill->fetchAll ( $select );

		if ($result)
			return $result;
		else
			return null;
	}

	public static function getUnBilledStudents($date = 0, $includestudents = false, $term, $year) {
		// this function returns the list of students who have not been billed
		// as per the term, year and grade in which they belong.... They must currently
		// be an active student....

		if ($date == 0)
			$date = date ( 'Y-m-d' );

		$termBillModel = new self ();
		$termBillModel->_name = 'students';

		/*
		 * $period = Content_Model_School::getCurrentTermYear (); $term = $period->term; $year = $period->year;
		 */

		$select = $termBillModel->select ();

		// first get the number of groups
		$typegroup = array (
				1,
				2 ); // 1 for termly fees and 2 for monthly fees
		$arr = array ();
		$arr2 = array ();
		$results = array();
		/* Zend_Debug::dump(Content_Model_TermBill::getFeeGroupGrades ( 1, $term, $year ));
		Zend_Debug::dump($year); */

		// getting for termly fees
		foreach ( $typegroup as $groupid ) {
 			$gradesarray = Content_Model_TermBill::getFeeGroupGrades ( $groupid, $term, $year );

			$select->reset ();

			if (!(count ( $gradesarray ) > 0)){
				$arr[] = null;
				$arr2 [] = null;
				continue;
			}

				$select->where ( 'NOT (cl_GradeLevel = ?)', 9999 )
					->where ( 'cl_Active = 1' )
					->where ( 'cl_GradeLevel in (?)', $gradesarray );

			if ($groupid == 1) {
				// termly fees
				if (count ( $gradesarray ) > 0)
				$select->where (
									"NOT EXISTS (?)",
									new Zend_Db_Expr (
													"SELECT cl_GPSN_ID from billedstudents where students.cl_GPSN_ID = billedstudents.cl_GPSN_ID AND billedstudents.term=$term AND billedstudents.year = '$year' AND billedstudents.payperiod = 'T'" ) );
			} else {
				if (count ( $gradesarray ) > 0)
				$select->where (
									"NOT EXISTS (?)",
									new Zend_Db_Expr (
													"SELECT cl_GPSN_ID from billedstudents where students.cl_GPSN_ID = billedstudents.cl_GPSN_ID AND billedstudents.payperiod = 'M' AND month(billdate) = month('$date')" ) );

			}

			try {
 				$results = $termBillModel->fetchAll ( $select );
 				$arr [] = $results->count();
				$arr2 [] = $results->toArray();

			} catch ( Exception $e ) {
				Zend_Debug::dump ( $e->getMessage () );
			}
		}

		// getting for monthly fees
		if ($includestudents)
			$results = array (
					'ubtermly' => (isset($arr [0] )) ? $arr[0] : null,
					'ubmonthly' => (isset($arr [1] )) ? $arr[1] : null,
					'ubtermlystudents' =>  (isset($arr2 [0] )) ? $arr2[0] : null,
					'ubmonthlystudents' =>  (isset($arr2 [1] )) ? $arr2[1] : null );
		else
			$results = array (
					'ubtermly' =>  (isset($arr [0] )) ? $arr[0] : null,
					'ubmonthly' => (isset($arr [1] )) ? $arr[1] : null);
		return $results;
	}

	public static function getFeeGroupGrades($type, $term, $year) {
		$tModel = new self ();
		$tModel->_name = 'vw_termbillgroups';
		$tModel->_primary = 'cl_id';

		$select = $tModel->select ( $tModel, array (
				'specificgrades, gradelevels' ) );
		$select->where ( 'term =?', $term )
			->where ( 'year =?', $year )
			->where ( 'type =?', $type )
			->group ( 'feegroup' );

		$results = $tModel->fetchAll ( $select );

		$arr = array ();

		if ($results->count () > 0) {
			foreach ( $results as $group ) {
				$arr = array_merge ( $arr, json_decode ( $group->gradelevels ) );
				if ($group->specificgrades != - 1)
					array_push ( $arr, $group->specificgrades );
			}
		}
		$arr = array_unique ( $arr );
		sort ( $arr, SORT_NUMERIC );
		return $arr;
	}

	public static function recordBillStudent($studentid, $term, $year, $payperiod, $month = 0) {
		$billedStudents = new self ();
		$billedStudents->_name = "billedstudents";

		$select = $billedStudents->select ();
		$select->where ( 'cl_GPSN_ID =?', $studentid )
			->where ( 'term =?', $term )
			->where ( 'year =?', $year );

		if ($month != 0)
			$select->where ( "month(billdate) =?", $month );

		$student = $billedStudents->fetchRow ( $select );

		try {
			if (! $student) {
				$bill = $billedStudents->createRow ();
				if ($bill) {
					$bill->cl_GPSN_ID = $studentid;
					$bill->term = $term;
					$bill->year = $year;
					$bill->payperiod = $payperiod;
					$bill->save ();
				}
			}
			return "Billing of $bill->cl_GPSN_ID successful";
		} catch ( Exception $e ) {
			return "Error processing bill of :" . $e->getMessage ();
		}
	}

	public static function clearBilling($term, $year, $payperiod, $month = 0) {
		$billedStudents = new self ();
		$billedStudents->_name = "billedstudents";

		$select = $billedStudents->select ();
		$select->where ( 'term =?', $term )->where ( 'year =?', $year );

		if ($month != 0)
			$select->orWhere ( "month(billdate) =?", $month );

		$students = $billedStudents->fetchAll ( $select );

		try {
			foreach ( $students as $student ) {
				$student->delete ();
			}
			return true;
		} catch ( Exception $e ) {
			return "Error processing bill of :" . $e->getMessage ();
		}
	}

	public static function updateRecordBillStudent($studentid, $term, $year) {
		$billedStudents = new self ();
		$billedStudents->_name = "billedstudents";

		$select = $billedStudents->select ();
		$select->where ( 'cl_GPSN_ID =?', $studentid )
			->where ( 'term =?', $term )
			->where ( 'year =?', $year );

		$bill = $billedStudents->fetchRow ( $select );
		try {
			if ($bill) {
				$bill->cl_GPSN_ID = $studentid;
				$bill->term = $term;
				$bill->year = $year;
				$bill->save ();
			}
			return "Update billing of $bill->cl_GPSN_ID successful";
		} catch ( Exception $e ) {
			return "Error updating processing bill of :" . $e->getMessage ();
		}
	}

	public static function billablesList() {
		$tModel = new self ();

		$select = $tModel->select ();
		$select->from ( $tModel, array (
				'description' ) );
		$select->distinct ( true );

		$result = $tModel->fetchAll ( $select );

		return $result;
	}

	public static function getPastBills() {
		// replication of past bills
		$tModel = new self ();
		$select = $tModel->select ();
		$select->group ( 'term' )->group ( 'year' );
		$results = $tModel->fetchAll ( $select );
		return $results;
	}

	public static function billsExist($term, $year) {
		$tModel = new self ();
		$select = $tModel->select ();
		$select->where ( 'term =?', $term )->where ( 'year =?', $year );
		$results = $tModel->fetchAll ( $select );
		if ($results->count () > 0)
			return true;
		else
			return false;
	}

	public static function importpastbills($term, $year, $next = 0) {

		$currentPeriod = Content_Model_School::getCurrentTermYear ( false );

		if ($next == 0) { // import into current
			if (! Content_Model_School::isCurrentPeriod (
								$currentPeriod->term,
								$currentPeriod->year )) {
				return - 100;
			} else {
				// import into current
				if (self::billsExist ( $currentPeriod->term, $currentPeriod->year ))
					return - 99;

				$tModel = new self ();
				$select = $tModel->select ();
				$select->where ( 'term =?', $term )->where ( 'year =?', $year );
				$pbills = $tModel->fetchAll ( $select );

				if ($pbills->count () > 0) {
					foreach ( $pbills as $bill ) {
						$row = $tModel->createRow ();
						/**
						 * *
						 * description varchar(255) NOT NULL,
						 * feegroup int(11) NOT NULL DEFAULT - 1,
						 * specificgrades int(11) DEFAULT NULL,
						 * amount float NOT NULL,
						 * mandatory int(11) NOT NULL DEFAULT 1,
						 * term int(11) NOT NULL,
						 * year varchar(9) NOT NULL,
						 * type int(11) NOT NULL,
						 */
						$row->description = $bill->description;
						$row->feegroup = $bill->feegroup;
						$row->specificgrades = $bill->specificgrades;
						$row->amount = $bill->amount;
						$row->mandatory = $bill->mandatory;
						$row->type = $bill->type;
						$row->term = $currentPeriod->term;
						$row->year = $currentPeriod->year;
						$row->save ();
					}

					return 1;
				}
				return 0;
			}
		} else if ($next == 1) {
			$nextterm = Content_Model_Attendance::getNextTerm ();
			if (! $nextterm) {
				return - 101;
			} else {
				// import into next term;
				if (self::billsExist ( $nextterm->term, $nextterm->year ))
					return - 99;

				$tModel = new self ();
				$select = $tModel->select ();
				$select->where ( 'term =?', $term )->where ( 'year =?', $year );
				$pbills = $tModel->fetchAll ( $select );

				if ($pbills->count () > 0) {
					foreach ( $pbills as $bill ) {
						$row = $tModel->createRow ();
						/**
						 * *
						 * description varchar(255) NOT NULL,
						 * feegroup int(11) NOT NULL DEFAULT - 1,
						 * specificgrades int(11) DEFAULT NULL,
						 * amount float NOT NULL,
						 * mandatory int(11) NOT NULL DEFAULT 1,
						 * term int(11) NOT NULL,
						 * year varchar(9) NOT NULL,
						 * type int(11) NOT NULL,
						 */
						$row->description = $bill->description;
						$row->feegroup = $bill->feegroup;
						$row->specificgrades = $bill->specificgrades;
						$row->amount = $bill->amount;
						$row->mandatory = $bill->mandatory;
						$row->type = $bill->type;
						$row->term = $nextterm->term;
						$row->year = $nextterm->year;
						$row->save ();
					}
				}
				return 1;
			}
		}

		return 0;
	}
}
