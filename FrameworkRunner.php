<?php

require_once('bezier.php');

class FrameworkRunner{

  private $nodes, $links, $data, $semantics = [], $results = [];

  public function __construct($aString){
    $decoded = json_decode($aString);

    $this->nodes = $decoded->nodes;
    $this->links = $decoded->links;
    $this->data  = $decoded->data;
    $this->getActivatedArgs();
    $this->getTheSemantics();
    //$this->getTheResults();
  }

  public function getState(){
    $state = array(
      "nodes" => $this->nodes,
      "links" => $this->links,
      "data"  => $this->data
    );
    return json_encode($state);
  }

  public function getActivatedArgs(){
    //go through each argument
    //for each membership function
    //if the x axis label is not in the data
    // or if the value is null
    //    remove the argument and any attacks associated with it

    //var_dump($this->links);
    foreach($this->nodes as $id => $node){

      foreach($node->membership_functions as $memfunc){

        if(!array_key_exists($memfunc->xLabel, $this->data) ||
        $this->data->{$memfunc->xLabel} < $memfunc->xMin ||
        $this->data->{$memfunc->xLabel} > $memfunc->xMax){

          //remove from array
          unset($this->nodes[$id]);
          //echo "No value: $memfunc->xLabel in data. $id removed";

          foreach($this->links as $key => $link){
            if($link->target->id === $id || $link->source->id === $id){
              unset($this->links[$key]);
              //echo "Removed link number $key";
            }
          }

          break;
        }
      }
    }
  }

  public function getTheSemantics(){

    if(empty($this->nodes)){
      return;
    }

    $attacks = "[";
    $first = true;

    foreach($this->links as $attack){

      $twoArgs = false;

      if(!$first){
        $attacks .= ",";
      } else{
        $first = false;
      }

      if($attack->left === true){
        $attacks .= "[";
        $attacks .= $attack->target->id;
        $attacks .= ",";
        $attacks .= $attack->source->id;
        $attacks .= "]";

        $twoArgs = true;
      }

      if($attack->right === true){

        if($twoArgs === true){
          $attacks .= ",";
        }

        $attacks .= "[";
        $attacks .= $attack->source->id;
        $attacks .= ",";
        $attacks .= $attack->target->id;
        $attacks .= "]";
      }
    }
    $attacks .= "]";

    $arguments = "[";
    $first = true;
    foreach($this->nodes as $argument){

      foreach($argument->membership_functions as $memfunc){
        foreach($memfunc->points as $point){
          // echo "x : " . $point->x;
          // echo "y : " . $point->y . "\n";
        }
      }

      if(!$first){
        $arguments .= ",";
      } else{
        $first = false;
      }

      $arguments .= $argument->id;
    }

    $arguments .= "]";

    exec("java -jar ./javaDung.jar $attacks $arguments", $output);

    $stringSemantics = json_decode(implode($output, "\n"));

    foreach($stringSemantics as $key => $stringSemantic){
      if(mb_substr($stringSemantic, 0, 2) === "[["){
        $subSemantics = explode("], ", mb_substr($stringSemantic, 1, strlen($stringSemantic) - 2));

        foreach($subSemantics as $k => $v){

          $v = mb_substr($v, 1, strlen($v) - 1);

          if(mb_substr($v, strlen($v) - 1, 1) === "]"){
            $v = mb_substr($v, 0, strlen($v) - 1);
          }

          $this->semantics[$key][$k] = explode(", ", $v);
        }
      }elseif(mb_substr($stringSemantic, 0, 1) === "["){
        //remove start and end and change to array
        $this->semantics[$key] = explode(", ", mb_substr($stringSemantic, 1, strlen($stringSemantic) - 2));
      }
    }

    //var_dump($this->semantics);
  }

  public function getTheResults(){
    if(empty($this->nodes)){
      return;
    }

    // go through each semantic
    foreach($this->semantics as $key => $value){
      //var_dump($value);
      if(is_array($value[0])){
        foreach($value as $k => $v){
          $this->results[$key][$k]["arguments"] = $v;
          $this->results[$key][$k]["result"] = $this->evaluateSemantic($v);
        }
      } else {
        $this->results[$key]["arguments"] = $value;
        $this->results[$key]["result"] = $this->evaluateSemantic($value);
      }
    }
    //var_dump($this->results);
    return $this->results;
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

    foreach($semantic as $arg){
      $argtotal = 0;
      $memfunc_count = 0;
      //var_dump($this->nodes[$arg]->membership_functions);
      if($arg === ""){
        continue;
      }

      if($this->nodes[$arg]->output_function->title === "Mitigating Argument"){
        // it is a mitigating argument
        continue;
      }

      $memfuncs = $this->nodes[$arg]->membership_functions;

      foreach($memfuncs as $memfunction){

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

}
