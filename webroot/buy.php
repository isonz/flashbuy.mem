<?php
//IMemcached::flush();
//检验产品ID是否合法
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if(!$id || !in_array($id, $GLOBALS['PIDS'])) exit(json_encode(array('error'=>1,'msg'=>"您所选择的产品不在抢购之列")));

$token = isset($_COOKIE["_token"]) ? $_COOKIE["_token"] : null;

//检查时间是否过期
$starttime = strtotime(_START)-time();
$endtime = strtotime(_ENDED)-time();
if($endtime < 0 || $starttime > 0 || !$token){
 	header("Location: /product/$id");
	exit;
}

//检查数量是否抢完
$pnumb = (int)IMemcached::getOne('pnumb');
if(_PNUMB <= $pnumb) exit(json_encode(array('error'=>1,'msg'=>"抱歉，已全部抢完")));

//验证用户
$user = IMemcached::getOne($token);
var_dump($user);
$username = null;
$mqkey = null;
$mokey = null;
$orded = null;  //是否购买过
if($user){
	$username = isset($user['username']) ? $user['username'] : null;
	$mqkey = isset($user['mqkey']) ? $user['mqkey'] : null;
	$mokey = isset($user['mokey']) ? $user['mokey'] : null;
	$orded = isset($user['orded']) ? $user['orded'] : null;
	$time = isset($user['time']) ? $user['time'] : 0;
}
if(!$username){ header("Location: /user?type=reurl&url=".Func::getCurrentURL()); exit; }
if($orded) exit(json_encode(array('error'=>1,'msg'=>"您已购买过,不能重复购买")));
if((time()-$time) < 5) exit(json_encode(array('error'=>1,'msg'=>"您访问太频繁")));

//重新排队
if(isset($_GET['requeue']) && !$orded){
	IMemcached::setOne($token, array('username'=>$username, 'time'=>time()), _MEMUSEREXP);
	$mqkey = null;
	$mokey = null;
}

//检查是否在订单队列
$moinfo = null;
if($mokey) $moinfo = IMemcached::getOne($mokey);
var_dump($moinfo);
if($moinfo){
	$data = array('username'=>$username, 'product_id'=>$id, 'token'=>Func::encodeStr($username));
	$json = Func::curlPost(CREATEORDER, $data);
	$data = json_decode($json, true);
	$error = isset($data['error']) ? $data['error'] : 1;
	if(!$error || 3==$error){	//2:没有默认地址，3:重复订单
		if(!$error)	if(!IMemcached::incrementOne('pnumb')) IMemcached::addOne('pnumb', 1, 24*3600);
		IMemcached::delOne($mokey);  //删除订单队列中的元素
		$user['orded'] = 1;  //在用户数据里标记已经抢到过
	}
	$user['time'] = time();
	IMemcached::setOne($token, $user, _MEMUSEREXP);
	//保证_head不大于_tail
	$mohead1 = IMemcached::incrementOne(_MEMORDER."_head");
	$motail = IMemcached::getOne(_MEMORDER.'_tail');
	if($mohead1 > $motail) $mohead1 = IMemcached::decrementOne(_MEMORDER."_head");
	exit($json);
}

//----检查ORDER队列的数量，少于_ONUMB时加入进去
$mohead = IMemcached::getOne(_MEMORDER.'_head');
$motail = IMemcached::getOne(_MEMORDER.'_tail');
var_dump($mohead,$motail);
if(!$mohead || !$motail || ($motail - $mohead) < _ONUMB){
	if(!$moinfo && !$mokey){	//用户中存储了mokey,下订单时删除了内存的mokey，所以重复请求时不再添加到MEM内存
    	$moinfo = MemQueue::add(_MEMORDER, $username, _MEMEXP_OD);
    	$moid = isset($moinfo['id']) ? $moinfo['id'] : 0;
    	$mokey = _MEMORDER."_".$moid;
    	$user['mokey'] = $mokey;
		IMemcached::delOne($mqkey);		//删除排队队列中的元素
        IMemcached::incrementOne(_MEMQUEUE."_head");
		//处理过期的ORDER队列
		for($i=$mohead; $i<=$motail; $i++){
			if(false === IMemcached::getOne(_MEMORDER."_".$i)) IMemcached::incrementOne(_MEMORDER."_head");
		}
	}
}

//检查是否在等候队列
$mqinfo = null;
if($mqkey) $mqinfo = IMemcached::getOne($mqkey);
if(!$mqinfo){
	$mqinfo = MemQueue::add(_MEMQUEUE, $username, _MEMEXP_PD);
	$mqid = isset($mqinfo['id']) ? $mqinfo['id'] : 0;
	if(!$mqid){ header("Location: /product/$id"); exit;}
	$mqkey = _MEMQUEUE."_".$mqid;
	$user['mqkey'] = $mqkey;
}else{
	$mqid = isset($mqinfo['id']) ? $mqinfo['id'] : 0;
	IMemcached::setOne($mqkey, $mqinfo, _MEMEXP_PD);
}
$user['time'] = time();	//保存本次访问的时间，防止频繁访问
IMemcached::setOne($token, $user, _MEMUSEREXP);

$mqhead = IMemcached::getOne(_MEMQUEUE.'_head');
$mqtail = IMemcached::getOne(_MEMQUEUE.'_tail');
exit(json_encode(array('error'=>1,'msg'=>"队列中", 'data'=>array($mqhead, $mqtail))));



//var_dump(MemQueue::get(_MEMQUEUE));
//MemQueue::add(_MEMQUEUE, rand(0,2000), 200);
//var_dump(IMemcached::getOne(_MEMQUEUE.'_tail'));

//IMemcached::flush();

/*
//memcache ，如果大于100个则进入排队后才验证资料是否齐全
$info = array('username'=>$username);
$info = Func::curlPost(CHECKUSERINFO, $info);
$info = json_decode($info,true);
var_dump($info);
*/
