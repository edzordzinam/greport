<?php

class RoutoTelecomSMS {

	var $user		= "";
	var $pass		= "";
	var $number		= "";
	var $ownnum		= "";
	var $message	= "";
	var $messageId	= "";
	var $type		= "";
	var $model		= "";
	var $op			= "";
	var $bulkId		= "";
	var $schedule	= "";

	function SetUser($newuser) {
		$this->user = $newuser;
		return;
	}

	function SetPass($newpass) {
		$this->pass = $newpass;
		return;
	}

	function SetNumber($newnumber) {
		$this->number = $newnumber;
		return;
	}

	function SetOwnNum($newownnum) {
		$this->ownnum = $newownnum;
		return;
	}

	function SetType($newtype) {
		$this->type = $newtype;
		return;
	}

	function SetModel($newmodel) {
		$this->model = $newmodel;
		return;
	}

	function SetMessage($newmessage) {
		$this->message = $newmessage;
		return;
	}

	function SetMessageId($newmessageid) {
		$this->messageId = $newmessageid;
	}

	function SetOp($newop) {
		$this->op = $newop;
		return;
	}

	function SetBulkId($bulkid) {
		$this->bulkId = $bulkid;
		return;
	}

	function SetSchedule($schedule) {
		$this->schedule = $schedule;
		return;
	}

	function MIMEEncode($s) {
		return base64_encode($s);
	}

	function Send() {
		$Body  = "number=" . $this->number;
		$Body .= "&user=" . urlencode($this->user);
		$Body .= "&pass=" . urlencode($this->pass);
		$Body .= "&message=" . urlencode($this->message);
		
		if (strlen($this->messageId)){
			$Body .= "&mess_id=" . urlencode($this->messageId) . "&delivery=1";
		}
		if ($this->ownnum != ""){
			$Body .= "&ownnum=" . urlencode($this->ownnum);
		}
		if ($this->model != ""){
			$Body .= "&model=" . $this->model;
		}
		if ($this->op != ""){
			$Body .= "&op=" . $this->op;
		}
		if ($this->type != ""){
			$Body .= "&type=" . $this->type;
		}
		if ($this->bulkId != ""){
			$Body .= "&bulkid=" . urlencode($this->bulkId) . "&delivery=1";
		}
		if ($this->schedule != ""){
			$Body .= "&schedule=" . urlencode($this->schedule);
		}
		
		$ContentLength = strlen($Body);
		
		$Host = "smsc5.routotelecom.com";
		
		$Header = "POST /SMSsend HTTP/1.0\n" . "Host: $Host\n" . "Content-Type: application/x-www-form-urlencoded\n" . "Content-Length: $ContentLength\n\n" . "$Body\n";
		
		echo("$Header\n");
		
		$socket = fsockopen($Host, 80, $errno, $errstr);
		
		if (!$socket) {
			return ("no_connection");
		}
		
		fputs($socket, $Header);
		
		$SocRet = "";
		
		while (!feof($socket)) {
			$SocRet .= fgets($socket, 128);
		}
		
		print $SocRet;
		
		fclose($socket);
		
		$pos = strpos($SocRet, "\n\r");
		
		$SocRet = trim(substr($SocRet, $pos + 2));
		
		return $SocRet;
	}

	function GetUser() {
		return $this->user;
	}

	function GetPass() {
		return $this->pass;
	}

	function GetNumber() {
		return $this->number;
	}

	function GetMessage() {
		return $this->message;
	}

	function GetMessageId() {
		return $this->messageId;
	}

	function GetOwnNum() {
		return $this->ownnum;
	}

	function GetOp() {
		return $this->op;
	}

	function GetType() {
		return $this->type;
	}

	function GetModel() {
		return $this->model;
	}

	function GetBulkId() {
		return $this->bulkId;
	}

	function GetSchedule() {
		return $this->schedule;
	}
}

?>
