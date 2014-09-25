<?php
$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;

switch ($type){
	case 'gettime':
		$starttime = strtotime(_START)-time();
		$endtime = strtotime(_ENDED)-time();
		if($starttime > 0 || $endtime > 0){
			Func::toJson(0,$starttime);
		}else{
			Func::toJson(1, $endtime);
		}
		break;
	default:
		echo '0';
}