<?php
class Pdfhost_IndexController extends Zend_Controller_Action
{

    public function init()
    {
		/* Initialize action controller here */
    	$this->getFrontController()->setParam ('pdfmode', $this->_request->getParam('mode'));
    }

    public function indexAction()
    {
		//action body
    }

    public function makepdfAction()
    {
		//action body
    	set_time_limit(0);
		$this->getHelper ( 'layout' )->disableLayout ();
		//$this->getHelper('viewRenderer')->setNoRender(true);

		$stream = -100;

		header ( "Access-Control-Allow-Origin: *" );

		$url = urldecode ( $_REQUEST ['url'] );
		$cid = urldecode ( $_REQUEST ['cid'] );
		$grade = urldecode ( $_REQUEST ['grade'] );
		$t = urldecode ( $_REQUEST ['t'] );
		$instructor = urldecode ( $_REQUEST ['i'] );
		$term = urldecode ( $_REQUEST ['tm'] );
		$year = urldecode ( $_REQUEST ['yr'] );
		$bill = urldecode( $_REQUEST['bill']);
		if (isset($_REQUEST['stream']))
			$stream = urldecode ( $_REQUEST['stream']);

		if (isset ( $_REQUEST ['sid'] ))
			$sid = urldecode ( $_REQUEST ['sid'] );

/* 		if (isset ( $_REQUEST ['O'] ))
			$orient = Zend_Pdf_Page::SIZE_A4;//urldecode ( $_REQUEST ['O'] );
		else
			$orient = Zend_Pdf_Page::SIZE_A4_LANDSCAPE;//'L';
 */
		if (isset ( $_REQUEST ['O'] ))
			$orient = urldecode ( $_REQUEST ['O'] );
		else
			$orient = 'L';

		$host = $_SERVER ["HTTP_HOST"];
		$phost = parse_url ( $url );

		// To prevent anyone else using your script to create their PDF files
		// if (!($host == $phost['host'])) { die("Your domain is not authorized
		// to access this resource"); }

		// using Zend_Http_Client for remote calls instantiate the client
		$client = new Zend_Http_Client ( $url, array (
				'keepalive' => true,
				'timeout' => 300
		) );

		// Do we have the cookies stored in our session?

/* 		 if (isset($_SESSION['cookiejar']) && $_SESSION['cookiejar']
		 		instanceof Zend_Http_CookieJar) {
		  		$client->setCookieJar($_SESSION['cookiejar']); }
		 else { */

		// If we don't, authenticate and store cookies
				$client->setCookieJar ();
				$h = $phost ['host'];
				$client->setUri ( "http://$h/auth" );
				$client->setParameterPost ( array (
						'username' => 'reportviewer',
						'password' => md5 ( 'eldag' )
				));

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
		$client->setParameterGet ('bill', $bill);

		if (isset($stream) && ($stream == 1 || $stream == 0))
			$client->setParameterGet ('stream', $stream);


		if (isset ( $_REQUEST ['sid'] )) {
			$client->setParameterGet ( 'id', $sid );
			// $sname = Content_Model_Students::getStudentName($sid);
			$sname = 'greport';
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

		//if report type of a ....
    	switch ($t) {
    		case 0://cummulative report
    			if ( $this->getFrontController()->getParam ('pdfmode') != 'html')
    		 		$this->view->reportname = $this->createCumPDF($html, $h, $file, $orient);
    			else
    				$this->view->reportname = $this->mpdfFile($html, $url, $t, $orient, $file);
    		break;
    		case 1: //term report
    			if ( $this->getFrontController()->getParam ('pdfmode') != 'html')
    				$this->view->reportname = $this->createTermPDF($html, $h, $file, $orient);
    			else
    				$this->view->reportname = $this->mpdfFile($html, $url, $t, $orient, $file);
    		break;
    		case 2: //progress report
    			if ( $this->getFrontController()->getParam ('pdfmode') != 'html')
    				$this->view->reportname = $this->createProgressPDF($html, $h, $file, $orient);
    			else
    				$this->view->reportname = $this->mpdfFile($html, $url, $t, $orient, $file);
    		break;
    		case 3: //commentreport
    			if ( $this->getFrontController()->getParam ('pdfmode') != 'html')
    				$this->view->reportname = $this->createCommentPDF($html, $h, $file, $orient);
    			else
    				$this->view->reportname = $this->mpdfFile($html, $url, $t, $orient, $file);
    		break;
    		case 10:
    			$reporttype = urldecode($_REQUEST['cc']);
    			$gname = urldecode ( $_REQUEST ['gn'] );
    			$this->getHelper('viewRenderer')->setNoRender(true);
     			echo $this->compilepdf($url,$html, $term, $year, $grade, $reporttype,$gname, $instructor, $stream);
    		break;
    		case 11 : //accountstatement
    			if ( $this->getFrontController()->getParam ('pdfmode') != 'html')
    				$this->view->reportname = $this->createAccountPDF($html, $h, $file, $orient);
    			else
    				$this->view->reportname = $this->mpdfFile($html, $url, $t, $orient, $file);
    		break;
    		default:
    			;
    		break;
    	}
    }

    public function downloadAction()
    {

		$this->getHelper ( 'layout' )->disableLayout ();
		$this->getHelper ( 'viewRenderer' )->setNoRender ( true );
		$req = $this->_request->getParam ( 'file' );
		if (isset ( $req )) {
			$filename = "./data/" . $req;
			if (file_exists ( $filename )) {
				  header('Content-Disposition: attachment; filename="'.basename($filename).'"');
				  header('Content-Type:application/pdf');
				  header("Content-length:".(string)filesize($filename));
				  readfile($filename);
			} else {
				echo "File does not exist";
			}
		}
		else
			echo "Invalid parameters provided";
    }

    public function scheduleCompileReportsAction(){
    	// action body
    	$this->getHelper('layout')->disableLayout();
    	$this->getHelper('viewRenderer')->setNoRender(true);

    	header ( "Access-Control-Allow-Origin: *" );

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

    	$stream = -100;

    	if (isset($_REQUEST['stream']))
			$stream = urldecode ($_REQUEST['stream']);

    	if (isset($schedule) && $schedule == 'notyet') {
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
			 'bill' => $bill,
			 'stream' => $stream
			));
	     }

       }

    public function availableReportsAction(){
    	$this->getHelper('layout')->disableLayout();
    	$this->getHelper('viewRenderer')->setNoRender(true);

    	header ( "Access-Control-Allow-Origin: *" );

    	$host = $this->_request->getParam('host');
    	$bill = $this->_request->getParam('bills');
    	$h = $_SERVER['HTTP_HOST'];

    	$filelist = array();

    	// create a handler for the directory
    	$directory = APPLICATION_PATH . "/../php/data/";
    	$handler = opendir($directory);

    	// open directory and walk through the filenames
    	while ($file = readdir($handler)) {
    		// if file isn't this directory or its parent, add it to the results
    		if ($file != "." && $file != "..") {
    			// check with regex that the file format is what we're expecting and not something else
	    		if (!isset($bill) && $bill == null){
	    			if(preg_match('#^(Assessments.Breakdown_|End.Of.Term_|Progress_|Comments_)[^\s]*'.$host.'#', $file)) {
	    				$filelist[] = $file;
	    			}
	    		}else{
	    			if(preg_match('#^(Entry Bill_|Termly Bill_)[^\s]*'.$host.'#', $file)) {
	    				$filelist[] = $file;
	    			}
	    		}
    		}
    	}

		$response = array();

    	foreach ($filelist as $file) {
    		$r = array();

    		 $name = str_getcsv($file,'_');
    		 $y = intval($name[4])+1;
    		// $r['name'] = "<a href='"."http://$h/download?file=$file"."'>"."$name[0] Report - $name[2] for Term $name[3] $name[4]/$y </a>";//.;
    		 $r['name'] = "<a href='"."http://$h/download?file=$file"."'>"."$name[0] Report";
    		 $r['date'] = date('D, jS F Y H:i:s', filemtime($directory.$file)) ;
    		 $r['grade'] = $name[2];
    		 $r['term'] = $name[3];
    		 $r['year'] = "$name[4]/$y";

    		 $response[] = $r;
    	}

    	$ar = array('aaData'=>$response);
    	echo json_encode($ar);
    }

    /*	PRIVATE AND PROTECTED FUNCTIONS */
    private function createPDF($html, $t, $orient, $url, $remotehost, $file)
    {
		$mpdf = new Mpdf_mPDF ( '' );
		//$mpdf->useSubstitutions = true; // optional - just as an example
		//$mpdf->SetHeader($url.'||Page
		//{PAGENO}'); // optional - just as an
		//example

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

		$mpdf->CSSselectMedia = 'mpdf'; // assuming you used this in the document
		// header
		$mpdf->setBasePath ( $url );
		$mpdf->SetDisplayMode ( 'fullpage' );
		$mpdf->SetDisplayPreferences ( '/ShowMenubar/ShowToolbar' );

		set_time_limit(300);

		$mpdf->WriteHTML ( $html );
		$mpdf->Output ( "data/$file", 'F' );

		return "http://$remotehost/download?file=$file";
    }

    private function createDOMPDFWeb($html, $remotehost, $file)
    {
		$tmpfile = tempnam("./dompdf/data", "dompdf_");
		$dd = file_put_contents($tmpfile, $html); // Replace $smarty->fetch()
		// with your HTML string

		//Zend_Debug::dump($dd); exit;

		$url = "dompdf/dompdf.php?input_file=" . rawurlencode($tmpfile) .
		"&paper=letter&output_file=" . rawurlencode("My Fancy PDF.pdf");

		header("Location: http://" . $_SERVER["HTTP_HOST"] . "/$url");

    }

    private function createAccountPDF($html, $remotehost, $filename, $orientation, $local = false)
    {
		//$filename="sample1.pdf";
		$resultData = Zend_Json::decode($html);

		$abs_path=APPLICATION_PATH . "/../php/data/";

		$style_body=new Zend_Pdf_Style();
		$style_body->setLineWidth(1);

		$doc=new PDFTable_Pdf_Document($filename,$abs_path,null);

		$page=$doc->createPage($orientation);

		$table=new PDFTable_Pdf_Table(array_sum($resultData['colspans']));

		/**HEADER**/

		//print_r($html); exit;

		$cols=array();
		$row=new PDFTable_Pdf_Table_HeaderRow();

		for($i=0;$i<count($resultData['columns']);$i++){
			$col=new PDFTable_Pdf_Table_Column();
 			if ($resultData['colspans'][$i] > 1){
				$col->setColspan($resultData['colspans'][$i]);
			}

			$col->setBorder(PDFTable_Pdf::LEFT, $style_body);
 			$col->setBorder(PDFTable_Pdf::BOTTOM, $style_body);

			$col->setText($resultData['columns'][$i]);
			$col->setAlignment(PDFTable_Pdf::CENTER);
			$cols[]=$col;
		}

		$row->setColumns($cols);
		$table->setHeader($row);

		if ($resultData['headersCount'] == 2){
			$row2 =new PDFTable_Pdf_Table_HeaderRow();

			for($i=0;$i<count($resultData['columns2']);$i++){
				$col=new PDFTable_Pdf_Table_Column();
				$col->setText($resultData['columns2'][$i]);
				$col->setBorder(PDFTable_Pdf::LEFT, $style_body);
				$cols2[]=$col;
			}

			$row2->setColumns($cols2);
			$table->addRow($row2);
		}

		/**BODY**/

		for($i=0;$i<count($resultData['data']);$i++){
			$cols=array();
			$row=new PDFTable_Pdf_Table_Row();

			$row->setBorder(PDFTable_Pdf::BOTTOM,$style_body);
			$row->setCellPadding(PDFTable_Pdf::BOTTOM,5);
			$row->setCellPadding(PDFTable_Pdf::TOP,2);

			for ($x = 0; $x < count($resultData['datacolumns']); $x++) {
				$col=new PDFTable_Pdf_Table_Column();
				$col->setBorder(PDFTable_Pdf::LEFT, $style_body);
				$col->setBorder(PDFTable_Pdf::RIGHT, $style_body);

				$col->setText($resultData['data'][$i][$resultData['datacolumns'][$x]]);
				if ($x!=0)
					$col->setAlignment(PDFTable_Pdf::CENTER);
				$cols[]=$col;
			}

			$row->setColumns($cols);
			$table->addRow($row);
		}

		$page->addTable($table,0,0);
		$page->setStyle($style_body);

		$doc->addPage($page);
		$doc->save();

		$host = $_SERVER['HTTP_HOST'];

		if ($local)
			return $filename;
		else
			return "http://$host/download?file=$filename";

    }

	private function createCumPDF($html, $remotehost, $file, $orient, $local = false){
		//load template which is the same as the $hostname
		$template = APPLICATION_PATH . "/../templates/$remotehost/cummulative.pdf";
		$resultData = Zend_Json::decode($html);
		$studentName = $resultData['data'][0]['stdname'];

		$abs_path=APPLICATION_PATH . "/../php/data/";

		$style_body=new Zend_Pdf_Style();
		$style_body->setLineWidth(0);
		$style_body->setLineColor(Zend_Pdf_Color_Html::color('#0070C0'));
		$style_body->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 9);

		//loading of original template
		$pdf = Zend_Pdf::load($template);

		//extraction of the page to be used
		$extractor = new Zend_Pdf_Resource_Extractor();
		$clonedPage = $extractor->clonePage($pdf->pages[0]);

		//creating of the PDFTable Document in the .......
		$doc=new PDFTable_Pdf_Document($file,$abs_path, null);

		$page = $doc->createPage($clonedPage, array(80,60,20,60));

		$footerTable = new PDFTable_Pdf_Table(3);
		$fcol = new PDFTable_Pdf_Table_Column();
		$fcol->setText('1 of 1');
		$fcol->setAlignment(PDFTable_Pdf::LEFT);

		$fcol2 = new PDFTable_Pdf_Table_Column();
		$fcol2->setText($resultData['gradelevel']);
		$fcol2->setAlignment(PDFTable_Pdf::CENTER);


		$fcol3 = new PDFTable_Pdf_Table_Column();
		$fcol3->setText("Term ".$resultData['metadata']['Term']." of ".$resultData['metadata']['Year']);
		$fcol3->setAlignment(PDFTable_Pdf::RIGHT);

		$frow = new PDFTable_Pdf_Table_Row();
		$frow->setBorder(PDFTable_Pdf::TOP, $style_body);
		$frow->setCellPadding(PDFTable_Pdf::TOP,5);
		$frow->setColumns(array($fcol, $fcol2, $fcol3));

		$footerTable->setWidth(790);
		$footerTable->addRow($frow);

		$doc->setFooter($footerTable);

		$table=new PDFTable_Pdf_Table(array_sum($resultData['colspans']));

		/**HEADER**/

		//print_r($html); exit;

		$cols=array();
		$row=new PDFTable_Pdf_Table_HeaderRow();
		$row->setCellPaddings(array(5,0,5,0));
		$row->removeBorder(PDFTable_Pdf::BOTTOM);


		for($i=0;$i<count($resultData['columns']);$i++){
			$col=new PDFTable_Pdf_Table_Column();
			if ($resultData['colspans'][$i] > 1){
				$col->setColspan($resultData['colspans'][$i]);
			}

			$col->setText(strtoupper($resultData['columns'][$i]));
			$col->setBorder(PDFTable_Pdf::TOP, $style_body);
			$col->setBorder(PDFTable_Pdf::LEFT, $style_body);
			$col->setBorder(PDFTable_Pdf::RIGHT, $style_body);
			$col->setBackgroundColor(Zend_Pdf_Color_Html::color('#DFEEF3'));
			$col->setAlignment(PDFTable_Pdf::CENTER);

			$cols[]=$col;
		}

		$row->setColumns($cols);
		$table->setHeader($row);

		/* LEVEL 2 HEADER */
		if ($resultData['headersCount'] == 2){
			$row2 =new PDFTable_Pdf_Table_HeaderRow();
			$row2->setCellPaddings(array(0,5,5,0));
			$row->removeBorder(PDFTable_Pdf::TOP);


			for($i=0;$i<count($resultData['columns2']);$i++){
				$col=new PDFTable_Pdf_Table_Column();
				$col->setText(strtoupper($resultData['columns2'][$i]));
				$col->setBorder(PDFTable_Pdf::LEFT, $style_body);
				$col->setBorder(PDFTable_Pdf::RIGHT, $style_body);
				$col->setAlignment(PDFTable_Pdf::CENTER);
				$col->setBackgroundColor(Zend_Pdf_Color_Html::color('#DFEEF3'));

				$cols2[]=$col;
			}

			$row2->setColumns($cols2);
			$table->addRow($row2);
		}

		/**BODY**/

		$tfont  = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_ROMAN);

		/* $style2=new Zend_Pdf_Style();
		$style2->setLineWidth(0);
		$style2->setLineColor(Zend_Pdf_Color_Html::color('#0070C0'));
		 */
		for($i=0;$i<count($resultData['data']);$i++){
			$cols=array();
			$row=new PDFTable_Pdf_Table_Row();

			$row->setBorder(PDFTable_Pdf::BOTTOM,$style_body);
			$row->setCellPadding(PDFTable_Pdf::BOTTOM,5);
			$row->setCellPadding(PDFTable_Pdf::TOP,5);


			for ($x = 0; $x < count($resultData['datacolumns']); $x++) {
				$col=new PDFTable_Pdf_Table_Column();
				$col->setBorder(PDFTable_Pdf::LEFT, $style_body);
				$col->setBorder(PDFTable_Pdf::RIGHT, $style_body);
				$col->setBorder(PDFTable_Pdf::BOTTOM, $style_body);

				$col->setFont($tfont, 12);

				if ($i % 2 != 0){
					$col->setBackgroundColor(Zend_Pdf_Color_Html::color('#DFEEF3'));
					$col->setBorder(PDFTable_Pdf::TOP, $style_body);
				}

				if ($x == 0){
					$col->setWidth(130);
					$col->setPadding(PDFTable_Pdf::LEFT, 5);
				}
				/* else
					$col->setWidth(60); */

				$col->setText($resultData['data'][$i][$resultData['datacolumns'][$x]]);
				if ($x!=0)
					$col->setAlignment(PDFTable_Pdf::CENTER);
				$cols[]=$col;
			}

			$row->setColumns($cols);
			$table->addRow($row);

		}

		$itable = new PDFTable_Pdf_Table(2);
		$itable->setWidth(450);

		$itable = $this->addCummulativeInterpretation($itable);

		$page->addTable($table,0,0);
		$page->addTable($itable, 0, 310);

		$page->setFillColor(Zend_Pdf_Color_Html::color('maroon'));
		$page->drawText($studentName . " - ".$resultData['gradelevel'], 380, 73);

		$page->setFillColor(Zend_Pdf_Color_Html::color('black'));
		$page->drawText($resultData['classteacher'], 545, 50);

		$doc->addPage($page);
		$doc->save();

		$host = $_SERVER['HTTP_HOST'];

		if ($local)
			return $file;
		else
			return "http://$host/download?file=$file";
	}

	private function addCummulativeInterpretation(PDFTable_Pdf_Table $table){
		/* ADDING CUMMULATIVE INTERPRETATIONS */

		$tableContent = array('HEADINGS'=>array('CLASSWORK','HOMEWORK','UNIT TEST','GROUP WORK','PROJECT','TOTAL CLASS MARK'
							)
							, 'INTERPRETATION' => array('Sum of all class exercises',
									  'Sum of all homework assignments',
									  'Sum of marks from topic tests',
									  'Sum of marks of group work presented',
									  'Marks obtained from project work',
									  'The percentage of all class work, homework, group work, projects and unit tests'));

		$rows = array();
		$style = new Zend_Pdf_Style();
		$style->setLineWidth(0);
		$style->setFontSize(9);

		$style->setLineColor(Zend_Pdf_Color_Html::color('silver'));

		for ($i = -1; $i < count($tableContent['HEADINGS']); $i++) {

			if($i == -1 ){
				$iRow = new PDFTable_Pdf_Table_Row();
				$iRow->setCellPaddings(array(2,5,2,5));
				$iRow->setBorder(PDFTable_Pdf::LEFT, $style);
				$iRow->setBorder(PDFTable_Pdf::TOP, $style);
				$iRow->setBorder(PDFTable_Pdf::BOTTOM, $style);
				$iRow->setBorder(PDFTable_Pdf::RIGHT, $style);

				$icols = array();

				$icol1 = new PDFTable_Pdf_Table_Column();
				$icol1->setText('HEADINGS');
				$icol1->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD),9);

				$icol1->setBackgroundColor(Zend_Pdf_Color_Html::color('#DFEEF3'));


				$icol2 = new PDFTable_Pdf_Table_Column();
				$icol2->setText('INTERPRETATION');
				$icol2->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD),9);
				$icol2->setBackgroundColor(Zend_Pdf_Color_Html::color('#DFEEF3'));


			}else{

				$iRow = new PDFTable_Pdf_Table_Row();
				$iRow->setCellPaddings(array(2,5,2,5));
				$iRow->setBorder(PDFTable_Pdf::LEFT, $style);
				$iRow->setBorder(PDFTable_Pdf::TOP, $style);
				$iRow->setBorder(PDFTable_Pdf::BOTTOM, $style);
				$iRow->setBorder(PDFTable_Pdf::RIGHT, $style);

				$icols = array();

				$icol1 = new PDFTable_Pdf_Table_Column();
				$icol1->setText($tableContent['HEADINGS'][$i]);
				$icol1->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA),9);

				if ($i % 2 != 0)
					$icol1->setBackgroundColor(Zend_Pdf_Color_Html::color('#DFEEF3'));

				$icol2 = new PDFTable_Pdf_Table_Column();
				$icol2->setText($tableContent['INTERPRETATION'][$i]);
				$icol2->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA),9);

				if ($i % 2 != 0)
					$icol2->setBackgroundColor(Zend_Pdf_Color_Html::color('#DFEEF3'));
			}

			$icols[] = $icol1;
			$icols[] = $icol2;
			$iRow->setColumns($icols);

			$table->addRow($iRow);
		}

		return $table;
	}

	private function createTermPDF($html, $remotehost, $file, $orient, $local=false){
		//load template which is the same as the $hostname
		$template = APPLICATION_PATH . "/../templates/$remotehost/term.pdf";
		$resultData = Zend_Json::decode($html);
		$studentName = $resultData['data'][0]['stdname'];

		$abs_path=APPLICATION_PATH . "/../php/data/";

		$style_body=new Zend_Pdf_Style();
		$style_body->setLineWidth(0);
		$style_body->setLineColor(Zend_Pdf_Color_Html::color('#0070C0'));
		$style_body->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 9);

		//loading of original template
		$pdf = Zend_Pdf::load($template);

		//extraction of the page to be used
		$extractor = new Zend_Pdf_Resource_Extractor();
		$clonedPage = $extractor->clonePage($pdf->pages[0]);

		//creating of the PDFTable Document in the .......
		$doc=new PDFTable_Pdf_Document($file,$abs_path, null);

		$page = $doc->createPage($clonedPage, array(122,42,20,42));

		$footerTable = new PDFTable_Pdf_Table(3);
		$fcol = new PDFTable_Pdf_Table_Column();
		$fcol->setText('1 of 1');
		$fcol->setAlignment(PDFTable_Pdf::LEFT);

		$fcol2 = new PDFTable_Pdf_Table_Column();
		$fcol2->setText($resultData['gradelevel']);
		$fcol2->setAlignment(PDFTable_Pdf::CENTER);


		$fcol3 = new PDFTable_Pdf_Table_Column();
		$fcol3->setText("Term ".$resultData['metadata']['Term']." of ".$resultData['metadata']['Year']);
		$fcol3->setAlignment(PDFTable_Pdf::RIGHT);

		$frow = new PDFTable_Pdf_Table_Row();
		$frow->setBorder(PDFTable_Pdf::TOP, $style_body);
		$frow->setCellPadding(PDFTable_Pdf::TOP,5);
		$frow->setColumns(array($fcol, $fcol2, $fcol3));

		$footerTable->setWidth(500);
		$footerTable->addRow($frow);

		$doc->setFooter($footerTable);

		$table=new PDFTable_Pdf_Table(array_sum($resultData['colspans']));

		/**HEADER**/

		//print_r($html); exit;

		$cols=array();
		$row=new PDFTable_Pdf_Table_HeaderRow();
		$row->setCellPaddings(array(5,0,5,0));
		//$row->removeBorder(PDFTable_Pdf::BOTTOM);


		for($i=0;$i<count($resultData['columns']);$i++){
			$col=new PDFTable_Pdf_Table_Column();
			if ($resultData['colspans'][$i] > 1){
				$col->setColspan($resultData['colspans'][$i]);
			}

			$col->setText(strtoupper($resultData['columns'][$i]));
			$col->setBorder(PDFTable_Pdf::TOP, $style_body);
			$col->setBorder(PDFTable_Pdf::LEFT, $style_body);
			$col->setBorder(PDFTable_Pdf::RIGHT, $style_body);
			$col->setBackgroundColor(Zend_Pdf_Color_Html::color('#DFEEF3'));
			$col->setAlignment(PDFTable_Pdf::CENTER);

			$cols[]=$col;
		}

		$row->setColumns($cols);
		$table->setHeader($row);

		/* LEVEL 2 HEADER */
		if ($resultData['headersCount'] == 2){
			$row2 =new PDFTable_Pdf_Table_HeaderRow();
			$row2->setCellPaddings(array(0,5,5,0));
			$row->removeBorder(PDFTable_Pdf::TOP);


			for($i=0;$i<count($resultData['columns2']);$i++){
				$col=new PDFTable_Pdf_Table_Column();
				$col->setText(strtoupper($resultData['columns2'][$i]));
				$col->setBorder(PDFTable_Pdf::LEFT, $style_body);
				$col->setBorder(PDFTable_Pdf::RIGHT, $style_body);
				$col->setAlignment(PDFTable_Pdf::CENTER);
				$col->setBackgroundColor(Zend_Pdf_Color_Html::color('#DFEEF3'));

				$cols2[]=$col;
			}

			$row2->setColumns($cols2);
			$table->addRow($row2);
		}

		/**BODY**/

		$tfont  = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_ROMAN);

		/* $style2=new Zend_Pdf_Style();
		 $style2->setLineWidth(0);
		$style2->setLineColor(Zend_Pdf_Color_Html::color('#0070C0'));
		*/
		for($i=0;$i<count($resultData['data']);$i++){
			$cols=array();
			$row=new PDFTable_Pdf_Table_Row();

			$row->setBorder(PDFTable_Pdf::BOTTOM,$style_body);
			$row->setCellPadding(PDFTable_Pdf::BOTTOM,4);
			$row->setCellPadding(PDFTable_Pdf::TOP,4);


			for ($x = 0; $x < count($resultData['datacolumns']); $x++) {
				$col=new PDFTable_Pdf_Table_Column();
				$col->setBorder(PDFTable_Pdf::LEFT, $style_body);
				$col->setBorder(PDFTable_Pdf::RIGHT, $style_body);
				$col->setBorder(PDFTable_Pdf::BOTTOM, $style_body);

				$col->setFont($tfont, 12);

				if ($i % 2 != 0){
					$col->setBackgroundColor(Zend_Pdf_Color_Html::color('#DFEEF3'));
					$col->setBorder(PDFTable_Pdf::TOP, $style_body);
				}

				if ($x == 0){
					$col->setWidth(130);
					$col->setPadding(PDFTable_Pdf::LEFT, 5);
				}
				/* else
				 $col->setWidth(60); */

				$col->setText($resultData['data'][$i][$resultData['datacolumns'][$x]]);
				if ($x!=0)
					$col->setAlignment(PDFTable_Pdf::CENTER);

				if($x == count($resultData['datacolumns'])-1)
					$col->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC),9);

				$cols[]=$col;
			}

			$row->setColumns($cols);
			$table->addRow($row);

		}

		$itable = new PDFTable_Pdf_Table(2);
		$itable->setWidth(450);

		//$itable = $this->addCummulativeInterpretation($itable);

		$page->addTable($table,0,0);
		//$page->addTable($itable, 0, 310);

		$page->setFillColor(Zend_Pdf_Color_Html::color('black'));
		$page->drawText("Term ".$resultData['metadata']['Term'], 410, 42);

		$page->setFillColor(Zend_Pdf_Color_Html::color('black'));
		$page->drawText($resultData['metadata']['Year'], 90, 42);

		$page->setFillColor(Zend_Pdf_Color_Html::color('black'));
		$page->drawText($resultData['gradelevel'], 410, 78);

		$page->setFillColor(Zend_Pdf_Color_Html::color('maroon'));
		$page->drawText($studentName, 190, 112);

		$page->setFillColor(Zend_Pdf_Color_Html::color('black'));
		$page->drawText($resultData['classteacher'], 73, 78);

		$doc->addPage($page);
		$doc->save();

		$host = $_SERVER['HTTP_HOST'];
		if ($local)
			return $file;
		else
			return "http://$host/download?file=$file";
	}

	private function createProgressPDF($html, $remotehost, $file, $orient, $local = false){
		//load template which is the same as the $hostname
		$template = APPLICATION_PATH . "/../templates/$remotehost/progress.pdf";
		$resultData = Zend_Json::decode($html);
		$studentName = $resultData['data'][0]['stdname'];

		$abs_path=APPLICATION_PATH . "/../php/data/";

		$style_body=new Zend_Pdf_Style();
		$style_body->setLineWidth(0);
		$style_body->setLineColor(Zend_Pdf_Color_Html::color('#0070C0'));
		$style_body->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 9);

		//loading of original template
		$pdf = Zend_Pdf::load($template);

		//extraction of the page to be used
		$extractor = new Zend_Pdf_Resource_Extractor();
		$clonedPage = $extractor->clonePage($pdf->pages[0]);

		//creating of the PDFTable Document in the .......
		$doc=new PDFTable_Pdf_Document($file,$abs_path, null);

		$page = $doc->createPage($clonedPage, array(90,40,20,40));

		$footerTable = new PDFTable_Pdf_Table(3);
		$fcol = new PDFTable_Pdf_Table_Column();
		$fcol->setText('1 of 1');
		$fcol->setAlignment(PDFTable_Pdf::LEFT);

		$fcol2 = new PDFTable_Pdf_Table_Column();
		$fcol2->setText($resultData['gradelevel']);
		$fcol2->setAlignment(PDFTable_Pdf::CENTER);


		$fcol3 = new PDFTable_Pdf_Table_Column();
		$fcol3->setText("Supervisor: ".strtoupper($resultData['classteacher'])." | Term ".$resultData['metadata']['Term']." of ".$resultData['metadata']['Year']);
		$fcol3->setAlignment(PDFTable_Pdf::RIGHT);

		$frow = new PDFTable_Pdf_Table_Row();
		$frow->setBorder(PDFTable_Pdf::TOP, $style_body);
		$frow->setCellPadding(PDFTable_Pdf::TOP,5);
		$frow->setColumns(array($fcol, $fcol2, $fcol3));
		$frow->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD),9);

		$footerTable->setWidth(790);
		$footerTable->addRow($frow);

		$doc->setFooter($footerTable);

		$table=new PDFTable_Pdf_Table(array_sum($resultData['colspans']));

		/**HEADER**/

		//print_r($html); exit;

		$cols=array();
		$row=new PDFTable_Pdf_Table_HeaderRow();
		$row->setCellPaddings(array(5,0,5,0));
		$row->removeBorder(PDFTable_Pdf::BOTTOM);


		for($i=0;$i<count($resultData['columns']);$i++){
			$col=new PDFTable_Pdf_Table_Column();
			if ($resultData['colspans'][$i] > 1){
				$col->setColspan($resultData['colspans'][$i]);
			}

			$col->setText(strtoupper($resultData['columns'][$i]));
			$col->setBorder(PDFTable_Pdf::TOP, $style_body);
			$col->setBorder(PDFTable_Pdf::LEFT, $style_body);
			$col->setBorder(PDFTable_Pdf::RIGHT, $style_body);
			$col->setBackgroundColor(Zend_Pdf_Color_Html::color('#DFEEF3'));
			$col->setAlignment(PDFTable_Pdf::CENTER);

			$cols[]=$col;
		}

		$row->setColumns($cols);
		$table->setHeader($row);

		/* LEVEL 2 HEADER */
		if ($resultData['headersCount'] == 2){
			$row2 =new PDFTable_Pdf_Table_HeaderRow();
			$row2->setCellPaddings(array(0,5,5,0));
			$row->removeBorder(PDFTable_Pdf::TOP);


			for($i=0;$i<count($resultData['columns2']);$i++){
				$col=new PDFTable_Pdf_Table_Column();
				$col->setText(strtoupper($resultData['columns2'][$i]));
				$col->setBorder(PDFTable_Pdf::LEFT, $style_body);
				$col->setBorder(PDFTable_Pdf::RIGHT, $style_body);
				$col->setAlignment(PDFTable_Pdf::CENTER);
				$col->setBackgroundColor(Zend_Pdf_Color_Html::color('#DFEEF3'));

				$cols2[]=$col;
			}

			$row2->setColumns($cols2);
			$table->addRow($row2);
		}

		/**BODY**/

		$tfont  = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_ROMAN);

		/* $style2=new Zend_Pdf_Style();
		 $style2->setLineWidth(0);
		$style2->setLineColor(Zend_Pdf_Color_Html::color('#0070C0'));
		*/
		for($i=0;$i<count($resultData['data']);$i++){
			$cols=array();
			$row=new PDFTable_Pdf_Table_Row();

			$row->setBorder(PDFTable_Pdf::BOTTOM,$style_body);
			$row->setCellPadding(PDFTable_Pdf::BOTTOM,3);
			$row->setCellPadding(PDFTable_Pdf::TOP,3);


			for ($x = 0; $x < count($resultData['datacolumns']); $x++) {
				$col=new PDFTable_Pdf_Table_Column();
				$col->setBorder(PDFTable_Pdf::LEFT, $style_body);
				$col->setBorder(PDFTable_Pdf::RIGHT, $style_body);
				$col->setBorder(PDFTable_Pdf::BOTTOM, $style_body);

				$col->setFont($tfont, 12);

				if ($i % 2 != 0){
					$col->setBackgroundColor(Zend_Pdf_Color_Html::color('#DFEEF3'));
					$col->setBorder(PDFTable_Pdf::TOP, $style_body);
				}

				if ($x == 0){
					$col->setWidth(130);
					$col->setPadding(PDFTable_Pdf::LEFT, 5);
				}
				/* else
				 $col->setWidth(60); */

				$col->setText($resultData['data'][$i][$resultData['datacolumns'][$x]]);
				if ($x!=0)
					$col->setAlignment(PDFTable_Pdf::CENTER);

				$cols[]=$col;
			}

			$row->setColumns($cols);
			$table->addRow($row);
		}

		$ctable = new PDFTable_Pdf_Table(1);
		$cRow = new PDFTable_Pdf_Table_Row();

		$cCol = new PDFTable_Pdf_Table_Column();
		$cCol->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 9);
		$cCol->setText("This report shows the marks gained by $studentName up to the Report Date. The Potential Grade is what $studentName could achieve at the end of the term continuing the same level of progress as shown to date. ");
		$cCol->setTextLineSpacing(3);

		$cRow->setColumns(array($cCol));
		$ctable->addRow($cRow);

		$itable = new PDFTable_Pdf_Table(2);
		$itable->setWidth(450);

		$itable = $this->addCummulativeInterpretation($itable);

		$page->addTable($table,0,0);
		$page->addTable($ctable, 0, 260);
		$page->addTable($itable, 0, 290);

		$page->setFillColor(Zend_Pdf_Color_Html::color('maroon'));
		$page->drawText($studentName, 340, 81);

		$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 9);
		$page->setFillColor(Zend_Pdf_Color_Html::color('black'));
		$page->drawText(date('jS F Y'), 100, 38);

		$page->setFillColor(Zend_Pdf_Color_Html::color('black'));
		$page->drawText($resultData['gradelevel'], 100, 58);

		$doc->addPage($page);
		$doc->save();

		$host = $_SERVER['HTTP_HOST'];
		if ($local)
			return $file;
		else
			return "http://$host/download?file=$file";

	}

	private function createCommentPDF($html, $remotehost, $file, $orient, $local = false){
		//load template which is the same as the $hostname
		$resultData = Zend_Json::decode($html);

		//print_r($resultData);

		$abs_path=APPLICATION_PATH . "/../php/data/";

		$style_body=new Zend_Pdf_Style();
		$style_body->setLineWidth(0);
		$style_body->setLineColor(Zend_Pdf_Color_Html::color('#0070C0'));
		$style_body->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 9);


		//extraction of the page to be used

		//creating of the PDFTable Document in the .......
		$doc=new PDFTable_Pdf_Document($file,$abs_path, null);

		$page = $doc->createPage(Zend_Pdf_Page::SIZE_A4, array(20,40, 30,40));

	 	$footerTable = new PDFTable_Pdf_Table(3);
		$fcol = new PDFTable_Pdf_Table_Column();
		$fcol->setText('@@CURRENT_PAGE of @@TOTAL_PAGES');
		$fcol->setAlignment(PDFTable_Pdf::LEFT);

		$fcol2 = new PDFTable_Pdf_Table_Column();
		$fcol2->setText($resultData[0]['gradename']);
		$fcol2->setAlignment(PDFTable_Pdf::CENTER);


		$fcol3 = new PDFTable_Pdf_Table_Column();
		$fcol3->setText("Term ".$resultData[0]['cl_term']." of ".$resultData[0]['cl_year']);
		$fcol3->setAlignment(PDFTable_Pdf::RIGHT);

		$frow = new PDFTable_Pdf_Table_Row();
		$frow->setBorder(PDFTable_Pdf::TOP, $style_body);
		$frow->setCellPadding(PDFTable_Pdf::TOP,5);
		$frow->setColumns(array($fcol, $fcol2, $fcol3));
		$frow->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER),9);

		$footerTable->setWidth(550);
		$footerTable->addRow($frow);

		$doc->setFooter($footerTable);


		$headerTable = new PDFTable_Pdf_Table(3);
		$hcol = new PDFTable_Pdf_Table_Column();
		$hcol->setText("Compilation of subject teachers' remarks on :".$resultData[0]['Fullname']);
		$hcol->setAlignment(PDFTable_Pdf::RIGHT);

		$hrow = new PDFTable_Pdf_Table_Row();
		$hrow->setCellPadding(PDFTable_Pdf::RIGHT,20);
		$hrow->setColumns(array($hcol));
		$hrow->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES),9);

		$headerTable->setWidth(200);
		$headerTable->addRow($hrow);

		$doc->setHeader($headerTable);

		$y = 0;
		$style = new Zend_Pdf_Style();
		$style->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_ROMAN),12);
		$page->setStyle($style);

		/* foreach ($resultData as $comment) {
			$lines = explode("\n",$this->getWrappedText($comment['cl_comment'],$style,500)) ;
			foreach($lines as $line)
			{
				$page->drawText($line, 0, $y);
				$y+=10;
			}
		} */

		$table = new PDFTable_Pdf_Table(1);
		foreach ($resultData as $comment) {
			//draw table for comments
			$table = $this->addCommentTableRow($table,  'Name of Student', false, true);
			$table = $this->addCommentTableRow($table, strtoupper($comment['cl_coursename']),true);
			$table = $this->addCommentTableRow($table, 'WORK COVERED',true);
			$table = $this->addCommentTableRow($table, $comment['cl_content']);
			$table = $this->addCommentTableRow($table, "TEACHER'S REMARKS", true);
			$table = $this->addCommentTableRow($table, $comment['cl_comment']);
			$table = $this->addCommentTableRow($table, '', false, true);
		}

		$page->addTable($table, 0, 0 );
		$page->drawText($resultData[0]['Fullname'], 0, 17);
		$doc->addPage($page);
		$doc->save();

		$host = $_SERVER['HTTP_HOST'];
		if ($local)
			return $file;
		else
			return "http://$host/download?file=$file";

	}

	protected function getWrappedText($string, Zend_Pdf_Style $style,$max_width)
	{
		$wrappedText = '' ;
		$lines = explode("\n",$string) ;
		foreach($lines as $line) {
			$words = explode(' ',$line) ;
			$word_count = count($words) ;
			$i = 0 ;
			$wrappedLine = '' ;
			while($i < $word_count)
			{
				/* if adding a new word isn't wider than $max_width,
				 we add the word */
				if($this->widthForStringUsingFontSize($wrappedLine.' '.$words[$i]
						,$style->getFont()
						, $style->getFontSize()) < $max_width) {
					if(!empty($wrappedLine)) {
						$wrappedLine .= ' ' ;
					}
					$wrappedLine .= $words[$i] ;
				} else {
					$wrappedText .= $wrappedLine."\n" ;
					$wrappedLine = $words[$i] ;
				}
				$i++ ;
			}
			$wrappedText .= $wrappedLine."\n" ;
		}
		return $wrappedText ;
	}

	/**
	 * found here, not sure of the author :
	 * http://devzone.zend.com/article/2525-Zend_Pdf-tutorial#comments-2535
	 */
	protected function widthForStringUsingFontSize($string, $font, $fontSize)
	{
		$drawingString = iconv('UTF-8', 'UTF-16BE//IGNORE', $string);
		$characters = array();
		for ($i = 0; $i < strlen($drawingString); $i++) {
			$characters[] = (ord($drawingString[$i++]) << 8 ) | ord($drawingString[$i]);
		}
		$glyphs = $font->glyphNumbersForCharacters($characters);
		$widths = $font->widthsForGlyphs($glyphs);
		$stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
		return $stringWidth;
	}

	private function addCommentTableRow(PDFTable_Pdf_Table $table,$text,$fill = false, $start = false){
		$style = new Zend_Pdf_Style();
		$style->setLineWidth(0);

		$row = new PDFTable_Pdf_Table_Row();
		$row->setCellPaddings(array(3,5,3,5));

		$col = new PDFTable_Pdf_Table_Column();
		$col->setBorder(PDFTable_Pdf::LEFT, $style);
		$col->setBorder(PDFTable_Pdf::RIGHT, $style);
		$col->setBorder(PDFTable_Pdf::BOTTOM, $style);
		$col->setBorder(PDFTable_Pdf::TOP, $style);
		$col->setTextLineSpacing(2);
		$col->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_ROMAN), 11);

		if ($start){
			$col->setText('_');
			$col->setAlignment(PDFTable_Pdf::RIGHT);
			$col->removeBorder(PDFTable_Pdf::LEFT);
			$col->removeBorder(PDFTable_Pdf::RIGHT);
		}
		else
			$col->setText($text);

		if ($fill){
			$col->setBackgroundColor(Zend_Pdf_Color_Html::color('#DFEEF3'));
			$col->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 9);
		}

		$row->setColumns(array($col));
		$table->addRow($row);

		return $table;
	}

	private function compilepdf($url,$classlist,$term, $year, $grade, $reporttype, $gradename, $instructor, $stream = -100){
		 set_time_limit(0);

		 $parsedhost = parse_url ( $url );
		 $host = $parsedhost['host'];
		 $classlist = Zend_Json::decode($classlist);

		 //Error Identified in the Generation of Bills if there are no students in the classroom..
		 //This required exceptions for Term Bills and Entry Bills which required no students to be in the class.
		 if ($reporttype != 11 || $reporttype  != 10)
			 if (count($classlist) == 0)
			 	return "<strong>No students enrolled in the selected class</strong>";

		 $compilation = '';

		 $nyear = str_replace("/", "_", $year);

		 $filelist = array();

			switch ($reporttype) {
			case 0:
				//get student list;
				 $compilation = "Assessments.Breakdown_".$host."_".$gradename."_".$term."_".$nyear.".pdf";
				 foreach ($classlist as $student) {
				 	if ( $this->getFrontController()->getParam ('pdfmode') != 'html')
				 		$filelist[] = $this->localMakePdf("http://$host/cumjson", $student, $grade, $term, $year, 0,'L');
				 	else
				 		$filelist[] = $this->localHtmlPdf("http://$host/cummulative", $student, $grade, $term, $year, 0, 'L', $instructor);
				 }
			break;
			case 1:
				//get student list;
				$compilation = "End.Of.Term_".$host."_".$gradename."_".$term."_".$nyear.".pdf";
				foreach ($classlist as $student) {
					if ( $this->getFrontController()->getParam ('pdfmode') != 'html')
						$filelist[] = $this->localMakePdf("http://$host/termjson", $student, $grade, $term, $year, 1,'P');
					else
						$filelist[] = $this->localHtmlPdf("http://$host/termreport", $student, $grade, $term, $year, 1, 'P', $instructor);
				}
			break;
			case 2:
				//get student list;
				$compilation = "Progress_".$host."_".$gradename."_".$term."_".$nyear.".pdf";
				foreach ($classlist as $student) {
					if ( $this->getFrontController()->getParam ('pdfmode') != 'html')
						$filelist[] = $this->localMakePdf("http://$host/progressjson", $student, $grade, $term, $year, 2,'L');
					else
						$filelist[] = $this->localHtmlPdf("http://$host/progressreport", $student, $grade, $term, $year, 2, 'L', $instructor);
				}
			break;
			case 3:
				//get student list;
				$compilation = "Comments_".$host."_".$gradename."_".$term."_".$nyear.".pdf";
				foreach ($classlist as $student) {
					if ( $this->getFrontController()->getParam ('pdfmode') != 'html')
						$filelist[] = $this->localMakePdf("http://$host/commentjson", $student, $grade, $term, $year, 3,'P');
					else
						$filelist[] = $this->localHtmlPdf("http://$host/commentreport", $student, $grade, $term, $year, 3, 'P', $instructor);
				}
			break;

				//Account Related Reports generation
			case 10:
				//Entry Level Bills -- for a class
				$compilation = "Entry Bill_".$host."_".$gradename."_".$term."_".$nyear.".pdf";
					if ( $this->getFrontController()->getParam ('pdfmode') != 'html')
						$filelist[] = $this->localMakePdf("http://$host/commentjson", $student, $grade, $term, $year, 3,'P');
					else
						$filelist[] = $this->localHtmlPdf("http://$host/entrybill", null, $grade, $term, $year, 1, 'P', $instructor, $stream);
			break;

			case 11:
				//term bills for students
				$compilation = "Termly Bill_".$host."_".$gradename."_".$term."_".$nyear.".pdf";
				//foreach ($classlist as $student) {
					if ( $this->getFrontController()->getParam ('pdfmode') != 'html')
						$filelist[] = $this->localMakePdf("http://$host/commentjson", $student, $grade, $term, $year, 3,'P');
					else
						$filelist[] = $this->localHtmlPdf("http://$host/termlybill", null, $grade, $term, $year, 1, 'P', $instructor, $stream);
				//}
			break;

			case 12:
				//term bills for students
				$compilation = "Termly Bill Students_".$host."_".$gradename."_".$term."_".$nyear.".pdf";
				foreach ($classlist as $student) {
					if ( $this->getFrontController()->getParam ('pdfmode') != 'html')
						$filelist[] = $this->localMakePdf("http://$host/commentjson", $student, $grade, $term, $year, 3,'P');
					else
						$filelist[] = $this->localHtmlPdf("http://$host/termlybillstudents", $student, $grade, $term, $year, 1, 'P', $instructor, $stream);
				}
			break;
			default:
				;
			break;
		}

		//merge pdf
		$CompiledPDF = new Zend_Pdf();


		$abs_path=APPLICATION_PATH . "/../php/data";

		  $pdfMerged = new Zend_Pdf();

		   foreach($filelist as $file){

		      // Load PDF Document
		      $OldPdf = Zend_Pdf::load("$abs_path/$file");

		      // Clone each page and add to merged PDF
		      for($i=0; $i<sizeof($OldPdf->pages); ++$i){
		         $page = clone $OldPdf->pages[$i];
		         $pdfMerged->pages[] = $page;
		      }
		   }

		// Save changes to PDF
		if (file_exists("$abs_path/$compilation"))
			unlink("$abs_path/$compilation");

		$pdfMerged -> save("$abs_path/$compilation");


		$h = $_SERVER['HTTP_HOST'];

		//cleaning of files
		foreach ($filelist as $file) {
			unlink("$abs_path/$file");
		}

		return "Report successfully generated and hosted at <a href='http://$h/download?file=$compilation'>$compilation</a>";;
	}

	private function localMakePdf($url, $studentid,  $grade, $term, $year, $t, $O = 'L'){
		set_time_limit(0);

		if ($O == 'P')
			$orient = Zend_Pdf_Page::SIZE_A4;//urldecode ( $_REQUEST ['O'] );
		else
			$orient = Zend_Pdf_Page::SIZE_A4_LANDSCAPE;//'L';

		$host = $_SERVER ["HTTP_HOST"];
		$phost = parse_url ( $url );

		// To prevent anyone else using your script to create their PDF files
		// if (!($host == $phost['host'])) { die("Your domain is not authorized
		// to access this resource"); }

		// using Zend_Http_Client for remote calls instantiate the client
		$client = new Zend_Http_Client ( $url, array (
				'keepalive' => true,
				'timeout' => 600
		) );


		 		$client->setCookieJar ();
		 		$h = $phost ['host'];
		 		$client->setUri ( "http://$h/auth" );
		 		$client->setParameterPost ( array (
		 				'username' => 'reportviewer',
		 				'password' => md5 ( 'eldag' )
		 		) );

		 		$client->request ( Zend_Http_Client::POST );

		 		$client->resetParameters ();
		 		$client->setUri ( $url );
		 		$client->setParameterGet ( 'grade', $grade );
		 		$client->setParameterGet ( 'term', $term );
		 		$client->setParameterGet ( 'year', $year );

		 		if (isset ( $studentid )) {
		 			$client->setParameterGet ( 'id', $studentid );
		 			$sname = 'greport';
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

		 		//if report type of a ....
		 		switch ($t) {
		 			case 0:
		 				return $this->createCumPDF($html, $h, $file, $orient, true);
		 				break;
		 			case 1: //cummulative report
		 				return $this->createTermPDF($html, $h, $file, $orient, true);
		 				break;
		 			case 2: //progress report
		 				return $this->createProgressPDF($html, $h, $file, $orient, true);
		 				break;
		 			case 3: //commentreport
		 				return $this->createCommentPDF($html, $h, $file, $orient, true);
		 				break;
		 			default:
		 				;
		 				break;
		 		}
	}

	private function localHtmlPdf($url, $sid, $grade, $term, $year, $t, $orient = 'L',$instructor, $stream = -100){
		set_time_limit ( 0 );
		$cid = 0;

		$host = $_SERVER ["HTTP_HOST"];
		$phost = parse_url ( $url );

		// To prevent anyone else using your script to create their PDF files
/* 		if (! ($host == $phost ['host'])) {
			die ( "Your domain is not authorized to access this resource" );
		} */

		// using Zend_Http_Client for remote calls instantiate the client
		$client = new Zend_Http_Client ( $url, array (
				'keepalive' => true,
				'timeout' => 120
		) );

		// Do we have the cookies stored in our session?
		if (isset($_SESSION['cookiejar']) && $_SESSION['cookiejar'] instanceof Zend_Http_CookieJar) {
			$client->setCookieJar($_SESSION['cookiejar']);
		} else {

		// If we don't, authenticate and store cookies
		$client->setCookieJar ();
		$client->setUri ( "http://$host/auth" );
		$client->setParameterPost ( array (
				'username' => 'reportviewer',
				'password' => md5 ( 'eldag' )
		) );

		$client->request ( Zend_Http_Client::POST );

		$client->resetParameters ();
		$client->setUri ( $url );
		}
		// retrieve response from remote

		$client->setParameterGet ( 'cid', $cid );
		$client->setParameterGet ( 'grade', $grade );
		$client->setParameterGet ( 't', $t );
		$client->setParameterGet ( 'i', $instructor );
		$client->setParameterGet ( 'term', $term );
		$client->setParameterGet ( 'year', $year );
		$client->setParameterGet ( 'check', 500);

		if ($stream != -100)
			$client->setParameterGet ('stream', $stream);

		if($sid != null){
			$client->setParameterGet ( 'id', $sid );
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

		$file = mt_rand ( 1, 999999999 ) . '.pdf';

		$mpdf = new Mpdf_mPDF ( '' );

		$mpdf->useSubstitutions = true; // optional - just as an example

		if ($t == 3)
			$mpdf->SetTopMargin ( 15 );
		else if ($t == 0 || $t == 2 || $t == 1)
			$mpdf->SetTopMargin ( 40 );
		else
			$mpdf->SetTopMargin ( 30 );
			$mpdf->AddPageByArray ( array ('orientation' => $orient));

			$mpdf->CSSselectMedia = 'mpdf'; // assuming you used this in the document header
			$mpdf->setBasePath ( $url );
			$mpdf->SetDisplayMode ( 'fullpage' );
			$mpdf->SetDisplayPreferences ( '/ShowMenubar/ShowToolbar' );
			$mpdf->WriteHTML ( $html );
			$mpdf->Output ( "data/$file", 'F' );

		return $file;
	}

	private function mpdfFile($html, $url, $t, $orient, $file){

		$mpdf = new Mpdf_mPDF ( '' );
		$mpdf->debug = false;

		$mpdf->useSubstitutions = true; // optional - just as an example

		if ($t == 3)
			$mpdf->SetTopMargin ( 15 );
		else if ($t == 0 || $t == 2 || $t == 1)
			$mpdf->SetTopMargin ( 40 );
		else
			$mpdf->SetTopMargin ( 30 );
		$mpdf->AddPageByArray ( array ('orientation' => $orient));

		$mpdf->CSSselectMedia = 'mpdf'; // assuming you used this in the document header
		$mpdf->setBasePath ( $url );
		$mpdf->SetDisplayMode ( 'fullpage' );
		$mpdf->SetDisplayPreferences ( '/ShowMenubar/ShowToolbar' );
		$mpdf->WriteHTML ( $html );
		$mpdf->Output ( "data/$file", 'F' );

		$ob = ob_get_contents(); // <--| Here we catch out previous output from buffer (and can log it, email it, or throw it away as I do :-) )
		ob_end_clean(); // <--| Finaly we clean output buffering and turn it off

		$h = $_SERVER['HTTP_HOST'];
		return "http://$h/download?file=$file";
	}

}
