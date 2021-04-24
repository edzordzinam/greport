<html>

<head>
<script src="../js/jquery.min.js" type="text/javascript"></script>
<script src="../js/jquery-ui-1.8.16.custom.min.js"
	type="text/javascript"></script>
<script type="text/javascript" src="jquery.webcam.js"></script>

<script>

  function closecam(){
   $("#webcam").hide();
  }

  function showcam(){
	   $("#webcam").show();
	  }

  function createcam(){
	  
		 
	  var pos = 0, ctx = null, saveCB, image = [];

      var canvas = document.createElement("canvas");
      canvas.setAttribute('width', 160);
      canvas.setAttribute('height', 120);
      
      if (canvas.toDataURL) {

              ctx = canvas.getContext("2d");
              
              image = ctx.getImageData(0, 0, 320, 240);
      
              saveCB = function(data) {
                      
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
                              $.post("./upload.php", {type: "data", image: canvas.toDataURL("image/png")});
                              pos = 0;
                      }
              };

      } else {

              saveCB = function(data) {
                      image.push(data);
                      
                      pos+= 4 * 320;
                      
                      if (pos >= 4 * 320 * 240) {
                              $.post("./upload.php", {type: "pixel", image: image.join('|')});
                              pos = 0;
                      }
              };
      }

      $("#webcam").webcam({

              width: 160,
              height: 120,
              mode: "callback",
              swffile: "jscam.swf",

              onSave: saveCB,

              onCapture: function () {
                      webcam.save();
              },

              debug: function (type, string) {
                      console.log(type + ": " + string);
              }
      });

      $("#btnCreateCam").remove();
  }
</script>
</head>

<body>

	<div id="webcam"></div>
    <canvas width="160" height="120" id="canvas"></canvas> 
 
    <button onclick="closecam();"> hide </button>
     <button id="btnCreateCam" onclick="createcam();"> create </button>
       <button onclick="showcam();"> show </button>
     
	<p style="width: 360px; text-align: center;">
		<a href="javascript:webcam.capture();void(0);">Take a picture
			instantly</a>
	</p>
    <p style="height: 22px; color: rgb(204, 0, 0); font-weight: bold;" id="status">
	<h3>Available Cameras</h3>
    <ul id="cams"></ul>

	<script type="text/javascript">

	
	window.addEventListener("load", function() {

 		var canvas = $("#content").find("#canvas");

		if (canvas.getContext) {
			ctx = canvas.getContext("2d");
			ctx.clearRect(0, 0, 160, 120);

			var img = new Image();
			//img.src = "/static/logo.gif";
			img.onload = function() {
				ctx.drawImage(img, 129, 89);
			};
			image = ctx.getImageData(0, 0, 320, 240);
		} 

	}, false);

	</script>

</body>

</html>