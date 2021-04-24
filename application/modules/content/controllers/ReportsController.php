<?php

class Content_ReportsController extends Zend_Controller_Action
{

	protected $_identifier;

    public function init()
    {
        /* Initialize action controller here */
    	$this->view->host = $_SERVER["HTTP_HOST"];
    	$this->view->pdfhost =  $_SERVER["HTTP_HOST"];
    	//$this->view->pdfhost =  $this->getFrontController()->getParam('pdfhost');
    	$this->view->mode =  $this->getFrontController()->getParam ('pdfmode');

    	if (Content_Model_School::getReportLayout() == 0){
			$this->_identifier = 'ges_standard';
    	}
    	else{
    		$this->_identifier = $this->_frontController->getInstance()->getParam('identifier');
    	}
    }

    public function indexAction()
    {
        // action body
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

    	//$s = $subjects->toArray();
    	//$x = $s[0]['cl_courseid'];
    	$firstGrade = intval($classes[0]);
    	$buttons =  "<label id='cc' style='display:none'>0</label><label id='gg' style='display:none'>$firstGrade</label><div class=\"btn-group\" data-toggle=\"buttons-radio\">";
    	$first = 0;

/*     	$tmp = array();
    	foreach ($subjects as $subject) {
    		if (in_array($subject->cl_courseid, $tmp))
    			continue;

    		$tmp[] = $subject->cl_courseid;
    		$st = substr($subject->cl_coursename, 0, 10);
    		if ($first == 0)
    			$buttons .= "<button id=\"btnfirst1\" onclick=\"$.fn.filterReport($subject->cl_courseid,$('#gg').text(),  $('#rStudents option:selected').val())\" type=\"button\" title=\"$subject->cl_coursename\" class=\"btn\">$st...</button>";
    		else
    			$buttons .= "<button onclick=\"$.fn.filterReport($subject->cl_courseid,$('#gg').text(),  $('#rStudents option:selected').val())\" type=\"button\" title=\"$subject->cl_coursename\" class=\"btn\">$st...</button>";
    		$first += 1;
    	}; */

    	unset($tmp);

    	$buttons .=  '</div>';

    	$this->view->host = $_SERVER["HTTP_HOST"];
    	$this->view->pdfhost =  $this->getFrontController()->getParam('pdfhost');
    	$this->view->classes = $classes;
    	$this->view->subButtons = $buttons;
    	$this->view->firstcourse = 0;
    	$this->view->locked = isset($identity->CurrentContext);
    	$this->view->term = $currentterm->term;
    	$this->view->year = $currentterm->year;
    	$this->view->online =  $this->getFrontController()->getParam ('online');
    }

    public function cumReportAction()
    {
        // action body
		$this->getHelper('layout')->disableLayout();

    	$reportModel = new Content_Model_Reports();

    	$id = $this->_request->getParam('id');
    	$term = $this->_request->getParam('term');
    	$year = $this->_request->getParam('year');
    	$grade = $this->_request->getParam('grade');

    	if ($id != null && $grade != null && $term != null && $year != null)
    	{

    		$report =  $reportModel->buildStudentReport($id, $term, $year, $grade);

    		$this->getHelper('layout')->disableLayout();
    		$this->view->report = $report;
    		$this->view->term = $term;
    		$this->view->year = $year;
    		$this->view->gradename = Custom_Grades::getGradeName($grade);
    		$this->view->grade = $grade;

    		$schoolconfig = Content_Model_School::loadConfigData();
    		$this->view->useCambridge = $schoolconfig['cambridge'];
    		$this->view->current = Content_Model_School::isCurrentPeriod($term, $year);
    		$this->view->classteacher = Content_Model_Instructors::findClassTeacher($grade);
    		$this->view->useTemplate = $this->_request->getParam('template');

    		if ($this->view->useTemplate){
    			try {
    				$this->renderScript("templates/$this->_identifier/cum-report.phtml");
    			} catch (Exception $e) {
    				$this->renderScript("templates/default/cum-report.phtml");

    			}
    		}


    	}
    }

    public function reportStudentsAction()
    {

		$this->getHelper('layout')->disableLayout();
		$grade = $this->_request->getParam('grade');
		$course = $this->_request->getParam('course');
		$term = $this->_request->getParam('term');
		$year = $this->_request->getParam('year');

		if (Content_Model_School::isCurrentPeriod($term, $year))
			$students = Content_Model_Students::studentsinGradeNames($grade);
		else
			$students = Content_Model_AssignmentMarks::getPastAssignmentStudents($grade, $course, $term, $year);

		$this->view->students = $students;
    }

    public function termReportAction()
    {
		$this->getHelper('layout')->disableLayout();

		$reportModel = new Content_Model_Reports();

    	$id = $this->_request->getParam('id');
    	$term = $this->_request->getParam('term');
    	$year = $this->_request->getParam('year');
    	$grade = $this->_request->getParam('grade');

    	if ($id != null && $grade != null && $term != null && $year != null)
    	{

    		$report =  $reportModel->buildStudentReport($id, $term, $year, $grade);

    		$this->view->report = $report;

    		$CurrentTerm = Content_Model_Attendance::getTermDates($term, $year);
    		$this->view->reportDate = $CurrentTerm->endDate;

    		$this->view->studentid = $id;
    		$this->view->studentname = Content_Model_Students::getStudentName($id);
    		$this->view->term = $term;
    		$this->view->year = $year;
    		$this->view->gradename = Custom_Grades::getGradeName($grade);
    		$this->view->grade = $grade;
    		$this->view->grading = Content_Model_Reports::gradingSystem();
    		$this->view->days =  $CurrentTerm->days;

			if (Content_Model_School::isCurrentPeriod($term, $year))
    			$this->view->rollcount = Content_Model_Students::getStudentInGradeCount(json_encode(array($grade)));
			else
				$this->view->rollcount = Content_Model_Students::getPastStudentsinGradeCountX(json_encode(array($grade)), $term, $year);

    		$schoolconfig = Content_Model_School::loadConfigData();
    		$this->view->examallocate = $schoolconfig['examallocate'];
    		$this->view->classallocate = $schoolconfig['classallocate'];
    		$this->view->useCambridge = $schoolconfig['cambridge'];

    		$this->view->attendcomment = '';
    		$this->view->classteacher = Content_Model_Instructors::findClassTeacher($grade);
    		$this->view->useTemplate = $this->_request->getParam('template');

    		$nextTerm = Content_Model_Attendance::getNextTerm();
    		if ($nextTerm != null){
    			$this->view->nextTermDate = date('jS F, Y',strtotime($nextTerm->cl_startdate));
    		}

    		if ($this->view->useTemplate){
    			try {
    				$this->renderScript("templates/$this->_identifier/term-report.phtml");
    			} catch (Exception $e) {
    				$this->renderScript("templates/default/term-report.phtml");
    			}
    		}
    	}

    }

    public function progressReportAction()
    {
        // action body
    	$reportModel = new Content_Model_Reports();
    	$this->getHelper('layout')->disableLayout();

    	$id = $this->_request->getParam('id');
    	$term = $this->_request->getParam('term');
    	$year = $this->_request->getParam('year');
    	$grade = $this->_request->getParam('grade');

    	if ($id != null && $grade != null && $term != null && $year != null)
    	{
    		$report =  $reportModel->buildStudentReport($id, $term, $year, $grade);
    		$this->view->report = $report;
    		$this->view->term = $term;
    		$this->view->year = $year;
    		$this->view->gradename = Custom_Grades::getGradeName($grade);
    		$this->view->grade = $grade;
    		$schoolconfig = Content_Model_School::loadConfigData();
    		$this->view->useCambridge = $schoolconfig['cambridge'];
    		$this->view->classteacher = Content_Model_Instructors::findClassTeacher($grade);
    		$this->view->useTemplate = $this->_request->getParam('template');
    		$this->view->current = Content_Model_School::isCurrentPeriod($term, $year);

    		if ($this->view->useTemplate){
    			try {
    				$this->renderScript("templates/$this->_identifier/progress-report.phtml");
    			} catch (Exception $e) {
    				$this->renderScript("templates/default/progress-report.phtml");
    			}
    		}
    	}
    }

    public function commentReportAction()
    {
        // action body
    	//extra student Mark and Grade
    	$reportModel = new Content_Model_Reports();
    	$this->getHelper('layout')->disableLayout();
    	//$this->renderScript("templates/cent.greport.phtml");

    	$id = $this->_request->getParam('id');
    	$term = $this->_request->getParam('term');
    	$year = $this->_request->getParam('year');
    	$grade = $this->_request->getParam('grade');

    	if ($id != null && $grade != null && $term != null && $year != null)
    	{
    		//$report =  $reportModel->buildStudentReport($id, $term, $year, $grade);
    		$commentreport = Content_Model_Reports::getCommentReport($id, $term, $year, $grade);

    		//$this->view->report = $report;
    		$this->view->comreport = $commentreport;
    		$this->view->term = $term;
    		$this->view->year = $year;
    		$this->view->studentname = Content_Model_Students::getStudentName($id);
    		$this->view->gradename = Custom_Grades::getGradeName($grade);
    		$this->view->grade = $grade;

    		$this->view->classteacher = Content_Model_Instructors::findClassTeacher($grade);

    		$comment = Content_Model_Attendance::loadAttendComment($id, $term, $year, $grade);

    		if($comment){
    			$this->view->atcomment = $comment->cl_attendcomment;
    			$this->view->prcomment = $comment->cl_prcomment;
    			$this->view->ctcomment = $comment->cl_ctcomment;
    		}
    		$this->view->useTemplate = $this->_request->getParam('template');


    		$currentterm = Content_Model_School::getCurrentTermYear(false);
    		$this->view->current = Content_Model_School::isCurrentPeriod($currentterm->term, $currentterm->year);
    		$this->view->classteacher = Zend_Auth::getInstance()->getIdentity()->cl_classteacher;

    		if ($this->view->useTemplate){
    			try {
    				$this->renderScript("templates/$this->_identifier/comment-report.phtml");
    			} catch (Exception $e) {
    				$this->renderScript("templates/default/comment-report.phtml");
    			}

    		}
    	}
    }

    public function cumJsonAction()
    {
    	// action body
    	//$this->getHelper('layout')->disableLayout();
    	$this->getHelper('viewRenderer')->setNoRender(true);

    	$reportModel = new Content_Model_Reports();

    	$id = $this->_request->getParam('id');
    	$term = $this->_request->getParam('term');
    	$year = $this->_request->getParam('year');
    	$grade = $this->_request->getParam('grade');

    	if ($id != null && $grade != null && $term != null && $year != null)
    	{

    		$report =  $reportModel->buildStudentReport($id, $term, $year, $grade);

    		$this->getHelper('layout')->disableLayout();
    		$this->view->report = $report;
    		$this->view->term = $term;
    		$this->view->year = $year;
    		$this->view->gradename = Custom_Grades::getGradeName($grade);
    		$this->view->grade = $grade;

    		$schoolconfig = Content_Model_School::loadConfigData();
    		$this->view->useCambridge = $schoolconfig['cambridge'];
    		$this->view->current = Content_Model_School::isCurrentPeriod($term, $year);
    		$this->view->classteacher = Content_Model_Instructors::findClassTeacher($grade);
    		$this->view->useTemplate = $this->_request->getParam('template');

    		$resultStructure = array(
    				'columns' => array(
    						'Subjects',
    						'Class Work',
    						'Home Work',
    						'Unit Test',
    						'Group Work',
    						'Project',
    						'Total Class',
    						'Exam',
    						'Term',
    						'Grade'),
    				'headersCount' => 2,
    				'classteacher' => $this->view->classteacher,
    				'gradelevel' => $this->view->gradename,
    				'columns2'=>array('','Mark','Mark','Mark','Mark','Mark','Mark','Mark','Mark',''),
    				'datacolumns' => array('cname','CWK','HWK','UNT','GPW','PRJ','TCM','EXM','TM','Letter'),
    				'colspans'=> array(1,1,1,1,1,1,1,1,1,1),
    				'metadata' => array('Term'=>$this->view->term, 'Year' => $this->view->year),
    				'data' => $report);

    		echo Zend_Json::encode($resultStructure);

    	}

    }

    public function termJsonAction()
    {
        // action body
    	$this->getHelper('viewRenderer')->setNoRender(true);

    	$reportModel = new Content_Model_Reports();

    	$id = $this->_request->getParam('id');
    	$term = $this->_request->getParam('term');
    	$year = $this->_request->getParam('year');
    	$grade = $this->_request->getParam('grade');

    	if ($id != null && $grade != null && $term != null && $year != null)
    	{
    		$this->getHelper('layout')->disableLayout();

    		$report =  $reportModel->buildStudentReport($id, $term, $year, $grade);

    		$this->view->report = $report;

    		$CurrentTerm = Content_Model_Attendance::getTermDates($term, $year);
    		$this->view->reportDate = $CurrentTerm->endDate;

    		$this->view->term = $term;
    		$this->view->year = $year;
    		$this->view->gradename = Custom_Grades::getGradeName($grade);
    		$this->view->grade = $grade;

    		$schoolconfig = Content_Model_School::loadConfigData();
    		$this->view->examallocate = $schoolconfig['examallocate'];
    		$this->view->classallocate = $schoolconfig['classallocate'];
    		$this->view->useCambridge = $schoolconfig['cambridge'];

    		$this->view->attendcomment = '';
    		$this->view->classteacher = Content_Model_Instructors::findClassTeacher($grade);
    		$this->view->useTemplate = $this->_request->getParam('template');

    		$resultStructure = array(
    				'columns' => array(
    						'Subjects',
    						'Class Mark',
    						'Exam Mark',
    						'Term Mark',
    						'Grade',
    						'Remark'),
    				'headersCount' => 1,
    				'classteacher' => $this->view->classteacher,
    				'gradelevel' => $this->view->gradename,
    				'columns2'=>array('','Mark','Mark','Mark','Mark','Mark','Mark','Mark','Mark',''),
    				'datacolumns' => array('cname','TCM','EXM','TM','Letter','Comment'),
    				'colspans'=> array(1,1,1,1,1,1),
    				'metadata' => array('Term'=>$this->view->term, 'Year' => $this->view->year),
    				'data' => $report);

    		echo Zend_Json::encode($resultStructure);
    	}
    }

    public function commentJsonAction()
    {
        // action body
    	//extra student Mark and Grade
    	$reportModel = new Content_Model_Reports();
    	$this->getHelper('layout')->disableLayout();
    	$this->getHelper('viewRenderer')->setNoRender(true);

    	$id = $this->_request->getParam('id');
    	$term = $this->_request->getParam('term');
    	$year = $this->_request->getParam('year');
    	$grade = $this->_request->getParam('grade');

    	if ($id != null && $grade != null && $term != null && $year != null)
    	{

    		$commentreport = Content_Model_Reports::getCommentReport($id, $term, $year, $grade);

    		$this->view->studentname = Content_Model_Students::getStudentName($id);
    		$this->view->comreport = $commentreport;
    		$this->view->term = $term;
    		$this->view->year = $year;
    		$this->view->gradename = Custom_Grades::getGradeName($grade);
    		$this->view->grade = $grade;

    		$this->view->classteacher = Content_Model_Instructors::findClassTeacher($grade);

    		$comment = Content_Model_Attendance::loadAttendComment($id, $term, $year, $grade);

    		if($comment){
    			$this->view->atcomment = $comment->cl_attendcomment;
    			$this->view->prcomment = $comment->cl_prcomment;
    			$this->view->ctcomment = $comment->cl_ctcomment;
    		}

    		echo Zend_Json::encode($commentreport);
    	}

    }

    public function progressJsonAction()
    {
    	$this->getHelper('viewRenderer')->setNoRender(true);

    	$reportModel = new Content_Model_Reports();

    	$id = $this->_request->getParam('id');
    	$term = $this->_request->getParam('term');
    	$year = $this->_request->getParam('year');
    	$grade = $this->_request->getParam('grade');

    	if ($id != null && $grade != null && $term != null && $year != null)
    	{
    		$report =  $reportModel->buildStudentReport($id, $term, $year, $grade, true);

    		$this->getHelper('layout')->disableLayout();
    		$this->view->report = $report;
    		$this->view->term = $term;
    		$this->view->year = $year;
    		$this->view->gradename = Custom_Grades::getGradeName($grade);
    		$this->view->grade = $grade;

    		$schoolconfig = Content_Model_School::loadConfigData();
    		$this->view->useCambridge = $schoolconfig['cambridge'];
    		$this->view->current = Content_Model_School::isCurrentPeriod($term, $year);
    		$this->view->classteacher = Content_Model_Instructors::findClassTeacher($grade);
    		$this->view->useTemplate = $this->_request->getParam('template');

    		$resultStructure = array(
    				'columns' => array(
    						'Subjects',
    						'Class Work',
    						'Home Work',
    						'Unit Test',
    						'Group Work',
    						'Project',
    						'Total',
    						'Potential'),
    				'headersCount' => 2,
    				'classteacher' => $this->view->classteacher,
    				'gradelevel' => $this->view->gradename,
    				'columns2'=>array('','Mark','Mark','Mark','Mark','Mark','Mark','Grade'),
    				'datacolumns' => array('cname','CWK','HWK','UNT','GPW','PRJ','TCM','Letter'),
    				'colspans'=> array(1,1,1,1,1,1,1,1),
    				'metadata' => array('Term'=>$this->view->term, 'Year' => $this->view->year),
    				'data' => $report);

    		echo Zend_Json::encode($resultStructure);
    	}
    }

    public function batchPrintAction()
    {
        // action body
		$identity = Zend_Auth::getInstance()->getIdentity();

    	if ($identity->cl_classteacher != -1 && $identity->role == 50){
    		$this->view->classes = array( $identity->cl_classteacher => Content_Model_GradeLevels::getGradeName($identity->cl_classteacher));
    	}else if ($identity->role == 100){
        	$this->view->classes = Content_Model_GradeLevels::gradelevels();
    	}else{
			$this->_forward('noauth','error','security');
    	}

        $this->view->reporttypes = array(0 => "Assessments Breakdown Report", 1=> "End of Term Report", 2=>"Student Progress Report", 3=>"Comments Report",/*  4=>'Attendance Report' */);
    	$this->view->terms = array(1=>"Term 1", 2=> "Term 2", 3=>"Term 3");
    	$this->view->years = Content_Model_School::getPastYearsArray(true);

    	$CurrentTerm = Content_Model_School::getCurrentTermYear(false);

    	if ($CurrentTerm){
    		$this->view->ct = $CurrentTerm->term;
    		$this->view->cy = $CurrentTerm->year;
    	}

    	$this->view->online =  $this->getFrontController()->getParam ('online');

    	$nextTerm = Content_Model_Attendance::getNextTerm();
    	if ($nextTerm != null){
    		$this->view->nextTermDate = date('jS F, Y',strtotime($nextTerm->cl_startdate));
    	}
    }


}

















