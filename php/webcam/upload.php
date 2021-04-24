<?php
require '../ini_config/session.php';
require_once "../class/AccessClass.php";
require_once "../class/UtilsClass.php";
require_once "../class/GenericPerson.php";
// $str = file_get_contents("php://input");
// file_put_contents("./tmp/upload.jpg", pack("H*", $str));

if ($_POST['type'] == "pixel") {
	// input is in format 1,2,3...|1,2,3...|...
	$im = imagecreatetruecolor(160, 120);

	foreach (explode("|", $_POST['image']) as $y => $csv) {
		foreach (explode(";", $csv) as $x => $color) {
			imagesetpixel($im, $x, $y, $color);
		}
	}
} else {
	// input is in format: data:image/png;base64,...
	$im = imagecreatefrompng($_POST['image']);
}

// do something with $im

if (isset($_POST['isHHPicture']) && $_POST['isHHPicture'] == 'true'){
	//generate new GPSN for picture

	$newPickUPID = $_POST['PKID'];

	if ($newPickUPID == null){
		exit(json_encode(array('studentID' =>  '00000000')));
	}

	imagepng($im, './pixs/'.$newPickUPID.'.jpg' , 0);

	$studentID = $newPickUPID;

}
else{

   if ($_POST['studentID'] == ''){
   	exit (json_encode(array('studentID' =>  'No student specified')));
   }

    imagepng($im, './pixs/'.$_POST['studentID'].'.jpg' , 0);
    $studentID = $_POST['studentID'];
}

    imagedestroy($im);

    echo json_encode(array('studentID' =>  $studentID));


?>