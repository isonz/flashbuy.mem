<?php
/*
 * @MEMCACHE队列
 * @MemQueue::get('key'):获取队列，先进先出，并且会删除已经取出的key
 * @MemQueue::add('key', 'value', 200); 增加值到队列,并设置过期时间
 */

class MemQueue
{
	public static function get($queue, $after_id = 0, $till_id = 0) 
	{
		if(!$after_id && !$till_id) {
			$tail = IMemcached::getOne($queue."_tail");
			$id = IMemcached::incrementOne($queue."_head");
			if(!$id) return false;
			if($id <= $tail) {
				$key = $queue."_".$id;
				$value = IMemcached::getOne($key);
				IMemcached::delOne($key);
				return array($id => $value);
			}else{
				IMemcached::decrementOne($queue."_head");
				return false;
			}
		}else if($after_id && !$till_id) {
			$till_id = IMemcached::getOne($queue."_tail");
		}
		$item_keys = array();
		for($i = $after_id + 1; $i <= $till_id; $i++){
			$item_keys[] = $queue."_".$i;
		}
		$values = IMemcached::getData($item_keys);
		IMemcached::delData($item_keys);
		return array($till_id => $values);
	}

	public static function add($queue, $value, $expiration = 0)
	{
		$id = IMemcached::incrementOne($queue."_tail");
		if(false === $id){
			if(false === IMemcached::addOne($queue."_tail", '1', 24*3600)){
				$id = IMemcached::incrementOne($queue."_tail");
				if(false === $id) return false;
			}else{
				$id = 1;
				IMemcached::addOne($queue."_head", "0", 24*3600);
			}
		}
		$value = array('id'=>$id,'value'=>$value);
		if(false === IMemcached::addOne($queue."_".$id, $value, $expiration)) return false;
		return $value;
	}

	public static function isEmpty($queue) 
	{
		$head = IMemcached::getOne($queue."_head");
        $tail = IMemcached::getOne($queue."_tail");
		if(false === $head || flase === $tail || $head >= $tail) return true;
		return false;
	}

	public static function clear($queue)
	{
		$head = IMemcached::setOne($queue."_head", 1);
        $tail = IMemcached::setOne($queue."_tail", 1);
		if(false === $head || false === $tail) return false;
		return true;
	}


}


