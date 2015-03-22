<?php

ini_set('memory_limit','64M');
require 'FrameworkRunner.php';

$handle = file_get_contents('testdata.json');

$fr = new FrameworkRunner($handle);

$sem_start = microtime(true);
$fr->getTheSemantics();
$sem_end = microtime(true);

$res_start = microtime(true);
$res = $fr->getTheResults();
$res_end = microtime(true);


$sem = $sem_end - $sem_start;
$res = $res_end - $res_start;

echo "Computing the semantics takes $sem seconds";
echo "Computing the results takes $res seconds";

var_dump($res);

?>
