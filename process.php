<?php
session_start();
include 'db.php';

// Security: Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$current_user_id = $_SESSION['user_id'];

// HANDLE SAVE (ADD / EDIT)
if (isset($_POST['save_expense'])) {
    $id = $_POST['expense_id']; 
    $descr = $_POST['description'];
    $amount = $_POST['amount'];
    $cat_id = $_POST['category_id'];

    if ($id == "") {
        // CREATE: Pass user ID
        $conn->query("CALL sp_add_expense($current_user_id, $cat_id, $amount, '$descr')");
    } else {
        // UPDATE: Pass user ID to ensure ownership
        $conn->query("CALL sp_update_expense($id, $current_user_id, $cat_id, $amount, '$descr')");
    }
    
    header("Location: index.php");
}

// HANDLE DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // DELETE: Pass user ID so you can't delete others' data via URL hacking
    $conn->query("CALL sp_delete_expense($id, $current_user_id)");
    header("Location: index.php");
}

$conn->close();
?>