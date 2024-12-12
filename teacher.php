<?php
session_start();

$teacher_id = $_SESSION['teacher_id'];

$conn = mysqli_connect("localhost", "root", "", "page");
if ($conn->connect_error) {
    die("Ошибка: " . $conn->connect_error);
}
mysqli_set_charset($conn, "utf8");


$Tname = '';
$Tsurname = '';
$Tlastname = '';

$sql = 'SELECT name,surname,lastname FROM teacher WHERE teacher_id=' . $teacher_id . '';
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $Tname = trim($row['name']);
        $Tsurname = trim($row['surname']);
        $Tlastname = trim($row['lastname']);
    }
}



$students = [];
$sql = "SELECT * FROM student ORDER BY surname ASC";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row['surname'] . " " . $row['name'];
    }
}

$classes = [];
$subjects = [];

$sql = "SELECT klass FROM lesson WHERE teacher_id = $teacher_id";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        if (!in_array(trim($row['klass']), $classes)) {
            $classes[] = (int)trim($row['klass']);
        }
    }
}


for($c = 0; $c < count($classes); $c++){
    $sql = "SELECT * FROM lesson WHERE TRIM(klass) = $classes[$c] AND teacher_id = $teacher_id";
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            if (!in_array(trim($row['predmet']), $subjects)) {
                $subjects[] = $row['predmet'];
            }
    }
}
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>teacher</title>
    <style>
        body {
            display: flex;
            margin: 20px;
            padding: 20px;
        }

        .show_student {
            display: inline-block;
            font-size: x-large;
        }
        form{
            margin-top: 0px;
            margin-bottom: 50px;
        }
        select{
            font-size: 16px;
        }
    </style>
</head>
<body>
<div class="show_student">
    <?php 
        echo '<h2>Добро пожаловать, ' . $Tsurname . ' ' . $Tname . ' ' . $Tlastname . '</h2>'; 
    ?>
    <h3>Посмотреть ученика: </h3>
    <form action="show_student's_marks.php" method="post" name="form1">
        Фамилия -
        <select name="student">
            <?php
            foreach ($students as $stud) {
                echo "<option>" . $stud . "</option>";
            }
            ?>
        </select> <input type="submit" value="показать">
    </form>

    <h3>Посмотреть класс: </h3>
    <form action="mark's_table.php" method="post" name="form2">
        Класс -
        <select name="class">
            <?php
            sort($classes);
            foreach ($classes as $class) {
                echo "<option>" . $class . "</option>";
            }
            ?>
        </select> 
        Предмет -
        <select name="subject">
            <?php
            sort($subjects);
            foreach ($subjects as $subject) {
                echo "<option>" . $subject . "</option>";
            }
            ?>
        </select> 
        <input type="submit" value="показать">
    </form>
    <a href='index.html'>Выйти</a>
</div>
</body>
</html>
