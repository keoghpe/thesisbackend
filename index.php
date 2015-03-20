<?php

require 'vendor/autoload.php';
require 'FrameworkRunner.php';

$app = new \Slim\Slim();

$app->get('/knowledgebases/', function () use ($app){

  $d = dir('./knowledge_bases');
  $ls = array();

  while (false !== ($entry = $d->read())) {
    if($entry[0] === "."){
      continue;
    }
     array_push($ls, $entry);
  }
  $d->close();

  header('Content-Type: application/json');
  echo json_encode($ls);
});

$app->get('/knowledgebases/:name', function ($name) use ($app){

  $response = file_get_contents('./knowledge_bases/' . $name . ".json");

  header('Content-Type: application/json');
  echo $response;
});

$app->get('/knowledgebases/:name', function ($name) use ($app){

  $response = file_get_contents('./knowledge_bases/' . $name . ".json");

  header('Content-Type: application/json');
  echo $response;
});

$app->post('/knowledgebases/:name', function($name) use ($app){

  $body = $app->request->getBody();

  $fp = fopen('./knowledge_bases/' . $name . '.json', 'w');
  fwrite($fp, $body);
  fclose($fp);

  header('Content-Type: application/json');
  echo json_encode(array("Result" => "Saved"));

});


$app->get('/datasets/', function () use ($app){

  $d = dir('./data_sets');
  $ls = array();

  while (false !== ($entry = $d->read())) {
    if($entry[0] === "."){
      continue;
    }
     array_push($ls, $entry);
  }
  $d->close();

  header('Content-Type: application/json');
  echo json_encode($ls);
});


$app->post('/', function () use ($app){

  $body = $app->request->getBody();

  $fr = new FrameworkRunner($body);

  header('Content-Type: application/json');
  echo json_encode($fr->getTheResults());

});

$app->run();

?>
