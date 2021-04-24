<?php

class Content_Form_SchoolConfig extends Twitter_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
        $this->setAttrib("horizontal", true);
        $this->setName("frm_schoolconfig");
        $this->setAction('/schoolconfig');


        $this->addElement('hidden', 'isValid', array("value"=>"true"));

        //school name
        $this->addElement('text','schoolname',array(
                'label' => 'School Name',
                'filters'=> array(new Zend_Filter_StringTrim()),
                'required' => true,
                'validators'=> array(new Zend_Validate_NotEmpty())
                ));

			/*  //upload logo name
	        $this->addElement('text','schoollogo',array(
	                'label' => 'Upload School Logo',
	                'filters'=> array(new Zend_Filter_StringTrim()),
	                'required' => false,
	        		'disabled' => true,
	                'validators'=> array(new Zend_Validate_NotEmpty())
	        )); */

        //school tel no.
        $this->addElement('text','schooltel',array(
                'label' => 'School Tel. No.',
                //'data-mask' => '099-9999999',
                'filters'=> array(new Zend_Filter_StringTrim()),
                'required' => true,
                'validators'=> array(new Zend_Validate_NotEmpty())
        ));

                //school tel no.
        $this->addElement('text','schoolfax',array(
              'label' => 'School Fax. No.',
              //'data-mask' => '099-9999999',
              'filters'=> array(new Zend_Filter_StringTrim()),
              'required' => false,
              //'validators'=> array(new Zend_Validate_NotEmpty())
         ));

        $mailValidator = new Zend_Validate_EmailAddress( array(
                        'allow' => Zend_Validate_Hostname::ALLOW_DNS,
                        'mx'    => false,
                        'deep'  => false,
        ));

        //school tel no.
        $this->addElement('text','schoolemail',array(
                'label' => 'School e-mail',
                'value' => 'info@greports.com',
                'required' => true,
                'filters'=> array(new Zend_Filter_StringTrim()),
                'required' => true,
                'validators'=> array(new Zend_Validate_NotEmpty(),
                                $mailValidator)
        ));

        $this->addElement('text','schoolwebsite',array(
        		'label' => 'School website',
        		'value' =>  $_SERVER['HTTP_HOST'],
        		'required' => false,
        		'filters'=> array(new Zend_Filter_StringTrim()),
        		'required' => true,
        ));

        $this->addElement('text','schooladdress',array(
        		'label' => 'School contact address',
        		'required' => true,
        		'filters'=> array(new Zend_Filter_StringTrim()),
        		'required' => true,
        ));

        //school tel no.
        $this->addElement('text','examallocate',array(
                'label' => '% of Marks for Exams',
                'data-mask' => '99',
                'value' => '60',
                'required' => true,
                'filters'=> array(new Zend_Filter_StringTrim()),
                'required' => true,
                'validators'=> array(new Zend_Validate_NotEmpty(),
                         new Zend_Validate_Digits())
        ));

        //school tel no.
        $this->addElement('text','classallocate',array(
                'label' => '% of Marks for Class',
                'data-mask' => '99',
                'value' => '40',
                'required' => true,
                'filters'=> array(new Zend_Filter_StringTrim()),
                'required' => true,
                'validators'=> array(new Zend_Validate_NotEmpty(),
                                    new Zend_Validate_Digits())
        ));

        $this->addElement('select','cambridge', array(
        		'label' => 'Cambridge Certified?',
        		'multioptions' => array(0 => 'No', 1 => 'Yes', )
        ));

        $this->addElement('select','receiptprinter', array(
        		'label' => 'Type of Receipt Printer',
        		'multioptions' => array(0 => 'Receipt Printer', 1 => 'Dot-Matrix Printer', 2=>'A4 Printer' )
        ));

        $this->addElement('select','duplicatereceipt', array(
        		'label' => 'Duplicate Receipt?',
        		'multioptions' => array(0 => 'No', 1 => 'Yes')
        ));

        $this->addElement('select','reportlayout', array(
        		'label' => 'Report Layout',
        		'multioptions' => array(0 => 'GES Standard', 1 => 'Custom Report' )
        ));
    /*  $this->addElement('text','licensestart',array(
                'label' => 'License Activation Date',
                'disabled' => true
                ));

        $this->addElement('text','licenseend',array(
                'label' => 'License Expiry Date',
                'disabled' => true
        ));

        $this->addElement('text','licensekey',array(
                'label' => 'License Key',
                'disabled' => true
        ));
*/
        $this->addElement("submit", "register",
                array("label" => "Save Configurations",
                        "class" => "btn btn-success",
                        "disabled" => true));


 }


}

