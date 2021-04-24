<?php

class Content_Form_FeeGroupUpdate extends Twitter_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */

        $this->setAttrib('horizontal', true);
        $this->setName('frm_feesgroup');

        //must be the first element in all forms
        $this->addElement('hidden', 'isValid', array("value"=>"false"));
        $this->addElement('hidden', 'cl_id');

        //group name
        $this->addElement('text','groupname',array(
                'label' => 'Category name',
                'required' => true,
                'filters' => array(new Zend_Filter_StringTrim()),
                'validators' => array(new Zend_Validate_NotEmpty(),
                            )
                ));


        //applicable grade levels
        $this->addElement('select','gradelevels', array(
                'label' => 'Applicable Classes',
                'style' => 'height:200px;',
                'required' => null,
                'multiple' => true,
                'registerInArrayValidator' => false,
                'multioptions' => array(null => '--- Select Class ---') + Content_Model_GradeLevels::gradelevels(),
                'validators' => array(new Zend_Validate_NotEmpty())
                ));


        $this->addElement("button", "return",
                array("label" => "List of Categories",
                        "class" => "btn btn-small btn-danger",
                        "onclick" => "$('a[href=\"#feesgroups\"]').click();"));


        $this->addElement("submit", "register",
        					array("label" => "Add Group",
        							"class" => "btn btn-success",
        							"disabled" => true));



    }


}

