<?php
session_start();
 
// Definir o header como image/png para indicar que esta página contém dados
// do tipo image->PNG
header("Content-type: image/png");
 
// Criar um novo recurso de imagem a partir de um arquivo
$imagemCaptcha = imagecreatefrompng("captcha.png")
or die("Não foi possível inicializar uma nova imagem");
 
//Carregar uma nova fonte
$fonteCaptcha = imageloadfont("arialgd.gdf");
 
// Criar o texto para o captcha
$textoCaptcha = substr(md5(uniqid('')),-5,5);
 
// Guardar o texto numa variável session
$_SESSION['session_textoCaptcha'] = $textoCaptcha;
 
// Indicar a cor para o texto
$corCaptcha = imagecolorallocate($imagemCaptcha,0,0,0);
 
// Escrever a string na cor escolhida
imagestring($imagemCaptcha,$fonteCaptcha,5,5,$textoCaptcha,$corCaptcha);
 
// Mostrar a imagem captha no formato PNG.
// Outros formatos podem ser usados com imagejpeg, imagegif, imagewbmp, etc.
imagepng($imagemCaptcha);
 
// Liberar memória
imagedestroy($imagemCaptcha);
 
?>
