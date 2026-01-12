<?php
session_start();
include 'db.php';

// ACCESS CONTROL
if (!isset($_SESSION['role']) || trim($_SESSION['role']) !== 'admin') {
    die("ACCESS DENIED: You do not have permission to view this page.");
}

// DELETE USER
if (isset($_GET['delete_user'])) {
    $id = $_GET['delete_user'];
    if ($id == $_SESSION['user_id']) {
        echo "<script>alert('You cannot delete your own account!'); window.location='admin.php';</script>";
    } else {
        $conn->query("CALL sp_delete_user($id)");
        $msg = "User deleted.";
    }
}

// PROMOTE / DEMOTE
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

// BACKUP
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
                $sqlScript .= isset($row[$j]) ? '"' . $row[$j] . '"' : '""';
                if ($j < (count($row) - 1)) $sqlScript .= ',';
            }
            $sqlScript .= ");\n";
        }
    }
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=pennywise_backup.sql');
    echo $sqlScript;
    exit;
}

// RESTORE
if (isset($_POST['restore'])) {
    if ($_FILES['sql_file']['tmp_name']) {
        $sql = file_get_contents($_FILES['sql_file']['tmp_name']);
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        if ($conn->multi_query($sql)) {
            do {
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            } while ($conn->more_results() && $conn->next_result());
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
    <link rel="stylesheet" href="admin-style.css"> <!-- external CSS -->
    <link rel="icon" type="image/png" href="wiselogo.png">
</head>

<body>

    <div class="container">
        <header class="admin-header">
            <h1>Admin Panel 🛡️</h1>
            <a href="index.php" class="sm-btn edit">Back to Expenses</a>
        </header>

        <?php if (isset($msg)) echo "<div class='alert'>$msg</div>"; ?>

        <section class="card">
            <h2>User Management</h2>
            <div class="table-wrapper">
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
                        while ($conn->more_results()) {
                            $conn->next_result();
                        }
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
                                        echo "<a href='admin.php?id={$row['id']}&action=promote' class='btn-promote' onclick='return confirm(\"⚠️ Promote this user to ADMIN?\")'>Make Admin ⬆️</a> ";
                                    } else {
                                        echo "<a href='admin.php?id={$row['id']}&action=demote' class='btn-demote' onclick='return confirm(\"⚠️ Demote this Admin?\")'>Demote ⬇️</a> ";
                                    }
                                    echo "<a href='admin.php?delete_user={$row['id']}' class='sm-btn delete' onclick='return confirm(\"❌ Delete this user permanently?\")'>X</a>";
                                } else {
                                    echo "<span class='self-user'>(You)</span>";
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card">
            <h2>Database Tools</h2>
            <div class="db-tools">
                <form method="POST">
                    <button type="submit" name="backup">Download Backup</button>
                </form>
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="sql_file" required>
                    <button type="submit" name="restore" onclick="return confirm('🧨 This will replace all data. Are you sure?')">Restore</button>
                </form>
            </div>
        </section>
    </div>

</body>

</html>