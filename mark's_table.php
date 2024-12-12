<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "page");
if (!$conn) {
    die("Ошибка: " .  mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

$class = $_POST['class'];
$subject = $_POST['subject'];

$sql = "SELECT * FROM student WHERE TRIM(klass) = '$class' ORDER BY surname ASC"; // убираем пробелы в klass и сортируем по алфавиту
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $students[$row['student_id']] = $row['surname'] . " " . $row['name'];
}


$sql = "SELECT * FROM lesson WHERE TRIM(klass) = '$class' AND LOWER(predmet) = '$subject' ORDER BY lesson_date"; // сортируем по дате урока, чтобы в дальнейшем заполнение было корректное, в календарном порядке
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)){
    $les_id = $row['lesson_id'];
    $date = explode("-", $row['lesson_date'])[2] . "/" . explode("-", $row['lesson_date'])[1];
    $teacher_id = $row['teacher_id'];
    $hometask = $row['hometask'];
    $theme = $row['subject'];

    $lessons[$date] = [$les_id, $teacher_id];
    $date_list[$date] = [$theme, $hometask];
}

$marks = array();

$sql = "SELECT student_id, lesson_id, grade FROM grade";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)){
    $les_id = $row['lesson_id'];
    $stud_id = $row['student_id'];
    $mark = $row['grade'];

    if( isset( $marks[$stud_id] ) ){
        if( isset( $marks[$stud_id][$les_id] ) ){
            $grades = $marks[$stud_id][$les_id] . "/" . $mark;
            $marks[$stud_id][$les_id] = $grades;
        } else{
            $marks[$stud_id][$les_id] = $mark;
        }
    }else{
        $marks[$stud_id][$les_id] = $mark;
    }
}

$absence = array();

$sql = "SELECT * FROM absence";
$result = mysqli_query($conn, $sql);
while($row = mysqli_fetch_assoc($result)){
    $les_id = $row['lesson_id'];
    $stud_id = $row['student_id'];

    if( isset($absence[$stud_id]) ){
        $absence[$stud_id][] = $les_id;
    } else{
        $absence[$stud_id] = array($les_id);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>student</title>
    <style>
    body{
        margin: 20px;
        padding: 20px;
    }
    .main {
        display: flex;
    }
    .left{
        flex: 1;
        margin-right: 10px;
        font-size: 1.2rem;
    }
    .right{
        flex: 1;
        font-size: 1.2rem;
    }
    
    table{
        margin: 0 auto;
        margin-top: 10px;
        font-size: 20px;
        border: 2px solid black;
    }

    th{
        border: 2px solid black;
        padding: 10px;
        font-size: 25px;
    }

    td{
        border: 2px solid black; 
        padding: 10px;
    }

    td input {
        width: 100%;
        height: 100%;
    }
</style>
</head>
<body>
    <?php
        echo "<h1>". $class . ' класс, ' . $subject ."</h1>";
    ?>
    <div class="main">
        <div class="left">
            <table>
                <tr>
                <th>
                    №
                </th>
                <th>
                    Фамилия, Имя
                </th>
                <?php

                    foreach($lessons as $key => $value){
                        echo "<th>" . $key ."</th>";
                    }
                    echo "<th>Итого</th> </tr>";

                    $i = 1;

                    foreach($students as $stud_id => $surName){
                        $grade = 0;
                        $kol = 0;
                        $st = "<tr> <td>" . $i . "</td> <td>" . $surName . "</td>";

                        foreach($lessons as $date => $value){
                            $les_id = $value[0];
                            
                            if (isset($marks[$stud_id][$les_id])){
                                $st .= "<td>" . $marks[$stud_id][$les_id] . "</td>";
                                for($m = 0; $m < count(explode('/', $marks[$stud_id][$les_id])); $m++){
                                    $grade += (int)explode('/', $marks[$stud_id][$les_id])[$m];
                                    $kol++;
                                }
                            }else{
                                if (isset($absence[$stud_id])){
                                    if (in_array($les_id, $absence[$stud_id])){
                                        $st .= "<td> н </td>";
                                    } else{
                                        $st .= "<td> </td>";
                                    }
                                } else{
                                    $st .= "<td> </td>";
                                }
                            }
                        }

                        if($grade > 0){
                            $st .= "<td>" . round($grade/$kol, 0) . "</td>";
                        } else{
                            $st .= "<td> н/а </td>"; 
                        }
                        echo $st . "</tr>";
                        $i++;
                    }
                ?>
            </table>
        </div>
        <div class="right">
            <?php
                $sql = "SELECT surname, name, lastname FROM teacher WHERE teacher_id = $teacher_id";
                $result = mysqli_query($conn, $sql);
                $row = mysqli_fetch_assoc($result);
                echo "Учитель: ". $row['surname'] . " " . $row['name'] . " " . $row['lastname'];
            ?>

            <table>
                <tr>
                    <th>
                        Дата
                    </th>
                    <th>
                        Тема урока
                    </th>
                    <th>
                        Домашнее задание
                    </th>
                </tr>
                <?php
                    foreach($date_list as $key => $value){
                        echo "<tr> <td>" . $key . "</td> <td>" . $value[0] . "</td> <td>" . $value[1] . "</td> </tr>"; 
                    }
                ?>
            </table>
        </div>    
    </div>    
    <a href='teacher.php'>Выйти</a>
</body>
</html>

