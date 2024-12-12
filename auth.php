<?php
$is = $_POST['type']; // препод или студент
$login = trim(strtolower($_POST['login']));//приведение к нижнему регистру НЕ РАБОТАЕТ
$password = trim(strtolower($_POST['password']));
$authenticated = false;

session_start();

$_SESSION['login'] = $login;

$conn = mysqli_connect("localhost", "root", "","page");
if (!$conn) {
    die("Ошибка: " .  mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");


$sql = "SELECT * FROM " . $is;
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        if (strtolower(trim($row["surname"])) == $login && trim($row['password']) == $password) {
            if ($is == 'student') {
                $_SESSION['student_id'] = trim($row['student_id']);
            }

            if ($is == 'teacher'){
                $_SESSION['teacher_id'] = trim($row['teacher_id']);
            }

            $authenticated = true;
            break;
        }
    }
}

if ($authenticated && $is == 'student') {
    header("Location: student.php");
    exit();
} else {
    if ($authenticated && $is == 'teacher') {
        header("Location: teacher.php");
        exit();
    } else {
        echo "Неправильный логин или пароль.";
        session_destroy();
    }
}

mysqli_close($conn);
?>
