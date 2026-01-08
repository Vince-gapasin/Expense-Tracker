<?php
include 'db.php';

// Handle Add or Update
if (isset($_POST['save_expense'])) {
    $id = $_POST['expense_id']; // Hidden field
    $descr = $_POST['description'];
    $amount = $_POST['amount'];
    $cat_id = $_POST['category_id'];

    if ($id == "") {
        // CREATE: No ID exists, so we Insert
        $conn->query("CALL sp_add_expense($cat_id, $amount, '$descr')");
    } else {
        // UPDATE: ID exists, so we Update
        $conn->query("CALL sp_update_expense($id, $cat_id, $amount, '$descr')");
    }
    
    header("Location: index.php");
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("CALL sp_delete_expense($id)");
    header("Location: index.php");
}

$conn->close();
?>