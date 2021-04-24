<?php

class Content_Form_Syllabus extends Twitter_Form
{
	protected $terms = array(1=>'1st Term', 2 => '2nd Term', 3 =>'3rd Term');
	
    public function init()
    {
        /* Form Elements & Other Definitions Here ... */

    	$this->setName('frm_syllabus');
    	$this->setAttrib('horizontal', true);
    	
    	//create new element
    	$this->addElement('hidden', 'isValid', array("value"=>"false"));
    	$this->addElement('hidden','cl_id');
    	
    	$this->addElement('text', 'section',array(
    		'label' => 'Syllabus Section',
    		'class' => 'input-xlarge',
    		'required' => true,
    		'validators' => array(new Zend_Validate_NotEmpty()),
    		'filters' => array(new Zend_Filter_StringTrim())	
    	));
    	
    	
 		$currentPeriod = Content_Model_School::getCurrentTermYear();

 		$this->addElement('select','term', array(
 		        'label' => 'Term',
 		        'value' => $currentPeriod->term,
 				'disabled' => 'disabled',
 		        'multioptions' => $this->terms,
 		       // 'required' => true,
 		        'validators' => array(new Zend_Validate_NotEmpty())
 		        ));

 		$this->addElement('text','year', array(
 		        'label' => 'Academic Year',
 		        'value' => $currentPeriod->year,
 		        'data-mask' => '2099/2099',
 		        //'required' => true,
 				'disabled' => 'disabled',
 		        ));
 		
 		$this->addElement('select', 'status', array(
 				'label' => 'Status',
 				'multioptions' => array(null=>'Select Status', 0 => 'Pending' , 1=> 'Completed', 2 => 'In-Progress'),
 				'required' => true,
 				'validators' => array(new Zend_Validate_NotEmpty())
 				));
 		
 		$this->addElement("button", "return",
 				array("label" => "Cancel",
 						"class" => "btn btn-danger btn-small",
 						"data-dismiss" => "modal",
 						"aria-hidden" => "true"
 		
 				));
 		
 		$this->addElement("submit", "btndiscount",
 				array("label" => "Update Syllabus",
 						"class" => "btn btn-success",
 						"disabled" => true,
 						"onclick" => "$.fn.updateSyllabus(); return false;",
 				));
 		
    }


}

