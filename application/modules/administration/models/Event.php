<?php 
/**
* This class is for dealing with establishing events
*/
class Administration_Model_Event extends Zend_Db_Table_Abstract
{
	protected $_name = 'event';

	/**
	 * @author Yoyo
	 * @param $url get event,ticket DB data by "event url"
	 */
	public function getEventDataByUrl($url)
	{
		$select = $this->getAdapter()
						->select()
						->from($this->_name)
						->join('ticket','event.event_sn = ticket.event_fk', array('ticketName' => 'name',
																								'ticket_sn',
																								'quantity',
																								'price',
																								'sale_start',
																								'sale_end',
																								'min_order',
																								'max_order',
																								'ticket_id')
						)
						->where('event.url = ?', $url);
		
		return $this->getAdapter()->fetchAll($select);
	}

	public function getRequiredSetByUrl($url)
	{
		$select = $this->getAdapter()
						->select()
						->from($this->_name, array('eventName' => 'name'))
						->join('required_form', 'event.event_sn = required_form.event_fk')
						->where('event.url = ?', $url);

		return $this->getAdapter()->fetchRow($select);
	}

	/**
	 * @author Yoyo
	 * @see used by user/ indexController
	 * get online events
	 * 
	 */
	public function getAvailableEvents()
	{
		$select = $this->getAdapter()
						->select()
						->from($this->_name)
						->where('event.publish = ?', PUBLISH);
		$results = $this->getAdapter()->fetchAll($select);

		return $results;
	}

	public function getUserSNByUrl($url)
	{
		$select = $this->getAdapter()
						->select()
						->from($this->_name, array('event_sn'))
						->where('event.url = ?', $url)
						->join('user_event', 'user_event.event_fk = event.event_sn', array('user_account_fk'));
				
		return $this->getAdapter()->fetchAll($select);
	}

	public function getOrdersByUrl($url)
	{
		$select = $this->getAdapter()
						->select()
						->from($this->_name, array('event_sn'))
						->where('event.url = ?', $url)
						->join('ticket', 'ticket.event_fk = event.event_sn', array('ticket_name' => 'name', 'price'))
						->join('order', 'order.ticket_fk = ticket.ticket_sn', array('quantity', 'order_time'))
						->join('user_account', 'user_account.user_account_sn = order.user_account_fk', array('account_name' => 'username',
																											'user_email' => 'email',
																											'user_phone' => 'phone'))
						->joinLeft('registration_form', 'order.registration_form_fk = registration_form.registration_form_sn')
						->joinLeft('birthday', 'registration_form.birthday_fk = birthday.birthday_sn')
						->joinLeft('groups', 'groups.groups_sn = registration_form.groups_fk', array('groups_name' => 'name'));

		return $this->getAdapter()->fetchAll($select);
	}
}
 ?>
