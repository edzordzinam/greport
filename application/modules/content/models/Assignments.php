<?php

/**
 * Assignments
 *
 * @author Edzordzinam
 * @version
 */

require_once 'Zend/Db/Table/Abstract.php';

class Content_Model_Assignments extends Zend_Db_Table_Abstract {
	/**
	 * The default table name
	 */
	protected $_name = 'assignments';

	public function newAssignment($instructor, $topic, $type, $year, $term, $date, $datedued, $maxmark, $course, $grade){

		$assignmentRow = $this->createRow();

		if ($assignmentRow) {
			$assignmentRow->cl_instructor = $instructor;
			$assignmentRow->cl_topic = $topic;
			$assignmentRow->cl_type = $type;
			$assignmentRow->cl_year = $year;
			$assignmentRow->cl_term = $term;
			$assignmentRow->cl_date = $date;
			$assignmentRow->cl_datedued = $datedued;
			$assignmentRow->cl_course = $course;
			$assignmentRow->cl_grade = $grade;
			$assignmentRow->cl_maxmark = $maxmark;
			$assignmentRow->save();
 		
			$id = $assignmentRow->cl_id;
			//$select = $this->select()->limit(1)->order('cl_id DESC');
			//$assessment = $this->fetchAll($select)->getRow(0, true);

			//$id = $assessment->cl_id;

			Content_Model_AssignmentMarks::initiateMarks($id, $grade);

			return array('id' => $id, 'topic' => $topic);
		}
		else
		{
			throw new Zend_Exception("Assignment update failed, contact administrator");
		}

	}

	public function updateAssignment($id, $topic, $type, $year, $term, $date, $datedued, $maxmark, $course, $grade){

	    $assignmentRow = $this->find($id)->current();

	    if ($assignmentRow) {
	        $assignmentRow->cl_topic = $topic;
	        $assignmentRow->cl_type = $type;
	        $assignmentRow->cl_year = $year;
	        $assignmentRow->cl_term = $term;
	        $assignmentRow->cl_date = $date;
	        $assignmentRow->cl_datedued = $datedued;
	        $assignmentRow->cl_course = $course;
	        
	        //check if grade has changed
	        if ($assignmentRow->cl_grade != $grade){
	        	Content_Model_AssignmentMarks::deInitializeMarks($assignmentRow->cl_id);
	        	Content_Model_AssignmentMarks::initiateMarks($assignmentRow->cl_id, $grade);
	        }	        	
	        	
	        $assignmentRow->cl_grade = $grade;
	        $assignmentRow->save();

	        $select = $this->select()->limit(1)->order('cl_id DESC');
	        $assessment = $this->fetchAll($select)->getRow(0, true);

	        return array('id' => $assessment->cl_id, 'topic' => $assessment->cl_topic);
	    }
	    else
	    {
	        throw new Zend_Exception("Assignment update failed, contact administrator");
	    }

	}

	public function deleteAssignment($assignment){
		//deinitialize the assignment marks
	 try {
	 	$assignmentMarks = new Content_Model_AssignmentMarks();

	 	if (Content_Model_AssignmentMarks::deInitializeMarks($assignment)){

	 		$where = array();

	 		$where[] = $this->getAdapter()->quoteInto('cl_id =?', $assignment);

	 		$row = $this->delete($where);
	 	}
	 } catch (Zend_Exception $e) {
	 	throw new Zend_Exception('Error in deleting assignment with id: '. $assignment);
	 }

	}

	public function listAssignments($all = true, $instructor = -1, $current = true, $term = 0, $year = '' ){

		$assignmentModel = new self();
		$select = $assignmentModel->select();

		if ($current){
			$cp = Content_Model_School::getCurrentTermYear();
			$term = $cp->term;
			$year = $cp->year;
		}

		if ($all){
			//$select->where('cl_course IN (?)', $allowedCourses[0] )
			//	   ->where('cl_grade IN (?)', $allowedGrades[0]);
		}
		else
		{
			$select->where('cl_instructor =?', $instructor)
					->where('cl_term =?', $term)
					->where('cl_year =?', $year);
		}

		$select->order(array('cl_id DESC', 'cl_date', 'cl_course'));

		$result = $assignmentModel->fetchAll($select);

		if ($instructor != NULL && $result->count() > 0){
			return $result;
		}
		else
			return null;
	}

	public function filterlistAssignments($all = true, $instructor = -1, $current = true, $courseid = 0, $gradeid = 0, $term = 0, $year = ''){

		$assignmentModel = new self();
		$select = $assignmentModel->select();


		if ($current){
			$cp = Model_Attendance::getCurrentTerm();
			$term = $cp->term;
			$year = $cp->year;
		}

		if ($all){
			$select->where('cl_course =?', $courseid)
					->where('cl_grade =?', $gradeid);		}
		else
		{
			$select->where('cl_instructor =?', $instructor)
					->where('cl_course =?', $courseid)
					->where('cl_grade =?', $gradeid)
					->where('cl_term =?', $term)
					->where('cl_year =?', $year);
		}

		$select->order(array('cl_id DESC', 'cl_date', 'cl_course','cl_type'));

		$result = $assignmentModel->fetchAll($select);

		if ($instructor != NULL && $result->count() > 0){
			return $result;
		}
		else
			return null;
	}

	public static function assignmentDetail($id){

		$assignmentModel = new self();

		$select = $assignmentModel->select();
		$select->where('cl_id = ?', $id);
		$select->limit(1);

	    $result = $assignmentModel->fetchAll($select)->toArray();

	    $result[0]['cl_coursename']= Content_Model_Course::getCourseName($result[0]['cl_course']);
	    $result[0]['cl_gradename'] = Custom_Grades::getGradeName($result[0]['cl_grade']);

	    $result[0]['cl_date'] = date('l, d F, Y', strtotime($result[0]['cl_date']));
	    $result[0]['cl_datedued'] = date('l, d F, Y', strtotime($result[0]['cl_datedued']));
	    $result[0]['day'] = date('d', strtotime($result[0]['cl_date']));
	    $result[0]['month'] = date('M', strtotime($result[0]['cl_date']));

	    return $result[0]; //returns the appropriate row
	}

	public static function maximumMarks($course, $term, $year, $grade){
		$assignmentModel = new self();

		$select = $assignmentModel->select()->from($assignmentModel, array('sum(cl_maxmark) as maximum'));;
		$select->where('cl_course =?', $course)
		 	   ->where('cl_term =?',  $term)
		 	   ->where('cl_year =?', $year)
			   ->where('cl_grade =?', $grade);

		return $assignmentModel->fetchAll($select)->toArray();

	}

	public static function updateMaxMark($assignmentid, $maxmark){
		$assignmentModel = new self();

		$select = $assignmentModel->select();
		$select->where('cl_id = ?', $assignmentid);
		$select->limit(1);

		$highestMark = Content_Model_AssignmentMarks::highestMark($assignmentid);

		if ($maxmark >= $highestMark->highest){
			$assignmentRow = $assignmentModel->find($assignmentid)->current();
			if ($assignmentRow){
				$assignmentRow->cl_maxmark = $maxmark;
				$assignmentRow->save();
				return $maxmark;
			}
			else
			{
				throw new Zend_Exception("Unable to add mark for the specified student, contact admin");
			}
		}
		else
			return 'Invalid mark';
	}

	public static function assignNumber($course, $term, $year, $grade, $type){
		$assignmentModel = new self();

		$select = $assignmentModel->select();
		$select->where('cl_course =?', $course)
		->where('cl_term =?',  $term)
		->where('cl_year =?',  $year)
		->where('cl_grade =?',  $grade)
		->where('cl_type =?',  $type);

		$num = 1;
		$num += $assignmentModel->fetchAll($select)->count();

		return $num;
	}

	public function assignmentStatistics($instructor, $course, $year, $term, $grade){
		$assignmentModel = new self();

		$select = $assignmentModel->select();
		$select->from($assignmentModel, array('COUNT(cl_type) as `count`', 'SUM(cl_maxmark) as `totalmarks`' ,'*'))
				->where('cl_course =?', $course)
				->where('cl_term =?',  $term)
				->where('cl_year =?',  $year)
				->where('cl_grade =?',  $grade)
				->where('cl_instructor =?',  $instructor)
				->group('cl_type');


		$result = $assignmentModel->fetchAll($select);

		if ($result->count() > 0)
			return $result;
		else
			return null;
	}

	public static function getTotalAssignments($instructor, $term, $year){
        $aModel = new self();

        $select = $aModel->select();
        $select->from($aModel, array('count(cl_id) as total'));
        
        if ($instructor != -99){        
	        $select->where('cl_instructor =?', $instructor)
	               ->where('cl_term =?', $term)
	               ->where('cl_year =?', $year);
        }else{
        	$select->where('cl_term =?', $term)
        			->where('cl_year =?', $year);
        }

        $result = $aModel->fetchRow($select);

        return $result->total;
	}

    public static function getInstructorAssignmentsID($instructor, $term, $year){
        $aModel = new self();

        $select = $aModel->select();
        $select->from($aModel, array('cl_id'));
        $select->where('cl_instructor =?', $instructor)
        ->where('cl_term =?', $term)
        ->where('cl_year =?', $year);

        $results = $aModel->fetchAll($select);
        $keys = array();

        foreach ($results as $result) {
            $keys[] = $result->cl_id;
        }
        return $keys;
    }

    public static function listAssessments($displayStart, $displayLength, $search, $iSortCol, $sSortDir, $instructor, $course, $term, $year, $grade){
          $aModel = new self();
          $aModel->_name = 'vw_assignments';
          $aModel->_primary = 'cl_id';

          $select = $aModel->select();

          $assignTotal = $aModel->getAdapter()->fetchOne("SELECT COUNT(cl_id) AS count FROM vw_assignments WHERE cl_instructor = $instructor and cl_term = $term and cl_year ='$year'");

          try {
              $select->from($aModel, array( new Zend_Db_Expr('SQL_CALC_FOUND_ROWS cl_id'), '*'));

              $select->where('cl_instructor =?', $instructor)
                     ->where('cl_course =?', $course)
                     ->where('cl_term =?', $term)
                     ->where('cl_year =?', $year)
                     ->where('cl_grade =?',$grade);

              //paging
              if ($displayLength != -1)
                  $select->limit($displayLength, $displayStart);

              //column filtering
              if ($search != "")
                  $select->where("cl_topic like '%$search%'")
                  ->orWhere("cl_coursename like '%$search%'")
                  ->orWhere("cl_type like '%$search%'");
              //order by
              $columns = array('cl_date','cl_topic','cl_type','cl_grade','cl_maxmark');
              if ($iSortCol != null)
                  $select->order("$columns[$iSortCol] $sSortDir");

              $assignments = $aModel->fetchAll($select);

              $db = $aModel->getDefaultAdapter();
              $db->setFetchMode(Zend_Db::FETCH_OBJ);
              $idisplayTotal = $db->fetchAll('select FOUND_ROWS() as ct');

              if($assignments){
                  return array('data' => $assignments, 'iTotalRecords' => $assignTotal, 'iDisplayRecords' => $idisplayTotal[0]->ct);
              }
              else
                  return null;

          } catch (Exception $e) {
              Zend_Debug::dump($e->getMessage());
          }
    }

    public static function assignmentStatisticsAll($instructor, $year, $term){
        $aModel = new self();
        $db = $aModel->getDefaultAdapter();

        try {
            $stmt = $db->query(
                    'call sp_assessmentstats(?,?,?);',
                    array($instructor,$term,$year)
            );

            $results = $stmt->fetchAll(Zend_Db::FETCH_OBJ);
            return $results;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function pastCourses($term, $year, $grade, $json = false){
    	$aModel = new self();
    	$aModel->_name = 'assignments';
    
    	/*select DISTINCT cl_course,
    		(select cl_coursename from courses where cl_courseid = assignments.cl_course) as cl_coursename,
    	(select cl_courseorder from courses where cl_courseid = assignments.cl_course) as cl_courseorder,
    	(select cl_gradeexempt from courses where cl_courseid = assignments.cl_course) as cl_gradeexempt,
    	(select ( AVG(cl_mark) > 10 AND NOT ISNULL(AVG(cl_mark))) from exammarks where exammarks.cl_term = assignments.cl_term and exammarks.cl_year = assignments.cl_year and exammarks.cl_grade = assignments.cl_grade  and cl_courseid = assignments.cl_course) as cl_examinable
    	from assignments
    	where cl_term = 3 and cl_year = '2011/2012' and cl_grade = 7
    	order by cl_courseorder */
    
    	$select = $aModel->select();
    	$select	->from($aModel,array('cl_course as cl_courseid',
    			new Zend_Db_Expr('(select cl_coursename from courses where cl_courseid = assignments.cl_course) as cl_coursename'),
    			new Zend_Db_Expr('(select cl_courseorder from courses where cl_courseid = assignments.cl_course) as cl_courseorder'),
    			new Zend_Db_Expr('(select cl_gradeexempt from courses where cl_courseid = assignments.cl_course) as cl_gradeexempt'),
    			new Zend_Db_Expr('(select (AVG(cl_mark) > 10 AND NOT ISNULL(AVG(cl_mark))) from exammarks where exammarks.cl_term = assignments.cl_term and exammarks.cl_year = assignments.cl_year and exammarks.cl_grade = assignments.cl_grade  and cl_courseid = assignments.cl_course) as cl_examinable')))
	    			->distinct(true)
	    			->where('cl_term =?', $term)
	    			->where('cl_year =?', $year)
	    			->where('cl_grade =?',$grade)
	    			->order('cl_courseorder DESC')->order('cl_examinable DESC')->order('cl_coursename ASC');
    
    	$results = $aModel->fetchAll($select);  
    	
    	if ($results)
    		return $results;
    	else
    		return null;
    }



}
