<?php


class Content_Form_UpdateCourse extends Twitter_Form  {

	public function init(){

		$this->setAction('/course/update');
		$this->setName('frm_updatecourse');
		$this->setAttrib('horizontal', true);

		//create new element
		$this->addElement('hidden', 'isValid', array("value"=>"true"));
		$this->addElement('hidden','cl_courseid');

	    //create the form elements
		$cnValidate = new Zend_Validate_Db_RecordExists(array('table'=>'courses',
		        'field'=>'cl_coursename'));
		$cnValidate->setMessage('Subject does not exist');

	    $this->addElement('text','cl_coursename', array(
	            "label" => "Subject name",
	            "required" => true,
	            "filters" => array(new Zend_Filter_StringTrim()),
	            "validators" => array(new Zend_Validate_NotEmpty()),
	            ));

	    $this->addElement('multiselect', 'cl_gradelevels', array(
	            'label' => 'Taught in classes',
	            'required' => true,
	            'class' => 'chzn-select',
	            'registerInArrayValidator' => false,
	            'multioptions' => Content_Model_GradeLevels::gradelevels()
	            ));

	    $this->addElement('multiselect', 'cl_gradeexempt', array(
	            'label' => 'Exempt from exams',
	            'class' => 'chzn-select',
	            'registerInArrayValidator' => false,
	            'multioptions' => Content_Model_GradeLevels::gradelevels()
	    ));

	    $this->addElement('select','cl_examinable',array(
	            'label' => 'Examinable',
	            'multioptions' => array(1=>'Yes', 0=>'No'),
	            ));


	    $this->addElement('select','cl_shared',array(
	            'label' => 'Multiple Instructors',
	            'multioptions' => array( 0=>'No', 1=>'Yes'),
	    ));

	    $this->addElement('text','cl_courseorder', array(
	            'label' => 'Order on report',
	            'value' => 0,
	            'required' => true,
	            'filters'=> array(new Zend_Filter_StringTrim()),
	            'validators' => array(new Zend_Validate_Digits(),
	                                  new Zend_Validate_GreaterThan(-1))
	            ));

	    $this->addElement("button", "return",
	            array("label" => "Cancel",
	                    "class" => "btn btn-small btn-danger",
	                    "onclick" => "$('a[href=\"#listsubjects\"]').click();"));

	    $this->addElement("submit", "register",
	    					array("label" => "Add Subject",
	    							"class" => "btn btn-success",
	    					));

	}



}

