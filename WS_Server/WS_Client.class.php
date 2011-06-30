<?php

class WS_Client{
	var $rSocket;
	var $bIsHandshaked = false;
	var $serverRef;
	var $requestHeader = array();
	var $protocol = 0;
	var $clientID = "";
	var $subscribeToClientUpdates = false;
        var $dataSet = array();
	
	public function __construct($rNewClientSocket, &$serverRef){
		$this->rSocket = $rNewClientSocket;
		$this->serverRef = $serverRef;
	}
	
	public function __destruct(){
		socket_close($this->rSocket);
	}

	public function send($sMessage, $wrap=true){
	print_r($this->rSocket);
		if($this->protocol == 1){
			$outputBuffer = $sMessage;
			$this->serverRef->log('Sending Message using protocol 1:'.$outputBuffer);
			socket_write($this->rSocket, $outputBuffer, strlen($outputBuffer));
		}
		if($this->protocol == 2){
			$outputBuffer = $sMessage;
			$this->serverRef->log('Sending Message using protocol 2:'.$outputBuffer);
			socket_write($this->rSocket, $outputBuffer, strlen($outputBuffer));
		}
	}
	
	public function handleMessage($sMessage){
		if(!$this->bIsHandshaked){
			$this->handShake($sMessage);
		}else{
			$this->serverRef->log('UserID:' . $this->clientID . " -> " . $sMessage);
			WS_MessageHandler::parseMessage($this->serverRef, $this, $sMessage);
		}
	}
	
	private function handShake($sHeader){
		print_r($sHeader);
		$headerLines = explode("\n", $sHeader);
		foreach ($headerLines as $key=>$headLine){
			$colonPos = strpos($headLine,":");
			if($colonPos>0){
				$this->requestHeader[substr($headLine, 0, $colonPos)] = substr($headLine, $colonPos+2);
			}
		}
		if(!isset($this->requestHeader['Sec-WebSocket-Key1'])){
			//handshake using the old protocol. Still used by Chrome
			$responseHeader = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n";
			$responseHeader.= "Upgrade: WebSocket"."\r\n";
			$responseHeader.= "Connection: Upgrade"."\r\n";
			$responseHeader.= "WebSocket-Origin: http://".$this->serverRef->sHost."\r\n";
			$responseHeader.= "WebSocket-Location: ws://".$this->serverRef->sHost.":8080/"."\r\n\r\n";
			$this->protocol = 1;
			$this->send($responseHeader);
			$this->bIsHandshaked = true;
		}else{
			$number1 = ereg_replace("[^0-9]", "", $this->requestHeader['Sec-WebSocket-Key1']);
			$number2 = ereg_replace("[^0-9]", "", $this->requestHeader['Sec-WebSocket-Key2']);
			$spaces1 = strlen(ereg_replace("[^ ]", "", $this->requestHeader['Sec-WebSocket-Key1']));
			$spaces2 = strlen(ereg_replace("[^ ]", "", $this->requestHeader['Sec-WebSocket-Key2']));
			$responseHeader = "HTTP/1.1 101 WebSocket Protocol Handshake"."\r\n";
			$responseHeader.= "Upgrade: WebSocket"."\r\n";
			$responseHeader.= "Connection: Upgrade"."\r\n";
			$responseHeader.= "Sec-WebSocket-Origin: http://".$this->serverRef->sHost."\r\n";
			$responseHeader.= "Sec-WebSocket-Location: ws://".$this->serverRef->sHost.":8080/"."\r\n\r\n";
			$key1_val = $number1/$spaces1;
			$key2_val = $number2/$spaces2;
			$responseHeaderKey = pack("N", $key1_val).pack("N", $key2_val).substr($sHeader, strlen($sHeader)-8);
			$responseHeaderKeyMD5 = md5($responseHeaderKey, true);
			$responseHeader.= $responseHeaderKeyMD5;
			$this->protocol = 2;
			$this->send($responseHeader);
			$this->bIsHandshaked = true;
		}
		$this->getUniqueID();
	} 
	
	private function getUniqueID(){
			$tmpKey = chr(rand(65,90)).chr(rand(65,90)).chr(rand(65,90)).chr(rand(65,90)).chr(rand(65,90)).chr(rand(65,90)).chr(rand(65,90)).chr(rand(65,90));
			$foundUniqueKey = 1;
			foreach($this->serverRef->aClients as $oClient){
				if($oClient->clientID == $tmpKey) {
					$foundUniqueKey = 0;
					$this->getUniqueID();
					die();
				}
			}
			if($foundUniqueKey == 1) $this->clientID = $tmpKey;
	}
	
}

?>