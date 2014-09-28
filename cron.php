<?php
require_once 'config.php';
require_once 'libs/Func.class.php';
require_once 'libs/IMemcached.class.php';

//处理超时未付款的数量
$pids = $GLOBALS['PIDS'];
$pids = implode(",", $pids);
$data = array('orderpayexp'=>_ORDERPAYEXP, 'pids'=>$pids, 'token'=>Func::encodeStr((string)_ORDERPAYEXP));
$data = Func::curlPost(GETEXPIREORD, $data);
$count = (int)$data['msg'];
if($count > 0){
	$pnumb = (int)IMemcached::getOne('pnumb');
	$numb = $pnumb - $count;
	for($i=0; $i<$numb; $i++) IMemcached::decrementOne('pnumb');
}

//处理过期的ORDER队列
$motail = (int)IMemcached::getOne(_MEMORDER.'_tail');
for($i=1; $i<=$motail; $i++){
	if(false === IMemcached::getOne(_MEMORDER."_".$i) && false === IMemcached::getOne("Del_"._MEMORDER."_".$i)) IMemcached::incrementOne(_MEMORDER."_head");
}

//记录日志
$pnumb = (int)IMemcached::getOne('pnumb');
error_log(date("Y-m-d H:i:s") .": $pnumb \n", 3, '/tmp/flashbuy.log');
