<?php

/**
 * Transactions
 *
 * @author Edzordzinam
 * @version
 *
 * transaction types
 * 0 - debit
 * 1 - credit
 * 2 - discount
 */

require_once 'Zend/Db/Table/Abstract.php';
class Content_Model_Transactions extends Zend_Db_Table_Abstract {

	/**
	 * The default table name
	 */
	protected $_name = 'transactions';

	public static function billStudent($transtype, $studentid, $gradelevel, $transdate, $description, $initiator, $paymode, $slipno, $term, $year, $month = false, $stream = -100) {
		// transaction type == 0 -> debit and 1 -> credit
		// paymode === 0 -> cash and 1 -> cheque
		$transactionalModel = new self ();

		$groupkey = Content_Model_FeeGroups::getGroupIDForGrade ( $gradelevel );

		if ($groupkey != - 1) {
			// getting Term Fee and Mandatory fees(1,1)
			if (!$month){
				$groupFee = Content_Model_TermBill::getGroupFee ( $groupkey, 1, 1, $term, $year , $stream );
				$classfee = Content_Model_TermBill::getGradeSpecificOnlyFee($gradelevel, 1, 1, $term, $year, $stream);
			}
			else{
				$groupFee = Content_Model_TermBill::getGroupFee ( $groupkey, 2, 1, $term, $year , $stream);
				$classfee = Content_Model_TermBill::getGradeSpecificOnlyFee($gradelevel, 2, 1, $term, $year, $stream);
			}

			try {

				//check if student is already been billed
				$select = $transactionalModel->select();
				$select->where('transtype =?',$transtype)
						->where('cl_GPSN_ID =?', $studentid)
						->where('gradelevel =?', $gradelevel)
						->where('transdescription =?', $description)
						->where('transinitiator =?', $initiator)
						->where('transterm =?', $term)
						->where('transyear =?', $year);
						//->where('transamount =?',$groupFee);

				$transaction = $transactionalModel->fetchRow($select);

				if ($transaction){
					$x = 0;
				}
				else
					$transaction = $transactionalModel->createRow ();

				if ($transaction) {
					$transaction->transtype = $transtype;
					$transaction->cl_GPSN_ID = $studentid;
					$transaction->gradelevel = $gradelevel;
					$transaction->transdate = $transdate;
					$transaction->transdescription = $description;
					$transaction->transinitiator = $initiator;
					$transaction->transpaymode = $paymode;
					$transaction->transslipno = $slipno;
					$transaction->transamount = $groupFee + $classfee;
					$transaction->transterm = $term;
					$transaction->transyear = $year;
					$transaction->save ();
				}
				return true;
			} catch ( Exception $e ) {
				Zend_Debug::dump ( $e->getMessage () );
				return false;
			}
		} else
			return false;
	}

	public static function billNewStudent($transtype, $studentid, $gradelevel, $transdate, $description, $initiator, $paymode, $slipno, $term, $year) {
		// transaction type == 0 -> debit and 1 -> credit
		// paymode === 0 -> cash and 1 -> cheque
		$transactionalModel = new self ();

		$groupkey = Content_Model_FeeGroups::getGroupIDForGrade ( $gradelevel );

		if ($groupkey != - 1) {
			// getting Entry Fee and Mandatory fees(1,1)
			$groupFee = Content_Model_TermBill::getGroupFee ( $groupkey, 0, 1, $term, $year );
			try {
				$transaction = $transactionalModel->createRow ();
				if ($transaction) {
					$transaction->transtype = $transtype;
					$transaction->cl_GPSN_ID = $studentid;
					$transaction->gradelevel = $gradelevel;
					$transaction->transdate = $transdate;
					$transaction->transdescription = $description;
					$transaction->transinitiator = $initiator;
					$transaction->transpaymode = $paymode;
					$transaction->transslipno = $slipno;
					$transaction->transamount = $groupFee;
					$transaction->transterm = $term;
					$transaction->transyear = $year;
					$transaction->save ();
				}
				return true;
			} catch ( Exception $e ) {
				Zend_Debug::dump ( $e->getMessage () );
				return false;
			}
		} else
			return false;
	}

	public static function getAccountSummary($term, $year) {
		$transModel = new self ();

		$select = $transModel->select ();
		$select->from ( $transModel, array (
				'sum(transamount) as TFPayable'
		) )->where ( 'transtype = 0' )->where ( 'transyear = ?', $year )->where ( 'transterm = ?', $term );

		$result = $transModel->fetchRow ( $select );

		$accountSummary = array ();
		if ($result)
			$accountSummary ['payable'] = $result->TFPayable;
		else
			$accountSummary ['payable'] = 0;

		$select->reset ();

		$select->from ( $transModel, array (
				'sum(transamount) as TFPaid'
		) )->where ( 'transtype = 1' )->where ( 'transyear = ?', $year )->where ( 'transterm = ?', $term );

		$result = $transModel->fetchRow ( $select );
		if ($result)
			$accountSummary ['paid'] = $result->TFPaid;
		else
			$accountSummary ['paid'] = 0;

		$select->reset ();

		$select->from ( $transModel, array (
				'sum(transamount) as TFDiscount'
		) )->where ( 'transtype = 2' )->where ( 'transyear = ?', $year )->where ( 'transterm = ?', $term );

		$result = $transModel->fetchRow ( $select );

		if ($result)
			$accountSummary ['discount'] = $result->TFDiscount;
		else
			$accountSummary ['discount'] = 0;

		$accountSummary ['outstanding'] = ($accountSummary ['payable'] - $accountSummary ['discount'] - $accountSummary ['paid']);

		return $accountSummary;
	}

	public static function offerDiscount($studentid, $gradelevel, $discountAmount, $userid) {
		$transModel = new self ();

		$currentPeriod = Content_Model_School::getCurrentTermYear ();
		$term = $currentPeriod->term;
		$year = $currentPeriod->year;

		if (Content_Model_School::isCurrentPeriod ( $term, $year ))
			$dateOfTransaction = date ( 'Y-m-d H:i:s' );
		else
			$dateOfTransaction = date ( 'Y-m-d H:i:s', strtotime ( $term->endDate ) );

		try {
			$select = $transModel->select ();

			$select->where ( 'cl_GPSN_ID =?', $studentid )->where ( 'transtype = 2' )->			// discount
			where ( 'transterm =?', $term )->where ( 'transyear =?', $year );

			$transaction = $transModel->fetchRow ( $select );

			if ($transaction) {
				// no discount has been applied hence...... apply
				$transaction->transtype = 2;
				$transaction->transdate = $dateOfTransaction;
				$transaction->transinitiator = $userid;
				$transaction->transamount = $discountAmount;
				$transaction->save ();
				Content_Model_AccountBalances::computeUpdateStudentBalance($studentid, $term, $year);

				return true;
			} else {
				$transaction = $transModel->createRow ();
				if ($transaction) {
					$transaction->transtype = 2;
					$transaction->cl_GPSN_ID = $studentid;
					$transaction->gradelevel = $gradelevel;
					$transaction->transdate = $dateOfTransaction;
					$transaction->transdescription = 'Discount';
					$transaction->transinitiator = $userid;
					$transaction->transpaymode = null;
					$transaction->transslipno = null;
					$transaction->transamount = $discountAmount;
					$transaction->transterm = $term;
					$transaction->transyear = $year;
					$transaction->save ();
					Content_Model_AccountBalances::computeUpdateStudentBalance($studentid, $term, $year);

					return true;
				}
			}
		} catch ( Exception $e ) {
			Zend_Debug::dump ( $e->getMessage () );
		}
	}

	public static function getStudentDiscount($studentid, $term = 0, $year = 1, $old = false) {
		$transModel = new self ();

		if (! $old) {
			$currentPeriod = Content_Model_School::getCurrentTermYear ();
			$term = $currentPeriod->term;
			$year = $currentPeriod->year;
		}

		try {
			$select = $transModel->select ();

			$select->where ( 'cl_GPSN_ID =?', $studentid )->where ( 'transtype = 2' )->			// discount
			where ( 'transterm =?', $term )->where ( 'transyear =?', $year );

			$transaction = $transModel->fetchRow ( $select );

			if ($transaction)
				return $transaction->transamount;
			else
				return 0;
		} catch ( Exception $e ) {
			Zend_Debug::dump ( $e->getMessage () );
		}
	}

	public static function getTermTransactions($term, $year, $transtype, $audit = false) {
		$transModel = new self ();
		$transModel->_name = 'vw_transactions';
		$transModel->_primary = 'cl_id';

		try {
			$select = $transModel->select ();

			if ($transtype != - 1)
				$select->where ( 'transtype = ?', $transtype );

			if (!$audit)
				$select->where ( 'transterm =?', $term )->where ( 'transyear =?', $year );

			$transaction = $transModel->fetchAll ( $select );

			if ($transaction)
				return $transaction;
			else
				return null;
		} catch ( Exception $e ) {
			Zend_Debug::dump ( $e->getMessage () );
		}
	}

	public static function getTermTransactionsSource($displayStart, $displayLength, $search, $iSortCol, $sSortDir, $term, $year, $transtype, $audit = false) {
		$transModel = new self ();
		$transModel->_name = 'vw_transactions';
		$transModel->_primary = 'cl_id';

		$transTotal = $transModel->getAdapter ()->fetchOne ( 'SELECT COUNT(cl_id) AS count FROM vw_transactions' );

		try {
			$select = $transModel->select ();
			$select->from ( $transModel, array (
					new Zend_Db_Expr ( 'SQL_CALC_FOUND_ROWS cl_id' ),
					'*'
			) );

			if ($transtype != - 1)
				$select->where ( 'transtype = ?', $transtype );

			if (!$audit)
				$select->where ( 'transterm =?', $term )->where ( 'transyear =?', $year );

			// paging
			if ($displayLength != - 1)
				$select->limit ( $displayLength, $displayStart );

				// column filtering
			if ($search != "")
				$select->where ( "fullname like '%$search%' OR transdescription like '%$search%' OR transamount like '%$search%' OR cl_GPSN_ID like '%$search%'" );

				// order by
			$columns = array (
					'transdate',
					'fullname',
					'gradelevel',
					'transtype',
					'transdescription',
					'transamount'
			);
			$select->order ( "$columns[$iSortCol] $sSortDir" );

			$transaction = $transModel->fetchAll ( $select );

			$db = $transModel->getDefaultAdapter ();
			$db->setFetchMode ( Zend_Db::FETCH_OBJ );
			$idisplayTotal = $db->fetchAll ( 'select FOUND_ROWS() as ct' );

			if ($transaction)
				return array (
						'data' => $transaction,
						'iTotalRecords' => $transTotal,
						'iDisplayRecords' => $idisplayTotal [0]->ct
				);
			else
				return null;
		} catch ( Exception $e ) {
			Zend_Debug::dump ( $e->getMessage () );
		}
	}

	public static function payFees($studentid, $gradelevel, $payAmount, $payMode, $slipNo, $userid, $optionids) {
		$transModel = new self ();

		$currentPeriod = Content_Model_School::getCurrentTermYear (true, true);
		$term = $currentPeriod->term;
		$year = $currentPeriod->year;

		//if (Content_Model_School::isCurrentPeriod ( $term, $year ))
			$dateOfTransaction = date ( 'Y-m-d H:i:s' );
		//else
		//	$dateOfTransaction = date ( 'Y-m-d H:i:s', strtotime ( $term->endDate ) );

		try {
			$transaction = $transModel->createRow ();
			if ($transaction) {
				$transaction->transtype = 1;
				$transaction->cl_GPSN_ID = $studentid;
				$transaction->gradelevel = $gradelevel;
				$transaction->transdate = $dateOfTransaction;
				$transaction->transdescription = 'Fees Payment';
				$transaction->transinitiator = $userid;
				$transaction->transpaymode = $payMode;
				$transaction->transslipno = $slipNo;
				$transaction->transamount = $payAmount;
				$transaction->transterm = $term;
				$transaction->transyear = $year;
				$transaction->save ();

				Content_Model_Transactions::billOptions ( $optionids, $studentid, $term, $year, $gradelevel );
				Content_Model_AccountBalances::computeUpdateStudentBalance($studentid, $term, $year);

				return $transaction->cl_id;
			}
		} catch ( Exception $e ) {
			Zend_Debug::dump ( $e->getMessage () );
		}
	}

	public static function billOptions($optionids, $studentid, $term, $year, $gradelevel) {
		if (count ( $optionids ) > 0) {
			foreach ( $optionids as $option ) {
				$termBill = new Content_Model_TermBill ();
				$bill = $termBill->find ( $option )->current ();

				if ($bill) {
					Content_Model_Transactions::billStudentDirect ( 0, $studentid, $bill->amount, $gradelevel, date ( 'Y-m-d H:i:s' ), $option . ";Bill - $bill->description", Zend_Auth::getInstance ()->getIdentity ()->cl_IID, null, null, $term, $year );
				}
			}
		}
	}

	public static function loadTransactionDetail($transID) {
		$tModel = new self ();
		$select = $tModel->select ();

		$select->where ( 'cl_id =?', $transID );

		$result = $tModel->fetchRow ( $select );

		if ($result)
			return $result;
		else
			return null;
	}

	public static function billStudentDirect($transtype, $studentid, $amount, $gradelevel, $transdate, $description, $initiator, $paymode, $slipno, $term, $year) {
		// transaction type == 0 -> debit and 1 -> credit
		// paymode === 0 -> cash and 1 -> cheque
		$transactionalModel = new self ();
		try {
			$transaction = $transactionalModel->createRow ();
			if ($transaction) {
				$transaction->transtype = $transtype;
				$transaction->cl_GPSN_ID = $studentid;
				$transaction->gradelevel = $gradelevel;
				$transaction->transdate = $transdate;
				$transaction->transdescription = $description;
				$transaction->transinitiator = $initiator;
				$transaction->transpaymode = $paymode;
				$transaction->transslipno = $slipno;
				$transaction->transamount = $amount;
				$transaction->transterm = $term;
				$transaction->transyear = $year;
				$transaction->save ();
			}
			return true;
		} catch ( Exception $e ) {
			Zend_Debug::dump ( $e->getMessage () );
			return false;
		}
	}

	public static function getDebitTransactions($studentid, $term, $year, $gradelevel) {
		$tModel = new self ();
		$select = $tModel->select ();
		$select->where ( 'cl_GPSN_ID =?', $studentid )->where ( 'transterm =?', $term )->where ( "transyear =?", $year )->where ( "transtype = 0" )->where ( 'gradelevel =?', $gradelevel )->order ( 'transdescription DESC' );

		$results = $tModel->fetchAll ( $select );
		if ($results)
			return $results;
		else
			return null;
	}

	public static function getLatestDebitTransaction($studentid, $term, $year, $gradelevel){
		$tModel = new self ();
		$select = $tModel->select ();
		$select->where ( 'cl_GPSN_ID =?', $studentid )->where ( 'transterm =?', $term )->where ( "transyear =?", $year )->where ( "transtype = 0" )->where ( 'gradelevel =?', $gradelevel )->order ( 'transdescription DESC' )->limit(1);

		$results = $tModel->fetchAll ( $select );
		if ($results)
			return $results->transdecription;
		else
			return null;
	}

	public static function getCreditTransactions($studentid, $term, $year, $gradelevel) {
		$tModel = new self ();
		$select = $tModel->select ();
		$select->where ( 'cl_GPSN_ID =?', $studentid )->where ( 'transterm =?', $term )->where ( "transyear =?", $year )->where ( "transtype = 1" )->where ( 'gradelevel =?', $gradelevel );

		$results = $tModel->fetchAll ( $select );
		if ($results)
			return $results;
		else
			return null;
	}

	public static function getStatementOfAccounts($term, $year) {
		$tModel = new self ();
		$db = $tModel->getDefaultAdapter ();

		try {
			$stmt = $db->query ( 'call sp_statementofaccounts_new(?,?)', array (
					$term,
					$year
			) );

			$results = $stmt->fetchAll ( Zend_Db::FETCH_OBJ );
			return $results;
		} catch ( Exception $e ) {
			return $e->getMessage ();
		}
	}

	public static function adjustAccount($studentid, $term, $year, $amount, $adjustmenttype, $reason) {
		$tModel = new self ();
		// add a new transaction to the transaction table to indicate the
		// adjustment
		$transactRow = $tModel->createRow ();
		if ($transactRow) {
			$studentModel = new Content_Model_Students ();
			$student = $studentModel->find ( $studentid )->current ();
			if ($student) {
				$gradelevel = $student->cl_GradeLevel;
				// if addition then transtype should be 0 and 1 for subtractions
				// credit..
				$transactRow->transtype = $adjustmenttype;
				$transactRow->transterm = $term;
				$transactRow->transyear = $year;
				$transactRow->transdescription = "A/C Correction :".strtolower($reason) ;
				$transactRow->cl_GPSN_ID = $studentid;
				$transactRow->transdate = date ( 'Y-m-d H:i:s' );
				$transactRow->transinitiator = Zend_Auth::getInstance ()->getIdentity ()->cl_IID;
				$transactRow->transpaymode = null;
				$transactRow->transslipno = null;
				$transactRow->transamount = $amount;
				$transactRow->gradelevel = $gradelevel;
				$transactRow->save ();
			}
		}
	}

	public static function daysReceipts($date){
		$tModel = new self();
		$select = $tModel->select()->from($tModel, array('sum(transamount) as daysreceipt'));
		$select->where('date(transdate) =?', date('Y-m-d',strtotime($date)))->where('transtype = 1');
		$result = $tModel->fetchRow($select);
		if ($result)
			return ($result->daysreceipt != null) ? number_format($result->daysreceipt,2) : '0.00';
		else
			return 0;
	}

	public static function monthReceipts($month){
		$tModel = new self();
		$select = $tModel->select()->from($tModel, array('sum(transamount) as monthreceipt'));
		$select->where('month(transdate) =?', $month)->where('transtype = 1');
		$result = $tModel->fetchRow($select);
		if ($result)
			return ($result->monthreceipt != null) ? number_format($result->monthreceipt,2) : '0.00';
		else
			return 0;
	}


}


