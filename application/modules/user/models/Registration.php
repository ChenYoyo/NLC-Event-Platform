<?php

date_default_timezone_set('Asia/Taipei');
/**
* 
*/
class User_Model_Registration extends Zend_Db_Table_Abstract
{
	protected $_name = 'registration_form';

	/**
	 * 1. use to save required registration data into DB 
	 * 2. establish order
	 * @param $post post data
	 * @param $orderID id generated from eventUrl using md5 @see RegistrationController-> _getOrderID()
	 * @param $eventUrl in the beginning , this is used to make sure  ticket[sn] is under event_sn, but it doesn't work for this time being
	 * @author Yoyo
	 * @return boolean true:success , false: fail to establish order
	 */
	public function establishOrder($post, $orderID)
	{
		$adapter = $this->getAdapter();
		$data = array();

		if (!(empty($post['year']) ||
			  empty($post['month']) ||
			  empty($post['day']))
			) {
			$result = $adapter->insert('birthday',array('year' => $post['year'],
												'month_fk' => $post['month'],
												'day_fk'   => $post['day'])
						);

			$birthSN = $adapter->lastInsertId('birthday', 'birthday_sn');
			$data['birthday_fk'] = $birthSN;
		}

		if (!empty($post['groups'])) {
			$data['group_fk'] = $post['groups'] + 1; #primaray key starts from 1 compared to 0 in select index
		}

		foreach ($post['form'] as $col => $value) {
			$data[$col] = $value;
		}

		$adapter->insert($this->_name, $data);
		$registration_formSN = $adapter->lastInsertId($this->_name, 'registration_form_sn');
		unset($data);

		$user_account_sn = Zend_Auth::getInstance()->getIdentity()->user_account_sn;
		
		$time = date("Y-m-d H:i:s");
		
		$adapter->beginTransaction();
		try {
			for ($i=0; $i < count($post['sn']); $i++) { 
				$adapter->insert('order',array('user_account_fk' => $user_account_sn,
											'ticket_fk'            => $post['sn'][$i],
											'quantity'             => $post['quantity'][$i],
											'order_time'           => $time,
											'registration_form_fk' => $registration_formSN,
											'order_id'             => $orderID,
											'status'               => ORDER_SUCCESS
											)
					);
			}
			$adapter->commit();

			return true;
		} catch (Exception $e) {
			$adapter->rollBack();
			
			return false;
 }
	}
}
 ?>