<?php

class Content_CourseController extends Zend_Controller_Action
{

    protected $gradeLevels = '';

    public function init()
    {
        /* Initialize action controller here */
    	$this->gradeLevels = Content_Model_GradeLevels::gradelevels();
    }

    public function indexAction()
    {
        // action body

    }

    public function createAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();

        $courseform = new Content_Form_NewCourse();
        $courseform->setAction('/course/create');
        $this->view->form = $courseform;

        if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('send') == 'true'){
         	$courseModel = new Content_Model_Course();

         	if($_POST['cl_coursename'] != '' && $_POST['cl_gradelevels'] != null ){
        		$courseModel->createCourse(
        				$_POST['cl_coursename'],
     			        json_encode($this->_request->getParam('cl_gradelevels')),
    			        json_encode($this->_request->getParam('cl_gradeexempt')),
        		        $_POST['cl_examinable'],
    			        $_POST['cl_shared'],
    			        $_POST['cl_courseorder']
        				);
    			die(json_encode(array('status'=>1)));
         	}
         	else
         	{
                      $this->getResponse()->setHttpResponseCode(406);
                      $this->getResponse()->sendHeaders();
         	}
        }
        
    }

    public function listAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();

    	$currentCourses = Content_Model_Course::getCourses();
    	if ($currentCourses->count() > 0){
    		$this->view->courses = $currentCourses;
    	}
    	else
    		$this->view->courses = null;
    }

    public function deleteAction()
    {
        // action body
 /*    	$id = $this->_request->getParam('id');
    	$courseModel = new Content_Model_Course();
    	$courseModel->deleteCourse($id);
    	return $this->_forward('list'); */
    }

    public function updateAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();

    	//$courseformUpdate = new Content_Form_UpdateCourse();
    	$courseformUpdate = new Content_Form_UpdateCourse();
    	$courseformUpdate->setAction('/course/update');

    	$courseModel = new Content_Model_Course();
    	if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('send') == 'true'){
    		  if ($courseformUpdate->isValid($_POST)){
    			$courseModel->updateCourse(
    					$_POST['cl_courseid'],
        				$_POST['cl_coursename'],
    			        json_encode($this->_request->getParam('cl_gradelevels')),
    			        json_encode($this->_request->getParam('cl_gradeexempt')),
    			        $_POST['cl_examinable'],
    			        $_POST['cl_shared'],
    			        $_POST['cl_courseorder']
        				);
    			die(json_encode(array('status'=>1)));
    		}
    		else
    		{
    		    $this->getResponse()->setHttpResponseCode(406);
    		    $this->getResponse()->sendHeaders();
    		}
    	}
    	else{
    		$id = $this->_request->getParam('id');
    		$currentCourse = $courseModel->find($id)->current();
    		$courseformUpdate->populate($currentCourse -> toArray());
            $courseformUpdate->cl_gradelevels->setValue(Zend_Json::decode($currentCourse->cl_gradelevels));
            $courseformUpdate->cl_gradeexempt->setValue(Zend_Json::decode($currentCourse->cl_gradeexempt));
    	}

    	$this->view->form = $courseformUpdate;
    }

    public function listpanelAction()
    {

    	$auth = Zend_Auth::getInstance();
    	$identity = $auth->getIdentity();

    	if (isset($identity->cl_IID)){
          $currentCourses = Content_Model_Course::getInstructorCourses($identity->cl_IID);

	       	if ($currentCourses != null){
	     		$this->view->courses = $currentCourses;
	    	}
	    	else
	    		$this->view->courses = null;
    	}
    	else
	    		$this->view->courses = null;
    }

    public function contentAction()
    {

 		$auth = Zend_Auth::getInstance();
 		$identity = $auth->getIdentity();
 		$this->view->term = $identity->term;
 		$this->view->year = $identity->year;

 		if (isset($identity->cl_IID)){
 			$currentCourses = Content_Model_Course::getInstructorCourses($identity->cl_IID);

 			if ($currentCourses != null){
 				$this->view->courses = $currentCourses;
 			}
 			else
 				$this->view->courses = null;
 		}
 		else
 			$this->view->courses = null;
    }

    public function contentLoadAction()
    {
 		$this->getHelper('layout')->disableLayout();
 		$this->getHelper('viewRenderer')->setNoRender();

 		header('Content-Type: application/json');

 		$courseid = $this->_request->getParam('courseid');
 		$term = $this->_request->getParam('term');
 		$year = $this->_request->getParam('year');
 		$gradeid = $this->_request->getParam('gradeid');

 		$content = Content_Model_Course::getCourseContent($courseid, $gradeid, $term, $year);

 		if ($content == null){
 			$content = new stdClass();
 			$content->cl_content = 'no works covered found';
 		}

 		echo json_encode(array("content"=> $content->cl_content,
 				'grade' => Custom_Grades::getGradeName($this->_request->getParam('gradeid')) ));

    }

    public function contentPostAction()
    {
 		//check to make sure that the request is a valid ajax / post request....
 		$this->getHelper('layout')->disableLayout();
 		$this->getHelper('viewRenderer')->setNoRender();

 		$courseid = $this->_request->getParam('courseid');
 		$term = $this->_request->getParam('term');
 		$year = $this->_request->getParam('year');
 		$gradeid = $this->_request->getParam('gradeid');
 		$content = $this->_request->getParam('content');

 		$result = Content_Model_Course::postCourseContent($courseid, $term, $gradeid, $year, $content);

 		header('Content-Type: application/json');

 		echo json_encode(array("content"=> $result->cl_content,
 				'grade' => Custom_Grades::getGradeName($this->_request->getParam('gradeid')) ));

    }

    public function deleteClassAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);

        Content_Model_Course::deleteClass(
        $this->_request->getParam('subject'),
        $this->_request->getParam('grade'));

        $this->getResponse()->setHttpResponseCode(202);
        $this->getResponse()->sendHeaders();
    }

    public function gradeCourseAction()
    {
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();

        $instructor = $this->_request->getParam('instructor');
        $grade = $this->_request->getParam('grade');

        $gradeCourses = Content_Model_Course::getInstructorGradeCourses($instructor, $grade, true);

        $str = "<option value='".null."'>-- select subject --</option>";
        if (count($gradeCourses) > 0){
            foreach ($gradeCourses as $key => $value) {
                $str .= "<option value='$key'>$value</option>";
            }
        }
        else {
            $str = "<option value='".null."'>No subjects found</option>";
        }

        echo $str;
    }

    public function courseSyllabusAction()
    {
        // action body
    	//action body
    	$this->getHelper('layout')->disableLayout();
    	$identity = Zend_Auth::getInstance()->getIdentity();
    	
    	$currentterm = Content_Model_School::getCurrentTermYear(false);
    	
    	if (!$currentterm)
    		$this->_redirect('/notermerror');
    	
    	if (isset($identity->CurrentContext)){
    		$this->view->archived = isset($identity->CurrentContext);
    		$ss = Content_Model_Instructors::taughtPastGradesClasses($identity->cl_IID, $identity->CurrentContext['Term'], $identity->CurrentContext['Year']);
    		$subjects = $ss['classes'];
    		$classes = $ss['grades'];
    		sort($classes, SORT_ASC);
    	}
    	else{
    		$classes = Content_Model_Instructors::taughtGrades($identity->cl_IID);
    		sort($classes, SORT_ASC);
    		$subjects = Content_Model_Course::getInstructorCourses($identity->cl_IID);
    	}
    	
    	$this->view->subjects = $subjects;
    	
    	$s = $subjects->toArray();
    	$x = $s[0]['cl_courseid'];
    	$firstGrade = intval($classes[0]);
    	$buttons =  "<label id='c' style='display:none'>$x</label><label id='g' style='display:none'>$firstGrade</label><div class=\"btn-group\" data-toggle=\"buttons-radio\">";
    	$first = 0;
    	
    	$tmp = array();
    	foreach ($subjects as $subject) {
    		if (in_array($subject->cl_courseid, $tmp))
    			continue;
    		 
    		$tmp[] = $subject->cl_courseid;
    		$st = substr($subject->cl_coursename, 0, 10);
    		if ($first == 0)
    			$buttons .= "<button id=\"btnfirst\" onclick=\"$.fn.filterSyllabus($subject->cl_courseid,$('#g').text())\" type=\"button\" title=\"$subject->cl_coursename\" class=\"btn btn-info btn-small\">$st...</button>";
    		else
    			$buttons .= "<button onclick=\"$.fn.filterSyllabus($subject->cl_courseid,$('#g').text())\" type=\"button\" title=\"$subject->cl_coursename\" class=\"btn btn-info btn-small\">$st...</button>";
    		$first += 1;
    	};
    	
    	$buttons .=  '</div>';
    	
    	$this->view->classes = $classes;
    	$this->view->subButtons = $buttons;
    	$this->view->firstcourse = $x;
    	
    	$this->view->current = Content_Model_School::isCurrentPeriod($currentterm->term, $currentterm->year);
        
    }

    public function updateSyllabusAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $form = new Content_Form_Syllabus();
        $this->view->sendState = 'new';
                
       	$currentTerm = Content_Model_School::getCurrentTermYear(false);
        
        if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('send') == 'new'){
        	$result = Content_Model_Course::addSyllabus(
        					$this->_request->getParam('cid'), 
        					$this->_request->getParam('grade'), 
        					$currentTerm->term, 
        					$currentTerm->year, 
        					$this->_request->getParam('section'), 
        					$this->_request->getParam('status')
        		);
        	if ($result) {
        		die('Syllabus content successfully updated');
        	}
        	else{
        		die('Access denied. Please check subject/class combination');
        	}
        }
        else if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('send') == 'old'){
        	    $result = Content_Model_Course::updateSyllabus(
        	    			$this->_request->getParam('cl_id'),
        					$this->_request->getParam('section'), 
        					$this->_request->getParam('status')
        		);
	        	if ($result) {
	        		die('Syllabus content successfully updated');
	        	}
	        	else{
	        		die('Access denied. Please check subject/class combination');
	        	}
        }
        else  if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('update') == 'true'){
        	
            $id = $this->_request->getParam('id');
            $syllabus = Content_Model_Course::loadSyllabus($id);
            $form->populate($syllabus -> toArray());
            $this->view->state = 'Student Record Update';
            $this->view->sendState = 'old';
        }
        else  if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('update') == 'delete'){
        	$id = $this->_request->getParam('id');
        	if (Content_Model_Course::deleteSyllabi($id)){
        		die('Section was successfully deleted');
        	}
        	else die('Deletion of section failed');
        }
        
        $this->view->info = Content_Model_Course::getCourseName($this->_request->getParam('cid'))." : ".Content_Model_GradeLevels::getGradeName($this->_request->getParam('grade'));
        $this->view->form = $form;
    }

    public function syllabiSourceAction()
    {
        // action body    	// action body
    	$this->getHelper('layout')->disableLayout();
    	$this->getHelper('viewRenderer')->setNoRender(true);
    	
    	$courseid = $this->_request->getParam('cid');
    	$gradeid = $this->_request->getParam('grade');
    	
    	$identity = Zend_Auth::getInstance()->getIdentity();
    	
    	if (!isset($identity->CurrentContext))
    		$allowed = Content_Model_Instructors::isCourseGradeAllowed($courseid, $gradeid, $identity->cl_IID);
    	else
    		$allowed = true;
    	
    	
    	if ($allowed){
    		$currentPeriod = Content_Model_School::getCurrentTermYear();
    		$term = $currentPeriod->term;
    		$year = $currentPeriod->year;
    	
    		$syllabuslist = Content_Model_Course::getSyllabusList(
    				$this->_request->getParam('iDisplayStart'),
    				$this->_request->getParam('iDisplayLength'),
    				$this->_request->getParam('sSearch'),
    				$this->_request->getParam('iSortCol_0'),
    				$this->_request->getParam('sSortDir_0'),
    				$term,
    				$year,
    				$courseid,
    				$gradeid
    		);
    	
    		$output = array ("sEcho" => intval ($this->_request->getParam('sEcho')),
    				"iTotalDisplayRecords" => $syllabuslist['iDisplayRecords'],
    				"iTotalRecords" => $syllabuslist['iTotalRecords'],
    				"aaData" => array () );
    	
    		$status = array(0=>'Pending', 1=>'Completed', 2=>'In-Progress');
    		
    		foreach ($syllabuslist['data'] as $syllabi) {
    	
    			$row = array();
    			
    			//$gLetter = Content_Model_Examinations::getGradeLetter($mark);
    			$row[] = $syllabi->section;
    			$row[] = $status[$syllabi->status];
    			$row[] = "<a class='label label-info' onclick='$.fn.loadSyllabus($syllabi->cl_id); return false;'>Update</a> | <a class='label label-important' onclick='$.fn.deleteSyllabi($syllabi->cl_id, \"$syllabi->section\"); return false;'>Delete</a>";
    			$output['aaData'][] = $row;
    		}
    	
    	}
    	else{
    		$output = array ("sEcho" => intval ($this->_request->getParam('sEcho')),
    				"iTotalDisplayRecords" => 0,
    				"iTotalRecords" => 0,
    				"aaData" => array () );
    	}
    	echo json_encode($output);
    }

    public function syllabiSummaryAction()
    {
        // action body
        
    	$identity = Zend_Auth::getInstance()->getIdentity();
    	$this->view->allow = true;
    	 
    	$currentterm = Content_Model_School::getCurrentTermYear(false);
    	 
    	if (!$currentterm)
    		$this->_redirect('/notermerror');
    	 
    	if (isset($identity->CurrentContext)){
    		$this->view->archived = isset($identity->CurrentContext);
    		$ss = Content_Model_Instructors::taughtPastGradesClasses($identity->cl_IID, $identity->CurrentContext['Term'], $identity->CurrentContext['Year']);
    		$subjects = $ss['classes'];
    		$classes = $ss['grades'];
    		
    		if(!$classes && $identity->role >= 100)
    			$classes = array_values(Custom_Grades::getPrimaryLevels());
    		else if (!$classes&& $identity->role == 50){
    			$this->view->allow = false;
    			return ('No records for this instructor');
    		}
    		
    		sort($classes, SORT_ASC);
    	}
    	else{
    		$classes = Content_Model_Instructors::taughtGrades($identity->cl_IID);
    		
    		if(!$classes && $identity->role >= 100)
    			$classes = array_values(Custom_Grades::getPrimaryLevels());
    		else if (!$classes&& $identity->role == 50){
    			$this->view->allow = false;
    			return ('No records for this instructor');
    		}
    		
    		sort($classes, SORT_ASC);
    		$subjects = Content_Model_Course::getInstructorCourses($identity->cl_IID);
    	}
    	 
    	$this->view->subjects = $subjects;
    	$this->view->classes = $classes;
    	
    	
        //Zend_Debug::dump($subjects); exit;
    }


}





















