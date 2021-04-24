<?php

class Content_Form_ViewBill extends Twitter_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
        $this->setAttrib('horizontal', true);
        $this->setName('frm_viewbill');
        $this->setAttrib('style', 'margin-bottom : 0px');

        //must be the first element in all forms
        $this->addElement('select','feegroupv',array(
                'label' => 'Available Groups',
                'onchange' => '$.fn.loadBills($("#feegroupv option:selected").val(),1);',
                'required' => true,
                'multioptions' =>array(null=>'--- Select a group ---') + Content_Model_FeeGroups::getFeeGroupsArray(),
                'filters' => array(new Zend_Filter_StringTrim()),
                'validators' => array(new Zend_Validate_NotEmpty())
        ));

    }


}

