<?php
session_start();
include 'db.php';

// 1. ACCESS CONTROL
if (!isset($_SESSION['role']) || trim($_SESSION['role']) !== 'admin') {
    die("ACCESS DENIED: You do not have permission to view this page.");
}

// --- LOGIC A: DELETE USER ---
if (isset($_GET['delete_user'])) {
    $id = $_GET['delete_user'];
    if ($id == $_SESSION['user_id']) {
        echo "<script>alert('You cannot delete your own account!'); window.location='admin.php';</script>";
    } else {
        $conn->query("CALL sp_delete_user($id)");
        $msg = "User deleted.";
    }
}

// --- LOGIC B: PROMOTE / DEMOTE ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    
    if ($id == $_SESSION['user_id']) {
        echo "<script>alert('You cannot change your own role!'); window.location='admin.php';</script>";
    } else {
        if ($action == 'promote') {
            $conn->query("CALL sp_update_user_role($id, 'admin')");
            $msg = "User promoted to Admin!";
        } elseif ($action == 'demote') {
            $conn->query("CALL sp_update_user_role($id, 'user')");
            $msg = "User demoted to Standard User.";
        }
    }
}

// --- LOGIC C: BACKUP (FIXED) ---
if (isset($_POST['backup'])) {
    $tables = ['categories', 'expenses', 'users']; 
    $sqlScript = "";
    foreach ($tables as $table) {
        $sqlScript .= "DROP TABLE IF EXISTS $table;\n";
        $row = $conn->query("SHOW CREATE TABLE $table")->fetch_row();
        $sqlScript .= "\n" . $row[1] . ";\n\n";
        $result = $conn->query("SELECT * FROM $table");
        while ($row = $result->fetch_row()) {
            $sqlScript .= "INSERT INTO $table VALUES(";
            for ($j = 0; $j < count($row); $j++) {
                $row[$j] = addslashes($row[$j]);
                $row[$j] = str_replace("\n", "\\n", $row[$j]);
                if (isset($row[$j])) { $sqlScript .= '"' . $row[$j] . '"'; } else { $sqlScript .= '""'; }
                if ($j < (count($row) - 1)) { $sqlScript .= ','; }
            }
            $sqlScript .= ");\n";
        }
    }
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=pennywise_backup.sql');
    echo $sqlScript;
    exit;
}

// --- LOGIC D: RESTORE ---
if (isset($_POST['restore'])) {
    if ($_FILES['sql_file']['tmp_name']) {
        $sql = file_get_contents($_FILES['sql_file']['tmp_name']);
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        if ($conn->multi_query($sql)) {
            do { if ($result = $conn->store_result()) { $result->free(); } } while ($conn->more_results() && $conn->next_result());
            $msg = "Database Restored Successfully!";
        } else {
            $msg = "Error restoring: " . $conn->error;
        }
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    
</head>
<body>

<div class="container" style="max-width: 700px;">
    
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2>Admin Panel 🛡️</h2>
        <a href="index.php" class="sm-btn edit">Back to Expenses</a>
    </div>

    <?php if(isset($msg)) echo "<div class='alert'>$msg</div>"; ?>

    <div class="card">
        <h3>User Management</h3>
        <table class="user-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while($conn->more_results()){ $conn->next_result(); }
                $result = $conn->query("CALL sp_get_all_users()");
                
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $is_me = ($row['id'] == $_SESSION['user_id']);
                        $roleClass = ($row['role'] == 'admin') ? 'role-admin' : 'role-user';
                        
                        echo "<tr>";
                        echo "<td><b>{$row['username']}</b></td>";
                        echo "<td><span class='role-badge $roleClass'>" . strtoupper($row['role']) . "</span></td>";
                        echo "<td>";
                        if (!$is_me) {
                            if ($row['role'] == 'user') {
                                // UPGRADE: Added confirmation for Promoting
                                echo "<a href='admin.php?id={$row['id']}&action=promote' 
                                         class='btn-promote'
                                         onclick='return confirm(\"⚠️ Are you sure you want to promote this user to ADMIN? They will have full system access.\")'>Make Admin ⬆️</a> ";
                            } else {
                                // UPGRADE: Added confirmation for Demoting
                                echo "<a href='admin.php?id={$row['id']}&action=demote' 
                                         class='btn-demote'
                                         onclick='return confirm(\"⚠️ Are you sure you want to demote this Admin to a standard User?\")'>Demote ⬇️</a> ";
                            }
                            // Delete Confirmation (Already existed, but good to double check)
                            echo "<a href='admin.php?delete_user={$row['id']}' class='sm-btn delete' onclick='return confirm(\"❌ Are you sure you want to DELETE this user permanently?\")'>X</a>";
                        } else {
                            echo "<span style='color:#ccc; font-size:12px;'> (You) </span>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="card" style="margin-top: 20px;">
        <h3>Database Tools</h3>
        <div style="display:flex; gap:10px; justify-content: space-between;">
            <form method="POST" style="width:48%;">
                <button type="submit" name="backup" style="background:#6f42c1;">Download Backup</button>
            </form>
            
            <form method="POST" enctype="multipart/form-data" style="width:48%; display:flex; gap:5px;">
                <input type="file" name="sql_file" required style="width: 60%; font-size:11px;">
                <button type="submit" name="restore" style="background:#dc3545; width:40%;" onclick="return confirm('🧨 SUPER WARNING: This will wipe ALL current data and replace it with the backup file. Are you absolutely sure?')">Restore</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>