<?php
/**
 * Course
 *
 * @author Edzordzinam
 * @version
 */

require_once 'Zend/Db/Table/Abstract.php';

class Content_Model_Instructors extends Zend_Db_Table_Abstract {
	/**
	 * The default table name
	 */
	protected $_name = 'instructors';


	public static function getInstructorDetail($instructor){
        $instructorModel = new self();
        $instructorModel->_primary = 'cl_IID';
        $result = $instructorModel->find($instructor)->current();
        return $result;
	}

	public function addInstructor($firstname, $lastname, $role, $grades, $courses, $classteacher , $username, $password, $email, $telno){

		//get a new row
		$rowInstructor = $this->createRow();

		//create a row
		if ($rowInstructor){
			$rowInstructor->lastname = $lastname;
			$rowInstructor->firstname = $firstname;
			$rowInstructor->role = $role;
			$rowInstructor->cl_grades = $grades;
			$rowInstructor->cl_courses = $courses;
			$rowInstructor->cl_classteacher = $classteacher;
			$rowInstructor->username = $username;
			$rowInstructor->password = $password;
			$rowInstructor->email = $email;
			$rowInstructor->telno = $telno;
			$rowInstructor->save();

			return $rowInstructor;
		}
		else
		{
			throw new Zend_Exception("Could not add instructor, contact administrator");
		}

	}

	public function getInstructors(){
		$instructorModel = new self();
		$select = $instructorModel->select();
		$select->order(array('lastname'))->where('not role=500');
		return $instructorModel->fetchAll($select);
	}

	public static function findClassTeacher($gradeid){
		$instructorModel = new self();
			$select = $instructorModel->select();
			$select->where('cl_classteacher =?', $gradeid)
					->order(array('lastname'));

		$result = $instructorModel->fetchRow($select);

		if ($result){
			return  "$result->firstname $result->lastname";
		}
		else
			return null;
	}

	public function addCourse($courseid, $grades, $instructor){

		$this->_name = 'courseinstructors';

		//add a new row if courseid is not found

		$this->_primary = array('cl_course', 'cl_instructor');
        $courseRow = $this->find($courseid, $instructor)->current();

		if ($courseRow){  //if row was found

		    $Oldgrades = Zend_Json::decode($courseRow->cl_grades);

		    $Oldgrades = array_merge($Oldgrades,$grades);

		    $courseRow->cl_course = $courseid;
			$courseRow->cl_instructor = $instructor;
			$courseRow->cl_grades = Zend_Json::encode($Oldgrades);
			$courseRow->cl_dateassigned = date('Y-m-d');
			$courseRow->save();
		}
		else{
		    $courseRow = $this->createRow();//creating a new row when subject has not already been assigned

		    $courseRow->cl_course = $courseid;
		    $courseRow->cl_instructor = $instructor;
		    $courseRow->cl_grades = Zend_Json::encode($grades);
		    $courseRow->cl_dateassigned = date('Y-m-d');

		    $courseRow->save();
		}
	}

	public function deactivate($instructor){
		//fetch the user's row
		$rowInstructor = $this->find($instructor)->current();

	    $data = array('active' => 0);

		if ($rowInstructor) {
			//update the row value
			$rowInstructor->setFromArray($data);
			$rowInstructor->save();
			//return the updated user
		}
		else{
			throw new Zend_Exception("Error deactivating instructor");
		}
	}

	public function activate($instructor){
		//fetch the user's row
		$rowInstructor = $this->find($instructor)->current();

	    $data = array('active' => 1);

		if ($rowInstructor) {
			//update the row value
			$rowInstructor->setFromArray($data);
			$rowInstructor->save();
			//return the updated user
		}
		else{
			throw new Zend_Exception("Error activating instructor");
		}
	}

	public function deleteCourse($courseid, $instructor){
		$this->_name = 'courseinstructors';

		//add a new row if courseid is not found

		$select = $this->select();
		$select->where('cl_course =?', $courseid)
				->where('cl_instructor =?', $instructor);

		$where = array();

		$where[] = $this->getAdapter()->quoteInto('cl_course = ?', $courseid);
		$where[] = $this->getAdapter()->quoteInto('cl_instructor = ?', $instructor);

		$row = $this->delete($where);

	}

	public function updatePassword($id, $username, $password, $oldpassword){
		//fetch the user's row
		$rowUser = $this->find($id)->current();

		if ($rowUser->password == md5($oldpassword)){
			if ($rowUser){
				//update the password
				$rowUser->username = trim($username);
				$rowUser->password = md5($password);
				$rowUser->save();

				return true;
			}
			else{
				throw new Zend_Exception("Password update failed. User not found!");
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	public function checkPassword($id, $username, $password, $oldpassword){
		//fetch the user's row
		$rowUser = $this->find($id)->current();

		if ($rowUser->password == md5($oldpassword)){
			return 0;
		}
		else
		{
			return 1;
		}
	}

	public static function coursegrades($course, $instructor){

	   try {
	       $instructorModel = new self();
	       $instructorModel->_name = 'courseinstructors';

	       $select = $instructorModel->select();
	       $select->from('courseinstructors', 'cl_grades')
	       ->where('cl_instructor =?', $instructor)
	       ->where('cl_course =?', $course);
	       $result = $instructorModel->fetchAll($select)->toArray();

	       if (count($result) > 0)
	           return $result[0];
	       else
	           return null;
	   } catch (Exception $e) {
	       throw new Zend_Exception($e->getMessage());
	   }
	}

	public static function isCourseGradeAllowed($course, $grade, $instructor){

	    try {
	        $instructorModel = new self();
	        $instructorModel->_name = 'courseinstructors';

	        $select = $instructorModel->select();
	        $select->from('courseinstructors', 'cl_grades')
	                    ->where('cl_instructor =?', $instructor)
	                    ->where('cl_course =?', $course)
	                    ->where('cl_grades like ?', '%"'.$grade.'"%');

	        $result = $instructorModel->fetchRow($select);

	        if ($result)
	            return true;
	        else
	            return false;
	    } catch (Exception $e) {
	        throw new Zend_Exception($e->getMessage());
	    }
	}

	public static function taughtGrades($instructor, $json = false){
		$instructorModel = new self();
		$instructorModel->_name = 'courseinstructors';

		$select = $instructorModel->select();
		$select->from('courseinstructors', 'cl_grades')
				->where('cl_instructor =?', $instructor);

		$result = $instructorModel->fetchAll($select);

		$grades = array();

		foreach ($result as $cGrade) {
			$cgrades = Zend_Json::decode($cGrade->cl_grades);
			$grades = array_merge($grades, $cgrades);
		}

		$grades = array_unique($grades);

		if (count($grades) > 0){
		  if (!$json)
			return $grades;
		  else{
		    $grades = array_values($grades);
		    return json_encode($grades);
		  }
		}
		else
			return null;
	}

	public static function taughtPastGradesClasses($instructor, $term, $year, $json = false){
		$instructorModel = new self();
		$instructorModel->_name = 'assignments';

		$select = $instructorModel->select();
		$select	->from($instructorModel,array('cl_grade', 'cl_course', 'cl_course as cl_courseid',
					new Zend_Db_Expr('(select cl_coursename from courses where courses.cl_courseid = cl_course) as cl_coursename')))
				->distinct(true)
				->where('cl_term =?', $term)
				->where('cl_year =?', $year)
				->where('cl_instructor =?',$instructor);

		$results = $instructorModel->fetchAll($select);
		$gradesNclasses = array();

		if ($results->count() > 0){
			foreach ($results as $result) {
				$gradesNclasses['grades'][] = $result->cl_grade;
			}

			$r = array();
			if (isset($gradesNclasses['grades']))
				$r['grades'] = array_unique($gradesNclasses['grades']);
			else
				$r['grades'] = array();

				$r['classes'] = $results;

			if (!$json){
				return $r;
			}
			else{
				$gradesNclasses['grades'] = array_unique(array_values($gradesNclasses['grades']));
				$r['grades'] = json_encode((array_values($gradesNclasses['grades'])));
				return $r;
			}
		}
		else
			return null;
	}

	public static function deleteClassSubject($courseid, $grade, $instructor){
 	    $instructorModel = new self();
	    $instructorModel->_name = 'courseinstructors';

	    $instructorModel->_primary = array('cl_course', 'cl_instructor');

	    $result = $instructorModel->find($courseid, $instructor)->current();

	    if ($result){
  	        $grades = json_decode($result->cl_grades);

  	        if(($key = array_search($grade, $grades)) !== false) {
  	            unset($grades[$key]);
  	        }

  	        $grades = array_values($grades);

  	        if(count($grades) == 0){
  	            //delete the entire row
  	            $result->delete();
  	        }else{
    	        $result->cl_grades = json_encode($grades);
    	        $result->save();
    	        return true;
  	        }
	    }
	    else
	        return false;

	}

	public static function resetPassword($instructor){
        $instructorModel = new self();
        $instructorModel->_primary = 'cl_IID';

        $result = $instructorModel->find($instructor)->current();

        if ($result){
            $result->password = md5('greport');
            $result->save();
        }
	}

    public static function updateDetails($instructor, $column, $value){
        $instructorModel = new self();
        $instructorModel->_primary = 'cl_IID';

        $instructorRow = $instructorModel->find($instructor)->current();

        if ($instructorRow){//record found
            switch ($column) {
                case 'ct':
                    //classteacher
                    $validator = new Zend_Validate_Db_NoRecordExists(array(
                                'table' => 'instructors',
                                'field' => 'cl_classteacher'
                            ));

                    if ($validator->isValid($value) || $value == -1 || $value == -2){
                        $instructorRow->cl_classteacher = $value;
                        $instructorRow->save();

                        return Custom_Grades::getGradeName($value);
                    }
                    else
                        return "Already Assigned";
                break;

                case 'em':
                    //email
                        $instructorRow->email = trim($value);
                        $instructorRow->save();

                        return trim($value);
                break;

                case 'tl':
                    //telno
                    $instructorRow->telno = trim($value);
                    $instructorRow->save();

                    return trim($value);
                break;

                case 'ec':
                    //ecode

                    $validator = new Zend_Validate_Db_NoRecordExists(array(
                            'table' => 'instructors',
                            'field' => 'ecode'
                    ));

                    if ($validator->isValid($value)){
                        $instructorRow->ecode = trim($value);
                        $instructorRow->save();
                        return trim($value);
                    }
                    else
                        return "Already Assigned";
                break;

                case 'gn':
                    //ecode
                    $instructorRow->firstname = trim($value);
                    $instructorRow->save();

                    return trim($value);
                break;


                case 'sn':
                    //ecode
                    $instructorRow->lastname = trim($value);
                    $instructorRow->save();

                    return trim($value);
                break;

                default:
                    ;
                break;
            }
        }
    }

    public static function allSubjectsAssigned($displayStart, $displayLength, $search, $iSortCol, $sSortDir){
            $instructorModel = new self();
            $instructorModel->_name = 'vw_instructorcourses';
            $instructorModel->_primary = 'cl_id';

            //retrieving the total number of rows in the courts table
            $assignTotal = $instructorModel->getAdapter()->fetchOne('SELECT COUNT(cl_instructor) AS count FROM vw_instructorcourses');

            $select = $instructorModel->select();

            //paging
            if ($displayLength != -1)
                $select->limit($displayLength, $displayStart);

            //column filtering
            if ($search != "")
                $select->where("cl_coursename like '%$search%'")
                ->orWhere("fullname like '%$search%'");

            //order by
            $columns = $instructorModel->info(Zend_Db_Table_Abstract::COLS);
            $select->order("$columns[$iSortCol] $sSortDir");

            $subjectAssigns = $instructorModel->fetchAll($select);

            return array('sa' => $subjectAssigns, 'iTotalRecords' => $assignTotal, 'iDisplayRecords' => $subjectAssigns->count());
    }

	public static function getPastCourseInstructor($course, $grade, $term, $year){
		$iModel = new self();
		$iModel->_name = 'assignments';

		$select = $iModel->select();
		$select->from($iModel, array('cl_instructor',
							new Zend_Db_Expr('(select CONCAT_WS(" ",firstname,lastname) from instructors where instructors.cl_IID = assignments.cl_instructor) as fullname')))
				->distinct(true)
				->where('cl_term =?', $term)
				->where('cl_year =?', $year)
				->where('cl_grade =?', $grade)
				->where('cl_course =?', $course);

		//Zend_Debug::dump($select->__toString());exit;

		$result = $iModel->fetchRow($select);

		return $result;

	}

	public static function listInstructors($role){
		$iModel = new self();

		$select = $iModel->select();
		$select->from($iModel, array('cl_IID','concat_ws(" ",firstname,lastname) as fullname'))
			   ->where('role =?',$role)->where('active = 1')->order('firstname');

		$result = $iModel->fetchAll($select);

		if ($result)
			return $result;
		else
			return null;
	}

	public static function generatePassword($length) {
		$lowercase = "qwertyuiopasdfghjklzxcvbnm";
		$uppercase = "ASDFGHJKLZXCVBNMQWERTYUIOP";
		$numbers = "1234567890";
		//$specialcharacters = ";:/<>?+!@#";
		$specialcharacters ='';
		$randomCode = "";
		mt_srand(crc32(microtime()));
		$max = strlen($lowercase) - 1;
		for ($x = 0; $x < abs($length/3); $x++) {
			$randomCode .= $lowercase{mt_rand(0, $max)};
		}
		$max = strlen($uppercase) - 1;
		for ($x = 0; $x < abs($length/3); $x++) {
			$randomCode .= $uppercase{mt_rand(0, $max)};
		}
		/* $max = strlen($specialcharacters) - 1;
		for ($x = 0; $x < abs($length/3); $x++) {
			$randomCode .= $specialcharacters{mt_rand(0, $max)};
		} */
		$max = strlen($numbers) - 1;
		for ($x = 0; $x < abs($length/3); $x++) {
			$randomCode .= $numbers{mt_rand(0, $max)};
		}

		if (strlen(str_shuffle($randomCode)) < 7)
			self::generatePassword(8);

		return str_shuffle($randomCode);
	}

	public static function isClassTeacherForGrade($grade){
		//being a class teacher can be retrieved from the Zend_Auth Instance
		$identity = Zend_Auth::getInstance()->getIdentity();
		$classteacher = $identity->cl_classteacher;

		if ($classteacher != -1){
			if ($grade == $classteacher)
				return true;
			else
				return false;
		}
		else
			return false;
	}

	public static function instructorCount(){
		$instructor = new self();
		$db = $instructor->getDefaultAdapter();

		try {
			$stmt = $db->query('select fn_instructorcount() as icount;',array());
			$result = $stmt->fetch(Zend_Db::FETCH_OBJ);
			return $result->icount;

		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
}