<?php

class Security_Model_Roles extends Zend_Db_Table_Abstract
{

    protected $_name = 'roles';
    protected $_schema = 'greport';

    /**
     * This function load the specified roles required to access the system
     * @author casensuits.com
     * @version 1.0
     * @namespace Content
     * @category Models
     * @return Zend_Db_Table_Rowset_Abstract
     */

    public static function loadRoles(){
        $roles = new self();
        $select = $roles->select();
        $select->order('role asc');
        $result = $roles->fetchAll($select);
        return $result;
    }
}

