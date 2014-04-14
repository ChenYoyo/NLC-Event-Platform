<?php

class User_SignupController extends Zend_Controller_Action
{

    public function init()
    {
        $this->_helper->layout->setLayout('user/layout');
    }

    public function indexAction()
    {
        $emailValidator = new Zend_Validate_EmailAddress();
        $nameValidator = new Zend_Validate_NotEmpty(array(Zend_Validate_NotEmpty::STRING, Zend_Validate_NotEmpty::SPACE));

        $password1_Validator = new Zend_Validate();
        $password1_Validator->addValidator(new Zend_Validate_StringLength(array('min' => 6, 'max' => 12)))
                           ->addValidator(new Zend_Validate_Alnum());

        $password2_Validator = new Zend_Validate();
        $password2_Validator->addValidator(new Zend_Validate_StringLength(array('min' => 6, 'max' => 12)))
                           ->addValidator(new Zend_Validate_Alnum());
        
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

        // $passwordIdentical = new Zend_Validate_Identical(array('token' => $post['password1']));

        $messages = array();
        $error = array();
        $noValiError = true;
        if ($this->getRequest()->isPost()) {
            if(!$emailValidator->isValid($post['user-email'])){
                $error['user-emailVali'] = '請輸入正確的Email帳號';
                $noValiError = false;
            }
            if(!$nameValidator->isValid($post['name'])){
                $error['nameVali'] = '姓名必填';
                $noValiError = false;
            }
            if (!$password1_Validator->isValid($post['password1'])) {
                $error['password1_Vali'] = '1.密碼長度需介於6～12之間，而且只能使用數字、英文';
                $noValiError = false;
            }
            if (!$password2_Validator->isValid($post['password2'])) {
                $error['password2_Vali'] = '1.密碼長度需介於6～12之間，而且只能使用數字、英文';
                $noValiError = false;
            }
            if (isset($post['password1'])&&
                isset($post['password2'])&&
                !($post['password1'] == $post['password2'])) {
                $error['passwordIdentical'] = '2.密碼輸入不同';
                $noValiError = false;
            }
            if (!($post['agree'] == 1)) {
                $error['agreeVali'] = '需同意服務條款及隱私權政策，才可以註冊';
                $noValiError = false;
            }
            if (!$captcha->isValid($post['captchaword'])) {
                $error['captchawordVali'] = '認證碼輸入錯誤';
                $noValiError = false;
            }

            if ($noValiError) {
                // register process
                $this->_signup($post);
                $this->view->messages = $post;
                $this->redirect('index/index');
            } else {
                $this->_genCaptcha($captcha);
                $this->view->error = $error;
                $this->view->messages = $post;
            }
        } else {
            $this->_genCaptcha($captcha);
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

    protected function _signup($post)
    {
        require_once APPLICATION_PATH . '/modules/user/models/Login.php';
        
        $signup = new User_Model_Login();
        if($signup->IsEmailExist($post['user-email'])){
            $this->view->signupStatus = '此Email帳號已經被註冊過';
            return;
        }

        $signup->registerUser($post);
        
    }

}

