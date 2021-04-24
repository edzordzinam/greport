<?php

class Content_Form_TermBillUpdate extends Twitter_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */

        $this->setAttrib('horizontal', true);
        $this->setName('frm_termbill');


        $CurrentTerm = Content_Model_School::getCurrentTermYear(false);

        //must be the first element in all forms
        $this->addElement('hidden', 'isValid', array("value"=>"false"));
        $this->addElement('hidden', 'cl_id');


        $this->addElement('text','description',array(
                'label' => 'Description',
                'required' => true,
        		'autocomplete' => 'off',
                'filters' => array(new Zend_Filter_StringTrim()),
                'validators' => array(new Zend_Validate_NotEmpty())
                ));


        //entry fees are paid only on admission
        //Term fees are paid termly,
        //Monthly fees are paid monthly
        //Yearly fees are paid every 1st Term.
        $this->addElement('select','type', array(
                'label' => 'Type of Fee',
                'required' => true,
                'multioptions' => array(0 => 'Entry Fee', 1 => 'Term Fee', 2 => 'Monthly Fee', 3=> 'Yearly Fee' ),
                'validators' => array(new Zend_Validate_NotEmpty())
        ));

        $this->addElement('select','feegroup',array(
                'label' => 'Fees Group',
                'required' => true,
        		'onchange' => '$.fn.loadBills2($("#feegroup option:selected").val(),-100);$("#feegroupv option[value="+$("#feegroup option:selected").val()+"]").attr("selected","selected");',
                'multioptions' => array(null=>'--- Select an option ---',-1=>'Apply to All Classes', -2=>'No Category Bill') + Content_Model_FeeGroups::getFeeGroupsArray(),
                'filters' => array(new Zend_Filter_StringTrim()),
                'validators' => array(new Zend_Validate_NotEmpty())
        ));

        $this->addElement('select','specificgrades',array(
                'label' => 'Specific Class ',
                'required' => true,
                'multioptions' => array(-1 => 'Not Applicable') + Content_Model_GradeLevels::gradelevels(),
                'filters' => array(new Zend_Filter_StringTrim()),
                'validators' => array(new Zend_Validate_NotEmpty())
        ));

       $this->addElement('select','stream', array(
        		 'label' => 'Qualifying Stream',
                'required' => true,
                //'multioptions' => array(-1 => 'Not Applicable', '0' => 'Non-Res./Day Student', '1' => 'Resident/Boarder', 2=> 'Part-Time', 3=> 'Evening Programme', 4=> 'Weekend Programme') ,
                'multioptions' => array(-1 => 'All Streams', '0' => 'Non-Res./Day Student', '1' => 'Resident/Boarder') ,
       		'filters' => array(new Zend_Filter_StringTrim()),
                'validators' => array(new Zend_Validate_NotEmpty())
        ));

        $this->addElement('text','amount',array(
                'label' => 'Amount',
                'required' => true,
                'data-mask' => "9999",
                'data-placeholder' => '0',
                'filters' => array(new Zend_Filter_StringTrim()),
                'validators' => array(new Zend_Validate_NotEmpty(),
                                new Zend_Validate_Digits(),
                                new Zend_Validate_GreaterThan(000)
                        )
                ));

        $this->addElement('select','mandatory', array(
                'label' => 'Mandatory',
                'required' => true,
                'multioptions' => array(1 => 'Mandatory', 0 => 'Optional' ),
                'validators' => array(new Zend_Validate_NotEmpty())
                ));


        $this->addElement('select','term', array(
                'label' => 'Applicable Term',
                'required' => true,
                'multioptions' => array(1 => '1st Term', 2 => '2nd Term', 3 => '3rd Term' ),
                'validators' => array(new Zend_Validate_NotEmpty()),
        		'value' => $CurrentTerm->term
        ));


        $this->addElement('text', 'year', array(
                'label' => 'Applicable Year',
                'required' => true,
                'data-mask' => "2099/2099",
                'validators' => array(new Zend_Validate_NotEmpty()),
                'filters' => array(new Zend_Filter_StringTrim()),
        		'value' => $CurrentTerm->year
                ));

        $this->addElement("button", "return",
                array("label" => "Cancel",
                        "class" => "btn btn-small btn-danger",
                        "onclick" => "$('a[href=\"#termbill\"]').click();"));


        $this->addElement("submit", "register",
        					array("label" => "Add Bill",
        							"class" => "btn btn-success",
        							"disabled" => true
        					));

    }


}

