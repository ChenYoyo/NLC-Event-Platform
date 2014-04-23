<?php
require_once APPLICATION_PATH . '/modules/administration/models/UserEvent.php';
date_default_timezone_set('Asia/Taipei');

class Administration_DashboardController extends Zend_Controller_Action
{
    protected $_eventModel = null;

    public function init()
    {
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

    public function listAction()
    {
		$UserEventModel = new Administration_Model_UserEvent();
		
        $results = $UserEventModel->getHostEventsBySN(Zend_Auth::getInstance()->getIdentity()->user_account_sn);
        $paginator = Zend_Paginator::factory($results);
        $paginator->setItemCountPerPage(10);
        $paginator->setCurrentPageNumber($this->_getParam('page'));
        $this->view->paginator = $paginator;
    }

    public function publishAction()
    {
       	$request = $this->getRequest();
    	$updatedRows = $this->getFrontController()
							->getParam('bootstrap')
							->getResource('db')
							->update('event',
						    			array('publish' => PUBLISH),
						    			"url = '" . $request->getParam('event') . "'"
					    	);
		$this->redirect('administration/dashboard/list'); 	
    }

    public function draftAction()
    {
       	$request = $this->getRequest();
    	$updatedRows = $this->getFrontController()
							->getParam('bootstrap')
							->getResource('db')
							->update('event',
						    			array('publish' => DRAFT),
						    			"url = '" . $request->getParam('event') . "'"
					    	);
		$this->redirect('administration/dashboard/list');
    }

    public function orderAction()
    {
        if ($this->hasParam('event')) {
            $eventUrl = $this->getRequest()->getParam('event');
            $this->view->eventUrl = $eventUrl;
            $this->view->orderData = $this->getEventModel()->getOrdersByUrl($eventUrl);
        } else {
            # code...
        }
    }

    public function exportXlsAction()
    {
        set_time_limit(0);
        
        $eventUrl = $this->getRequest()->getParam('event');
        $userData = $this->getEventModel()->getUserSNByUrl($eventUrl);
        if ($userData[0]['user_account_fk'] == Zend_Auth::getInstance()->getIdentity()->user_account_sn){
            $data = $this->getEventModel()->getOrdersByUrl($eventUrl);
            $this->_generateExl($data);
        }
    }

    /**
     * @param mixed $data data to export
     */
    protected function _generateExl($data)
    {
        set_time_limit(0);

        $filename = APPLICATION_PATH . "/data/order-" . date("m-d-Y") .  ".xls";
        $realPath = realpath($filename);
        $orderKey = Zend_Registry::get('orderKey');
        $gender = Zend_Registry::get('gender');

        if (false === $realPath) {
            touch($filename);
            chmod($filename, 0777);
        }
 
        $filename = realpath($filename);
        // var_dump($data);
        $handle = fopen($filename, "w");

        $finalData = array();
        foreach ($data as $row) {
            $temp = array();
            foreach (array_keys($orderKey) as $key => $value) {
                if (($value !== 'birthday_fk') &&
                    ($value !== 'gender')) {
                    array_push($temp, $row[$value]);
                } elseif ($value == 'birthday_fk') {
                    array_push($temp, $row['year'] . '/' . $row['month_fk'] . '/' . $row['day_fk']);
                } elseif ($value == 'gender') {
                    array_push($temp, $gender[$row[$value]]);
                }
            }
            $finalData[] = $temp; 
            unset($temp);
        }

        array_unshift($finalData, array_values($orderKey));
        foreach ($finalData as $finalRow) {
            fputcsv($handle, $finalRow, "\t");
        }
 
        fclose($handle);
 
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
 
        $this->getResponse()
            ->setRawHeader("Content-Type: application/vnd.ms-excel; charset=UTF-8")
            ->setRawHeader("Content-Disposition: attachment; filename=" . basename($filename))
            ->setRawHeader("Content-Transfer-Encoding: binary")
            ->setRawHeader("Expires: 0")
            ->setRawHeader("Cache-Control: must-revalidate, post-check=0, pre-check=0")
            ->setRawHeader("Pragma: public")
            ->setRawHeader("Content-Length: " . filesize($filename))
            ->sendResponse();
 
        readfile($filename);
        unlink($filename);
        exit();
    }
}

