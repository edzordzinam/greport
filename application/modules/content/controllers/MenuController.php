<?php

class Content_MenuController extends Zend_Controller_Action
{

    protected $_identity;

    public function init()
    {
        /* Initialize action controller here */
        if (Zend_Auth::getInstance()->hasIdentity())
         $this->_identity = Zend_Auth::getInstance()->getIdentity();
        else
         $this->_identity->role = -1;
    }

    public function indexAction()
    {
        // action body
    }

    public function pdflinkVuriAction()
    {
        // action body
    }

    public function pdflinkHuriAction()
    {
        // action body
    }

    public function sidebarAction()
    {
        // action body
        if (isset($this->_identity->menu_items)){
            $menuitems = $this->_identity->menu_items;
            $this->view->items = $menuitems;
            $this->view->mobile = $this->_request->getParam('mobile');
            $this->view->navmenu = $this->_request->getParam('nav');
        }


    }

	public function shortcutsAction(){

	}
}







