<?php /*include_once("index.html"); */ ?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="main.css?v=1" />
</head>
<style></style>
<body>
<?php

  // variables
  $row = 0;
  $assoc_array = array();

  // helper functions
  function debug_array($this_array) {
    echo '<pre>';
    print_r($this_array);
    echo '</pre>';
  }

  function print_course($row_count) {
    echo "<div class='arts_class'>";
      for ($c=0; $c < $row_count; $c++) {
          echo "$faculty[$c]<br/>";
      }
      echo "</div>";
  }

  function build_assoc_array() {
    //Map lines of the string returned by file function to $rows array.
    $rows   = array_map('str_getcsv', file('main.csv'));
    //Get the first row that is the HEADER row.
    $header_row = array_shift($rows);
    //This array holds the final response.
    $this_array = array();
    foreach($rows as $row) {
        if(!empty($row)){
            $this_array[] = array_combine($header_row, $row);
        }
    }
    return $this_array;
  }
?>

<?php

if (($handle = fopen("main.csv", "r")) !== false) {

  while (($result = fgetcsv($handle)) !== false) {
    if($row>0) {
      $faculty[] = $result[0];
      $year[] = $result[1];
      $semester[] = $result[2];
      $course_dept[] = $result[3];
      $course_num[] = $result[4];
      $course_title[] = $result[5];
      $notes[] = $result[6];
    }
    $row++;
  }

  // make unique arrays of data
  $faculty_unique = array_unique($faculty);
  $year_unique = array_unique($year);
  $semester_unique = array_unique($semester);
  $course_dept_unique = array_unique($course_dept);
  $course_num_unique = array_unique($course_num);
  $course_title_unique = array_unique($course_title);
  $notes_unique = array_unique($notes);

  fclose($handle);

  //$row_count = 50;
/*
  for ($c=0; $c < $row; $c++) {
    if(isset ($faculty[$c]))
    {
      echo "<div class='arts_class'>";
      echo "$faculty[$c]<br/>";
      echo "$year[$c] $semester[$c]<br/>";
      echo "$course_dept[$c] $course_num[$c]<br/>";
      echo "$course_title[$c]<br/>";
      echo "</div>";
    }
  }
*/
  $assoc_array = build_assoc_array();

  /*
  // group by 'faculty'
  $assoc_array_grouped = array();
  foreach ($assoc_array as $element){
    $assoc_array_grouped[$element['faculty']][] = $element;
  }
  */

  $row_count = 30;

  for ($c=0; $c < $row; $c++) {
    if(isset ($faculty[$c]))
    {
      echo "<div class='arts_class'>";
        echo "<div class='course-title'>";
        echo $assoc_array[$c]["course title"];
        echo "</div>";
        echo "<div class='year-semester'>";
        echo $assoc_array[$c]["year"];
        echo ", ";
        echo $assoc_array[$c]["semester"];
        echo "</div>";
        echo "<div class='course-dept-number'>";
        echo $assoc_array[$c]["course dept"];
        echo " ";
        echo $assoc_array[$c]["course number"];
        echo "</div>";
        echo "<div class='faculty'>";
        echo $assoc_array[$c]["faculty"];
        echo "</div>";
      echo "</div>";
    }
  }

  /*
  echo '<pre>';
  print_r($assoc_array);
  //print_r($assoc_array[1]['faculty']);
  echo '</pre>';
  */

/*
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
      echo "<div class='arts_class'>";
        $num = count($data);
        $row++;
        for ($c=0; $c < $num; $c++) {
            echo "$data[$c]<br/>";

        }
        echo "</div>";
    }
    fclose($handle);
*/

}
?>






</body>
</html>
