<?php
// Secure Database Connection
$conn = mysqli_connect("localhost", "root", "", "bit3208_week6");
if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

// 1. CREATE Operation
if (isset($_POST['add_book'])) {
    if (!empty($_POST['title']) && !empty($_POST['author']) && !empty($_POST['category'])) {
        $stmt = $conn->prepare("INSERT INTO books (title, author, category) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $_POST['title'], $_POST['author'], $_POST['category']);
        $stmt->execute();
        $stmt->close();
        header("Location: library.php");
        exit();
    }
}

// 2. UPDATE Operation
if (isset($_POST['update_book'])) {
    if (!empty($_POST['title']) && !empty($_POST['author']) && !empty($_POST['category']) && !empty($_POST['book_id'])) {
        $id = (int)$_POST['book_id'];
        $stmt = $conn->prepare("UPDATE books SET title = ?, author = ?, category = ? WHERE book_id = ?");
        $stmt->bind_param("sssi", $_POST['title'], $_POST['author'], $_POST['category'], $id);
        $stmt->execute();
        $stmt->close();
        header("Location: library.php");
        exit();
    }
}

// 3. DELETE Operation
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM books WHERE book_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: library.php");
    exit();
}

// Setup Variables for Edit Mode
$edit_mode = false;
$edit_data = ['book_id' => '', 'title' => '', 'author' => '', 'category' => ''];

if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM books WHERE book_id = ?");
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
    <title>Library Management</title>
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

        input[type="text"] {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 0.95rem;
            transition: border-color 0.2s;
        }

        input[type="text"]:focus {
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
        <h2>Library Inventory</h2>
        
        <?php if($edit_mode): ?>
            <div class="edit-notice">Editing Book #<?php echo htmlspecialchars($edit_data['book_id']); ?></div>
        <?php endif; ?>

        <form method="POST" class="form-group">
            <?php if($edit_mode): ?>
                <input type="hidden" name="book_id" value="<?php echo htmlspecialchars($edit_data['book_id']); ?>">
            <?php endif; ?>
            
            <input type="text" name="title" placeholder="Book Title" value="<?php echo htmlspecialchars($edit_data['title']); ?>" required>
            <input type="text" name="author" placeholder="Author" value="<?php echo htmlspecialchars($edit_data['author']); ?>" required>
            <input type="text" name="category" placeholder="Category" value="<?php echo htmlspecialchars($edit_data['category']); ?>" required>
            
            <?php if($edit_mode): ?>
                <button type="submit" name="update_book" class="update-btn">Save Updates</button>
                <a href="library.php" class="btn-clear">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add_book">Save Book</button>
            <?php endif; ?>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Book ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // 4. READ Operation
                $result = $conn->query("SELECT * FROM books");
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['book_id']}</td>
                            <td>" . htmlspecialchars($row['title']) . "</td>
                            <td>" . htmlspecialchars($row['author']) . "</td>
                            <td>" . htmlspecialchars($row['category']) . "</td>
                            <td class='actions-cell'>
                                <a href='?edit={$row['book_id']}' class='action-link edit-link'>Edit</a>
                                <a href='?delete={$row['book_id']}' class='action-link delete-link' onclick=\"return confirm('Remove this book from inventory?');\">Delete</a>
                            </td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>