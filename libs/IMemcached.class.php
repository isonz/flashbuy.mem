<?php
/*
 * Memcached class
 * @Auth ison.zhang
 *
 * //配置服务器信息
 * $GLOBALS['CONFIG_MEMCACHED'] = array(
 * array(
 * 'host' => '192.168.77.200',
 * 'port' => '11211',
 * 'weight' => 1,
 * ),
 * );
 *
 *
 */
class IMemcached
{
    private static $_mem = null;
    private static $_type = null;
    public static $errors = array();
    
    // 初始化
    static private function _init()
    {
		if(!self::$_mem || !self::$_type){
        	self::$_type = class_exists('Memcached') ? "Memcached" : (class_exists('Memcache') ? "Memcache" : null);
        	if ('Memcached' == self::$_type) {
        	    self::$_mem = new Memcached();
        	} elseif ('Memcache' == self::$_type) {
        	    self::$_mem = new Memcache();
        	} else {
        	    exit("ERROR: Failed to load Memcached or Memcache Class");
        	}
        	self::_connect();
		}
		return self::$_mem;
    }
    
    // 负载均衡，有多个MEMCACHE服务器时均衡选择
    static private function _connect()
    {
        $servers = $GLOBALS['CONFIG_MEMCACHED'];
        if ('Memcache' == self::$_type){ 
        	$len = count($servers);
        	$num = rand(0, $len - 1);
        	$server = $servers[$num];
			return self::_addServer($server);
		}
		return self::$_mem->addServers($servers);
    }
    
    // Add Server
    static private function _addServer(array $server)
    {
        if (empty($server)) return false;
        $host = isset($server['host']) ? $server['host'] : null;
        $port = isset($server['port']) ? $server['port'] : null;
        $weight = isset($server['weight']) ? $server['weight'] : 0;
        if (!$host || ! $port) return false;
        if (!$status = self::$_mem->addServer($host, $port, $weight)) exit("ERROR: Could not connect to the server named $host");
        return $status;
    }
    
	/**
	 * Name: addOne
     * @param:$key key
     * @param:$value 值
     * @param:$expiration 过期时间
     * @return : TRUE or FALSE
	 * 加入单个数据到,如果键名重复不会被改写
    **/
    static public function addOne($key, $value, $expiration = 0)
    {
        if (!$key) return false;
        $expiration = (int) $expiration;
        self::_init();
        
        if ('Memcache' == self::$_type) return self::$_mem->add($key, $value, false, $expiration);
        return self::$_mem->add($key, $value, $expiration);
    }

	/**
     * Name: addData
     * @param:$key key
     * @param:$value 值
     * @param:$expiration 过期时间
     * @return : TRUE or FALSE
     * $items = array('key1' => 'value1','key2' => 'value2', ...);
    **/
    static public function addData($items, $expiration = 0)
    {
		if (!$items || !is_array($items)) return false;
        $expiration = (int) $expiration;
        self::_init();
		foreach ($items as $key => $value){
			if(!self::addOne($key, $value, $expiration)) return false;
		}
		return true;
    }
	
	/**
     * @Name   与add类似,但服务器有此键值时仍可写入替换
     * @param  $key key
     * @param  $value 值
     * @param  $expiration 过期时间
     * @return TRUE or FALSE
    **/
	static public function setOne($key, $value, $expiration = 0)
	{
		if (!$key) return false;
        $expiration = (int) $expiration;
        self::_init();

        if ('Memcache' == self::$_type) return self::$_mem->set($key, $value, false, $expiration);
        return self::$_mem->set($key, $value, $expiration);
	}

	/**
     * @Name   setData 存储多个元素
     * @param  $keys key
     * @param  $values 值
     * @param  $expiration 过期时间
     * @return TRUE or FALSE
	 * $items = array('key1' => 'value1','key2' => 'value2',);
    **/
    static public function setData(array $items, $expiration = 0)
    {
        if (!$items || !is_array($items)) return false;
        $expiration = (int) $expiration;
        self::_init();
        if ('Memcache' == self::$_type){
			foreach ($items as $key => $value){
				self::setOne($key, $value, $expiration);
			}
			return true;
		}
        return self::$_mem->setMulti($items, $expiration);
    }

	/**
     * @Name   getOne 根据键名获取值
     * @param  $key key
     * @return array OR json object OR string...
    **/
    static public function getOne($key)
    {
		if (!$key) return false;
		self::_init();
		return self::$_mem->get($key);
    }

	/**
     * @Name   getData 根据键名获取值
     * @param  $key key
     * @return array OR false
	 * keys = array(key1, key2, ...);
    **/
    static public function getData(array $keys)
    {
        if (!$keys || !is_array($keys)) return false;
        self::_init();
		if ('Memcache' == self::$_type) return self::$_mem->get($keys);
		return self::$_mem->getMulti($keys);
    }

	 /**
     * @Name   delOne
     * @param  $key key
     * @param  $time 服务端等待删除该元素的总时间
     * @return true OR false
    **/
    static public function delOne($key, $time = 0)
    {
		if (!$key) return false;
        self::_init();
        return self::$_mem->delete($key);
	}

	/**
     * @Name   delData
     * @param  $keys key
     * @param  $time 服务端等待删除该元素的总时间
     * @return true OR false
	 * keys = array(key1, key2, ...);
    **/
    static public function delData(array $keys, $time = 0)
    {
        if (!$keys || !is_array($keys)) return false;
        self::_init();
		foreach ($keys as $key){
			self::delOne($key, $time);
		}
		return true;
    }

	/**
	 * 向已存在元素后追加数据
     * @Name   appendOne
     * @param  $keys key
     * @param  $value value
     * @return true OR false
    **/
    static public function appendOne($key, $value, $expiration = 0)
    {
		if (!$key) return false;
        self::_init();

        if ('Memcache' == self::$_type){
		 	$old = self::getOne($key);
			$value = $old . $value;
			return self::setOne($key, $value, $expiration);
		}
        return self::$_mem->append($key, $value);
    }

	/**
     * 向一个已存在的元素前面追加数据
     * @Name   prependOne
     * @param  $keys key
     * @param  $value value
     * @return true OR false
    **/
    static public function prependOne($key, $value, $expiration = 0)
    {
        if (!$key) return false;
        self::_init();

        if ('Memcache' == self::$_type){
            $old = self::getOne($key);
            $value = $value . $old;
            return self::setOne($key, $value, $expiration);
        }
        return self::$_mem->prepend($key, $value);
    }

	/**
     * @Name   replaceOne  与 setOne 类似
     * @param  $key key
     * @param  $value 要替换的value
     * @param  $expiration 到期时间
     * @return none
     * add by cheng.yafei
    **/
	static public function replaceOne($key, $value, $expiration = 0)
    {
        if (!$key) return false;
        $expiration = (int) $expiration;
        self::_init();

        if ('Memcache' == self::$_type) return self::$_mem->replace($key, $value, false, $expiration);
        return self::$_mem->replace($key, $value, $expiration);
    }

    /**
     * @Name   replaceData 替换多个元素
     * @param  $keys key
     * @param  $values 值
     * @param  $expiration 过期时间
     * @return TRUE or FALSE
     * $items = array('key1' => 'value1','key2' => 'value2');
    **/
    static public function replaceData(array $items, $expiration = 0)
    {
        if (!$items || !is_array($items)) return false;
        $expiration = (int) $expiration;
        self::_init();
        foreach ($items as $key => $value){
        	if(!self::replaceOne($key, $value, $expiration)) return false;
        }
        return true;
    }

	//增加一个数值元素的值
	static public function incrementOne($key, $offset = 1)
	{
		if (!$key) return false;
		self::_init();
		return self::$_mem->increment($key, $offset);
	}

	//增加多个数值元素的值 $items = array('key1' => 'offset1','key2' => 'offset2');
    static public function incrementData($items)
    {
		if (!$items || !is_array($items)) return false;
        self::_init();
		foreach ($items as $key => $offset){
            if(!self::incrementOne($key, $offset)) return false;
        }
        return true;
    }

	//减少一个数值元素的值
    static public function decrementOne($key, $offset = 1)
    {
        if (!$key) return false;
        self::_init();
        return self::$_mem->decrement($key, $offset);
    }

    //减少多个数值元素的值 $items = array('key1' => 'offset1','key2' => 'offset2');
    static public function decrementData($items)
    {
        if (!$items || !is_array($items)) return false;
        self::_init();
        foreach ($items as $key => $offset){
            if(!self::decrementOne($key, $offset)) return false;
        }
        return true;
    }

    // 清空memcache 所有数据 return void
    static public function flush()
    {
		self::_init();
        return self::$_mem->flush();
    }

    //获取服务器池中所有服务器的版本信息
    static public function getversion()
    {
		self::_init();
        return self::$_mem->getVersion();
    }
     
    //获取服务器池的统计信息
    static public function getstats($type="items")
    {
     	self::_init();
		if ('Memcache' == self::$_type) return self::$_mem->getStats($type);
        return self::$_mem->getStats();
    }
     
    /**
     * @Name: 开启大值自动压缩
     * @param:$tresh 控制多大值进行自动压缩的阈值。
     * @param:$savings 指定经过压缩实际存储的值的压缩率，值必须在0和1之间。默认值0.2表示20%压缩率。
     * @return : true OR false
    **/
    static public function setcompressthreshold($tresh, $savings=0.2)
    {
		self::_init();
        if ('Memcache' == self::$_type) return self::$_mem->setCompressThreshold($tresh, $savings);
        return true;
    }
	
}
