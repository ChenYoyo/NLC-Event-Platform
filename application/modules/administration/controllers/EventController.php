<?php
        
class Administration_EventController extends Zend_Controller_Action
{
    protected $_db;

    /**
     * get default DB resource
     */
    protected function _getDB()
    {
        if (!isset($this->_db)) {
            $this->_db = $this->getFrontController()->getParam('bootstrap')->getResource('db');
        }

        return $this->_db;
    }

    public function init()
    {
        $this->_helper->layout->setLayout('user/layout');
    }

    public function indexAction()
    {

    }

    public function newAction()
    {
        $sql = 'SELECT name FROM groups WHERE 1';
    	$this->view->forms = $this->_getCustomizedFormData();
        $this->view->groups = $this->_getDB()->fetchCol($sql);
    }

    /**
     * create event funttion
     */
    public function createAction()
    {
        $request =$this->getRequest();
        
        if ($request->isPost()) {
            $post = $request->getPost();
            $db = $this->_getDB();
            $data = array();

            foreach ($post['event'] as $key => $value) {
                if (($key != 'activityName') &&
                    ($key != 'url') &&
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

            // ensure url is unique
            $sql = 'SELECT url FROM event WHERE url = ?';
            do {
                $data['url'] = uniqid();
            } while ($db->fetchOne($sql, $data['url']) != 0);
            
            $db->insert('event', $data);

            unset($data);

            // create the data of Table user_event
            $event_fk = $db->lastInsertId('event', 'event_sn');
            $user_account_sn = Zend_Auth::getInstance()->getIdentity()->user_account_sn;
            $db->insert('user_event', array('user_account_fk' => $user_account_sn, 'event_fk' => $event_fk));            

            $data = array();
            foreach ($post['ticket'] as $id => $columns) {
                foreach ($columns as $column => $value) {
                    if ($column != 'ticketName') {
                        $data[$id][$column] = $value;
                    } else {
                        $data[$id]['name'] = $value;
                    }
                }
                $data[$id]['event_fk'] = $event_fk;
            }

            $db->beginTransaction();
            foreach ($data as $insertData) {
                $db->insert('ticket', $insertData);
            }

            $db->commit();
        }
        
        $this->redirect('index/index');
    }

    /**
     * @author Yoyo
     * this function use hardcode data. Maybe it will save into DB in the future.
     */
    protected function _getCustomizedFormData()
    {
        $name = array('formName' => '姓名',
                    'name'        => 'name',
                    'type'        => 'text',
                    'placeholder' => '陳小妹'
        );

        $email = array('formName' => 'Email',
                    'name'        => 'email',
                    'type'        => 'text',
                    'placeholder' => 'aaa@xxx.com'
        );

        $phone = array('formName' => '電話',
                    'name'        => 'phone',
                    'type'        => 'text',
                    'placeholder' => '0912123456'
        );

        $beneficiary = array('formName' => '保險受益人',
                            'name'        => 'beneficiary',
                            'type'        => 'text',
                            'placeholder' => '王小豬'
        );

        $IDcard = array('formName' => '身分證字號',
                    'name'        => 'ID',
                    'type'        => 'text',
                    'placeholder' => 'A123789654'
        );
        
        $result = array();
        array_push($result, $name, $email, $phone, $beneficiary, $IDcard);
        
        return $result;
    }
}

