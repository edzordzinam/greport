<?php

class Content_Form_Discount extends Twitter_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */

        $this->setAttrib('horizontal', true);
        $this->setName('frm_discount');
        $this->setAction('/offerdiscount');

        //must be the first element in all forms
        $this->addElement('hidden', 'isValid', array("value"=>"false"));
        $this->addElement('hidden', 'cl_id');

        $this->addElement('text','studentname', array(
                'label' => 'Student\'s name',
                'disabled' => true
                ));

        $this->addElement('select','gradelevel', array(
                'label' => 'Class',
                'disabled' => true,
                'multioptions' => Content_Model_GradeLevels::gradelevels()
        ));

        $this->addElement('text', 'discount', array(
                'label' => 'Discount Allowed',
                'class' => 'number',
        		'placeholder' => '0',
                'required' => true,
                'validators' => array(new Zend_Validate_NotEmpty())
                ));

        $this->addElement("button", "return",
                array("label" => "Cancel",
                        "class" => "btn btn-small btn-danger",
                        "onclick" => "$('a[href=\"#studentdebtors\"]').click();"));


        $this->addElement("submit", "btndiscount",
        					array("label" => "Approve Discount",
        							"class" => "btn btn-success",
        							"disabled" => true
        					));


    }


}

