<?php

class User_RegistrationController extends Zend_Controller_Action
{
	protected $_eventModel = null;

    public function init()
    {
        /* Initialize action controller here */
        $this->_helper->layout->setLayout('user/layout');
    }

    public function getEventModel()
    {
    	if (!isset($this->_eventModel)) {
    		require_once APPLICATION_PATH . '/modules/administration/models/Event.php';
    		$this->_eventModel = new Administration_Model_Event();
    	}

    	return $this->_eventModel;
    }

    public function newAction()
    {	
    	
  		$url = $this->getRequest()->getParam('event');
  		
  		$data = $this->getEventModel()->getEventDataByUrl($url);
  		// only one event(activity), at least one ticket
  		$this->view->event = $data[0];
  		$this->view->tickets = $data;
  		$this->view->params = $url;
    }

    public function orderAction()
    {
    	$request = $this->getRequest();

    	if ($request->isPost()) {
    		
    		$sql = 'SELECT name FROM groups WHERE 1';
	    	$eventUrl = $request->getParam('event');

	        $this->view->groups = $this->getFrontController()->getParam('bootstrap')->getResource('db')->fetchCol($sql);
    		$this->view->required = $this->getEventModel()->getRequiredSetByUrl($eventUrl);
    		$this->view->orders = $this->_calOrderPrice($request->getPost());
    		
    		$this->view->user_account = Zend_Auth::getInstance()->getIdentity();
    		$this->view->params = $eventUrl;
    	}
    }
    
    /**
     * @author Yoyo
     * @param $post post data
     * @return mixed see the return $orders
     * Caculate ticket's infor
     */
    protected function _calOrderPrice($post)
    {
    	$orders = array();
    	$db = $this->getFrontController()->getParam('bootstrap')->getResource('db');
    	foreach ($post['ticket'] as $ticket_sn => $quantity) {
	    		$temp = $db->fetchRow('SELECT ticket_sn,name,price FROM ticket WHERE ticket_sn = ?', $ticket_sn);
	    		$orders[$ticket_sn] = array('ticket_sn' => $ticket_sn,
										'name'     => $temp['name'],
										'price'    => $temp['price'],
										'quantity' => $quantity,
										'total'    => $temp['price'] * $quantity
	    							);
		}

		return $orders;
    }

    public function confirmAction()
    {
    	$request = $this->getRequest();

    	if ($request->isPost()) {
    		$post = $request->getPost();
    		require_once APPLICATION_PATH . '/modules/user/models/Registration.php';
    		$registrationModel = new User_Model_Registration();
    		$registrationModel->establishOrder($post, $request->getParam('event'));
    	}
    }
}

