<?php
/**
 * @param 
 *  
 */
class NewLife_View_Helper_FormActionUrl extends Zend_View_Helper_Abstract 
{
    /**
     * @param $action form action
     */
    public function formActionUrl($action, $controller = null, $module = null, array $params = null )
    {
        $urlHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('Url');
        return $urlHelper->simple($action, $controller, $module, $params);
    }
}