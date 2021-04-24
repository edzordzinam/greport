<?php

class Content_Form_FeeGroupNew extends Twitter_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */

        $this->setAttrib('horizontal', true);
        $this->setName('frm_feesgroup');

        //must be the first element in all forms
        $this->addElement('hidden', 'isValid', array("value"=>"false"));
        $this->addElement('hidden', 'cl_id');

        $dbGroupExists = new Zend_Validate_Db_NoRecordExists(
                array('table' => 'feegroups', 'field' => 'groupname'));
        $dbGroupExists->setMessage('Category already exists');

        //group name
        $this->addElement('text','groupname',array(
                'label' => 'Category name',
                'required' => true,
                'filters' => array(new Zend_Filter_StringTrim()),
                'validators' => array(new Zend_Validate_NotEmpty(),
                            $dbGroupExists)
                ));


        //applicable grade levels
        $this->addElement('select','gradelevels', array(
                'label' => 'Applicable Classes',
                'style' => 'height:200px;',
                'required' => null,
                'multiple' => true,
                'registerInArrayValidator' => false,
                'multioptions' => array(null => '--- Select Class ---') + Content_Model_FeeGroups::getUnAssignedGrades(),
                'validators' => array(new Zend_Validate_NotEmpty())
                ));

        $this->addElement("button", "return",
        					array("label" => "Cancel",
        							"class" => "btn btn-small btn-danger",
        							"onclick" => "$('a[href=\"#feesgroups\"]').click();"));




        $this->addElement("submit", "register",
                array("label" => "Add Group",
                        "class" => "btn btn-success",
                        "disabled" => true));


    }


}

