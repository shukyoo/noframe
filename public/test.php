<?php
require dirname(__DIR__) . '/boot.php';


$a = \Lib\Request::path();
echo '<br>';
print_r($a);