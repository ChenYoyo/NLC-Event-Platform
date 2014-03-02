<?php
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
     	if ($this->getRequest()->isPost()) {
            if (!$passwordValidator->isValid($post['password'])) {
            	$messages['password'] = '密碼長度需介於6～12之間，而且只能使用數字、英文';
            }
            if(!$emailValidator->isValid($post['user-email'])){
            	$messages['user-email'] = '請輸入正確的Email帳號';
            }

            $this->view->validatedError = $messages;
        }
    }

    protected function loginAction($value='')
    {
    	$this->redirect('index/index');
    }


}

