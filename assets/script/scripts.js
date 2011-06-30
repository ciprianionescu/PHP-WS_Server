/*
 * Variabile globale
 */

var ws; //obiectul WebSocket
var session_id; //session_id
var clientID;
var clientsArray = new Array();
var drawDataSent = 0;

/*
 * Functii globale
 */
	/*
	 * Quick Access to console.log();
	 */
	function c_log(message){
		if (console.log) {
			console.log(message);
		}else{
			alert(message);
		}
	}
	
	/*
	 * Init WebSockets
	 */
	function initWebSockets(){
		ws = new WebSocket("ws://localhost:8080");

		// Set event handlers.
		ws.onopen = wsOnOpen;
		ws.onmessage = wsMessageHandler;
		ws.onclose = wsOnClose;
		ws.onerror = wsError;
	}
	
	/*
	 * WebSocket open handler
	 */
	function wsOnOpen(){
		if(ws.readyState == 1){
			//do something amazing
			ws.send("Auth|getClientID");
		}else{
			ws.close();
		}
		c_log("socket opened");
	}
	
	/*
	 * WebSocket close handler
	 */
	function wsOnClose(){
		c_log("socket closed");
		//attempt to reconect after 5 seconds
		setTimeout('initWebSockets', 5000);
	}
	
	/*
	 * WebSocket error handler
	 */
	function wsError(){
		c_log("socket error");
	}
	
	/*
	 * WebSocket message handler
	 */
	function wsMessageHandler(e){
		call_function = e.data.substring(0, e.data.indexOf(":"));
		call_parameters = e.data.substring(e.data.indexOf(":")+1);
		eval(call_function + "('" + call_parameters + "')");
	}
	
	function Auth_setClientID(sClientID){
		clientsArray = new Array();
		clientID = sClientID;
		ws.send("Auth|enableClientUpdates");
	}
	
	function Auth_newClientID(sClientID){
                if($("#lista-utilizatori ul li[rel='"+sClientID+"']").size()==0){
                    $("<li rel=\""+sClientID+"\">&nbsp;</li>").appendTo("#lista-utilizatori ul").show();
                }
	}
        function Auth_clientDisconected(sClientID){
            $("#lista-utilizatori ul li[rel='"+sClientID+"']").remove();
        }
        function Comunity_nickNameChanged(sParams){
            sParams = sParams.split("/");
            sClientID = sParams[0];
            sNickName = sParams[1];
            $("#lista-utilizatori ul li[rel='"+sClientID+"']").html(sNickName);
        }
        function Comunity_postMessage(sParams){
            sParamsDelimPos = sParams.indexOf("/");
            sClientID = sParams.substring(0, sParamsDelimPos);
            sMessage = sParams.substring(sParamsDelimPos+1);
            sClientName = $("#lista-utilizatori ul li[rel='"+sClientID+"']").html();
            
            randomPosX = Math.round(Math.random() * $("body").width());
            randomPosY = Math.round(Math.random() * $("body").height());
            $("<div class=\"message-posted\"><span class=\"message-posted-body\">" + sMessage + "</span><span class=\"message-posted-client\" rel=\"" + sClientID + "\">by " + sClientName + "</span></div>").css({
                'left':randomPosX+"px",
                'top':randomPosY+"px"
            }).appendTo("#app-container").show().delay(1000).animate({
                'opacity':0.15,
                'font-size':'10px'
            }, 7000, function(){ $(this).remove() });
        }
        
        function Comunity_drawLine(sParams){
            params = sParams.split("/");
            drawPad = document.getElementById('draw-pad').getContext("2d");
            drawPad.strokeStyle = '#000000';
            drawPad.fillStyle   = '#00f';
            drawPad.strokeStyle = '#f00';
            drawPad.lineWidth   = 1;
            drawPad.beginPath();
            drawPad.moveTo(params[0], params[1]);
            drawPad.lineTo(params[2], params[3]);
            drawPad.stroke();
            drawPad.closePath();
        }
        
        function Comunity_drawDataSent(sParam){
            drawDataSent = 0;
        }
/*
 * Variabile globale
 */



/*
 * Do on document ready
 */

$(function(){

	WebSocket.__swfLocation = "assets/script/WebSocketMain.swf";
	// Set this to dump debug message from Flash to console.log:
	WebSocket.__debug = false;
	
	if(readCookie('session_id') != null){
		session_id = readCookie('session_id'); 
	}
	
	initWebSockets();
	
	/*
	 * Ataseaza eventuri pe link-ul "afiseaza toti utilizatorii" afiseaza-toti-utilizatorii-link>a
	 */
	
	$("#afiseaza-toti-utilizatorii-link a").click(function(){
		if($(this).html()=="Afiseaza toti utilizatorii"){
			$("#lista-utilizatori").stop().slideDown('fast');
			$(this).html("Ascunde utlizatorii");
		}else{
			$("#lista-utilizatori").stop().slideUp('fast');
			$(this).html("Afiseaza toti utilizatorii");
		}
		
	});
	
	/*
	 * Ataseaza eventuri pe li-urile ce contin referinta catre utilizatorii activi
	 */
	
	$("#lista-utilizatori ul li").live('mouseenter', function(){
		$(this).addClass('hovered');
	});
	
	$("#lista-utilizatori ul li").live('mouseleave', function(){
		$(this).removeClass('hovered');
	});
	
	$("#lista-utilizatori ul li").live('click', function(){
		if($(this).hasClass('active')){
			//deactivate
			ws.send("Comunity|unSubscribeToClient:\"" + $(this).attr('rel') + "\"");
			$(this).removeClass('active');
		}else{
			//activate
			ws.send("Comunity|subscribeToClient:\"" + $(this).attr('rel') + "\"");
			$(this).addClass('active');
		}
	});
	
	/*
	 * Ataseaza eventuri pe inputul de NickName
	 */
	
        $("#nickname").keyup(function(){
            ws.send("Comunity|updateMyNickName:\"" + $(this).val() + "\"");
        });
        
	/*
	 * Ataseaza eventuri pe inputul de Mesaj
	 */
	
        $("#mesaj").bind('keyup', function(e) {
            var code = (e.keyCode ? e.keyCode : e.which);
            if(code == 13) {
                ws.send("Comunity|postMessageToSubscribers:\"" + $(this).val() + "\"");
            }
        });
        
	/*
	 * Ataseaza eventuri pt draw-pad
	 */

        
        drawPad = document.getElementById('draw-pad').getContext("2d");
	mouseDown = 0;
	
	var lingrad = drawPad.createLinearGradient(0,0,100,$('body').height());  
	lingrad.addColorStop(0, '#ffffff');
	lingrad.addColorStop(0.5, '#aaaaaa');  
	lingrad.addColorStop(1, '#ffffff');  
	
	// assign gradients to fill and stroke styles  
	drawPad.fillStyle = lingrad;  

	drawPad.fillRect(0,0,$('body').width(),$('body').height());  
	
	drawPad.strokeStyle = '#000000';
	 drawPad.fillStyle   = '#00f';
          drawPad.strokeStyle = '#f00';
          drawPad.lineWidth   = 1;
       
	document.getElementById('draw-pad').addEventListener('mousemove', drawPad_mousemove, false);
	document.getElementById('draw-pad').addEventListener('mousedown', drawPad_mousedown, false);
	document.getElementById('draw-pad').addEventListener('mouseup', drawPad_mouseup, false);
        var mouseDown = 0;
        var lastPosX = 0;
        var lastPosY = 0;
        
        function drawPad_mousemove(evt){
            var x, y;
            if (evt.layerX || evt.layerX == 0) { // Firefox
                x = evt.layerX;
                y = evt.layerY;
            } else if (evt.offsetX || evt.offsetX == 0) { // Opera
                x = evt.offsetX;
                y = evt.offsetY;
            }
            
            if(mouseDown==1 && drawDataSent == 0){
                drawPad.lineWidth   = 1;
                drawPad.beginPath();
                drawPad.moveTo(lastPosX, lastPosY);
                drawPad.lineTo(x, y);
                drawPad.stroke();
                drawPad.closePath();
                ws.send('Comunity|drawLine:"'+lastPosX+'", "'+lastPosY+'", "'+x+'", "'+ y +'"');
                drawDataSent = 1;
                lastPosX = x;
                lastPosY = y;
            }
            

        }
        
        function drawPad_mousedown(evt){
            var x, y;
            if (evt.layerX || evt.layerX == 0) { // Firefox
                x = evt.layerX;
                y = evt.layerY;
            } else if (evt.offsetX || evt.offsetX == 0) { // Opera
                x = evt.offsetX;
                y = evt.offsetY;
            }
            lastPosX = x;
            lastPosY = y;
            mouseDown = 1;
        }
        
        function drawPad_mouseup(){
            mouseDown = 0;
        }

});


/*
 * Additional functions
 */

function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function eraseCookie(name) {
	createCookie(name,"",-1);
}
