<?php include_once("index.html"); ?>


<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="main.css">
</head>

<style>
<?php include 'main.css'; ?>
</style>

<body>

<?php
    
echo "AAA\n";
    
$row = 1;
if (($handle = fopen("main.csv", "r")) !== FALSE) {
    
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data);
        $row++;
        for ($c=0; $c < $num; $c++) {
            echo "<div = 'arts_class'>$data[$c] . </div>\n";
            
        }
    }
    fclose($handle);

}
?>   




<?php 
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
    
</body>
</html>
