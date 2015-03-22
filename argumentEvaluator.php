<?php

require_once('bezier.php');

class ArgumentEvaluator extends Thread {

  private $theArgument;

  function __construct(&$argument){
    $this->theArgument = $argument;
  }

  public function run(){

  }

}


public function evaluateSemantic($semantic){


  $semanticTotal = 0;
  $sem_count = 0;
  // for one semantic
  // go through each argument
  // for each membership function
  //    get the data point
  //    get it's corresponding value
  //


  // THIS NEEDS TO BE MADE EFFICIENT
  // FOR EACH ARGUMENT
  //


  foreach($semantic as $arg){

    // CHECK IF THE ARGUMENT HAS A RESULT
    //    IF IT DOES ADD IT TO THE SEMANTIC TOTAL AND CONTINUE
    // IF NOT
    //    GO THROUGH EACH MEMBERSHIP FUNCTION
    //        GET THE RESULT
    //        SAVE THE INPUT AND OUTPUT
    //    SAVE THE AVERAGE IN THE NODE AND ADD IT TO THE SEMANTIC TOTAL

    $argtotal = 0;
    $memfunc_count = 0;
    //var_dump($this->nodes[$arg]->membership_functions);
    if($arg === ""
    || $this->nodes[$arg]->output_function->title === "Mitigating Argument"){
      continue;
    }

    //
    //
    //

    $memfuncs = $this->nodes[$arg]->membership_functions;

    foreach($memfuncs as $i => $memfunction){

      $pointsList = [];

      foreach($memfunction->points as $point){
        array_push($pointsList, new Point($point->x, $point->y));
      }

      $myBez = new Bezier($pointsList);

      $argtotal += $myBez->yFromX($this->data->{$memfunction->xLabel});
      $memfunc_count++;
    }

    // Get the averages of the truth
    if($memfunc_count === 0){
      $average = 0;
    } else {
      $average = $argtotal / $memfunc_count;
    }
    //get the corresponding output value for $average

    //
    //
    //

    $pointsList = [];

    foreach($this->nodes[$arg]->output_function->points as $point){
      array_push($pointsList, new Point($point->x, $point->y));
    }

    $myBez = new Bezier($pointsList);

    $semanticTotal += $myBez->yFromX($average);
    $sem_count++;
  }

  if($sem_count === 0){
    return 0;
  } else {
    return $semanticTotal / $sem_count;
  }
}





?>
