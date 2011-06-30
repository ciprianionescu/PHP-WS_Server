<?php

class Auth{

	public function __construct(){
	}
	
	public function getClientID(&$messageHandler, $parameters){
		$clientRef = $messageHandler->clientRef;
		$clientRef->send(chr(0)."Auth_setClientID:".$clientRef->clientID.chr(255));
		
		//warn all the users with the subscribeToClientUpdates flag set to true that a new client exists;
		$clientRef->serverRef->broadcastMessage(chr(0)."Auth_newClientID:".$clientRef->clientID.chr(255));
	}
	
	public function enableClientUpdates(&$messageHandler, $parameters){
		$clientRef = $messageHandler->clientRef;
		$clientRef->subscribeToClientUpdates = true;
		
		foreach($clientRef->serverRef->aClients as $oClient){
			if($oClient->subscribeToClientUpdates == true){
				$clientRef->send(chr(0)."Auth_newClientID:".$oClient->clientID.chr(255));
                                if(isset($oClient->dataSet['NickName'])){
                                    $clientRef->send(chr(0)."Comunity_nickNameChanged:".$oClient->clientID."/".$oClient->dataSet['NickName'].chr(255));
                                }else{
                                    $clientRef->send(chr(0)."Comunity_nickNameChanged:".$oClient->clientID."/&nbsp;".chr(255));
                                }
			}
		}
	}

}

?>