<?php

class Content_CommentsController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        //action body
        //$this->getHelper('layout')->disableLayout();
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
        	$subjects = Content_Model_Course::getInstructorCourses($identity->cl_IID);

        	if(!$classes && $identity->role >= 100)
        		$classes = array_values(Custom_Grades::getPrimaryLevels());
        	else if (!$classes&& $identity->role == 50){
        		$this->view->allow = false;
        		return ('No records for this instructor');
        	}

        	sort($classes, SORT_ASC);

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
                $buttons .= "<button id=\"btnfirst\" onclick=\"$.fn.filterComments($subject->cl_courseid,$('#g').text())\" type=\"button\" title=\"$subject->cl_coursename\" class=\"btn btn-info btn-small\">$st...</button>";
            else
                $buttons .= "<button onclick=\"$.fn.filterComments($subject->cl_courseid,$('#g').text())\" type=\"button\" title=\"$subject->cl_coursename\" class=\"btn btn-info btn-small\">$st...</button>";
            $first += 1;
        };

        $buttons .=  '</div>';

        $this->view->classes = $classes;
        $this->view->subButtons = $buttons;
        $this->view->firstcourse = $x;

        $this->view->current = Content_Model_School::isCurrentPeriod($currentterm->term, $currentterm->year);
    }

    public function commentSourceAction()
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

    			if (!($examscore->cl_examid == null)){ //because subject is probably not examinable
	    			$anchor = "<a href=\"#\"
	    						rel=\"popover\" data-title=\"".$examscore->fullname."\" data-placement=\"left\"
	    						data-trigger=\"hover\" data-html=\"true\" data-content=\"$examscore->cl_comment\"
	    						onclick='$.fn.loadComments($examscore->cl_examid,\"Comment : $examscore->fullname\",\"$examscore->cl_GPSN_ID\"); return false;'>Update
	    					   </a>";
    			}else{
    				$anchor = "<a href=\"#\"
	    						rel=\"popover\" data-title=\"".$examscore->fullname."\" data-placement=\"left\"
    					    	data-trigger=\"hover\" data-html=\"true\" data-content=\"No comment provided yet\"
    					    	onclick='$.fn.loadComments(-1,\"Comment : $examscore->fullname\",\"$examscore->cl_GPSN_ID\"); return false;'>Update
    					    	</a>";
    			}
    			//$anchor = $examscore->cl_comment;

    			$row = array();
    			$row[] = $examscore->cl_GPSN_ID;
    			$row[] = ($examscore->exammark == null && $examscore->exempt == 0) ? "<strong style='color : blue'>$examscore->fullname</strong>" :
    			(($examscore->exammark == 0 && $examscore->exempt == 0) ? "<strong style='color : maroon'>$examscore->fullname</strong>" :
    					(($examscore->exempt == 1) ? "<strong style='color : green'>$examscore->fullname</strong>" : $examscore->fullname));
    			$row[] = $anchor;
    			$row[] = "<strong style='color:black'>$examscore->markedover</strong>";
    			$row[] = "<strong style='color:blue'>$examscore->exammark</strong>";
    			$row[] = ($examscore->exempt == 1) ? 'Yes' : 'No';
    			$mark = ($examscore->exammark != NULL || $examscore->exammark != 0 ) ? round(($examscore->exammark / $examscore->markedover )*100,2) : '-';
    			$row[] = "<span class='label label-mini label-inverse'>$mark</span>" ;
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

    public function loadCommentAction()
    {
    	$examid = $this->_request->getParam('data');
    	$commentObj = new Content_Model_Examinations();
    	$comment = $commentObj->addComment($examid, '', false);

    	$this->getHelper('layout')->disableLayout();
    	// $this->getHelper('viewRenderer')->setNoRender();
    	$this->view->comment = $comment['comment'];

    	if ($examid == -1){
    		$this->view->studentid = $this->_request->getParam('studentid');
    	}
    	else
    		$this->view->studentid = -1;

    	$this->view->examid = $examid;
    }

    public function updateCommentAction()
    {
        // action body
    	$this->getHelper('layout')->disableLayout();
    	$this->getHelper('viewRenderer')->setNoRender();

    	$examid = $this->_request->getParam('eid');
    	$cnt = $this->_request->getParam('cm');
    	$course = $this->_request->getParam('course');
    	$grade = $this->_request->getParam('grade');
    	$studentid = $this->_request->getParam('studentid');

    	if ($examid == -1){
    		$currentterm = Content_Model_School::getCurrentTermYear();
    		echo Content_Model_Examinations::updateNewComment($studentid, $course, $grade, $currentterm->term, $currentterm->year, $cnt);
    	}else
  			echo Content_Model_Examinations::updateComment($examid, $cnt);

    }

	public function loadClassCommentsAction(){
		$this->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender(true);

		$currentPeriod = Content_Model_School::getCurrentTermYear();

		$studentid = $this->_request->getParam('studentid');
		$grade = $this->_request->getParam('grade');
		$update = $this->_request->getParam('update');


		if (isset($update) && $update == 1 && Content_Model_Instructors::isClassTeacherForGrade($grade)){
			$comment = $this->_request->getParam('comment');
			Content_Model_Comments::updateClassTeacherComment($studentid, $grade, $currentPeriod->term, $currentPeriod->year, $comment);

			echo ($comment);
		}else{
			header('Content-Type: application/json');
			$ret = array('allow' =>Content_Model_Instructors::isClassTeacherForGrade($grade),
			'comment' => Content_Model_Comments::loadClassTeacherComment($studentid, $grade, $currentPeriod->term, $currentPeriod->year),
			'picture' => (file_exists("./data/student_pixs/$studentid.jpg")? '/data/student_pixs/'.$studentid.'.jpg': 'http://www.placehold.it/80x80/EFEFEF/AAAAAA')
			);
			echo json_encode($ret);
		}
	}

}







