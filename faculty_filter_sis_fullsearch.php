<?php include_once("index.html"); ?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="main.css?v=9" />
</head>
<style></style>
<body>
<?php

  // variables
  $row = 0;
  $assoc_array = array();
  $grouped_assoc_array = array();

  // global constants
  $INSTRUCTOR = "Instructor";
  $FALL = 'Fall';
  $SPRING = 'Spring';
  $SUMMER = 'Summer';
  $CURRENT_YEAR = '2023';
  $CURRENT_SEMESTER = $SPRING;
  $TITLE = 'Title';
  $DISSERTATION_COURSENUMBER = '9990';
  $COURSE = 'Crse';
  $ARTS = 'ARTS';
  $SUBJECT = 'Subj';
  $YEAR = 'Year';
  $SEMESTER = 'Semester';
  $FILTERNAME = 'filter_name';
  $PTAG = ' (P)';

  // helper functions
  function debug_array($this_array) {
    echo '<pre>';
    print_r($this_array);
    echo '</pre>';
  }

  function build_assoc_array() {

    $CSV_DATA = 'main.csv';
    $CSV_SIS_DATA = 'sis_arts_2023_spr.csv';

    global $INSTRUCTOR;

    //Map lines of the string returned by file function to $rows array.
    $rows   = array_map('str_getcsv', file($CSV_SIS_DATA));
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

  // group course records by Instructor
  function group_assoc_array($groupby, $source_array) {
    $assoc_array_grouped = array();
    foreach ($source_array as $element){
      $assoc_array_grouped[$element[$groupby]][] = $element;
    }

    $assoc_array_grouped = clean_faculty_key_names($assoc_array_grouped);

    return $assoc_array_grouped;
  }

  // Clean up SIS data which tags a "(P)" in the Instructor name
  function clean_faculty_key_names($grouped_array) {

    global $INSTRUCTOR, $PTAG;

    // Clean up source Data "(P)" tag on instructors
    foreach($grouped_array as $key=>$value) {

        $clean_name = str_replace($PTAG, '', $key);
        $clean_name = trim_instructor_name($clean_name);

        //clean each instructor name used as Key for assoc array
        unset($grouped_array[$key]);
        $grouped_array[$clean_name] = $value;

        // step through each course and clean the Instructor name
        foreach($grouped_array[$clean_name] as $key2=>$value2) {
          $grouped_array[$clean_name][$key2][$INSTRUCTOR] = $clean_name;
        }
    }
    return $grouped_array;
  }

  function trim_instructor_name($current_name_string)
  {
      $val = ",";
      $name = '';

      $pos = strpos($current_name_string, $val);

      if($pos == false) {
        $name = $current_name_string;
      } else {
        $name = substr($current_name_string, 0, $pos);
      }
      return $name;
  }

  // setup for hand made CSV data
  function echo_grouped_array($this_grouped_array, $filter_name) {

    global $faculty;
    global $FILTERNAME, $FALL, $SPRING, $SUMMER, $CURRENT_YEAR, $CURRENT_SEMESTER, $TITLE, $DISSERTATION_COURSENUMBER, $COURSE, $ARTS, $SUBJECT, $YEAR, $SEMESTER, $INSTRUCTOR;

    $faculty_keys = array_keys($this_grouped_array);
    $faculty_count = count($faculty_keys);
    $filter_names = array();

    // container row for all faculty
    echo "<div class='row'>";

    if($filter_name == '') {
      // blank filter, show all, do nothing
      echo 'No Faculty Name filter set';
      $filtering = false;

    } else {
      echo 'Current Filter: ' . $filter_name;

      $filtering = true;

      // check for multiple search entries (',' delimiter) (e.g. Hamilton, Meltz)
      $filter_names = explode(",", $filter_name);

    }

    // step through each faculty member
    for($i = 0; $i< $faculty_count; $i++) {

      // get number of courses per faculty member
      $faculty_course_count = count($this_grouped_array[$faculty_keys[$i]]);
      $current_faculty = $faculty_keys[$i];
      //$current_faculty = str_replace('(P)', '', $faculty_keys[$i]);

      // want to be able to search on last or first names
      if($filtering) {

        $match = false;

        foreach($filter_names as $filt) {
          if(str_contains($current_faculty, $filt)) {
              $match = true;
          }
        }

        if(!$match) {
          continue;
        }
      }

      // column for each faculty member
      echo "<div class='column'>";

      // get array of all courses for this faculty member
      $current_faculty_courses = $this_grouped_array[$current_faculty];

      echo "<div class='faculty_header' style=text-align:center>";
      echo $current_faculty . " (" . $faculty_course_count . ")</div>";

      // for each course, print cell
      for($j = 0; $j< $faculty_course_count; $j++) {

        /*
        // check semester of class for css coding
        if($current_faculty_courses[$j][$SEMESTER] == $FALL) {
          echo "<div class='arts_class fall'>";
        } elseif ($current_faculty_courses[$j][$SEMESTER] == $SPRING) {
          echo "<div class='arts_class spring'>";
        } elseif ($current_faculty_courses[$j][$SEMESTER] == $SUMMER) {
          echo "<div class='arts_class summer'>";
        } else {
          echo "<div class='arts_class'>";
        }
        */

        // set DISSERTATION Courses to a class
        if($current_faculty_courses[$j][$SUBJECT] == $ARTS && $current_faculty_courses[$j][$COURSE] == $DISSERTATION_COURSENUMBER) {
          echo "<div class='arts_class arts9990'>";
        } else {
          echo "<div class='arts_class'>";
        }
        echo "<div class='course-title'>";
        echo $current_faculty_courses[$j][$TITLE];
        echo "</div>";
        echo "<div class='year-semester'>";
        echo $current_faculty_courses[$j][$YEAR];
        echo ", ";
        echo $current_faculty_courses[$j][$SEMESTER];
        echo "</div>";
        echo "<div class='course-dept-number'>";
        echo $current_faculty_courses[$j][$SUBJECT];
        echo " ";
        echo $current_faculty_courses[$j][$COURSE];
        echo "</div>";
        echo "<div class='faculty'>";
        echo $current_faculty_courses[$j][$INSTRUCTOR];
        echo "</div>";
       echo "</div>";
      }

      // close faculty column
      echo "</div>";
    }

    // close faculty row
    echo "</div>";
  }

  // build our arrays
  $assoc_array = build_assoc_array();
  $grouped_assoc_array = group_assoc_array($INSTRUCTOR, $assoc_array);

  echo "<form name='form' action='' method='get'>";
  echo "<input id='name' name='filter_name' type='text' value='' />";
  echo "<input type='button' value='CLEAR SEARCH' onclick='location=\"faculty_filter_sis_fullsearch.php\"' />";
  echo "</form>";

  $filter_name = $_GET[$FILTERNAME];

  // display grouped array data
  echo_grouped_array($grouped_assoc_array, $filter_name);

  // debug data dumps for testing
  //debug_array($grouped_assoc_array);
  //debug_array($assoc_array);

?>

</body>
</html>
