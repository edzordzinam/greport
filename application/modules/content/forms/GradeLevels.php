<?php

class Content_Form_GradeLevels extends Twitter_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
    	$this->setAttrib('horizontal', true);
    	$this->setAction('/updategradelevels');
    	$this->setName('frm_gradelevels');
    	
    	//create new element
		$this->addElement('hidden', 'isValid', array("value"=>"false"));
		$this->addElement('hidden','cl_id');
		
		$this->addElement('text','gradename',array(
				'label' => 'Grade/Class/Level',
				'required' =>true,
				'placeholder' => 'Enter name of class',
				'validators' => array(new Zend_Validate_NotEmpty()),
				'filters' => array(new Zend_Filter_StringTrim()),
		));
		
		$this->addElement('select','preceed',array(
				'label' => 'Preceeding Classes',
				'required' => true,
				'multiple' => 'multiple',
				'class' => 'chzn-select',
				'registerInArrayValidator' => false,
				'multioptions' => Content_Model_GradeLevels::getUnassignedPreceed(),
				'placeholder' => 'Select preceeding class',
				))	;

		$this->addElement('text','capacity', array(
				'label' => 'Class Capacity',
				'data-mask' => '999',
				'data-placeholder' => '0',
				'value' => '020',
				'required' => true,
				'validators' => array(new Zend_Validate_NotEmpty(), new Zend_Validate_GreaterThan(0))				
				));

		
		$this->addElement("button", "return",
				array("label" => "Cancel",
						"class" => "btn btn-small btn-danger",
						"data-dismiss" => "modal",
						"aria-hidden" => "true"
			));		
		
		$this->addElement("submit", "register",
				array("label" => "Register Class",
						"class" => "btn btn-success",
						'onclick' => '$.fn.addNewClass(); return false;',
						"data-dismiss" => "modal",
						"aria-hidden" => "true",
						"disabled" => true));
		
    }


}

