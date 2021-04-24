<?php

class Content_AssignmentsController extends Zend_Controller_Action
{

    protected $_identity = null;

    protected $_locked = false;

    public function init()
    {

    	$auth = Zend_Auth::getInstance();
    	$this->_identity = $auth->getIdentity();
    	$this->_locked = isset($this->_identity->CurrentContext);

    	$mobileContext = $this->_helper->getHelper('MobileContext');
    	$mobileContext->addActionContext('index')
    	              ->addActionContext('assign-summary')
    	                ->initContext();

    }

    public function indexAction()
    {
        // action body
        //$this->getHelper('layout')->disableLayout();
    	$CurrentTerm = Content_Model_School::getCurrentTermYear(false);

    	if (!$CurrentTerm)
    		$this->_redirect('/notermerror');

    	$this->view->locked = isset($this->_identity->CurrentContext);

    	$this->view->term = $CurrentTerm->term;
    	$this->view->year = $CurrentTerm->year;
    }

    public function newAction()
    {
        // action body
         $this->view->layout()->disableLayout();
         if ($this->_locked) return ;

    	 $form = new Content_Form_Assignment();
    	 $this->view->state = 'New Assessment Entry';
    	 $this->view->sendState = 'new';

    	 $current = Content_Model_School::getCurrentTermYear(false);
    	 $term = $current->term;
    	 $year = $current->year;

    	 if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('send') == 'new'){
    	     //saving new data
    	     if ($form->isValid($this->_request->getParams())){
    	         try {
                     $assignmentModel = new Content_Model_Assignments();
        	    	 $res = $assignmentModel->newAssignment(
        	    			$this->_identity->cl_IID ,
        	    			$this->_request->getParam('cl_topic'),
        	    			$this->_request->getParam('cl_type'),
        	    			$year,//$this->_request->getParam('cl_year'),
        	    			$term,//$this->_request->getParam('cl_term'),
        	    			date('Y-m-d', strtotime($this->_request->getParam('cl_date'))),
        	    			date('Y-m-d', strtotime($this->_request->getParam('cl_date'))),
        	    			$this->_request->getParam('cl_maxmark'),
        	    			$this->_request->getParam('cl_course'),
        	    			$this->_request->getParam('cl_grade')
        	    	);
    	             $this->getResponse()->setHttpResponseCode(202);
    	             $this->getResponse()->sendHeaders();
    	             die(json_encode(array('status' => 1, 'update' => 0) + $res));
    	         } catch (Exception $e) {
                    Zend_Debug::dump($e->getMessage());
    	         }
    	     }
    	 }
    	 else if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('send') == 'old'){
    	     //updating of existing record
    	     if ($form->isValidPartial($this->_request->getParams())){
    	         try {
    	             $assignmentModel = new Content_Model_Assignments();
    	             $res = $assignmentModel->updateAssignment(
    	                     $this->_request->getParam('cl_id') ,
    	                     $this->_request->getParam('cl_topic'),
    	                     $this->_request->getParam('cl_type'),
    	                     $year,//$this->_request->getParam('cl_year'),
    	                     $term, //$this->_request->getParam('cl_term'),
    	                     date('Y-m-d', strtotime($this->_request->getParam('cl_date'))),
    	                     date('Y-m-d', strtotime($this->_request->getParam('cl_date'))),
    	                     0,
    	                     $this->_request->getParam('cl_course'),
    	                     $this->_request->getParam('cl_grade')
     	             );
    	             $this->getResponse()->setHttpResponseCode(202);
    	             $this->getResponse()->sendHeaders();
    	             die(json_encode(array('status' => 1, 'update' => 1)+ $res));
    	         } catch (Exception $e) {
                    Zend_Debug::dump($e->getMessage());
    	         }
    	     }
    	 }
    	 else  if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('update') == 'true')
         {
            $assignmentModel = new Content_Model_Assignments();
            $id = $this->_request->getParam('cl_id');
            $assignment = $assignmentModel->find($id)->current();

            $identity = Zend_Auth::getInstance()->getIdentity();
            $form->cl_course->addMultiOptions(Content_Model_Course::getInstructorGradeCourses($identity->cl_IID, $assignment->cl_grade, true));
            $form->populate($assignment -> toArray());
            $form->cl_maxmark->setAttrib('disabled', true);
            //$form->removeElement('cl_maxmark');

            $this->view->state = 'Assessment Record Update';
            $this->view->sendState = 'old';
         }

      	 $this->view->form = $form;

    }

    public function addAction()
    {
    	if ($this->_locked) return ;
	    	 $assignmentModel = new Content_Model_Assignments();
	    	 $assignmentModel->newAssignment(
	    			$this->_identity->cl_IID ,
	    			$this->_request->getParam('cl_topic'),
	    			$this->_request->getParam('cl_type'),
	    			$this->_request->getParam('cl_year'),
	    			$this->_request->getParam('cl_term'),
	    			date('Y-m-d', strtotime($this->_request->getParam('cl_date'))),
	    			date('Y-m-d', strtotime($this->_request->getParam('cl_date'))),
	    			$this->_request->getParam('cl_maxmark'),
	    			$this->_request->getParam('cl_course'),
	    			$this->_request->getParam('cl_grade')
	    	);

	    	$this->_redirect('/home/');
    }

    public function deleteAction()
    {
    	if ($this->_locked) return ;
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);

    	$assignmentModel = new Content_Model_Assignments();
    	$assignmentModel->deleteAssignment($this->_request->getParam('id'));

    }

    public function listAction()
    {
        // action body
        $assignmentModel = new Content_Model_Assignments();
        $assignments =  $assignmentModel->listAssignments();

        $this->view->assignments = $assignments;

    }

    public function myAssignmentsAction()
    {
    	// action body

    	if (isset($this->_identity->cl_IID)){
    		$myID =  $this->_identity->cl_IID;

   		   	$assignmentModel = new Content_Model_Assignments();
	    	$assignments =  $assignmentModel->listAssignments(false, $myID);

	    	$this->view->assignments = $assignments;
    	}

    	else
    		$this->view->assignments = null;
    }

    public function detailAction()
    {
        // action body
		//action here has been deferred to Ajax Controller's detailAction
    }

    public function postMarksAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);
        if ($this->_locked) return ;

    	//if ($this->_request->isXmlHttpRequest()){
        try {
            if ((floatval($this->_request->getParam('value')) <= floatval($this->_request->getParam('maxmark')))
                    && floatval($this->_request->getParam('value')) >= 0){

                Content_Model_AssignmentMarks::addMark(
                        $this->_request->getParam('student_id'),
                        $this->_request->getParam('assignment_id'),
                        trim($this->_request->getParam('value'))
                );

                echo intval($this->_request->getParam('value'));

            }elseif (floatval($this->_request->getParam('value')) < 0){
                echo 'Invalid mark';
            }
            else
            {
                echo 'Invalid mark';
            }/**/
        } catch (Exception $e) {
            Zend_Debug::dump($e->getMessage());
        }

       // }
        //else {
     	   	 	//throw new Zend_Exception("A non-ajax call was made to the server, pls contact administrator");
       //
    }

    public function topicnumberAction()
    {

    	$this->getHelper('layout')->disableLayout();
    	$this->getHelper('viewRenderer')->setNoRender();

    	$autoNum = Content_Model_Assignments::assignNumber(
    			$this->_request->getParam('course'),
    			$this->_request->getParam('term'),
    			$this->_request->getParam('year'),
    			$this->_request->getParam('grade'),
    			$this->_request->getParam('type'));

    	echo $this->_request->getParam('type') .' - #0' . $autoNum;

    }

    public function maxmarkAction()
    {
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);
        if ($this->_locked) return ;

    	echo $assignmentModel = Content_Model_Assignments::updateMaxMark(
    			$this->_request->getParam('assignmentid'),
    			trim($this->_request->getParam('value')));

    }

    public function listAssignmentsAction()
    {
    	if (isset($this->_identity->cl_IID)){
    		$myID =  $this->_identity->cl_IID;

    		$assignmentModel = new Content_Model_Assignments();
    		$assignments =  $assignmentModel->listAssignments(false, $myID);

    		$this->view->assignments = $assignments;
    	}

    	else
    		$this->view->assignments = null;
    }

    public function assignSummaryAction()
    {
        // action body
    	if (!$this->_request->getParam('layout')== true)
    		$this->getHelper('layout')->disableLayout();
        $identity = Zend_Auth::getInstance()->getIdentity();

        $ins = $this->_request->getParam('instructor');

        $currentPeriod = Content_Model_School::getCurrentTermYear();
        $term = $currentPeriod->term;
        $year = $currentPeriod->year;
        $this->view->archived = isset($identity->CurrentContext);


         if (isset($identity->CurrentContext)){

         	if ($ins){
         		unset($identity);
         		$identity = new stdClass();
         		$identity->cl_IID = $ins;
         	}

        	$ss = Content_Model_Instructors::taughtPastGradesClasses($identity->cl_IID, $term, $year, true);
        	$subjects = $ss['classes'];
        	$classes = $ss['grades'];

        	if(!$classes)
        		die('No records for this instructor');

        	sort(json_decode($classes), SORT_ASC);

        	$this->view->totalStudents = Content_Model_AssignmentMarks::getPastAssignmentStudentsCount($classes, $term, $year);
        }
        else{
        	if ($ins){
        		unset($identity);
        		$identity = new stdClass();
        		$identity->cl_IID = $ins;
        	}

	        $classes = Content_Model_Instructors::taughtGrades($identity->cl_IID, true);

	     	if(!$classes)
        		die('No records for this instructor');

	        sort(json_decode($classes), SORT_ASC);
	        $subjects = Content_Model_Course::getInstructorCourses($identity->cl_IID);
	        $this->view->totalStudents = Content_Model_Students::getStudentInGradeCount($classes);

        }


        $this->view->stats = Content_Model_Assignments::assignmentStatisticsAll($identity->cl_IID, $year, $term);

        $assignmentIds = Content_Model_Assignments::getInstructorAssignmentsID($identity->cl_IID, $term, $year);

        $this->view->assignedClasses = $classes;

        $s = "";
        $si = array();

        foreach ($subjects as $subject) {
        	if (in_array($subject->cl_courseid, $si))
        		continue;

        	$si[] = $subject->cl_courseid;

            if ($this->_request->getParam('mobile'))
                $st = substr($subject->cl_coursename, 0, 3);
            else
                $st = substr($subject->cl_coursename, 0, 3);
            $s .= "<span class=\"label label-mini label-info\">
            <a href='#' title=\"$subject->cl_coursename\"
             rel=\"tooltip\">$st</a></span> | ";
        };

        $this->view->assignedSubs = $s;

        if (count($assignmentIds)){
	        $this->view->totalStdAss = Content_Model_AssignmentMarks::getGradableAssignmentsCount($assignmentIds);
	        $this->view->totalGraded = Content_Model_AssignmentMarks::getGradedAssignmentsCount($assignmentIds);
	        $this->view->totalUnGraded = Content_Model_AssignmentMarks::getUnGradedAssignmentsCount($assignmentIds);
	        $this->view->totalZero = Content_Model_AssignmentMarks::getZeroAssignmentsCount($assignmentIds);
	        $this->view->totalAssess = Content_Model_Assignments::getTotalAssignments($identity->cl_IID, $term, $year);
        	$this->view->totalExempt = Content_Model_AssignmentMarks::getExemptAssignmentsCount($assignmentIds);
        }

        $this->view->lock = $this->_request->getParam('lock');
    }

    public function assessmentsAction()
    {
        // action body
        if (!$this->_request->getParam('layout')== true)
        	$this->getHelper('layout')->disableLayout();

        $identity = Zend_Auth::getInstance()->getIdentity();
        $this->view->allow = true;

        $currentPeriod = Content_Model_School::getCurrentTermYear();
        $term = $currentPeriod->term;
        $year = $currentPeriod->year;
        $ins = $this->_request->getParam('instructor');

        $this->view->current = Content_Model_School::isCurrentPeriod($term, $year);

        if (isset($identity->CurrentContext)){
          $this->view->archived = isset($identity->CurrentContext);

          if ($ins){
          	unset($identity);
          	$identity = new stdClass();
          	$identity->cl_IID = $ins;
          	$identity->role = 50;
          	$this->view->current = false;
          }

          $ss = Content_Model_Instructors::taughtPastGradesClasses($identity->cl_IID, $term, $year);
          $subjects = $ss['classes'];
          $classes = $ss['grades'];


           	if(count($classes) == 0 && $identity->role >= 100)
    			$classes = array_values(Custom_Grades::getPrimaryLevels());
    		else if ($identity->role != 50){
    			$this->view->allow = false;
    			return ('No records for this instructor');
    		}

    		sort($classes, SORT_ASC);
        }
        else{

        	if ($ins){
        		unset($identity);
        		$identity = new stdClass();
        		$identity->role = 50;
        		$identity->cl_IID = $ins;
        		$this->view->current = false;
        	}

        	$classes = Content_Model_Instructors::taughtGrades($identity->cl_IID);

           	if(!$classes && $identity->role >= 100)
    			$classes = array_values(Custom_Grades::getPrimaryLevels());
    		else if ($identity->role != 50){
    			$this->view->allow = false;
    			return ('No records for this instructor');
    		}

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
                $buttons .= "<button id=\"btnfirst\" onclick=\"$.fn.filterAssignments($subject->cl_courseid,$('#g').text())\" type=\"button\" title=\"$subject->cl_coursename\" class=\"btn btn-info btn-small\">$st...</button>";
            else
                $buttons .= "<button onclick=\"$.fn.filterAssignments($subject->cl_courseid,$('#g').text())\" type=\"button\" title=\"$subject->cl_coursename\" class=\"btn btn-info btn-small\">$st...</button>";
            $first += 1;
        };

        unset($tmp);

        $buttons .=  '</div>';

        $this->view->classes = $classes;
        $this->view->subButtons = $buttons;
        $this->view->firstcourse = $x;
        $this->view->ins = $identity->cl_IID;
    }

    public function assessmentlistAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);
        $ins = $this->_request->getParam('instructor');


        $currentPeriod = Content_Model_School::getCurrentTermYear();
        $term = $currentPeriod->term;
        $year = $currentPeriod->year;

        $identity = Zend_Auth::getInstance()->getIdentity();

        $course = $this->_request->getParam('cid');
        $grade = $this->_request->getParam('grade');

        if ($ins){
        	unset($identity);
        	$identity = new stdClass();
        	$identity->cl_IID = $ins;
        }

        if (Content_Model_School::isCurrentPeriod($term, $year)){
        	$allowed = Content_Model_Instructors::isCourseGradeAllowed($course, $grade,$identity->cl_IID);
        }
        else
        	$allowed = true;

        if ($allowed){
                $assessments = Content_Model_Assignments::listAssessments(
                        $this->_request->getParam('iDisplayStart'),
                        $this->_request->getParam('iDisplayLength'),
                        $this->_request->getParam('sSearch'),
                        $this->_request->getParam('iSortCol_0'),
                        $this->_request->getParam('sSortDir_0'),
                        $identity->cl_IID,
                        $course,
                        $term,
                        $year,
                        $grade
                );

                 $output = array ("sEcho" => intval ($this->_request->getParam('sEcho')),
                        "iTotalDisplayRecords" => $assessments['iDisplayRecords'],
                        "iTotalRecords" => $assessments['iTotalRecords'],
                        "aaData" => array () );

                //transformation of the array
                $allGrades = Content_Model_GradeLevels::gradelevels();
                $types =   array("CWK" => "CLASS WORK",
                                "HWK"=>"HOME WORK",
                                "GPW"=>"GROUP WORK",
                                "PRJ" => "PROJECT",
                                "UNT" => "UNIT TEST");

                foreach ($assessments['data'] as $assessment) {

                    $row = array();
                    $row[] = $assessment->cl_id;
                    $row[] = date('jS-M-Y', strtotime($assessment->cl_date));
                    $row[] = $assessment->cl_topic . " <a href='#' onclick='$.fn.updateassessment($assessment->cl_id); return false;'><span class='label label-success label-mini'>update</span></a>";
                    $row[] = $types[$assessment->cl_type];
                    $row[] = $allGrades[$assessment->cl_grade];
                    $row[] = $assessment->cl_maxmark;
                    $row[] = "<a href='#' onclick='$.fn.loadScores($assessment->cl_id, \"$assessment->cl_topic\"); return false;'><span class='label label-inverse label-mini'>scores</span></a>"
                             . " | <a href='#' onclick='$.fn.deleteassessment($assessment->cl_id, \"<strong>$assessment->cl_topic</strong>\"); return false;'><span class='label label-important label-mini'>delete</span></a>";;
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

    public function assessmentScoresAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();

        $id = $this->_request->getParam('data');
        $lock = $this->_request->getParam('lock');
        $this->view->locked = isset(Zend_Auth::getInstance()->getIdentity()->CurrentContext);

        if ($lock)
        	$this->view->locked = true;

        $assignmentDetails = Content_Model_Assignments::assignmentDetail($id);

            $marksModel = new Content_Model_AssignmentMarks();
            $scoreslist =  $marksModel->listScoresList($id);
            $studentsInGrade = Content_Model_Students::studentsinGradeNames($assignmentDetails['cl_grade']);

      try {
          $this->view->assignmentid = $id;
          $this->view->grade = $assignmentDetails['cl_grade'];
          $this->view->coursename = $assignmentDetails['cl_coursename'];
          $this->view->year = $assignmentDetails['cl_year'];
          $this->view->term = $assignmentDetails['cl_term'];
          $this->view->gradename = $assignmentDetails['cl_gradename'];
          $this->view->maxmark = $assignmentDetails['cl_maxmark'];
          $this->view->scoreslist = $scoreslist;
          $this->view->studentsInGrade = $studentsInGrade;
          $this->view->archived = isset(Zend_Auth::getInstance()->getIdentity()->CurrentContext);
         //$this->view->locked = Model_Reports::locked($assignmentDetails['cl_term'], $assignmentDetails['cl_year'], $assignmentDetails['cl_grade'])
         /*
           */
     	 } catch (Exception $e) {
          	Zend_Debug::dump($e->getMessage());
     	 }
    }

    public function exemptFromAssessmentAction()
    {
        // action body
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);
        if ($this->_locked) return ;

            try {
                Content_Model_AssignmentMarks::addExempt(
                    $this->_request->getParam('student_id'),
                    $this->_request->getParam('assignment_id'),
                    $this->_request->getParam('value')
                );
                if ($this->_request->getParam('value')) echo 'Yes'; else echo 'No';
            } catch (Exception $e) {
                Zend_Debug::dump($e->getMessage());
            }
    }

    public function ungradedAssessmentsAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->view->ins = $this->_request->getParam('instructor');
    }

    public function ungradedAssessSourceAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);
        $ins = $this->_request->getParam('instructor');

        $identity = Zend_Auth::getInstance()->getIdentity();

        $currentPeriod = Content_Model_School::getCurrentTermYear();
        $term = $currentPeriod->term;
        $year = $currentPeriod->year;

        $zero = $this->_request->getParam('zero');

        if ($ins){
        	unset($identity);
        	$identity = new stdClass();
        	$identity->cl_IID = $ins;
        }

        try {
            $assignmentIds = Content_Model_Assignments::getInstructorAssignmentsID($identity->cl_IID, $term, $year);

            if (count($assignmentIds) == 0)
            	die(json_encode(array ("sEcho" => intval ($this->_request->getParam('sEcho')),
                    "iTotalDisplayRecords" => 0,
                    "iTotalRecords" => 0,
                    "aaData" => array ())));

            $ungradeds =  Content_Model_AssignmentMarks::getUnGradedAssignments(
                    $this->_request->getParam('iDisplayStart'),
                    $this->_request->getParam('iDisplayLength'),
                    $this->_request->getParam('sSearch'),
                    $this->_request->getParam('iSortCol_0'),
                    $this->_request->getParam('sSortDir_0'),
                    $assignmentIds,
                    $zero);

            $output = array ("sEcho" => intval ($this->_request->getParam('sEcho')),
                    "iTotalDisplayRecords" => $ungradeds['iDisplayRecords'],
                    "iTotalRecords" => $ungradeds['iTotalRecords'],
                    "aaData" => array () );

            //transformation of the array
            $allGrades = Content_Model_GradeLevels::gradelevels();

            foreach ($ungradeds['data'] as $ungraded) {
                $row = array();
                $row[] = $ungraded->cl_assignmentid;
                $row[] = $ungraded->fullname;
                $row[] = $ungraded->course;
                $row[] = $ungraded->cl_topic;
                $row[] = $ungraded->cl_maxmark;
                $row[] = $ungraded->cl_mark;
                $row[] = ($ungraded->cl_exempt == 0)?'No' :'Yes';
                $row[] = $allGrades[$ungraded->cl_grade];
                $row[] = "<a href='#' onclick='$.fn.loadScores($ungraded->cl_assignmentid, \"$ungraded->cl_topic\"); return false;'><span class='label label-inverse label-mini'>scores</span></a>";
                $output['aaData'][] = $row;
            }
            echo json_encode($output);
        } catch (Exception $e) {
            Zend_Debug::dump($e->getMessage());
        }
    }

    public function zerogradedAssessmentsAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->view->ins = $this->_request->getParam('instructor');
    }

    public function outlookAction()
    {
		// action body
		$this->getHelper('layout')->disableLayout();
		$identity = Zend_Auth::getInstance()->getIdentity();

		$currentPeriod = Content_Model_School::getCurrentTermYear();
		$term = $currentPeriod->term;
		$year = $currentPeriod->year;
		$ins = $this->_request->getParam('ins');
		$this->view->locked = isset($identity->CurrentContext);


		if (isset($identity->CurrentContext)){
			$this->view->archived = isset($identity->CurrentContext);

			if ($ins){
				unset($identity);
				$identity = new stdClass();
				$identity->cl_IID = $ins;
				$this->view->locked = true;
			}

			$ss = Content_Model_Instructors::taughtPastGradesClasses($identity->cl_IID, $term, $year);
			$subjects = $ss['classes'];
			$classes = $ss['grades'];
			sort($classes, SORT_ASC);

			if(!$classes)
				die('No records for this instructor');

		}
		else{
			if ($ins){
				unset($identity);
				$identity = new stdClass();
				$identity->cl_IID = $ins;
				$this->view->locked = true;
			}

			$classes = Content_Model_Instructors::taughtGrades($identity->cl_IID);

			if(!$classes)
				die('No records for this instructor');

			sort($classes, SORT_ASC);
			$subjects = Content_Model_Course::getInstructorCourses($identity->cl_IID);
		}

		$this->view->subjects = $subjects;

		$s = $subjects->toArray();
		$x = $s[0]['cl_courseid'];
		$firstGrade = intval($classes[0]);
		$buttons =  "<label id='cc' style='display:none'>$x</label><label id='gg' style='display:none'>$firstGrade</label><div class=\"btn-group\" data-toggle=\"buttons-radio\">";
		$first = 0;

		$tmp = array();
		foreach ($subjects as $subject) {
			if (in_array($subject->cl_courseid, $tmp))
				continue;

			$tmp[] = $subject->cl_courseid;
			$st = substr($subject->cl_coursename, 0, 10);
			if ($first == 0)
				$buttons .= "<button id=\"btnfirst1\" onclick=\"$.fn.filterOutlook($subject->cl_courseid,$('#gg').text())\" type=\"button\" title=\"$subject->cl_coursename\" class=\"btn btn-info btn-small\">$st...</button>";
			else
				$buttons .= "<button onclick=\"$.fn.filterOutlook($subject->cl_courseid,$('#gg').text())\" type=\"button\" title=\"$subject->cl_coursename\" class=\"btn btn-info btn-small\">$st...</button>";
			$first += 1;
		};

		unset($tmp);

		$buttons .=  '</div>';

		$this->view->host = $_SERVER["HTTP_HOST"];
		$this->view->classes = $classes;
		$this->view->subButtons = $buttons;
		$this->view->firstcourse = $x;
		$currentterm = Content_Model_School::getCurrentTermYear(false);
		$this->view->term = $currentterm->term;
		$this->view->year = $currentterm->year;
		$this->view->ins = $identity->cl_IID;
    }

    public function getCourseReportAction()
    {
		$this->view->layout()->disableLayout();

		$reportModel = new Content_Model_Reports();

		$courseid = $this->_request->getParam('cid');
		$grade = $this->_request->getParam('grade');
		$rptType = $this->_request->getParam('t');
		$instructor = $this->_request->getParam('i');
		$term = $this->_request->getParam('term');
		$year = $this->_request->getParam('year');

/* 		$CurrentTerm = Content_Model_School::getCurrentTermYear(false);
		$term = $CurrentTerm->term;
		$year = $CurrentTerm->year; */

		$assignments = $reportModel->courseAssignments($rptType, $courseid, $term, $year, $grade, $instructor);

		$arr_assignments = array();

		//1st level iteration with list of assignments
		foreach ($assignments as $row) {

			//build an object for each assignment
			$objAssignment = new stdClass();
			$objAssignment->assignmentid = $row->cl_assignmentid;

			//2nd level iteration with the list of students and assignment
			$assignmentsDetail = $reportModel->courseReport($rptType, $courseid, $row->cl_assignmentid, $term, $year, $grade);

			foreach ($assignmentsDetail as $detail) {
				$objAssignment->marks[$detail->cl_studentid] = $detail->cl_mark;
				$objAssignment->maximum = $detail->cl_maxmark;
				$objAssignment->exempts[$detail->cl_studentid] = $detail->cl_exempt;
			}

			$arr_assignments[] = $objAssignment;
		}

		$this->view->assignments = $assignments;
		if (Content_Model_School::isCurrentPeriod($term, $year))
			$this->view->students = Content_Model_Students::studentsinGradeNames($grade);
		else
			$this->view->students = Content_Model_AssignmentMarks::getPastAssignmentStudents($grade, $courseid, $term, $year);
		$this->view->reports = $arr_assignments;

		$this->view->instructor = (Content_Model_Instructors::getPastCourseInstructor($courseid, $grade, $term, $year) != null) ?
									Content_Model_Instructors::getPastCourseInstructor($courseid, $grade, $term, $year)->fullname : '';

		/* $classteacher = Content_Model_Instructors::findClassTeacher($grade);
		$this->view->classteacher = $classteacher->firstname .' '. $classteacher->lastname;  */
		$this->view->classteacher = '';
		$this->view->coursename = Content_Model_Course::getCourseName($courseid);

		$this->view->assignmentType =  $rptType;

		$this->view->term = $term;
		$this->view->year = $year;
		$this->view->gradename = Custom_Grades::getGradeName($grade);
		$this->view->grade = $grade;
    }

    public function showSummaryAction()
    {

		$reportModel = new Content_Model_Reports();

		$courseid = $this->_request->getParam('cid');
		$grade = $this->_request->getParam('grade');
		$instructor = $this->_request->getParam('i');
		$term = $this->_request->getParam('term');
		$year = $this->_request->getParam('year');

		$summary = $reportModel->courseAssignmentSummary($courseid, $term, $year, $grade, $instructor);

		if (Content_Model_School::isCurrentPeriod($term, $year))
			$students = Content_Model_Students::studentsinGradeNames($grade);
		else
			$students = Content_Model_AssignmentMarks::getPastAssignmentStudents($grade, $courseid, $term, $year);

		$isExaminable =  Content_Model_Course::isExaminable($courseid, $grade);
		$arr_summary = array();

		$Maxmarks = new stdClass();
		$Maxmarks->CWK = 0;
		$Maxmarks->HWK = 0;
		$Maxmarks->GPW = 0;
		$Maxmarks->UNT = 0;
		$Maxmarks->PRJ = 0;

		foreach ($students as $student) {

			$studentRecord = new stdClass();
			$studentRecord->studentid = $student['cl_GPSN_ID'];
			$studentRecord->fullname = $student['fullname'];
			$studentRecord->cc = $courseid;
			$studentRecord->CWK = 0;
			$studentRecord->HWK = 0;
			$studentRecord->GPW = 0;
			$studentRecord->UNT = 0;
			$studentRecord->PRJ = 0;

			$studentRecord->CWKMax = 0;
			$studentRecord->HWKMax = 0;
			$studentRecord->GPWMax = 0;
			$studentRecord->UNTMax = 0;
			$studentRecord->PRJMax = 0;

			$studentRecord->GT = 0;

			foreach ($summary as $row) {

				if ($student['cl_GPSN_ID'] == $row->cl_studentid){

					switch ($row->cl_type) {
						case 'CWK':
							$Exemption = Content_Model_AssignmentMarks::getExemptTotal($row->cl_studentid, $term, $year, $grade, $courseid, $row->cl_type);

							if ($Exemption){
								$EM = $Exemption->EMarkTotal;
								$ET = $Exemption->ETotal;
							}
							else{
								$EM = 0;
								$ET = 0;
							}

							$studentRecord->CWK = ($row->total != null) ? ($row->total - $EM) : 0 ;
							if (($row->Mtotal - $ET) != 0)
								$studentRecord->CWKP = (($row->total - $EM) / ($row->Mtotal - $ET)) * 100;
							else
								$studentRecord->CWKP = 0;
							$studentRecord->CWKMax = $row->Mtotal - $ET;
							if ($Maxmarks->CWK < $row->Mtotal)
								$Maxmarks->CWK = $row->Mtotal;
							//Zend_Debug::dump($row->Mtotal);
							break;

						case 'HWK':
							$Exemption = Content_Model_AssignmentMarks::getExemptTotal($row->cl_studentid, $term, $year, $grade, $courseid, $row->cl_type);

							//Zend_Debug::dump($row->Total . '/' .$Exemption->EMarkTotal . '  :   '. $Exemption->ETotal.'/'.$row->MTotal);
							if ($Exemption){
								$EM = $Exemption->EMarkTotal;
								$ET = $Exemption->ETotal;
							}
							else{
								$EM = 0;
								$ET = 0;
							}

							$studentRecord->HWK = ($row->total != null) ? ($row->total - $EM) : 0 ;
							if (($row->Mtotal - $ET) != 0)
								$studentRecord->HWKP = (($row->total - $EM) / ($row->Mtotal - $ET)) * 100;
							else
								$studentRecord->HWKP = 0;
							$studentRecord->HWKMax = $row->Mtotal - $ET;
							if ($Maxmarks->HWK < $row->Mtotal){
								$Maxmarks->HWK = $row->Mtotal;
							}
							break;

						case 'GPW':
							$Exemption = Content_Model_AssignmentMarks::getExemptTotal($row->cl_studentid, $term, $year, $grade, $courseid, $row->cl_type);

							//Zend_Debug::dump($row->Total . '/' .$Exemption->EMarkTotal . '  :   '. $Exemption->ETotal.'/'.$row->MTotal);
							if ($Exemption){
								$EM = $Exemption->EMarkTotal;
								$ET = $Exemption->ETotal;
							}
							else{
								$EM = 0;
								$ET = 0;
							}

							$studentRecord->GPW = ($row->total != null) ?  ($row->total - $EM) : 0 ;
							if (($row->Mtotal - $ET) != 0)
								$studentRecord->GPWP = (($row->total - $EM) / ($row->Mtotal - $ET)) * 100;
							else
								$studentRecord->GPWP = 0;
							$studentRecord->GPWMax = $row->Mtotal - $ET;
							if ($Maxmarks->GPW < $row->Mtotal)
								$Maxmarks->GPW = $row->Mtotal;
							break;

						case 'UNT':
							$Exemption = Content_Model_AssignmentMarks::getExemptTotal($row->cl_studentid, $term, $year, $grade, $courseid, $row->cl_type);

							if ($Exemption){
								$EM = $Exemption->EMarkTotal;
								$ET = $Exemption->ETotal;
							}
							else{
								$EM = 0;
								$ET = 0;
							}

							$studentRecord->UNT = ($row->total != null) ?  ($row->total - $EM) : 0 ;
							if (($row->Mtotal - $ET) != 0)
								$studentRecord->UNTP = (($row->total - $EM) / ($row->Mtotal - $ET)) * 100;
							else
								$studentRecord->UNTP = 0;
							$studentRecord->UNTMax = $row->Mtotal - $ET;
							if ($Maxmarks->UNT < $row->Mtotal)
								$Maxmarks->UNT = $row->Mtotal;
							break;

						case 'PRJ':

							$Exemption = Content_Model_AssignmentMarks::getExemptTotal($row->cl_studentid, $term, $year, $grade, $courseid, $row->cl_type);

							if ($Exemption){
								$EM = $Exemption->EMarkTotal;
								$ET = $Exemption->ETotal;
							}
							else{
								$EM = 0;
								$ET = 0;
							}

							$studentRecord->PRJ = ($row->total != null) ?  ($row->total - $EM) : 0 ;
							if (($row->Mtotal - $ET) != 0)
								$studentRecord->PRJP = (($row->total - $EM) / ($row->Mtotal - $ET)) * 100;
							else
								$studentRecord->PRJP = 0;
							$studentRecord->PRJMax = $row->Mtotal - $ET;
							if ($Maxmarks->PRJ < $row->Mtotal)
								$Maxmarks->PRJ = $row->Mtotal;
							break;

						default:

							break;
					}//END OF SWITCH

					//TOTAL CLASS MARK

					$studentRecord->GT = $studentRecord->CWK +
					$studentRecord->HWK +
					$studentRecord->GPW +
					$studentRecord->UNT +
					$studentRecord->PRJ;


					$studentRecord->HM =$Maxmarks->HWK;
					$studentRecord->CM =$Maxmarks->CWK;
					$studentRecord->UM =$Maxmarks->UNT;
					$studentRecord->GM =$Maxmarks->GPW;
					$studentRecord->PM =$Maxmarks->PRJ;

					$Maxmarks->Total = $Maxmarks->CWK +
					$Maxmarks->HWK +
					$Maxmarks->GPW +
					$Maxmarks->UNT +
					$Maxmarks->PRJ;

					$studentRecord->TCM100 = ($studentRecord->GT /
							($studentRecord->CWKMax +
									$studentRecord->HWKMax +
									$studentRecord->UNTMax +
									$studentRecord->GPWMax +
									$studentRecord->PRJMax)) * 100;

					$studentRecord->TCM40 = $studentRecord->TCM100 * 0.40;

					$mark = $reportModel->getCourseExamMark($row->cl_studentid, $courseid, $term, $year, $grade);

					if ($mark){
						$markedover =  Content_Model_Examinations::markedover($term, $year, $courseid, $grade, 0, false);
						if ($markedover != 0)
							$studentRecord->EXM100 = ($mark->cl_mark / $markedover) * 100;
						else
							$studentRecord->EXM100 = 0;
					}
					else
						$studentRecord->EXM100 =  0;

					$studentRecord->EXM60 = $studentRecord->EXM100 * 0.60;

					$studentRecord->TM = $studentRecord->TCM40 + $studentRecord->EXM60;

					if (!$isExaminable){
						$studentRecord->TM = $studentRecord->TCM100;
					}

					$studentRecord->GR = $reportModel->getGradeLetter(round($studentRecord->TM));
					$studentRecord->isExaminable = $isExaminable;
				}
			}//end of summary for

			$arr_summary[] = $studentRecord;

		}//end of student for

		$this->view->term = $term;
		$this->view->year = $year;
		$this->view->gradename = Custom_Grades::getGradeName($grade);
		$this->view->grade = $grade;

		$this->view->classteacher = '';
		$this->view->instructor = (Content_Model_Instructors::getPastCourseInstructor($courseid, $grade, $term, $year) != null) ?
									Content_Model_Instructors::getPastCourseInstructor($courseid, $grade, $term, $year)->fullname : '';

		$this->view->coursename = Content_Model_Course::getCourseName($courseid);
		$this->view->isExaminable = $isExaminable;
		$this->view->maxmarks = $Maxmarks;

		$this->view->summary = $arr_summary;

		$this->view->layout()->disableLayout();
    }

    public function studentDetailsAction()
    {
		$this->view->layout()->disableLayout();

		$reportModel = new Content_Model_Reports();

		$courseid = $this->_request->getParam('cid');
		$grade = $this->_request->getParam('grade');
		$rptType = $this->_request->getParam('t');
		$instructor = $this->_request->getParam('i');
		$term = $this->_request->getParam('term');
		$year = $this->_request->getParam('year');
		$studentid = $this->_request->getParam('sid');

		/* 		$CurrentTerm = Content_Model_School::getCurrentTermYear(false);
		 $term = $CurrentTerm->term;
		$year = $CurrentTerm->year; */

		$assignments = $reportModel->courseAssignments($rptType, $courseid, $term, $year, $grade, $instructor, $studentid);

		$arr_assignments = array();

		//1st level iteration with list of assignments
		foreach ($assignments as $row) {

			//build an object for each assignment
			$objAssignment = new stdClass();
			$objAssignment->assignmentid = $row->cl_assignmentid;

			//2nd level iteration with the list of students and assignment
			$assignmentsDetail = $reportModel->courseReport($rptType, $courseid, $row->cl_assignmentid, $term, $year, $grade,$studentid);

			foreach ($assignmentsDetail as $detail) {
				$objAssignment->marks[$detail->cl_studentid] = $detail->cl_mark;
				$objAssignment->maximum = $detail->cl_maxmark;
				$objAssignment->exempts[$detail->cl_studentid] = $detail->cl_exempt;
			}

			$arr_assignments[] = $objAssignment;
		}

		$this->view->assignments = $assignments;
		if (Content_Model_School::isCurrentPeriod($term, $year))
			$this->view->students = Content_Model_Students::studentsinGradeNames($grade, $studentid);
		else
			$this->view->students = Content_Model_AssignmentMarks::getPastAssignmentStudents($grade, $courseid, $term, $year, $studentid);
		$this->view->reports = $arr_assignments;

		$this->view->instructor = (Content_Model_Instructors::getPastCourseInstructor($courseid, $grade, $term, $year) != null) ?
		Content_Model_Instructors::getPastCourseInstructor($courseid, $grade, $term, $year)->fullname : '';

		/* $classteacher = Content_Model_Instructors::findClassTeacher($grade);
		 $this->view->classteacher = $classteacher->firstname .' '. $classteacher->lastname;  */
		$this->view->classteacher = '';
		$this->view->coursename = Content_Model_Course::getCourseName($courseid);

		$this->view->assignmentType =  $rptType;

		$this->view->term = $term;
		$this->view->year = $year;
		$this->view->gradename = Custom_Grades::getGradeName($grade);
		$this->view->grade = $grade;
    }

    public function monitorAssessmentsAction()
    {
    	// action body
    	//$this->getHelper('layout')->disableLayout();
    	$CurrentTerm = Content_Model_School::getCurrentTermYear(false);

    	if (!$CurrentTerm)
    		$this->_redirect('/notermerror');

    	$this->view->locked = isset($this->_identity->CurrentContext);

    	$this->view->term = $CurrentTerm->term;
    	$this->view->year = $CurrentTerm->year;

    	$this->view->instructors = Content_Model_Instructors::listInstructors(50);
    }

    public function getRecentStudentAssignmentAction()
    {
        // action body
    	$this->getHelper('layout')->disableLayout();
        $studentid = $this->_request->getParam('studentid');
        $CurrentTerm = Content_Model_School::getCurrentTermYear();

        $this->view->term = $CurrentTerm->term;
        $this->view->year = $CurrentTerm->year;

        $result = Content_Model_AssignmentMarks::getRecentStudentAssignments($studentid, $CurrentTerm->term, $CurrentTerm->year);

        $this->view->recentassignments = $result->toArray();
    }

    public function exemptgradedAction()
    {
    	$this->getHelper('layout')->disableLayout();
    	$this->view->ins = $this->_request->getParam('instructor');
    }


}

































