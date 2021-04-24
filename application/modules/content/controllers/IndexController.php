<?php
class Content_IndexController extends Zend_Controller_Action
{

    public function init()
    {
		$mobileContext = $this->_helper->getHelper ( 'MobileContext' );
		$mobileContext->addActionContext ( 'index' )->initContext ();
		$this->view->pdfhost =  $this->getFrontController()->getParam('pdfhost');
		$this->view->mode =  $this->getFrontController()->getParam ('pdfmode');
    }

    public function indexAction()
    {
		// action body
		$this->view->schoolname = Content_Model_School::getSchoolName ();
		$schoolconfig = Content_Model_School::loadConfigData ();
		if (isset($schoolconfig ['cambridge']))
			$this->view->useCambridge = $schoolconfig ['cambridge'];
		$this->view->schoolinfo = $schoolconfig;
		$this->view->studentno = Content_Model_Students::getActiveStudentCount ();
		$this->view->icount = Content_Model_Instructors::instructorCount();

		$hostarray = explode('.',$_SERVER['HTTP_HOST']);
		if ($hostarray[0] != 'www')
			$CurrentTerm = Content_Model_School::getCurrentTermYear ( false );

		$this->view->pdfhost = $this->getFrontController ()->getParam ( 'pdfhost' );

		if ($CurrentTerm) {
			$this->view->term = $CurrentTerm->term;
			$this->view->year = $CurrentTerm->year;
			$this->view->axCount = Content_Model_Assignments::getTotalAssignments ( - 99, $CurrentTerm->term, $CurrentTerm->year );
			$this->view->totalweeks = $CurrentTerm->weeks;
			$this->view->currentweek = Content_Model_School::getTermWeeks ( $CurrentTerm->startDateAdj, Zend_Date::now (), $CurrentTerm->endDate );
			$this->view->host = $_SERVER ["HTTP_HOST"];
		}

		if (Zend_Auth::getInstance ()->getIdentity () != null){
			$this->view->dashboard = (Zend_Auth::getInstance ()->getIdentity ()->role == 50 || Zend_Auth::getInstance ()->getIdentity ()->role == 100) ? 1 :
				((Zend_Auth::getInstance()->getIdentity()->role == 110) ? 2 : 0) ;
		}else
		{
			$this->renderScript('/index/index_login.phtml');
		}
    }

    public function makepdfAction()
    {
		$this->getHelper ( 'layout' )->disableLayout ();

		$url = urldecode ( $_REQUEST ['url'] );
		$cid = urldecode ( $_REQUEST ['cid'] );
		$grade = urldecode ( $_REQUEST ['grade'] );
		$t = urldecode ( $_REQUEST ['t'] );
		$instructor = urldecode ( $_REQUEST ['i'] );
		$term = urldecode ( $_REQUEST ['tm'] );
		$year = urldecode ( $_REQUEST ['yr'] );
		$check = urldecode($_REQUEST['check']);

		if (isset ( $_REQUEST ['sid'] ))
			$sid = urldecode ( $_REQUEST ['sid'] );

		if (isset ( $_REQUEST ['O'] ))
			$orient = urldecode ( $_REQUEST ['O'] );
		else
			$orient = 'L';

		$host = $_SERVER ["HTTP_HOST"];
		$phost = parse_url ( $url );

		// To prevent anyone else using your script to create their PDF files
		if (! ($host == $phost ['host'])) {
			die ( "Your domain is not authorized to access this resource" );
		}

		// using Zend_Http_Client for remote calls instantiate the client
		$client = new Zend_Http_Client ( $url, array (
				'keepalive' => true,
				'timeout' => 120
		) );

		// Do we have the cookies stored in our session?
		/*
		 * if (isset($_SESSION['cookiejar']) && $_SESSION['cookiejar'] instanceof Zend_Http_CookieJar) { $client->setCookieJar($_SESSION['cookiejar']); } else {
		 */
		// If we don't, authenticate and store cookies
		$client->setCookieJar ();
		$client->setUri ( "http://$host/auth" );
		$client->setParameterPost ( array (
				'username' => 'reportviewer',
				'password' => md5 ( 'eldag' )
		) );

		$client->request ( Zend_Http_Client::POST );

		// Now, clear parameters and set the URI to the original one
		// (note that the cookies that were set by the server are now
		// stored in the jar)
		$client->resetParameters ();
		$client->setUri ( $url );
		// }
		// retrieve response from remote

		$client->setParameterGet ( 'cid', $cid );
		$client->setParameterGet ( 'grade', $grade );
		$client->setParameterGet ( 't', $t );
		$client->setParameterGet ( 'i', $instructor );
		$client->setParameterGet ( 'term', $term );
		$client->setParameterGet ( 'year', $year );
		if (isset($check))
			$client->setParameterGet('check', $check);

		if (isset ( $_REQUEST ['sid'] )) {
			$client->setParameterGet ( 'id', $sid );
			$sname = Content_Model_Students::getStudentName ( $sid );
		}

		$client->setParameterGet ( 'template', true );

		$response = $client->request ( Zend_Http_Client::GET );
		$code = $response->getStatus ();
		if ($code == 200) {
			$html = $response->getBody ();
		} else
			$html = $response->getStatus () . " : " . $response->getMessage ();

			// Store cookies in session, for next page
		$_SESSION ['cookiejar'] = $client->getCookieJar ();

		if (isset ( $_REQUEST ['sid'] ))
			$file = $sname . "_" . mt_rand ( 1, 999999 ) . '.pdf';
		else
			$file = mt_rand ( 1, 999999 ) . '.pdf';

		$mpdf = new Mpdf_mPDF ( '' );

		if (! Content_Model_School::isCurrentPeriod ( $term, $year )) {
			$mpdf->SetWatermarkText ( 'ARCHIVE', 0.08 );
			$mpdf->showWatermarkText = true;
		}

		$mpdf->useSubstitutions = true; // optional - just as an example
		                                // $mpdf->SetHeader($url.'||Page {PAGENO}'); // optional - just as an example

		if ($t == 'RP') {
			$printer = Content_Model_School::getReceiptPrinter();
			if ($printer == 0){
			$mpdf->AddPageByArray ( array (
					'orientation' => 'P',
					'sheet-size' => array (
							72,
							150
					),
					'margin-left' => 0,
					'margin-right' => 0,
					'margin-top' => 20,
					'margin-header' => 0
				));
			}
			else {
				$mpdf->AddPageByArray ( array (
						'orientation' => 'P',
						'margin-top' => 18,
						'margin-header' => 4
				));
			}
			$mpdf->SetJS ( 'this.print()' );
		} else {
			if ($t == 3)
				$mpdf->SetTopMargin ( 15 );
			else if ($t == 0 || $t == 2 || $t == 1)
				$mpdf->SetTopMargin ( 40 );
			else
				$mpdf->SetTopMargin ( 30 );

			$mpdf->AddPageByArray ( array (
					'orientation' => $orient
			) );
		}

		$mpdf->CSSselectMedia = 'mpdf'; // assuming you used this in the document header
		$mpdf->setBasePath ( $url );
		$mpdf->SetDisplayMode ( 'fullpage' );
		$mpdf->SetDisplayPreferences ( '/ShowMenubar/ShowToolbar' );
		$mpdf->WriteHTML ( $html );
		$mpdf->Output ( "data/$file", 'F' );

		$this->view->reportname = "/data/$file";
    }

    public function viewpdfAction()
    {
		// action body
		$this->view->printURL = $this->_request->getParam ( 'url' );
    }

    public function validateformAction()
    {
		// action body
		$this->getHelper ( 'layout' )->disableLayout ();
		$this->getHelper ( 'viewRenderer' )->setNoRender ();

		// determine which type of form has been sent
		switch ($this->_request->getParam ( 'formName' )) {
			case 'frm_newinstructor' :
				$form = new Content_Form_NewInstructor ();
				break;

			case 'frm_assingadd' :
				$form = new Content_Form_ClassSubjectAssign ();
				break;

			case 'frm_newcourse' :
				$form = new Content_Form_NewCourse ();
				break;

			case 'frm_updatecourse' :
				$form = new Content_Form_UpdateCourse ();
				break;

			case 'frm_termdates' :
				$form = new Content_Form_TermDates ();
				break;

			case 'frm_schoolconfig' :
				$form = new Content_Form_SchoolConfig ();
				break;

			case 'frm_student' :
				$form = new Content_Form_Student ();
				break;

			case 'frm_storeitems' :
				$form = new Content_Form_UpdateItemStore ();
				break;

			case 'frm_feesgroup' :
				$form = new Content_Form_FeeGroupNew ();
				break;

			case 'frm_groupfeesU' :
				$form = new Content_Form_FeeGroupUpdate ();
				break;

			case 'frm_termbill' :
				$form = new Content_Form_TermBillUpdate ();
				break;

			case 'frm_discount' :
				$form = new Content_Form_Discount ();
				break;

			case 'frm_newassessment' :
				$form = new Content_Form_Assignment ();
				break;

			case 'frm_updatepass' :
				$form = new Content_Form_ChangePassword ();
				break;

			case 'frm_payfees' :
				$form = new Content_Form_PayFees ();
				break;

			case 'frm_adjustaccount' :
				$form = new Content_Form_AdjustAccount ();
				break;

			case 'frm_gradelevels' :
				$form = new Content_Form_GradeLevels ();
				break;

			case 'frm_syllabus' :
				$form = new Content_Form_Syllabus ();
				break;

			case 'frm_expenses' :
				$form = new Content_Form_Expenses();
				break;

			case 'frm_imprest' :
				$form = new Content_Form_Imprest();
				break;

			default :
				;
			break;
		}

		// unset($_POST['captcha']);
		$form->isValidPartial ( $_POST );
		$this->_helper->json ( $form->getMessages () );
    }

    public function aboutAction()
    {
		// action body
		$this->getHelper ( 'layout' )->disableLayout ();
    }

    public function examsControllerAction()
    {
		// action body
    }

    public function keepAliveAction()
    {
		// action body
		$this->getHelper ( 'layout' )->disableLayout ();
    }

    public function feedbackAction()
    {
		// action body
    }

    private function createZendPDF($html, $host, $filename, $orientation)
    {
		// $filename="sample1.pdf";
		$resultData = Zend_Json::decode ( $html );

		$abs_path = APPLICATION_PATH . "/../php/data/";

		$style_body = new Zend_Pdf_Style ();
		$style_body->setLineWidth ( 1 );

		$doc = new PDFTable_Pdf_Document ( $filename, $abs_path );

		$page = $doc->createPage ( $orientation );

		$table = new PDFTable_Pdf_Table ( array_sum ( $resultData ['colspans'] ) );

		/**
		 * HEADER*
		 */

		// print_r($html); exit;

		$cols = array ();
		$row = new PDFTable_Pdf_Table_HeaderRow ();

		for($i = 0; $i < count ( $resultData ['columns'] ); $i ++) {
			$col = new PDFTable_Pdf_Table_Column ();
			if ($resultData ['colspans'] [$i] > 1) {
				$col->setColspan ( $resultData ['colspans'] [$i] );
			}

			$col->setBorder ( PDFTable_Pdf::LEFT, $style_body );
			$col->setBorder ( PDFTable_Pdf::BOTTOM, $style_body );

			$col->setText ( $resultData ['columns'] [$i] );
			$cols [] = $col;
		}

		$row->setColumns ( $cols );
		$table->setHeader ( $row );

		if ($resultData ['headersCount'] == 2) {
			$row2 = new PDFTable_Pdf_Table_HeaderRow ();

			for($i = 0; $i < count ( $resultData ['columns2'] ); $i ++) {
				$col = new PDFTable_Pdf_Table_Column ();
				$col->setText ( $resultData ['columns2'] [$i] );
				$col->setBorder ( PDFTable_Pdf::LEFT, $style_body );
				$cols2 [] = $col;
			}

			$row2->setColumns ( $cols2 );
			$table->addRow ( $row2 );
		}

		/**
		 * BODY*
		 */

		for($i = 0; $i < count ( $resultData ['data'] ); $i ++) {
			$cols = array ();
			$row = new PDFTable_Pdf_Table_Row ();

			$row->setBorder ( PDFTable_Pdf::BOTTOM, $style_body );
			$row->setCellPadding ( PDFTable_Pdf::BOTTOM, 5 );
			$row->setCellPadding ( PDFTable_Pdf::TOP, 2 );

			for($x = 0; $x < count ( $resultData ['datacolumns'] ); $x ++) {
				$col = new PDFTable_Pdf_Table_Column ();
				$col->setBorder ( PDFTable_Pdf::LEFT, $style_body );
				$col->setBorder ( PDFTable_Pdf::RIGHT, $style_body );

				$col->setText ( $resultData ['data'] [$i] [$resultData ['datacolumns'] [$x]] );
				if ($x != 0)
					$col->setAlignment ( PDFTable_Pdf::CENTER );
				$cols [] = $col;
			}

			$row->setColumns ( $cols );
			$table->addRow ( $row );
		}

		$page->addTable ( $table, 0, 0 );
		$page->setStyle ( $style_body );

		$doc->addPage ( $page );
		$doc->save ();

		return "http://$host/download?file=$filename";
    }

    public function downloadAction()
    {
		// action body
		$this->getHelper ( 'layout' )->disableLayout ();
		$this->getHelper ( 'viewRenderer' )->setNoRender ( true );

		// action body
		$req = $this->_request->getParam ( 'file' );

		if (isset ( $req )) {
			$filename = "./data/" . $req;

			if (file_exists ( $filename )) {
				header ( "Content-Type: application/pdf" );
				/*
				 * header("Pragma: public"); header("Expires: 0"); header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); header("Cache-Control: private", false); //sending download file header("Content-Type: application/octet-stream"); //application/octet-stream is more generic it works because in now days browsers are able to detect file anyway header("Content-Disposition: attachment; filename=\"" . basename($filename) . "\""); //ok header("Content-Transfer-Encoding: binary"); header("Content-Length: " . filesize($filename)); //ok readfile($filename);
				 */

				readfile ( $filename );
			} else {
				echo "File does not exist";
			}
		} else
			echo "Invalid parameters provided";
    }

    public function progressJsonAction()
    {
		// action body
    }

    public function dashboardAction()
    {
		// action body
		$identity = Zend_Auth::getInstance ()->getIdentity ();
		$this->view->allow = true;

		$currentPeriod = Content_Model_School::getCurrentTermYear (false);

		if (!$currentPeriod)
			$this->_redirect('/notermerror');

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
				$identity->role = 50;
				$this->view->current = false;
			}

			$ss = Content_Model_Instructors::taughtPastGradesClasses ( $identity->cl_IID, $term, $year );
			$subjects = $ss ['classes'];
			$classes = $ss ['grades'];

			if (count ( $classes ) == 0 && $identity->role >= 100)
				$classes = array_values ( Custom_Grades::getPrimaryLevels () );
			else if ($identity->role != 50) {
				$this->view->allow = false;
				return ('No records for this instructor');
			}

			sort ( $classes, SORT_ASC );
		} else {

			if ($ins) {
				unset ( $identity );
				$identity = new stdClass ();
				$identity->role = 50;
				$identity->cl_IID = $ins;
				$this->view->current = false;
			}

			$classes = Content_Model_Instructors::taughtGrades ( $identity->cl_IID );

			if (! $classes && $identity->role >= 100)
				$classes = array_values ( Custom_Grades::getPrimaryLevels () );
			else if ($identity->role != 50) {
				$this->view->allow = false;
				return ('No records for this instructor');
			}

			sort ( $classes, SORT_ASC );
			$subjects = Content_Model_Course::getInstructorCourses ( $identity->cl_IID );
		}

		$this->view->subjects = $subjects;

		$firstGrade = intval ( $classes [0] );
		$first = 0;

		$this->view->classes = $classes;
		$this->view->ins = $identity->cl_IID;
		$this->view->isClassTeacher = (Zend_Auth::getInstance ()->getIdentity ()->cl_classteacher != - 1) ? true : false;

		$this->view->students = Content_Model_Students::getStudentsInGrades ( json_encode ( array (
				$firstGrade
		) ), date ( 'Y-m-d', strtotime ( $currentPeriod->startDate ) ), date ( 'Y-m-d', strtotime ( $currentPeriod->endDate ) ) );
		$this->view->labels = "<label id='c' style='display:none'>0</label><label id='g' style='display:none'>$firstGrade</label>";
		$this->view->schooldays = $currentPeriod->schooldays;

		$schoolconfig = Content_Model_School::loadConfigData ();
		$this->view->useCambridge = $schoolconfig ['cambridge'];
		$this->view->schoolinfo = $schoolconfig;
		$this->view->studentno = Content_Model_Students::getActiveStudentCount ();

		if ($currentPeriod) {
			$this->view->term = $currentPeriod->term;
			$this->view->year = $currentPeriod->year;
			$this->view->axCount = Content_Model_Assignments::getTotalAssignments ( - 99, $currentPeriod->term, $currentPeriod->year );
			$this->view->totalweeks = $currentPeriod->weeks;
			$this->view->currentweek = Content_Model_School::getTermWeeks ( $currentPeriod->startDateAdj, Zend_Date::now () );
			$this->view->host = $_SERVER ["HTTP_HOST"];
			$this->view->sd = strtotime ( $currentPeriod->startDate );
			$this->view->ed = strtotime ( $currentPeriod->endDate );
		}
    }

    public function availableReportsAction()
    {
		$this->getHelper ( 'layout' )->disableLayout ();
		$this->getHelper ( 'viewRenderer' )->setNoRender ( true );

		header ( "Access-Control-Allow-Origin: *" );

		$host = $this->_request->getParam ( 'host' );
		$h = $_SERVER ['HTTP_HOST'];

		$filelist = array ();

		Zend_Debug::dump('Available reports');

		// create a handler for the directory
		$directory = APPLICATION_PATH . "/../php/data/";
		$handler = opendir ( $directory );

		// open directory and walk through the filenames
		while ( $file = readdir ( $handler ) ) {
			// if file isn't this directory or its parent, add it to the results
			//if ($file != "." && $file != "..") {
				// check with regex that the file format is what we're expecting and not something else
				//if (preg_match ( '#^(Assessments.Breakdown_|EndOfTerm_|Progress_|Comments_)[^\s]*' . $host . '#', $file )) {
					// add to our file array for later use
					$filelist [] = $file;
				//}
			//}
		}

		$response = array ();

		foreach ( $filelist as $file ) {
			$r = array ();

			$name = str_getcsv ( $file, '_' );
			$y = intval ( $name [4] ) + 1;
			// $r['name'] = "<a href='"."http://$h/download?file=$file"."'>"."$name[0] Report - $name[2] for Term $name[3] $name[4]/$y </a>";//.;
			$r ['name'] = "<a href='" . "http://$h/download?file=$file" . "'>" . "$name[0] Report";
			$r ['date'] = date ( 'D, jS F Y H:i:s', filemtime ( $directory . $file ) );
			$r ['grade'] = $name [2];
			$r ['term'] = $name [3];
			$r ['year'] = "$name[4]/$y";

			$response [] = $r;
		}

		$ar = array (
				'aaData' => $response
		);
		echo json_encode ( $ar );
    }

    public function schoolCalendarAction()
    {
		// this action is responsible for the generation and monitoring of the events on the school calender
		// should allow school administrator to add events to the school calendar whilst at the same time allowing
		// the personalization of the calendar of every individual user of the system
		// ie. students, instructors and administrator...
		$currentPeriod = Content_Model_School::getCurrentTermYear ();
		$term = $currentPeriod->term; // current academic term
		$year = $currentPeriod->year; // curent academic year..
    }

    private function compilepdf($url, $classlist, $term, $year, $grade, $reporttype, $gradename)
    {
		set_time_limit ( 0 );

		$parsedhost = parse_url ( $url );
		$host = $parsedhost ['host'];
		$classlist = Zend_Json::decode ( $classlist );
		$compilation = '';

		$nyear = str_replace ( "/", "_", $year );

		$filelist = array ();

		switch ($reporttype) {
			case 0 :
				// get student list;
				$compilation = "Assessments.Breakdown_" . $host . "_" . $gradename . "_" . $term . "_" . $nyear . ".pdf";
				foreach ( $classlist as $student ) {
					$filelist [] = $this->localMakePdf ( "http://$host/cumjson", $student, $grade, $term, $year, 0, 'L' );
				}
				break;
			case 1 :
				// get student list;
				$compilation = "EndOfTerm_" . $host . "_" . $gradename . "_" . $term . "_" . $nyear . ".pdf";
				foreach ( $classlist as $student ) {
					$filelist [] = $this->localMakePdf ( "http://$host/termjson", $student, $grade, $term, $year, 1, 'P' );
				}
				break;
			case 2 :
				// get student list;
				$compilation = "Progress_" . $host . "_" . $gradename . "_" . $term . "_" . $nyear . ".pdf";
				foreach ( $classlist as $student ) {
					$filelist [] = $this->localMakePdf ( "http://$host/progressjson", $student, $grade, $term, $year, 2, 'L' );
				}
				break;
			case 3 :
				// get student list;
				$compilation = "Comments_" . $host . "_" . $gradename . "_" . $term . "_" . $nyear . ".pdf";
				foreach ( $classlist as $student ) {
					$filelist [] = $this->localMakePdf ( "http://$host/commentjson", $student, $grade, $term, $year, 3, 'P' );
				}
				break;
			default :
				;
				break;
		}

		// merge pdf
		$CompiledPDF = new Zend_Pdf ();

		$abs_path = APPLICATION_PATH . "/../php/data";

		$pdfMerged = new Zend_Pdf ();

		foreach ( $filelist as $file ) {

			// Load PDF Document
			$OldPdf = Zend_Pdf::load ( "$abs_path/$file" );

			// Clone each page and add to merged PDF
			for($i = 0; $i < sizeof ( $OldPdf->pages ); ++ $i) {
				$page = clone $OldPdf->pages [$i];
				$pdfMerged->pages [] = $page;
			}
		}

		// Save changes to PDF

		if (file_exists ( "$abs_path/$compilation" ))
			unlink ( "$abs_path/$compilation" );

		$pdfMerged->save ( "$abs_path/$compilation" );

		$h = $_SERVER ['HTTP_HOST'];

		// cleaning of files
		foreach ( $filelist as $file ) {
			unlink ( "$abs_path/$file" );
		}

		return "Report successfully generated and hosted at <a href='http://$h/download?file=$compilation'>$compilation</a>";
		;
    }

    private function localMakePdf($url, $studentid, $grade, $term, $year, $t, $O, $instructor)
    {
		set_time_limit ( 0 );

		$cid = 0;

		if (isset ( $_REQUEST ['sid'] ))
			$sid = urldecode ( $_REQUEST ['sid'] );

		if (isset ( $_REQUEST ['O'] ))
			$orient = urldecode ( $_REQUEST ['O'] );
		else
			$orient = 'L';

		$host = $_SERVER ["HTTP_HOST"];
		$phost = parse_url ( $url );

		// To prevent anyone else using your script to create their PDF files
		if (! ($host == $phost ['host'])) {
			die ( "Your domain is not authorized to access this resource" );
		}

		// using Zend_Http_Client for remote calls instantiate the client
		$client = new Zend_Http_Client ( $url, array (
				'keepalive' => true,
				'timeout' => 120
		) );

		// Do we have the cookies stored in our session?
		/*
		 * if (isset($_SESSION['cookiejar']) && $_SESSION['cookiejar'] instanceof Zend_Http_CookieJar) { $client->setCookieJar($_SESSION['cookiejar']); } else {
		*/
		// If we don't, authenticate and store cookies
		$client->setCookieJar ();
		$client->setUri ( "http://$host/auth" );
		$client->setParameterPost ( array (
				'username' => 'reportviewer',
				'password' => md5 ( 'eldag' )
		) );

								$client->request ( Zend_Http_Client::POST );

								// Now, clear parameters and set the URI to the original one
								// (note that the cookies that were set by the server are now
								// stored in the jar)
								$client->resetParameters ();
								$client->setUri ( $url );
								// }
								// retrieve response from remote

								$client->setParameterGet ( 'cid', $cid );
								$client->setParameterGet ( 'grade', $grade );
								$client->setParameterGet ( 't', $t );
								$client->setParameterGet ( 'i', $instructor );
								$client->setParameterGet ( 'term', $term );
								$client->setParameterGet ( 'year', $year );

								if (isset ( $_REQUEST ['sid'] )) {
								$client->setParameterGet ( 'id', $sid );
								$sname = Content_Model_Students::getStudentName ( $sid );
		}

		$client->setParameterGet ( 'template', true );

		$response = $client->request ( Zend_Http_Client::GET );
		$code = $response->getStatus ();
		if ($code == 200) {
		$html = $response->getBody ();
		} else
									$html = $response->getStatus () . " : " . $response->getMessage ();

								// Store cookies in session, for next page
		$_SESSION ['cookiejar'] = $client->getCookieJar ();

			if (isset ( $_REQUEST ['sid'] ))
						$file = $sname . "_" . mt_rand ( 1, 999999 ) . '.pdf';
			else
						$file = mt_rand ( 1, 999999 ) . '.pdf';

			$mpdf = new Mpdf_mPDF ( '' );

			if (! Content_Model_School::isCurrentPeriod ( $term, $year )) {
				$mpdf->SetWatermarkText ( 'ARCHIVE', 0.08 );
				$mpdf->showWatermarkText = true;
			}

		$mpdf->useSubstitutions = true; // optional - just as an example

		if ($t == 'RP') {
		$mpdf->AddPageByArray ( array (
					'orientation' => 'P',
					'sheet-size' => array (
									72,
									150
							),
					'margin-left' => 0,
					'margin-right' => 0,
					'margin-top' => 20,
					'margin-header' => 0
							) );
		$mpdf->SetJS ( 'this.print()' );
		} else {
			if ($t == 3)
				$mpdf->SetTopMargin ( 15 );
			else if ($t == 0 || $t == 2 || $t == 1)
				$mpdf->SetTopMargin ( 40 );
			else
				$mpdf->SetTopMargin ( 30 );

				$mpdf->AddPageByArray ( array (
											'orientation' => $orient
													) );
									}

				$mpdf->CSSselectMedia = 'mpdf'; // assuming you used this in the document header
				$mpdf->setBasePath ( $url );
				$mpdf->SetDisplayMode ( 'fullpage' );
				$mpdf->SetDisplayPreferences ( '/ShowMenubar/ShowToolbar' );
				$mpdf->WriteHTML ( $html );
				$mpdf->Output ( "data/$file", 'F' );

				return $file;
    }

    public function scheduleCompileReportsAction()
    {
		// action body
		$this->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender(true);

		$url = urldecode ( $_REQUEST ['url'] );
		$cid = urldecode ( $_REQUEST ['cid'] );
		$grade = urldecode ( $_REQUEST ['grade'] );
		$t = urldecode ( $_REQUEST ['t'] );
		$instructor = urldecode ( $_REQUEST ['i'] );
		$term = urldecode ( $_REQUEST ['tm'] );
		$year = urldecode ( $_REQUEST ['yr'] );
		$cc = urldecode ( $_REQUEST ['cc'] );
		$gname = urldecode ( $_REQUEST ['gn'] );
		$schedule = urldecode( $_REQUEST['online']);
		$bill = urldecode($_REQUEST['bill']);

		if (isset($schedule) && $schedule == 'true') {
			$host = $_SERVER['HTTP_HOST'];

			$joburi = "buildpdf?url=$url&sid=160205509&tm=$term&yr=$year&i=47&cid=0&grade=$grade&t=10&data=1&cc=$cc&gn=$gname&bill=$bill";

			$return = Pdfhost_Model_ScheduledJobs::addJob(
					mt_rand(501000, 50000000),
					$joburi,
					array(),
					array()) ;

			if ($return)
				echo "Batch Compilation of reports successfully scheduled, You shall receive a message when job has been completed.";
			else
				echo 'This job cannot be scheduled because it already exists in queue of jobs to be processed. Please check after 5 mins for completed job';
		}else {
			echo $this->_forward('makepdf','index', 'pdfhost',array(
				 'url' => $url,
				 'tm' => $term,
				 'yr' => $year,
				 'i' => 47,
				 'cid' => 0,
				 'grade' => $grade,
				 't' => 10,
				 'data' => 1,
				 'cc' => $cc,
				 'gname' => $gname,
				 'sid' => 160205509,
				 'bill' => $bill
			));
		}
    }

    public function receivebackupAction()
    {
        //action body
        //receive backup
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);

        $filename = $this->_request->getParam('filename');

        echo $filename;

        $adapter = new Zend_File_Transfer_Adapter_Http();

        $destionation = APPLICATION_PATH . "/../php/dbbackup";
		$t = time();

		if (file_exists("$destionation/$filename"))
			unlink("$destionation/$filename");
         $adapter->setDestination($destionation);
       // $adapter->addFilter('Rename',array('target'=> "$destionation/$filename"));

        if (!$adapter->receive()) {
        	$messages = $adapter->getMessages();
        	echo implode("\n", $messages);
        }

        $this->_response->setHttpResponseCode(200);
    }

    public function uploadbackupAction()
    {
        // action body
        //get backup and send to remote server.....
		//location is in backup...
    	$this->getHelper('layout')->disableLayout();
    	$this->getHelper('viewRenderer')->setNoRender(true);

    	$backuphost = $this->getFrontController()->getParam('backuphost');
    	$url = "http://$backuphost/rbackup";

    	$backupname = $this->_request->getParam('backup');

    	$client = new Zend_Http_Client ( $url, array (
    			'keepalive' => true,
    			'timeout' => 120
    	) );

    	$filename = "/backup/$backupname";

 		//file would have already been compressed so just encrypt...
		$encryptedfile = new Zend_Filter_File_Encrypt(
			    array('key'    => '37191423',
			    	  'vector' => 'myvector',
					  'adapter' => 'mcrypt'));

		$encryptedfile->setFilename("$filename.c");

		$efile = $encryptedfile->filter("$filename");

		if (file_exists("$filename"))
			unlink("$filename");

		$client->setFileUpload($efile, 'file');
		$client->setParameterPost('filename', str_replace(' ', '_', Content_Model_School::getSchoolName()."_".date('jS-M-Y')));

    	$response = $client->request (Zend_Http_Client::POST);

		if ($response->getStatus() == 200){
		/* 	 if (file_exists("$filename.c"))
				unlink("$filename.c");

			if (file_exists("$filename"))
				unlink("$filename"); */
		}

		$this->getResponse()->setHttpResponseCode(200);
		//add database backup logs to database
    }

    public function dbackupAction()
    {
        // action body
    	$this->getHelper ( 'layout' )->disableLayout ();
    	$this->getHelper ( 'viewRenderer' )->setNoRender ( true );

    	// action body
    	$req = $this->_request->getParam ( 'file' );

    	if (isset ( $req )) {
    		$filename = "./dbbackup/" . $req;

    		//decrypt file and serve it...
    		//file would have already been compressed so just encrypt...
    		$decryptedfile = new Zend_Filter_File_Decrypt(
    				array('key' => '37191423',
						  'vector' => 'myvector',
    						'adapter' => 'mcrypt'));

    		$decryptedfile->setFilename("/tmp/$req.tar.bz2");

    		$dfile = $decryptedfile->filter("$filename");


    		if (file_exists ( $dfile )) {
    			header ( "Content-Type: application/x-bzip2" );
    			header("Content-Disposition: attachment; filename=\"" . basename($dfile) . "\"");
    			header("Content-Transfer-Encoding: binary");
    			header("Content-Length: " . filesize($dfile));
    			readfile ( $dfile );
    			unlink($dfile);
    		} else {
    			echo "File does not exist";
    		}
    	} else
    		echo "Invalid parameters provided";
    }

    public function sseResponseAction()
    {

    }

    public function robotsAction(){
		$this->getHelper('layout')->disableLayout();

    }

    public function testAction(){

    }

}































