<?php

class Content_Form_NewInstructor extends Twitter_Form  {
			//declaration of the various roles
			const ROLE_SU 	 = 200;
			const ROLE_ADMIN = 100;
			const ROLE_FRONTDESK = 40;
			const ROLE_REPORT   = 3;

	protected $gradeLevels =  '';

	public function init(){
	    $this->gradeLevels = Content_Model_GradeLevels::gradelevels();

		$this->setAttrib("horizontal", true);
		$this->setName('frm_newinstructor');

		//must be the first element in all forms
		$this->addElement('hidden', 'isValid', array("value"=>"false"));
        $this->addElement('hidden', 'cl_iid');

        //fname
        $this->addElement('text', 'firstname', array(
                    "label" => 'Given name',
                    "required" => true,
                    'filters' => array(
                                new Zend_Filter_Alpha(),
                                new Zend_Filter_StringTrim()
                            ),
                    'validators' => array(
                                new Zend_Validate_NotEmpty()
                            )
                ));

	    //surname
	           $this->addElement('text', 'lastname', array(
                    "label" => 'Family name',
                    "required" => true,
                    'filters' => array(
                                new Zend_Filter_Alpha(),
                                new Zend_Filter_StringTrim()
                            ),

                ));


	            //username
	            $validateUserName = new Zend_Validate_Db_NoRecordExists(array('table'=>'instructors',
	                                          'field'=>'username'));
	            $validateUserName->setMessage('The username not available, please provide another');

        	    $this->addElement('text', 'username', array(
                    "label" => 'Login name',
                    "required" => true,
                    'validators' => array(
                                $validateUserName
                            ),
        	        "filters" => array(new Zend_Filter_StringTrim())
                ));


        	   $this->addElement('password','password', array(
        	           'label'=>'Password',
        	           'required' => true,
        	           'validators' => array(
        	                   new Zend_Validate_NotEmpty()
        	           )
        	           ));

        	   $this->addElement('password','password2', array(
        	           'label'=>'Confirm Password',
        	           'required' => true,
        	           'validators' => array(
        	                   new Zend_Validate_NotEmpty()
        	           )
        	   ));


        	$this->addElement('select','role', array(
        	        ));


        	$this->addElement("select", "role", array(
        	        "label" => "Access Level/Role",
        	        "title" => "Security Level of the user",
        	        "multiOptions" =>  array(0 => ' --- Select Access Level --- ',
        	                50 => 'Instructor',
        	                100 => 'Principal',
        	                100 => 'Administrator',
        	                40 => 'Front Desk'),
        	        'validators' => array(new Zend_Validate_NotEmpty(Zend_Validate_NotEmpty::INTEGER + Zend_Validate_NotEmpty::ZERO))
        	));

        	$ctValidate = new Zend_Validate_Db_NoRecordExists(array('table'=>'instructors',
        	        'field'=>'cl_classteacher',
        			'exclude' => array(
        					'field' => 'cl_classteacher',
        					'value' => 1
        			)
        	));
        	$ctValidate->setMessage('Already assigned to another instructor');

        	$this->addElement("select", "cl_classteacher", array(
        	        "label" => "Class teacher for",
        	        "title" => "The class for which the teacher is responsible",
        	        "multiOptions" =>  array(0 => ' ---- Select Grade ---- ', 1 => 'Subject Teacher') + $this->gradeLevels,
        	        'validators' => array(
        	                        new Zend_Validate_NotEmpty(Zend_Validate_NotEmpty::INTEGER + Zend_Validate_NotEmpty::ZERO),
        	                        $ctValidate
        	                )
        	));


        	$mailValidator = new Zend_Validate_EmailAddress( array(
                                    'allow' => Zend_Validate_Hostname::ALLOW_DNS,
                                    'mx'    => false,
                                    'deep'  => false,

                                ));
        	$mailValidator->setMessage('An invalid mail server provided');

        	$this->addElement("text", "email", array(
        	         "label" => 'Email Address',
        	         "validators" => array(
                            $mailValidator
        	            ) ,
        	         "filters" => array(new Zend_Filter_StringTrim())
        	        ));


        $telValidator = new Zend_Validate_Regex('([(][0]{1}[\d]{2}[)][-][\d]{7}$)');
        $telValidator->setMessage('Tel no. provided is invalid');


        	$this->addElement("text","telno", array(
        	        "label" => 'Mobile no:',
        			"data-mask" => "(999)-9999999",
        	        "filters" => array(new Zend_Filter_StringTrim()),
        	        "validators" => array(
        	                              new Zend_Validate_NotEmpty(),
        	                              $telValidator,
        	                )

        	        ));


        	$this->addElement("button", "return",
        						array("label" => "Cancel",
        								"class" => "btn btn-small btn-danger",
        								"onclick" => "$('a[href=\"#listinstructors\"]').click();"));

	    $this->addElement("submit", "register",
	                array("label" => "Add Instructor",
	                      "class" => "btn btn-success",
	                      "disabled" => true));


	}



}
