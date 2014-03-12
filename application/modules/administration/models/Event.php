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
	public function getEventByUrl($url)
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
																								'max_order')
						)
						->where('event.url = ?', $url);
		
		return $this->getAdapter()->fetchAll($select);
	}
}
 ?>