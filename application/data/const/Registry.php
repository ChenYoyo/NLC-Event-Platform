<?php 
require_once 'Zend/Registry.php';
require_once APPLICATION_PATH . '/data/const/Const.php';

Zend_Registry::set('orderStatus' , array(
	ORDER_CANCEL  => '取消報名',
	ORDER_SUCCESS => '成功報名',
	ORDER_PENDING => '等待中')
);
 ?>