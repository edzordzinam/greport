<?php

class Content_Model_DbTable_Menu extends Zend_Db_Table_Abstract
{

    protected $_name = 'menu';
    protected $_schema = 'greport';

    public static function loadItems($role){
        $mTable = new self();

        $select = $mTable->select();
        $select->where('role =?', $role);

        $result = $mTable->fetchAll($select);

        if ($result){
            return $result;
        }
        else
            return null;
    }

}

