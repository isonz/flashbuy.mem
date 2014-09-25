<?php
$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;

/**************** cas **/
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', '.ptp.cn');
$cassess = isset($_COOKIE["cassession"]) ? $_COOKIE["cassession"] : null;
$casflag = isset($_COOKIE["casflag"]) ? $_COOKIE["casflag"] : null;
if(!$cassess){
    session_start();
}else{
    if(!$casflag){
        session_id($cassess);
        session_start();
    }
    setcookie("casflag", '', time()-1, '/');
}
/*************** end cas **/

//----------------cas ------------------- 
if(isset($_REQUEST['logout'])){
	unset($_SESSION);
	session_unset();
	session_destroy();
}

$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;
if(!$username){
	require_once _CAS.'cas.config.php';
	require_once _CAS.'phpCAS.class.php';
	$phpcas = new phpCAS();
	//$phpcas->setDebug();
	$client = $phpcas->client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);
	$phpcas->setNoCasServerValidation();
	$phpcas->handleLogoutRequests();
	$phpcas->forceAuthentication();
	$username = $phpcas->getUser();
	if($username) $_SESSION['username'] = $username;
	if(isset($_REQUEST['logout'])) $phpcas->logout();
}else{
	$user = md5($username._USERSERCT);
	IMemcached::addOne($user, array('username'=>$username), _MEMUSEREXP);
	setcookie("_token", $user, time()+3600, '/'); 
}
//-------------- cas ------------

switch ($type){
	case 'reurl':
		$url = isset($_REQUEST['url']) ? $_REQUEST['url'] : null;
		header("Location: $url");
		break;
	case 'checkinfo':
		$data = array('username'=>$username, 'token'=>Func::encodeStr($username));
		$data = Func::curlPost(CHECKUSERINFO, $data);
		echo $data;
		break;
	default:
		echo $username .' | <a id="logout" href="/user?logout">退出</a>';
}
