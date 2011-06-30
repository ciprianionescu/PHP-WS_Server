<?php

class WS_MessageHandler{
	
	var $serverRef;
	var $clientRef;
	
	public function parseMessage(&$oServer, &$oClient, $sMessage){
		
		$this->serverRef = $oServer;
		$this->clientRef = $oClient;
		
		if($sMessage[strlen($sMessage)-1] == chr(255)){
			$sMessage = substr($sMessage, 0, strlen($sMessage)-1);
		}
		if($sMessage[0] == chr(0)){
			$sMessage = substr($sMessage, 1);
		}
		
		$dashPos = strpos($sMessage, "|");
		$className = substr($sMessage, 0, $dashPos);
		
		$firstColonPos = strpos($sMessage, ":", $dashPos);
		if($firstColonPos){
			$functionName = substr($sMessage, $dashPos+1, $firstColonPos-$dashPos-1);
		}else{
			$functionName = substr($sMessage, $dashPos+1);
		}

		$paramsArray = array();
		if($firstColonPos){
			$paramsString = substr($sMessage, $firstColonPos+1);
			$matches = preg_split('/\"+,+ *\"+/', $paramsString);
			if(count($matches)){
				if($matches[0][0]=="\"") $matches[0] = substr($matches[0], 1);
				//if(count($matches)>1){
					if($matches[count($matches)-1][strlen($matches[count($matches)-1])-1] == "\""){
						$matches[count($matches)-1] = substr($matches[count($matches)-1], 0, strlen($matches[count($matches)-1])-1);
					}
				//}
				$paramsArray = $matches;
			}
		}
		if($className!=""){
			if(class_exists($className)){
				if(method_exists($className, $functionName)){
					$className::$functionName($this, $paramsArray);
				}else{
					$this->serverRef->log('ERR: Class Found but method not available');
				}
			}else{
				$this->serverRef->log('ERR: Invalid Class call');
			}
		}else{
			$this->serverRef->log('ERR: Invalid Class call');
		}
	}
}

?>