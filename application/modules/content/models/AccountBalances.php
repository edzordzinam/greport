<?php

/**
 * AccountBalances
 *
 * @author Edzordzinam
 * @version
 */

require_once 'Zend/Db/Table/Abstract.php';

class Content_Model_AccountBalances extends Zend_Db_Table_Abstract
{

    /**
     * The default table name
     */
    protected $_name = 'accountbalances';

    public static function getStudentBalance($studentid, $term, $year, $all = true){
        $accountBalance = new self();
        $db = $accountBalance->getDefaultAdapter();

        try {

        	if ($all){
	            $stmt = $db->query(
	                    'select fn_accountbalanceall(?,?,?) as balance',
	                    array($studentid,$term,$year)
	            );
        	}else {
        		$stmt = $db->query(
        				'select fn_accountbalance(?,?,?) as balance',
        				array($studentid,$term,$year)
        		);
        	}

            $result = $stmt->fetch(Zend_Db::FETCH_OBJ);
            return $result->balance;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function getStudentPayable($studentid, $term, $year){
    	$accountBalance = new self();
    	$db = $accountBalance->getDefaultAdapter();

    	try {
    		$stmt = $db->query(
    				'select fn_accountpayable(?,?,?) as payable',
    				array($studentid,$term,$year)
    		);

    		$result = $stmt->fetch(Zend_Db::FETCH_OBJ);

    		return $result->payable;

    	} catch (Exception $e) {
    		return $e->getMessage();
    	}
    }

    public static function getPriorArrears($studentid, $term, $year){
    	$accountBalance = new self();
    	$db = $accountBalance->getDefaultAdapter();

    	try {
    		$stmt = $db->query(
    				'select fn_priorbalance(?,?,?) as arrears',
    				array($studentid,$term,$year)
    		);

    		$result = $stmt->fetch(Zend_Db::FETCH_OBJ);


    		return $result->arrears;

    	} catch (Exception $e) {
    		return $e->getMessage();
    	}
    }

    public static function getStudentPaid($studentid, $term, $year, $transid, $transdate){
    	$accountBalance = new self();
    	$db = $accountBalance->getDefaultAdapter();

    	try {
    		$stmt = $db->query(
    				'select fn_accountpaid(?,?,?,?,?) as paid',
    				array($studentid,$term,$year, $transid, $transdate)
    		);

    		$result = $stmt->fetch(Zend_Db::FETCH_OBJ);
    		return $result->paid;

    	} catch (Exception $e) {
    		return $e->getMessage();
    	}
    }

    public static function computeUpdateBalances($term, $year){
        $accountBalModel = new self();

        $students = Content_Model_Students::getActiveStudentIDs();
        try {
            if ($students){
                    foreach ($students as $student) {
                        //check if record exists in account balances
                        $select = $accountBalModel->select();
                        $select->where('cl_GPSN_ID =?',$student->cl_GPSN_ID)
                                ->where('balanceterm =?', $term)
                                ->where('balanceyear =?', $year);

                        $accountbalance = $accountBalModel->fetchRow($select);

                        if ($accountbalance){
                            //record has been found .... update it
                            $accountbalance->balancedate = date('Y-m-d H:i:s');
                            $accountbalance->balanceamount = self::getStudentBalance($student->cl_GPSN_ID, $term, $year);
                            $accountbalance->save();
                        }
                        else{
                            //record not found insert it...
                            $accountbalance = $accountBalModel->createRow();
                            if ($accountbalance){
                                $accountbalance->cl_GPSN_ID = $student->cl_GPSN_ID;
                                $accountbalance->balancedate = date('Y-m-d H:i:s');
                                $accountbalance->balanceamount = self::getStudentBalance($student->cl_GPSN_ID, $term, $year);
                                $accountbalance->balanceterm = $term;
                                $accountbalance->balanceyear = $year;
                                $accountbalance->save();
                            }
                        }
                    }
                }
        } catch (Exception $e) {
            Zend_Debug::dump($e->getMessage());
        }
    }

    public static function computeUpdateStudentBalance($studentid, $term, $year){
    	$accountBalModel = new self();

    	try {
    				//check if record exists in account balances
    				$select = $accountBalModel->select();
    				$select->where('cl_GPSN_ID =?',$studentid)
    				->where('balanceterm =?', $term)
    				->where('balanceyear =?', $year);

    				$accountbalance = $accountBalModel->fetchRow($select);

    				if ($accountbalance){
    					//record has been found .... update it
    					$accountbalance->balancedate = date('Y-m-d H:i:s');
    					$accountbalance->balanceamount = self::getStudentBalance($studentid, $term, $year);
    					$accountbalance->save();
    				}
    				else{
    					//record not found insert it...
    					$accountbalance = $accountBalModel->createRow();
    					if ($accountbalance){
    						$accountbalance->cl_GPSN_ID = $studentid;
    						$accountbalance->balancedate = date('Y-m-d H:i:s');
    						$accountbalance->balanceamount = self::getStudentBalance($studentid, $term, $year);
    						$accountbalance->balanceterm = $term;
    						$accountbalance->balanceyear = $year;
    						$accountbalance->save();
    					}
    				}

	    	} catch (Exception $e) {
	    		Zend_Debug::dump($e->getMessage());
	    	}
    }

    public static function getAllBalances($displayStart, $displayLength, $search, $iSortCol, $sSortDir, $term, $year, $debtors = true, $residence = true){
        $accountBalances = new self();
        $accountBalances->_name = "vw_accountbalances";
        $accountBalances->_primary = 'cl_id';

        try {
            $balancesTotal = $accountBalances->getAdapter()->fetchOne('SELECT COUNT(cl_GPSN_ID) AS count FROM vw_accountbalances');

            $select = $accountBalances->select();
            $select->from($accountBalances, array( new Zend_Db_Expr('SQL_CALC_FOUND_ROWS cl_id'), '*'));
            //paging
            if ($displayLength != -1)
                $select->limit($displayLength, $displayStart);

            //column filtering
            if ($search != ""){
                $select->where("fullname like '%$search%'")
                		->orWhere("cl_GPSN_ID like '%$search%'");

                $grades = Content_Model_GradeLevels::gradelevels();
                $grades = $grades + array(9999=>'Graduated');

                $ser = function ( $val ) use ( $search ) {
                    return ( stripos( $val, $search ) !== false ? true : false );
                };

                $grade = array_keys( array_filter( $grades , $ser ));

                if (count($grade) > 1)
                    $select->orWhere("cl_GradeLevel IN (?)",$grade);

               // Zend_Debug::dump($grade);
            };


            $select->where('balanceterm =?', $term)
            		->where('balanceyear =?', $year);

            if ($debtors)
                $select->where('balanceamount > 0');
            else
                $select->where('balanceamount < 0');

            $select->where('cl_Resident =?', $residence);

            //order by
            $columns = array('cl_GPSN_ID','fullname','cl_GradeLevel','balanceamount');

            $select->order("$columns[$iSortCol] $sSortDir");

            $balances = $accountBalances->fetchAll($select);

            $db = $accountBalances->getDefaultAdapter();
            $db->setFetchMode(Zend_Db::FETCH_OBJ);
            $idisplayTotal = $db->fetchAll('select FOUND_ROWS() as ct');

        } catch (Exception $e) {
            Zend_Debug::dump($e->getMessage());
        }
        return array('data' => $balances, 'iTotalRecords' => $balancesTotal, 'iDisplayRecords' =>  $idisplayTotal[0]->ct);
    }


}
