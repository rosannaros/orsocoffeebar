<?php
session_start();
if ($_POST['role']) {
    $_SESSION['id_user'] = $_POST['id_user'];
    $_SESSION['role'] = $_POST['role'];
    $_SESSION['nama'] = $_POST['nama'];
    echo "success";
}
?>