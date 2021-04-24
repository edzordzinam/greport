<?php
require_once 'Zend/Db/Table/Abstract.php';

class Content_Model_Store extends Zend_Db_Table_Abstract {
	/**
	 * The default table name
	 */
	protected $_name = 'store';

    public static function listStoreItems($displayStart, $displayLength, $search, $iSortCol, $sSortDir){
        $storeModel = new self();

        //retrieving the total number of rows in the courts table
        $assignTotal = $storeModel->getAdapter()->fetchOne('SELECT COUNT(cl_id) AS count FROM store');

        $select = $storeModel->select();

        //paging
        if ($displayLength != -1)
            $select->limit($displayLength, $displayStart);

        //column filtering
        if ($search != "")
            $select->where("itemname like '%$search%'");

        //order by
        $columns = $storeModel->info(Zend_Db_Table_Abstract::COLS);
        $select->order("$columns[$iSortCol] $sSortDir");

        $store = $storeModel->fetchAll($select);

        return array('data' => $store, 'iTotalRecords' => $assignTotal, 'iDisplayRecords' => $store->count());
    }

    public static function updateStore($id, $itemname, $itemprice, $itemquantity){
        $storeModel = new self();

        if ($id == null){
            //new item
            $row = $storeModel->createRow();
            if ($row){
                $row->itemname = $itemname;
                $row->itemprice = $itemprice;
                $row->itemquantity = $itemquantity;
                $row->save();
            }
        }else{
            //update item
            $row = $storeModel->find($id)->current();
            if ($row){
                $row->itemname = $itemname;
                $row->itemprice = $itemprice;
                $row->itemquantity = $itemquantity;
                $row->save();
            }

        }
    }

}

