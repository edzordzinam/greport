<?php

class Security_Model_Resources extends Zend_Db_Table_Abstract
{

    protected $_name = 'resources';
	protected $_schema = 'greport';

    public static function loadResources(){
        $resources = new self();

        $select = $resources->select();
        $select->distinct()->from($resources, array('controller'));
        $result = $resources->fetchAll($select);

        if ($result)
            return $result;
        else
            return null;
    }

    public static function loadAcl_list(){
        $resources = new self();

        $result = $resources->fetchAll();

        if ($result)
            return $result;
        else
            return null;
    }

}

