<?php

/**
 * GradeLevels
 *
 * @author elvis
 * @version
 */

require_once 'Zend/Db/Table/Abstract.php';
class Content_Model_GradeLevels extends Zend_Db_Table_Abstract {
	/**
	 * The default table name
	 */
	protected $_name = 'gradelevels';

	public static function addGradeLevel($gradename, $preceed, $capacity){
		$gModel = new self();
		$gradelevel = $gModel->createRow();

		if ($gradelevel){
			$gradelevel->gradename = $gradename;
			$gradelevel->capacity = $capacity;
			$gradelevel->preceed = $preceed;
			$gradelevel->save();
		}
	}

	public static function gradeExists($name){
		$gModel = new self();
		$select = $gModel->select();

		$select->where("gradename like '%$name%'");

		$exists = $gModel->fetchAll($select);
		if (count($exists) > 0){
			return true;
		}else
			return false;
	}

	public static function updateGradeLevel($id,$name){
		$gModel = new self();
		$gradelevel = $gModel->find($id)->current();
		if ($gradelevel){
			$gradelevel->name = $name;
		}
	}

	public static function gradelevels(){
		//get list of classrooms...
		$gModel = new self();
		$gModel->_name ='vw_gradelevels';
		$gModel->_primary = 'cl_id';

		$select = $gModel->select();
		$select->order('MOD(cl_id,100)');

		$results = $gModel->fetchAll($select);

		if ($results->count() > 0){
			$arr = array();
			foreach ($results as $result) {
				$arr[$result->cl_id] = $result->gradename;
			}
			return $arr;
		}
		else
			return array();
	}

	public static function getPrimaryLevels(){
		//this

	}

	public static function getGradeName($gradeid){
		//this must come from the classrooms table
	 $gModel = new self();
	 $result = $gModel->find($gradeid)->current();

	 if ($result)
	 	return $result->gradename;
	 else
	 	return '';
	}

	public static function nextLevel($gradelevel){
		$gModel = new self();
		$gModel->_name = "vw_gradelevels";
		$gModel->_primary = 'cl_id';
		$select = $gModel->select();
		//$select->where('preceed like ?', '%"'.$gradelevel.'"%');
		$select->where('preceed =?',$gradelevel);
		$result = $gModel->fetchAll($select);

		if ($result)
			return $result;
		else
			return null;
	}

	public static function getClassesListSource($displayStart, $displayLength, $search, $iSortCol, $sSortDir){
		$gModel = new self();
		$gModel->_name = 'vw_gradelevels';
		$gModel->_primary = 'cl_id';

		$select = $gModel->select();

		$gradeTotal = $gModel->getAdapter()->fetchOne("SELECT COUNT(cl_id) AS count FROM vw_gradelevels");

		try {
			$select->from($gModel, array( new Zend_Db_Expr('SQL_CALC_FOUND_ROWS cl_id'), '*'));


			//paging
			if ($displayLength != -1)
				$select->limit($displayLength, $displayStart);

			//column filtering
			if ($search != "")
				$select->where("gradename like '%$search%'");

						//order by
			$columns = array('cl_id');
			if ($iSortCol != null)
				$select->order("MOD(cl_id,100) $sSortDir");

			$gradelevels = $gModel->fetchAll($select);

			$db = $gModel->getDefaultAdapter();
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$idisplayTotal = $db->fetchAll('select FOUND_ROWS() as ct');

			if($gradelevels){
				return array('data' => $gradelevels, 'iTotalRecords' => $gradeTotal, 'iDisplayRecords' => $idisplayTotal[0]->ct);
			}
			else
				return null;

		} catch (Exception $e) {
			Zend_Debug::dump($e->getMessage());
		}
	}

	public static function getUnassignedPreceed(){
		$gModel = new self ();
		$db = $gModel->getDefaultAdapter ();

		try {
			$stmt = $db->query ('call sp_unassignedpreceed()');

			$results = $stmt->fetchAll ( Zend_Db::FETCH_OBJ);

			$arr = array();

			foreach ($results as $result) {
				$arr[$result->cl_id] = $result->gradename;
			}

			return $arr;

		} catch ( Exception $e ) {
			return $e->getMessage ();
		}
	}
}
