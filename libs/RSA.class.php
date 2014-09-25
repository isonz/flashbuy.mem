<?php
class RSA
{
	private $_pubkey = "CAS/Key/pub.key";
	private $_prvkey = 'CAS/Key/sso_private.key';
	
	public function verify($plaintext, $md)
	{
		$publickey = file_get_contents($this->_pubkey);
		$md = base64_decode($md);
		$publickey = $this->der2pemPub($publickey);
		$res = openssl_pkey_get_public($publickey);
		if (openssl_verify($plaintext, $md, $res) === 1){
			return true;
		}
		return false;
	}
	
	public function der2pemPub($der_data)
	{
		$pem = chunk_split(base64_encode($der_data), 64, "\n");
		$pem = "-----BEGIN PUBLIC KEY-----\n".$pem."-----END PUBLIC KEY-----\n";
		return $pem;
	}
	
	public function der2pemRsaPriv($der)
	{
		static $BEGIN_MARKER = "-----BEGIN PRIVATE KEY-----";
	    static $END_MARKER = "-----END PRIVATE KEY-----";
	    $value = base64_encode($der);
	    $pem = $BEGIN_MARKER . "\n";
	    $pem .= chunk_split($value, 64, "\n");
	    $pem .= $END_MARKER . "\n";
	    return $pem;
	}
	
	public function ssoSignature($plaintext)
	{
		//$this->_prvkey = 'CAS/Key/private.key';
		$this->_prvkey = 'CAS/Key/sso_private.key';
		if(!file_exists($this->_prvkey)) exit("密钥文件不存在");
		
		$privatekey = file_get_contents($this->_prvkey);
		$privatekey = $this->der2pemRsaPriv($privatekey);  //密钥为二进制码时需要

		$res = openssl_get_privatekey($privatekey);
		openssl_sign($plaintext, $sign, $res);
		openssl_free_key($res);
		$sign = base64_encode($sign);

		return $sign;
	}
	
	// plaintexts = array($login,$order_code,$price,$describle);
	// params = array('yzm'=>$yzm, 'type'=>$type);
	public function toRsa(array $plaintexts, array $params=array(), array $textsignkey=array('plaintext', 'md'), $to='java', $splie='-1001-')
	{
		$textsign = $this->getTextSign($plaintexts, $splie);
		$params[$textsignkey[0]] = $textsign['plaintext'];
		$params[$textsignkey[1]] = $textsign['sign'];
		if('java'==$to) $params = $this->toJavaEncode($params);
		return $params;
	}
	
	public function getTextSign($data, $splie='-1001-')
	{
		if(!is_array($data)) return false;
		$plaintext = implode($splie, $data);
		if(!$sign = $this->ssoSignature($plaintext)){
			echo 'Create signature failed';
			return false;
		}
		return array('plaintext'=>$plaintext, 'sign'=>$sign);
	}
	
	public function toJavaEncode($data)
	{
		if(!is_array($data)) return false;
		$encoded = "";
		while (list($k, $v) = each($data)){
			$encoded .= ($encoded ? '&' : '');
			$encoded .= rawurlencode($k)."=".rawurlencode($v);
		}
		return $encoded;
	}
}