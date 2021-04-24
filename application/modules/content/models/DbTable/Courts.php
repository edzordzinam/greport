<?php

class Content_Model_DbTable_Courts extends Zend_Db_Table_Abstract
{
    protected $_name = 'courts';

    /**
     * Impliments the list of all courts in the system
     * @author casensuits.com
     * @name listCourts
     * @namespace Content
     * @category Courts_DbTable
     * @throws Zend_Exception
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public static function listCourts(){
        $courtTable = new self();
        try {
            return $courtTable->fetchAll();
        } catch (Exception $e) {
            throw new Zend_Exception($e->getMessage());
        }
    }


    /**
     * Impliments the addition of a new court
     * @author casensuits.com
     * @name newCourt
     * @namespace Content
     * @category Courts_DbTable
     * @param array $data
     * @return boolean
     * @throws Zend_Exception
     */
    public function newCourt(array $data){
        try {
           $newRow = $this->createRow();
           if ($newRow){
               $newRow->courtname = $data['courtname'];
               $newRow->courttype = $data['courttype'];
               $newRow->courtdivision = $data['courtdivision'];
               $newRow->courtregion = $data['courtregion'];
               $newRow->courtlocation = $data['courtlocation'];
               $newRow->save();
           }
        } catch (Exception $e) {
            throw new Zend_Exception($e->getMessage());
        }
        return true;
    }

    /**
     * Implementation for the retrieval of data formatted for a jquery datatable
     * @name get_dtbRecords
     * @author casensuits.com
     * @namespace Content
     * @category Courts_DbTable
     * @param unknown_type $displayStart
     * @param unknown_type $displayLength
     * @param unknown_type $searchcode
     * @param unknown_type $iSortCol
     * @param unknown_type $sSortDir
     * @return multitype:number unknown Zend_Db_Table_Rowset_Abstract
     */
    public static function get_dtbRecords($displayStart, $displayLength, $searchcode, $iSortCol, $sSortDir){
        $courtsTable = new self();

        //retrieving the total number of rows in the courts table
        $courtsTotal = $courtsTable->getAdapter()->fetchOne('SELECT COUNT(courtid) AS count FROM courts');

        $select = $courtsTable->select();

        //paging
        if ($displayLength != -1)
            $select->limit($displayLength, $displayStart);

        //column filtering
        if ($searchcode['Name'] != "" ||
            count($searchcode['Type']) > 0 ||
            count($searchcode['Division']) > 0 ||
            count($searchcode['Location']) > 0 ||
            count($searchcode['Region']) > 0 ||
            $searchcode['CCode'] != ""
                                ){

            $N = $searchcode['Name'];
            $select->where("courtname like '%$N%'");
            $C = $searchcode['CCode'];
            $select->orWhere("courtcode like '%$C%'");

            if (count($searchcode['Type']) > 0){
                $T = $searchcode['Type'][0];
                $select->orWhere("courttype like '%$T%'");
            };

            if (count($searchcode['Division']) > 0){
                $D =  $searchcode['Division'][0];
                $select->orWhere("courtdivision like '%$D%'");
            };

            if (count($searchcode['Location']) > 0) {
                $L = $searchcode['Location'][0];
                $select->orWhere("courtlocation like '%$L%'");
            }

            if (count($searchcode['Region']) > 0) {
                $R = $searchcode['Region'][0];
                $select->orWhere("courtregion like '%$R%'");
            }


        }

        //order by
        $columns = $courtsTable->info(Zend_Db_Table_Abstract::COLS);
        $select->order("$columns[$iSortCol] $sSortDir");

        $courts = $courtsTable->fetchAll($select);

        return array('courts' => $courts, 'iTotalRecords' => $courtsTotal, 'iDisplayRecords' => $courts->count());
    }


    /**
     * Impliments the list of courts of a specific type for a select option display
     * @author casensuits.com
     * @name listSpecificTypeCourts
     * @namespace Content
     * @category Courts_DbTable
     * @param integer $cType
     */
    public static function listSpecificTypeCourts($cType){
        $courtsTable = new self();

        $select = $courtsTable->select();

        $select->where('courttype =?', intval($cType));

        $result = $courtsTable->fetchAll($select);

        if ($result) {
            return $result;
        }
        else
            return null;
    }
}

