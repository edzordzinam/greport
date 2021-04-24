<?php

class Content_Form_Imprest extends Twitter_Form {

	public function init() {

		/* Form Elements & Other Definitions Here ... */

		$this->setAttrib ( 'horizontal', true );
		$this->setName ( 'frm_imprest' );

		// must be the first element in all forms
		$this->addElement ( 'hidden', 'isValid', array (
				"value" => "false" ) );
		$this->addElement ( 'hidden', 'cl_id' );

		$this->addElement (
							'select',
							'imonth',
							array (
									'label' => 'Month of Imprest',
 									'multioptions' => array(
											null => '--- Indicate Month ---',
											1 => 'January',
											2 => 'February',
											3 => 'March',
											4 => 'April',
											5 => 'May',
											6 => 'June',
											7 => 'July',
											8 => 'August',
											9 => 'September',
											10 => 'October',
											11 => 'November',
											12 => 'December'),
 											'validators' => array(new Zend_Validate_NotEmpty())));

		$this->addElement (
							'text',
							'amount',
							array (
									'label' => 'Imprest Amount',
									'class' => 'span4',
									'placeholder' => 0000,
									'required' => true,
									'validators' => array(new Zend_Validate_GreaterThan(0))));

		$this->addElement('text', 'iyear', array(
							'label' => 'Year',
							'class' => 'span4',
							'disabled' => true,
							'value' => date('Y', time())
						));

		$this->addElement("button", "return",
							array("label" => "Cancel",
									"class" => "btn btn-small btn-danger",
									"onclick" => "window.location.href='/home'"));

		$this->addElement("submit", "btndiscount",
							array("label" => "Update Imprest",
									"class" => "btn btn-success",
									"disabled" => true,
									"onclick" => '$.fn.updateImprest(); return false;'
							));

	}
}