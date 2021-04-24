<?php

class Content_Model_Imprest extends Zend_Db_Table_Abstract {
	protected $_name = "imprest";

	public function updateImprest($month, $year, $amount){
		$iModel = new self();
		$select = $iModel->select();

		$select->where('imonth =?', $month)->where('iyear =?', $year);
		$result = $iModel->fetchRow($select);

		if (!$result){
			$row = $iModel->createRow();
			$row->imonth = $month;
			$row->iyear = $year;
			$row->amount = $amount;
			$row->save();
			return true;
		}else {
			$result->amount = $amount;
			$result->save();
			return true;
		}
	}

	public function getImprests($year){
		$iModel = new self();
		$select = $iModel->select()->where('iyear =?', $year);
		return $iModel->fetchAll($select);
	}

	public function getMonthlyImprest($month, $year){
		$iModel = new self();
		$select = $iModel->select();

		$select->where('imonth =?', $month)->where('iyear =?', $year);
		$result = $iModel->fetchRow($select);
		if ($result)
			return $result->amount;
		else
			return null;
	}
}