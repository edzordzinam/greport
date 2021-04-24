<?php

    class Content_Form_PasswordStrength extends Zend_Validate_Abstract
    {
        const LENGTH = 'minLength';
        const UPPER  = 'upper';
        const LOWER  = 'lower';
        const DIGIT  = 'digit';
     
        protected $_messageTemplates = array(
            self::LENGTH => "minimum length is 6 characters",
            self::UPPER  => "must contain at least one uppercase letter; <br> ",
            self::LOWER  => "must contain at least one lowercase letter; <br>",
            self::DIGIT  => "must contain at least one digit character <br> "
        );
     
        public function isValid($value)
        {
            $this->_setValue($value);
     
            $isValid = true;
         
            if (!preg_match('/[A-Z]/', $value)) {
                $this->_error(self::UPPER);
               $isValid = false;
            }
     
            if (!preg_match('/[a-z]/', $value)) {
                $this->_error(self::LOWER);
                $isValid = false;
            }
     
            if (!preg_match('/\d/', $value)) {
                $this->_error(self::DIGIT);
                 $isValid = false;
            }
            
            if (strlen($value) < 6){
            	$this->_error(self::LENGTH);
            	$isValid = false;
            }
     
            return $isValid;
        }
    }

?>