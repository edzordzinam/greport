<?php

class Content_SchoolController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
        $this->view->form = new Content_Form_TermDates();
        $nextterm = $this->_request->getParam('nextterm');
        if (isset($nextterm) && $nextterm == 1)
        	$this->view->nextterm = true;
    }

    public function setupTermAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $form = new Content_Form_TermDates();


        if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('send') == 'true'){
             if ($form->isValid($this->_request->getParams())){
                Content_Model_School::newTermDates(
                        $this->_request->getParam('term'),
                        $this->_request->getParam('year'),
                        $this->_request->getParam('cl_startdate'),
                        $this->_request->getParam('cl_enddate'),
                        $this->_request->getParam('holidays'));

                $this->getResponse()->setHttpResponseCode(202);
                $this->getResponse()->sendHeaders();
                die(json_encode(array('status'=>1)));
             }
        }

        $this->view->form = $form;
    }

    public function listTermDatesAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->view->termdates = Content_Model_School::listtermdates();
    }

    public function schoolConfigAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();

        $form = new Content_Form_SchoolConfig();

        if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('send') == 'true'){
                //updating data
             if ($form->isValid($this->_request->getParams())){
                 Content_Model_School::saveConfigData($form->getValues());
                 die(json_encode(array('status'=>1)));
             }
        }
        else{
                //loading data
            $configData = Content_Model_School::loadConfigData();
            $form->populate($configData);
            $this->view->schoollogo = Content_Model_School::getSchoolLogo();
        }

        $this->view->form = $form;
    }

    public function setPeriodContextAction()
    {
    	//This is the action for the setting of the Term & Year context in which the application runs

        // action body

    	$url = parse_url($_SERVER["HTTP_REFERER"]);

    	$current = $this->_request->getParam('current');

        if ($current != 1){
    	        $Term = $this->_request->getParam('Term');
       			$Year = $this->_request->getParam('Year');

	        $Auth = Zend_Auth::getInstance();
	        $Auth_Storage = $Auth->getStorage();
	        $Data = $Auth_Storage->read();
	        $CurrentTerm = array('Term' => $Term, 'Year' => $Year);

	        $Data->CurrentContext = $CurrentTerm;

	        $Auth_Storage->write($Data);

	        $this->view->term = $Term;
	        $this->view->year = $Year;
        }else{
        	$Auth = Zend_Auth::getInstance();
        	$Auth_Storage = $Auth->getStorage();
        	$Data = $Auth_Storage->read();

        	if (isset($Data->CurrentContext))
        		unset($Data->CurrentContext);

        	$Auth_Storage->write($Data);
        }

        if (!($url['path'] == '/notermerror'))
        	$this->_redirect($url['path']);
        else{
        	$this->_redirect($_SESSION['redirect']);
        }

    }

    public function setupClassesAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
    }

    public function getClassesListAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);

    	$gradelevels = Content_Model_GradeLevels::getClassesListSource(
    			$this->_request->getParam('iDisplayStart'),
    			$this->_request->getParam('iDisplayLength'),
    			$this->_request->getParam('sSearch'),
    			$this->_request->getParam('iSortCol_0'),
    			$this->_request->getParam('sSortDir_0')
    	);

    	$output = array ("sEcho" => intval ($this->_request->getParam('sEcho')),
    			"iTotalDisplayRecords" => $gradelevels['iDisplayRecords'],
    			"iTotalRecods" => $gradelevels['iTotalRecords'],
    			"aaData" => array () );

    	//transformation of the array
   	foreach ($gradelevels['data'] as $grade) {
    		$row = array();
    		$row[] = $grade->gradename;
    		$row[] = $grade->preceedname;
    		$row[] = $grade->capacity;
    		$output['aaData'][] = $row;
    	}

    	echo json_encode($output);
    }

    public function newGradelevelAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->view->form = new Content_Form_GradeLevels();

     	$commit = $this->_request->getParam('commit');

        if ($commit){
        	$gradename = $this->_request->getParam('gradename');
        	$preceed = $this->_request->getParam('preceed');
        	$capacity = $this->_request->getParam('capacity');
        	Content_Model_GradeLevels::addGradeLevel($gradename, $preceed, $capacity);
        }

        //Zend_Debug::dump(Content_Model_GradeLevels::getUnassignedPreceed());

    }

    public function markGradingAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->view->grading = Content_Model_Reports::gradingSystem();
    }

    public function uploadlogoAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);

    	$adapter = new Zend_File_Transfer_Adapter_Http();

    	$destionation = APPLICATION_PATH . "/../php/img/logos";

    	//$adapter->setDestination($destionation);
    	$filename = mt_rand() . ".jpg";

    	$adapter->addFilter('Rename',array('target'=> "$destionation/$filename"));

    	if (!$adapter->receive()) {
    		$messages = $adapter->getMessages();
    		echo implode("\n", $messages);
    	}

    	//save file name to database
    	$data = array('schoollogo' => $filename);

    	if (file_exists(Content_Model_School::getSchoolLogo(true)))
    		unlink(Content_Model_School::getSchoolLogo(true));

    	Content_Model_School::saveConfigData($data);

    	echo "Logo successfully uploaded";
    }



}



















