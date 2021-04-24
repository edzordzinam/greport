<?php

/**
 * FeeGroups
 *
 * @author Edzordzinam
 * @version
 */

require_once 'Zend/Db/Table/Abstract.php';

class Content_Model_FeeGroups extends Zend_Db_Table_Abstract
{

    /**
     * The default table name
     */
    protected $_name = 'feegroups';

    public static function getUnAssignedGrades(){
        $feegModel = new self();

        $allGradesKeys = array_keys(Content_Model_GradeLevels::gradelevels());

        $results = $feegModel->fetchAll();

        if ($results){
            foreach ($results as $result) {
                $grades = json_decode($result->gradelevels);
                //$grades = array(2,3,4,5,6);
                $allGradesKeys = array_diff($allGradesKeys, $grades);
            }
            $allGradesKeys = array_flip($allGradesKeys);
        }

        return array_intersect_key(Content_Model_GradeLevels::gradelevels(), $allGradesKeys) ;
    }

    public static function updateGroups($id, $groupname, $gradelevels){
        $feeGroupsModel = new self();

        if ($id == null){
            //new item
            $row = $feeGroupsModel->createRow();
            if ($row){
                $row->groupname = $groupname;
                $row->gradelevels = json_encode($gradelevels);
                $row->save();
            }
        }else{
            //update item
            $row = $feeGroupsModel->find($id)->current();
            if ($row){
                $row->groupname = $groupname;
                $row->gradelevels = json_encode($gradelevels);
                $row->save();
            }

        }
    }

    public static function listFeeGroups($displayStart, $displayLength, $search, $iSortCol, $sSortDir){
        $feesGroupModel = new self();

        //retrieving the total number of rows in the courts table
        $groupsTotal = $feesGroupModel->getAdapter()->fetchOne('SELECT COUNT(cl_id) AS count FROM feegroups');

        $select = $feesGroupModel->select();
        $select->from($feesGroupModel, array( new Zend_Db_Expr('SQL_CALC_FOUND_ROWS cl_id'), '*'));
        //paging
        if ($displayLength != -1)
            $select->limit($displayLength, $displayStart);

        //column filtering
        if ($search != "")
            $select->where("groupname like '%$search%'");

        //order by
        $columns = $feesGroupModel->info(Zend_Db_Table_Abstract::COLS);
        $select->order("$columns[$iSortCol] $sSortDir");

        $groups = $feesGroupModel->fetchAll($select);

        $db = $feesGroupModel->getDefaultAdapter();
        $db->setFetchMode(Zend_Db::FETCH_OBJ);
        $idisplayTotal = $db->fetchAll('select FOUND_ROWS() as ct');

        return array('data' => $groups, 'iTotalRecords' => $groupsTotal, 'iDisplayRecords' =>  $idisplayTotal[0]->ct);
    }

    public static function getFeeGroupsArray(){
        $feeGroupModel = new self();
        $select = $feeGroupModel->select();
        $select->from($feeGroupModel, array('cl_id','groupname'));

        $results = $feeGroupModel->fetchAll($select);

        $resultArray = array();

        foreach ($results as $result) {
            $resultArray[$result->cl_id] = $result->groupname;
        }

        return $resultArray;
    }

    public static function getGroupIDForGrade($grade){
        $feeGroupModel = new self();
        $select = $feeGroupModel->select();
        $select->where('gradelevels like ?', '%"'.$grade.'"%');

        $result = $feeGroupModel->fetchRow($select);
        if ($result)
            return $result->cl_id;
        else
            return -1;
    }

}
