<?php

class Content_Form_PayFees extends Twitter_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */

        $this->setAttrib('horizontal', true);
        $this->setName('frm_payfees');

        //must be the first element in all forms
        $this->addElement('hidden', 'isValid', array("value"=>"false"));
        $this->addElement('hidden', 'Pcl_id');

        $this->addElement('text','Pstudentname', array(
                'label' => 'Student\'s name',
                'disabled' => true
                ));

        $this->addElement('select','Pgradelevel', array(
                'label' => 'Class',
                'disabled' => true,
                'multioptions' => Content_Model_GradeLevels::gradelevels()
        ));

        $this->addElement('text', 'arrears', array(
        				'label' => 'Total arrears',
        				'disabled' => true,
        				'validators' => array(new Zend_Validate_NotEmpty(),
        						new Zend_Validate_GreaterThan(0))
        		));

        $this->addElement('text', 'Pamount', array(
                'label' => 'Amount paid',
                'class' => 'number',
                'placeholder' => '0',
                'required' => true,
                'validators' => array(	new Zend_Validate_NotEmpty(),
                                    	new Zend_Validate_GreaterThan(0))
                ));
        $cc = new Zend_Validate_Identical('Pamount');
        $cc->setMessage('Must match amount paid');

        $this->addElement('text', 'Pconfirmamount', array(
        		'label' => 'Confirm amount',
        		'required' => true,
        		'class' => 'number',
                'placeholder' => '0',
        		'validators' => array(	new Zend_Validate_NotEmpty(),
        								new Zend_Validate_GreaterThan(0),
        								$cc)
        ));

        $this->addElement('select','Ppaymode', array(
        		'label' => 'Mode of payment',
        		'required' => true,
        		'multioptions' => array( 0 => 'Cash payment', 1 => 'Cheque/Deposit'),
        		));

        $this->addElement('text','Pslipno',array(
        		'label' => 'Cheque/Deposit Slip No.'
        		));


        $this->addElement("button", "return",
                array("label" => "Cancel",
                  		"class" => "btn btn-danger btn-small",
						"data-dismiss" => "modal",
						"aria-hidden" => "true"
                		));

        $this->addElement("submit", "btnpayfees",
        					array("label" => "Make Payment",
        							"class" => "btn btn-success",
        							"disabled" => true,
        							"onclick" => '$.fn.makePayment(); return false;'
        					));


       }


}

