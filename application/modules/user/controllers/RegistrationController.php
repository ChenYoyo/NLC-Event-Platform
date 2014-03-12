<?php

class User_RegistrationController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        $this->_helper->layout->setLayout('user/layout');
    }

    public function newAction()
    {	
    	require_once APPLICATION_PATH . '/modules/administration/models/Event.php';
  		$url = $this->getRequest()->getParam('event');

  		$eventModel = new Administration_Model_Event();
  		$data = $eventModel->getEventByUrl($url);
  		// only one event(activity), at least one ticket
  		$this->view->event = $data[0];
  		$this->view->tickets = $data;
    }

    public function orderAction()
    {
    	$request = $this->getRequest();

    	if ($request->isPost()) {
    		$orders = array();
    		$this->view->orders = $this->_calOrderPrice($request->getPost());
    	}
    }

    protected function _calOrderPrice($post)
    {
    	$orders = array();
    	$db = $this->getFrontController()->getParam('bootstrap')->getResource('db');
    	foreach ($post['ticket'] as $ticket_sn => $quantity) {
	    		$temp = $db->fetchRow('SELECT ticket_sn,name,price FROM ticket WHERE ticket_sn = ?', $ticket_sn);
	    		$orders[$ticket_sn] = array('name' => $temp['name'],
	    									'price' => $temp['price'],
	    									'quantity' => $quantity,
	    									'total' => $temp['price'] * $quantity
	    							);
		}
		
		return $orders;
    }


}

