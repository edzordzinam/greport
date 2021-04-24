<?php

require_once 'Zend/Db/Table/Abstract.php';


class Content_Model_Notification extends Zend_Db_Table_Abstract {
	/**
	 * The default table name
	 */
	protected $_name = 'notification';


	/**
	 *
	 * @param int $recipientUid
	 * @return int
	 */
	public function fetchNumberByRecipientUid($recipientUid)
	{
		return $this->_db->fetchAll("SELECT count(*) as count "
				. " FROM notification WHERE recipientUid = %d AND isNew = 1"
				, $recipientUid)->count;
	}
	/**
	 *
	 * @param int $recipientUid
	 * @param int $eventId
	 */
	public function add($recipientUid, $eventId)
	{
		$this->_db->update("INSERT INTO "
				. " notification (`id`, `recipientUid`, `eventId`, `isNew`) VALUES (NULL, '%d', '%d', '1')"
				, $recipientUid, $eventId);
	}
	/**
	 *
	 * @param int $recipientUid
	 */
	public function removeAll($recipientUid)
	{
		$this->_db->update("DELETE FROM "
				. " notification WHERE recipientUid = %d"
				, $recipientUid);
	}


}

