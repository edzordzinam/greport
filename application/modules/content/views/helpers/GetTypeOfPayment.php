<?php
/**
 *
 * @author Edzordzi
 * @version
 */
require_once 'Zend/View/Interface.php';

/**
 * GetTypeOfPayment helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_GetTypeOfPayment {

	/**
	 *
	 * @var Zend_View_Interface
	 */
	public $view;

	/**
	 */
	public function getTypeOfPayment($TOP) {
		// TODO Auto-generated
		// Zend_View_Helper_GetTypeOfPayment::getTypeOfPayment() helper
		switch ($TOP) {
			case 0:
				echo "TERM FEES BILL";
				break;

			case 1:
				echo "ADMISSION FEES BILL";
				break;

			case 2:
				echo "OPTIONAL ITEM PAY.";
				break;

			case 3:
				echo "FEE PAYMENT_FULL";
				break;

			case 4:
				echo "1ST INSTALLMENT";
				break;

			case 5:
				echo "2ND INSTALLMENT";
				break;

			case 6:
				echo "3RD INSTALLMENT";
				break;

			case 7:
				echo "ENTRY FEE PAYMENT";
				break;

			default:
				;
				break;
		}
	}

	/**
	 * Sets the view field
	 *
	 * @param $view Zend_View_Interface
	 */
	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
	}
}
