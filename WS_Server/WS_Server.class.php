<?php

function __autoload($class_name) {
    require __DIR__."/Handlers/{$class_name}/{$class_name}.class.php";
}

/*
 *
 * This class allows the user to instantiate a WebSockets server.
 *
 */

class WS_Server {
	var $sHost;
	var $sResource;
	var $iListenPort;
	var $iFlashListenPort;
	var $aListeners;
	var $aSockets = array();
	var $aClients = array();
	var $bSupportFlashCallback;
	var $MasterSocket;
	var $FlashPolicySocket;
	
	public function __construct ($sHost, $iListenPort)
	{
		require_once("WS_Client.class.php");
		require_once("WS_MessageHandler.class.php");
		
		if(!$sHost) $sHost = $_SERVER['HTTP_HOST'];
		if(!$iListenPort) $iListenPort = 8080;
		$this->sHost = $sHost;
		$this->iListenPort = $iListenPort;
	}

	public function __destruct()
	{
		//implement destructor
	}
	
	private function addClient($rNewClientSocket){
		$newWS_Client = new WS_Client($rNewClientSocket, $this) or die("test");
		array_push($this->aSockets, $rNewClientSocket);
		array_push($this->aClients, $newWS_Client);
		$this->log("socket accepted: $rNewClientSocket");
	}
	
	private function removeClientBySocket($rClientSocket){
		$totalClients = count($this->aClients);
		for($i=0;$i<$totalClients;$i++){
			if($this->aClients[$i]->rSocket == $rClientSocket){
				$found=$i;
				break; 
			}
		}
		
                if(!is_null($found)){
                    $this->broadcastMessage(chr(0)."Auth_clientDisconected:".$this->aClients[$found]->clientID.chr(255));
                    array_splice($this->aClients,$found,1); 
		}
		
		$index = array_search($rClientSocket, $this->aSockets);
		if($index>=0){
			array_splice($this->aSockets, $index,1);
		}
		$this->log("socket closed: $rClientSocket");
	}
	
	private function findClientBySocket($rSocket){
		foreach($this->aClients as $oWS_Client){
			if($oWS_Client->rSocket == $rSocket){
				return $oWS_Client;
			}
		}
		return null;
	}
	
	public function handleClientResponse(&$oClient, $sMessage){
		$this->broadCastMessage($sMessage);
	}
	
	public function broadcastMessage($sMessage){
		foreach ($this->aClients as $oClient){
			if($oClient->subscribeToClientUpdates == true){	
				$oClient->send($sMessage);
			}
		}
	}
	
	public function startListening()
	{
		$this->MasterSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or $this->logAndDie("socket_option() failed");
		socket_set_option($this->MasterSocket, SOL_SOCKET, SO_REUSEADDR, 1) or $this->logAndDie("socket_option() failed");
		socket_bind($this->MasterSocket, $this->sHost, $this->iListenPort) or $this->logAndDie("socket_bind() failed");
		socket_listen($this->MasterSocket,20) or $this->logAndDie("socket_listen() failed");
		$this->aSockets = array($this->MasterSocket);
		
		$this->FlashPolicySocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or $this->logAndDie("socket_option() failed");
		socket_set_option($this->FlashPolicySocket, SOL_SOCKET, SO_REUSEADDR, 1) or $this->logAndDie("socket_option() failed");
		socket_bind($this->FlashPolicySocket, $this->sHost, $this->iFlashListenPort) or $this->logAndDie("socket_bind() failed");
		socket_listen($this->FlashPolicySocket,20) or $this->logAndDie("socket_listen() failed");
		array_push($this->aSockets, $this->FlashPolicySocket);
		
		//start the infinite loop
		while(true){
			$aSocketsList = $this->aSockets;
			socket_select($aSocketsList, $nullWrite=NULL, $nullExcept=NULL, NULL);
			
			foreach ($aSocketsList as $rSocket){
				if($rSocket == $this->MasterSocket)
				{
					$rNewClientSocket = socket_accept($this->MasterSocket);
					if($rNewClientSocket>0){
						$this->addClient($rNewClientSocket);
					}else{
						$this->log("socket_accept() failed");
					}
				}
				else
				{
					$sRecvSize = socket_recv($rSocket, $sRecvBuffer, 2048, 0);
					if($sRecvSize == 0){
						$this->removeClientBySocket($rSocket);
					}else{
						
						if(substr($sRecvBuffer,0, 22) == "<policy-file-request/>"){
							$this->log("FlashPolicy Requested");
							$crossFile = file("crossdomain.xml");
							$crossFile = join('',$crossFile);
							socket_write($rSocket, $crossFile, strlen($crossFile));
							socket_close($rSocket);
							$index = array_search($rSocket, $this->aSockets);
							if($index>=0){
								array_splice($this->aSockets, $index,1);
							}
							$this->log("FlashPolicy Sent and socket closed");
						}else{
							$oWS_Client = $this->findClientBySocket($rSocket);
							if($oWS_Client){
								$this->findClientBySocket($rSocket)->handleMessage($sRecvBuffer);
							}else{
								$this->log("Client Not Found");
							}
						}
					}
				}
			}
		}

		
	}
	
	public function log($sMessage){
		print_r("On " . date("Y-m-d H:m:s") . " -> ". $sMessage . "\n");
	}
	
	private function logAndDie($sMessage){
		$this->log($sMessage);
		die();
	}
	
}

?>