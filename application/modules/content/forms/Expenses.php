<?php

class Content_Form_Expenses extends Twitter_Form {

	public function init() {

		$this->setAttrib ( 'horizontal', true );
		$this->setName ( 'frm_expenses' );

		// must be the first element in all forms
		$this->addElement ( 'hidden', 'isValid', array (
				"value" => "false" ) );
		$this->addElement ( 'hidden', 'cl_id' );

		// DOB
		$this->addElement (
							'text',
							'etransdate',
							array (
									'label' => 'Date',
									'data-mask' => '99-99-2099',
									'required' => true,
									'value' => Zend_Date::now ()->toString ( 'dd-M-Y' ),
									'data-date-format' => 'dd-mm-yyyy',
									'validators' => array (
											new Zend_Validate_Date ( array (
													'format' => 'dd-mm-yyyy' ) ) ) ) );

		$this->addElement (
							'text',
							'description',
							array ( 'autocomplete' => 'off',
									'label' => 'Description',
									'required' => true,
									'class' => 'span12',
									'placeholder' => 'Details of Expenditure' ) );

		$this->addElement (
							'text',
							'amount',
							array (
									'label' => 'Amount paid',
									'class' => 'number',
									'placeholder' => '0',
									'required' => true,
									'validators' => array (
											new Zend_Validate_NotEmpty (),
											new Zend_Validate_GreaterThan ( 0 )
											  ) ) );
		$cc = new Zend_Validate_Identical ( 'amount' );
		$cc->setMessage ( 'Must match amount paid' );

		$this->addElement (
							'text',
							'Pconfirmamount',
							array (
									'label' => 'Confirm amount',
									'required' => true,
									'class' => 'number',
									'placeholder' => '0',
									'validators' => array (
											new Zend_Validate_NotEmpty (),
											new Zend_Validate_GreaterThan ( 0 ),

											$cc ) ) );

		$this->addElement (
							'select',
							'paymentmode',
							array (
									'label' => 'Mode of payment',
									'required' => true,
									'multioptions' => array (
											0 => 'Cash payment',
											1 => 'Cheque/Deposit' ) ) );

		$this->addElement (
							'text',
							'chequeno',
							array (
									'label' => 'Cheque/Deposit Slip No.' ) );

		// firstname
		$this->addElement (
							'text',
							'receivedby',
							array (
									'label' => 'Recipient',
									'placeholder' => 'Name of Vendor or Recipient',
									'required' => true,
									'filters' => array (
											new Zend_Filter_StringTrim () ),
									'validators' => array (
											new Zend_Validate_NotEmpty () ) ) );

		$this->addElement (
							"button",
							"return",
							array (
									"label" => "Cancel",
									"class" => "btn btn-small btn-danger",
									"onclick" => "window.location.href='/home'"));

		$this->addElement (
							"submit",
							"btnexpense",
							array (
									"label" => "Submit for Authorization",
									"class" => "btn btn-small btn-success",
									"disabled" => true ) );

	}
}