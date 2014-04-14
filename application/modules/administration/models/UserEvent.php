<?php 
/**
* 
*/
class Administration_Model_UserEvent extends Zend_Db_Table_Abstract
{
	protected $_name = 'user_event';

	public function init()
	{
	
	}

	/**
	 * get host events by user_account_sn
	 * @param int $user_account_sn
	 */
	public function getHostEventsBySN($user_account_sn)
	{
		$select = $this->getAdapter()
						->select()
						->from($this->_name)
						->join('event', 'event.event_sn = user_event.event_fk')
						->where('user_event.user_account_fk = ?', $user_account_sn);
		$results = $this->getAdapter()->fetchAll($select);
		
		return $results;
	}
}
 ?>