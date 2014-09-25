<?php
error_reporting(E_ALL);
ini_set("display_startup_errors","1");
ini_set("display_errors","On");

//======================================= Basic
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
if(!defined('_SITE')){
    define('_SITE', dirname(__FILE__) . DS);
}
if(!defined('_LIBS')){
    define('_LIBS', _SITE . 'libs' . DS);
}
if(!defined('_MODS')){
	define('_MODS', _SITE . 'mods' . DS);
}
if(!defined('_VIEWS')){
	define('_VIEWS', _SITE . 'views' . DS);
}
if(!defined('_DATA')){
	define('_DATA', _SITE . 'data' . DS);
}
if(!defined('_LOGS')){
    define('_LOGS', _DATA . 'logs' . DS);
}
//------------------------ encoding
if(!defined('_ENCODING')){
	define('_ENCODING', 'UTF-8');
}
//======================================= Smarty
if(!defined('_SMARTY')){
	define('_SMARTY', _LIBS . 'Smarty' . DS);
}
if(!defined('_SMARTY_TEMPLATE')){
	define('_SMARTY_TEMPLATE', _SITE .'template' . DS);
}
if(!defined('_SMARTY_COMPILED')){
	define('_SMARTY_COMPILED', _DATA . 'compileds' . DS);
}
if(!defined('_SMARTY_CACHE')){
	define('_SMARTY_CACHE', _DATA . 'caches' . DS);
}
//======================================== Config
$GLOBALS['CONFIG_DATABASE'] = array(
	'host'      => '192.168.77.99',
    'user'      => 'root',
    'pwd'       => 'admin888',
    'dbname'    => 'flashbuy',
	'port'      => 3306,
	'tb_prefix' => ''
);

$GLOBALS['CONFIG_MEMCACHED'] = array(
	array(
		'host' => '127.0.0.1',
		'port' => '11211',
		'weight' => 1,
	),
);
define('_CAS', _LIBS . 'CAS' . DS);
define('_USERSERCT', 'Ptp.Cn@2012'); //用户加密字符KEY
define('_MEMQUEUE', 'SAVE_QUEUE'); //排队队列
define('_MEMORDER', 'ORDER_QUEUE'); //购买队列

define('CHECKUSERINFO', 'http://mall.ptp.cn/flashbuy/checkUserInfoComplate');  //检查用户资料是否完整
define('CREATEORDER', 'http://mall.ptp.cn/flashbuy/createOrder');  			//下订单
define('GETEXPIREORD', 'http://mall.ptp.cn/flashbuy/getExpireOrderNumb');  //查询过期订单

$GLOBALS['PIDS'] = array(456,458); 	//定义抢购商品的ID
define('_MEMUSEREXP', 1800); //用户过期时间
define('_MEMEXP_PD', 60); //排队等候时60秒过期
define('_MEMEXP_OD', 600); //订单等候时间10分钟过期
define('_ORDERPAYEXP', 1800); //订单支付过期时间

//开始抢购时间
define('_START', '2014-09-09 11:04:00');
define('_ENDED', '2014-10-20 18:58:00');
define('_PNUMB', 3); //本次抢购的数量
define('_ONUMB', 1); //每批进入ORDER队列的用户数


