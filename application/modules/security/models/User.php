<?php

require_once 'Zend/Db/Table/Abstract.php';

class Security_Model_User extends Zend_Db_Table_Abstract
{
  /***
   * The default table name
   */

	protected $_name = 'instructors';

	/**
	 * Function for the addition of a user into the system
	 * @category Security Module
	 * @param String $username
	 * @param String $password
	 * @param String $firstname
	 * @param String $lastName
	 * @param Integer $role
	 * @throws Zend_Exception
	 * @version 1.0.0
	 */
	public function createUser($username, $password, $firstname, $lastName, $role){
		$rowUser = $this->createRow();

		if ($rowUser){
			//update the row values
			$rowUser->username = trim($username);
			$rowUser->password = md5($password);
			$rowUser->firstname = trim($firstname);
			$rowUser->lastname = trim($lastName);
			$rowUser->role = $role;
			$rowUser->save();
			return $rowUser;
		}
		else{
			throw  new Zend_Exception('Could not create user! - contact Administrator');
		}
	}

	/**
	 * Returns all users in the database
	 * @return Zend_Db_Table_Rowset_Abstract
	 */
	public static function getUsers(){
		$userModel = new self();
		$select = $userModel->select();
		$select->order(array('lastname', 'firstname'));
		return $userModel->fetchAll($select);
	}

	/**
	 * Updates the selected users profile details
	 * @param Integer $id
	 * @param String $username
	 * @param String $firstname
	 * @param String $lastname
	 * @param Integer $role
	 * @throws Zend_Exception
	 */
	public function updateUser($id, $username, $firstname, $lastname, $role){
		//fetch the user's row
		$rowUser = $this->find($id)->current();

		if ($rowUser) {
			//update the row value
			$rowUser->username = $username;
			$rowUser->firstname = $firstname;
			$rowUser->lastname = $lastname;
			$rowUser->role = $role;
			$rowUser->save();

			//return the updated user
			return $rowUser;
		}
		else{
			throw new Zend_Exception("User update failed. User not found!");
		}
	}

	public function updatePassword($id, $username, $password, $oldPassword){
		//fetch the user's row
		$rowUser = $this->find($id)->current();

		if ($rowUser->password == md5($oldPassword) ){
			if ($rowUser){
				//update the password
				$rowUser->password = md5($password);
				$rowUser->save();
				return true;
			}
			else{
				throw new Zend_Exception("Password update failed. User not found!");
			}
		}
		else
			return false;
	}

	public function deleteUser($id){
		//fetch the user's row
		$rowUser = $this->find($id)->current();

		if ($rowUser){
			$rowUser->delete();
		}
		else
		{
			throw new Zend_Exception("Could no delete user. User not found!");
		}
	}


}

