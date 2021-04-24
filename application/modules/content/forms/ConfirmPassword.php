<?php

    class Content_Form_ConfirmPassword extends Zend_Validate_Abstract
    {
    	const MATCH  = 'match';
    	
    	protected $_messageTemplates = array(
    			self::MATCH  => "Password is incorrect"
    	);
    	
        public function isValid($value)
        {
            $this->_setValue($value);
     
            $isValid = false;
         
  			$iModel = new Content_Model_Instructors();
  			
  			$result = $iModel->find(Zend_Auth::getInstance()->getIdentity()->cl_IID)->current();
  			
  			if($result){
  				if (md5($value) == $result->password)
  					return true;
  			}
     
  			$this->_error(self::MATCH);
            return $isValid;
        }
    }

?>