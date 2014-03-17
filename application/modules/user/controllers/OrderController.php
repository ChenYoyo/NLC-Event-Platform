<?php

class User_OrderController extends Zend_Controller_Action
{
	protected $_orderModel;

    public function init()
    {
        $this->_helper->layout->setLayout('user/layout');
    }

    public function indexAction()
    {
    	
    }

    public function eventAction()
    {
    	$request = $this->getRequest();

    	// ensure user's identity is the same as the owner of this order
    	$orderData = $this->getModel()->getOrderDataByOrderID($request->getParam('id'));
    	if($orderData[0]['username'] == Zend_Auth::getInstance()->getIdentity()->username) {
			$this->view->orderData = $orderData;
    	} else {
    		$this->redirect('login/logout');
    	}
    }

    public function listAction()
    {
    	$results = $this->getModel()->getOrderList();
		$this->view->orders = $results;
    }

    public function getModel()
    {
    	require_once APPLICATION_PATH . '/modules/user/models/Order.php';
		if (!isset($this->_orderModel)) {
			$this->_orderModel = new User_Model_Order();
		}

		return $this->_orderModel;
    }


}

