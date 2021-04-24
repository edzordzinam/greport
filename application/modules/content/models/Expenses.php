<?php

class Content_Model_Expenses extends Zend_Db_Table_Abstract {
	protected $_name = "expenses";

	public static function addExpense($data) {
		$eModel = new self ();
		try {
			if (count ( $data ) > 0) {
				$row = $eModel->createRow ();
				$row->etransdate = date ( 'Y-m-d', strtotime ( $data ['etransdate'] ) );
				$row->amount = $data ['amount'];
				$row->description = $data ['description'];
				$row->paymentmode = $data ['paymentmode'];
				$row->receivedby = $data ['receivedby'];
				$row->chequeno = $data ['chequeno'];
				$row->save ();
				return true;
			}
		} catch ( Exception $e ) {
			Zend_Debug::dump ( $e->getMessage () );
			return false;
		}
	}

	public static function loadExpenses(
						$displayStart,
						$displayLength,
						$search,
						$iSortCol,
						$sSortDir,
						$date,
						$type = -1,
						$month = -1) {

		$eModel = new self ();

		$expenseTotal = $eModel->getAdapter ()->fetchOne (
							'SELECT COUNT(cl_id) AS count FROM expenses' );

		try {
			$select = $eModel->select ();
			$select->from (
								$eModel,
								array (
										new Zend_Db_Expr ( 'SQL_CALC_FOUND_ROWS cl_id' ),
										'*' ) );

			// paging
			if ($displayLength != - 1)
				$select->limit ( $displayLength, $displayStart );

				// order by
			$columns = array (
					'etransdate',
					'description',
					'receivedby',
					'amount' );

			$select->order ( "$columns[$iSortCol] $sSortDir" );
			$select->where('year(etransdate) =?', date('Y',time()));
			//Zend_Debug::dump($type . " = " . $month);

			if ($date != 'all' && $type == 1){
				$select->where ( 'etransdate =?', date ( 'Y-m-d', strtotime ( $date ) ) );
			}

	/* 		Zend_Debug::dump($date); */

			if ($type == 2 /*month*/){
				$select->where('month(etransdate) =?', $month)->where('year(etransdate) =?', date('Y',time()));
			}

			//Zend_Debug::dump($select->__toString());

			$expenses = $eModel->fetchAll ( $select );

			$db = $eModel->getDefaultAdapter ();
			$db->setFetchMode ( Zend_Db::FETCH_OBJ );
			$idisplayTotal = $db->fetchAll ( 'select FOUND_ROWS() as ct' );

			if ($expenses)
				return array (
						'data' => $expenses,
						'iTotalRecords' => $expenseTotal,
						'iDisplayRecords' => $idisplayTotal [0]->ct );
			else
				return null;
		} catch ( Exception $e ) {
			Zend_Debug::dump ( $e->getMessage () );
		}
	}

	public static function dayExpenditure($date){
		$eModel = new self();
		$select = $eModel->select()->from($eModel, array('sum(amount) as todaysexpense'));;;
		$select->where('etransdate =?', date('Y-m-d',strtotime($date)));
		$result = $eModel->fetchRow($select);

		if ($result)
			return ($result->todaysexpense != null) ? number_format($result->todaysexpense,2) : '0.00';
		else
			return 0;
	}

	public static function monthExpenditure($month){
		$eModel = new self();
		$select = $eModel->select()->from($eModel, array('sum(amount) as monthexpense'));;;
		$select->where('month(etransdate) =?', $month);
		$result = $eModel->fetchRow($select);
		if ($result)
			return $result->monthexpense;
		else
			return 0;
	}

	public static function authorizeExpense($expenseId){
		$eModel = new self();
		$result = $eModel->find($expenseId)->current();

		if ($result){
			$result->authorizedby = 1;
			$result->save();
			return true;
		}
		else
			return false;
	}
}

?>