<?php 
/**
* 
*/
class User_Model_Login extends Zend_Db_Table_Abstract
{
	protected $_name = 'user_account';

	public function init()
	{
	
	}

	/**
	 * @return bool
	 */
	public function IsEmailExist($email)
	{
		$result = $this->getAdapter()
						->fetchAll('SELECT email FROM ' . $this->_name . ' WHERE email = ?', $email);
		return !empty($result);
	}

	/**
	 * @return bool
	 */
	public function IsPasswordCorrect($email, $password)
	{
		$result = $this->getAdapter()
						->fetchAll('SELECT email,password FROM ' . $this->_name . ' WHERE email = ?', $email);
		return ($password == $result[0]['password']);
	}

}
 ?>