<?php

require 'vendor/autoload.php';
require 'FrameworkRunner.php';

$app = new \Slim\Slim();

$app->get('/knowledgebases/', function () use ($app){

  $d = dir('./knowledge_bases');
  $ls = array();

  while (false !== ($entry = $d->read())) {
    if($entry === "." || $entry === ".."){
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
    if($entry === "." || $entry === ".."){
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





  // $decoded_body = json_decode($body);
  //
  // $attacks = "[";
  // $first = true;
  //
  // foreach($decoded_body->links as $attack){
  //
  //   if(!$first){
  //     $attacks .= ",";
  //   } else{
  //     $first = false;
  //   }
  //
  //   $attacks .= "[";
  //   $attacks .= $attack->target->id;
  //   $attacks .= ",";
  //   $attacks .= $attack->source->id;
  //   $attacks .= "]";
  //
  // }
  // $attacks .= "]";
  //
  // $arguments = "[";
  // $first = true;
  // foreach($decoded_body->nodes as $argument){
  //
  //   foreach($argument->membership_functions as $memfunc){
  //     foreach($memfunc->points as $point){
  //       //echo "x : " . $point->x;
  //       //echo "y : " . $point->y . "\n";
  //     }
  //   }
  //
  //   if(!$first){
  //     $arguments .= ",";
  //   } else{
  //     $first = false;
  //   }
  //
  //   $arguments .= $argument->id;
  // }
  //
  // $arguments .= "]";
  //
  // exec("java -jar javaDung.jar $attacks $arguments", $output);
  // $JSONoutput = json_decode(implode($output, "\n"));

  // header('Content-Type: application/json');
  // echo json_encode($JSONoutput);
});

$app->run();

?>
