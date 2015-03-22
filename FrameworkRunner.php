<?php

require_once('bezier.php');

class FrameworkRunner{

  private $nodes, $links, $data, $extensionsToGet, $semantics = [], $results = [];

  public function __construct($aString){

    $decoded = json_decode($aString);

    $this->nodes = $decoded->nodes;
    $this->links = $decoded->links;
    $this->data  = $decoded->data;
    $this->extensionsToGet = $decoded->extensions;
    $this->getActivatedArgs();
    //$this->getTheSemantics();
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

    // convert the nodes and links into the appropriate format
    // for the jar

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

    // get the semantics

    exec("java -jar ./javaDung.jar $attacks $arguments $this->extensionsToGet", $output);

    $stringSemantics = json_decode(implode($output, "\n"));

    //var_dump($stringSemantics);

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
    //return $this->results;

    return array(
      "results" => $this->results,
      "nodes" => $this->nodes,
      "links" => $this->links,
      "data" =>$this->data
    );
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

      // echo "Start loop";

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

      $this->nodes[$arg] = (array) $this->nodes[$arg];

      if(!isset($this->nodes[$arg]['total'])){

        $this->nodes[$arg]['total'] = 0;
        // echo "Computing property: " . $this->nodes[$arg]['name'];
        $this->nodes[$arg] = (object) $this->nodes[$arg];
        $memfuncs = $this->nodes[$arg]->membership_functions;

        foreach($memfuncs as $i => $memfunction){

          $pointsList = [];

          foreach($memfunction->points as $point){
            array_push($pointsList, new Point($point->x, $point->y));
          }

          $myBez = new Bezier($pointsList);


          $inputValue = $this->data->{$memfunction->xLabel};
          $outputValue = $myBez->yFromX($inputValue);

          $memfunction = (array) $memfunction;
          $memfunction['inputValue'] = $inputValue;
          $memfunction['outputValue'] = $outputValue;
          $memfunction = (object) $memfunction;

          $this->nodes[$arg]->membership_functions[$i] = $memfunction;

          $argtotal += $outputValue;
          $memfunc_count++;
        }

        // Get the averages of the truth
        if($memfunc_count === 0){
          $average = 0;
        } else {
          $average = $argtotal / $memfunc_count;
        }

        $pointsList = [];

        foreach($this->nodes[$arg]->output_function->points as $point){
          array_push($pointsList, new Point($point->x, $point->y));
        }

        $myBez = new Bezier($pointsList);


        $this->nodes[$arg]->degreeOfTruth = $average;

        // echo "Average is: " . $average;

        $this->nodes[$arg]->total = $myBez->yFromX($average);

        // echo " Property computed \n";

      } else {

        // echo "Saved property used \n";

        $this->nodes[$arg] = (object) $this->nodes[$arg];
      }

      $semanticTotal += $this->nodes[$arg]->total;
      $sem_count++;
    }
    //echo "end loop \n";

    if($sem_count === 0){
      return 0;
    } else {

      // echo "Semantic evaluated: " . $semanticTotal / $sem_count;

      return $semanticTotal / $sem_count;
    }
  }

}
