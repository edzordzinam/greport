<?php

class Content_ExamsController extends Zend_Controller_Action
{

    protected $config = null;

    protected $_identity = null;
    protected $_locked = false;

    public function init()
    {
        $auth = Zend_Auth::getInstance();
    	$this->_identity = $auth->getIdentity();
    	$this->_locked = isset($this->_identity->CurrentContext);
    }

    public function indexAction()
    {
    }

    public function enterMarksAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $identity = Zend_Auth::getInstance()->getIdentity();
        $this->view->allow = true;
        // action body
        //$this->getHelper('layout')->disableLayout();
/*         $identity = Zend_Auth::getInstance()->getIdentity();

        $classes = Content_Model_Instructors::taughtGrades($identity->cl_IID);

        sort($classes, SORT_ASC);

        $subjects = Content_Model_Course::getInstructorCourses($identity->cl_IID);
        $this->view->subjects = $subjects; */


        if (isset($identity->CurrentContext)){
        	$this->view->archived = isset($identity->CurrentContext);
        	$ss = Content_Model_Instructors::taughtPastGradesClasses($identity->cl_IID, $identity->CurrentContext['Term'], $identity->CurrentContext['Year']);
        	$subjects = $ss['classes'];
        	$classes = $ss['grades'];

        	if(!$classes && $identity->role >= 100)
        		$classes = array_values(Custom_Grades::getPrimaryLevels());
        	elseif (!$classes&& $identity->role == 50){
        		$this->view->allow = false;
        		return ('No records for this instructor');
        	}

        	sort($classes, SORT_ASC);
        }
        else{
        	$classes = Content_Model_Instructors::taughtGrades($identity->cl_IID);

        	if(!$classes && $identity->role >= 100)
        		$classes = array_values(Custom_Grades::getPrimaryLevels());
        	elseif (!$classes&& $identity->role == 50){
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
                $buttons .= "<button id=\"btnfirst\" onclick=\"$.fn.filterExams($subject->cl_courseid,$('#g').text())\" type=\"button\" title=\"$subject->cl_coursename\" class=\"btn btn-info btn-small\">$st...</button>";
            else
                $buttons .= "<button onclick=\"$.fn.filterExams($subject->cl_courseid,$('#g').text())\" type=\"button\" title=\"$subject->cl_coursename\" class=\"btn btn-info btn-small\">$st...</button>";
            $first += 1;
        };

        $buttons .=  '</div>';

        $this->view->classes = $classes;
        $this->view->subButtons = $buttons;
        $this->view->firstcourse = $x;
        $this->view->locked = isset($identity->CurrentContext);

    }

    public function enterCommentsAction()
    {
    	// action body
    	$courseid = $this->_request->getParam('cc');
    	$gradeid = $this->_request->getParam('gc');
    	$year = $this->_identity->year;
    	$term = $this->_identity->term;

    	$examsModel = new Content_Model_Examinations();

    	$examsModel->initiatiateExamMarks($courseid, $gradeid, $term, $year);

    	$studentsexamlist = Content_Model_Examinations::getExamsList($term, $year, $gradeid, $courseid);

    	$this->view->term = $term;
    	$this->view->year = $year;
    	$this->view->gradename = Custom_Grades::getGradeName($gradeid);
    	$this->view->coursename = Content_Model_Course::getCourseName($courseid);
    	$this->view->students = $studentsexamlist;
    	$this->view->locked = Content_Model_Reports::locked($term, $year, $gradeid);
    }

    public function markOverAction()
    {
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        if ($this->_locked) return ;

    	$course = $this->_request->getParam('cid');
    	$grade = $this->_request->getParam('grade');

        $currentPeriod = Content_Model_School::getCurrentTermYear();

		$markover = trim($this->_request->getParam('value'));

		$result = Content_Model_Examinations::markedover($currentPeriod->term, $currentPeriod->year, $course, $grade, $markover, true);

		echo $result;
    }

    public function loadStudentSubjectCommentAction()
    {
    	$studentid = $this->_request->getParam('studentid');
    	$course = $this->_request->getParam('course');
    	$grade = $this->_request->getParam('grade');
    	$year = $this->_request->getParam('year');
    	$term = $this->_request->getParam('term');
    	$commentObj = new Content_Model_Examinations();
    	$comment = $commentObj->addComment($studentid, $term, $year, $course, $grade, '', false);

    	$this->getHelper('layout')->disableLayout();
    	$this->getHelper('viewRenderer')->setNoRender();
    	echo $comment;
    }

    public function exemptStudentAction()
    {
    	$this->getHelper('layout')->disableLayout();
    	$this->getHelper('viewRenderer')->setNoRender();
    	if ($this->_locked) return ;

    		$marksModel = new Content_Model_Examinations();

    		$currentPeriod = Content_Model_School::getCurrentTermYear();

    			$marksModel->addExempt(
    					$this->_request->getParam('student_id'),
    			        $currentPeriod->term,
    			        $currentPeriod->year,
    					$this->_request->getParam('cid'),
    					$this->_request->getParam('grade'),
    					$this->_request->getParam('value')
    			);

    			if ($this->_request->getParam('value')) echo 'Yes'; else echo 'No';
    }

    public function examsummaryAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $identity = Zend_Auth::getInstance()->getIdentity();
        $this->view->allow = true;
        $classes = Content_Model_Instructors::taughtGrades($identity->cl_IID, true);
        $subjects = Content_Model_Course::getInstructorCourses($identity->cl_IID);

        $currentPeriod = Content_Model_School::getCurrentTermYear();
        $term = $currentPeriod->term;
        $year = $currentPeriod->year;

        $this->view->assignedClasses = $classes;

        if(!$classes && $identity->role >= 100)
        	$classes = array_values(Custom_Grades::getPrimaryLevels());
        elseif (!$classes&& $identity->role == 50){
        	$this->view->allow = false;
        	return ('No records for this instructor');
        }

        $s = "";
        foreach ($subjects as $subject) {
            $st = substr($subject->cl_coursename, 0, 3);
            $s .= "<span class=\"label label-mini label-info\">
            <a href='#' title=\"$subject->cl_coursename\"
             rel=\"tooltip\">$st</a></span> | ";
        };

        $this->view->assignedSubs = $s;
        $this->view->totalStudents = Content_Model_Students::getStudentInGradeCount($classes);

    }

    public function examslistAction()
    {
        // action body
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

            $examscoreslist = Content_Model_Examinations::getExamScoreList(
                    $this->_request->getParam('iDisplayStart'),
                    $this->_request->getParam('iDisplayLength'),
                    $this->_request->getParam('sSearch'),
                    $this->_request->getParam('iSortCol_0'),
                    $this->_request->getParam('sSortDir_0'),
                    $term,
                    $year,
                    $courseid,
                    $gradeid,
                    $gradeid
                );

        $output = array ("sEcho" => intval ($this->_request->getParam('sEcho')),
                "iTotalDisplayRecords" => $examscoreslist['iDisplayRecords'],
                "iTotalRecords" => $examscoreslist['iTotalRecords'],
                "aaData" => array () );


         foreach ($examscoreslist['data'] as $examscore) {
                     $row = array();
                     $row[] = $examscore->cl_GPSN_ID;
                     $row[] = ($examscore->exammark == null && $examscore->exempt == 0) ? "<strong style='color : blue'>$examscore->fullname</strong>" :
                                (($examscore->exammark == 0 && $examscore->exempt == 0) ? "<strong style='color : maroon'>$examscore->fullname</strong>" :
                                (($examscore->exempt == 1) ? "<strong style='color : green'>$examscore->fullname</strong>" : $examscore->fullname));
                     $row[] = "<strong style='color:black'>$examscore->markedover</strong>";
                     $row[] = "<strong style='color:blue'>$examscore->exammark</strong>";
                     $row[] = ($examscore->exempt == 1) ? 'Yes' : 'No';
                     if ($examscore->markedover != 0)
                     	$mark = ($examscore->exammark != NULL || $examscore->exammark != 0 && $examscore->markedover != 0) ? round(($examscore->exammark / $examscore->markedover )*100,2) : '-';
                     else
                     	$mark = '-';
                     //$mark = ($examscore->exammark != NULL || $examscore->exammark != 0 ) ? round(($examscore->exammark / $examscore->markedover )*100,2) : '-';
                     $row[] = "<span class='label label-mini label-inverse'>$mark</span>";
                     //$gLetter = Content_Model_Examinations::getGradeLetter($mark);
                     //$row[] = "<strong style='color:maroon'>$gLetter</strong>";
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

    public function addExamMarkAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);
		if ($this->_locked) return ;

        $currentPeriod = Content_Model_School::getCurrentTermYear();

 	   		if ($this->_request->isXmlHttpRequest()){

 	   			preg_match_all('!\d+!', $this->_request->getParam('value'), $values);

 	   			if (isset($values[0][0])){
 	   				$value = $values[0][0];
 	   			}
 	   			else{
 	   				$value = 0;
 	   			}

 	   			$marksModel = new Content_Model_Examinations();
 	   			$markover = strip_tags($this->_request->getParam('markover'));

 	   			if ((floatval($value) <= $markover )
 	   					&& floatval($value) >= 0){
	 	   			$marksModel->addMark(
	 	   					$this->_request->getParam('student_id'),
	 	   					$currentPeriod->term,
	 	   					$currentPeriod->year,
	 	   					$this->_request->getParam('cid'),
	 	   					$this->_request->getParam('grade'),
	 	   					trim($value)
	 	   			);

	 	   			echo intval($value);
 	   			}
 	   			else
 	   				echo 'Invalid mark';
 	   		}
 	   		else {
 	   			throw new Zend_Exception("A non-ajax call was made to the server, pls contact administrator");
 	   		}

    }

    public function resetMarksAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);
        if ($this->_locked) return ;

        $course = $this->_request->getParam('course');
        $grade = $this->_request->getParam('grade');

        $currentPeriod = Content_Model_School::getCurrentTermYear();

        Content_Model_Examinations::resetExamMarks($course, $grade, $currentPeriod->term, $currentPeriod->year);
    }


}







