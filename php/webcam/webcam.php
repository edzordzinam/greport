<html>

<head>
<script src="../js/jquery.min.js" type="text/javascript"></script>
<script src="../js/jquery-ui-1.8.16.custom.min.js"
	type="text/javascript"></script>
<script type="text/javascript" src="jquery.webcam.js"></script>
</head>

<body>

	<div id="webcam"></div>
    <canvas width="134" height="134" id="canvas"></canvas> 
 
 
    
	<p style="width: 360px; text-align: center;">
		<a href="javascript:webcam.capture();void(0);">Take a picture
			instantly</a>
	</p>
    <p style="height: 22px; color: rgb(204, 0, 0); font-weight: bold;" id="status">
	<h3>Available Cameras</h3>
    <ul id="cams"></ul>

	<script type="text/javascript">

	var pos = 0;
	var ctx = null;
	var cam = null;
	var image = null;

	var filter_on = false;
	var filter_id = 0;


	jQuery("#webcam").webcam({

		width: 320,
		height: 240,
		mode: "save",
		swffile: "jscam.swf",

		onSave: function(data) {

		    var col = data.split(";");
		    var img = image;

		    for(var i = 0; i < 320; i++) {
		        var tmp = parseInt(col[i]);
		        img.data[pos + 0] = (tmp >> 16) & 0xff;
		        img.data[pos + 1] = (tmp >> 8) & 0xff;
		        img.data[pos + 2] = tmp & 0xff;
		        img.data[pos + 3] = 0xff;
		        pos+= 4;
		    }

		    if (pos >= 4 * 320 * 240) {
		        ctx.putImageData(img, 0, 0);
		        pos = 0;
		    }
		},

		onTick: function(remain) {

			if (0 == remain) {
				jQuery("#status").text("Cheese!");
			} else {
				jQuery("#status").text(remain + " seconds remaining...");
			}
		},
		
		onCapture: function () {
			webcam.save('./upload.php');

			//jQuery("#flash").css("display", "block");
			//jQuery("#flash").fadeOut(100, function () {
				//jQuery("#flash").css("opacity", 1);
			//});
		},

		debug: function (type, string) {
			jQuery("#status").html(type + ": " + string);
		},
		
		onLoad: function () {
			var cams = webcam.getCameraList();
			for(var i in cams) {
				jQuery("#cams").append("<li>" + cams[i] + "</li>");
			}
		}
	});


	function getPageSize() {

	/*	var xScroll, yScroll;

		if (window.innerHeight || window.scrollMaxY) {
			xScroll = window.innerWidth + window.scrollMaxX;
			yScroll = window.innerHeight + window.scrollMaxY;
		} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
			xScroll = document.body.scrollWidth;
			yScroll = document.body.scrollHeight;
		} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
			xScroll = document.body.offsetWidth;
			yScroll = document.body.offsetHeight;
		}

		var windowWidth, windowHeight;

		if (self.innerHeight) { // all except Explorer
			if(document.documentElement.clientWidth){
				windowWidth = document.documentElement.clientWidth;
			} else {
				windowWidth = self.innerWidth;
			}
			windowHeight = self.innerHeight;
		} else if (document.documentElement || document.documentElement.clientHeight) { // Explorer 6 Strict Mode
			windowWidth = document.documentElement.clientWidth;
			windowHeight = document.documentElement.clientHeight;
		} else if (document.body) { // other Explorers
			windowWidth = document.body.clientWidth;
			windowHeight = document.body.clientHeight;
		}

		// for small pages with total height less then height of the viewport
		if(yScroll < windowHeight){
			pageHeight = windowHeight;
		} else {
			pageHeight = yScroll;
		}

		// for small pages with total width less then width of the viewport
		if(xScroll < windowWidth){
			pageWidth = xScroll;
		} else {
			pageWidth = windowWidth;
		}*/

		return [500, 500];
	}
	
	window.addEventListener("load", function() {

 		var canvas = document.getElementById("canvas");

		if (canvas.getContext) {
			ctx = document.getElementById("canvas").getContext("2d");
			ctx.clearRect(0, 0, 160, 120);

			var img = new Image();
			//img.src = "/static/logo.gif";
			img.onload = function() {
				ctx.drawImage(img, 129, 89);
			}
			image = ctx.getImageData(0, 0, 320, 240);
		} 
		
		//var pageSize = getPageSize();
		//jQuery("#flash").css({ height: pageSize[1] + "px" });

	}, false);

	</script>

</body>

</html>