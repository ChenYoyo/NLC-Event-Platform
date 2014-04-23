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
        $paginator = Zend_Paginator::factory($results);
        $paginator->setItemCountPerPage(10);
        $paginator->setCurrentPageNumber($this->_getParam('page'));
        $this->view->paginator = $paginator;
    }

    public function cancelOrderAjaxAction()
    {
    	$request = $this->getRequest();

    	$updatedRows = $this->getFrontController()
							->getParam('bootstrap')
							->getResource('db')
							->update('order',
						    			array('status' => ORDER_CANCEL),
						    			"order_id = '" . $request->getParam('id') . "'"
					    	);

		if ($updatedRows > 0) {
			$this->_helper->json(array('result' => 'success'));
		} else {
			$this->_helper->json(array('result' => 'fail'));
		}
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

