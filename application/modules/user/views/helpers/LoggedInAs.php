<?php
class Zend_View_Helper_LoggedInAs extends Zend_View_Helper_Abstract 
{
    public function loggedInAs ()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $username = $auth->getIdentity()->username;
            $logoutUrl = $this->view->url(array('controller'=>'login', 'action'=>'logout'), null, true);

            return 'Hi, ' . $username .  '. <a href="'.$logoutUrl.'">登出</a>';
        } 

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        
        if($controller == 'login' && $action == 'index') {
            return '';
        }
        $loginUrl = $this->view->url(array('controller'=>'login', 'action'=>'index'));
        return '<a href="'.$loginUrl.'">登入</a>';
    }
}