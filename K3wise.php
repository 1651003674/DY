<?php
$s = '中文abc';
$s = iconv('utf-8', 'utf-8', $s); //不是 utf-8 时需转 utf-8
print_r($s);
//print_r(unpack('C*', $s));