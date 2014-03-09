<?php
class NewLife_View_Helper_GetLoginStatus extends Zend_View_Helper_Abstract 
{
    /**
     * @param string status or username
     */
    public function getLoginStatus($param = 'status')
    {
        $auth = Zend_Auth::getInstance();
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $controller = $request->getControllerName();
        $action = $request->getActionName();

        if ($auth->hasIdentity()) {
            $username = $auth->getIdentity()->username;
            if ($param == 'username') {
                return $username;
            }
            if($param == 'status'){
                $logoutUrl = $this->view->url(array('module'=>'user', 'controller'=>'login', 'action'=>'logout'), null, true);
                return '<a href="'.$logoutUrl.'">登出</a>';    
            }
        }
        
        if($controller == 'login' && $action == 'index') {
            return '';
        }
        $loginUrl = $this->view->url(array('module'=>'user', 'controller'=>'login', 'action'=>'index'));
        return '<a href="'.$loginUrl.'">登入</a>';
    }
}