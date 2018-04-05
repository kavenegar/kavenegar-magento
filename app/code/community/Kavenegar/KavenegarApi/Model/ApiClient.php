<?php
class BaseRuntimeException extends RuntimeException 
{
	public function getName()
    {
        return 'BaseRuntimeException';
    }
    public function __construct($message, $code=0) {
        parent::__construct($message, $code);
    }
	public function errorMessage(){
		return "\r\n".$this->getName() . "[{$this->code}] : {$this->message}\r\n";
	}
}
class HttpException extends BaseRuntimeException
{
	public function getName()
    {
        return 'HttpException';
    }	
}
class ApiException extends BaseRuntimeException
{
	public function getName()
    {
        return 'ApiException';
    }
}

class Kavenegar_KavenegarApi_Model_ApiClient{
	const APIPATH = "http://api.kavenegar.com/v1/%s/%s/%s.json/";
	private  function get_path($apiKey,$method, $base = 'sms'){
		return sprintf(self::APIPATH,$apiKey,$base,$method);
	}
	private  function execute($url,$data=null){
        $headers = array (
           	'Accept: application/json',
           	'Content-Type: application/x-www-form-urlencoded',
			'charset: utf-8'
        );
		$fields_string = "";
		if(!is_null($data))
		{
		    $fields_string=http_build_query($data);
		}
		$handle = curl_init();
	       curl_setopt($handle, CURLOPT_URL, $url);
	       curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
	       curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
	       curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
	       curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
	       curl_setopt($handle, CURLOPT_POST, true);
	       curl_setopt($handle, CURLOPT_POSTFIELDS, $fields_string);
		
	       $response = curl_exec($handle);
	       $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
		$content_type = curl_getinfo($handle, CURLINFO_CONTENT_TYPE);
		$curl_errno = curl_errno($handle);
           $curl_error = curl_error($handle);
		if($curl_errno){
			throw new HttpException($curl_error,$curl_errno);
		}		
		$json_response = json_decode($response);
		if($code != 200 && is_null($json_response)) {
			throw new HttpException("Request have errors", $code);
		}else{
			$json_return = $json_response->return;
			if($json_return->status != 200) {
				throw new ApiException($json_return->message, $json_return->status);
			}					
			return $json_response->entries;
		}				
	}	
	public function send($apiKey,$sender,$receptor,$message,$localid)
 	{
		$path =  $this->get_path($apiKey,"send","sms");
		$params = array(
				"receptor" => is_array($receptor) ? implode(",",$receptor) : $receptor , 
				"sender"   => $sender, 
				"message"  => $message,
				"localid"  => $localid,
				
		);
		$result=$this->execute($path,$params);
		return $result;
 	}
	public function accountInfo($apiKey)
	{
		$path = $this->get_path($apiKey,"info","account");
		return $this->execute($path);
	}	
}
