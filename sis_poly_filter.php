<?php include_once("index.html"); ?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="main.css?v=13" />
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
  $csv_sis_2020_fall = 'sis_arts_2020_fall.csv';
  $csv_sis_2020_summer = 'sis_arts_2020_summer.csv';
  $csv_sis_2020_spring = 'sis_arts_2020_spring.csv';
  $csv_sis_2019_summer = 'sis_arts_2019_summer.csv';
  $csv_sis_2019_spring = 'sis_arts_2019_spring.csv';
  $csv_sis_2019_fall = 'sis_arts_2019_fall.csv';
  $csv_sis_2018_summer = 'sis_arts_2018_summer.csv';
  $csv_sis_2018_spring = 'sis_arts_2018_spring.csv';
  $csv_sis_2018_fall = 'sis_arts_2018_fall.csv';
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
    "2020_FALL" => $csv_sis_2020_fall,
    "2020_SUMMER" => $csv_sis_2020_summer,
    "2020_SPRING" => $csv_sis_2020_spring,
    "2019_SUMMER" => $csv_sis_2019_summer,
    "2019_SPRING" => $csv_sis_2019_spring,
    "2019_FALL" => $csv_sis_2019_fall,
    "2018_SUMMER" => $csv_sis_2018_summer,
    "2018_SPRING" => $csv_sis_2018_spring,
    "2018_FALL" => $csv_sis_2018_fall,
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
  $NAME = 'Name';
  $COMMA = ',';
  $ALL = 'All';
  $ADJUNCT = 'Adjunct';
  $FULL = 'Full';
  $ASSOCIATE = 'Associate';
  $ASSISTANT = 'Assistant';
  $LECTURER = 'Lecturer';
  $SENIOR = 'Senior';

  //  final string output for echo
  $output = '';

  // helper functions
  function debug_array($this_array) {
    build_output('<pre>');
    build_output(print_r($this_array, true));
    build_output('</pre>');
  }

  function remove_secondary_faculty($data_array) {

    global $INSTRUCTOR;

    $return_array = array();

    foreach($data_array as $key=>$value) {
        $data_array[$key][$INSTRUCTOR] = trim_instructor_name($value[$INSTRUCTOR]);
    }

    $return_array = $data_array;

    return $return_array;
  }

  function build_assoc_array($data_file_names, $debug=false) {

    global $PTAG;

    //This array holds the final response.
    $this_array = array();
    $count = 0;
    foreach($data_file_names as $key => $datafilename){

      //Map lines of the string returned by file function to $rows array.
      $rows  = array_map('str_getcsv', file($datafilename));

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

    return $assoc_array_grouped;
  }

  // Clean up SIS data which tags a "(P)" in the Instructor name
  function clean_faculty_key_names($grouped_array) {

    global $INSTRUCTOR, $PTAG;

    // Clean up source Data "(P)" tag on instructors
    foreach($grouped_array as $key=>$value) {

        //clean each instructor name used as Key for assoc array, then reset it as key
        $clean_name = trim_instructor_name($key);

        //echo "<br/>" . "Clean Name: " . $clean_name . "($key)";

        unset($grouped_array[$key]);
        $grouped_array[$clean_name] = $value;
        //$grouped_array[$key] = $value;

        // step through each course and clean the Instructor name
        foreach($grouped_array[$clean_name] as $key2=>$value2) {
          $grouped_array[$clean_name][$key2][$INSTRUCTOR] = $clean_name;
        }
    }

    return $grouped_array;
  }

  // remove extra faculty names from adjuncts
  function ___trim_instructor_name($current_name_string) {
      $val = ",";
      $name = '';

      $pos = strpos($current_name_string, $val);

      if($pos == false) {
        $name = $current_name_string;
      } else {
        $name = substr($current_name_string, 0, $pos);
      }
      //echo $name . "<br/>";
      return $name;
  }

  // remove extra faculty names from adjuncts
  function trim_instructor_name($current_name_string) {

      global $COMMA, $source_faculty;
      $name = '';
      $names = array();
      $current_name_string = str_replace(', ', ',', $current_name_string);

      $pos = strpos($current_name_string, $COMMA);

      if($pos == false) {
        $name = $current_name_string;
      } else {
        $names[0] = substr($current_name_string, 0, $pos);
        $names[1] = substr($current_name_string,$pos+1,strlen($current_name_string) );

        // check if one of the names is an adjunct, use that
        if( isAdjunct($names[0]) ) {
          $name = $names[0];
        } else {
          $name = $names[1];
        }
      }

      return $name;
  }

  function isAdjunct($facultyname) {

    global $ADJUNCT, $INSTRUCTOR, $source_faculty, $TYPE;

    $returnval = false;

    if(is_null($facultyname)) {
      $returnval = true;
    } else {

      $facultyname = strtolower($facultyname);

      foreach($source_faculty as $key=>$subarray) {
        if(strtolower($subarray[$INSTRUCTOR]) == $facultyname) {
          if(strtolower($subarray[$TYPE]) == strtolower($ADJUNCT)) {
            $returnval = true;
          }
        }
      }

    }

    return $returnval;
  }

  function build_filter_array($filter_string) {

    global $EQUALS, $NAME;

    $delimiter = ";";
    $lastPos = 0;
    $positions = array();
    $results = array();

    // remove spaces
    $filter_string = str_replace(' ', '', $filter_string);

    // if string is blank, return empty array
    if(strlen($filter_string) > 0){

      //check to see if filter is just names (i.e. one or more comma delimited strings)
      if(strpos($filter_string, $EQUALS, 0) == false) {
          //prepend "name=" to just names to simplify flow
          $filter_string = $NAME . $EQUALS . $filter_string;
      }

      //tack delimiter to end of string to make process cleaner
      if(substr($filter_string, -1) != $delimiter) {
        $filter_string .= $delimiter;
      }

      while (($lastPos = strpos($filter_string, $delimiter, $lastPos))!== false) {
        $positions[] = $lastPos;
        $lastPos = $lastPos + strlen($delimiter);
      }

      $currentPos = 0;

      foreach ($positions as $value) {

        $thisValue = substr($filter_string, $currentPos, $value-$currentPos);
        $currentPos = $value+1;
        $pos = strpos($thisValue, $EQUALS);
        $currentKey = strtolower(substr($thisValue, 0, $pos));
        $currentValue = strtolower(substr($thisValue, $pos+1));

        $results[$currentKey] = $currentValue;
      }
    }
    return $results;
  }

  // setup for SIS exported CSV data with YEAR and SEMESTER columns added manually
  function echo_grouped_array($this_grouped_array, $filter_name) {

    global $source_faculty, $grouped_faculty, $TYPE;
    global $COMMA, $EQUALS, $NAME, $FILTERNAME, $FALL, $SPRING, $SUMMER, $CURRENT_YEAR, $CURRENT_SEMESTER, $TITLE, $DISSERTATION_COURSENUMBER, $COURSE, $ARTS, $SUBJECT, $YEAR, $SEMESTER, $INSTRUCTOR;

    $faculty_keys = array_keys($this_grouped_array);
    $faculty_count = count($faculty_keys);
    $filter_names = array();

    $filtering = false;
    //$complex_filter = false;
    $poly_filter = false;
    $filter_array = array();

    // container row for all faculty
    build_output("<div class='row'>");

    // check filter string for poly complex filter string (multiple x=y with semi-colon delimiters)
    $filter_array = build_filter_array($filter_name);

    if(empty($filter_array)) {

      // blank filter, show all, do nothing
      build_output('No filter set. Use names (e.g. ' . '<a href="sis_poly_filter.php?filter_name=hamilton%2C+meltz">' . 'hamilton, meltz' . '</a>' . ') or ' . '<a href="http://localhost/sis_poly_filter.php?filter_name=type%3Dassociate">' . '"type=associate"' . '</a>'  . '(full, associate, assistant, lecturer, senior, adjunct), ' . '<a href="http://localhost/sis_poly_filter.php?filter_name=year%3D2022">year=2022</a> or ' . '<a href="http://localhost/sis_poly_filter.php?filter_name=semester%3Dfall">semester=fall</a>'  . "<div style=clear:both></div>");

      } else {

      // rebuild original filterstring for debug
      foreach($filter_array as $key=>$value){
        $filter_debug .= $key . "=" . $value . ";";
      }

      build_output('Current Filter: <b>' . $filter_debug . "</b><div style=clear:both></div>");

      $filtering = true;


      // step through each faculty member
      for($i = 0; $i< $faculty_count; $i++) {

        //string to aggregate faculty column data headers while filter is being checked
        $faculty_column_output = '';

        // get number of courses per faculty member
        $faculty_course_count = count($this_grouped_array[$faculty_keys[$i]]);

        //echo $faculty_keys[$i] . ": " . $faculty_course_count . "<br/>";

        // current faculty's name
        $current_faculty_name = $faculty_keys[$i];
        $current_faculty_type = $grouped_faculty[$current_faculty_name][0][$TYPE];

        // want to be able to search on last or first names
        if($filtering) {

          $willcontinue = false;
          $filtermatch = false;
          $resultmatch = false;
          $resultcount = 0;
          $yearsearch = false;
          $semestersearch = false;
          $facultytypesearch = false;
          $facultynamesearch = false;

          $query_year = '';
          $query_years = array();

          $query_semester = '';
          $query_semesters = array();

          $query_facultytype = '';
          $query_facultytypes = array();

          $query_facultynames = array();

          // step through filter array and process each filter
          foreach($filter_array as $key=>$value) {

            $query_searchtype = $key;
            $query_value = $value;
            $query_values = array();

            if(strpos($query_value, $COMMA) ) {
              $query_values = explode($COMMA, $query_value); // check for multiple comma-delimited values
            } else {
              $query_values[0] = $query_value;
            }

            // faculty-level query
            if( $query_searchtype == strtolower($NAME) ) {

              $faculty_name_match = false;

              $query_facultynames = $query_values;
              $facultynamesearch = true;
              foreach($query_facultynames as $query_val){

                if(str_contains( strtolower($current_faculty_name), strtolower($query_val ) )) {
                  $faculty_name_match = true;
                }
              }
            }

            // faculty-level query
            if( $query_searchtype == strtolower($TYPE) ) {

              $faculty_type_match = false;

              $query_facultytypes = $query_values;
              $facultytypesearch = true;

              foreach($query_facultytypes as $query_val){
                if(strtolower($query_val) == strtolower($current_faculty_type)) {
                  $faculty_type_match = true;
                }
              }
            }

            // course-level query
            if($query_searchtype == strtolower($YEAR)) {
              $query_years = $query_values;
              $yearsearch = true;
            }

            // course-level query
            if($query_searchtype == strtolower($SEMESTER)) {
              $query_semesters = $query_values;
              $semestersearch = true;
            }
          } // foreach($filter_array as $key=>$value)...
       } //if($filtering)...

        // start column for each faculty member
        $faculty_column_output .= return_output("<div class='column'>");

        // get array of all courses for this faculty member
        $current_faculty_courses = $this_grouped_array[$current_faculty_name];
        $faculty_column_output .= return_output("<div class='faculty_header' style=text-align:center>");
        $faculty_column_output .= return_output($current_faculty_name . " (" . $current_faculty_type . ', ' . $faculty_course_count . ")</div>");

        // step through each course
        for($j = 0; $j< $faculty_course_count; $j++) {

          $resultmatch = false;

          // do year search
          if($yearsearch) {

            $year_match = false;

            foreach($query_years as $query_year) {
              if(strtolower($current_faculty_courses[$j][$YEAR]) == $query_year) {
                $year_match = true;
              }
            }
          }

          if($semestersearch) {

            $semester_match = false;

            foreach($query_semesters as $query_semester) {
              if(strtolower($current_faculty_courses[$j][$SEMESTER]) == $query_semester) {
                $semester_match = true;
              }
            }
          }

          // search conditions: And OR types here...
          if($facultynamesearch && $yearsearch && $semestersearch) {
            if($faculty_name_match && $year_match && $semester_match) {
              $resultmatch = true;
            }
          } elseif($facultynamesearch && $yearsearch) {
            if($faculty_name_match && $year_match) {
              $resultmatch = true;
            }
          } elseif($facultynamesearch && $semestersearch) {
            if($faculty_name_match && $semester_match) {
              $resultmatch = true;
            }
          } elseif($facultytypesearch && $yearsearch && $semestersearch) {
            if($faculty_type_match && $year_match && $semester_match){
              $resultmatch = true;
            }
          } elseif($facultytypesearch && $yearsearch){
            if($faculty_type_match && $year_match) {
              $resultmatch = true;
            }
          } elseif($facultytypesearch && $semestersearch){
            if($faculty_type_match && $semester_match) {
              $resultmatch = true;
            }
          } elseif($yearsearch && $semestersearch){
            if($year_match && $semester_match) {
              $resultmatch = true;
            }
          } elseif($facultynamesearch){
            if($faculty_name_match) {
              $resultmatch = true;
            }
          } elseif($facultytypesearch){
            if($faculty_type_match) {
              $resultmatch = true;
            }
          } elseif($yearsearch){
            if($year_match) {
              $resultmatch = true;
            }
          } elseif($semestersearch){
            if($semester_match) {
              $resultmatch = true;
            }
          }

          if($resultmatch) {

            $resultcount++;
            // set DISSERTATION Courses to a class
            if($current_faculty_courses[$j][$SUBJECT] == $ARTS && $current_faculty_courses[$j][$COURSE] == $DISSERTATION_COURSENUMBER) {
              $faculty_column_output .= return_output("<div class='arts_class arts9990'>");
            } else {
              $faculty_column_output .= return_output("<div class='arts_class'>");
            }

           $faculty_column_output .= return_output("<div class='course-title'>" . $current_faculty_courses[$j][$TITLE] . "</div>");
           $faculty_column_output .= return_output("<div class='year-semester'>" . $current_faculty_courses[$j][$YEAR] . ", ");
           $faculty_column_output .= return_output($current_faculty_courses[$j][$SEMESTER] . "</div>");
           $faculty_column_output .= return_output("<div class='course-dept-number'>" . $current_faculty_courses[$j][$SUBJECT] . " ");
           $faculty_column_output .= return_output($current_faculty_courses[$j][$COURSE] . "</div>");
           $faculty_column_output .= return_output("<div class='faculty'>" . $current_faculty_courses[$j][$INSTRUCTOR] . "</div>");
           $faculty_column_output .= return_output("</div>");
          }
       } // for each faculty course...

        if($resultcount > 0) {
          // close faculty column
          $faculty_column_output .= return_output("</div>");
          build_output($faculty_column_output);
        }

      } // for each faculty...

      // close faculty row
      build_output("</div>");
    }
  } //function echo_grouped_array...

  function build_output($echo_string) {
      global $output;

      $output .= $echo_string;
  }

  function return_output($return_string) {
      return $return_string;
  }
  // build faculty array for filtering
  $source_faculty = build_assoc_array($data_faculty, true);
  $grouped_faculty = group_assoc_array($INSTRUCTOR, $source_faculty);

  // for each $data_files .csv build assoc array and merge them
  $assoc_array = build_assoc_array($data_source_files);
  $assoc_array = remove_secondary_faculty($assoc_array);
  $grouped_assoc_array = group_assoc_array($INSTRUCTOR, $assoc_array);
  //$grouped_assoc_array = clean_faculty_key_names($grouped_assoc_array);

  build_output("<form name='form' action='' method='get'>");
  build_output("<input id='name' name='filter_name' type='text' value='' />");
  build_output("<input type='button' value='CLEAR SEARCH' onclick='location=\"sis_poly_filter.php\"' />");
  build_output("</form>");

  $filter_name = $_GET[$FILTERNAME];

  // display grouped array data
  echo_grouped_array($grouped_assoc_array, $filter_name);

  // display output
  echo $output;
?>

</body>
</html>
