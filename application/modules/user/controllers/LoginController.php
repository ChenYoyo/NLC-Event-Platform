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
    /**
     * login page : 1. Validation 2. Login process
     */
    public function indexAction()
    {
    	$passwordValidator = new Zend_Validate();
		$passwordValidator->addValidator(new Zend_Validate_StringLength(array('min' => 6, 'max' => 12)))
			               ->addValidator(new Zend_Validate_Alnum());

		$emailValidator = new Zend_Validate_EmailAddress();

        $captcha = new Zend_Captcha_Image();
        $captcha->setName('captchaword')
                ->setFont(APPLICATION_PATH . '/data/arial.ttf')
                ->setFontSize(28)
                ->setImgDir(APPLICATION_PATH . '/../public/img')
                ->setImgUrl('/img')
                ->setWordLen(5)
                ->setDotNoiseLevel(20)
                ->setExpiration(300);

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
            if (!$captcha->isValid($post['captchaword'])) {
                $messages['captchawordVali'] = '認證碼輸入錯誤';
                $noValiError = false;
            }

            $messages['password'] = $post['password'];
            $messages['user-email'] = $post['user-email'];

            if ($noValiError) {
            	// login process
            	$this->_checkAccount($post);

            	$this->view->messages = $messages;
            } else {
                $this->_genCaptcha($captcha);
                $this->view->messages = $messages;
            }
        } else {
            $this->_genCaptcha($captcha);
        }

        if (Zend_Auth::getInstance()->hasIdentity() && $noValiError) {
            $this->redirect('index/index');
        }
    }
    /**
     * check if email or password exists in database
     * @param array post data
     */
    protected function _checkAccount($post)
    {
    	$login = new User_Model_Login();

    	$loginStatus = array();
		$emailStatus = false;
		$passwordStatus = false;

		$adapter = $this->_getAuthAdapter();
    	
    	$adapter->setIdentity($post['user-email'])
    			->setCredential($post['password']);
                
        require_once APPLICATION_PATH . '/../library/Zend/Auth.php';
    	$auth = Zend_auth::getInstance();
    	$result = $auth->authenticate($adapter);

    	if($result->isValid()){
    		//login successful
    		Zend_Session::start();
    		$user = $adapter->getResultRowObject();
    		$auth->getStorage()->write($user);

    		$user_session = new Zend_Session_Namespace('Zend_Auth');
    		$user_session->email = Zend_Auth::getInstance()->getStorage()->read()->email;
			Zend_Session::rememberMe();
    		
			$this->redirect('index/index');
    	}else if($result->getCode() == Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND){
    		$this->view->loginStatus = '帳號不存在';
    	}else if ($result->getCode() == Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID) {
    		$this->view->loginStatus = '密碼錯誤';
    	}else {
			$this->view->loginStatus = '請重新登入';
    	}
    }

    /**
     * @param mixed $captcha captcha_image class
     */
    protected function _genCaptcha($captcha)
    {
        $id = $captcha->generate();
        $this->view->captchaId = $id;
        $this->view->captcha = $captcha->render($this->view);
    }

    /**
     * 
     * 
     * @return mixed authAdapter
     */
    protected function _getAuthAdapter()
    {
    	$authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table::getDefaultAdapter());
		$authAdapter->setTableName('user_account')
					->setIdentityColumn('email')
					->setCredentialColumn('password');

		return $authAdapter;
    }
    /**
     * logout action
     */
    public function logoutAction()
    {
    	Zend_Auth::getInstance()->clearIdentity();
    	$this->redirect('login/index');
    }
}

