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
    <link rel="stylesheet" href="index.css">
</head>

<!-- Logout Modal -->
<div id="logout-modal" class="modal">
    <div class="modal-content">
        <p>Are you sure you want to log out?</p>
        <div class="modal-buttons">
            <button id="confirm-logout" class="confirm-btn">Yes</button>
            <button id="cancel-logout" class="cancel-btn">No</button>
        </div>
    </div>
</div>


<body>
    <div class="container">

        <!-- HEADER -->
        <div class="header">
            <h2>PennyWise 💸</h2>

            <div class="header-links">
                <?php if (isset($_SESSION['role']) && trim($_SESSION['role']) === 'admin'): ?>
                    <a href="admin.php" class="admin-btn">Admin Panel</a>
                <?php endif; ?>

                <button id="logout-btn" class="logout-btn">Logout</button>

            </div>
        </div>

        <!-- ADD EXPENSE -->
        <div class="card">
            <h3 id="form-title">Add New Expense</h3>

            <form action="process.php" method="POST" class="expense-form">
                <input type="hidden" name="expense_id" id="expense_id">
                <input type="text" name="description" id="description" placeholder="Description" required>
                <input type="number" step="0.01" name="amount" id="amount" placeholder="Amount" required>
                <select name="category_id" id="category_id" required>
                    <option value="" disabled selected>Select Category</option>
                    <?php
                    $cats = $conn->query("SELECT * FROM categories");
                    while ($row = $cats->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
                    }
                    ?>
                </select>
                <button type="submit" name="save_expense" id="save-btn">Add Expense</button>
                <button type="button" id="cancel-btn" onclick="resetForm()">Cancel</button>
            </form>
        </div>

        <!-- HISTORY -->
        <div class="card">
            <h3>History</h3>

            <table class="history-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($conn->more_results()) {
                        $conn->next_result();
                    }

                    $uid = $_SESSION['user_id'];
                    $result = $conn->query("CALL sp_get_all_expenses($uid)");
                    if ($result) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>{$row['description']}</td>";
                            echo "<td><span class='category-label'>{$row['category']}</span></td>";
                            echo "<td>₱" . number_format($row['amount'], 2) . "</td>";
                            echo "<td>
                        <button onclick='editExpense({$row['id']}, \"{$row['description']}\", {$row['amount']}, {$row['category_id']})'
                            class='edit-btn'>Edit</button>
                        <a href='process.php?delete={$row['id']}'
                           onclick='return confirm(\"Are you sure you want to delete this expense transaction?\")'
                           class='delete-btn'>X</a>
                    </td>";
                            echo "</tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- TOTALS -->
        <div class="card">
            <h3>Totals</h3>

            <div class="totals-grid">
                <?php
                include 'db.php';
                $summary = $conn->query("CALL sp_get_summary($uid)");
                while ($row = $summary->fetch_assoc()) {
                    echo "<div class='total-item'>
                        <strong>{$row['name']}</strong><br>₱" . number_format($row['total'], 2) . "
                      </div>";
                }
                ?>
            </div>
        </div>

    </div>

    <script>
        // Logout Modal
        const logoutBtn = document.getElementById('logout-btn');
        const logoutModal = document.getElementById('logout-modal');
        const confirmLogout = document.getElementById('confirm-logout');
        const cancelLogout = document.getElementById('cancel-logout');

        logoutBtn.addEventListener('click', () => {
            logoutModal.style.display = 'flex';
        });

        cancelLogout.addEventListener('click', () => {
            logoutModal.style.display = 'none';
        });

        confirmLogout.addEventListener('click', () => {
            window.location.href = 'login.php';
        });

        function editExpense(id, desc, amount, catId) {
            document.getElementById('expense_id').value = id;
            document.getElementById('description').value = desc;
            document.getElementById('amount').value = amount;
            document.getElementById('category_id').value = catId;

            document.getElementById('form-title').innerText = "Edit Expense";
            document.getElementById('save-btn').innerText = "Update Expense";
            document.getElementById('cancel-btn').style.display = "block";
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