<?php

class Content_Form_UpdateItemStore extends Twitter_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */

        $this->setAttrib('horizontal', true);
        $this->setName('frm_storeitems');

        //must be the first element in all forms
        $this->addElement('hidden', 'isValid', array("value"=>"false"));
        $this->addElement('hidden', 'cl_id');

        $dbItemExists = new Zend_Validate_Db_NoRecordExists(
                array('table' => 'store', 'field' => 'itemname'));
        $dbItemExists->setMessage('Item already exists in store');

        $this->addElement('text','itemname',array(
                 'label' => 'Item Name',
                 'required' => true,
                 'filters' => array(new Zend_Filter_StringTrim()),
                 'validators' => array(new Zend_Validate_NotEmpty())
                ));

        $this->addElement('text','itemprice',array(
                'label' => 'Unit price',
                'required' => true,
                'filters' => array(new Zend_Filter_StringTrim()),
                'validators' => array(new Zend_Validate_Float()),
                //'data-mask' => '999'
                ));

        $this->addElement('text','itemquantity',array(
                'label' => 'Quantity',
                'required' => true,
                'filters' => array(new Zend_Filter_StringTrim()),
                'validators' => array(new Zend_Validate_Digits()),
                //'data-mask' => '999'
        ));

        $this->addElement('text','minquantity',array(
                'label' => 'Minimum Quantity',
                'required' => true,
                'filters' => array(new Zend_Filter_StringTrim()),
                'validators' => array(new Zend_Validate_Digits()),
                //'data-mask' => '999'
        ));

        $this->addElement("button", "return",
                array("label" => "Cancel",
                        "class" => "btn btn-small btn-danger",
                        "onclick" => "$('a[href=\"#liststoreitems\"]').click();"));

        $this->addElement("submit", "register",
        					array("label" => "Add Item",
        							"class" => "btn btn-success",
        							"disabled" => true));



    }


}

