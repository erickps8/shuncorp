<?php
require("2/captcha.php");
$imagemCaptcha = imagecreatefrompng("captcha.png");
$textoCaptcha = substr(md5(uniqid('')),-9,9);

?>

<img src="captcha.php" >
