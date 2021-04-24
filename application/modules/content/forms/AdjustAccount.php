<?php

class Content_Form_AdjustAccount extends Twitter_Form{
	
	public function init(){

		$this->setAttrib('horizontal', true);
		$this->setName('frm_adjustaccount');
		$this->setAction('/adjustaccount');
		
		$this->addElement('hidden', 'isValid', array("value"=>"false"));
		
		$this->addElement('select', 'liststudents', array(
					'label' => 'Student Name',
					'class' => 'chzn-select',
					'style' => 'width : 300px !important;',
					'required' => true,
					'multioptions' => Content_Model_Students::studentsInAllGrade()
				));

		$this->addElement('select','adjType', array(
				'label' => 'Type of Adjustment',
				'required' => true,
				'multioptions' => array( null => '--- Treat transaction as ---', 0 => 'Debit/Addition', 1 => 'Credit/Subtraction'),
		));
		
		$this->addElement('text', 'adjAmount', array(
                'label' => 'Adjusting Amount',
                'class' => 'number',
                'placeholder' => '0',
                'required' => true,
                'validators' => array(	new Zend_Validate_NotEmpty(),
                                    	new Zend_Validate_GreaterThan(0))
                ));
		
        $cc = new Zend_Validate_Identical('adjAmount');
        $cc->setMessage('Must match adjusting amount');
        
        $this->addElement('text', 'confirmAdjAmount', array(
        		'label' => 'Confirm amount',
        		'required' => true,
        		'class' => 'number',
                'placeholder' => '0',
        		'validators' => array(	new Zend_Validate_NotEmpty(),
        								new Zend_Validate_GreaterThan(0), 
        								$cc)
        ));

        $this->addElement('text', 'reason', array(
        		'label' => 'Reason for adjustment',
        		'required' => true,
        		'validators' => array(new Zend_Validate_StringLength(array('min'=>10, 'max'=>50)))
        ));
                
        $this->addElement("button", "return",
        		array("label" => "Cancel",
        				"class" => "btn btn-danger btn-small",
        				"data-dismiss" => "modal",
        				"aria-hidden" => "true"
        		));
        $this->addElement("submit", "btnAdjust",
        		array("label" => "Update Account",
        				"class" => "btn btn-success",
        				"disabled" => true,
        				"onclick" => '$.fn.adjAccount(); return false;'
        		));
        
        
	}
	
}

?>