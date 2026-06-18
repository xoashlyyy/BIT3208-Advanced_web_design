<?php
// Secure Database Connection
$conn = mysqli_connect("localhost", "root", "", "bit3208_week6");
if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

// 1. CREATE Operation
if (isset($_POST['add_student'])) {
    if (!empty($_POST['fullname']) && !empty($_POST['email']) && !empty($_POST['course'])) {
        $stmt = $conn->prepare("INSERT INTO students (fullname, email, course) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $_POST['fullname'], $_POST['email'], $_POST['course']);
        $stmt->execute();
        $stmt->close();
        header("Location: students.php");
        exit();
    }
}

// 2. UPDATE Operation
if (isset($_POST['update_student'])) {
    if (!empty($_POST['fullname']) && !empty($_POST['email']) && !empty($_POST['course']) && !empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE students SET fullname = ?, email = ?, course = ? WHERE id = ?");
        $stmt->bind_param("sssi", $_POST['fullname'], $_POST['email'], $_POST['course'], $id);
        $stmt->execute();
        $stmt->close();
        header("Location: students.php");
        exit();
    }
}

// 3. DELETE Operation
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: students.php");
    exit();
}

// Setup Variables for Edit Mode
$edit_mode = false;
$edit_data = ['id' => '', 'fullname' => '', 'email' => '', 'course' => ''];

if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $edit_data = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management</title>
    <style>
        :root {
            --primary: #0ea5e9;
            --primary-hover: #0284c7;
            --background: #f8fafc;
            --surface: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --danger: #ef4444;
            --success: #10b981;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: var(--background);
            color: var(--text-main);
            margin: 0;
            padding: 2rem;
            display: flex;
            justify-content: center;
        }

        .container {
            width: 100%;
            max-width: 900px;
            background: var(--surface);
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05);
        }

        h2 {
            margin-top: 0;
            color: var(--text-main);
            font-weight: 600;
            border-bottom: 2px solid var(--background);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        input[type="text"], input[type="email"] {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 0.95rem;
            transition: border-color 0.2s;
        }

        input[type="text"]:focus, input[type="email"]:focus {
            outline: none;
            border-color: var(--primary);
        }

        button {
            background-color: var(--primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
            white-space: nowrap;
        }

        button.update-btn { background-color: var(--success); }
        button:hover { opacity: 0.9; }

        .btn-clear {
            background-color: var(--border);
            color: var(--text-main);
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        th {
            background-color: var(--background);
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        tr:hover { background-color: #fcfcfc; }

        .actions-cell { display: flex; gap: 1rem; }
        .action-link { text-decoration: none; font-weight: 500; font-size: 0.9rem; }
        .edit-link { color: var(--primary); }
        .delete-link { color: var(--danger); }
        .action-link:hover { text-decoration: underline; }
        
        .edit-notice {
            color: var(--success);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Student Management System</h2>
        
        <?php if($edit_mode): ?>
            <div class="edit-notice">Editing Student #<?php echo htmlspecialchars($edit_data['id']); ?></div>
        <?php endif; ?>

        <form method="POST" class="form-group">
            <?php if($edit_mode): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_data['id']); ?>">
            <?php endif; ?>
            
            <input type="text" name="fullname" placeholder="Full Name" value="<?php echo htmlspecialchars($edit_data['fullname']); ?>" required>
            <input type="email" name="email" placeholder="Email Address" value="<?php echo htmlspecialchars($edit_data['email']); ?>" required>
            <input type="text" name="course" placeholder="Course" value="<?php echo htmlspecialchars($edit_data['course']); ?>" required>
            
            <?php if($edit_mode): ?>
                <button type="submit" name="update_student" class="update-btn">Save Updates</button>
                <a href="students.php" class="btn-clear">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add_student">Save Student</button>
            <?php endif; ?>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // 4. READ Operation
                $result = $conn->query("SELECT * FROM students");
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['id']}</td>
                            <td>" . htmlspecialchars($row['fullname']) . "</td>
                            <td>" . htmlspecialchars($row['email']) . "</td>
                            <td>" . htmlspecialchars($row['course']) . "</td>
                            <td class='actions-cell'>
                                <a href='?edit={$row['id']}' class='action-link edit-link'>Edit</a>
                                <a href='?delete={$row['id']}' class='action-link delete-link' onclick=\"return confirm('Delete this student?');\">Delete</a>
                            </td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>