<?php


class Content_Form_ClassSubjectAssign extends Twitter_Form  {
			//declaration of the various roles
			const ROLE_SU 	 = 0;
			const ROLE_ADMIN = 1;
			const ROLE_FRONTDESK = 2;
			const ROLE_REPORT   = 3;
			const ROLE_PRINCIPAL = 4;
			const ROLE_INSTRUCTOR = 5;

	protected $gradeLevels =  '';

	public function init(){

		$this->setAction('/instructor/assign-add');
		$this->setAttrib("horizontal", true);
		$this->setName('frm_assingadd');

		//create new element
		$this->addElement('hidden', 'isValid', array("value"=>"false"));
		$this->addElement('hidden', 'cl_inst_id');

        $this->addElement('select', 'cl_courses', array(
                'label' => 'Subject to Assign',
                'multiOptions' => array(0=> '--- Select Subject ---') + Content_Model_Course::getCourseNames(),
                'onchange' => '$.fn.getAvailableClasses($("#cl_courses").val());',
                'validators' => array(new Zend_Validate_NotEmpty(Zend_Validate_NotEmpty::INTEGER + Zend_Validate_NotEmpty::ZERO))
                ));

        $this->addElement('multiselect', 'cl_grades', array(
                    'label' => 'Unassigned classes',
                    'required' => true,
                    'style' => 'height : 200px',
                    'registerInArrayValidator' => false
                ));
        $this->addElement("button", "return",
        					array("label" => "Cancel",
        							"class" => "btn btn-small btn-danger",
        							"onclick" => "$('a[href=\"#listinstructors\"]').click();"));


	    $this->addElement("submit", "register",
	            array("label" => "Assign Class and Subject",
	                    "class" => "btn btn-success",
	                    "disabled" => true,
	                    'onclick' => '$.fn.sendAssignForm(); return false;'));


	}
}
