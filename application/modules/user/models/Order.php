<?php 
/**
* 
*/
class User_Model_Order extends Zend_Db_Table_Abstract
{
	protected $_name = 'order';

	public function init()
	{
	
	}

	/**
	 * @return mixed data for redirect(order/list)
	 */
	public function getOrderList()
	{
		$user_account_sn = Zend_Auth::getInstance()->getIdentity()->user_account_sn;
		$select = $this->getAdapter()
						->select()
						->from($this->_name)
						->group('order_time')
						->having('order.user_account_fk = ?', $user_account_sn)
						->join('ticket', 'ticket.ticket_sn = order.ticket_fk', array('event_fk'))
						->join('event', 'event.event_sn = event_fk', array('event_sn', 'event_name' => 'name', 'start_time', 'url'));
		
		return $this->getAdapter()->fetchAll($select);
	}
	
	/**
	 * @param string $orderID it's encrpted by salt and md5()
	 * @return mixed data for showing on view redirect(order/event)
	 */
	public function getOrderDataByOrderID($orderID)
	{
		$select = $this->getAdapter()
						->select()
						->from($this->_name, array('quantity', 'order_time'))
						->where('order.order_id = ?', $orderID)
						->join('user_account', 'user_account.user_account_sn = order.user_account_fk', array('username'))
						->join('ticket', 'order.ticket_fk = ticket.ticket_sn', array('ticket_name' => 'name', 'price'))
						->join('event', 'ticket.event_fk = event.event_sn', array('event_name' => 'name', 'url'))
						->joinLeft('registration_form', 'order.registration_form_fk = registration_form.registration_form_sn')
						->joinLeft('birthday', 'registration_form.birthday_fk = birthday.birthday_sn')
						->joinLeft('groups', 'groups.groups_sn = registration_form.groups_fk', array('groups_name' => 'name'));

		return $this->getAdapter()->fetchAll($select);
	}
}
 ?>