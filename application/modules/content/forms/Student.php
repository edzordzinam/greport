<?php

class Content_Form_Student extends Twitter_Form
{

    protected $gradeLevels = '';

    public function init()
    {
	    $this->gradeLevels = Content_Model_GradeLevels::gradelevels();

        /* Form Elements & Other Definitions Here ... */
	    $this->setName('frm_student');
	    $this->setAttrib("horizontal", true);


        //must be the first element in all forms
        $this->addElement('hidden', 'isValid', array("value"=>"false"));
        $this->addElement('hidden', 'cl_GPSN_ID');

        $this->addElement('select','billStudent',array(
        		'label' => 'Billing',
        		'required' => true,
        		'multioptions' => array(null => '--- Treat student as ---',  1 => "New Admission", 0 => "Existing Student Record"),
        		'validators' => array(new Zend_Validate_NotEmpty())
        ));

        //firstname
        $this->addElement('text','cl_FirstName', array(
                    'label' => 'Given names',
                    'required' => true,
                    'filters' => array(new Zend_Filter_StringTrim()),
                    'validators' => array(new Zend_Validate_NotEmpty()
                                          )
                ));

        //firstname
        $this->addElement('text','cl_LastName', array(
                'label' => 'Surname',
                'required' => true,
                'filters' => array(new Zend_Filter_StringTrim()),
                'validators' => array(new Zend_Validate_NotEmpty())
        ));


        //DOB
        $this->addElement('text','cl_DOB', array(
                   'label' => 'Date of Birth',
                   'data-mask' => '99-99-9999',
                   'required' => true,
                   'value' =>  Zend_Date::now()->toString('d/M/Y'),
                   'data-date-format' => 'dd/mm/yyyy',
                    'validators' => array(new Zend_Validate_Date(array('format'=>'dd/mm/yyyy')))
                ));

        //POB
        $this->addElement('text','cl_POB', array(
                'label' => 'Place of Birth',
                'required' => true,
                'filters' => array(new Zend_Filter_StringTrim()),
                'validators' => array(new Zend_Validate_NotEmpty(),
                        new Zend_Validate_Alpha())
        ));

        //cl_Gender
        $this->addElement('select','cl_Gender',array(
                'label' => 'Gender',
                'required' => true,
                'multioptions' => array(null => '--- Specify gender ---',  '0' => "Female", 1 => "Male"),
                'validators' => array(new Zend_Validate_NotEmpty())
        ));

        //cl_Resident
        $this->addElement('select', 'cl_Resident',array(
        		'label' => 'Residency Status',
        		'required' => true,
        		'multioptions' => array(null => '-- Specify Status --', '0' => 'Non-Res./Day Student', '1' => 'Resident/Boarder'),
        		'validators' => array(new Zend_Validate_NotEmpty())
        ));


        $this->addElement('select', 'cl_PrimaryContact', array(
       			'label' => 'Primary Contact',
        		'required' => true,
        		'multioptions' => array(null => '-- for SMS/Email Alerts --', '0' => 'Father', '1' => 'Mother', '2' => 'Guardian'),
        		'validators' => array(new Zend_Validate_NotEmpty())
       ));

        //cl_GradeLevel
        $this->addElement('select','cl_GradeLevel',array(
                'label' => 'Class/Grade',
                'required' => true,
                'multioptions' => array(null => "--- Select entry class ---") + $this->gradeLevels,
                'validators' => array(new Zend_Validate_NotEmpty())
                ));


        $mailValidator = new Zend_Validate_EmailAddress( array(
                'allow' => Zend_Validate_Hostname::ALLOW_DNS,
                'mx'    => false,
                'deep'  => false,

        ));
        $mailValidator->setMessage('An invalid mail server provided');

        $this->addElement("hidden", "cl_ContactEmail", array(
                "label" => 'Parent\'s Email Address',
                "validators" => array(
                        $mailValidator
                ) ,
                "filters" => array(new Zend_Filter_StringTrim())
        ));


        $telValidator = new Zend_Validate_Regex('([(][0]{1}[\d]{2}[)][-][\d]{7}$)');
        $telValidator->setMessage('Tel no. provided is invalid');


        $this->addElement("hidden","cl_ContactTel", array(
                "label" => 'Parent\'s Mobile no:',
                "data-mask" => "(999)-9999999",
                "filters" => array(new Zend_Filter_StringTrim()),
                "validators" => array(
                        new Zend_Validate_NotEmpty(),
                        $telValidator)
                )
        );

        $this->addElement("button", "return",
        					array("label" => "Cancel",
        							"class" => "btn btn-small btn-danger",
        							"onclick" => "$('a[href=\"#listcurrentstudents\"]').click();"));


        $this->addElement("submit", "register",
                array("label" => "Register Student",
                        "class" => "btn btn-small btn-success",
                        "disabled" => true));



    }
}

