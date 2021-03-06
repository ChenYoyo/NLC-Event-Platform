<?php 
require_once 'Zend/Registry.php';
require_once APPLICATION_PATH . '/data/const/Const.php';

Zend_Registry::set('orderStatus' , array(
	ORDER_CANCEL  => '取消報名',
	ORDER_SUCCESS => '成功報名',
	ORDER_PENDING => '等待中')
);

Zend_Registry::set('publishKey', array(
	PUBLISH => '上架',
	DRAFT   => '下架')
);

Zend_Registry::set('classroomKey', array(
		1 => '第1教室',
		2 => '第2教室',
		3 => '第3教室',
		4 => '第4教室',
		5 => '第5教室',
		6 => '第6教室',
		7 => '第7教室',
		8 => '第8教室',
		9 => '第9教室')
);

/**
 * 縣市
 */
Zend_Registry::set('CountyKey', array(
	1  => '臺北市',
	2  => '基隆市',
	3  => '新北市',
	4  => '宜蘭縣',
	5  => '新竹市',
	6  => '新竹縣',
	7  => '桃園縣',
	8  => '苗栗縣',
	9  => '臺中市',
	10 => '彰化縣',
	11 => '南投縣',
	12 => '嘉義市',
	13 => '嘉義縣',
	14 => '雲林縣',
	15 => '臺南市',
	16 => '高雄市',
	17 => '南海諸島',
	18 => '澎湖縣',
	19 => '屏東縣',
	20 => '臺東縣',
	21 => '花蓮縣',
	22 => '金門縣',
	23 => '連江縣'
));

/**
 * 活動分類
 */
Zend_Registry::set('categoryKey', array(
	1 => '娛樂',
	2 => '職場',
	3 => '生活',	
));

/**
 * 隱私
 */
Zend_Registry::set('privacyKey', array(
	1 => '公開',
	2 => '不公開'
));

/**
 * 牧區
 */
Zend_Registry::set('groupsKey', array(
	1  => '約1',
	2  => '約2',
	3  => '約3',
	4  => '約4',
	5  => '保1',
	6  => '保2',
	7  => '保3',
	8  => '保4',
	9  => '保5',
	10 => '保6',
	11 => '保7',
	12 => '保8',
	13 => '大1',
	14 => '大2',
	15 => '大3',
	16 => '大4',
	17 => '大5',
	18 => '大6',
	19 => '大7',
	20 => '大8',
	21 => '大9',
	22 => '大10',
	23 => '但1',
	24 => '但2',
	25 => '但3',
	26 => '但4',
	27 => '但5',
	28 => '但6',
	29 => '但7'
));

Zend_Registry::set('orderKey', array(
	'ticket_name'  =>	'票種',
	'price'        =>	'價格',
	'quantity'     =>	'數量',
	'order_time'   =>	'購買時間',
	'account_name' =>	'使用者姓名',
	'user_email'   =>	'使用者Email',
	'name'         =>	'姓名',
	'email'        =>	'Email',
	'gender'       =>	'性別',
	'phone'        =>	'電話',
	'ID'           =>	'身分證字號',
	'beneficiary'  =>	'受益人',
	'groups_name'  =>	'牧區',
	'food'         =>	'食物',
	'birthday_fk'  =>	'生日',
	'other'        =>	'其他'
));

/**
 * Gender
 */
Zend_Registry::set('gender', array(
	0 => '女',
	1 => '男'
));
 ?>