<?php include_once("index.html"); ?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="main.css?v=12" />
</head>
<style></style>
<body>
<?php

  // data files in use
  $csv_sis_2023_spring = 'sis_arts_2023_spring.csv';
  $csv_sis_2022_fall = 'sis_arts_2022_fall.csv';
  $csv_sis_2022_summer = 'sis_arts_2022_summer.csv';
  $csv_sis_2022_spring = 'sis_arts_2022_spring.csv';
  $csv_sis_2021_fall = 'sis_arts_2021_fall.csv';
  $csv_sis_2021_summer = 'sis_arts_2021_summer.csv';
  $csv_sis_2021_spring = 'sis_arts_2021_spring.csv';
  $csv_faculty_data = 'faculty.csv';
  $csv_test_data = 'test_data_2.csv';

  $data_source_files = array(
    //"2023_TEST" => $csv_test_data,
    "2023_SPRING" => $csv_sis_2023_spring,
    "2022_FALL" => $csv_sis_2022_fall,
    "2022_SUMMER" => $csv_sis_2022_summer,
    "2022_SPRING" => $csv_sis_2022_spring,
    "2021_FALL" => $csv_sis_2021_fall,
    "2021_SUMMER" => $csv_sis_2021_summer,
    "2021_SPRING" => $csv_sis_2021_spring,
  );

  // faux relational db faculty info
  $data_faculty = array(
    "FACULTY" => $csv_faculty_data,
  );
  $source_faculty = array();
  $grouped_faculty = array();

  // array to hold SIS .csv source data from $data_files files
  $source_arrays = array();

  // variables
  $row = 0;
  $assoc_array = array();
  $grouped_assoc_array = array();

  // global variables (fake constants)
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
  $TYPE = 'Type';
  $EQUALS = '=';

  // helper functions
  function debug_array($this_array) {
    echo '<pre>';
    print_r($this_array);
    echo '</pre>';
  }

  function build_assoc_array($data_file_names, $debug=false) {

    global $PTAG;

    //This array holds the final response.
    $this_array = array();
    $count = 0;
    foreach($data_file_names as $key => $datafilename){

      //Map lines of the string returned by file function to $rows array.
      $rows   = array_map('str_getcsv', file($datafilename));

      // remove problematic "(P)" from faculty name fields
      foreach($rows as $key => $dirtyrow) {
        $rows[$key] = str_replace($PTAG, '', $dirtyrow);
      }

      if($count == 0) {
        //Get the first row that is the HEADER row.
        $header_row = array_shift($rows);
      }

      foreach($rows as $row) {
          if(!empty($row)){
              $this_array[] = array_combine($header_row, $row);
          }
      }

      /*
      if($debug) {
        var_dump($this_array);
      }
      */

      $count++;
    }

    return $this_array;
  }

  // group course records by Instructor
  function group_assoc_array($groupby, $source_array) {
    $assoc_array_grouped = array();
    foreach ($source_array as $element){
      $assoc_array_grouped[$element[$groupby]][] = $element;
    }

    //$assoc_array_grouped = clean_faculty_key_names($assoc_array_grouped);

    return $assoc_array_grouped;
  }

  // Clean up SIS data which tags a "(P)" in the Instructor name
  function clean_faculty_key_names($grouped_array) {

    global $INSTRUCTOR, $PTAG;

    // Clean up source Data "(P)" tag on instructors
    foreach($grouped_array as $key=>$value) {

        //clean each instructor name used as Key for assoc array, then reset it as key
        $clean_name = trim_instructor_name($key);
        unset($grouped_array[$key]);
        $grouped_array[$clean_name] = $value;

        // step through each course and clean the Instructor name
        foreach($grouped_array[$clean_name] as $key2=>$value2) {
          $grouped_array[$clean_name][$key2][$INSTRUCTOR] = $clean_name;
        }
    }
    return $grouped_array;
  }

  // remove extra faculty names from adjuncts
  function trim_instructor_name($current_name_string) {
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

  // setup for SIS exported CSV data with YEAR and SEMESTER columns added manually
  function echo_grouped_array($this_grouped_array, $filter_name) {

    global $source_faculty, $grouped_faculty, $TYPE;
    global $EQUALS, $FILTERNAME, $FALL, $SPRING, $SUMMER, $CURRENT_YEAR, $CURRENT_SEMESTER, $TITLE, $DISSERTATION_COURSENUMBER, $COURSE, $ARTS, $SUBJECT, $YEAR, $SEMESTER, $INSTRUCTOR;

    $faculty_keys = array_keys($this_grouped_array);
    $faculty_count = count($faculty_keys);
    $filter_names = array();

    $filtering = false;
    $complex_filter = false;

    // container row for all faculty
    echo "<div class='row'>";

    if($filter_name == '') {
      // blank filter, show all, do nothing
      echo 'No filter set. Use names (e.g. hamilton, meltz) or "type=associate" (full, associate, assistant, lecturer, senior, adjunct), year=2022 or semester=fall'  . "<div style=clear:both></div>";

    } else {
      echo 'Current Filter: ' . $filter_name . "<div style=clear:both></div>";

      $filtering = true;

      // check for complex search with $EQUALS
      if(strpos($filter_name, $EQUALS) ){

          $complex_filter = true;

      } else {
          // simple name filter (comma delimited): check for multiple search entries (',' delimiter) (e.g. Hamilton, Meltz)
          $filter_names = explode(",", $filter_name);
      }
    }

    // step through each faculty member
    for($i = 0; $i< $faculty_count; $i++) {

      // get number of courses per faculty member
      $faculty_course_count = count($this_grouped_array[$faculty_keys[$i]]);
      $current_faculty = $faculty_keys[$i];

      // get faculty type
      $faculty_type = $grouped_faculty[$current_faculty][0][$TYPE];

      // want to be able to search on last or first names
      if($filtering) {

        $match = false;
        $yearsearch = false;
        $semestersearch = false;
        $query_year = '';
        $query_semester = '';

        if($complex_filter) {

          // break apart search string at $EQUALS
          $pos = strpos($filter_name, $EQUALS);
          $query_type = strtolower(substr($filter_name, 0, $pos));
          $query_value = strtolower(substr($filter_name, $pos+1));

          if( $query_type == strtolower($TYPE) )
          {
            // do a faculty type search
            if( strtolower($faculty_type) == $query_value ){
              $match = true;
            }

          } elseif($query_type == strtolower($YEAR)){

              // do a year search
              $yearsearch = true;
              $query_year = $query_value;
              $match = true;

          } elseif($query_type == strtolower($SEMESTER)){

              // do a year search
              $semestersearch = true;
              $query_semester = $query_value;
              $match = true;
          }

        } else {

          // simple names filter
          foreach($filter_names as $filt) {
            //echo "<br/>" . $filt . "<br/>";
            if(str_contains( strtolower($current_faculty), strtolower($filt) )) {
              $match = true;
            }
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
      echo $current_faculty . " (" . $faculty_type . ', ' . $faculty_course_count . ")</div>";

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

        // do year search
        if($yearsearch) {

          if(strtolower($current_faculty_courses[$j][$YEAR]) != $query_year) {
            continue;
          }
        } elseif($semestersearch) {

          if(strtolower($current_faculty_courses[$j][$SEMESTER]) != $query_semester) {
            continue;
          }
        }

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

  // for each $data_files .csv build assoc array and merge them
  $assoc_array = build_assoc_array($data_source_files);
  $grouped_assoc_array = group_assoc_array($INSTRUCTOR, $assoc_array);
  $grouped_assoc_array = clean_faculty_key_names($grouped_assoc_array);

  // build faculty array for filtering
  $source_faculty = build_assoc_array($data_faculty, true);
  $grouped_faculty = group_assoc_array($INSTRUCTOR, $source_faculty);

  echo "<form name='form' action='' method='get'>";
  echo "<input id='name' name='filter_name' type='text' value='' />";
  echo "<input type='button' value='CLEAR SEARCH' onclick='location=\"sis_semester_year_filter.php\"' />";
  echo "</form>";

  $filter_name = $_GET[$FILTERNAME];

  // display grouped array data
  echo_grouped_array($grouped_assoc_array, $filter_name);

  // debug data dumps for testing
  //debug_array($grouped_assoc_array);
  //debug_array($assoc_array);
  //var_dump($grouped_faculty);
?>

</body>
</html>
