<?php
session_start();
include 'db.php';

// Access Control: Only Admins Allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("ACCESS DENIED: You do not have permission to view this page.");
}

// --- LOGIC: BACKUP ---
if (isset($_POST['backup'])) {
    $tables = ['categories', 'expenses', 'users']; // Tables to backup
    $sqlScript = "";
    
    foreach ($tables as $table) {
        // Get Create Table Structure
        $row = $conn->query("SHOW CREATE TABLE $table")->fetch_row();
        $sqlScript .= "\n\n" . $row[1] . ";\n\n";
        
        // Get Data
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

    // Force Download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=db_backup_' . date('Y-m-d') . '.sql');
    echo $sqlScript;
    exit;
}

// --- LOGIC: RESTORE ---
if (isset($_POST['restore'])) {
    if ($_FILES['sql_file']['tmp_name']) {
        $sql = file_get_contents($_FILES['sql_file']['tmp_name']);
        
        // Disable Foreign Key Checks temporarily to prevent errors during drop/insert
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        
        // Execute multiple queries
        $conn->multi_query($sql);
        
        // Clear results to prevent sync errors
        while ($conn->next_result()) {;}
        
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        $msg = "Database Restored Successfully!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Admin Panel</title>
</head>
<body>
<div class="container">
    <div class="card">
        <h1>Admin Panel 🛡️</h1>
        <p>Welcome, <b><?php echo $_SESSION['username']; ?></b></p>
        <a href="index.php" class="sm-btn edit">Back to Dashboard</a>
        <hr>
        
        <?php if(isset($msg)) echo "<p style='color:green; font-weight:bold;'>$msg</p>"; ?>

        <h3>Database Backup</h3>
        <p>Download a copy of your current data.</p>
        <form method="POST">
            <button type="submit" name="backup" style="background:#17a2b8;">Download .SQL Backup</button>
        </form>
        <br>

        <h3>Database Restore</h3>
        <p style="color:red; font-size: 12px;">Warning: This will overwrite current data.</p>
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="sql_file" required>
            <button type="submit" name="restore" style="background:#dc3545;" onclick="return confirm('Are you sure? This will wipe current data!')">Restore Database</button>
        </form>
    </div>
</div>
</body>
</html>