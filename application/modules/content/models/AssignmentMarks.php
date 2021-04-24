<?php

/**
 * AssignmentMarks
 *
 * @author Edzordzinam
 * @version
 */

require_once 'Zend/Db/Table/Abstract.php';

class Content_Model_AssignmentMarks extends Zend_Db_Table_Abstract {
	/**
	 * The default table name
	 */
	protected $_name = 'assignmentmarks';


	/**
	 * Function gets the list of ids of student and
	 * enters them into the marks table and initialized their marks to zero
	 * for subsequent manipulation of the marks
	 * @param integer $assignmentid
	 * @param integer $grade
	 */
	public static function initiateMarks($assignmentid, $grade){
            try {
                $marksModel = new self();

                $marksModel->_name = 'assignmentmarks';

                foreach (Content_Model_Students::studentsInGrade($grade) as $row) {
                    $markRow = $marksModel->createRow();
                    if ($markRow) {
                        $markRow->cl_studentid = $row->cl_GPSN_ID;
                        $markRow->cl_assignmentid = $assignmentid;
                        $markRow->save();
                    }
                }
            } catch (Exception $e) {
                Zend_Debug::dump($e->getMessage() . 'initiatite method');
            }
	 }


	/**
	 * Function to return the list of students in the grade who
	 * have been initialized for the assignment for markes input
	 * @param integer $assignmentid
	 */
	public static function listScoresList($assignmentid){
		$marksModel = new self();
		$marksModel->_name = 'vw_assignmentmarks';

		$marksModel->_primary = array('cl_assignmentid' , 'cl_studentid');
		$select = $marksModel->select();
		$select -> where('cl_assignmentid = ?', $assignmentid)
				->order('cl_studentid');

		return $marksModel->fetchAll($select);
	}


	public static function deInitializeMarks($assignmentid){
		//delete all the marks associated with a particular assignment....
	  try{
		$assignmentMarks = new self();
		$where = array();
		$where[] = $assignmentMarks->getAdapter()->quoteInto('cl_assignmentid =?', $assignmentid);
		$row = $assignmentMarks->delete($where);
		return true;
	  }
	  catch (Zend_Exception $e){
	  	throw new Zend_Exception('Error in de-initializing assignment marks for assignment with id :'. $assignmentid);
	  }

	}


	public static function addMark($studentid, $assignmentid, $mark){
        $marksModel = new self();

        try {

            $select = $marksModel->select()->where('cl_assignmentid =?', $assignmentid)->where('cl_studentid =?', $studentid);

            $marksRow = $marksModel->fetchRow($select);
            if ($marksRow){
                $marksRow->cl_mark = $mark;
                $marksRow->save();

                return $mark;
            }
            else
            {
                //throw new Zend_Exception("Unable to add mark for the specified student, contact admin");
                if ($assignmentid != null or $assignmentid != 0){
                    $newRow = $marksModel->createRow();
                    $newRow->cl_studentid = $studentid;
                    $newRow->cl_assignmentid = $assignmentid;
                    $newRow->cl_mark = $mark;
                    $newRow->save();

                    return $mark;
                    }
                else
                    return 'Invalid assignment was received';
            }
        } catch (Exception $e) {
            Zend_Debug::dump($e->getMessage());
        }

	}


	public static function addExempt($studentid, $assignmentid, $exempt){
        $marksModel = new self();

		$marksModel->_primary = array('cl_assignmentid', 'cl_studentid');

		$marksRow = $marksModel->find(array($assignmentid), array($studentid))->current();
		if ($marksRow){
			$marksRow->cl_exempt = $exempt;
			$marksRow->save();
			return $exempt;
		}
		else
		{
		    if ($assignmentid != null or $assignmentid != 0){
		        $newRow = $marksModel->createRow();
		        $newRow->cl_studentid = $studentid;
		        $newRow->cl_assignmentid = $assignmentid;
		        $newRow->cl_exempt = $exempt;
		        $newRow->save();
		        return $exempt;
		    }
		}

	}


	public static function highestMark($assignmentid){
		$assignmarks = new self();
		$select = $assignmarks->select();

		$select->from($assignmarks, array('Max(cl_mark) as highest'))
				->where('cl_assignmentid =?', $assignmentid);

		$result = $assignmarks->fetchRow($select);

		return $result;
	}

	public static function getExemptTotal($studentid, $term, $year, $grade, $courseid, $type){
		$AssignModel = new self();
		$AssignModel->_name = 'vw_getexempt';
		$AssignModel->_primary = array('cl_studentid', 'cl_term', 'cl_year', 'cl_grade', 'cl_course', 'cl_type', );

		$select = $AssignModel->select();
		$select->where('cl_studentid =?', $studentid)
				->where('cl_term =?', $term)
				->where('cl_year =?', $year)
				->where('cl_grade =?', $grade)
				->where('cl_course =?', $courseid)
				->where('cl_type =?', $type);

		$ExemptRow = $AssignModel->fetchRow($select);

		if ($ExemptRow)
			return $ExemptRow;
		else
			return null;
	}

	public static function getAllExemptTotal($studentid, $term, $year, $grade, $courseid, $type){
		$AssignModel = new self();
		$db = $AssignModel->getDefaultAdapter();

		try {
			$stmt = $db->query(
					'call sp_getexempttotal_all(?,?,?,?,?,?);',
					array($courseid,$term,$year,$grade, $type, $studentid)
			);

			$result = $stmt->fetchAll(Zend_Db::FETCH_OBJ);
		}catch (Exception $e) {
			return $e->getMessage();
		}

		if ($result){
			//Zend_Debug::dump($result);
			return $result;
		}else
			return null;

	}


	public static function getGradableAssignmentsCount($assignmentkeys){
	    $amModel = new self();

        $select = $amModel->select();
        $select->from($amModel, array('count(cl_markid) as total'));
        $select->where('cl_assignmentid in (?)', $assignmentkeys);

        $result = $amModel->fetchRow($select);

        return $result->total;
	}

	public static function getGradedAssignmentsCount($assignmentkeys){
	    $amModel = new self();

	    $select = $amModel->select();
	    $select->from($amModel, array('count(cl_markid) as total'));
	    $select->where('cl_assignmentid in (?)', $assignmentkeys)
	            ->where('cl_mark is not null');

	    $result = $amModel->fetchRow($select);

	    return $result->total;
	}

	public static function getUnGradedAssignmentsCount($assignmentkeys){
	    $amModel = new self();

	    $select = $amModel->select();
	    $select->from($amModel, array('count(cl_markid) as total'));
	    $select->where('cl_assignmentid in (?)', $assignmentkeys)
	            ->where('cl_mark is null')->where('NOT cl_exempt = 1');

	    $result = $amModel->fetchRow($select);

	    return $result->total;
	}

	public static function getExemptAssignmentsCount($assignmentkeys){
		$amModel = new self();

		$select = $amModel->select();
		$select->from($amModel, array('count(cl_markid) as total'));
		$select->where('cl_assignmentid in (?)', $assignmentkeys)
				->where('cl_exempt = 1');

		$result = $amModel->fetchRow($select);

		return $result->total;
	}


	public static function getUnGradedAssignments($displayStart, $displayLength, $search, $iSortCol, $sSortDir, $assignmentkeys, $zero){
	    $amModel = new self();
	    $amModel->_name = 'vw_assignmentmarks_stds';
	    $amModel->_primary = 'cl_markid';

	    try {
	    	$select = $amModel->select();
	    	$select->from($amModel, array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS cl_markid'), '*'));

	    	//paging
	    	if ($displayLength != -1)
	    		$select->limit($displayLength, $displayStart);

	    	if ($search != "")
	    		$select->where("fullname like '%$search%' or cl_topic like '%$search%'");


			    switch ($zero) {
			    	case 0:
			    		$ungradedTotal = Content_Model_AssignmentMarks::getUnGradedAssignmentsCount($assignmentkeys);
			    		$select->where('cl_assignmentid in (?)', $assignmentkeys)
			    			->where('cl_mark is null')->where('NOT cl_exempt = 1');
			    	break;

			    	case 1 :
			    		$ungradedTotal = Content_Model_AssignmentMarks::getZeroAssignmentsCount($assignmentkeys);
			    		$select->where('cl_assignmentid in (?)', $assignmentkeys)
			    				->where('cl_mark = 0')->where('NOT cl_exempt = 1');
			    	break;

			    	case -1 :
			    		$ungradedTotal = Content_Model_AssignmentMarks::getExemptAssignmentsCount($assignmentkeys);
			    		$select->where('cl_assignmentid in (?)', $assignmentkeys)
			    					->where('cl_exempt = 1');
			    	break;
			    	default:
			    		;
			    	break;
			    }
                //order by
                $columns = array('cl_assignmentid','fullname','course', 'cl_topic','cl_maxmark','cl_mark','cl_exempt','cl_grade');
                if ($iSortCol != null)
                    $select->order("$columns[$iSortCol] $sSortDir");

                $ungraded = $amModel->fetchAll($select);

                $db = $amModel->getDefaultAdapter();
                $db->setFetchMode(Zend_Db::FETCH_OBJ);
                $idisplayTotal = $db->fetchAll('select FOUND_ROWS() as ct');

                if($ungraded){
                      return array('data' => $ungraded, 'iTotalRecords' => $ungradedTotal, 'iDisplayRecords' => $idisplayTotal[0]->ct);
                }
                else
                  return null;
            } catch (Exception $e) {
                Zend_Debug::dump($e->getMessage());
            }
	}

	public static function getZeroAssignmentsCount($assignmentkeys){
	    $amModel = new self();

	    $select = $amModel->select();
	    $select->from($amModel, array('count(cl_markid) as total'));
	    $select->where('cl_assignmentid in (?)', $assignmentkeys)
	            ->where('cl_mark = 0')->where('NOT cl_exempt = 1');

	    $result = $amModel->fetchRow($select);

	    return $result->total;
	}

	public static function getPastAssignmentStudents($grade, $course, $term, $year, $student = -1, $toArray = true){
		$studentModel = new self();
		$studentModel->_name = 'vw_assignmentstudents';
		$studentModel->_primary = 'cl_GPSN_ID';

		try {
			$select = $studentModel->select();
			$select->where('cl_Gradelevel = ?', $grade)
					->where('cl_term =?', $term)
					->where('cl_year =?',$year)
					//->where('cl_course =?',$course)
					->group('cl_GPSN_ID')
					->order('fullname');

			if ($student != -1)
				$select->where('cl_GPSN_ID =?', $student);

		 	if ($toArray)
				return $studentModel->fetchAll($select)->toArray();
		 	else
		 		return $studentModel->fetchAll($select);

		} catch (Exception $e) {
			Zend_Debug::dump($e->getMessage());
		}
	}

	public static function getPastAssignmentStudentsCount($grades, $term, $year, $student = -1){
		$studentModel = new self();
		$studentModel->_name = 'vw_assignmentstudents';
		$studentModel->_primary = 'cl_GPSN_ID';

		try {
			$select = $studentModel->select();
			$select//->from($studentModel, array('count(cl_GPSN_ID) as cnt'))
					->where('cl_GradeLevel in (?)', json_decode($grades))
					->where('cl_term =?', $term)
					->where('cl_year =?',$year)
					->group('cl_GPSN_ID')
					->order('fullname');

			if ($student != -1)
				$select->where('cl_GPSN_ID =?', $student);

			return $studentModel->fetchAll($select)->count();
		} catch (Exception $e) {
			Zend_Debug::dump($e->getMessage());
		}
	}

	public static function getRecentStudentAssignments($studentid, $term, $year){
			$studentModel = new self();
			$studentModel->_name = 'vw_assignmentmarks_stds';
			$studentModel->_primary = 'cl_studentid';

			try {
				$select = $studentModel->select();

				$select->where('cl_studentid =?',$studentid)
						->where('cl_term =?', $term)
						->where('cl_year =?',$year)
						->limit(15)->order('cl_date DESC');

				return $studentModel->fetchAll($select);
			} catch (Exception $e) {
				Zend_Debug::dump($e->getMessage());
			}

	}
}
