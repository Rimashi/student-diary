<?php
session_start();

$student_id = $_SESSION['student_id'];

$conn = mysqli_connect("localhost", "root", "", "page");
if ($conn->connect_error) {
    die("Ошибка: " . $conn->connect_error);
}

mysqli_set_charset($conn, "utf8");

$sql = "SELECT * FROM student WHERE TRIM(student_id)=$student_id";
if ($result = $conn->query($sql)) {
    while($row = $result->fetch_assoc()){
        $name = trim($row['name']);
        $surname = trim($row['surname']);
        $class = trim($row['klass']);
    }    
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>student</title>
    <style>
    body{
        margin: 20px;
        padding: 20px;
    }
    .content {
        display: flex;
        text-align: center;
    }
    
    table{
        margin: 0 auto;
        font-size: 20px;
        border: 2px solid black;
    }

    th{
        border: 2px solid black;
    }

    td{
        border: 2px solid black; 
        padding: 10px;
    }
</style>
</head>
<body>
    <h1>Дневник учащегося</h1>
    <?php
        echo "<h1>". $surname . ' ' . $name ."</h1>";
    ?>
</body>
</html>

<?php
    $grades = [];
    $description = [];

    $sql = "SELECT * FROM grade WHERE student_id='$student_id'";
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $mark = $row['grade'];
            $desc = $row['description'];
            //оценки[id_урока] = строка с оценками
            if(isset($grades[$row['lesson_id']])){//если такой урок есть в массиве, то берем значение и конкат 
                array_push($grades[$row['lesson_id']], $mark);
                array_push($description[$row['lesson_id']], $desc);
            } else{
                $grades[$row['lesson_id']][]=$mark;
                $description[$row['lesson_id']][] = $desc;
            }
        }
    }

    if(count($grades) !=0 ){
        echo "<div class='content'><table>
        <tr>
            <td>Предмет</td>
            <td>Дата</td>
            <td>Оценка</td>
            <td>Тема задания</td>
            <td>Тема урока</td>
            <td>Домашнее задание</td>
        </tr>";
        foreach($grades as $les_id => $grade){
            $sql = "SELECT * FROM lesson WHERE lesson_id = $les_id";
            if ($result = $conn->query($sql)) {
                while ($row = $result->fetch_assoc()) {
                    $date =  explode("-", $row['lesson_date']);
                    
                    for($i = 0; $i < count($grade); $i++){
                        echo "<tr> 
                        <td>". $row['predmet'] ."</td>
                        <td>". $date[2] . "/" . $date[1] ."</td> 
                        <td>". $grade[$i] ."</td>
                        <td>" . $description[$les_id][$i] ."</td>
                        <td>" . $row['subject'] . "</td>
                        <td>" . $row['hometask'] . "</td>
                        </tr>";    
                    }
                }
            }
        }
        echo "</table></div>";
    } else {
        echo "<center><h2>У учащегося нет оценок по предметам</h2></center>";
    }


echo "<a href='index.html'>Назад</a>";
unset($_SESSION["student_id"]); 
session_destroy();
$conn->close();
?>
