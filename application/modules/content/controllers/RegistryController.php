<?php

class Content_RegistryController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    /**
     * This action return the list of cases published by the Registry
     * @name index
     * @author casensuits.com
     * @namespace Content
     * @category Registry
     * @version 1.0
     * @return list of published cases
     * @tutorial
      */
    public function indexAction()
    {
        // action body list
        //returns the list of published cases.
    }

    /**
     * This action implements the addition of a new case filed at the Registry
     * @name addCase
     * @author casensuits.com
     * @namespace Content
     * @category Registry
     * @version 1.0
     * @tutorial
      */
    public function addCaseAction()
    {
        // action body
    }

    /**
     * This action implements the archiving of cases as per a parameter
     * @name archiveCase
     * @author casensuits.com
     * @namespace Content
     * @category Registry
     * @version 1.0
     * @tutorial
     */
    public function archiveCaseAction()
    {
        // action body
    }

    /**
     * This action implements the publishing by registry for public assess
     * @name publishCase
     * @author casensuits.com
     * @namespace Content
     * @category Registry
     * @version 1.0
     * @tutorial
     */
    public function publishCaseAction()
    {
        //exparte cases cannot be published for public assess
        //action body
    }

    /**
     * This action implements the uploading of case files for public assess
     * @name archiveCase
     * @author casensuits.com
     * @namespace Content
     * @category Registry
     * @version 1.0
     * @tutorial
      */
    public function uploadCaseAction()
    {
        // action body
    }

    /**
     * This action implements the updating the details of a case for public assess
     * @name updateCase
     * @author casensuits.com
     * @namespace Content
     * @category Registry
     * @version 1.0
     * @tutorial
     */
    public function updateCaseAction()
    {
        // action body
        $this->view->foo = 'eldag';
    }

    /**
     * This action implements the relisting of a case for hearing
     * @name relistCase
     * @author casensuits.com
     * @namespace Content
     * @category Registry
     * @version 1.0
     * @tutorial
     */
    public function relistCaseAction()
    {
        // action body
    }

    /**
     * This action implements the sending of email/sms on published cases
     * @name sendMessage
     * @author casensuits.com
     * @namespace Content
     * @category Registry
     * @version 1.0
     * @tutorial
     */
    public function sendMessageAction()
    {
        // action body
    }

    /**
     * This action implements the list of all registries
     * @name newRegistry
     * @author casensuits.com
     * @namespace Content
     * @category Registry
     * @version 1.0
     * @tutorial
     */
    public function listRegistriesAction()
    {
        // action body
        //$this->view->registries = (new Content_Model_Registry())->getRegistries();
        $this->getHelper('layout')->disableLayout();
    }

    /**
     * This action implements the addition of new registries
     * @name newRegistry
     * @author casensuits.com
     * @namespace Content
     * @category Registry
     * @version 1.0
     * @tutorial
     */
    public function newRegistryAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->view->form = new Content_Form_NewRegistry();

    }

    /**
     * Implements the updating of registry information
     * @name updateRegistry
     * @author casensuits.com
     * @namespace Content
     * @category Registry
     * @version 1.0
     * @tutorial
     */
    public function updateRegistryAction()
    {
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);

        // action body
        if($this->_request->isXmlHttpRequest())
        {
            //receive parameters and update the database and add a new registry
            echo json_encode("Registry successfully updated");
        }

    }

    /**
     * Implements the deletion of a registry information
     * @name deleteRegistry
     * @author casensuits.com
     * @namespace Content
     * @category Registry
     * @version 1.0
     * @tutorial
     */
    public function deleteRegistryAction()
    {
        // action body
        //only possible if the registry has not registered any files
    }

    /**
     * Implements the addition of registrar information
     * @name addRegistrar
     * @author casensuits.com
     * @namespace Content
     * @category Registry
     * @version 1.0
     * @tutorial
     */
    public function addRegistrarAction()
    {
        // action body
        // registrar being added must be an existing user with role registrar
    }

    /**
     * Implements the updating of a registrar information
     * @name updateRegistrar
     * @author casensuits.com
     * @namespace Content
     * @category Registry
     * @version 1.0
     * @tutorial
     */
    public function updateRegistrarAction()
    {
        // action body
    }

    /**
     * Implements the deletion and deactivation of a registrar
     * @name deleteRegistrar
     * @author casensuits.com
     * @namespace Content
     * @category Registry
     * @version 1.0
     */
    public function deleteRegistrarAction()
    {
        // action body
    }

    /**
     * Implements the collection of statistical data on all registries
     * @name statistics
     * @author casensuits.com
     * @namespace Content
     * @category Registry
     * @version 1.0
     */
    public function statisticsAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
    }

    public function validateformAction()
    {
        //implemented in index controller
    }


}

































