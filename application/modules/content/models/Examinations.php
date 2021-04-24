<?php

/**
 * Examinations
 *
 * @author Edzordzinam
 * @version
 */

require_once 'Zend/Db/Table/Abstract.php';

class Content_Model_Examinations extends Zend_Db_Table_Abstract {
	/**
	 * The default table name
	 */
	protected $_name = 'exammarks';

	//this is to make it ready for exam records to be entered into the
	//examinations table
	public function initiatiateExamMarks($courseid, $grade, $term, $year){

		$this->_name = 'exammarks';

		if (!$this->isExamsInitialized($courseid, $grade, $term, $year)){
			foreach (Content_Model_Students::studentsInGrade($grade) as $row) {
				$examsRow = $this->createRow();
				if ($examsRow) {
					$examsRow->cl_studentid = $row->cl_GPSN_ID;
					$examsRow->cl_courseid = $courseid;
					$examsRow->cl_term = $term;
					$examsRow->cl_year = $year;
					$examsRow->cl_grade = $grade;
					$examsRow->save();
				}
			}
		}
		else{
			//add new students to the exams table
			foreach (Content_Model_Students::studentsInGrade($grade) as $row) {
				$select = $this->select();
				$select->where('cl_courseid =?', $courseid)
						->where('cl_studentid = ?', $row->cl_GPSN_ID)
						->where('cl_term =?', $term)
						->where('cl_year =?', $year)
						->where('cl_grade =?', $grade);
				$examsRow = $this->fetchRow($select);

				if (!$examsRow){//if student not found add
					$examsRow = $this->createRow();
					if ($examsRow) {
						$examsRow->cl_studentid = $row->cl_GPSN_ID;
						$examsRow->cl_courseid = $courseid;
						$examsRow->cl_term = $term;
						$examsRow->cl_year = $year;
						$examsRow->cl_grade = $grade;
						$examsRow->save();
					}
					//Zend_Debug::dump($row->cl_GPSN_ID);

				}

			}
		}
	}

	public function isExamsInitialized($courseid, $grade, $term, $year){

		$select = $this->select();
		$select->where("cl_courseid = ?", $courseid)
							 ->where('cl_term = ?', $term)
							 ->where('cl_year = ?', $year)
							 ->where('cl_grade =?', $grade);

		$count = $this->fetchAll($select)->count();

		if ($count > 0) {
				//exams already initialized
				return true;
			}
			else
			{
				return false;
		}

	}

	public static function resetExamMarks($courseid, $grade, $term, $year){

	    $eModel = new self();

		$select = $eModel->select();

		$shared = Content_Model_Course::isShared($courseid);
		$instructor = Zend_Auth::getInstance()->getIdentity()->cl_IID;

		if (!$shared){
    		$select->where('cl_courseid = ?', $courseid)
    						->where('cl_term = ?', $term)
    						->where('cl_year = ?', $year)
    						->where('cl_grade =?', $grade);

    		$count = $eModel->fetchAll($select)->count();

    		if ($count > 0){
    			$eModel->delete(array(
    					 'cl_courseid = ?' => $courseid,
    					 'cl_term = ?' =>  $term,
    					 'cl_year = ?' =>  $year,
    					 'cl_grade =?' => $grade
    					));
    		}
		}
		else{
		    $select->where('cl_courseid = ?', $courseid)
            		    ->where('cl_term = ?', $term)
            		    ->where('cl_year = ?', $year)
            		    ->where('cl_grade =?', $grade)
		                ->where('cl_instructor =?',$instructor);

		    $count = $eModel->fetchAll($select)->count();

		    if ($count > 0){
		        $eModel->delete(array(
		                'cl_courseid = ?' => $courseid,
		                'cl_term = ?' =>  $term,
		                'cl_year = ?' =>  $year,
		                'cl_grade =?' => $grade,
		                'cl_instructor =?' => $instructor
		        ));
		    }
		}

		self::deleteMarkOver($courseid, $grade, $term, $year, $shared, $instructor);
	}

	public static function deleteMarkOver($courseid, $grade, $term, $year, $shared, $instructor){

	    $eModel = new self();
	    $eModel->_name ='examsmarkover';

	    $select = $eModel->select();

	    if (!$shared){
	        $select->where('course = ?', $courseid)
	        ->where('term = ?', $term)
	        ->where('year = ?', $year)
	        ->where('grade =?', $grade);

	        $count = $eModel->fetchAll($select)->count();

	        if ($count > 0){
	            $eModel->delete(array(
	                    'course = ?' => $courseid,
	                    'term = ?' =>  $term,
	                    'year = ?' =>  $year,
	                    'grade =?' => $grade
	            ));
	        }
	    }
	    else{
	        $select->where('course = ?', $courseid)
	        ->where('term = ?', $term)
	        ->where('year = ?', $year)
	        ->where('grade =?', $grade)
	        ->where('instructor =?',$instructor);

	        $count = $eModel->fetchAll($select)->count();

	        if ($count > 0){
	            $eModel->delete(array(
	                    'course = ?' => $courseid,
	                    'term = ?' =>  $term,
	                    'year = ?' =>  $year,
	                    'grade =?' => $grade,
	                    'instructor =?' => $instructor
	            ));
	        }
	    }
	}

	public static function getExamsList($term, $year, $grade, $course){
		$examsModel = new self();

		$examsModel->_name = "vw_studentexammarks";

		$examsModel->_primary = array("cl_studentid", "cl_term", "cl_year", "cl_courseid", "cl_grade");


		$select = $examsModel->select();

		$select->where('cl_term =?', $term)
				->where('cl_year = ?', $year)
				->where('cl_courseid = ?', $course)
				->where('cl_grade =?', $grade);

		return $examsModel->fetchAll($select);

	}

	public function addMark($studentid, $term, $year, $course, $grade,  $mark){

	    $shared = Content_Model_Course::isShared($course);
	    $instructor = Zend_Auth::getInstance()->getIdentity()->cl_IID;

		if ($shared){
		    $this->_primary = array("cl_studentid", "cl_term", "cl_year", "cl_courseid", "cl_grade", "cl_instructor");
		    $marksRow = $this->find(array($studentid), array($term), array($year) , array($course), array($grade), array($instructor) )->current();
		}else{
		    $this->_primary = array("cl_studentid", "cl_term", "cl_year", "cl_courseid", "cl_grade");
		    $marksRow = $this->find(array($studentid), array($term), array($year) , array($course), array($grade) )->current();
		}

		if ($marksRow){
			$marksRow->cl_mark = $mark;
			$marksRow->save();
			return $mark;
		}
		else
		{
		    //due to no initialization add mark for those without one
		    $examsRow = $this->createRow();
		    if ($examsRow) {
		       $examsRow->cl_studentid = $studentid;
		       $examsRow->cl_courseid = $course;
		       $examsRow->cl_term = $term;
		       $examsRow->cl_year = $year;
		       $examsRow->cl_grade = $grade;
		       if ($shared)
		           $examsRow->cl_instructor = $instructor;
		       $examsRow->cl_mark = $mark;
		       $examsRow->save();
		   }
		}
	}

	public function addExempt($studentid, $term, $year, $course, $grade,  $exempt){

	    $shared = Content_Model_Course::isShared($course);
        $instructor = Zend_Auth::getInstance()->getIdentity()->cl_IID;

	    if ($shared){
	        $this->_primary = array("cl_studentid", "cl_term", "cl_year", "cl_courseid", "cl_grade", "cl_instructor");
	        $marksRow = $this->find(array($studentid), array($term), array($year) , array($course), array($grade), array($instructor) )->current();
	    }else{
		    $this->_primary = array("cl_studentid", "cl_term", "cl_year", "cl_courseid", "cl_grade");
		    $marksRow = $this->find(array($studentid), array($term), array($year) , array($course), array($grade) )->current();
	    }

		if ($marksRow){
			$marksRow->cl_exempt = $exempt;
			$marksRow->save();
			return $exempt;
		}
		else
		{
			$examsRow = $this->createRow();
		    if ($examsRow) {
		       $examsRow->cl_studentid = $studentid;
		       $examsRow->cl_courseid = $course;
		       $examsRow->cl_term = $term;
		       $examsRow->cl_year = $year;
		       $examsRow->cl_grade = $grade;
		       if ($shared)
		           $examsRow->cl_instructor = $instructor;
		       $examsRow->cl_exempt = $exempt;
		       $examsRow->save();
		   }
		}

	}

	public static function addComment($examid, $cnt, $update = true){
		$eModel = new self();

		$eModel->_primary = array("cl_examid");

		$marksRow = $eModel->find($examid)->current();

		if ($marksRow and $update){
			$marksRow->cl_comment = $cnt;
			$marksRow->save();

			return 'updated';
		}
		else
		{
		 if ($marksRow){
			if ($marksRow->cl_comment != NULL)
				return array('comment'=>$marksRow->cl_comment, 'studentid' => $marksRow->cl_studentid);
			else
				return null;
		 }
		 else
		 	return null;
		}
	}

	public static function updateComment($examid, $cnt){
		$eModel = new self();

		$eModel->_primary = array("cl_examid");

		$marksRow = $eModel->find($examid)->current();

		if ($marksRow){
			$marksRow->cl_comment = $cnt;
			$marksRow->save();

			return 'updated';
		}

	}

	public static function updateNewComment($studentid, $course, $grade, $term, $year, $cnt){
		$eModel = new self();

		$shared = Content_Model_Course::isShared($course);

 		$select = $eModel->select();
		$select->where('cl_studentid =?', $studentid)
				->where('cl_term =?', $term)
				->where('cl_year =?', $year)
				->where('cl_grade =?', $grade)
				->where('cl_courseid =?', $course);

		$result = $eModel->fetchRow($select);

		if ($result == null){

			$marksRow = $eModel->createRow();

			if ($marksRow){
				$marksRow->cl_studentid = $studentid;
				$marksRow->cl_courseid = $course;
				$marksRow->cl_grade = $grade;
				$marksRow->cl_term = $term;
				$marksRow->cl_year = $year;
				$marksRow->cl_comment = $cnt;
				if ($shared){
					$marksRow->cl_instructor = Zend_Auth::getInstance()->getIdentity()->cl_IID;
				}
				$marksRow->save();

				return $marksRow->cl_examid;
			}
		}
	}

	public static function markedover($term, $year, $course, $grade,  $markedover, $update = true){
		$markover = new self();
		$markover->_name ='examsmarkover';

		$select = $markover->select();

		$select->where("course = ?", $course)
				->where('term = ?', $term)
				->where('year = ?', $year)
				->where('grade =?', $grade);

		if (Content_Model_Course::isShared($course)){
            $select->where('instructor =?', Zend_Auth::getInstance()->getIdentity()->cl_IID);
		}

		$row = $markover->fetchRow($select);

		$highest = self::highestMark($term, $year, $course, $grade);

		if ($update){
				if ($row){
				  if ($highest <= $markedover){
					$row->markover = $markedover;
					$row->save();
					return $markedover;
				  }
				  else
				  {
				  	return -1;
				  }
				}
				else
				{
					$newRow = $markover->createRow();
					if ($newRow){
						$newRow->term = $term;
						$newRow->year = $year;
						$newRow->grade = $grade;
						$newRow->course = $course;
						if (Content_Model_Course::isShared($course))
						    $newRow->instructor = Zend_Auth::getInstance()->getIdentity()->cl_IID;
						$newRow->markover = $markedover;
						$newRow->save();
						return $markedover;
					}
				}
		}
		else{
		  if ($row)
			return $row->markover;
		  else {
		  	return 0;
		  }
		}
	}

	public static function markedOverReport($term, $year, $course, $grade,  $markedover, $update = true){
		$markover = new self();
		$markover->_name ='examsmarkover';

		$select = $markover->select();

		$select->from($markover,array('sum(markover) as markover'))
				->where("course = ?", $course)
				->where('term = ?', $term)
				->where('year = ?', $year)
				->where('grade =?', $grade)
				->group("course");

		$row = $markover->fetchRow($select);

			if ($row)
				return $row->markover;
			else {
				return 0;
			}
	}

	public static function highestMark($term, $year, $course, $grade){
		$marks = new self();

		$select = $marks->select();
		$select->from($marks, array('Max(cl_mark) as highest'))
				->where('cl_term =?',$term)
				->where('cl_year =?',$year)
				->where('cl_grade=?',$grade)
				->where('cl_courseid=?',$course);
		$row = $marks->fetchRow($select);

		if($row->highest != null)
			return $row->highest;
		else
			return 0;

	}

    public static function getExamScoreList($displayStart, $displayLength, $search, $iSortCol, $sSortDir,
                        $term, $year, $courseid, $studentgrade, $examgrade){
        $eModel = new self();

        if (!isset(Zend_Auth::getInstance()->getIdentity()->CurrentContext)){
        	$eModel->_name = 'students';
        	$eModel->_primary = 'cl_GPSN_ID';
        	$select = $eModel->select();

        	$studentCount = Content_Model_Students::getStudentInGradeCount(json_encode(array($studentgrade)));

            if (!Content_Model_Course::isShared($courseid)){
                $select->from($eModel,array(
                    new Zend_Db_Expr('SQL_CALC_FOUND_ROWS cl_GPSN_ID'), 'concat_ws(" ", cl_FirstName,cl_LastName) as fullname',
                	new Zend_Db_Expr("(select cl_examid from exammarks where exammarks.cl_studentid = students.cl_GPSN_ID and exammarks.cl_term = $term and exammarks.cl_year = '$year' and exammarks.cl_courseid = $courseid and exammarks.cl_grade = $examgrade) as cl_examid"),
                	new Zend_Db_Expr("(select cl_comment from exammarks where exammarks.cl_studentid = students.cl_GPSN_ID and exammarks.cl_term = $term and exammarks.cl_year = '$year' and exammarks.cl_courseid = $courseid and exammarks.cl_grade = $examgrade) as cl_comment"),
                	new Zend_Db_Expr("(select cl_mark from exammarks where exammarks.cl_studentid = students.cl_GPSN_ID and exammarks.cl_term = $term and exammarks.cl_year = '$year' and exammarks.cl_courseid = $courseid and exammarks.cl_grade = $examgrade) as exammark"),
                    new Zend_Db_Expr("(select cl_exempt from exammarks where exammarks.cl_studentid = students.cl_GPSN_ID and exammarks.cl_term = $term and exammarks.cl_year = '$year' and exammarks.cl_courseid = $courseid and exammarks.cl_grade = $examgrade) as exempt"),
                    new Zend_Db_Expr("(select markover from examsmarkover where examsmarkover.term = $term and examsmarkover.year = '$year' and examsmarkover.course = $courseid and examsmarkover.grade = $examgrade) as markedover")));
            }
            else{
                $instructor = Zend_Auth::getInstance()->getIdentity()->cl_IID;
                $select->from($eModel,array(
                        new Zend_Db_Expr('SQL_CALC_FOUND_ROWS cl_GPSN_ID'), 'concat_ws(" ", cl_FirstName,cl_LastName) as fullname',
                		new Zend_Db_Expr("(select cl_examid from exammarks where exammarks.cl_studentid = students.cl_GPSN_ID and exammarks.cl_term = $term and exammarks.cl_year = '$year' and exammarks.cl_courseid = $courseid and exammarks.cl_grade = $examgrade and exammarks.cl_instructor = $instructor) as cl_examid"),
                		new Zend_Db_Expr("(select cl_comment from exammarks where exammarks.cl_studentid = students.cl_GPSN_ID and exammarks.cl_term = $term and exammarks.cl_year = '$year' and exammarks.cl_courseid = $courseid and exammarks.cl_grade = $examgrade and exammarks.cl_instructor = $instructor) as cl_comment"),
                		new Zend_Db_Expr("(select cl_mark from exammarks where exammarks.cl_studentid = students.cl_GPSN_ID and exammarks.cl_term = $term and exammarks.cl_year = '$year' and exammarks.cl_courseid = $courseid and exammarks.cl_grade = $examgrade and exammarks.cl_instructor = $instructor) as exammark"),
                        new Zend_Db_Expr("(select cl_exempt from exammarks where exammarks.cl_studentid = students.cl_GPSN_ID and exammarks.cl_term = $term and exammarks.cl_year = '$year' and exammarks.cl_courseid = $courseid and exammarks.cl_grade = $examgrade and exammarks.cl_instructor = $instructor) as exempt"),
                        new Zend_Db_Expr("(select markover from examsmarkover where examsmarkover.term = $term and examsmarkover.year = '$year' and examsmarkover.course = $courseid and examsmarkover.grade = $examgrade and examsmarkover.instructor = $instructor) as markedover")));
            }

            $select->where('cl_GradeLevel =?', $studentgrade)
                   ->where('cl_Active =?',true);
        }

        else{
        	//$eModel->_name = "exammarks";
        	$eModel->_name = "vw_examsarchive";
        	$eModel->_primary = 'cl_studentid';
        	$select = $eModel->select();

        	$studentCount = Content_Model_Students::getPastStudentsinGradeCount(json_encode(array($studentgrade)), $term, $year, $courseid);

        	if (!Content_Model_Course::isShared($courseid)){
        		$select->from($eModel,array(
        				new Zend_Db_Expr('SQL_CALC_FOUND_ROWS cl_studentid as cl_GPSN_ID'),
        				//new Zend_Db_Expr('(select concat_ws(" ", cl_FirstName,cl_LastName) from students where students.cl_GPSN_ID = exammarks.cl_studentid)  AS fullname'),
        				'cl_examid','fullname','cl_mark as exammark', 'cl_exempt as exempt','cl_comment',
        				new Zend_Db_Expr("(select markover from examsmarkover where examsmarkover.term = $term and examsmarkover.year = '$year' and examsmarkover.course = $courseid and examsmarkover.grade = $examgrade) as markedover")));
        	}
        	else{
        		$instructor = Zend_Auth::getInstance()->getIdentity()->cl_IID;
        		$select->from($eModel,array(
       					new Zend_Db_Expr('SQL_CALC_FOUND_ROWS cl_studentid as cl_GPSN_ID'),
        				//new Zend_Db_Expr('(select concat_ws(" ", cl_FirstName,cl_LastName) from students where students.cl_GPSN_ID = exammarks.cl_studentid)  AS fullname'),
        				'cl_examid','fullname','cl_mark as exammark', 'cl_exempt as exempt','cl_comment',
        				new Zend_Db_Expr("(select markover from examsmarkover where examsmarkover.term = $term and examsmarkover.year = '$year' and examsmarkover.course = $courseid and examsmarkover.grade = $examgrade and examsmarkover.instructor = $instructor) as markedover")));
        	}

        	$select->where('cl_grade =?', $studentgrade)
        			->where('cl_term =?',$term)
        			->where('cl_year =?', $year)
        			->where('cl_courseid =?', $courseid);

        	if (Content_Model_Course::isShared($courseid))
        		$select->where('cl_instructor =?', $instructor);

        }
             //paging
            if ($displayLength != -1)
                $select->limit($displayLength, $displayStart);

            //column filtering
            if ($search != "")
                $select->where("cl_LastName like '%$search%' OR cl_FirstName like '%$search%'");

            //order by
            $columns = array('cl_GPSN_ID','fullname','exammark');
            if ($iSortCol != null)
                $select->order("$columns[$iSortCol] $sSortDir");

            //
           // Zend_Debug::dump($select->__toString()); exit;
            $examscores = $eModel->fetchAll($select);

            $db = $eModel->getDefaultAdapter();
            $db->setFetchMode(Zend_Db::FETCH_OBJ);
            $idisplayTotal = $db->fetchAll('select FOUND_ROWS() as ct');

            if($examscores){
                return array('data' => $examscores, 'iTotalRecords' => $studentCount, 'iDisplayRecords' => $idisplayTotal[0]->ct);
            }
            else
                return null;

    }

	public static function getGradeLetter($TM) {
		// TODO Auto-generated Zend_View_Helper_getGradeLetter::getGradeLetter()
		// helper
    	if ($TM >= 90  && $TM <= 100)
    		return "A*";
    	elseif ($TM >= 80 && $TM < 90 )
    	    return "A";
    	elseif ($TM >= 70 && $TM < 80)
    		return "B";
    	elseif ($TM >= 60 && $TM < 70)
    		return "C";
    	elseif ($TM >= 50 && $TM < 60)
    	    return "D";
    	elseif ($TM >= 40 && $TM < 50)
    		return "E";
    	elseif ($TM >= 0 && $TM < 40)
    	    return "F";
	}

	public static function getExamScoreRaw($term, $year, $courseid, $studentgrade, $examgrade){
		$eModel = new self();

		if (!isset(Zend_Auth::getInstance()->getIdentity()->CurrentContext)){
			$eModel->_name = 'students';
			$eModel->_primary = 'cl_GPSN_ID';
			$select = $eModel->select();

			if (!Content_Model_Course::isShared($courseid)){
				$select->from($eModel,array(
						new Zend_Db_Expr('SQL_CALC_FOUND_ROWS cl_GPSN_ID'), 'concat_ws(" ", cl_FirstName,cl_LastName) as fullname',
						new Zend_Db_Expr("(select cl_examid from exammarks where exammarks.cl_studentid = students.cl_GPSN_ID and exammarks.cl_term = $term and exammarks.cl_year = '$year' and exammarks.cl_courseid = $courseid and exammarks.cl_grade = $examgrade) as cl_examid"),
						new Zend_Db_Expr("(select cl_comment from exammarks where exammarks.cl_studentid = students.cl_GPSN_ID and exammarks.cl_term = $term and exammarks.cl_year = '$year' and exammarks.cl_courseid = $courseid and exammarks.cl_grade = $examgrade) as cl_comment"),
						new Zend_Db_Expr("(select cl_mark from exammarks where exammarks.cl_studentid = students.cl_GPSN_ID and exammarks.cl_term = $term and exammarks.cl_year = '$year' and exammarks.cl_courseid = $courseid and exammarks.cl_grade = $examgrade) as exammark"),
						new Zend_Db_Expr("(select cl_exempt from exammarks where exammarks.cl_studentid = students.cl_GPSN_ID and exammarks.cl_term = $term and exammarks.cl_year = '$year' and exammarks.cl_courseid = $courseid and exammarks.cl_grade = $examgrade) as exempt"),
						new Zend_Db_Expr("(select markover from examsmarkover where examsmarkover.term = $term and examsmarkover.year = '$year' and examsmarkover.course = $courseid and examsmarkover.grade = $examgrade) as markedover")));
			}
			else{
				$instructor = Zend_Auth::getInstance()->getIdentity()->cl_IID;
				$select->from($eModel,array(
						new Zend_Db_Expr('SQL_CALC_FOUND_ROWS cl_GPSN_ID'), 'concat_ws(" ", cl_FirstName,cl_LastName) as fullname',
						new Zend_Db_Expr("(select cl_examid from exammarks where exammarks.cl_studentid = students.cl_GPSN_ID and exammarks.cl_term = $term and exammarks.cl_year = '$year' and exammarks.cl_courseid = $courseid and exammarks.cl_grade = $examgrade and exammarks.cl_instructor = $instructor) as cl_examid"),
						new Zend_Db_Expr("(select cl_comment from exammarks where exammarks.cl_studentid = students.cl_GPSN_ID and exammarks.cl_term = $term and exammarks.cl_year = '$year' and exammarks.cl_courseid = $courseid and exammarks.cl_grade = $examgrade and exammarks.cl_instructor = $instructor) as cl_comment"),
						new Zend_Db_Expr("(select cl_mark from exammarks where exammarks.cl_studentid = students.cl_GPSN_ID and exammarks.cl_term = $term and exammarks.cl_year = '$year' and exammarks.cl_courseid = $courseid and exammarks.cl_grade = $examgrade and exammarks.cl_instructor = $instructor) as exammark"),
						new Zend_Db_Expr("(select cl_exempt from exammarks where exammarks.cl_studentid = students.cl_GPSN_ID and exammarks.cl_term = $term and exammarks.cl_year = '$year' and exammarks.cl_courseid = $courseid and exammarks.cl_grade = $examgrade and exammarks.cl_instructor = $instructor) as exempt"),
						new Zend_Db_Expr("(select markover from examsmarkover where examsmarkover.term = $term and examsmarkover.year = '$year' and examsmarkover.course = $courseid and examsmarkover.grade = $examgrade and examsmarkover.instructor = $instructor) as markedover")));
			}

			$select->where('cl_GradeLevel =?', $studentgrade)
			->where('cl_Active =?',true);
		}

		else{
			//$eModel->_name = "exammarks";
			$eModel->_name = "vw_examsarchive";
			$eModel->_primary = 'cl_studentid';
			$select = $eModel->select();

			if (!Content_Model_Course::isShared($courseid)){
				$select->from($eModel,array(
						new Zend_Db_Expr('SQL_CALC_FOUND_ROWS cl_studentid as cl_GPSN_ID'),
						//new Zend_Db_Expr('(select concat_ws(" ", cl_FirstName,cl_LastName) from students where students.cl_GPSN_ID = exammarks.cl_studentid)  AS fullname'),
						'cl_examid','fullname','cl_mark as exammark', 'cl_exempt as exempt','cl_comment',
						new Zend_Db_Expr("(select markover from examsmarkover where examsmarkover.term = $term and examsmarkover.year = '$year' and examsmarkover.course = $courseid and examsmarkover.grade = $examgrade) as markedover")));
			}
			else{
				$instructor = Zend_Auth::getInstance()->getIdentity()->cl_IID;
				$select->from($eModel,array(
						new Zend_Db_Expr('SQL_CALC_FOUND_ROWS cl_studentid as cl_GPSN_ID'),
						//new Zend_Db_Expr('(select concat_ws(" ", cl_FirstName,cl_LastName) from students where students.cl_GPSN_ID = exammarks.cl_studentid)  AS fullname'),
						'cl_examid','fullname','cl_mark as exammark', 'cl_exempt as exempt','cl_comment',
						new Zend_Db_Expr("(select markover from examsmarkover where examsmarkover.term = $term and examsmarkover.year = '$year' and examsmarkover.course = $courseid and examsmarkover.grade = $examgrade and examsmarkover.instructor = $instructor) as markedover")));
			}

			$select->where('cl_grade =?', $studentgrade)
			->where('cl_term =?',$term)
			->where('cl_year =?', $year)
			->where('cl_courseid =?', $courseid);

			if (Content_Model_Course::isShared($courseid))
				$select->where('cl_instructor =?', $instructor);

		}

		$examscores = $eModel->fetchAll($select);


		if($examscores){
			return $examscores;
		}
		else
			return null;

	}
}
