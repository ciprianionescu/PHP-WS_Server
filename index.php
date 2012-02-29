<?php
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<title>Web Sockets Demo</title>
		<script type="text/javascript" src="assets/script/jquery-1.4.2.min.js"></script>
		<script type="text/javascript" src="assets/script/swfobject.js"></script>
		<script type="text/javascript" src="assets/script/FABridge.js"></script>
                <script type="text/javascript" src="assets/script/web_socket.js"></script>
		<script type="text/javascript" src="assets/script/scripts.js"></script>
	
		<link rel="stylesheet" href="assets/css/style.css">
	</head>
	<body>
			<div id=app-header>
				<div id=app-header-nickname>
					<label for=nickname>Nickname</label>
					<input type="text" name=nickanme id=nickname>
				</div>
				<div id=app-header-mesaj>
					<label for=mesaj>Mesaj</label>
					<input type="text" name=mesaj id=mesaj>
				</div>
				<div id=afiseaza-toti-utilizatorii>
					<div id=lista-utilizatori>
						<ul>
						</ul>
					</div>
					<div id=afiseaza-toti-utilizatorii-link>
						<a href="#afiseaza-utilizatori">Afiseaza toti utilizatorii</a>
					</div>
				</div>
			</div>
			<div id=app-container>
                            <canvas id="draw-pad" width="640" height="480">
                            </canvas>
			</div>
			<div id=app-footer>
			</div>
	</body>
</html>
