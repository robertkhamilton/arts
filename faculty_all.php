<?php /*include_once("index.html"); */ ?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="main.css?v=5" />
</head>
<style></style>
<body>
<?php

  // variables
  $row = 0;
  $assoc_array = array();
  $grouped_assoc_array = array();

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

  function group_assoc_array($groupby, $source_array) {
    $assoc_array_grouped = array();
    foreach ($source_array as $element){
      $assoc_array_grouped[$element[$groupby]][] = $element;
    }

    return $assoc_array_grouped;
  }

  function echo_grouped_array($this_grouped_array) {

    global $faculty;

    $faculty_keys = array_keys($this_grouped_array);
    $faculty_count = count($faculty_keys);

    // container row for all faculty
    echo "<div class='row'>";

    // step through each faculty member
    for($i = 0; $i< $faculty_count; $i++) {

      // column for each faculty member
      echo "<div class='column'>";

      // get number of courses per faculty member
      $faculty_course_count = count($this_grouped_array[$faculty_keys[$i]]);
      $current_faculty = $faculty_keys[$i];

      // get array of all courses for this faculty member
      $current_faculty_courses = $this_grouped_array[$current_faculty];

      // debug
      echo $current_faculty . ": ";
      echo $faculty_course_count . "<br>";

      // for each course, print cell
      for($j = 0; $j< $faculty_course_count; $j++) {

        // check semester of class for css coding
        if($current_faculty_courses[$j]["semester"] == "Fall") {
          echo "<div class='arts_class fall'>";
        } elseif ($current_faculty_courses[$j]["semester"] == "Spring") {
          echo "<div class='arts_class spring'>";
        } elseif ($current_faculty_courses[$j]["semester"] == "Summer") {
          echo "<div class='arts_class summer'>";
        } else {
          echo "<div class='arts_class'>";
        }

         echo "<div class='course-title'>";
         echo $current_faculty_courses[$j]['course title'];
         echo "</div>";
         echo "<div class='year-semester'>";
         echo $current_faculty_courses[$j]["year"];
         echo ", ";
         echo $current_faculty_courses[$j]["semester"];
         echo "</div>";
         echo "<div class='course-dept-number'>";
         echo $current_faculty_courses[$j]["course dept"];
         echo " ";
         echo $current_faculty_courses[$j]["course number"];
         echo "</div>";
         echo "<div class='faculty'>";
         echo $current_faculty_courses[$j]["faculty"];
         echo "</div>";
       echo "</div>";
      }

      // close faculty column
      echo "</div>";
    }

    // close faculty row
    echo "</div>";
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

  // build our arrays
  $assoc_array = build_assoc_array();
  $grouped_assoc_array = group_assoc_array('faculty', $assoc_array);

  // display grouped array data
  echo_grouped_array($grouped_assoc_array);
  
}
?>

</body>
</html>
