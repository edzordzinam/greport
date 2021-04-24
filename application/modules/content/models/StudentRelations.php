<?php
class Content_Model_StudentRelations extends Zend_Db_Table_Abstract {
	protected $_name = 'students_relations';

	public static function appendRelationalData($studentid, $relationtype, $fullname, $address, $telno1, $telno2, $email) {
		$rModel = new self ();

		$select = $rModel->select();
		$select->where('studentid =?', $studentid)->where('relationtype =?', $relationtype);

		$result = $rModel->fetchRow($select);
		if ($result){
			//modify the data matching the corresponding relation type.
			$result->fullname = trim($fullname);
			$result->address = trim($address);
			$result->telno1 = $telno1;
			$result->telno2 = $telno2;
			$result->email = $email;
			$result->save();
		}else{
			//insert new record into the relation table
			$row = $rModel->createRow();
			$row->studentid = $studentid;
			$row->fullname = trim($fullname);
			$row->address = trim($address);
			$row->telno1 = trim($telno1);
			$row->telno2 = trim($telno2);
			$row->email = $email;
			$row->relationtype = $relationtype;
			$row->save();
		}
	}

	public static function deleteRelationalData($studentid, $relationtype){
		$rModel = new self();
		$select = $rModel->select();
		$select->where('studentid = ?', $studentid)->where('relationtype =?', $relationtype);
		$result = $rModel->fetchRow($select);
		if ($result){
			$result->delete();
			return true;
		}
		else
			return false;
	}

	public static function getStudentRelations($studentid){
		$rModel = new self();
		$select = $rModel->select();
		$select->where('studentid = ?', $studentid);
		$result = $rModel->fetchAll($select);
		if ($result->count() > 0 ){
			return $result;
		}
		else
			return null;
	}


}

