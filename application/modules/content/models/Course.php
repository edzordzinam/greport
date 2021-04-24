<?php

/**
 * Course
 *
 * @author Edzordzinam
 * @version
 */

require_once 'Zend/Db/Table/Abstract.php';

class Content_Model_Course extends Zend_Db_Table_Abstract {
	/**
	 * The default table name
	 */
	protected $_name = 'courses';


	/**
	 * Function for the addition of a new course into the system
	 * @category Default Module
	 * @param String $coursename
	 * @param String $gradelevels
	 * @throws Zend_Exception
	 * @version 1.0.0
	 */
	public function createCourse($coursename, $gradelevels, $gradeexempt, $examinable, $shared, $order){
		$rowCourse= $this->createRow();

		if ($rowCourse){
			//update the row values
			$rowCourse->cl_coursename = $coursename;
			$rowCourse->cl_gradelevels = $gradelevels;
			$rowCourse->cl_gradeexempt = $gradeexempt;
			$rowCourse->cl_examinable = $examinable;
			$rowCourse->cl_shared = $shared;
			$rowCourse->cl_courseorder = $order;
			$rowCourse->save();

			return $rowCourse;
		}
		else{
			throw  new Zend_Exception('Could not create user! - contact Administrator');
		}
	}

	public static function getCourses(){
		$courseModel = new self();
		$select = $courseModel->select();
		$select->order(array('cl_courseorder DESC', 'cl_coursename ASC'));

		return $courseModel->fetchAll($select);
	}

	public static function getInstructorCourses($instructor){

		$courseModel = new self();

		$courseModel->_name = 'vw_instructorcourses';
		$courseModel->_primary = array("cl_id");

		$select = $courseModel->select();
		$select->order(array('cl_coursename', 'cl_gradelevels'))
		       ->where('cl_instructor =?', $instructor);

		$result = $courseModel->fetchAll($select);

		if (($instructor != NULL) && ($result->count() > 0))
			return $result;
		else
			return null;
	}

	public static function getInstructorGradeCourses($instructor, $grade, $forselect = false){
		$courseModel = new self();

		$courseModel->_name = 'vw_instructorcourses';
		$courseModel->_primary = array("cl_id");

		$select = $courseModel->select();
		$select->order(array('cl_coursename', 'cl_gradelevels'))
				->where('cl_instructor =?', $instructor)
		 		->where('cl_gradelevels like ?', '%"'.$grade.'"%');

		if ($instructor != NULL){

			$result = $courseModel->fetchAll($select);

			$i = 0;

			if ($result->count() > 0){
			  if (!$forselect){
				foreach ($result as $row) {
					//var_dump($row);
					$rows[$i]['cid'] = $row->cl_courseid;
					$rows[$i]['cname'] = $row->cl_coursename;
					$i += 1;
				}
			  }
			  else{
			      foreach ($result as $row) {
			          $rows[$row->cl_courseid] = $row->cl_coursename;
			      }
			  }
			    return $rows;
			}
			else
				return -1;
		}
		else
			return -1;
	}

	public static function getAvailableClasses($courseid){

		$courseModel = new self();

		$courseModel->_name = 'vw_instructorcourses';
		$courseModel->_primary = array("cl_id");

		$select = $courseModel->select();
		$select->order(array('cl_coursename', 'cl_gradelevels'))
		->where('cl_courseid =?', $courseid)
		->where('cl_shared =?', 0); //where zero means it is not a shared course....

		if ($courseid != NULL){

			$result = $courseModel->fetchAll($select);

			$i = 0;

			$data = array();

			if ($result->count() > 0){
				foreach ($result as $row) {
					//var_dump($row);
					$classes = Zend_Json::decode($row->cl_gradelevels);
					//var_dump($classes);

				    foreach ($classes as $value) {
				    	 if (!in_array($value, $data)){
				    			 	$data[] = $value;
				    			 	//var_dump($data);
				    	}
				    }
				}

				return $data;
			}
			else
				return null;
		}
		else
			return null;
	}

	public static function getCoursesArray(){
		$courseModel = new self();
		$select = $courseModel->select();
		$select->order(array('cl_coursename', 'cl_gradelevels'));
		return $courseModel->fetchAll($select)->toArray();
	}

	public static function getCourseNames($view = false){
		$courseModel = new self();
		$select = $courseModel->select() ;
		$select->order(array('cl_coursename'));

		$rowSet = $courseModel->fetchAll($select);

		if ($rowSet->count() > 0){
			if (!$view){
				$cNames = array();
				$cCodes = array();
			}
			else
			{
				$cNames = array('');
				$cCodes = array(-1);
			}
	
			foreach ($rowSet as $row) {
				$cNames[] = $row->cl_coursename;
				$cCodes[] = $row->cl_courseid;
			}
	
			return array_combine($cCodes, $cNames);
		}
		else
			return array();
	}

	public static function getCourseName($courseCode){
		$courseModel = new self();
		$select = $courseModel->select()->where('cl_courseid = ?', $courseCode)->limit(1) ;
		$select->order(array('cl_coursename'));

		$rowSet = $courseModel->fetchRow($select);

		return  $rowSet->cl_coursename;
	}

	public function updateCourse($id, $coursename, $gradelevels, $gradeexempt, $examinable, $shared, $order){
		//fetch the user's row
		$rowCourse = $this->find($id)->current();

		if ($rowCourse) {
			//update the row value
			$rowCourse->cl_coursename = $coursename;
			$rowCourse->cl_gradelevels = $gradelevels;
			$rowCourse->cl_gradeexempt = $gradeexempt;
			$rowCourse->cl_examinable = $examinable;
			$rowCourse->cl_shared = $shared;
			$rowCourse->cl_courseorder = $order;
			$rowCourse->save();

			//return the updated user
			return $rowCourse;
		}
		else{
			throw new Zend_Exception("Course update failed. course not found!");
		}
	}

	public function deleteCourse($id){
		//fetch the user's row
		$rowCourse = $this->find($id)->current();

		if ($rowCourse){
			$rowCourse->delete();
		}
		else
		{
			throw new Zend_Exception("Could no delete course. Course not found!");
		}
	}

	public static function getGradeCourses($gradeid){
	   $courseModel = new self();

	   $select = $courseModel->select();
	   $select->where('cl_gradelevels like ?', '%"'.$gradeid.'"%')
	   			->order('cl_courseorder DESC')->order('cl_examinable DESC')->order('cl_coursename ASC');

	   return $courseModel->fetchAll($select);

	}

	//this function is responsible for the loading of the course content for a
	//particular term, academic year and grade in which the subject is taught....
	public static function getCourseContent($courseid, $gradeid, $term, $year){

		$courseModel = new self();
		$courseModel->_name = "tb_coursecontent";

		//loading the course content
		$select = $courseModel->select();
		$select->where('cl_courseid =?', $courseid)
				->where('cl_term =?', $term)
				->where('cl_year =?', $year)
				->where('cl_grade =?', $gradeid);


		$result = $courseModel->fetchRow($select);

		if ($result){
			return $result;
		}else{
			return null;
		}

	}

	public static function postCourseContent($courseid, $term, $gradeid, $year, $content){
		//first search for the record to see if it exits...
		$courseModel = new self();
		$courseModel->_name = "coursecontent";

		//loading the course content
		$select = $courseModel->select();
		$select->where('cl_courseid =?', $courseid)
		->where('cl_term =?', $term)
		->where('cl_year =?', $year)
		->where('cl_grade =?', $gradeid);


		$contentRow = $courseModel->fetchRow($select);

		if ($contentRow){
			//it means a record has been located... then it must be updated with the new content
			$contentRow->cl_courseid = $courseid;
			$contentRow->cl_term = $term;
			$contentRow->cl_grade = $gradeid;
			$contentRow->cl_year = $year;
			$contentRow->cl_content = $content;
			$contentRow->save();
			return $contentRow;
		}
		else{
			//no record was found and as such an entry must be made into the database for the selected period
			$newContentRow = $courseModel->createRow();
			$newContentRow->cl_courseid = $courseid;
			$newContentRow->cl_term = $term;
			$newContentRow->cl_year = $year;
			$newContentRow->cl_grade = $gradeid;
			$newContentRow->cl_content = $content;
			$newContentRow->save();
			return $newContentRow;
		}

	}

	public static function isExaminable($courseid, $grade){
		$courseModel = new self();

		$resultRow = $courseModel->find($courseid)->current();
		
		if ($resultRow->cl_gradeexempt != 'null')
			$isGradeExempt = in_array($grade, Zend_Json::decode($resultRow->cl_gradeexempt));
		else
			$isGradeExempt = false;

		return ($resultRow->cl_examinable && !$isGradeExempt) ;
	}

	public static function isPastExaminable($term, $year, $course, $grade){
		$aModel = new self();
		$aModel->_name = 'exammarks';
		
		$select = $aModel->select();
		$select->from($aModel,array('(AVG(cl_mark) > 10 AND NOT ISNULL(AVG(cl_mark))) as cl_examinable'))
				->where('cl_term =?', $term)
				->where('cl_year =?', $year)
				->where('cl_grade =?', $grade)
				->where('cl_courseid =?', $course);
		
		$result = $aModel->fetchRow($select);
		
		return $result->cl_examinable;
	}
	
	
	public static function isShared($courseid){
	    $courseModel = new self();
	    $resultRow = $courseModel->find($courseid)->current();
	    return (bool)($resultRow->cl_shared);
	}
	
	public static function sharedInstructors($course, $grade, $instructor){
		$courseModel = new self();
		$courseModel->_name = 'courseinstructors';
		
		$select = $courseModel->select();
		/*select cl_instructor, 
			(select concat_ws(' ',firstname,lastname) from instructors where instructors.cl_IID = courseinstructors.cl_instructor) as fullname
			from courseinstructors where cl_course = 1 and cl_grades like "%9%" and NOT cl_instructor = 36
		 * */
		$select->from($courseModel, array('cl_instructor', 
						new Zend_Db_Expr('select concat_ws(" ",firstname,lastname) where instructors.cl_IID = courseinstructors.cl_instructor) as fullname')))
				->where("cl_course = $course and cl_grades like '%$grade%' and NOT cl_instructor = $instructor");
		
		$result = $courseModel->fetchAll($select);
		
		if ($result){
			$resultStr = '';
			foreach ($result as $r) {
				$resultStr = $r->fullname."; ";
			}
			return $resultStr;
		}
		else
			return '';
		
	}


	public static function courseInstructor($course, $grade){
		$courseModel = new self();
		$courseModel->_name = 'vw_getcourseinstructor';

		$courseModel->_primary = 'cl_id';

		$select = $courseModel->select(array('fullname'));
		$select	->where('cl_grades like ?', '%"'.$grade.'"%')
				->where('cl_course =?', $course);

		$result = $courseModel->fetchRow($select);

		return $result;
	}

	public static function deleteClass($subject, $grade){
	    $courseModel = new self();
	    $courseModel->_name = 'courses';

	    $courseModel->_primary = 'cl_courseid';

	    $result = $courseModel->find($subject)->current();

	    if ($result){
	        $grades = json_decode($result->cl_gradelevels);

	        if(($key = array_search($grade, $grades)) !== false) {
	            unset($grades[$key]);
	        }

	        $grades = array_values($grades);

	         $result->cl_gradelevels = json_encode($grades);
	         $result->save();
	         return true;
	    }
	    else
	        return false;

	}
	
	public static function isSubjectTaughtInClass($courseid, $grade){
		$cModel = new self();
		
		$select = $cModel->select();
		$select->where('cl_courseid =?', $courseid);
		
		$result = $cModel->fetchRow($select);
		
		if ($result){
			$r = json_decode($result->cl_gradelevels);
			if (in_array($grade, $r))
				return true;
			else 
				return false;
		}
		return false;
	}

	public static function addSyllabus($courseid, $grade, $term, $year, $section, $status){
		$allowed = Content_Model_Instructors::isCourseGradeAllowed($courseid, $grade, Zend_Auth::getInstance()->getIdentity()->cl_IID);
		
		if ($allowed){
			$sModel = new self();
			$sModel->_name = 'syllabus';
			
			$syllabus = $sModel->createRow();
	
			if ($syllabus){
				$syllabus->section = $section;
				$syllabus->status = $status;
				$syllabus->courseid = $courseid;
				$syllabus->gradeid = $grade;
				$syllabus->term = $term;
				$syllabus->year = $year;
				$syllabus->save();
				
				
				//update course content
				//select all and save
				$select = $sModel->select();
				$select->where('term =?', $term)
						->where('year =?', $year)
						->where('gradeid =?', $grade)
						->where('courseid =?', $courseid)
						->where('status =?', 1);

				$results = $sModel->fetchAll($select);
				$content = '';
				
				if ($results){
					foreach ($results as $syllabi) {
						$content .= "$syllabi->section ;";
					}
					self::postCourseContent($courseid, $term, $grade, $year, $content);
				}			
			}
			
			return true;
		}
		else
			return false;
		
	}
	
	public static function updateSyllabus($cl_id, $section, $status){
		$sModel = new self();
		$sModel->_name = 'syllabus';
			
		$syllabi = $sModel->find($cl_id)->current();
		
		if ($syllabi){
			$syllabi->section = $section;
			$syllabi->status = $status;
			$syllabi->save();
			
			//select all and save
			$select = $sModel->select();
			$select->where('term =?', $syllabi->term)
					->where('year =?', $syllabi->year)
					->where('gradeid =?', $syllabi->gradeid)
					->where('courseid =?', $syllabi->courseid)
					->where('status =?', 1);
					
			$results = $sModel->fetchAll($select);
			$content = '';
			
			if ($results){
				foreach ($results as $syllabi) {
					$content .= $syllabi->section."; ";
				}
				self::postCourseContent($syllabi->courseid, $syllabi->term, $syllabi->gradeid, $syllabi->year, $content);
			}
			
			return true;
		}
		return false;
	}
	
	public static function deleteSyllabi($cl_id){
		$sModel = new self();
		$sModel->_name = 'syllabus';
		
		$syllabi = $sModel->find($cl_id)->current();
		
		if ($syllabi){
			$syllabi->delete();
			
			$select = $sModel->select();
			$select->where('term =?', $syllabi->term)
				->where('year =?', $syllabi->year)
				->where('gradeid =?', $syllabi->gradeid)
				->where('courseid =?', $syllabi->courseid)
				->where('status =?', 1);
				
			$results = $sModel->fetchAll($select);
			$content = '';
				
			if ($results){
				foreach ($results as $syllabi) {
					$content .= "$syllabi->section ;";
				}
				self::postCourseContent($syllabi->courseid, $syllabi->term, $syllabi->gradeid, $syllabi->year, $content);
			}
			
			return true;
		}
		else 
			return false;
	}
	
	public static function getSyllabusList($displayStart, $displayLength, $search, $iSortCol, $sSortDir,
                        $term, $year, $courseid, $grade){
		$aModel = new self();
		$aModel->_name = 'syllabus';
		
		$select = $aModel->select();
		
		$syllabusTotal = $aModel->getAdapter()->fetchOne("SELECT COUNT(cl_id) AS count FROM syllabus WHERE term = $term and year ='$year'");
		
		try {
			$select->from($aModel, array( new Zend_Db_Expr('SQL_CALC_FOUND_ROWS cl_id'), '*'));
		
			$select->where('courseid =?', $courseid)
					->where('term =?', $term)
					->where('year =?', $year)
					->where('gradeid =?',$grade);
		
			//paging
			if ($displayLength != -1)
				$select->limit($displayLength, $displayStart);
		
			//column filtering
			if ($search != "")
				$select->where("section like '%$search%'");
			
			//order by
			$columns = array('section','status');
			if ($iSortCol != null)
				$select->order("$columns[$iSortCol] $sSortDir");
				
			$syllabus = $aModel->fetchAll($select);
		
			$db = $aModel->getDefaultAdapter();
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$idisplayTotal = $db->fetchAll('select FOUND_ROWS() as ct');
		
			if($syllabus){
				return array('data' => $syllabus, 'iTotalRecords' => $syllabusTotal, 'iDisplayRecords' => $idisplayTotal[0]->ct);
			}
			else
				return null;
		
		} catch (Exception $e) {
			Zend_Debug::dump($e->getMessage());
		}
	}

	public static function loadSyllabus($cl_id){
			$sModel = new self();
			$sModel->_name = 'syllabus';
			$result = $sModel->find($cl_id)->current();
			if ($result)
				return $result;
			else 
				return null;
	}
	
	public static function loadAllSyllabus($courseid, $gradeid, $term,  $year){
		$sModel = new self();
		$sModel->_name = 'syllabus';
		$select = $sModel->select();
		$select->where('term =?', $term)
				->where('year =?', $year)
				->where('gradeid =?', $gradeid)
				->where('courseid =?', $courseid);
		$result = $sModel->fetchAll($select);
		if ($result)
			return $result;
		else
			return null;
	}
}
