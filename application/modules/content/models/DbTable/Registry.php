<?php

class Content_Model_DbTable_Registry extends Zend_Db_Table_Abstract
{

    protected $_name = 'registry';


    /**
     * This method returns the list of all registries
     * @author casensuits
     * @version 1.0
     * @namespace Content_Model_DbTable
     * @category Registry
     * @return Zend_Db_Table_Rowset_Abstract|NULL
     */
    protected function listRegistries(){
        $select = $this->select();

        $result = $this->fetchAll();

        if ($result)
            return $result;
        else
            return null;
    }


    /**
     * This method add a new registry to system
     * @author casensuits
     * @version 1.0
     * @namespace Content_Model_DbTable
     * @category Registry
     * @param array $data : sh'd be an associative array of field=>value
     * @return NULL or Zend_Exception error code and message
     */
    protected function newRegistry(array $data){

        try {
            $newRegistry = $this->createRow($data);
        } catch (Zend_Exception $e) {
           throw new Zend_Exception($e->getMessage() . ' : '. $e->getCode());
        }

        return true;
    }
}

