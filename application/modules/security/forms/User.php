<?php


class Security_Form_User extends Twitter_Form  {

	//declaration of the various roles
	const ROLE_SU 	 = 0;
	const ROLE_ADMIN = 1;
	const ROLE_FRONTDESK = 2;
	const ROLE_REPORT   = 3;
	const ROLE_PRINCIPAL = 4;
	const ROLE_INSTRUCTOR = 5;

	public function init(){

		$this->addAttribs(array( 'style' => 'margin-left:10px; margin-right:20px;'));
		$this->setMethod('post');
		$this->setAction('/login/user/create');

		//create new element
		$id = $this->createElement('hidden', 'id');

		//element options
		$id->setDecorators(array('ViewHelper'));

		$this->addElement($id);

	    //create the form elements
	    $username = $this->createElement('text', 'username')->setLabel('Username')->setAttrib('required', 'required');

	    $password = $this->createElement('password', 'password')->setLabel('Password')->setAttrib('required', 'required');

	    $firstName = $this->createElement('text', 'firstname')->setAttrib('required', 'required')->setLabel('First Name')->setFilters(array('StripTags'));

	    $LastName = $this->createElement('text', 'lastname')->setLabel('Last Name')->setAttrib('required', 'required')->setFilters(array('StripTags'));


	    $role = $this->createElement('select', 'role');
	    			$role->setLabel('Select a role:')
	      			->addMultiOption( self::ROLE_SU, 'Super User')
	    			->addMultiOption( self::ROLE_ADMIN, 'Administrator')
	    			->addMultiOption( self::ROLE_FRONTDESK, 'Front Desk')
	     			->addMultiOption( self::ROLE_REPORT, 'Reporting')
	   				->addMultiOption( self::ROLE_PRINCIPAL, 'Principal')
	     			->addMultiOption( self::ROLE_INSTRUCTOR, 'Instructor');

	    $submit= new Zend_Form_Element_Submit('submit');
	    $submit->setValue('submit')->setAttribs(array('class'=>'btn btn-success pull-right', 'required' => 'required'));


	    $this->addElements(array($id, $username, $firstName, $LastName, $password,  $role, $submit));

	}



}
