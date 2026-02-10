<?php
session_start();
include '../config.php';

if (isset($_POST['update'])) {
    $id = input($_POST['id']);
    $fb = input($_POST['feedback']);
    $st = input($_POST['status']);

    $sql = "UPDATE Aspirasi SET feedback = '$fb', status = '$st' WHERE id_aspirasi = '$id'";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: index.php");
    } else {
        echo "Gagal update: " . mysqli_error($conn);
    }
}
?>