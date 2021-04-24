<?php

/**
 * This is the form for the creation of assignments ....
 *
 *@author Edzordzinam
 *
 */
class Content_Form_Assignment extends Twitter_Form {

	protected $terms = array(1=>'1st Term', 2 => '2nd Term', 3 =>'3rd Term');

	protected $gradeLevels = '';

	public function init(){

	    $this->setAction('/newassessment');
	    $this->setAttrib("horizontal", true);
	    $this->setName('frm_newassessment');
	    $this->gradeLevels = Content_Model_GradeLevels::gradelevels();
	    $identity = Zend_Auth::getInstance()->getIdentity();


	    //create new element
	    $this->addElement('hidden', 'isValid', array("value"=>"false"));
	    $this->addElement('hidden', 'cl_id');

		//adding assignment id
        $grades = Content_Model_Instructors::taughtGrades($identity->cl_IID, false);
        $ngrades = array();

        foreach ($grades as $key => $value) {
            $ngrades[$value] = $this->gradeLevels[$value];
        }

 		$this->addElement('select','cl_grade', array(
		        'label' => 'Class/Grade',
 		        'required' => true,
		        'multioptions' => array(null => '--- Select Class ---') + $ngrades,
 		        'registerInArrayValidator' => false,
 		        'onchange' => "$.fn.GetClassCourses($identity->cl_IID, $('#cl_grade option:selected').val());",
 		        'validators' => array(new Zend_Validate_NotEmpty())
 		       ));

 		$this->addElement('select','cl_course', array(
                'label' => 'Subject',
 		        'required' => true,
 		        'registerInArrayValidator' => false,
 		        'multioptions' => array(null => '--- Select a class above ---'),
 		        'validators' => array(new Zend_Validate_NotEmpty()),
 		        'onchange' => 'javascript:$("#cl_topic").val("");'
 		        ));

 		$this->addElement('select', 'cl_type', array(
 		        'label' => 'Assessment Type',
 		        'onchange' => '$.fn.generateTopic($("#cl_type").val());',
 		        'required' => true,
 		        'registerInArrayValidator' => false,
 		        'multioptions' => array(null => '-- assessment type --') + Custom_AssignmentTypes::Types(),
 		        'validators' => array(new Zend_Validate_NotEmpty())
 		        ));


 		$this->addElement('text','cl_topic', array(
 		        'label' => 'Subject Topic',
 		        'required' => true,
 		        'validators' => array(new Zend_Validate_NotEmpty(),
 		                              new Zend_Validate_StringLength(array('max' => '60'))),
 		        'filters' => array(new Zend_Filter_StringTrim())
 		        ));


 		$currentPeriod = Content_Model_School::getCurrentTermYear();

 		$this->addElement('select','cl_term', array(
 		        'label' => 'Term',
 		        'value' => $currentPeriod->term,
 				'disabled' => 'disabled',
 		        'multioptions' => $this->terms,
 		       // 'required' => true,
 		        'validators' => array(new Zend_Validate_NotEmpty())
 		        ));

 		$this->addElement('text','cl_year', array(
 		        'label' => 'Academic Year',
 		        'value' => $currentPeriod->year,
 		        'data-mask' => '2099/2099',
 		        //'required' => true,
 				'disabled' => 'disabled',
 		        ));

 		$this->addElement('text','cl_date', array(
 		        'label' => 'Date Assigned',
 		        //'data-mask' => '9999-99-99',
 		        'required' => true,
 		        'value' =>  Zend_Date::now()->toString('Y-M-d'),
 		        'data-date-format' => 'yyyy-mm-dd',
 		        'validators' => array(new Zend_Validate_Date(array('format'=>'yyyy-mm-dd')))
 		));

 		$this->addElement('text','cl_maxmark',array(
 		        'label' => 'Maximum mark',
 		        'data-mask' => '?999',
 		        'data-placeholder' => ' ',
 		        'required' => true,
 		        'validators' => array(new Zend_Validate_NotEmpty(),
 		                            new Zend_Validate_GreaterThan(0))
 		        ));


		$this->addElement("button", "return",
		        array("label" => "Cancel",
		                "class" => "btn btn-small btn-danger",
		                "onclick" => "$('a[href=\"#assessments\"]').click();"));

		$this->addElement("submit", "register",
							array("label" => "Add Assessment",
									"class" => "btn btn-success",
									"disabled" => true));

	}

}

?>