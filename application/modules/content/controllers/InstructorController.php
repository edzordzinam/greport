<?php

class Content_InstructorController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    	$instructorform = new Content_Form_NewInstructor();
    	$instructorform->setAction('/instructor/index');
    	$this->view->form = $instructorform;
    }

    public function createAction()
    {
        // action body

        $this->getHelper('layout')->disableLayout();

    	$instructorform = new Content_Form_NewInstructor();

    	//check to if this is a post
    	if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('send') == 'true'){

    		//check the validity of the form
    		if ($instructorform->isValid($_POST)){
    			//create a new instructor model
    			$instructorModel = new Content_Model_Instructors();
    			$instructorModel->addInstructor(
    					  $_POST['firstname'],
    					  $_POST['lastname'],
    					  $_POST['role'],
    					  null, //json_encode($_POST['cl_grades']),
    					  null, //json_encode($_POST['cl_courses']),
    					  $_POST['cl_classteacher'],
    					  $_POST['username'],
    					  md5($_POST['password']),
    					  $_POST['email'],
    					  $_POST['telno']
    					);

    			$instructorform = new Content_Form_NewInstructor();
    			$this->view->form = $instructorform;
    			die(json_encode(array('status'=>1)));
    		}
    	}

    	$this->view->form = $instructorform;

    }

    public function listAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();

    	$currentInstructors = Content_Model_Instructors::getInstructors();
    	if ($currentInstructors->count() > 0){
    		$this->view->instructors = $currentInstructors;
    	}
    	else
    		$this->view->instructors = null;
    }

    public function editAction()
    {
        $this->getHelper('layout')->disableLayout();

        try {
            $instructorCourses = Content_Model_Course::getInstructorCourses($this->_request->getParam('instructor'));
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        $instructor = Content_Model_Instructors::getInstructorDetail($this->_request->getParam('instructor'));

        $this->view->courses = $instructorCourses;
        $this->view->i_name = $this->_request->getParam('i_name');
        $this->view->instructor = $this->_request->getParam('instructor');
        $this->view->lastname = $instructor->lastname;
        $this->view->firstname = $instructor->firstname;
    }

    public function assignAddAction()
    {
 		//posted new class assignment
 		if ($this->_request->isPost()){
 		   //add class to the instructors
 		   //instructors id /
 		   $instructor = $this->_request->getParam('cl_inst_id');

 		   $instructorModel = new Content_Model_Instructors();
 		   $instructorModel->addCourse(
 		   							$this->_request->getParam('cl_courses'),
 		   							$this->_request->getParam('cl_grades'),
 		   							$instructor);

 		  // $this->_redirect('/instructor/index');
 		   //$this->_redirect('/instructor/edit/instructor/'.$instructor);
 		}
    }

    public function assignFormAction()
    {
    	$assignAddForm = new Content_Form_ClassSubjectAssign();
    	$this->view->form = $assignAddForm;
    }

    public function deleteSubjectAction()
    {

    	$instructorModel = new Content_Model_Instructors();
    	$instructorModel->deleteCourse(
    			$this->_request->getParam('courseid'),
    			$this->_request->getParam('instructorid'));
    }

    public function deactivateAction()
    {
    	$instructorModel = new Content_Model_Instructors();
    	$instructorModel->deactivate($this->_request->getParam('instructor'));
    	$this->_redirect('/instructor/index');
    }

    public function activateAction()
    {
    	$instructorModel = new Content_Model_Instructors();
    	$instructorModel->activate($this->_request->getParam('instructor'));
    	$this->_redirect('/instructor/index');
    }

    public function updatePassAction()
    {
		// action body
		$this->getHelper('layout')->disableLayout();

		//get identity of the user
		$instructor =  Zend_Auth::getInstance()->getIdentity()->cl_IID;
		$change = $this->_request->getParam('change');
		if (isset($change) && $change == 1){
			$this->getHelper('viewRenderer')->setNoRender(true);
			$iModel = new Content_Model_Instructors();
			$result = $iModel->find($instructor)->current();
			if ($result){
				if ($result->password == $this->_request->getParam('oldpwd') &&
						$this->_request->getParam('newpwd') == $this->_request->getParam('matchpwd')){
					$result->password = $this->_request->getParam('newpwd');
					$result->save();

					echo 'Password successfully updated';
				}
				else
					echo 'Update failed : password mismatch';
			}
		}

		$passwordForm = new Content_Form_ChangePassword();

		$this->view->form = $passwordForm;
    }

    public function checkPassAction()
    {

		$auth = Zend_Auth::getInstance();
		$instructor = $auth->getIdentity()->cl_IID;

		$instructorModel = new Content_Model_Instructors();
		echo $instructorModel->checkPassword(
				$instructor,
						$this->_request->getParam('username'),
						$this->_request->getParam('password'),
						$this->_request->getParam('oldpassword'),
						true);

		$this->getHelper('layout')->disableLayout();
		//$this->getHelper('viewRenderer')->setNoRender();

    }

    public function instSubjectsAction()
    {
		//$this->getHelper('layout')->disableLayout();

		$auth = Zend_Auth::getInstance();
		$instructor = $auth->getIdentity()->cl_IID;

		$CourseModel = new Content_Model_Course();
		$subjects = $CourseModel->getInstructorCourses($instructor);

		$this->view->instSubjects = $subjects;
    }

    public function instSubjects2Action()
    {
		//$this->getHelper('layout')->disableLayout();

		$auth = Zend_Auth::getInstance();
		$instructor = $auth->getIdentity()->cl_IID;

		$CourseModel = new Content_Model_Course();
		$subjects = $CourseModel->getInstructorCourses($instructor);

		$sclasses = array();
		//$classes[] = $ini;

		foreach ($subjects as $subject) {
			$classes = array();
			$ins = new Content_Model_Instructors();
			$gradelevels = $ins->coursegrades($subject->cl_courseid, $instructor);

			if (count($gradelevels['cl_grades']) > 0){
				foreach (Zend_Json::decode($gradelevels["cl_grades"]) as $key => $value){
					$marksAllowed[] = true;
					$class = new stdClass();
					$class->gradeid = $value;
					$class->gradename = Custom_Grades::getGradeName($value);
					$classes[] = $class;
				}
			}

			$sclasses[$subject->cl_courseid] = $classes;
		}


		$this->view->classes = $sclasses;

		$this->view->instSubjects = $subjects;




    }

    public function instSubjectsClassesAction()
    {
		$auth = Zend_Auth::getInstance();
		$instructor = $auth->getIdentity()->cl_IID;

		$ins = new Content_Model_Instructors();
		$gradelevels = $ins->coursegrades($this->_request->getParam('courseid'), $instructor);

		$ini = new stdClass();
		$ini->gradeid = '-1';
		$ini->gradename = '';

		$classes = array();
		$classes[] = $ini;

		if (count($gradelevels['cl_grades']) > 0){
			foreach (Zend_Json::decode($gradelevels["cl_grades"]) as $key => $value){
				//$marksAllowed[] = Model_Instructors::allowedAccess($this->_request->getParam('cl_id'), $value);
				$marksAllowed[] = true;
				$class = new stdClass();
				$class->gradeid = $value;
				$class->gradename = Custom_Grades::getGradeName($value);
 				$classes[] = $class;
			}

			$this->view->classes = $classes;
		}
		else
			$this->view->classes = null;


		$this->getHelper('layout')->disableLayout();
    }

    public function assignmentStatsAction()
    {
		$auth = Zend_Auth::getInstance();
		$instructor = $auth->getIdentity()->cl_IID;

		$currentPeriod = Content_Model_School::getCurrentTermYear();

		$assignModel = new Content_Model_Assignments();
		$stats = $assignModel->assignmentStatistics(
							$instructor,
							4,
							$currentPeriod->year,
							$currentPeriod->term,
							11);

		$this->view->stats = $stats;
    }

    public function enterCommentsAction()
    {
    }

    public function ctcommentsAction()
    {
		$auth = Zend_Auth::getInstance();
		$class = $auth->getIdentity()->cl_classteacher;
		$this->view->grades = array($class => Custom_Grades::getGradeName($class));

		$classteacher = Content_Model_Instructors::findClassTeacher($class);

		$this->view->ctname = $classteacher->firstname .' '. $classteacher->lastname;
    }

    public function allowedGradesAction()
    {
		$auth = Zend_Auth::getInstance();
		$instructor = $auth->getIdentity()->cl_IID;

		$ins = new Content_Model_Instructors();
		$gradelevels = $ins->taughtGrades($instructor);

		$this->view->aGrades = $gradelevels;

    }

    public function deleteSubjectClassAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);

        Content_Model_Instructors::deleteClassSubject(
               $this->_request->getParam('subject'),
               $this->_request->getParam('grade'),
               $this->_request->getParam('instructor'));
    }

    public function availableClassesAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);

        $gradeLevels = Content_Model_GradeLevels::gradelevels();

        if ($this->_request->isXmlHttpRequest()){
            $courseModel = new Content_Model_Course();

            $result = $courseModel->getAvailableClasses($this->_request->getParam('courseid'));

            $av = array();

            if ($result != null){
                foreach ($gradeLevels as $key => $value) {
                    if (!in_array($key, $result)){
                    	if (Content_Model_Course::isSubjectTaughtInClass($this->_request->getParam('courseid'), $key))
                        		$av[$key] = $value;
                    }
                }
            }
            else{
            	foreach ($gradeLevels as $key => $value) {
            			if (Content_Model_Course::isSubjectTaughtInClass($this->_request->getParam('courseid'), $key))
            				$av[$key] = $value;
            	}
            }
            header('Content-Type: application/json');

            echo json_encode($av);
        }
        else
        {
            throw new Zend_Exception("Non ajax call made to server... Process aborted");
        }
    }

    public function resetPasswordAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);

        //reseting the password of an instructor ... send reset to email address or telno via SMS
        Content_Model_Instructors::resetPassword($this->_request->getParam('instructor'));

        $this->getResponse()->setHttpResponseCode(202);
        $this->getResponse()->sendHeaders();
    }

    public function allgradelevelsAction()
    {
        //action body
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);

        echo Zend_Json::encode(Array(-1=>'Not Applicable', -2=>'Subject Teacher') + Content_Model_GradeLevels::gradelevels());
    }

    public function updateDetailsAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);

        $instructor = $this->getRequest()->getParam('instructor');
        $value = $this->_request->getParam('value');
        $column = $this->_request->getParam('column');

        echo Content_Model_Instructors::updateDetails($instructor, $column, $value);

    }

    public function allsubjectsAssignedAction()
    {
        $this->view->layout()->disableLayout();
    }

    public function dtballassignsourceAction()
    {
        // action body
        $this->view->layout()->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);

      $subjectAssigns = Content_Model_Instructors::allSubjectsAssigned(
                 $this->_request->getParam('iDisplayStart'),
                $this->_request->getParam('iDisplayLength'),
                $this->_request->getParam('sSearch'),
                $this->_request->getParam('iSortCol_0'),
                $this->_request->getParam('sSortDir_0')
        );

         $output = array ("sEcho" => intval ($this->_request->getParam('sEcho')),
                 "iTotalDisplayRecords" => $subjectAssigns['iTotalRecords'],//$subjectAssigns['iDisplayRecords'],
                 "iTotalRecords" => $subjectAssigns['iTotalRecords'],
                 "aaData" => array () );

      /*   $output = array ("sEcho" => intval ($this->_request->getParam('sEcho')),
                "iTotalDisplayRecords" => 0,//$courts['iDisplayRecords'],
                "iTotalRecords" => 0,
                "aaData" => array () );
 */
        //transformation of the array
        foreach ($subjectAssigns['sa'] as $subAssign) {
            $row = array();
            $row[] = $subAssign->cl_coursename;

            $grades = array_intersect_key(Content_Model_GradeLevels::gradelevels(),
                                        array_flip(Zend_Json::decode($subAssign->cl_gradelevels)));


            $row[] ="<span style='font-size: 12px;'>" . implode('; ', array_values($grades)) ."</span>";

            $row[] = $subAssign->fullname."
                    	<span class=\"label label-inverse label-mini\"><a href=\"\" onclick=\"$.fn.getInstDetails(".$subAssign->cl_instructor.",'".$subAssign->fullname."'); return false;\">Edit</a></span>
                        ";
            $row[] = ($subAssign->cl_shared) ? 'Yes' : 'No';
            $output['aaData'][] = $row;
        }

        echo json_encode($output);
    }

    public function studentListAction()
    {
        // action body
    }

    public function generatepwdAction()
    {
    	// action body

    	//get identity of the user
    	$instructor =  Zend_Auth::getInstance()->getIdentity()->cl_IID;
    	$change = $this->_request->getParam('change');
    	if (isset($change) && $change == 1){
    		$this->getHelper('viewRenderer')->setNoRender(true);
    		$iModel = new Content_Model_Instructors();
    		$result = $iModel->find($instructor)->current();
    		if ($result){
    			if ($result->password == $this->_request->getParam('oldpwd') &&
    					$this->_request->getParam('newpwd') == $this->_request->getParam('matchpwd')){
    				$result->password = $this->_request->getParam('newpwd');
    				$result->save();

    				echo 'Password successfully updated';
    			}
    			else
    				echo 'Update failed : password mismatch';
    		}
    	}

    	$passwordForm = new Content_Form_ChangePassword();

    	$this->view->form = $passwordForm;
    }

    public function profileAction()
    {
        // action body
        $identity = Zend_Auth::getInstance()->getIdentity();

        $this->view->username = $identity->username;
        $this->view->lastname = $identity->lastname;
        $this->view->firstname = $identity->firstname;
        $this->view->email = $identity->email;
        $this->view->telno = $identity->telno;
    }

    public function mailboxAction()
    {
        // action body
    }

    public function authorizeExpenseAction(){
		$this->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender(true);

		$expenseid = $this->_request->getParam('expenseid');
		$result = Content_Model_Expenses::authorizeExpense($expenseid);

		header('Content-Type: application/json');
		echo json_encode(array('status' => 1));
    }

}



























