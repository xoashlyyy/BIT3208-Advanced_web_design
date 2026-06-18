<?php
// 1. SESSION MANAGEMENT & USER LOGIN (Bonus Feature)
session_start();

$login_error = '';
if (isset($_POST['login'])) {
    // Simple hardcoded authentication for the Week 6 scope
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['logged_in'] = true;
        header("Location: employees.php");
        exit();
    } else {
        $login_error = "Invalid credentials. Please use admin / admin123.";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: employees.php");
    exit();
}

// Secure Database Connection
$conn = mysqli_connect("localhost", "root", "", "bit3208_week6");
if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

$system_message = '';
$system_message_type = '';

// --- PROTECTED DASHBOARD LOGIC ---
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {

    // 2. FORM VALIDATION & CREATE OPERATION (Bonus Feature)
    if (isset($_POST['add_employee'])) {
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        $dept = trim($_POST['department']);

        // Backend Validation
        if (empty($fullname) || empty($email) || empty($dept)) {
            $system_message = "All fields are required.";
            $system_message_type = "error";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $system_message = "Invalid email format.";
            $system_message_type = "error";
        } else {
            $stmt = $conn->prepare("INSERT INTO employees (fullname, email, department) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $fullname, $email, $dept);
            $stmt->execute();
            $stmt->close();
            header("Location: employees.php?msg=added");
            exit();
        }
    }

    // FORM VALIDATION & UPDATE OPERATION
    if (isset($_POST['update_employee'])) {
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        $dept = trim($_POST['department']);
        $id = (int)$_POST['id'];

        if (empty($fullname) || empty($email) || empty($dept)) {
            $system_message = "All fields are required to update.";
            $system_message_type = "error";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $system_message = "Invalid email format.";
            $system_message_type = "error";
        } else {
            $stmt = $conn->prepare("UPDATE employees SET fullname = ?, email = ?, department = ? WHERE id = ?");
            $stmt->bind_param("sssi", $fullname, $email, $dept, $id);
            $stmt->execute();
            $stmt->close();
            header("Location: employees.php?msg=updated");
            exit();
        }
    }

    // DELETE OPERATION
    if (isset($_GET['delete'])) {
        $id = (int)$_GET['delete']; 
        $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header("Location: employees.php?msg=deleted");
        exit();
    }

    // Success Messages handling
    if(isset($_GET['msg'])) {
        $system_message_type = "success";
        if($_GET['msg'] === 'added') $system_message = "Employee registered successfully.";
        if($_GET['msg'] === 'updated') $system_message = "Employee record updated.";
        if($_GET['msg'] === 'deleted') $system_message = "Employee record removed.";
    }

    // Setup Variables for Edit Mode
    $edit_mode = false;
    $edit_data = ['id' => '', 'fullname' => '', 'email' => '', 'department' => ''];

    if (isset($_GET['edit'])) {
        $edit_mode = true;
        $id = (int)$_GET['edit'];
        $stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $edit_data = $row;
        }
        $stmt->close();
    }

    // 3. SEARCH FUNCTIONALITY (Bonus Feature)
    $search_query = "";
    $search_term = "";
    if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
        $search_term = "%" . trim($_GET['search']) . "%";
        $search_query = "WHERE fullname LIKE ? OR department LIKE ?";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management System</title>
    <style>
        :root {
            --primary: #0ea5e9; --primary-hover: #0284c7; --background: #f8fafc;
            --surface: #ffffff; --text-main: #0f172a; --text-muted: #64748b;
            --border: #e2e8f0; --danger: #ef4444; --success: #10b981;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: var(--background);
            color: var(--text-main);
            margin: 0; padding: 2rem 1rem;
            display: flex; justify-content: center;
        }

        .container {
            width: 100%; max-width: 1000px;
            background: var(--surface); padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);
        }

        .login-container { max-width: 400px; text-align: center; margin: 4rem auto; }

        h2 {
            margin-top: 0; font-weight: 600;
            border-bottom: 2px solid var(--background);
            padding-bottom: 0.5rem; margin-bottom: 1.5rem;
            display: flex; justify-content: space-between; align-items: center;
        }

        .logout-btn {
            font-size: 0.85rem; background: var(--danger);
            color: white; padding: 0.4rem 0.8rem; border-radius: 4px; text-decoration: none;
        }

        .form-group { display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
        .form-group > input { flex: 1; min-width: 200px; }

        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border);
            border-radius: 6px; font-size: 0.95rem; transition: border-color 0.2s;
        }
        input:focus { outline: none; border-color: var(--primary); }

        button {
            background-color: var(--primary); color: white;
            padding: 0.75rem 1.5rem; border: none; border-radius: 6px;
            font-weight: 500; cursor: pointer; white-space: nowrap; width: 100%;
        }
        @media(min-width: 600px) { button { width: auto; } }
        button.update-btn { background-color: var(--success); }
        button:hover { opacity: 0.9; }

        .btn-clear {
            background-color: var(--border); color: var(--text-main);
            text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 6px;
            font-weight: 500; display: inline-flex; justify-content: center; align-items: center;
        }

        /* Responsive Table Wrapper */
        .table-wrapper { overflow-x: auto; width: 100%; }
        table { width: 100%; border-collapse: collapse; min-width: 600px; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border); }
        th { background-color: var(--background); color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; }
        tr:hover { background-color: #fcfcfc; }

        .actions-cell { display: flex; gap: 1rem; }
        .action-link { text-decoration: none; font-weight: 500; font-size: 0.9rem; }
        .edit-link { color: var(--primary); }
        .delete-link { color: var(--danger); }

        .msg { padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; font-weight: 500; }
        .msg.error { background: #fee2e2; color: #991b1b; }
        .msg.success { background: #d1fae5; color: #065f46; }

        @media (max-width: 768px) {
            .container { padding: 1.5rem; }
            .header-actions { flex-direction: column; align-items: stretch; }
        }
    </style>
</head>
<body>

    <?php if (!isset($_SESSION['logged_in'])): ?>
    <div class="container login-container">
        <h2>System Login</h2>
        <?php if($login_error): ?>
            <div class="msg error"><?php echo htmlspecialchars($login_error); ?></div>
        <?php endif; ?>
        <form method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
            <input type="text" name="username" placeholder="Username (admin)" required>
            <input type="password" name="password" placeholder="Password (admin123)" required>
            <button type="submit" name="login">Sign In</button>
        </form>
    </div>
    <?php exit(); endif; ?>
    <div class="container">
        <h2>
            Employee Directory 
            <a href="?logout=true" class="logout-btn">Log Out</a>
        </h2>
        
        <?php if($system_message): ?>
            <div class="msg <?php echo $system_message_type; ?>"><?php echo htmlspecialchars($system_message); ?></div>
        <?php endif; ?>

        <div class="header-actions" style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
            <form method="GET" style="display: flex; gap: 0.5rem; flex: 1;">
                <input type="text" name="search" placeholder="Search by name or department..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit">Search</button>
                <a href="employees.php" class="btn-clear">Clear</a>
            </form>
        </div>

        <?php if($edit_mode): ?>
            <div class="msg success">Editing Record #<?php echo htmlspecialchars($edit_data['id']); ?></div>
        <?php endif; ?>
        
        <form method="POST" class="form-group">
            <?php if($edit_mode): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_data['id']); ?>">
            <?php endif; ?>
            
            <input type="text" name="fullname" placeholder="Full Name" value="<?php echo htmlspecialchars($edit_data['fullname']); ?>" required>
            <input type="email" name="email" placeholder="Email Address" value="<?php echo htmlspecialchars($edit_data['email']); ?>" required>
            <input type="text" name="department" placeholder="Department" value="<?php echo htmlspecialchars($edit_data['department']); ?>" required>
            
            <?php if($edit_mode): ?>
                <button type="submit" name="update_employee" class="update-btn">Save Updates</button>
                <a href="employees.php" class="btn-clear">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add_employee">Register Employee</button>
            <?php endif; ?>
        </form>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($search_query)) {
                        $search_stmt = $conn->prepare("SELECT * FROM employees $search_query");
                        $search_stmt->bind_param("ss", $search_term, $search_term);
                        $search_stmt->execute();
                        $result = $search_stmt->get_result();
                    } else {
                        $result = $conn->query("SELECT * FROM employees");
                    }

                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>" . htmlspecialchars($row['fullname']) . "</td>
                                <td>" . htmlspecialchars($row['email']) . "</td>
                                <td>" . htmlspecialchars($row['department']) . "</td>
                                <td class='actions-cell'>
                                    <a href='?edit={$row['id']}' class='action-link edit-link'>Edit</a>
                                    <a href='?delete={$row['id']}' class='action-link delete-link' onclick=\"return confirm('Are you sure you want to delete this record?');\">Delete</a>
                                </td>
                              </tr>";
                    }
                    
                    if (isset($search_stmt)) {
                        $search_stmt->close();
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>