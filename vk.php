<?php

class vk {
	
	private $id = 0;
	private $pass = '';
	private $phone = '';
	private $cookies = '';// адрес кукиес-файла
	private $useragent = 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.64 Safari/537.11';

    /**
     * @param int $id - id учетной записи
     * @param string $pass - пароль учетной записи
     * @param string $phone - номер телефона учетной записи (в виде +74991234567)
     */
    function __construct($id = 0, $pass = '', $phone = ''){
        $this->id = 0;
        $this->pass = '';
        $this->phone = '';
		$this->cookies = dirname(__FILE__).'/cookies.txt';
		$this->auth();
		//$this->otherCountry();// ранее было нужно, теперь похоже не нужно
	}

    /**
     * должен вернуть яваскрипт код, который запускает функцию яваскрипт-переадресации пользователя на его страницу
     * @return mixed
     */
    private function auth(){
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, 'http://login.vk.com/?act=login');
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($c, CURLOPT_COOKIEJAR, $this->cookies);
		curl_setopt($c, CURLOPT_POST, 1);
		curl_setopt($c, CURLOPT_USERAGENT, $this->useragent);
		$e = urlencode($this->phone);
		$p = urlencode($this->pass);
		$s = 'act=login&q=1&al_frame=1&expire=&captcha_sid=&captcha_key=&from_host=vk.com&email=' . $e . '&pass=' . $p;
		curl_setopt($c, CURLOPT_POSTFIELDS, $s);
		$r = curl_exec($c);
		curl_close($c);
		return $r;
	}

    /**
     * необходимо вызывать если запрос делается скриптом с сервера расположенном не в РФ
     *
     * @return mixed
     * @throws Exception
     */
    private function otherCountry(){
		
		$data = $this->getToHash();
		
		if( empty($data['to']) ){
			throw new Exception();
			exit;
		}
		
		if( empty($data['hash']) ){
			throw new Exception();
			exit;
		}
		
		// подтверждаем своим номером телефона (вернее последними 4 цифрами)
		$c = curl_init();
		//curl_setopt($c, CURLOPT_REFERER, 'http://login.vk.com/username');
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($c, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($c, CURLOPT_COOKIEJAR, $this->cookies);
		curl_setopt($c, CURLOPT_COOKIEFILE, $this->cookies);
		curl_setopt($c, CURLOPT_POST, 1);
		curl_setopt($c, CURLOPT_POSTFIELDS, 'act=security_check&code='.mb_substr($this->phone,-4).'&to='.$data['to'].'&al_page=3&hash='.$data['hash']);
		curl_setopt($c, CURLOPT_URL, 'http://vk.com/login.php');
		$r = curl_exec($c);
		curl_close($c);
		return $r;
	}

    /**
     * парсит страницу пользователя и находит параметры
     *
     * @return array
     */
     private function getToHash(){
		
		$to = $hash = '';
		
		/* следующий код для нахождения $to и $hash */
		$c = curl_init();
		curl_setopt($c, CURLOPT_REFERER, 'http://login.vk.com/?act=login');
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($c, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($c, CURLOPT_COOKIEJAR, $this->cookies);
		curl_setopt($c, CURLOPT_COOKIEFILE, $this->cookies);
		curl_setopt($c, CURLOPT_URL, 'http://vk.com/id'. $this->id);
		$r = curl_exec($c);
		curl_close($c);
		$ex = explode("to: '", $r);
		if($ex['1']){
			$ex = explode("'", $ex['1']);
			$to = $ex['0'];
		}
		$ex = explode("hash: '", $r);
		if($ex['1']){
			$ex = explode("'", $ex['1']);
			$hash = $ex['0'];
		}
		
		return array('to' => $to, 'hash' => $hash);
	}
	
	private $hash = '';// хэш определенного пользователя
	private $to_id = 0;// id определенного пользователя

    /**
     * Указывает, какому пользователю нужно отправить сообщение
     *
     * определяет хэш указанной учетной записи и устанавливает переменную to_id
     *
     * @param int $id - пользователь, которому нужно отправить сообщение
     * @throws Exception
     */
    function sendTo($id = 0){
		
		if( empty($id) || !is_numeric($id) ){
			throw new Exception();
			exit;
		}
		
		sleep(1);
		
		$c = curl_init();
		curl_setopt($c, CURLOPT_HEADER, 1);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_REFERER, 'http://vk.com/settings.php');
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($c, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($c, CURLOPT_COOKIEJAR, $this->cookies);
		curl_setopt($c, CURLOPT_COOKIEFILE, $this->cookies);
        /* отправить сообщение в группу можно так:
        curl_setopt($c, CURLOPT_URL, 'http://vk.com/club'. $id); */
		curl_setopt($c, CURLOPT_URL, 'http://vk.com/id'. $id);
		$r = curl_exec($c);
		curl_close($c);
		$ex = explode('"post_hash":"', $r);
		if($ex['1']){
			$ex = explode('"', $ex['1']);
			if( !empty($ex['0']) ){
				$this->hash = $ex['0'];
				$this->to_id = $id;
			}
		}
	}

    /**
     * отправляет сообщение пользователю на стену (а если стена закрыта, то в личку)
     *
     * @param string $message - сообщение
     * @param string $pluse - дополнительные данные (медиа)
     * @return mixed - возвращает содержимое стены
     * @throws Exception
     */
    function message($message='', $pluse = ''){
		
		if( empty($this->to_id) || !is_numeric($this->to_id) ){
			throw new Exception();
			exit;
		}
		
		if( empty($this->hash) ){
			throw new Exception();
			exit;
		}
		
		if( empty($message) ){
			throw new Exception();
			exit;
		}
		
		sleep(1);// методом тыка доказано, что нужна пауза
		
		$q = 'act=post&al=1&hash='.$this->hash.'&Message='.urlencode($message).'&to_id='.$this->to_id.'&type=all'.$pluse;
		$c = curl_init();
		curl_setopt($c, CURLOPT_HEADER, 0);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('X-Requested-With: XMLHttpRequest'));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_POST, 1);
		curl_setopt($c, CURLOPT_REFERER, 'http://vk.com/settings');
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($c, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($c, CURLOPT_POSTFIELDS, $q);
		curl_setopt($c, CURLOPT_COOKIEJAR, $this->cookies);
		curl_setopt($c, CURLOPT_COOKIEFILE, $this->cookies);
		curl_setopt($c, CURLOPT_TIMEOUT, 15);
		curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 15);
		curl_setopt($c, CURLOPT_URL, 'http://vk.com/al_wall.php');
		$r = curl_exec($c);
		curl_close($c);
		return $r;
	}
	
	/**
	* вспомогательная фукнция - просто создает файл по указанному пути и пишет в него лог-данные
	*/
	function file($path='', $message=''){
		
		if($path){
			
			if($fp = fopen($path, 'a')){
				fwrite ($fp, ($message? $message : '------------Date: '.date("H:i:s d.m.Y")."\n"));
				fclose ($fp);
				@chmod($path, 0664);
			}
		}
	}
}