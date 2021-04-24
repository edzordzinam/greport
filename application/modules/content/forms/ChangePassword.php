<?php
 

class Content_Form_ChangePassword extends Twitter_Form  {
			//declaration of the various roles
					
	public function init(){
				
		$this->setAttrib('horizontal', true);
		$this->setName('frm_updatepass');

		$this->addElement('password', 'currentpwd', array(
					'label' => 'Current password',
				    'validators' => array(new Content_Form_ConfirmPassword())
				));
		
		$this->addElement('password','newpwd', array(
					'label' => 'New password',
					'required' => true,
					'validators' => array(new Content_Form_PasswordStrength())
				));
		
		$mv = new Zend_Validate_Identical('newpwd');
		$mv->setMessage('New password mismatch');
		
		$this->addElement('password', 'matchpwd',array(
				'label' => 'Confirm password',
				'required' => true,
				'validators' => array($mv)
		));
 
	
		$this->addElement("button", "return",
				array("label" => "Cancel",
						"class" => "btn btn-danger btn-small",
						"data-dismiss" => "modal",
						"aria-hidden" => "true"
						
				));
		
		$this->addElement("submit", "btndiscount",
				array("label" => "Update password",
						"class" => "btn btn-success",
						"disabled" => true,
						"onclick" => "$.fn.updatePassword(); return false;",
				));
	}
	
	
}
