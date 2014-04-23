<?php
        
class Administration_EventController extends Zend_Controller_Action
{
    protected $_db;

    protected $_eventModel = null;

    const DEFAULT_UPLOAD_FILE_NAME = '300_300.gif';

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

    public function getEventModel()
    {
        if (!isset($this->_eventModel)) {
            require_once APPLICATION_PATH . '/modules/administration/models/Event.php';
            $this->_eventModel = new Administration_Model_Event();
        }

        return $this->_eventModel;
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

            $data = $this->handleEventData($post);

            // ensure url is unique
            $sql = 'SELECT url FROM event WHERE url = ?';
            do {
                $data['url'] = substr(md5(uniqid(rand(), true)), 3, 10);
            } while ($db->fetchOne($sql, $data['url']) != 0);

            $data['image'] = $this->_uploadPicture();
            // var_dump($data['image']);
            try {
                $db->beginTransaction();
                $db->insert('event', $data);
                // important use lastInsertId() before you commit a transaction
                $event_fk = $db->lastInsertId('event', 'event_sn');
                $db->commit();
                
            } catch (Exception $e) {
                $db->rollBack();
            }
            
            unset($data);

            // create the data of Table user_event
            $user_account_sn = Zend_Auth::getInstance()->getIdentity()->user_account_sn;
            $db->insert('user_event', array('user_account_fk' => $user_account_sn, 'event_fk' => $event_fk));            
            
            // handle $_POST['ticket']
            $data = array();
            $data = $this->handleTicketData($post, $event_fk);

            try {
                $db->beginTransaction();
                foreach ($data as $insertData) {
                    $db->insert('ticket', $insertData);
                }
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
            }

            unset($data);

            $data = array();
            $data = $this->handleRequiredFormData($post, $event_fk);

            $db->insert('required_form', $data);
        }
        
        $this->redirect('index/index');
    }

    public function editAction()
    {
        $request = $this->getRequest();
        $sql = 'SELECT name FROM groups WHERE 1';

        if ($this->hasParam('event')) {
            $eventUrl = $request->getParam('event');
            $userData = $this->getEventModel()->getUserSNByUrl($eventUrl);

            // only the owner of the event has privilege to edit
            if ($userData[0]['user_account_fk'] == Zend_Auth::getInstance()->getIdentity()->user_account_sn) {
                # only one event(activity), at least one ticket
                $data = $this->getEventModel()->getEventDataByUrl($eventUrl);
                $this->view->event    = $data[0];
                $this->view->tickets  = $data; 
                $this->view->required = $this->getEventModel()->getRequiredSetByUrl($eventUrl);
                $this->view->params   = array('event' => $eventUrl);
                $this->view->action   = 'update';    
            }
        } else {
            $this->view->params   = null;
            $this->view->action   = 'create';
        }
        
        $this->view->groups   = $this->getFrontController()->getParam('bootstrap')->getResource('db')->fetchCol($sql);
        $this->view->forms    = $this->_getCustomizedFormData();
    }

    public function updateAction()
    {
        $request =$this->getRequest();
        $eventUrl = $request->getParam('event');
        $userData = $this->getEventModel()->getUserSNByUrl($eventUrl);

        // only the owner of the event has privilege to edit
        if ($request->isPost() &&
            $userData[0]['user_account_fk'] == Zend_Auth::getInstance()->getIdentity()->user_account_sn) {

            $post = $request->getPost();
            $db = $this->_getDB();
                    
            // handle $_POST[event]
            $data = array();
            
            $data = $this->handleEventData($post);
            $tempFileName = $this->_uploadPicture();

            // if upload files was not changed from user side, image won't be updated on server
            if ($tempFileName != self::DEFAULT_UPLOAD_FILE_NAME) {
                $data['image'] = $tempFileName;
            }
            
            try {
                $db->beginTransaction();
                $db->update('event',
                            $data,
                            "url = '" . $request->getParam('event') . "'"
                    );
                
                $sql = 'SELECT event_sn FROM event WHERE url = ?';
                $event_fk = $db->fetchOne($sql, $request->getParam('event'));
                $db->commit();
                
            } catch (Exception $e) {
                $db->rollBack();
            }
            
            unset($data);

            // handle $_POST['ticket']
            $data = array();
            $data = $this->handleTicketData($post, $event_fk);

            try {
                $db->beginTransaction();
                foreach ($data as $insertData) {
                    $db->update('ticket',
                             $insertData,
                            'event_fk = ' . $event_fk . ' AND ticket_id = ' . $insertData['ticket_id']
                    );
                }
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
            }

            unset($data);

            $data = array();
            $data = $this->handleRequiredFormData($post, $event_fk);

            try {
                $db->beginTransaction();
                $db->update('required_form',
                            $data,
                            "event_fk = " . $event_fk
                );
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
            }
        }
        
        $this->redirect('index/index');
    }

    protected function _uploadPicture()
    {
        $adapter = new Zend_File_Transfer_Adapter_Http();
        $adapter->addValidator('Extension', false, array(  'extension' => 'jpg,png,jpeg,gif',
                                                            'messages' => array(Zend_Validate_File_Extension::FALSE_EXTENSION => "'%value%' 上傳錯誤地文件格式",
                                                                                Zend_Validate_File_Extension::NOT_FOUND       => "'%value%' 錯誤副檔名")))
                ->addValidator('IsImage', false, array( 'mimetype' => 'image/jpeg,image/png,image/gif',
                                                        'messages' => array(Zend_Validate_File_IsImage::FALSE_TYPE   => "檔案 '%value%'不是圖檔, 而是 '%type%'",
                                                                            Zend_Validate_File_IsImage::NOT_DETECTED => "檔案 '%value%' mimetype的形式偵測不到",
                                                                            Zend_Validate_File_IsImage::NOT_READABLE => "檔案 '%value%' 無法讀取或偵測不到")))
                ->addValidator('FilesSize', false, array('min'      => '1kB',
                                                         'max'      => '2.5MB',
                                                         'messages' => array(Zend_Validate_File_FilesSize::TOO_BIG      => "上傳檔案大小最大限制在 '%max%' 但此檔案大小為 '%size%'",
                                                                            Zend_Validate_File_FilesSize::TOO_SMALL     => "上傳檔案大小最小限制在 '%max%' 但此檔案大小為 '%size%'",
                                                                            Zend_Validate_File_FilesSize::NOT_READABLE  => "檔案無法讀取",)));
       
        $adapter->setDestination(APPLICATION_PATH . '/../public/img/');

        $oldFileName = $adapter->getFileName(null, false);
        $extension = array_pop(explode('.', $oldFileName));
        $newFileName = substr(md5(uniqid(rand(), true)), 3, 10) . '.' . $extension;

        $adapter->addFilter('Rename', array('target' => APPLICATION_PATH . '/../public/img/' . $newFileName));

        if ($adapter->isValid()) {
            $adapter->receive();
            return $newFileName;
        } else{
            // $messages = $adapter->getMessages();
            // echo implode("\n", $messages);
            return self::DEFAULT_UPLOAD_FILE_NAME;
        }
    }

    /**
     *  handle $_POST[event]
     * @param $post post data
     */
    protected function handleEventData($post)
    {
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
                $data['description'] = $value;
            } else{
                // do sth to catch error
            }
        }

        return $data;
    }

    /**
     * handle ticket data
     * @param $post postData
     * @param $event_fk event foreign key
     */
    protected function handleTicketData($post, $event_fk)
    {
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
            $data[$id]['ticket_id'] = $id;
        }

        return $data;
    }
    
    /**
     * handle required form data
     * @param $post postdata
     * @param $event_fk event foreign key
     */
    protected function handleRequiredFormData($post, $event_fk)
    {
        $data = array();
        foreach ($post['form'] as $column => $value) {
            $data[$column] = $value;
        }
        $data['event_fk'] = $event_fk;

        return $data;
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

