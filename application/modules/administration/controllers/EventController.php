<?php
        
class Administration_EventController extends Zend_Controller_Action
{

    public function init()
    {
        $this->_helper->layout->setLayout('user/layout');
    }

    public function indexAction()
    {

    }

    public function newAction()
    {
    	
    }

    /**
     * create event funttion
     */
    public function createAction()
    {
        $request =$this->getRequest();
        
        if ($request->isPost()) {
            $post = $request->getPost();
            $db = $this->getFrontController()->getParam('bootstrap')->getResource('db');
            $data = array();

            foreach ($post['event'] as $key => $value) {
                if (($key != 'activityName') &&
                    ($key != 'ticketName') &&
                    ($key != 'description') &&
                    !empty($value)) {

                    $data[$key] = $value;
                } elseif ($key == 'activityName') {
                    $data['Name'] = $value;
                } elseif ($key == 'description') {
                    $data['description'] = strip_tags($value);
                } else{
                    // do sth to catch error
                }
            }
            
            $db->insert('event', $data);

            // create the data of Table user_event
            $lastInsertId = $db->lastInsertId('event', 'event_sn');
            $user_account_sn = Zend_Auth::getInstance()->getIdentity()->user_account_sn;
            $db->insert('user_event', array('user_account_fk' => $user_account_sn, 'event_fk' => $lastInsertId));            
        }
        
        $this->redirect('index/index');
    }
}

