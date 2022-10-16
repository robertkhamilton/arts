<?php include_once("index.html"); ?>

<?php 

$row = 1;
if (($handle = fopen("main.csv", "r")) !== FALSE) {
    
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data);
        $row++;
        for ($c=0; $c < $num; $c++) {
            echo "<div>$data[$c] . "</div>\n";
            
        }
    }
    fclose($handle);

}
        


$row = 1;
if (($handle = fopen("main.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data);
        echo "<p> $num fields in line $row: <br /></p>\n";
        $row++;
        for ($c=0; $c < $num; $c++) {
            echo $data[$c] . "<br />\n";
        }
    }
    fclose($handle);
}


?>
