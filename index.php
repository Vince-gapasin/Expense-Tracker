<?php 
session_start();
include 'db.php'; 

// Force Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PennyWise</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom: 2px solid #ddd; padding-bottom: 10px;">
        <h2 style="margin:0;">PennyWise 💸</h2>
        
        <div style="display:flex; gap: 5px;">
            <?php if(isset($_SESSION['role']) && trim($_SESSION['role']) === 'admin'): ?>
                <a href="admin.php" class="sm-btn edit" style="background-color: #17a2b8;">Admin Panel</a>
            <?php endif; ?>
            
            <a href="login.php" class="sm-btn delete" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
        </div>
    </div>

    <div class="card">
        <h3 id="form-title">Add New Expense</h3>
        <form action="process.php" method="POST">
            <input type="hidden" name="expense_id" id="expense_id">
            
            <input type="text" name="description" id="description" placeholder="Description" required>
            <input type="number" step="0.01" name="amount" id="amount" placeholder="Amount" required>
            
            <select name="category_id" id="category_id" required>
                <option value="" disabled selected>Select Category</option>
                <?php
                $cats = $conn->query("SELECT * FROM categories");
                while($row = $cats->fetch_assoc()) {
                    echo "<option value='{$row['id']}'>{$row['name']}</option>";
                }
                ?>
            </select>
            
            <button type="submit" name="save_expense" id="save-btn">Add Expense</button>
            <button type="button" id="cancel-btn" onclick="resetForm()" style="display:none; background:#6c757d;">Cancel</button>
        </form>
    </div>

    <div class="card">
        <h3>History</h3>
        <table>
            <thead>
                <tr>
                    <th>Desc</th>
                    <th>Cat</th>
                    <th>Amount</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Flush connection to prevent "Commands out of sync" error
                while($conn->more_results()){ $conn->next_result(); } 

                $uid = $_SESSION['user_id'];
                $result = $conn->query("CALL sp_get_all_expenses($uid)");
                if($result) {
                    while($row = $result->fetch_assoc()){
                        echo "<tr>";
                        echo "<td>{$row['description']}</td>";
                        echo "<td><span class='badge'>{$row['category']}</span></td>";
                        echo "<td>$" . number_format($row['amount'], 2) . "</td>";
                        echo "<td>
                                <button class='sm-btn edit' onclick='editExpense({$row['id']}, \"{$row['description']}\", {$row['amount']}, {$row['category_id']})'>Edit</button>
                                <a href='process.php?delete={$row['id']}' 
                                class='sm-btn delete' 
                                onclick='return confirm(\"Are you sure you want to delete this expense transaction?\")'>X</a>
                              </td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3>Totals</h3>
        <div class="summary-grid">
            <?php
            // Re-connect or flush needed because SPs return multiple result sets
            include 'db.php'; 
            $uid = $_SESSION['user_id'];
            $summary = $conn->query("CALL sp_get_summary($uid)");
            while($row = $summary->fetch_assoc()){
                echo "<div class='stat-box'><strong>{$row['name']}</strong><br>$" . number_format($row['total'], 2) . "</div>";
            }
            ?>
        </div>
    </div>
</div>

<script>
    // JS to fill the form for editing
    function editExpense(id, desc, amount, catId) {
        document.getElementById('expense_id').value = id;
        document.getElementById('description').value = desc;
        document.getElementById('amount').value = amount;
        document.getElementById('category_id').value = catId;

        document.getElementById('form-title').innerText = "Edit Expense";
        document.getElementById('save-btn').innerText = "Update Expense";
        document.getElementById('cancel-btn').style.display = "inline-block";
    }

    function resetForm() {
        document.getElementById('expense_id').value = "";
        document.getElementById('description').value = "";
        document.getElementById('amount').value = "";
        document.getElementById('category_id').value = "";

        document.getElementById('form-title').innerText = "Add New Expense";
        document.getElementById('save-btn').innerText = "Add Expense";
        document.getElementById('cancel-btn').style.display = "none";
    }
</script>

</body>
</html>