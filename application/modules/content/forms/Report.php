<?php

class Content_Form_Report extends EasyBib_Form
{
    public function init()
    {
        $submit      = new Zend_Form_Element_Button('submit');


        $this->addElements(array($submit));
        $this->addError('this');

         EasyBib_Form_Decorator::setFormDecorator($this, EasyBib_Form_Decorator::BOOTSTRAP, 'submit', 'cancel');
    }

}