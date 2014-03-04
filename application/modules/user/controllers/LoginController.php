<?php

require_once APPLICATION_PATH . '/modules/user/models/Login.php';

/**
 * @author Yoyo
 */
class User_LoginController extends Zend_Controller_Action
{

    public function init()
    {
        $this->_helper->layout->setLayout('user/layout');
    }

    public function indexAction()
    {
    	$passwordValidator = new Zend_Validate();
		$passwordValidator->addValidator(new Zend_Validate_StringLength(array('min' => 6, 'max' => 12)))
			               ->addValidator(new Zend_Validate_Alnum());

		$emailValidator = new Zend_Validate_EmailAddress();

		$request =$this->getRequest();
		$post = $request->getPost();

		$messages = array();
		$noValiError = true;
     	if ($this->getRequest()->isPost()) {
            if (!$passwordValidator->isValid($post['password'])) {
            	$messages['passwordVali'] = '密碼長度需介於6～12之間，而且只能使用數字、英文';
            	$noValiError = false;
            }
            if(!$emailValidator->isValid($post['user-email'])){
            	$messages['user-emailVali'] = '請輸入正確的Email帳號';
            	$noValiError = false;
            }

            $messages['password'] = $post['password'];
            $messages['user-email'] = $post['user-email'];

            if ($noValiError) {
            	// login process
            	$this->_checkAccount($messages['user-email'], $messages['password']);
            	$this->view->messages = $messages;
            } else {
            	$this->view->messages = $messages;
            }
        }
    }
    /**
     * check if email or password exists in database
     */
    protected function _checkAccount($email, $password)
    {
    	$login = new User_Model_Login();

    	$loginStatus = array();
		$emailStatus = false;
		$passwordStatus = false;

		if ($login->IsEmailExist($email)) {
			$emailStatus = true;

			if ($login->IsPasswordCorrect($email, $password)) {
				$passwordStatus = true;
			} else {
				$passwordStatus = false;
			}
		} else{
			$emailStatus = false;
		}

		if (!$emailStatus) {
			$this->view->loginStatus = '帳號不存在';
		} elseif (!$passwordStatus) {
			$this->view->loginStatus = '密碼錯誤';
		} else {
			//login successful
			$this->redirect('index/index');
		}
    }
}

