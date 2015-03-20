<?php

class Point {
  public $x;
  public $y;

  public function __construct($x, $y){
    $this->x = $x;
    $this->y = $y;
  }

  public function printContents(){
    echo "X value: " . $this->x . ", Y value: " . $this->y;
  }

}

class Bezier {

  private $scaledPoints, $x_min, $x_max, $y_min, $y_max,
          $x_difference, $y_difference;

  public function __construct($points_list = null){

    $this->scaledPoints = $points_list;
    //find min and maxes
    $this->x_min = 9999999;
    $this->x_max = -9999999;
    $this->y_min = 9999999;
    $this->y_max = -9999999;

    $this->scaledPoints = [];

    foreach($points_list as $point){
      $this->x_min = $this->x_min > $point->x ? $point->x: $this->x_min;
      $this->x_max = $this->x_max < $point->x ? $point->x: $this->x_max;
      $this->y_min = $this->y_min > $point->y ? $point->y: $this->y_min;
      $this->y_max = $this->y_max > $point->y ? $point->y: $this->y_max;
    }

    $this->x_difference = $this->x_max - $this->x_min;
    $this->y_difference = $this->y_max - $this->y_min;

    foreach($points_list as $point){
      array_push($this->scaledPoints, new Point(($point->x - $this->x_min)/$this->x_difference, ($point->y - $this->y_min)/$this->y_difference));
    }
  }

  public function yFromX($X){

    //Do a binary search
    $xTolerance = .0000001;

    $lowerT = 0;
    $upperT = 1;
    $lower = $this->getPointAt($lowerT);
    $upper = $this->getPointAt($upperT);

    do{
      // get the point with the least difference
      // $lower->printContents();
      // $upper->printContents();

      if(abs($lower->x - $X) < abs($upper->x - $X)){

        $upperT = ($upperT + $lowerT) / 2;
        //echo $upperT;
        $upper = $this->getPointAt($upperT);

        //break;
      } else {
        $lowerT = ($upperT + $lowerT) / 2;
        $lower = $this->getPointAt($lowerT);
      }

      if(abs($X - $lower->x) < $xTolerance){
        return $lower->y;
      } elseif(abs($X - $upper->x) < $xTolerance){
        return $upper->y;
      }

    } while(abs($X - $upper->x) > $xTolerance && abs($X - $lower->x) > $xTolerance);

    return ($upper->y + $lower->y)/2;
  }

  public function xFromY($Y){

    //Do a binary search
    $yTolerance = .0000001;

    $lowerT = 0;
    $upperT = 1;
    $lower = $this->getPointAt($lowerT);
    $upper = $this->getPointAt($upperT);

    do{
      // get the point with the least difference
      // $lower->printContents();
      // $upper->printContents();

      if(abs($lower->y - $Y) < abs($upper->y - $Y)){

        $upperT = ($upperT + $lowerT) / 2;
        //echo $upperT;
        $upper = $this->getPointAt($upperT);

        //break;
      } else {
        $lowerT = ($upperT + $lowerT) / 2;
        $lower = $this->getPointAt($lowerT);
      }

      if($lower->y === $Y){
        return $lower->x;
      }
      if($upper->y === $Y){
        return $upper->x;
      }


      if(abs($Y - $lower->y) < $yTolerance){
        return $lower->x;
      } elseif(abs($Y - $upper->y) < $yTolerance){
        return $upper->x;
      }

    } while(abs($Y - $upper->y) > $yTolerance && abs($Y - $lower->y) > $yTolerance);

    return ($upper->x + $lower->x)/2;
  }




  public function getPointAt($someT){
    return $this->scaleOutput($this->bezierFunc($this->scaledPoints, $someT));
  }

  public function scaleOutput($aPoint){
    return new Point(($aPoint->x * $this->x_difference) + $this->x_min, ($aPoint->y * $this->y_difference)  + $this->y_min);
  }

  private function bezierFunc($pointsList, $someT){
    if(count($pointsList) == 1){
      return $pointsList[0];
    } else {
      $P1 = $this->bezierFunc(array_slice($pointsList, 0, count($pointsList)-1), $someT);
      $P2 = $this->bezierFunc(array_slice($pointsList, 1, count($pointsList)), $someT);
      $nt = 1 - $someT;
      return new Point($nt * $P1->x + $someT * $P2->x, $nt * $P1->y + $someT * $P2->y);
    }
  }
}

// $pointsList = [new Point(0,0), new Point(3.333333,4),
//               new Point(6.66666,8), new Point(10, 10)];
//
//
// $myBez = new Bezier($pointsList);
//
// $val = $myBez->getPointAt(0.3);
//
// $val->printContents();
//
// echo "\n";
// echo $myBez->yFromX(2.999998593);
// echo "\n";
// echo $myBez->xFromY(3.546);

?>
