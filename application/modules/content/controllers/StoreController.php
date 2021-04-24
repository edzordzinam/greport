<?php

class Content_StoreController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }

    public function listStoreItemsAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();

    }

    public function listStoreSourceAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);

        $stores = Content_Model_Store::listStoreItems(
                $this->_request->getParam('iDisplayStart'),
                $this->_request->getParam('iDisplayLength'),
                $this->_request->getParam('sSearch'),
                $this->_request->getParam('iSortCol_0'),
                $this->_request->getParam('sSortDir_0')
        );

        $output = array ("sEcho" => intval ($this->_request->getParam('sEcho')),
                "iTotalDisplayRecords" => $stores['iTotalRecords'],
                "iTotalRecords" => $stores['iTotalRecords'],
                "aaData" => array () );


        //transformation of the array
        foreach ($stores['data'] as $store) {
            $row = array();
            //$row[] = $store->cl_id;
            $row[] = $store->itemname;
            $row[] = $store->itemquantity;
            $row[] = $store->itemprice;
            $row[] = ($store->itemquantity * $store->itemprice);
            $row[] = $store->itemcode;
            $row[] ="<a onclick='$.fn.toggleStoreUpdate($store->cl_id);'><span class='label label-inverse label-mini'>update</span></a> | ".
                    "<a onclick='$.fn.toggleItemStatus($store->cl_id);'><span class='label label-info label-mini'>discontinue</span></a>";
            $output['aaData'][] = $row;
        }

        echo json_encode($output);
    }

    public function updateStoreAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $form = new Content_Form_UpdateItemStore();

        $this->view->state = 'New Item Entry';
        $this->view->sendState = 'new';

        if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('send') == 'new'){
            //saving new data
            if ($form->isValid($this->_request->getParams())){
                try {
                    Content_Model_Store::updateStore(
                        null,
                        $form->getValue('itemname'),
                        $form->getValue('itemprice'),
                        $form->getValue('itemquantity')
                    );
                        $this->getResponse()->setHttpResponseCode(202);
                        $this->getResponse()->sendHeaders();
                        die(json_encode(array('status' => 1)));
                } catch (Exception $e) {}
            }
        }
        else if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('send') == 'old'){
            //updating of existing record
            if ($form->isValid($this->_request->getParams())){
               try {
                    Content_Model_Store::updateStore(
                        $form->getValue('cl_id'),
                        $form->getValue('itemname'),
                        $form->getValue('itemprice'),
                        $form->getValue('itemquantity')
                    );
                        $this->getResponse()->setHttpResponseCode(202);
                        $this->getResponse()->sendHeaders();
                        die(json_encode(array('status' => 1)));
                } catch (Exception $e) {}
            }
        }
        else  if ($this->_request->isXMLHttpRequest() && $this->_request->getParam('update') == 'true')
        {
            $storeModel = new Content_Model_Store();

            $id = $this->_request->getParam('cl_id');
            $store = $storeModel->find($id)->current();
            $form->populate($store -> toArray());
            $this->view->state = 'Item Update';
            $this->view->sendState = 'old';
        }
        $this->view->form = $form;
    }

    public function listStoreTranxAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
    }


}











