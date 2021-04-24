<?php
class Content_StudentController extends Zend_Controller_Action {

	public function init() {
		/* Initialize action controller here */
		// Zend_Debug::dump($this->_request->getParams());
	}

	public function indexAction() {
		// action body
		$newstudent = $this->_request->getParam ( 'newstudent' );
		if ($newstudent == 1)
			$this->view->newstudent = true;
	}

	public function currentStudentsAction() {
		// action body
		$this->view->layout ()->disableLayout ();
		$this->getHelper ( 'viewRenderer' )->setNoRender ( true );

		$students = Content_Model_Students::listStudents (
							$this->_request->getParam ( 'iDisplayStart' ),
							$this->_request->getParam ( 'iDisplayLength' ),
							$this->_request->getParam ( 'sSearch' ),
							$this->_request->getParam ( 'iSortCol_0' ),
							$this->_request->getParam ( 'sSortDir_0' ),
							true,
							false );

		$output = array (
				"sEcho" => intval ( $this->_request->getParam ( 'sEcho' ) ),
				"iTotalDisplayRecords" => $students ['iDisplayRecords'],
				"iTotalRecords" => $students ['iTotalRecords'],
				"aaData" => array () );

		$grades = Content_Model_GradeLevels::gradelevels ();
		$grades = $grades + array (
				9999 => 'Graduated' );

		// transformation of the array
		foreach ( $students ['data'] as $student ) {
			$row = array ();
			$row [] = $student->cl_GPSN_ID;
			$row [] = $student->cl_FirstName;
			$row [] = $student->cl_LastName;
			$row [] = ($student->cl_Gender) ? 'Male' : 'Female';
			$row [] = $grades [$student->cl_GradeLevel];

			if (Zend_Auth::getInstance ()->getIdentity ()->role >= 100) {
				$row [] = "<a onclick='$.fn.updateStudentRecord($student->cl_GPSN_ID);'><span class='label label-info label-mini'>update</span></a> | " . "<a onclick='$.fn.toggleStudentStatus($student->cl_GPSN_ID,0);'><span class='label label-important label-mini'>withdraw</span></a>";
		} else if (Zend_Auth::getInstance ()->getIdentity ()->role == 40) {
			$row [] = "<a onclick='$.fn.bookAttendance($student->cl_GPSN_ID);'><span class='label label-info label-mini'>Book Attendance</span></a> ";
		} else {
			$row [] = '';
		}
		$output ['aaData'] [] = $row;
	}

	echo json_encode ( $output );
}

public function pastStudentsAction() {
	// action body
	$this->view->layout ()->disableLayout ();
	$this->getHelper ( 'viewRenderer' )->setNoRender ( true );

	$students = Content_Model_Students::listStudents (
						$this->_request->getParam ( 'iDisplayStart' ),
						$this->_request->getParam ( 'iDisplayLength' ),
						$this->_request->getParam ( 'sSearch' ),
						$this->_request->getParam ( 'iSortCol_0' ),
						$this->_request->getParam ( 'sSortDir_0' ),
						false );

	$output = array (
			"sEcho" => intval ( $this->_request->getParam ( 'sEcho' ) ),
			"iTotalDisplayRecords" => $students ['iDisplayRecords'],
			"iTotalRecords" => $students ['iTotalRecords'],
			"aaData" => array () );

	$grades = Content_Model_GradeLevels::gradelevels ();
	$grades = $grades + array (
			9999 => 'Graduated' );

	// transformation of the array
	foreach ( $students ['data'] as $student ) {
		$row = array ();
		$row [] = $student->cl_GPSN_ID;
		$row [] = $student->cl_FirstName;
		$row [] = $student->cl_LastName;
		$row [] = ($student->cl_Gender) ? 'Male' : 'Female';
		$row [] = $grades [$student->cl_GradeLevel];
		if (Zend_Auth::getInstance ()->getIdentity ()->role >= 100)
			$row [] = "<a onclick='$.fn.toggleStudentStatus($student->cl_GPSN_ID,1);'><span class='label label-success label-mini'>re-admit</span></a>";
		else
			$row [] = '';
		$output ['aaData'] [] = $row;
	}

	echo json_encode ( $output );
}

public function listpaststudentsAction() {
	// action body
	$this->getHelper ( 'layout' )->disableLayout ();
	$this->view->locked = $this->_request->getParam ( 'locked' );
}

public function listcurrentstudentsAction() {
	// action body
	$this->getHelper ( 'layout' )->disableLayout ();
	$this->view->locked = $this->_request->getParam ( 'locked' );
}

public function toggleStatusAction() {
	// action body
	$this->getHelper ( 'layout' )->disableLayout ();
	$this->getHelper ( 'viewRenderer' )->setNoRender ( true );

	Content_Model_Students::toggleStatus ( $this->_request->getParam ( 'studentid' ) );

	$this->getResponse ()->setHttpResponseCode ( 202 );
	$this->getResponse ()->sendHeaders ();
}

public function recordUpdateAction() {
	// action body
	$this->getHelper ( 'layout' )->disableLayout ();

	$form = new Content_Form_Student ();
	$this->view->state = 'New Student Entry';
	$this->view->sendState = 'new';
	$this->view->studentpix = Content_Model_Students::getStudentPicture ( null );

	if ($this->_request->isXMLHttpRequest () && $this->_request->getParam ( 'send' ) == 'new') {
		// saving new data
		if ($form->isValid ( $this->_request->getParams () )) {
			try {
				$newStudentID = Content_Model_Students::newStudent (
									$form->getValue ( 'cl_FirstName' ),
									$form->getValue ( 'cl_LastName' ),
									$form->getValue ( 'cl_DOB' ),
									$form->getValue ( 'cl_POB' ),
									$form->getValue ( 'cl_Gender' ),
									$form->getValue ( 'cl_GradeLevel' ),
									$form->getValue ( 'cl_ContactEmail' ),
									$form->getValue ( 'cl_ContactTel' ),
									$form->getValue ( 'billStudent' ),
									$this->_request->getParam ( 'cl_Resident' ),
									$this->_request->getParam ( 'cl_PrimaryContact' ) );
				//father relationship type = 1
				Content_Model_StudentRelations::appendRelationalData (
									$newStudentID,
									1,
									$this->_request->getParam ( 'father' ),
									$this->_request->getParam ( 'faddress' ),
									$this->_request->getParam ( 'ft1' ),
									$this->_request->getParam ( 'ft2' ),
									$this->_request->getParam ( 'femail' ) );

				// add mother information;relationtype = 2
				Content_Model_StudentRelations::appendRelationalData (
									$newStudentID,
									2,
									$this->_request->getParam ( 'mother' ),
									$this->_request->getParam ( 'maddress' ),
									$this->_request->getParam ( 'mt1' ),
									$this->_request->getParam ( 'mt2' ),
									$this->_request->getParam ( 'memail' ) );

				//guardian relationship type = 3
				if ($this->_request->getParam ( 'guardian' ) != '')
					Content_Model_StudentRelations::appendRelationalData (
										$newStudentID,
										3,
										$this->_request->getParam ( 'guardian' ),
										$this->_request->getParam ( 'gaddress' ),
										$this->_request->getParam ( 'gt1' ),
										$this->_request->getParam ( 'gt2' ),
										$this->_request->getParam ( 'gemail' ) );

				$this->getResponse ()->setHttpResponseCode ( 202 );
				$this->getResponse ()->sendHeaders ();
				die (
									json_encode (
														array (
																'status' => 1,
																'studentid' => $newStudentID ) ) );
			} catch ( Exception $e ) {
				Zend_Debug::dump ( $e->getMessage () );
			}
		}
	} else if ($this->_request->isXMLHttpRequest () && $this->_request->getParam ( 'send' ) == 'old') {
		$form->removeElement ( 'billStudent' );
		// updating of existing record
		if ($form->isValid ( $this->_request->getParams () )) {
			try {
				Content_Model_Students::updateStudent (
									$form->getValue ( 'cl_GPSN_ID' ),
									$form->getValue ( 'cl_FirstName' ),
									$form->getValue ( 'cl_LastName' ),
									$form->getValue ( 'cl_DOB' ),
									$form->getValue ( 'cl_POB' ),
									$form->getValue ( 'cl_Gender' ),
									$form->getValue ( 'cl_GradeLevel' ),
									$form->getValue ( 'cl_ContactEmail' ),
									$form->getValue ( 'cl_ContactTel' ),
									$this->_request->getParam ( 'cl_Resident' ),
									$this->_request->getParam ( 'cl_PrimaryContact' ) );

				Content_Model_StudentRelations::appendRelationalData (
									$form->getValue ( 'cl_GPSN_ID' ),
									1,
									$this->_request->getParam ( 'father' ),
									$this->_request->getParam ( 'faddress' ),
									$this->_request->getParam ( 'ft1' ),
									$this->_request->getParam ( 'ft2' ),
									$this->_request->getParam ( 'femail' ) );

				// add father information;
				Content_Model_StudentRelations::appendRelationalData (
									$form->getValue ( 'cl_GPSN_ID' ),
									2,
									$this->_request->getParam ( 'mother' ),
									$this->_request->getParam ( 'maddress' ),
									$this->_request->getParam ( 'mt1' ),
									$this->_request->getParam ( 'mt2' ),
									$this->_request->getParam ( 'memail' ) );

				if ($this->_request->getParam ( 'guardian' ) != '')
					Content_Model_StudentRelations::appendRelationalData (
										$form->getValue ( 'cl_GPSN_ID' ),
										3,
										$this->_request->getParam ( 'guardian' ),
										$this->_request->getParam ( 'gaddress' ),
										$this->_request->getParam ( 'gt1' ),
										$this->_request->getParam ( 'gt2' ),
										$this->_request->getParam ( 'gemail' ) );

				$this->getResponse ()->setHttpResponseCode ( 202 );
				$this->getResponse ()->sendHeaders ();
				die ( json_encode ( array (
						'status' => 1 ) ) );
			} catch ( Exception $e ) {
			}
		}
	} else if ($this->_request->isXMLHttpRequest () &&
		 $this->_request->getParam ( 'update' ) ==
		 'true') {
		$studentModel = new Content_Model_Students ();
		// $form->removeElement ( 'billStudent' );

		$id = $this->_request->getParam ( 'studentid' );
		$student = $studentModel->find ( $id )->current ();
		$form->populate ( $student->toArray () );
		$this->view->state = 'Student Record Update';
		$this->view->sendState = 'old';
		$this->view->studentpix = Content_Model_Students::getStudentPicture ( $id );
		// load relational data
		$this->view->relations = Content_Model_StudentRelations::getStudentRelations ( $id );

	}

	$this->view->form = $form;
}

public function oldStudentsAction() {
	// action body
	// action body
	$this->view->layout ()->disableLayout ();
	$this->getHelper ( 'viewRenderer' )->setNoRender ( true );

	$students = Content_Model_Students::listStudents (
						$this->_request->getParam ( 'iDisplayStart' ),
						$this->_request->getParam ( 'iDisplayLength' ),
						$this->_request->getParam ( 'sSearch' ),
						$this->_request->getParam ( 'iSortCol_0' ),
						$this->_request->getParam ( 'sSortDir_0' ),
						false,
						true );

	$output = array (
			"sEcho" => intval ( $this->_request->getParam ( 'sEcho' ) ),
			"iTotalDisplayRecords" => $students ['iDisplayRecords'],
			"iTotalRecords" => $students ['iTotalRecords'],
			"aaData" => array () );

	$grades = Content_Model_GradeLevels::gradelevels ();
	$grades = $grades + array (
			9999 => 'Graduated' );

	// transformation of the array
	foreach ( $students ['data'] as $student ) {
		$row = array ();
		$row [] = $student->cl_GPSN_ID;
		$row [] = $student->cl_FirstName;
		$row [] = $student->cl_LastName;
		$row [] = ($student->cl_Gender) ? 'Male' : 'Female';
		$row [] = $grades [$student->cl_GradeLevel];
		if (Zend_Auth::getInstance ()->getIdentity ()->role >= 100)
			$row [] = "<a onclick='$.fn.studentPreview($student->cl_GPSN_ID,1);'><span class='label label-success label-mini'>records</span></a>";
		else
			$row [] = '';
		$output ['aaData'] [] = $row;
	}

	echo json_encode ( $output );
}

public function listoldstudentsAction() {
	// action body
	$this->getHelper ( 'layout' )->disableLayout ();
	$this->view->locked = $this->_request->getParam ( 'locked' );
}

public function indexAlternateAction() {
	// action body
	$this->view->locked = true;
}

public function academicProfileAction() {
	// action body
	$identity = Zend_Auth::getInstance ()->getIdentity ();
	$this->view->allow = true;

	$currentPeriod = Content_Model_School::getCurrentTermYear ( false );

	if (! $currentPeriod)
		$this->_redirect ( '/notermerror' );

	$term = $currentPeriod->term;
	$year = $currentPeriod->year;
	$ins = $this->_request->getParam ( 'instructor' );

	$this->view->current = Content_Model_School::isCurrentPeriod ( $term, $year );

	if (isset ( $identity->CurrentContext )) {
		$this->view->archived = isset ( $identity->CurrentContext );

		if ($ins) {
			unset ( $identity );
			$identity = new stdClass ();
			$identity->cl_IID = $ins;
			$this->view->current = false;
		}

		$ss = Content_Model_Instructors::taughtPastGradesClasses ( $identity->cl_IID, $term, $year );
		$subjects = $ss ['classes'];
		$classes = $ss ['grades'];

		if (! $classes && $identity->role >= 100)
			$classes = array_values ( Custom_Grades::getPrimaryLevels () );
		elseif (! $classes && $identity->role == 50) {
			$this->view->allow = false;
			return ('No records for this instructor');
		}
		sort ( $classes, SORT_ASC );
	} else {

		if ($ins) {
			unset ( $identity );
			$identity = new stdClass ();
			$identity->cl_IID = $ins;
			$this->view->current = false;
		}

		$classes = Content_Model_Instructors::taughtGrades ( $identity->cl_IID );

		if (! $classes && $identity->role >= 100)
			$classes = array_values ( Custom_Grades::getPrimaryLevels () );
		elseif (! $classes && $identity->role == 50) {
			$this->view->allow = false;
			return 'No records for this instructor';
		}

		sort ( $classes, SORT_ASC );
		// $subjects = Content_Model_Course::getInstructorCourses($identity->cl_IID);
	}

	// $this->view->subjects = $subjects;

	// $s = $subjects->toArray();
	// $x = $s[0]['cl_courseid'];
	$firstGrade = intval ( $classes [0] );
	$buttons = "<div class=\"btn-group\" data-toggle=\"buttons-radio\">";
	$first = 0;

	$tmp = array ();
	/*
	 * foreach ($subjects as $subject) { if (in_array($subject->cl_courseid, $tmp)) continue; $tmp[] = $subject->cl_courseid; $st = substr($subject->cl_coursename, 0, 10); if ($first == 0) $buttons .= "<button id=\"btnfirst\" onclick=\"$.fn.filterAssignments($subject->cl_courseid,$('#g').text())\" type=\"button\" title=\"$subject->cl_coursename\" class=\"btn btn-info btn-small\">$st...</button>"; else $buttons .= "<button onclick=\"$.fn.filterAssignments($subject->cl_courseid,$('#g').text())\" type=\"button\" title=\"$subject->cl_coursename\" class=\"btn btn-info btn-small\">$st...</button>"; $first += 1; };
	 */

	unset ( $tmp );
	$buttons .= '</div>';

	$this->view->studentlist = Content_Model_Students::studentsinGradeNames ( $firstGrade );

	$this->view->classes = $classes;
	$this->view->subButtons = $buttons;
	// $this->view->firstcourse = $x;
	$this->view->courses = ($identity->role == 100) ? Content_Model_Course::getCourses () : Content_Model_Course::getInstructorCourses (
						$identity->cl_IID );
	$this->view->labels = "<label id='c' style='display:none'>0</label><label id='g' style='display:none'>$firstGrade</label>";
	$this->view->ins = $identity->cl_IID;
}

public function studentsListAction() {
	// action body
	$this->getHelper ( 'layout' )->disableLayout ();

	$gradelevel = $this->_request->getParam ( 'grade' );
	$fordashboard = $this->_request->getParam ( 'dash' );
	$this->view->fordashboard = $fordashboard;

	if ($fordashboard == 1) {
		$currentPeriod = Content_Model_School::getCurrentTermYear ();
		$this->view->schooldays = $currentPeriod->schooldays;
		$this->view->studentlists = Content_Model_Students::getStudentsInGrades (
							json_encode ( array (
									$gradelevel ) ),
							date ( 'Y-m-d', strtotime ( $currentPeriod->startDate ) ),
							date ( 'Y-m-d', strtotime ( $currentPeriod->endDate ) ) );
		$this->view->classteacher = Content_Model_Instructors::isClassTeacherForGrade (
							$gradelevel );
	} else
		$this->view->studentlists = Content_Model_Students::studentsinGradeNames ( $gradelevel );
}

public function bookAttendanceAction() {
	// action body
	if ($this->_request->isXMLHTTPRequest () && $this->_request->getParam ( 'post' ) == true) {
		$this->getHelper ( 'layout' )->disableLayout ();
		$this->getHelper ( 'viewRenderer' )->setNoRender ( true );

		$studentid = $this->_request->getParam ( 'studentid' );

		$response = Content_Model_Attendance::bookAttendance ( $studentid );

		header ( 'Content-type: application/json' );

		// Zend_Debug::dump(file_exists("./data/student_pixs/$studentid.jpg"));

		if ($response) {
			if (file_exists ( "./data/student_pixs/$studentid.jpg" )) {
				$imgbinary = fread (
									fopen ( "./data/student_pixs/$studentid.jpg", "r" ),
									filesize ( "./data/student_pixs/$studentid.jpg" ) );
				$picture = 'data:image/jpg;base64,' . base64_encode ( $imgbinary );
			} else {
				$picture = 'http://placehold.it/70x70';
			}
		} else {
			$picture = 'http://placehold.it/70x70';
		}

		if ($response == 0)
			echo json_encode (
								array (
										"count" => Content_Model_Attendance::dailyAttendance (
															date ( 'Y-m-d' ) ),
										"message" => "<strong style='color : red'>Student not found for ID : " . $studentid . "</strong>" ) );
		else if ($response [0] == 2)
			echo json_encode (
								array (
										"count" => Content_Model_Attendance::dailyAttendance (
															date ( 'Y-m-d' ) ),
										"message" => "<strong style='color : green; font-size:11px;'>Attendance already booked for : <br> </strong><strong style='color:black'>" . $response [1] . "</strong><br><img class='img-polaroid' width='70' src=\"$picture\" />" ) );
		else if (is_array ( $response )) {
			echo json_encode (
								array (
										"count" => Content_Model_Attendance::dailyAttendance (
															date ( 'Y-m-d' ) ),
										"message" => "<strong style='color : blue; font-size:11px;'>Attendance successfully booked for : <br> </strong> <strong style='color:black'> $response[1] </strong><br><img class='img-polaroid' width='70' src=\"$picture\" />" ) );
		}
	} else
		$this->view->count = Content_Model_Attendance::dailyAttendance ( date ( 'Y-m-d' ) );
}

public function attendanceHistoryAction() {
	// action body
	// loading the attendance history of attendance for the day
	$days = Content_Model_School::getWorkingDaysInWeek ( date ( 'Y-m-d' ) );

	$weekdays = array ();
	$weekdaysSQL = array ();
	$population = Content_Model_Students::getActiveStudentCount ();

	foreach ( $days as $weekday ) {
		// Zend_Debug::dump($weekday);
		$weekdays [] = $weekday->format ( "D, d-M" );
		$weekdaysSQL [] = $weekday->format ( "Y-m-d" );
	}

	// returning data
	$weekData = Content_Model_Attendance::WeeklyAttendanceSummary ( 10, $weekdaysSQL, '07:30' );
	$weekDistribution = Content_Model_Attendance::AttendanceDistribution (
						10,
						$weekdaysSQL,
						'07:30' );

	$data = array ();
	$absentees = array ();
	$timelost = array ();
	$distribution = array ();

	foreach ( $weekData as $row ) {
		$searchIndex = array_search ( $row->weekday, $weekdaysSQL );
		$data [$searchIndex] = intval ( $row->cnt );
		$timelost [$searchIndex] = intval ( $row->avgtimelost / 60 );
		$absentees [$searchIndex] = $population - $data [$searchIndex];
	}

	for($i = 0; $i < 5; $i ++) {
		if (! array_key_exists ( $i, $data )) {
			$data [$i] = 0;
			$timelost [$i] = 0;
			$absentees [$i] = intval ( $population );
		}
	}

	// distribution data
	foreach ( $weekDistribution as $dist ) {
		$distribution [] = array (
				'name' => Content_Model_GradeLevels::getGradeName ( $dist->gradelevel ),
				'y' => intval ( $dist->laters ),
				'gradeid' => $dist->gradelevel );
	}

	$distribution [0] ['selected'] = true;
	$distribution [0] ['sliced'] = true;

	ksort ( $data, SORT_ASC );
	ksort ( $absentees, SORT_ASC );
	ksort ( $timelost, SORT_ASC );

	$chart = new Highcharts_Highchart ();
	$chart->chart->renderTo = "container";
	$chart->chart->zoomType = 'xy';
	// $chart->chart->type = "column";

	$chart->title->text = "Daily Attendance Summary for the Week";
	$HOST = $_SERVER ['HTTP_HOST'];
	$chart->subtitle->text = "Source: $HOST";

	$chart->plotOptions->column->pointPadding = 0.2;
	$chart->plotOptions->column->borderWidth = 0;
	$chart->plotOptions->column->dataLabels = array (
			'enabled' => true,
			'style' => array (
					'font-weight' => 'normal',
					'color' => 'black' ) );
	// $chart->plotOptions->column->stacking = 'normal';

	$chart->xAxis->categories = $weekdays;
	$chart->xAxis->gridLineWidth = 1;

	// primary axis
	$chart->yAxis [] = array (
			'min' => 0,
			'title' => array (
					'text' => 'No. of students',
					'style' => array (
							'color' => 'blue',
							'font-size' => '12px',
							'font-weight' => 'normal' ) ),
			'minorTickInterval' => 'auto',
			'labels' => array (
					'formatter' => new Highcharts_HighchartJsExpr (
																	"function() {
											return this.value;}" ),
					'style' => array (
							'color' => 'blue' ) ),
			'plotLines' => array (
					array (
							'color' => 'blue',
							'dashStyle' => 'LongDashDotDot',
							'value' => $population,
							'width' => 2,
							'zIndex' => 4,
							'label' => array (
									'text' => 'Student Population',
									'style' => array (
											'color' => 'silver' ) ) ) ) ); // end of plotlines

	// secondary axiss
	$chart->yAxis [] = array (
			'min' => 0,
			'gridLineWidth' => 0,
			'minorTickInterval' => null,
			'title' => array (
					'text' => 'Late Arrivals (minutes)',
					'style' => array (
							'color' => '#89A54E',
							'font-size' => '12px',
							'font-weight' => 'normal' ) ),
			'type' => 'datetime',
			'labels' => array (
					'formatter' => new Highcharts_HighchartJsExpr (
																	"function() {return (parseInt(this.value)) }" ),
					'style' => array (
							'color' => '#89A54E' ) ),
			'plotLines' => array (
					array (
							'color' => 'green',
							'dashStyle' => 'shortdash',
							'value' => array_sum ( $timelost ) / 5,
							'width' => 2,
							'zIndex' => 5,
							'label' => array (
									'text' => 'Weekly Average Lateness',
									'style' => array (
											'color' => 'red' ),
									'align' => 'right' ) ) ), // end of plotlines
			'plotBands' => array (
					array ( // Light air
							'from' => 0,
							'to' => array_sum ( $timelost ) / 5,
							'color' => 'rgba(68, 170, 213, 0.1)',
							'label' => array (
									'style' => array (
											'color' => '#606060' ) ) ) ),
			'opposite' => true );

	$chart->legend->layout = "horizontal";
	$chart->legend->backgroundColor = "#FFFFFF";
	$chart->legend->align = "center";
	$chart->legend->verticalAlign = "bottom";
	$chart->legend->floating = false;
	$chart->legend->shadow = 3;

	/*
	 * $chart->tooltip->formatter = new Highcharts_HighchartJsExpr("function() { return '' + this.x +': '+ this.y +' students';}");
	 */

	$chart->series [] = array (
			'type' => 'column',
			'name' => "In attendance",
			'data' => $data,
			'dataLabels' => array (
					'enabled' => true,
					// 'rotation' => -90,
					'color' => 'black',
					'align' => 'right',
					'formatter' => new Highcharts_HighchartJsExpr (
																	"function() {
											return this.y;}" ),
					'style' => array (
							'fontSize' => '10px',
							'fontFamily' => 'Verdana, sans-serif' ) ) );

	$chart->series [] = array (
			'type' => 'column',
			'name' => "Absentees",
			'data' => $absentees,
			'dataLabels' => array (
					'enabled' => true,
					// 'rotation' => -90,
					'color' => 'black',

					'formatter' => new Highcharts_HighchartJsExpr (
																	"function() {
															return this.y;}" ),
					'style' => array (
							'fontSize' => '10px',
							'fontFamily' => 'Verdana, sans-serif' ) ) );

	$chart->series [] = array (
			'type' => 'spline',
			'name' => 'Average Lateness',
			'color' => '#89A54E',
			'yAxis' => 1,
			'dashStyle' => 'shortdot',
			'data' => $timelost );

	$this->chart->exporting = array (
			'url' => "http://$HOST/hcserver/",
			'enabled' => true );

	// chart2

	$distribution_chart = new Highcharts_Highchart ();
	$distribution_chart->chart->renderTo = "container_piechart";
	// $chart->chart->type = "column";
	$distribution_chart->title->text = "Late Arrivals Distribution for the Week";
	$distribution_chart->subtitle->text = "Click on any slice of pie for details";
	$distribution_chart->subtitle->style = array (
			'color' => 'blue' );

	$distribution_chart->tooltip = array (
			'pointFormat' => '{series.name}: <b>{point.percentage}%</b>',
			'percentageDecimals' => 1 );
	$distribution_chart->plotOptions->pie = array (
			'allowPointSelect' => true,
			'cursor' => 'pointer',
					 /* 'formatter' => new Highcharts_HighchartJsExpr("function() {
									return this.point.name +' : <strong>'+ this.percentage.toFixed(2) +'</strong> %';}") */

			);

	$distribution_chart->series [] = array (
			'type' => 'pie',
			'name' => 'Late Arrivals',
			'data' => $distribution,

			'showInLegend' => false,
			'dataLabels' => array (
					'enabled' => true ),
			'point' => array (
					'events' => array (
							'click' => new Highcharts_HighchartJsExpr (
																		"function(e) {
								var clicked = this;
									$.loadModal('/attendancedetail?gradeid='+this.gradeid,this.name + ' - Late arrival stats for the week');
								}" ),
							'mouseOver' => new Highcharts_HighchartJsExpr (
																			"function(e) {
								this.slice();
							}" ),
							'mouseOut' => new Highcharts_HighchartJsExpr (
																		"function(e) {
								this.slice();
							}" ) ) ) );

	$this->view->chart = $chart;
	$this->view->distribution_chart = $distribution_chart;
}

public function attendanceDetailAction() {
	// action body
	$this->getHelper ( 'layout' )->disableLayout ();

	$days = Content_Model_School::getWorkingDaysInWeek ( date ( 'Y-m-d' ) );

	$weekdays = array ();
	$weekdaysSQL = array ();

	foreach ( $days as $weekday ) {
		// Zend_Debug::dump($weekday);
		$weekdays [] = $weekday->format ( "d-m-Y" );
		$weekdaysSQL [] = $weekday->format ( "Y-m-d" );
	}

	// $this->_request->getParam('gradeid')
	$studentlist = Content_Model_Students::studentsinGradeNames (
						$this->_request->getParam ( 'gradeid' ) );
	$attendanceSheet = array ();

	foreach ( $studentlist as $student ) {
		$sheet = new stdClass ();
		$sheet->student = $student ['fullname'];

		$weekData = Content_Model_Attendance::AttendanceDetail (
							$student ['cl_GPSN_ID'],
							$weekdaysSQL,
							'07:30' );

		$daily = array ();
		$late = array ();

		foreach ( $weekData as $row ) {
			$searchIndex = array_search ( $row->weekday, $weekdaysSQL );
			$daily [$searchIndex] = intval ( $row->cnt );
			$late [$searchIndex] = intval ( $row->timelost / 60 );
		}

		for($i = 0; $i < 5; $i ++) {
			if (! array_key_exists ( $i, $daily )) {
				$daily [$i] = 0;
				$late [$i] = - 1;
			}
		}

		ksort ( $daily, SORT_ASC );
		ksort ( $late, SORT_ASC );

		$sheet->lateness = $late;

		$attendanceSheet [] = $sheet;
	}

	$this->view->attendanceSheet = $attendanceSheet;
	$this->view->weekdays = $weekdays;
}

public function academicProfileChartAction() {
	// action body
	$d = $this->_request->getParam ( 'dash' );
	if (! (isset ( $d ) && $d == 1))
		$this->getHelper ( 'layout' )->disableLayout ();

	$chart = new Highcharts_Highchart ();
	$chart->chart->renderTo = "container";
	$chart->chart->zoomType = 'xy';

	$chart->title->text = "Subject Performance Evaluation : " .
		 Content_Model_Course::getCourseName ( $this->_request->getParam ( 'courseid' ) );
	$HOST = $_SERVER ['HTTP_HOST'];
	$chart->subtitle->text = "Source: $HOST";

	$chart->plotOptions->column->pointPadding = 0.2;
	$chart->plotOptions->column->borderWidth = 0;
	$chart->plotOptions->column->dataLabels = array (
			'enabled' => true,
			'style' => array (
					'font-weight' => 'normal',
					'color' => 'black' ) );

	$chart->legend->layout = "horizontal";
	$chart->legend->backgroundColor = "#FFFFFF";
	$chart->legend->align = "center";
	$chart->legend->verticalAlign = "bottom";
	$chart->legend->floating = false;
	$chart->legend->shadow = 3;

	$ct = Content_Model_School::getCurrentTermYear ();
	$terms = Content_Model_School::getPastTermYearsArray ( TRUE );
	$timeline = array ();
	$termSQL = array ();
	$yearSQL = array ();

	$x = 1;

	foreach ( $terms as $term ) {
		$timeline [] = "Term " . $term ['term'] . ' <br/>' . $term ['year'];
		$termSQL [] = $term ['term'];
		$yearSQL [] = $term ['year'];
		if ($x == 5)
			break;
		else
			$x += 1;
	}

	$timeline [] = "Term " . $ct->term . ' <br/>' . $ct->year;
	$termSQL [] = $ct->term;
	$yearSQL [] = $ct->year;

	$chart->xAxis->categories = $timeline;
	$chart->xAxis->gridLineWidth = 1;

	$chart->yAxis = array (
			'min' => 0,
			'max' => 100,
			'title' => array (
					'text' => 'Grading Scale',
					'style' => array (
							'color' => 'blue',
							'font-size' => '12px',
							'font-weight' => 'normal' ) ),
			// 'minorTickInterval' => 'auto',
			'gridLineWidth' => 1,
			'labels' => array (
					'formatter' => new Highcharts_HighchartJsExpr (
																	"function() {
																	return this.value;}" ),
					'style' => array (
							'color' => 'blue' ) ),
			'plotBands' => array (
					array ( // Light air
							'from' => 0,
							'to' => 50,
							'color' => 'rgba(68, 170, 213, 0.1)',
							'label' => array (
									'style' => array (
											'color' => '#606060' ) ) ) ),
			'plotLines' => array (
					array (
							'color' => 'red',
							'dashStyle' => 'shortdash',
							'value' => 50,
							'width' => 2,
							'zIndex' => 1,
							'label' => array (
									'text' => '50%',
									'style' => array (
											'color' => 'red' ),
									'align' => 'right' ) ) ) ); // end of plotlines

	$performanceSheet = Content_Model_PreparedReport::loadReportDetail (
						$this->_request->getParam ( 'studentid' ),
						$this->_request->getParam ( 'courseid' ),
						0,
						$termSQL,
						$yearSQL );

	$grade = array ();
	$TCM = array ();
	$EXM = array ();
	$x = 0;

	foreach ( $termSQL as $t ) {
		$grade [] = 0;
		$TCM [] = 0;
		$EXM [] = 0;

		foreach ( $performanceSheet as $sheet ) {
			if ($t == $sheet->cl_term && $yearSQL [$x] == $sheet->cl_year) {

				$grade [$x] = round ( floatval ( $sheet->TM ) );
				$TCM [$x] = round ( floatval ( $sheet->TCM ) );
				$EXM [$x] = round ( floatval ( $sheet->EXM ) );
				break;
			}
		}
		if ($x == 5)
			break;
		else
			$x += 1;
	}

	$chart->series [] = array (
			'type' => 'column',
			'name' => '% Class Mark',
			'data' => $TCM );

	/*
	 * $chart->series[] = array( 'type' => 'spline', 'showInLegend'=> false, 'data' => $TCM );
	 */

	if (Content_Model_Course::isExaminable (
						$this->_request->getParam ( 'courseid' ),
						$this->_request->getParam ( 'gradeid' ) )) {
		$chart->series [] = array (
				'type' => 'column',
				'name' => '% Exams Mark',
				'data' => $EXM );

		/*
		 * $chart->series[] = array( 'type' => 'spline', 'showInLegend'=> false, 'dashStyle'=> 'DashDot', 'data' => $EXM );
		 */
	}
	;

	$chart->series [] = array (
			'type' => 'spline',
			'name' => "Term Grade",
			'dashStyle' => 'ShortDashDotDot',
			'lineWidth' => 3,
			// 'color' => 'green',
			'data' => $grade );

	$this->view->chart = $chart;
}

public function classStudentsListJsonAction() {
	// action body
	// should be an ajax request
	$this->getHelper ( 'layout' )->disableLayout ();
	$this->getHelper ( 'viewRenderer' )->setNoRender ( true );


	$gradelevel = $this->_request->getParam ( 'grade' );
	$term = $this->_request->getParam ( 'term' );
	$year = $this->_request->getParam ( 'year' );
	$bill = $this->_request->getParam('bill');
	$stream = $this->_request->getParam('stream');

	if ($bill != 999){
		if (Content_Model_School::isCurrentPeriod ( $term, $year ))
			$studentList = Content_Model_Students::studentsInGrade ( $gradelevel );
		else
			$studentList = Content_Model_AssignmentMarks::getPastAssignmentStudents (
								$gradelevel,
								null,
								$term,
								$year,
								- 1,
								false );
	}else {
		$studentList = Content_Model_Students::studentsInGrade ( $gradelevel, $stream );
	}


	$studentids = array ();

	foreach ( $studentList as $student ) {
		$studentids [] = $student->cl_GPSN_ID;
	}

	echo Zend_Json::encode ( $studentids );
}

public function additionalInfoAction() {
	// this action is for the appending of additional information;
	$infoType = $this->_request->getParam ( 'infotype' );

	switch ($infoType) {
		case 1 :
			// add parental data...
			;
			break;

		default :
			;
			break;
	}
}

public function uploadpictureAction() {
	// action body
	$this->getHelper ( 'layout' )->disableLayout ();
	$this->getHelper ( 'viewRenderer' )->setNoRender ( true );
	$newid = $this->_request->getParam ( 'studentid' );

	$adapter = new Zend_File_Transfer_Adapter_Http ();

	$destionation = APPLICATION_PATH . "/../php/data/student_pixs";

	// $adapter->setDestination($destionation);
	$filename = $newid . ".jpg";

	$adapter->addFilter (
						'Rename',
						array (
								'target' => "$destionation/$filename",
								'overwrite' => true ) );

	if (! $adapter->receive ()) {
		$messages = $adapter->getMessages ();
		echo implode ( "\n", $messages );
	}

	echo "Picture successfully uploaded";
}

public function studentdistroAction(){
	// loading the attendance history of attendance for the day

	$chart = new Highcharts_Highchart ();
	$chart->chart->renderTo = "container";
	$chart->chart->zoomType = 'xy';
	$chart->chart->backgroundColor = 'transparent';
	//$chart->chart->type = "spline";

	$chart->title->text = "School Capacity Outlook";
	$HOST = $_SERVER ['HTTP_HOST'];
	$chart->subtitle->text = "Source: $HOST";

	$chart->plotOptions->column->pointPadding = 0.2;
	$chart->plotOptions->column->borderWidth = 0;
	$chart->plotOptions->column->dataLabels = array (
			'enabled' => true,
			'style' => array ('font-weight' => 'normal','color' => 'black')
	);

	$gradedistro = Content_Model_School::getGradeDistribution();
	$gradelevels = array();
	$capacities = array();
	$population = array();

	$totalcapacity = 0;
	$totalpopulation = 0;
	foreach ($gradedistro as $distro) {
		$gradelevels[] = $distro->gradename;
		$capacities[]= intval($distro->capacity);
		$population[] = intval($distro->population);
		$totalcapacity += intval($distro->capacity);
		$totalpopulation += intval($distro->population);
	}

	$this->view->totalcapacity = $totalcapacity;
	$this->view->availablespace = $totalcapacity - $totalpopulation;

 	$chart->xAxis->categories = $gradelevels;
	$chart->xAxis->gridLineWidth = 1;
	$chart->xAxis->labels->rotation = -90;
	$chart->xAxis->labels->align = 'right';
	$chart->xAxis->labels->style = array (
			'fontSize' => '10px',
			'fontFamily' => 'Verdana, sans-serif',
			'color' => 'black');

	$chart->legend->layout = "horizontal";
	$chart->legend->backgroundColor = "#FFFFFF";
	$chart->legend->align = "center";
	$chart->legend->verticalAlign = "bottom";
	$chart->legend->floating = false;
	$chart->legend->shadow = 1;

	/*
	 * $chart->tooltip->formatter = new Highcharts_HighchartJsExpr("function() { return '' + this.x +': '+ this.y +' students';}");
	*/

	// primary axis
	$chart->yAxis [] = array (
			'min' => 0,
			'title' => array (
					'text' => 'No. of students',
					'style' => array (
							'color' => 'blue',
							'font-size' => '12px',
							'font-weight' => 'normal' ) ),
			'minorTickInterval' => 'auto',
			'labels' => array (
					'formatter' => new Highcharts_HighchartJsExpr (
										"function() {
											return this.value;}" ),
					'style' => array (
							'color' => 'blue' ) ),
			'plotLines' => array (
					array (
							'color' => 'blue',
							'dashStyle' => 'LongDashDotDot',
							'value' => 10,
							'width' => 2,
							'zIndex' => 4,
							'label' => array (
									'text' => 'Student Population',
									'style' => array (
											'color' => 'silver' ) ) ) ) ); // end of plotlines

	// secondary axiss
  	$chart->yAxis [] = array (
			'min' => 0,
			'gridLineWidth' => 0,
			'minorTickInterval' => null,
			'title' => array (
					'text' => 'Late Arrivals (minutes)',
					'style' => array (
							'color' => '#89A54E',
							'font-size' => '12px',
							'font-weight' => 'normal' ) ),
			'type' => 'datetime',
			'labels' => array (
					'formatter' => new Highcharts_HighchartJsExpr (
										"function() {return (parseInt(this.value)) }" ),
					'style' => array (
							'color' => '#89A54E' ) ),
					'opposite' => true );

	$chart->series [] = array (
			'type' => 'spline',
			'name' => "Capacities",
			'data' => $capacities,
			'dataLabels' => array (
					'enabled' => true,
					// 'rotation' => -90,
					'color' => 'black',
					'align' => 'right',
					'formatter' => new Highcharts_HighchartJsExpr (
										"function() {
											return this.y;}" ),
					'style' => array (
							'fontSize' => '10px',
							'fontFamily' => 'Verdana, sans-serif' ) ) );

	$chart->series [] = array (
			'type' => 'spline',
			'name' => "Class Population",
			'data' => $population,
			'dataLabels' => array (
					'enabled' => true,
					// 'rotation' => -90,
					'color' => 'black',

					'formatter' => new Highcharts_HighchartJsExpr (
										"function() {
															return this.y;}" ),
					'style' => array (
							'fontSize' => '10px',
							'fontFamily' => 'Verdana, sans-serif' ) ) );

	/* $chart->series [] = array (
			'type' => 'spline',
			'name' => 'Average Lateness',
			'color' => '#89A54E',
			'yAxis' => 1,
			'dashStyle' => 'shortdot',
			'data' => array(10,7,8,9,6) );
 	*/

	$this->chart->exporting = array (
			'url' => "http://$HOST/hcserver/",
			'enabled' => true );

	$this->view->chart = $chart;
 }

}

































