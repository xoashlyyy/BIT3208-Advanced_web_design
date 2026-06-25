<?php
session_start();
require 'includes/db.php';
require 'includes/auth.php';

require_role('super_admin');

// ── POST ACTIONS ─────────────────────────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $username = clean('username');
        $password = $_POST['password'] ?? '';
        $role     = in_array($_POST['role'] ?? '', array_keys($ROLE_LABELS)) ? $_POST['role'] : 'viewer';

        if ($username === '' || strlen($password) < 8) {
            $_SESSION['flash_error'] = "Username and a password of at least 8 characters are required.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt   = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashed, $role);
            if ($stmt->execute()) {
                $_SESSION['flash_success'] = "User '{$username}' created as " . ($ROLE_LABELS[$role] ?? $role) . ".";
            } else {
                $_SESSION['flash_error'] = "Failed to create user. Username may already exist.";
            }
        }

    } elseif ($action === 'edit_role') {
        $id      = intval($_POST['id'] ?? 0);
        $newRole = in_array($_POST['role'] ?? '', array_keys($ROLE_LABELS)) ? $_POST['role'] : 'viewer';

        if ($id === (int)$_SESSION['user_id'] && $newRole !== 'super_admin') {
            $_SESSION['flash_error'] = "You cannot demote your own Super Admin account.";
        } else {
            $stmt = $conn->prepare("UPDATE users SET role=? WHERE id=?");
            $stmt->bind_param("si", $newRole, $id);
            if ($stmt->execute()) {
                $_SESSION['flash_success'] = "Role updated successfully.";
            } else {
                $_SESSION['flash_error'] = "Failed to update role.";
            }
        }

    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id === (int)$_SESSION['user_id']) {
            $_SESSION['flash_error'] = "You cannot delete your own account.";
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $_SESSION['flash_success'] = "User deleted.";
            } else {
                $_SESSION['flash_error'] = "Failed to delete user.";
            }
        }

    } elseif ($action === 'reset_password') {
        $id          = intval($_POST['id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';
        if (strlen($newPassword) < 8) {
            $_SESSION['flash_error'] = "Password must be at least 8 characters.";
        } else {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt   = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->bind_param("si", $hashed, $id);
            if ($stmt->execute()) {
                $_SESSION['flash_success'] = "Password reset successfully.";
            } else {
                $_SESSION['flash_error'] = "Failed to reset password.";
            }
        }
    }

    header("Location: users.php");
    exit();
}

// ── FETCH USERS ───────────────────────────────────────────────────────────────
$users = $conn->query("SELECT id, username, role, created_at FROM users ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management | StockTrack</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.6);
                 backdrop-filter:blur(4px); z-index:2000; align-items:center; justify-content:center; }
        .modal.active { display:flex; }
        .modal-content { background:var(--bg-surface); padding:32px; border-radius:12px;
                         width:100%; max-width:420px; border:1px solid var(--border); position:relative; }
        .close-btn { position:absolute; top:16px; right:16px; background:none; border:none;
                     color:var(--text-muted); font-size:1.5rem; cursor:pointer; width:auto; padding:0; line-height:1; }
        .close-btn:hover { color:var(--text-main); background:none; }
        .btn-text { background:none; border:none; padding:0; color:var(--primary); font-size:0.875rem;
                    font-weight:500; cursor:pointer; margin-right:12px; display:inline; width:auto; }
        .btn-text:hover { text-decoration:underline; background:none; }
        .btn-text.delete { color:var(--danger); }
        .btn-text.reset  { color:#f59e0b; }
        select { width:100%; padding:12px 16px; margin-bottom:20px; background:var(--bg-base);
                 border:1px solid var(--border); color:var(--text-main); border-radius:8px; font-size:1rem; }
        select:focus { outline:none; border-color:var(--primary); }
    </style>
</head>
<body class="flex-row-layout">

<!-- SIDEBAR -->
<div class="sidebar">
    <h2 style="margin-bottom:40px; font-weight:700; color:#fff;">StockTrack.</h2>
    <h3>Warehouse</h3>
    <a href="dashboard.php">Inventory Overview</a>
    <h3 style="margin-top:24px;">Admin</h3>
    <a href="users.php" class="active">User Management</a>
    <h3 style="margin-top:24px;">Account</h3>
    <a href="login.php?logout=1" class="logout">Sign Out</a>
</div>

<!-- MAIN -->
<div class="main-content">
    <div style="margin-bottom:32px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 style="font-size:1.8rem; margin-bottom:4px;">User Management</h1>
            <p style="color:var(--text-muted); font-size:0.9rem; display:flex; align-items:center; gap:8px;">
                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                <?php echo role_badge($_SESSION['user_role']); ?>
            </p>
        </div>
        <button onclick="openModal('addUserModal')" style="width:auto; padding:10px 20px;">+ Add User</button>
    </div>

    <!-- Flash messages -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="msg" style="background:rgba(16,185,129,.1); color:#10b981; border:1px solid rgba(16,185,129,.2);">
            <?php echo htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="msg error">
            <?php echo htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>

    <div class="data-card">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td style="color:var(--text-muted); font-family:monospace;"><?php echo (int)$u['id']; ?></td>
                <td style="font-weight:500;">
                    <?php echo htmlspecialchars($u['username']); ?>
                    <?php if ((int)$u['id'] === (int)$_SESSION['user_id']): ?>
                        <span style="color:var(--text-muted); font-size:0.75rem;"> (you)</span>
                    <?php endif; ?>
                </td>
                <td><?php echo role_badge($u['role']); ?></td>
                <td style="color:var(--text-muted); font-size:0.85rem;">
                    <?php echo date('M j, Y', strtotime($u['created_at'])); ?>
                </td>
                <td class="action-links">
                    <button class="btn-text"
                            onclick="openRoleModal(<?php echo (int)$u['id']; ?>, <?php echo json_encode($u['username']); ?>, <?php echo json_encode($u['role']); ?>)">
                        Change Role
                    </button>
                    <button class="btn-text reset"
                            onclick="openResetModal(<?php echo (int)$u['id']; ?>, <?php echo json_encode($u['username']); ?>)">
                        Reset Password
                    </button>
                    <?php if ((int)$u['id'] !== (int)$_SESSION['user_id']): ?>
                    <form method="POST" style="display:inline;"
                          onsubmit="return confirm('Delete user <?php echo htmlspecialchars(addslashes($u['username'])); ?>? This cannot be undone.');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
                        <button type="submit" class="btn-text delete">Delete</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ADD USER MODAL -->
<div id="addUserModal" class="modal">
    <div class="modal-content">
        <button class="close-btn" onclick="closeModal('addUserModal')">&times;</button>
        <h2 style="margin-bottom:20px;">Add New User</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <label>Username</label>
            <input type="text" name="username" required autofocus>
            <label>Password <small style="color:var(--text-muted);">(min 8 chars)</small></label>
            <input type="password" name="password" required minlength="8">
            <label>Role</label>
            <select name="role">
                <?php foreach ($ROLE_LABELS as $key => $label): ?>
                <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($label); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Create User</button>
        </form>
    </div>
</div>

<!-- CHANGE ROLE MODAL -->
<div id="roleModal" class="modal">
    <div class="modal-content">
        <button class="close-btn" onclick="closeModal('roleModal')">&times;</button>
        <h2 style="margin-bottom:8px;">Change Role</h2>
        <p id="roleModalSubtitle" style="color:var(--text-muted); font-size:0.875rem; margin-bottom:20px;"></p>
        <form method="POST" action="">
            <input type="hidden" name="action" value="edit_role">
            <input type="hidden" name="id" id="role_id">
            <label>New Role</label>
            <select name="role" id="role_select">
                <?php foreach ($ROLE_LABELS as $key => $label): ?>
                <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($label); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Update Role</button>
        </form>
    </div>
</div>

<!-- RESET PASSWORD MODAL -->
<div id="resetModal" class="modal">
    <div class="modal-content">
        <button class="close-btn" onclick="closeModal('resetModal')">&times;</button>
        <h2 style="margin-bottom:8px;">Reset Password</h2>
        <p id="resetModalSubtitle" style="color:var(--text-muted); font-size:0.875rem; margin-bottom:20px;"></p>
        <form method="POST" action="">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="id" id="reset_id">
            <label>New Password <small style="color:var(--text-muted);">(min 8 chars)</small></label>
            <input type="password" name="new_password" required minlength="8">
            <button type="submit">Reset Password</button>
        </form>
    </div>
</div>

<script>
function openModal(id)  { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

// Close on backdrop click
document.querySelectorAll('.modal').forEach(function(m) {
    m.addEventListener('click', function(e) { if (e.target === m) closeModal(m.id); });
});

function openRoleModal(id, username, currentRole) {
    document.getElementById('role_id').value               = id;
    document.getElementById('roleModalSubtitle').textContent = 'Updating role for: ' + username;
    document.getElementById('role_select').value           = currentRole;
    openModal('roleModal');
}

function openResetModal(id, username) {
    document.getElementById('reset_id').value                = id;
    document.getElementById('resetModalSubtitle').textContent = 'Setting new password for: ' + username;
    openModal('resetModal');
}
</script>
</body>
</html>