<?php

class Content_Form_TermDates extends Twitter_Form  {

    public function init(){

        $this->setAttrib('horizontal', true);
        $this->setName('frm_termdates');

        //must be the first element in all forms
        $this->addElement('hidden', 'isValid', array("value"=>"false"));
        $this->addElement('hidden', 'cl_id');

        $this->addElement('select','term', array(
                    'label' => 'Academic Term',
                    'multioptions' => array(0 => '--- select term ---',
                                            1=> '1st Term',
                                            2=> '2nd Term',
                                            3=> '3rd Term'),
                    'required' => true,
                    'validators' => array(new Zend_Validate_NotEmpty(Zend_Validate_NotEmpty::INTEGER + Zend_Validate_NotEmpty::ZERO))
                ));

        $this->addElement('text','year', array(
                    'data-mask' => '2099/2099',
                    'label' => 'Academic Year',
                    'required' => true,
                    'title' => '20XX/20YY',
                    'pattern' => '\d{4}[/]\d{4}$',
                    'filters' => array(new Zend_Filter_StringTrim()),
                    'validators' => array(new Zend_Validate_NotEmpty())
                ));

        $this->addElement('text','cl_startdate', array(
                   'label' => 'Term Start Date',
                   'data-mask' => '99-99-9999',
                   'required' => true,
                   'value' =>  Zend_Date::now()->toString('d-M-Y'),
                   'data-date-format' => 'dd-mm-yyyy',
                    'validators' => array(new Zend_Validate_Date(array('format'=>'dd-mm-yyyy')))
                ));


        $this->addElement('text','cl_enddate', array(
                'label' => 'Term End Date',
                'data-mask' => '99-99-9999',
                'required' => true,
                'value' => Zend_Date::now()->toString('d-M-Y'),
                'data-date-format' => 'dd-mm-yyyy',
                'validators' => array(new Zend_Validate_Date(array('format'=>'dd-mm-yyyy')))
        ));


        $this->addElement('text','holidays', array(
                'label' => 'No. of Holidays',
                'class' => 'maskholiday',
                'required' => true,
                'value' => 0,
                'filters' => array(new Zend_Filter_StringTrim()),
                'validators' => array(new Zend_Validate_NotEmpty(),
                                      new Zend_Validate_Digits(),
                                      new Zend_Validate_GreaterThan(-1))
                ));
 
        $this->addElement("submit", "register",
                array("label" => "Update dates",
                        "class" => "btn btn-success",
                        "disabled" => true));


    }

}