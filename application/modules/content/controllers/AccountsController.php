<?php

class Content_AccountsController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    	$this->view->host = $_SERVER["HTTP_HOST"];
    	$this->view->pdfhost =  $_SERVER["HTTP_HOST"];
    	//$this->view->pdfhost =  $this->getFrontController()->getParam('pdfhost');
    	$this->view->mode =  $this->getFrontController()->getParam ('pdfmode');

    	$check = $this->_request->getParam('check');

    	if (!(isset($check) && $check == 500)){
	    	$CurrentTerm = Content_Model_School::getCurrentTermYear(false);
	    		if (!$CurrentTerm)
	    			$this->_redirect('/notermerror');
    	}

    }

    public function indexAction()
    {

    	//Zend_Debug::dump(Content_Model_TermBill::getGroupFee(1, 1, 1, 3, '2012/2013',1) + Content_Model_TermBill::getGradeSpecificOnlyFee(11, 1, 1, 3, '2012/2013',1));
        // action body
    	$currentTerm = Content_Model_School::getCurrentTermYear(false);

    	if (!$currentTerm)
    		$this->_redirect('/notermerror');

    	$this->view->term = $currentTerm->term;
    	$this->view->year = $currentTerm->year;
    	$this->view->current = Content_Model_School::isCurrentPeriod($currentTerm->term, $currentTerm->year);
    	$this->view->billsexist = Content_Model_TermBill::billsExist($currentTerm->term, $currentTerm->year);

        //check for scheduled jobs
        //checking for computing account balances
        if ($this->view->current){
        	$term = $currentTerm->term;
        	$year = $currentTerm->year;

	        //if (!Content_Model_ScheduledJobs::checkJob(101)){
	        if(false){
	            //add the job
	            Content_Model_ScheduledJobs::addJob(
	                             101,
	                             "processbalances?term=$term&year=$year",
	                             array("schedule_time" => date("Y-m-d H:i:s", strtotime("+60 seconds")),
	                                   "schedule" => '10 23 * * *'),
	                             array());
	        }
        }

        $this->view->host = $_SERVER["HTTP_HOST"];
        $this->view->pdfhost = $this->getFrontController()->getParam('pdfhost');
        $this->view->show = $this->_request->getParam('show');
    }

    public function billGroupsListAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();

    }

    public function groupUpdateAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $form =  new Content_Form_FeeGroupNew(); ;

        $this->view->state = 'New Group Entry';
        $this->view->sendState = 'new';
        $this->view->formname = 'frm_feesgroup';

        if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('send') == 'new'){
            //saving new data
            if ($form->isValid($this->_request->getParams())){
                try {
                    Content_Model_FeeGroups::updateGroups(
                        null,
                        $form->getValue('groupname'),
                        $form->getValue('gradelevels')
                    );
                        $this->getResponse()->setHttpResponseCode(202);
                        $this->getResponse()->sendHeaders();
                        die(json_encode(array('status' => 1)));
                } catch (Exception $e) {}
            }
        }
        else if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('send') == 'old'){
            //updating of existing record
            $form =  new Content_Form_FeeGroupUpdate();
            if ($form->isValid($this->_request->getParams())){
                try {
                    Content_Model_FeeGroups::updateGroups(
                        $form->getValue('cl_id'),
                        $form->getValue('groupname'),
                        $form->getValue('gradelevels')
                    );
                        $this->getResponse()->setHttpResponseCode(202);
                        $this->getResponse()->sendHeaders();
                        die(json_encode(array('status' => 1)));
                } catch (Exception $e) {}
            }
        }
        else  if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('update') == 'true')
        {
            $feesGroupModel = new Content_Model_FeeGroups();

            $form =  new Content_Form_FeeGroupUpdate();
            $form->setName('frm_groupfeesU');

            $id = $this->_request->getParam('cl_id');
            $feesgroup = $feesGroupModel->find($id)->current();
            $form->populate($feesgroup -> toArray());

            $form->gradelevels->setValue(Zend_Json::decode($feesgroup->gradelevels));
            $this->view->state = 'Category Update';
            $this->view->sendState = 'old';
            $this->view->formname = 'frm_groupfeesU';


        }

        $this->view->form = $form;
    }

    public function groupListSourceAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);


        $groups = Content_Model_FeeGroups::listFeeGroups(
                $this->_request->getParam('iDisplayStart'),
                $this->_request->getParam('iDisplayLength'),
                $this->_request->getParam('sSearch'),
                $this->_request->getParam('iSortCol_0'),
                $this->_request->getParam('sSortDir_0')
        );

        $output = array ("sEcho" => intval ($this->_request->getParam('sEcho')),
                "iTotalDisplayRecords" => $groups['iTotalRecords'],
                "iTotalRecords" => $groups['iTotalRecords'],
                "aaData" => array () );


        //transformation of the array
        foreach ($groups['data'] as $group) {
            $row = array();
            //$row[] = $store->cl_id;
            $row[] = $group->groupname;

            $grades = array_intersect_key(Content_Model_GradeLevels::gradelevels(),
                    array_flip(Zend_Json::decode($group->gradelevels)));

            $row[] = "<span style='font-size : 11px; font-weight:bold'>". implode('; ', array_values($grades))."</span>";

            $row[] ="<a onclick='$.fn.toggleGroupUpdate($group->cl_id);'><span class='label label-inverse label-mini'>update</span></a> | ".
                    "<a onclick='$.fn.toggleGroupDelete($group->cl_id);'><span class='label label-info label-mini'>delete</span></a>";
            $output['aaData'][] = $row;
        }

        echo json_encode($output);
    }

    public function termBillAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $currentTerm = Content_Model_School::getCurrentTermYear(false);

        $this->view->term = $currentTerm->term;
        $this->view->year = $currentTerm->year;
        $this->view->current = Content_Model_School::isCurrentPeriod($currentTerm->term, $currentTerm->year);
    }

    public function termBillSourceAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);

        if (isset($_REQUEST['term']) && isset($_REQUEST['year'])){
			$term = $this->_request->getParam('term');
			$year = $this->_request->getParam('year');
        }else{
        	$currentTerm = Content_Model_School::getCurrentTermYear(false);
        	$term = $currentTerm->term;
        	$year = $currentTerm->year;
        }

        $termbills = Content_Model_TermBill::listBills(
                $this->_request->getParam('iDisplayStart'),
                $this->_request->getParam('iDisplayLength'),
                $this->_request->getParam('sSearch'),
                $this->_request->getParam('iSortCol_0'),
                $this->_request->getParam('sSortDir_0'),
        		$term,
        		$year
        );

        $output = array ("sEcho" => intval ($this->_request->getParam('sEcho')),
                "iTotalDisplayRecords" => $termbills['iTotalRecords'],
                "iTotalRecords" => $termbills['iTotalRecords'],
                "aaData" => array () );

        //transformation of the array
        $allGrades = Content_Model_GradeLevels::gradelevels();
        $feeGroups = Content_Model_FeeGroups::getFeeGroupsArray();

        foreach ($termbills['data'] as $termbill) {
            $row = array();
            $row[] = $termbill->description;

            $terms = array(1=>'1st', 2 => "2nd", 3=>"3rd");
			$feetypes = array(0 => 'Entry Fee', 1 => 'Term Fee', 2 => 'Monthly Fee', 3=> 'Yearly Fee' );

           // $row[] = implode('; ', array_values($grades));
            $row[] = ($termbill->feegroup == -1) ? 'All' : (($termbill->feegroup == -2) ? 'None' :$feeGroups[$termbill->feegroup]);
            $row[] = ($termbill->specificgrades == -1) ? 'N/A' : $allGrades[$termbill->specificgrades];
            $row[] = $termbill->amount;
            $row[] = $terms[$termbill->term];
            $row[] = $termbill->year;
            $row[] = $feetypes[$termbill->type];
            $row[] = ($termbill->mandatory == 1) ? 'mandatory' : 'optional';

            $row[] ="<a onclick='$.fn.toggleBillUpdate($termbill->cl_id);'><span class='label label-inverse label-mini'>update</span></a> | ".
                    "<a onclick='$.fn.toggleBillDelete($termbill->cl_id);'><span class='label label-info label-mini'>delete</span></a>";

            $output['aaData'][] = $row;
        }

        echo json_encode($output);
    }

    public function termBillUpdateAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();

        $form = new Content_Form_TermBillUpdate();

        $this->view->state = 'New Billable';
        $this->view->sendState = 'new';

        if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('send') == 'new'){
            //saving new data
            if ($form->isValid($this->_request->getParams())){
                try {
                    Content_Model_TermBill::updateBills(
                        null,
                        $form->getValue('description'),
                        $form->getValue('feegroup'),
                        $form->getValue('specificgrades'),
                        $form->getValue('amount'),
                        $form->getValue('mandatory'),
                        $form->getValue('type'),
                        $form->getValue('term'),
                        $form->getValue('year'),
                        $form->getValue('stream')
                    );
                    $this->getResponse()->setHttpResponseCode(202);
                    $this->getResponse()->sendHeaders();
                    die(json_encode(array('status' => 1)));
                } catch (Exception $e) {
                    Zend_Debug::dump($e->getMessage());
                }
            }
        }
        else if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('send') == 'old'){
            //updating of existing record
            if ($form->isValid($this->_request->getParams())){
                try {
                    Content_Model_TermBill::updateBills(
                        $form->getValue('cl_id'),
                        $form->getValue('description'),
                        $form->getValue('feegroup'),
                        $form->getValue('specificgrades'),
                        $form->getValue('amount'),
                        $form->getValue('mandatory'),
                        $form->getValue('type'),
                        $form->getValue('term'),
                        $form->getValue('year'),
                        $form->getValue('stream')
                    );
                    $this->getResponse()->setHttpResponseCode(202);
                    $this->getResponse()->sendHeaders();
                    die(json_encode(array('status' => 1)));
                } catch (Exception $e) {
                }
            }
        }
        else  if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('update') == 'true')
        {
            $termbill = new Content_Model_TermBill();

            $id = $this->_request->getParam('cl_id');
            $termbill = $termbill->find($id)->current();
            $form->populate($termbill -> toArray());
            //$form->gradelevels->setValue(Zend_Json::decode($termbill->gradelevels));
            $this->view->state = 'Bill Update';
            $this->view->sendState = 'old';
        }

        $this->view->form = $form;
        $this->view->viewform = new Content_Form_ViewBill2();

    }

    public function accountSummaryAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $currentPeriod = Content_Model_School::getCurrentTermYear();
        $term = $currentPeriod->term;
        $year = $currentPeriod->year;

        $groups =Content_Model_FeeGroups::getFeeGroupsArray();
        $totalsArray = array();
        $totalsEArray = array();
        $totalsYArray = array();
        $totalsMArray = array();

        foreach ($groups as $key => $value) {
        	$total = new stdClass();
        	$total->id = $key;
        	$total->name = $value;
        	$total->value =number_format(Content_Model_TermBill::getGroupFee($key, 0, 1,$term,$year), 2,'.',',');
        	$list = Content_Model_TermBill::getBillforGroup($key, 0, 1, $term, $year);
        	$total->list = '<ul>';
            foreach ($list as $value) {
            	$total->list .= "<li>$value->description : <strong> GHC $value->amount </strong></li>";
            }
            $total->list .= '</ul>';
            $totalsEArray[] = $total;
        }

        foreach ($groups as $key => $value) {
            $total = new stdClass();
            $total->id = $key;
            $total->name = $value;
            $total->value = number_format(Content_Model_TermBill::getGroupFee($key, 1, 1,$term,$year), 2,'.',',');
            $list = Content_Model_TermBill::getBillforGroup($key, 1, 1, $term, $year);
			$total->list = '<ul>';
            foreach ($list as $value) {
            	$total->list .= "<li>$value->description : <strong> GHC $value->amount </strong></li>";
            }
            $total->list .= '</ul>';
            $totalsArray[] = $total;
        }

        foreach ($groups as $key => $value) {
        	$total = new stdClass();
        	$total->id = $key;
        	$total->name = $value;
        	$total->value = number_format(Content_Model_TermBill::getGroupFee($key, 2, 1,$term,$year), 2,'.',',');
        	$list = Content_Model_TermBill::getBillforGroup($key, 2, 1, $term, $year);
        	$total->list = '<ul>';
        	foreach ($list as $value) {
        		$total->list .= "<li>$value->description : <strong> GHC $value->amount </strong></li>";
        	}
        	$total->list .= '</ul>';
        	$totalsMArray[] = $total;
        }

        foreach ($groups as $key => $value) {
        	$total = new stdClass();
        	$total->id = $key;
        	$total->name = $value;
        	$total->value = number_format(Content_Model_TermBill::getGroupFee($key, 3, 1,$term,$year), 2,'.',',');
        	$list = Content_Model_TermBill::getBillforGroup($key,3, 1, $term, $year);
        	$total->list = '<ul>';
        	foreach ($list as $value) {
        		$total->list .= "<li>$value->description : <strong> GHC $value->amount </strong></li>";
        	}
        	$total->list .= '</ul>';
        	$totalsYArray[] = $total;
        }



        $this->view->termFeeTotals = $totalsArray;
        $this->view->termEntryFeeTotals = $totalsEArray;
        $this->view->monthlyTotals = $totalsMArray;
        $this->view->yearlyTotals = $totalsYArray;
        $this->view->accountSummary = Content_Model_Transactions::getAccountSummary($term, $year);

    }

    public function checkBillStudentsAction()
    {
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);

        $date = date('Y-m-d');

        $period = Content_Model_School::getCurrentTermYear ();
        $term = $period->term;
        $year = $period->year;

        $studentCount = Content_Model_Students::getActiveStudentCount();
        $studentUnbilled = Content_Model_TermBill::getUnBilledStudents($date, false, $term, $year);

        $data = array('activecount' => $studentCount,
                      'unbilledtermly'=>$studentUnbilled['ubtermly'],
        			  'unbilledmonthly'=>$studentUnbilled['ubmonthly'],
        			  'month' => date('F',strtotime($date)),
                      'success' => ( ($studentUnbilled['ubtermly'] + $studentUnbilled['ubmonthly']) == 0) ? true : false,
        			  'term' => $term,
        			  'year' => $year
        );

       header("Content-Type: text/json");
        //..perform queries and put it in $data..

        echo json_encode($data);
    }

    public function billStudentsAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);
        $term = $this->_request->getParam('term');
        $year = $this->_request->getParam('year');

        $secret = $this->_request->getParam('secret');

         if ($secret == md5('14233791')){
            //get list of students currently active
            //check each of them against the

             //TODO: CHECK TO SEE OF TERM BILLS HAVE BEEN APPROVED FOR BILLING
             //TODO: ADD TERM AND YEAR TO THE SELECTION OF BILL
            /*  $currentPeriod = Content_Model_School::getCurrentTermYear();
             $term = $currentPeriod->term;
             $year = $currentPeriod->year; */

             $date = date('Y-m-d');

             $studentUnbilled = Content_Model_TermBill::getUnBilledStudents($date, true, $term, $year);

			 //
             if ($studentUnbilled['ubtermly'] > 0){
             	 foreach ($studentUnbilled['ubtermlystudents'] as $student) {
                     //the actual billing of accounts
                     $description = "Term Fees for term $term of $year";
                     $billed = Content_Model_Transactions::billStudent(
                             0,
                             $student['cl_GPSN_ID'],
                             $student['cl_GradeLevel'],
                             date('Y-m-d H:i:s'),
                             $description,
                             '999',
                             null,
                             null,
                             $term,
                             $year,
                     		 false,
        					 $student['cl_Resident']);
                    if ($billed){
                     //recording the billing of accounts
                      echo Content_Model_TermBill::recordBillStudent($student['cl_GPSN_ID'], $term, $year, 'T') . "\n\n";
                    }
                    else{
                      echo "Billing failed for :". $student['cl_GPSN_ID']."\n\n";
                    }
                 }
             };

             if ($studentUnbilled['ubmonthly'] > 0){
             	 foreach ($studentUnbilled['ubmonthlystudents'] as $student) {
                     //the actual billing of accounts
                     $description = "Monthly fee : ".date('F',strtotime($date));
                     $billed = Content_Model_Transactions::billStudent(
                             0,
                             $student['cl_GPSN_ID'],
                             $student['cl_GradeLevel'],
                             date('Y-m-d H:i:s'),
                             $description,
                             '999',
                             null,
                             null,
                             $term,
                             $year,
                     		 true,
        					 $student['cl_Resident']);
                    if ($billed){
                     //recording the billing of accounts
                      echo Content_Model_TermBill::recordBillStudent($student['cl_GPSN_ID'], $term, $year, 'M', date('m',strtotime($date))) . "\n\n";
                    }
                    else{
                      echo "Billing failed for : ".$student['cl_GPSN_ID']."\n\n";
                    }
                 }
             };

             if (($studentUnbilled['ubmonthly'] + $studentUnbilled['ubtermly']) == 0)
                 	echo "All students already billed --- aborting job. \n\n";
        }else
        {
            echo "Secret provided is invalid";
            $this->getResponse()->setHttpResponseCode(403);
            $this->getResponse()->sendHeaders();
        }

    }

    public function initiateAutoBillingAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);
        $term = $this->_request->getParam('t');
        $year = $this->_request->getParam('y');

        $time = time();

        //$studentCount = Content_Model_Students::getActiveStudentCount();
       // $studentUnbilled = Content_Model_TermBill::getUnBilledStudents($date);

        if ($this->getFrontController()->getParam('online') == 'true'){
         header('Content-Type: application/json');
         $job = Content_Model_ScheduledJobs::addJob(
                            100,
                            "billstudents?time=$time&term=$term&year=$year",
                            array(),// array("schedule_time" => date("Y-m-d H:i:s", strtotime("+15 seconds"))),
                            array());

         $data = array('success' => true);
         echo json_encode($data);
        }else {
			$this->_forward('bill-students','accounts', 'content', array('term'=>$term, 'year'=>$year, 'time'=>time(),'secret' => md5('14233791')));
			header('Content-Type: application/json');
			$data = array('success'=>true);
			echo json_encode($data);
        }
    }

    public function listUnbilledStudentsAction()
    {
        // action body

    }

    public function processAccountBalancesAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);

        $secret = $this->_request->getParam('secret');
        $term = $this->_request->getParam('term');
        $year = $this->_request->getParam('year');

        if ($secret == md5('14233791')){
            try {
                Content_Model_AccountBalances::computeUpdateBalances($term, $year);

                //injecting cleaning of pdf folder here
                $dir = './data/';
                if($dh = opendir($dir)){
                	  $x = 0;
                	while(($file = readdir($dh))!== false){
                		if(file_exists($dir.$file)) {
                			if ( filemtime($dir.$file) <= time()-60*60 ) {
                				$x += 1;
                				@unlink($dir.$file);
                			}
                		}
                	}
                	closedir($dh);
                	//echo "$x file[s] were cleaned up from pdf folder \n\n";
                }

              // header('Content-Type: application/json');
               echo("Processing of Balances completed successfully");
               $this->getResponse()->setHttpResponseCode(200);
               $this->getResponse()->sendHeaders();

            } catch (Exception $e) {
                Zend_Debug::dump($e->getMessage());
            }
        }

    }

    public function studentDebtorsAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();

    }

    public function studentCreditorsAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();

    }

    public function studentBalSourceAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);

        $currentPeriod = Content_Model_School::getCurrentTermYear();
        $debtors = $this->_request->getParam('debtors');
        $residence = $this->_request->getParam('residence');

        $term = $currentPeriod->term;
        $year = $currentPeriod->year;

            try {
                $balances = Content_Model_AccountBalances::getAllBalances(
                        $this->_request->getParam('iDisplayStart'),
                        $this->_request->getParam('iDisplayLength'),
                        $this->_request->getParam('sSearch'),
                        $this->_request->getParam('iSortCol_0'),
                        $this->_request->getParam('sSortDir_0'),
                        $term,
                        $year,
                        ($debtors == 1) ? true : false,
                		($residence == 1)? true : false
                );

                $grades = Content_Model_GradeLevels::gradelevels();
                $grades = $grades + array('9999' => 'Graduated');

                $output = array ("sEcho" => intval ($this->_request->getParam('sEcho')),
                        "iTotalDisplayRecords" => $balances['iDisplayRecords'],
                        "iTotalRecords" => $balances['iTotalRecords'],
                        "aaData" => array () );

                //transformation of the array
                $allGrades = Content_Model_GradeLevels::gradelevels();

                foreach ($balances['data'] as $balance) {
                    $row = array();
                    $fullname = $balance->fullname;

                    $terms = array(1=>'1st', 2 => "2nd", 3=>"3rd");

                    // $row[] = implode('; ', array_values($grades));
                    $row[] = $balance->cl_GPSN_ID;
                    if ($debtors == 1)
                    	$row[] = $balance->fullname . "<a class='pull-right' onclick='$.fn.payfees($balance->cl_GPSN_ID,\"$fullname\", $balance->cl_GradeLevel);'><span class='label label-important label-mini'>Make Payment</span></a>";
					else
                   	$row[] = $balance->fullname;
                    $row[] = $grades[$balance->cl_GradeLevel ];
                    $row[] = $balance->balanceamount;
                    if ($debtors == 1){
                        $row[] ="<a onclick='$.fn.offerDiscount($balance->cl_GPSN_ID,\"$fullname\", $balance->cl_GradeLevel);'><span class='label label-inverse label-mini'>discount</span></a>";
                                /*"<a onclick='$.fn.toggleBillDelete($balance->cl_GPSN_ID);'><span class='label label-info label-mini'>delete</span></a>"; */
                    }else{
                        $row[] = ' ';
                    }

                    $output['aaData'][] = $row;
                }
            } catch (Exception $e) {
                Zend_Debug::dump($e->getMessage());
            }

        echo json_encode($output);
    }

    public function offerDiscountAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();

        $identity = Zend_Auth::getInstance()->getIdentity();

        $form = new Content_Form_Discount();

        $this->view->state = 'Fees Discount';
        $this->view->sendState = 'new';

        if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('send') == 'new'){
            //saving new data
            if ($form->isValid($this->_request->getParams())){
                try {
                  $result =  Content_Model_Transactions::offerDiscount(
                        $form->getValue('cl_id'),
                        $form->getValue('gradelevel'),
                        $form->getValue('discount'),
                        $identity->cl_IID
                    );
                  if ($result){
                    $this->getResponse()->setHttpResponseCode(202);
                    $this->getResponse()->sendHeaders();
                    die(json_encode(array('status' => 1)));
                  }else
                    die(json_encode(array('status' => 0)));

                } catch (Exception $e) {
                    Zend_Debug::dump($e->getMessage());
                }
            }
        }
        else if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('send') == 'old'){
            //updating of existing record
            if ($form->isValid($this->_request->getParams())){
                try {
                   $result = Content_Model_Transactions::offerDiscount(
                        $form->getValue('cl_id'),
                        $form->getValue('gradelevel'),
                        $form->getValue('discount'),
                        $identity->cl_IID
                    );
                  if ($result){
                    $this->getResponse()->setHttpResponseCode(202);
                    $this->getResponse()->sendHeaders();
                    die(json_encode(array('status' => 1)));
                  }
                  else
                    die(json_encode(array('status' => 0)));

                } catch (Exception $e) {
                    Zend_Debug::dump($e->getMessage());
                }
            }
        }
        else  if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('update') == 'true')
        {

           // $grades = Content_Model_GradeLevels::gradelevels();
            $data = array(
                    'cl_id' => $this->_request->getParam('studentid'),
                    'studentname' => $this->_request->getParam('studentname'),
                    'discount' => Content_Model_Transactions::getStudentDiscount($this->_request->getParam('studentid')),
                    'gradelevel' => $this->_request->getParam('gradelevel')
                    );
            $form->populate($data);

            $this->view->state = 'Discount Update';
            $this->view->sendState = 'old';
        }

        $this->view->form = $form;
    }

    public function initComputeBalancesAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);

        $currentPeriod = Content_Model_School::getCurrentTermYear();
        $term = $currentPeriod->term;
        $year = $currentPeriod->year;


        if ($this->getFrontController()->getParam('online') == 'true'){
        	header("Content-Type: text/json");

            Content_Model_ScheduledJobs::addJob(
                             101,
                             "processbalances?term=$term&year=$year",
                             array(),
                             array());
            echo json_encode('Computation of balances to be executed by server within the next 5mins');
        }else{
 			$result = $this->forward('process-account-balances','accounts','content',array('term'=>$term, 'year' => $year, 'secret' =>md5('14233791')));
 			echo ($result);
        }
    }

    public function studentDiscountAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();

        $currentPeriod = Content_Model_School::getCurrentTermYear();
        $term = $currentPeriod->term;
        $year = $currentPeriod->year;

        $discounts =  Content_Model_Transactions::getTermTransactions($term, $year, 2);

       // Zend_Debug::dump($discounts);

        $this->view->discounts = $discounts;

    }

    public function viewBillsAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->view->form = new Content_Form_ViewBill();
    }

    public function viewBillSourceAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $currentTerm = Content_Model_School::getCurrentTermYear(false);
        $this->view->page = $this->_request->getParam('view');

        if ($this->_request->getParam('group') != null){
            $this->view->billables = Content_Model_TermBill::getBillforGroup($this->_request->getParam('group'), $this->_request->getParam('feetype'), 1, $currentTerm->term, $currentTerm->year);
            $this->view->otherbillables = Content_Model_TermBill::getAdditionalBillforGroup($this->_request->getParam('group'), $this->_request->getParam('feetype'), 1, $currentTerm->term, $currentTerm->year);
            $this->view->button = ($this->_request->getParam('feetype') == -100) ? 4 : $this->_request->getParam('feetype');
        }
        else
        {
            $this->getHelper('viewRenderer')->setNoRender(true);
            echo "<p align=\"center\" class=\"well well-small\">Please select a valid group from above to view a bill</p>";
        }
    }

    public function showTransactionAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();

        $currentPeriod = Content_Model_School::getCurrentTermYear();

        if (!$currentPeriod)
        	$this->_redirect('/notermerror');

        $term = $currentPeriod->term;
        $year = $currentPeriod->year;

         $audit = $this->_request->getParam('audit');
         if (!isset($audit))
         	$audit = false;
         else
         	$audit = true;
       /* if ($audit)
       		$transactions =  Content_Model_Transactions::getTermTransactions($term, $year, -1, $audit);
       	else
       		$transactions =  Content_Model_Transactions::getTermTransactions($term, $year, -1); */

        $this->view->audit = $audit;
/*         $this->view->transactions = $transactions;
 */    }

    public function showTransSourceAction()
    {
            // action body
            $this->getHelper('layout')->disableLayout();
            $this->getHelper('viewRenderer')->setNoRender(true);

            $audit = $this->_request->getParam('audit');

            $currentPeriod = Content_Model_School::getCurrentTermYear();
            $term = $currentPeriod->term;
            $year = $currentPeriod->year;

            $transactions =  Content_Model_Transactions::getTermTransactionsSource(
                    $this->_request->getParam('iDisplayStart'),
                    $this->_request->getParam('iDisplayLength'),
                    $this->_request->getParam('sSearch'),
                    $this->_request->getParam('iSortCol_0'),
                    $this->_request->getParam('sSortDir_0'),
                    $term,
                    $year,
                    -1,
            		$audit
            );

            $output = array ("sEcho" => intval ($this->_request->getParam('sEcho')),
                    "iTotalDisplayRecords" => $transactions['iDisplayRecords'],
                    "iTotalRecords" => $transactions['iTotalRecords'],
                    "aaData" => array () );

            //transformation of the array
            $allGrades = Content_Model_GradeLevels::gradelevels();
            $types = array(0 => "Debit-Bill", 1=>"Credit-Paid", "Credit-Disc.");

            foreach ($transactions['data'] as $transaction) {
                $pmode = ($transaction->transpaymode == null) ? "n/a" : (($transaction->transpaymode == 0) ? "CASH" : "CHEQUE");
                $sno = ($transaction->transslipno == null) ? 'n/a' : $transaction->transslipno;
                $userid = ($transaction->transinitiator == 999) ? "Auto Billing" : $transaction->transuser;

               // $details = "Receipt No : <span class='label label-important label-mini'>".$transaction->cl_id."</span> <br/> ";
                $details  = "Student : <span class='label label-inverse label-mini'>".$transaction->fullname."</span> <br/> ";
                $details .= "Payment Mode : <span class='label label-important label-mini'>".$pmode."</span> <br/> ";
                $details .='Slip/Cheque no : <span class=\'label label-important label-mini\'>'.$sno.'</span> <br/> ';
                $details .='Tranx date : <span class=\'label label-success label-mini\'>'.(date('D, jS M Y',strtotime($transaction->transdate))) .'</span> <br/> ';
                $details .='Performed by : <span class=\'label label-info label-mini\'>'.$userid.'</span> <br/> ';

                $d = str_getcsv($transaction->transdescription,';');
                if(is_numeric($d[0]))
                		$desc = $d[1];
                else
                		$desc = $transaction->transdescription;

                if($transaction->transtype == 1) {
               		 $anchor = "<a href=\"#\"
                        rel=\"popover\" data-title=\"Account Audit Info\" data-placement=\"left\"
                        data-trigger=\"hover\" data-html=\"true\" data-content=\"$details\" onclick=\"$.fn.printReceipt($transaction->cl_id);\">
                            $desc
                       </a>";
                }else{
                	$anchor = "<a
                	rel=\"popover\" data-title=\"Account Audit Info\" data-placement=\"left\"
                	data-trigger=\"hover\" data-html=\"true\" data-content=\"$details\">
                	$desc
                	</a>";
                }


                $row = array();
                $row[] = date('d-M-Y H:i:s', strtotime($transaction->transdate));
                $row[] = $transaction->fullname;
                $row[] = $allGrades[$transaction->gradelevel];
                $row[] = $types[$transaction->transtype];
                $row[] = $anchor;
                $row[] = ($transaction->transtype == 0)? $transaction->transamount : "(".$transaction->transamount.")";
                $output['aaData'][] = $row;
            }

            echo json_encode($output);
    }

    public function payFeesAction()
    {
        // action body
    	$this->getHelper('layout')->disableLayout();

    	$identity = Zend_Auth::getInstance()->getIdentity();

    	$form = new Content_Form_PayFees();
    	$currentTerm = Content_Model_School::getCurrentTermYear(false);
    	$term = $currentTerm->term;
    	$year = $currentTerm->year;

    	$this->view->state = 'Fees Payment';
    	$this->view->sendState = 'new';

    	if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('send') == 'pay'){
    		//saving new data
    			try {
    				$this->getHelper('viewRenderer')->setNoRender(true);

    				$studentid = $this->_request->getParam('studentid');
    				$gradelevel = $this->_request->getParam('gradelevel');
    				$payAmount = $this->_request->getParam('amount');
    				$payMode = $this->_request->getParam('mode');
    				$slipNo = $this->_request->getParam('slipno');
    				$optionids = $this->_request->getParam("options");

    				$result = Content_Model_Transactions::payFees(
    						$studentid,
    						$gradelevel,
    						$payAmount,
    						$payMode,
    						$slipNo,
    						$identity->cl_IID,
    						$optionids
    				);
    				if ($result){
    					echo $result;
    				}else
    					die(json_encode(array('status' => 0)));

    			} catch (Exception $e) {
    				Zend_Debug::dump($e->getMessage());
    			}
    	}
    	else  if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('update') == 'true')
    	{

    		$this->view->currentarrears =  Content_Model_AccountBalances::getStudentBalance($this->_request->getParam('studentid'), $term, $year, false);
    		$this->view->priorarrears = Content_Model_AccountBalances::getPriorArrears($this->_request->getParam('studentid'), $term, $year);

       		$data = array(
    				'Pcl_id' => $this->_request->getParam('studentid'),
    				'Pstudentname' => Content_Model_Students::getStudentName($this->_request->getParam('studentid')),
    				'arrears' => $this->view->currentarrears + $this->view->priorarrears,
    				'Pgradelevel' => $this->_request->getParam('gradelevel')
    		);

    		$form->populate($data);
    		$groupkey = Content_Model_FeeGroups::getGroupIDForGrade($this->_request->getParam('gradelevel'));
    		$optionals = Content_Model_TermBill::getBillforGroup($groupkey, 1, 0, $term, $year);

    		//Zend_Debug::dump(Content_Model_TermBill::getBillforGroup($groupkey, 1, 0, $term, $year));
    		 $optionalView ='';

    		 $debits = Content_Model_Transactions::getDebitTransactions($this->_request->getParam('studentid'), $term, $year,  $this->_request->getParam('gradelevel'));
    		 $debitsArr = array();

    		 if ($debits){
	    		 foreach ($debits as $debit) {
	    		 	$d = str_getcsv( $debit->transdescription,';');
	    		 	if(is_numeric($d[0]))
	    		 		$debitsArr[] = $d[0];
	    		 }
    		 }

    		 foreach ($optionals as $option) {
    		 	//$sid = str_getcsv($this->_request->getParam('student'),';');

    		 	if (in_array($option->cl_id, $debitsArr)) {
     				$optionalView .=  "<tr>
    								<td align=\"center\"><input form=\"frm_payfees\" type=\"checkbox\" class=\"case\" name=\"case$option->cl_id\"
    										value=\"$option->cl_id\" disabled/></td>
    									<td width=\"200\">$option->description</td>
    								<td><span id=\"amt$option->cl_id\">$option->amount</span></td>
    								</tr>";
    		 	}
    		 	else{
    		 		$optionalView .=  "<tr>
				    		 		<td align=\"center\"><input form=\"frm_payfees\" type=\"checkbox\" class=\"case\" name=\"case$option->cl_id\"
				    		 			value=\"$option->cl_id\"/></td>
				    		 		<td width=\"200\">$option->description</td>
				    		 		<td><span id=\"amt$option->cl_id\">$option->amount</span></td>
		    		 			</tr>";
    		 	}
    		 }

    		$this->view->optview = $optionalView;
    		$this->view->sendState = 'pay';
    	}

    	$this->view->form = $form;
    }

    public function printReceiptAction()
    {
        // action body
        $receiptid = $this->_request->getParam('receiptid');
      /*   $currentTerm = Content_Model_School::getCurrentTermYear(false,true);
        $term = $currentTerm->term;
        $year = $currentTerm->year; */

        $term = $this->_request->getParam('term');
        $year = $this->_request->getParam('year');

        $this->view->receipt = Content_Model_Transactions::loadTransactionDetail($receiptid);

        $this->view->priorbalance = Content_Model_AccountBalances::getPriorArrears($this->view->receipt->cl_GPSN_ID, $term, $year);
        $this->view->balance =$this->view->priorbalance +  Content_Model_AccountBalances::getStudentBalance( $this->view->receipt->cl_GPSN_ID, $term, $year, false);
        $this->view->payable = $this->view->priorbalance + Content_Model_AccountBalances::getStudentPayable($this->view->receipt->cl_GPSN_ID, $term, $year);
        $this->view->paid =	Content_Model_AccountBalances::getStudentPaid($this->view->receipt->cl_GPSN_ID, $term, $year, $this->view->receipt->cl_id, $this->view->receipt->transdate);
        $this->view->discount = Content_Model_Transactions::getStudentDiscount($this->view->receipt->cl_GPSN_ID,$term, $year, true);
        $this->view->debits = Content_Model_Transactions::getDebitTransactions($this->view->receipt->cl_GPSN_ID, $term, $year, $this->view->receipt->gradelevel);



        $this->getHelper('layout')->disableLayout();

        $printer = Content_Model_School::getReceiptPrinter();
        if ($printer == 0){
        	$this->renderScript("accounts/print-receipt.phtml");
        }else{
        	$duplicate = Content_Model_School::getDuplicateReceipt();
        	if (!$duplicate)
        		$this->renderScript("accounts/print-receipt_A4.phtml");
        	else
        		$this->renderScript("accounts/print-receipt_A4_duplicate.phtml");
        }
    }

    public function accountsStatementAction()
    {
        // action body
    	header ( "Access-Control-Allow-Origin: *" );

        $this->getHelper('layout')->disableLayout();
        //$this->getHelper('viewRenderer')->setNoRender(true);

    	$this->view->term = $this->_request->getParam('term');
    	$this->view->year = $this->_request->getParam('year');

    	if (Content_Model_School::isCurrentPeriod($this->view->term, $this->view->year))
    		$this->view->accountdate = date('jS M Y');
    	else{
    		$ad =  Content_Model_Attendance::getTermDates($this->view->term, $this->view->year );
     		$this->view->accountdate = $ad->endDate;
    	}

    	$this->view->statements = Content_Model_Transactions::getStatementOfAccounts($this->view->term, $this->view->year);

    	$statement = $this->view->statements;// Content_Model_Transactions::getStatementOfAccounts($this->view->term, $this->view->year);
/*
    	$resultStructure = array(
    			'columns' => array('Student','Class','Opening', 'Charges', 'Payments', 'Closing'),
    			'headersCount' => 2,
    			'columns2' => array('','','Arrears','Credit','Fees','Discounts','','Arrears','Credit'),
    			'datacolumns' => array('fullname','gradename','openingarrears','openingcredit', 'termpayable','discount','totalpaid','closingarrears','closingcredit'),
    			'colspans'=> array(1,1,2,2,1,2),
    			'metadata' => array('Term'=>$this->view->term, 'Year' => $this->view->year, 'Accounting Date'=> $this->view->accountdate),
    			'data' => $statement); */

    	//echo Zend_Json::encode($resultStructure);
    }

    public function adjustAccountAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->view->form = new Content_Form_AdjustAccount();
        $commit = $this->_request->getParam('commit');

        if (isset($commit) && $commit == 1){
        	$adjamount = $this->_request->getParam('adjamount');
        	$reason = $this->_request->getParam('reason');
        	$studentid = $this->_request->getParam('studentid');
        	$adjtype = $this->_request->getParam('adjtype');

        	$CurrentTerm = Content_Model_School::getCurrentTermYear(false);
        	$term = $CurrentTerm->term;
        	$year = $CurrentTerm->year;

        	Content_Model_Transactions::adjustAccount($studentid, $term, $year, $adjamount, $adjtype, $reason);
        }
    }

    public function billableslistAction()
    {
        // action body
    	$this->getHelper('layout')-> disableLayout();
    	$this->getHelper('viewRenderer')->setNoRender(true);

    	$query = $this->_request->getParam('query');

		$list = Content_Model_TermBill::billablesList();
		$arr = array();

		foreach ($list as $value) {
			$arr[] = $value->description;
		}

		echo Zend_Json::encode($arr);
    }

    public function printbillsAction()
    {
        // action body
    	$identity = Zend_Auth::getInstance()->getIdentity();

    	if ($identity->cl_classteacher != -1 && $identity->role == 50){
    		$this->view->classes = array( $identity->cl_classteacher => Content_Model_GradeLevels::getGradeName($identity->cl_classteacher));
    	}else if ($identity->role == 110){
    		$this->view->classes = Content_Model_GradeLevels::gradelevels();
    	}else{
    		$this->_forward('noauth','error','security');
    	}

    	$this->view->reporttypes = array(10 => "Entry Level Bill", 11=> "Term Bills", 12=>"Term Bills - Students");
    	$this->view->terms = array(1=>"Term 1", 2=> "Term 2", 3=>"Term 3");
    	$this->view->streams = array('0' => 'Non-Residential Students', '1' => 'Residenced Students (Boarders)');
    	$this->view->years = Content_Model_School::getPastYearsArray(true);

    	$CurrentTerm = Content_Model_School::getCurrentTermYear();
    	$this->view->ct = $CurrentTerm->term;
    	$this->view->cy = $CurrentTerm->year;
    	$this->view->online =  $this->getFrontController()->getParam ('online');

    }

    public function clearbillsAction()
    {
        // action body
        $this->getHelper('layout')-> disableLayout();
    	$this->getHelper('viewRenderer')->setNoRender(true);

    	$currentTerm = Content_Model_School::getCurrentTermYear(false);
    	$term = $currentTerm->term;
    	$year = $currentTerm->year;

        if (Zend_Auth::getInstance()->getIdentity()->role == 110){
			$result = Content_Model_TermBill::clearBilling($term, $year, date('m'));
        }

        header("Content-Type: text/json");

        echo json_encode(array('success' => true));
    }

    public function entrybillAction()
    {
        // action body
    	$this->getHelper('layout')->disableLayout();
    	$identifier = $this->_frontController->getInstance()->getParam('identifier');

        $term = $this->_request->getParam('term');
        $year = $this->_request->getParam('year');
        $grade = $this->_request->getParam('grade');
        $stream = $this->_request->getParam('stream');

		//generate the bill for printing.....
		$this->view->term = $term;
		$this->view->year = $year;
		$this->view->grade = $grade;
		$this->view->stream = $stream;

		$groupkey = Content_Model_FeeGroups::getGroupIDForGrade ( $grade );

		if ($groupkey != - 1){
          $this->view->billables = Content_Model_TermBill::getBillforGroup($groupkey, 0, 1, $term, $year, $stream);
          $this->view->termbillables = Content_Model_TermBill::getBillforGroup($groupkey, 1, 1, $term, $year, $stream);
          $this->view->gradebillables = Content_Model_TermBill::getBillforGrade($grade, 1, 1, $term, $year, $stream);
          $this->view->otherbillables = Content_Model_TermBill::getAdditionalBillforGroup($groupkey, 0, 1, $term, $year, $stream);
		}
		try {
			$this->renderScript("templates/$identifier/entrybill.phtml");
		} catch (Exception $e) {
			$this->renderScript("templates/default/entrybill.phtml");
		}
    }

    public function termlybillAction()
    {
    	// action body
    	$this->getHelper('layout')->disableLayout();
    	$identifier = $this->_frontController->getInstance()->getParam('identifier');


    	$term = $this->_request->getParam('term');
    	$year = $this->_request->getParam('year');
    	$grade = $this->_request->getParam('grade');
    	$stream = $this->_request->getParam('stream');

    	//generate the bill for printing.....
    	$this->view->term = $term;
    	$this->view->year = $year;
    	$this->view->grade = $grade;
    	$this->view->stream = $stream;

    	$groupkey = Content_Model_FeeGroups::getGroupIDForGrade ( $grade );

    	if ($groupkey != - 1){
    		$this->view->termbillables = Content_Model_TermBill::getBillforGroup($groupkey, 1, 1, $term, $year, $stream);
    		$this->view->gradebillables = Content_Model_TermBill::getBillforGrade($grade, 1, 1, $term, $year, $stream);
    		$this->view->otherbillables = Content_Model_TermBill::getAdditionalBillforGroup($groupkey, 1, 1, $term, $year, $stream);
    	}

    	try {
    		$this->renderScript("templates/$identifier/termlybill.phtml");
    	} catch (Exception $e) {
    		$this->renderScript("templates/default/termlybill.phtml");
    	}
    }

    public function termlybillstudentsAction()
    {
    	// action body
    	$this->getHelper('layout')->disableLayout();
    	$identifier = $this->_frontController->getInstance()->getParam('identifier');

    	$term = $this->_request->getParam('term');
    	$year = $this->_request->getParam('year');
    	$grade = $this->_request->getParam('grade');
    	$id = $this->_request->getParam('id');
    	$stream = $this->_request->getParam('stream');

      	//generate the bill for printing.....
    	$this->view->term = $term;
    	$this->view->year = $year;
    	$this->view->grade = $grade;
    	$this->view->student = Content_Model_Students::getStudentName($id);
		$this->view->arrears = Content_Model_AccountBalances::getPriorArrears($id, $term, $year);
		$this->view->stream = $stream;

    	$groupkey = Content_Model_FeeGroups::getGroupIDForGrade ( $grade );

    	if ($groupkey != - 1){
    		$this->view->termbillables = Content_Model_TermBill::getBillforGroup($groupkey, 1, 1, $term, $year, $stream);
    		$this->view->gradebillables = Content_Model_TermBill::getBillforGrade($grade, 1, 1, $term, $year, $stream);
    		$this->view->otherbillables = Content_Model_TermBill::getAdditionalBillforGroup($groupkey, 1, 1, $term, $year, $stream);
    	}

    	try {
    		$this->renderScript("templates/$identifier/termlybillstudents.phtml");
    	} catch (Exception $e) {
    		$this->renderScript("templates/default/termlybillstudents.phtml");
    	}
    }

	public function showpayfeesAction(){
		// action body
		$currentTerm = Content_Model_School::getCurrentTermYear(false);

		if (!$currentTerm)
			$this->_redirect('/notermerror');

		$this->view->term = $currentTerm->term;
		$this->view->year = $currentTerm->year;
		$this->view->current = Content_Model_School::isCurrentPeriod($currentTerm->term, $currentTerm->year);

		$this->view->host = $_SERVER["HTTP_HOST"];
		$this->view->pdfhost = $this->getFrontController()->getParam('pdfhost');
	}

	public function showtranxpageAction(){
		// action body
		$currentPeriod = Content_Model_School::getCurrentTermYear(true, true);

		$this->view->term = $currentPeriod->term;
		$this->view->year  = $currentPeriod->year;

		//$transactions =  Content_Model_Transactions::getTermTransactions($this->view->term, $this->view->year, -1, true);
		//$this->view->current = Content_Model_School::isCurrentPeriod($currentPeriod->term, $currentPeriod->year);
		//$this->view->transactions = $transactions;
		$this->view->audit = true;
	}

	public function pastbillablesAction(){
		$this->getHelper('layout')->disableLayout();

		$this->view->pastbills = Content_Model_TermBill::getPastBills();

	}

	public function importbillsAction(){
		$this->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender(true);
		header('Content-Type: application/json');

		$term = $this->_request->getParam('term');

		if ($term == null)
			die(json_encode(array('status' => -200)));

		if ($this->_request->isXmlHttpRequest()){
			$result = Content_Model_TermBill::importpastbills($this->_request->getParam('term'),$this->_request->getParam('year'), $this->_request->getParam('next'));
		}

		echo json_encode(array('status' => $result));
	}

	public function expensesAction(){
		$this->view->form = new Content_Form_Expenses();
		$this->view->sendState = 'new';
	}

	public function addExpensesAction(){

		 $this->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender(true);

		$form = new Content_Form_Expenses();
		$this->view->sendState = 'new';

		if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('send') == 'new'){
			//saving new data
			if ($form->isValid($this->_request->getParams())){
				try {
					//$form->getValue($name)
					Content_Model_Expenses::addExpense($form->getValues());
					$this->getResponse()->setHttpResponseCode(202);
					$this->getResponse()->sendHeaders();
					die(json_encode(array('status' => 1)));
				} catch (Exception $e) {
					Zend_Debug::dump($e->getMessage());
				}
			}
		}
		else if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('send') == 'old'){
			//updating of existing record
			if ($form->isValid($this->_request->getParams())){
				try {

					$this->getResponse()->setHttpResponseCode(202);
					$this->getResponse()->sendHeaders();
					die(json_encode(array('status' => 1)));
				} catch (Exception $e) {
				}
			}
		}
		else  if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('update') == 'true')
		{
			$termbill = new Content_Model_TermBill();
			/*
			 $id = $this->_request->getParam('cl_id');
			$termbill = $termbill->find($id)->current();
			$form->populate($termbill -> toArray());
			//$form->gradelevels->setValue(Zend_Json::decode($termbill->gradelevels));
			$this->view->state = 'Bill Update';
			$this->view->sendState = 'old';*/
		}

		$this->view->form = $form;
		$this->view->viewform = new Content_Form_ViewBill2();
	}

	public function expenseSourceAction(){
		$this->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender(true);
		$type = $this->_request->getParam('type');
		$month = $this->_request->getParam('month');
		$transdate = $this->_request->getParam('transdate');

		$month = intval($month);
		$type = intval($type);

		if ($month == -11)
			$transdate = date('Y-m-d', time());
		else if (($month) == 0)
			$transdate = 'all';

		try {
			$expenses = Content_Model_Expenses::loadExpenses(
								$this->_request->getParam('iDisplayStart'),
								$this->_request->getParam('iDisplayLength'),
								$this->_request->getParam('sSearch'),
								$this->_request->getParam('iSortCol_0'),
								$this->_request->getParam('sSortDir_0'),
								$transdate,
								$type,
								$month);


			$output = array ("sEcho" => intval ($this->_request->getParam('sEcho')),
					"iTotalDisplayRecords" => $expenses['iDisplayRecords'],
					"iTotalRecords" => $expenses['iTotalRecords'],
					"aaData" => array () );

			foreach ($expenses['data'] as $expense) {
				$row = array();
				$row[] = $expense->etransdate;
				$row[] = $expense->description;
				$row[] = $expense->receivedby;
				$row[] = $expense->amount;
				$output['aaData'][] = $row;
			}
		} catch (Exception $e) {
			Zend_Debug::dump($e->getMessage());
		}

		echo json_encode($output);
	}

	public function imprestAction(){
		$this->view->form = new Content_Form_Imprest();
		$this->view->month = date('m',time());

		if ($this->_request->isXmlHttpRequest()){
			//send update or new ...
			$update = $this->_request->getParam('update');
			if ($update == true){
				$this->getHelper('layout')->disableLayout();
				$this->getHelper('viewRenderer')->setNoRender(true);
				$month = $this->_request->getParam('imonth');
				$amount = $this->_request->getParam('amount');
				$result = Content_Model_Imprest::updateImprest($month, date('Y',time()), $amount);
				echo json_encode(array('status' => $result));
			}
		}else{
			$this->view->imprests = Content_Model_Imprest::getImprests(date('Y',time()));
		}
	}

	public function deleteTermBill(){

	}
}

































































