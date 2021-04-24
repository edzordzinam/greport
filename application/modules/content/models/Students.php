<?php

/**
 * Students
 *
 * @author Edzordzinam
 * @version
 */

require_once 'Zend/Db/Table/Abstract.php';

class Content_Model_Students extends Zend_Db_Table_Abstract {

	/**
	 * The default table name
	 */
	protected $_name = 'students';

	public static function listStudents(
						$displayStart,
						$displayLength,
						$search,
						$iSortCol,
						$sSortDir,
						$current,
						$graduates = false) {

		$studentModel = new self ();

		// retrieving the total number of rows in the courts table
		$studentsTotal = $studentModel->getAdapter ()->fetchOne (
							'SELECT COUNT(cl_GPSN_ID) AS count FROM students' );

		$select = $studentModel->select ();
		$select->from (
							$studentModel,
							array (
									new Zend_Db_Expr ( 'SQL_CALC_FOUND_ROWS cl_GPSN_ID' ),
									'cl_FirstName',
									'cl_LastName',
									'cl_Gender',
									'cl_GradeLevel' ) );
		// $select->columns();

		// paging
		if ($displayLength != - 1)
			$select->limit ( $displayLength, $displayStart );

		$grades = Content_Model_GradeLevels::gradelevels ();
		$grades = $grades + array (
				9999 => 'Graduated' );

		$ser = function ($val) use($search) {
			return (stripos ( $val, $search ) !== false ? true : false);
		};

		$grade = array_keys ( array_filter ( $grades, $ser ) );

		// column filtering
		if ($search != "")
			$select->where (
								"(cl_LastName like '%$search%') OR (cl_FirstName like '%$search%') OR (cl_OtherName like '%$search%') OR (cl_GPSN_ID like '$search')" );

		if (count ( $grade ) > 1)
			$select->orWhere ( "cl_GradeLevel IN (?)", $grade );

		if ($graduates)
			$select->where ( 'cl_GradeLevel =?', 9999 ); // past graduated students
		else {
			if ($current)
				$select->where ( 'cl_Active = ?', 1 );
			else
				$select->where ( 'cl_Active = ?', 0 );
			$select->where ( 'NOT (cl_GradeLevel = ?)', 9999 ); // only current students
		}

		// order by
		// $columns = $studentModel->info(Zend_Db_Table_Abstract::COLS);
		$columns = array (
				0 => 'cl_GPSN_ID',
				1 => 'cl_FirstName',
				2 => 'cl_LastName',
				3 => 'cl_Gender',
				4 => 'cl_Active' );

		$select->order ( "$columns[$iSortCol] $sSortDir" );

		try {
			$students = $studentModel->fetchAll ( $select );

			$db = $studentModel->getDefaultAdapter ();
			$db->setFetchMode ( Zend_Db::FETCH_OBJ );
			$idisplayTotal = $db->fetchAll ( 'select FOUND_ROWS() as ct' );

		} catch ( Exception $e ) {
			Zend_Debug::dump ( $e->getMessage () );
		}

		return array (
				'data' => $students,
				'iTotalRecords' => $studentsTotal,
				'iDisplayRecords' => $idisplayTotal [0]->ct );

	}

	public static function toggleStatus($studentid) {
		$studentModel = new self ();

		$row = $studentModel->find ( $studentid )->current ();

		if ($row) {
			$row->cl_Active = ! $row->cl_Active;
			$row->save ();
			return true;
		} else
			return false;
	}

	public static function newStudent(
						$firstname,
						$lastname,
						$DOB,
						$POB,
						$gender,
						$grade,
						$email,
						$tel,
						$billStudent,
						$resident,
						$primarycontact) {
		$studentModel = new self ();

		$newStudent = $studentModel->createRow ();
		$newID = self::getNewStudentID ();
		$CurrentTerm = Content_Model_School::getCurrentTermYear ( false );
		if ($CurrentTerm != null) {
			$term = $CurrentTerm->term;
			$year = $CurrentTerm->year;
		}

		if ($newStudent) {
			$newStudent->cl_GPSN_ID = $newID;
			$newStudent->cl_FirstName = self::titleCase ( $firstname );
			$newStudent->cl_LastName = self::titleCase ( $lastname );
			$newStudent->cl_DOB = date ( 'Y-m-d', strtotime ( $DOB ) );
			$newStudent->cl_POB = $POB;
			$newStudent->cl_Gender = $gender;
			$newStudent->cl_GradeLevel = $grade;
			$newStudent->cl_ContactEmail = $email;
			$newStudent->cl_ContactTel = $tel;
			$newStudent->cl_DateEnrolled = date ( 'Y-m-d' );
			$newStudent->cl_Resident = $resident;
			$newStudent->cl_PrimaryContact = $primarycontact;
			$newStudent->save ();

			if ($CurrentTerm != null)
				if ($billStudent == 1) {
					$description = "Entry Fees for - term $term of $year";
					Content_Model_Transactions::billNewStudent (
										0,
										$newID,
										$grade,
										date ( 'Y-m-d H:i:s' ),
										$description,
										Zend_Auth::getInstance ()->getIdentity ()->cl_IID,
										null,
										null,
										$term,
										$year );
				}
		}
		return $newID;
	}

	public static function updateStudent(
						$studentid,
						$firstname,
						$lastname,
						$DOB,
						$POB,
						$gender,
						$grade,
						$email,
						$tel,
						$resident,
						$primarycontact) {
		$studentModel = new self ();

		$student = $studentModel->find ( $studentid )->current ();

		if ($student) {
			$student->cl_FirstName = self::titleCase ( $firstname );
			$student->cl_LastName = self::titleCase ( $lastname );
			$student->cl_DOB = date ( 'Y-m-d', strtotime ( $DOB ) );
			$student->cl_POB = $POB;
			$student->cl_Gender = $gender;
			$student->cl_GradeLevel = $grade;
			$student->cl_ContactEmail = $email;
			$student->cl_ContactTel = $tel;
			$student->cl_Resident = $resident;
			$student->cl_PrimaryContact = $primarycontact;
			$student->save ();
		}

	}

	public static function getNewStudentID() {
		$studentModel = new self ();
		$studentModel->_name = 'students';

		$select = $studentModel->select ();

		$select->from ( $studentModel, array (
				"MAX(DISTINCT cl_GPSN_ID) as max_id" ) );

		$result = $studentModel->fetchRow ( $select );

		$max = $result->max_id;

		$max = substr ( $max, 7 );

		// generate new student id for the student....
		/**
		 * the person type for calling this function shall be
		 * 1 - for Students, 2 - for Employees, 3 - for Parents
		 */

		$Partial_IDString = date ( 'y' ) . date ( 'm' ) . date ( 'd' );
		$Partial_IDString = $Partial_IDString . '1'; // students

		// $Last_GPSN_ID = 10; //get the last GPSN_ID sent to the database...
		                                             // execute a storedProc to retrive same

		$Last_GPSN_ID = $max + 1;

		$New_GPSN_ID = $Partial_IDString . str_pad ( $Last_GPSN_ID, 4, "0", STR_PAD_LEFT );

		return $New_GPSN_ID;
	}

	public static function getActiveStudentCount() {
		$studentModel = new self ();

		$select = $studentModel->select ();
		$select->from ( $studentModel, array (
				'Count(cl_GPSN_ID) as activecount' ) )
			->where ( 'NOT (cl_GradeLevel = ?)', 9999 )
			->where ( 'cl_Active = 1' );

		$result = $studentModel->fetchRow ( $select );

		if ($result)
			return $result->activecount;
		else
			return 0;
	}

	public static function getActiveStudentIDs() {
		$studentsModel = new self ();

		$select = $studentsModel->select ();

		$select->from ( $studentsModel, array (
				'cl_GPSN_ID' ) )
			->where ( 'cl_Active =?', 1 )
			->where ( 'NOT (cl_GradeLevel = ?)', 9999 );

		$students = $studentsModel->fetchAll ( $select );

		if ($students)
			return $students;
		else
			return null;
	}

	public static function getStudentInGradeCount($grades) {
		$studentModel = new self ();

		$select = $studentModel->select ();

		$select->from ( $studentModel, array (
				'COUNT(cl_GPSN_ID) as stdcnt' ) )
			->where ( 'cl_Active = 1' )
			->where ( 'NOT (cl_GradeLevel = ?)', 9999 )
			->where ( 'cl_GradeLevel in (?)', json_decode ( $grades ) );

		$result = $studentModel->fetchRow ( $select );

		return $result->stdcnt;
	}

	public static function getStudentsInGrades($grades, $startdate, $enddate) {
		$studentModel = new self ();

		$select = $studentModel->select ();
		$d = date ( 'Y-m-d' );
		$select->from (
							$studentModel,
							array (
									"cl_GPSN_ID",
									"concat_ws(' ',cl_FirstName,cl_LastName) as fullname",
									"cl_ContactEmail",
									"cl_ContactTel",
									new Zend_Db_Expr (
													"(Select if(ISNULL(studentid),false,true) from student_attendance where date(checkedin) = date('$d') and studentid = cl_GPSN_ID) as present" ),
									new Zend_Db_Expr (
													"(select count(studentid) FROM student_attendance WHERE (studentid = cl_GPSN_ID and date(checkedin) >= '$startdate' and date(checkedin) <= '$enddate')) as totalattendance" ) ) )
			->where ( 'cl_Active = 1' )
			->where ( 'NOT (cl_GradeLevel = ?)', 9999 )
			->where ( 'cl_GradeLevel in (?)', json_decode ( $grades ) );

		$result = $studentModel->fetchAll ( $select );

		return $result->toArray ();
	}

	public static function getPastStudentsinGradeCount($grades, $term, $year, $courseid) {
		$studentModel = new self ();
		$studentModel->_name = 'exammarks';

		$select = $studentModel->select ();

		$select->from ( $studentModel, array (
				'COUNT(cl_studentid) as stdcnt' ) )
			->where ( 'cl_term =?', $term )
			->where ( 'NOT (cl_grade = ?)', 9999 )
			->where ( 'cl_year =?', $year )
			->where ( 'cl_grade in (?)', json_decode ( $grades ) )
			->where ( 'cl_courseid =?', $courseid );

		$result = $studentModel->fetchRow ( $select );

		return $result->stdcnt;
	}

	public static function getPastStudentsinGradeCountX($grades, $term, $year) {
		$studentModel = new self ();
		$studentModel->_name = 'preparedreport';

		$select = $studentModel->select ();

		$select->from ( $studentModel, array (
				'COUNT(cl_studentid)' ) )
			->where ( 'cl_term =?', $term )
			->where ( 'cl_year =?', $year )
			->where ( 'cl_grade in (?)', json_decode ( $grades ) )
			->group ( 'cl_studentid' );

		$result = $studentModel->fetchAll ( $select )->count ();

		return $result;
	}

	public static function studentsInGrade($gradelevel, $stream = -100) {

	 	$studentModel = new self ();

		$select = $studentModel->select ()
			->from ( $studentModel, array (
				'cl_GradeLevel',
				'cl_GPSN_ID' ) )
			->where ( 'cl_GradeLevel =?', $gradelevel )
			->where ( 'cl_Active =?', true );

			if (isset($stream))
				if ($stream == 1 || $stream == 0)
					$select->where('cl_Resident =?', $stream);

		return $studentModel->fetchAll ( $select );
	}

	public static function studentsinGradeNames($gradelevel, $student = -1) {
		$studentModel = new self ();
		try {
			$select = $studentModel->select ();
			$select->from (
								$studentModel,
								array (
										'cl_GPSN_ID',
										'concat_ws(\' \',cl_FirstName,cl_LastName) as fullname',
										'cl_GradeLevel',
										'cl_Active' ) )
				->where ( 'cl_gradelevel = ?', $gradelevel )
				->where ( 'cl_Active =?', true )
				->order ( 'cl_FirstName' );

			if ($student != - 1)
				$select->where ( 'cl_GPSN_ID =?', $student );

			return $studentModel->fetchAll ( $select )->toArray ();
		} catch ( Exception $e ) {
			Zend_Debug::dump ( $e->getMessage () );
		}
	}

	public static function cleanStudents() {
		$studentModel = new self ();

		$result = $studentModel->fetchAll ();

		foreach ( $result as $row ) {
			/*
			 * $row->cl_LastName = $studentModel->cleanString($row->cl_LastName); $row->cl_FirstName = $studentModel->cleanString($row->cl_FirstName); $row->cl_OtherName = $studentModel->cleanString($row->cl_OtherName);
			 */

			$row->cl_LastName = self::titleCase ( $row->cl_LastName );
			$row->cl_FirstName = self::titleCase ( $row->cl_FirstName );
			$row->cl_OtherName = self::titleCase ( $row->cl_OtherName );

			$row->save ();
		}
	}

	public static function getStudentName($studentid) {
		$sModel = new self ();
		$select = $sModel->select ()->from (
							$sModel,
							array (
									'cl_LastName',
									'cl_FirstName' ) );
		$select->where ( 'cl_GPSN_ID =?', $studentid );

		$result = $sModel->fetchRow ( $select );

		if ($result)
			return "$result->cl_FirstName $result->cl_LastName";
		else
			return null;
	}

	public static function cleanString($string) {
		$s = preg_replace ( '/[\x00-\x1F\x7F]/', '', $string ); // removing control charge
		$s = preg_replace ( '#\s{2,}#', ' ', $s ); // removing extra spaces
		$s = trim ( $s );
		return $s;
	}

	public static function titleCase(
						$string,
						$delimiters = array(" ", "-", "O'"),
						$exceptions = array("to", "a", "the", "of", "by", "and", "with", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X")) {
		/*
		 * Exceptions in lower case are words you don't want converted Exceptions all in upper case are any words you don't want converted to title case but should be converted to upper case, e.g.: king henry viii or king henry Viii should be King Henry VIII
		 */
		$string = strtolower ( $string );

		foreach ( $delimiters as $delimiter ) {
			$words = explode ( $delimiter, $string );
			$newwords = array ();
			foreach ( $words as $word ) {
				if (in_array ( strtoupper ( $word ), $exceptions )) {
					// check exceptions list for any words that should be in upper case
					$word = strtoupper ( $word );
				} elseif (! in_array ( $word, $exceptions )) {
					// convert to uppercase

					$word = ucfirst ( $word );
				}
				array_push ( $newwords, self::cleanString ( $word ) );
			}

			$string = join ( $delimiter, $newwords );
		}
		return $string;
	}

	public static function studentsInAllGrade() {
		$studentModel = new self ();
		try {
			$select = $studentModel->select ();
			$select->from (
								$studentModel,
								array (
										'cl_GPSN_ID',
										'concat_ws(\' \',cl_FirstName,cl_LastName) as fullname' ) )
				->where ( 'cl_Active =?', true )
				->order ( 'fullname' );

			$results = $studentModel->fetchAll ( $select );

			$selectArray = array ();

			foreach ( $results as $result ) {
				$selectArray [$result->cl_GPSN_ID] = $result->fullname;
			}

			return $selectArray;

		} catch ( Exception $e ) {
			Zend_Debug::dump ( $e->getMessage () );
		}
	}

	public static function getStudentPicture($studentid){

		if (file_exists("./data/student_pixs/$studentid.jpg"))
			return "/data/student_pixs/$studentid.jpg?".time();
		else
			return "/data/student_pixs/nopix.gif";
	}
}
