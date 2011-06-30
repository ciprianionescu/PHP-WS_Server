<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Comunity
 *
 * @author Ciprian
 */
class Comunity {
    public function __construct(){
    }

    public function updateMyNickName(&$messageHandler, $parameters){
        $clientRef = $messageHandler->clientRef;
        $clientRef->dataSet['NickName'] = $parameters[0];

        //warn all the users with the subscribeToClientUpdates flag set to true that a new client exists;
        $clientRef->serverRef->broadcastMessage(chr(0)."Comunity_nickNameChanged:".$clientRef->clientID."/".$clientRef->dataSet['NickName'].chr(255));
    }
    
    public function subscribeToClient(&$messageHandler, $parameters){
        $clientRef = $messageHandler->clientRef;
        $clientToSubscribeTo = $parameters[0];
        $serverRef = $clientRef->serverRef;
        foreach($serverRef->aClients as $oClient){
            if($oClient->clientID == $clientToSubscribeTo){
                $oClient->dataSet['subscribers'][$clientRef->clientID] = true;
            }
        }
    }
    
    public function unSubscribeToClient(&$messageHandler, $parameters){
        $clientRef = $messageHandler->clientRef;
        $clientToUnSubscribeFrom = $parameters[0];
        $serverRef = $clientRef->serverRef;
        foreach($serverRef->aClients as $oClient){
            if($oClient->clientID == $clientToSubscribeTo){
                if(isset($oClient->dataSet['subscribers'][$clientRef->clientID])){
                    unset($oClient->dataSet['subscribers'][$clientRef->clientID]);
                }
            }
        }
    }
    
    public function postMessageToSubscribers(&$messageHandler, $parameters){
        $clientRef = $messageHandler->clientRef;
        $serverRef = $clientRef->serverRef;
        $sMessageToPost = $parameters[0];
        foreach($serverRef->aClients as $oClient){
            if(isset($oClient->dataSet['subscribers'][$oClient->clientID]) && $oClient->dataSet['subscribers'][$oClient->clientID]){
                $oClient->send(chr(0)."Comunity_postMessage:".$clientRef->clientID."/".$sMessageToPost.chr(255));
            }
        }
    }
    
    public function drawLine(&$messageHandler, $parameters){
        $clientRef = $messageHandler->clientRef;
        $serverRef = $clientRef->serverRef;
        foreach($serverRef->aClients as $oClient){
            if(isset($oClient->dataSet['subscribers'][$oClient->clientID]) && $oClient->dataSet['subscribers'][$oClient->clientID]){
                $oClient->send(chr(0)."Comunity_drawLine:{$parameters[0]}/{$parameters[1]}/{$parameters[2]}/{$parameters[3]}".chr(255));
            }
        }
        $clientRef->send(chr(0)."Comunity_drawDataSent:ok".chr(255));
    }
    
    
    public function doPing(&$messageHandler, $parameters){
    	$clientRef = $messageHandler->clientRef;
    	$serverRef = $clientRef->serverRef;
    	$clientRef->send(chr(0).$parameters[0].chr(255));
    }
}

?>
