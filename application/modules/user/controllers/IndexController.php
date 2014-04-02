<?php

class User_IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
    	$this->_helper->layout->setLayout('user/layout');
    	$results = $this->getModel()->getAvailableEvents();
		
        $paginator = Zend_Paginator::factory($results);
        $paginator->setItemCountPerPage(10);
        $paginator->setCurrentPageNumber($this->_getParam('page'));
        $this->view->paginator = $paginator;
    }

    public function getModel()
    {
    	require_once APPLICATION_PATH . '/modules/administration/models/Event.php';
		if (!isset($this->_orderModel)) {
			$this->_orderModel = new Administration_Model_Event();
		}

		return $this->_orderModel;
    }
}

