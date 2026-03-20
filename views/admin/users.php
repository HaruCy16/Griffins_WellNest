<?php
/**
 * Admin - Users Management
 * Wellnest Mental Health Web Application
 * 
 * Features:
 * - View all users (students, counselors, admins)
 * - Add new counselors and admins
 * - Edit user details
 * - Activate/deactivate users
 * - Soft delete users
 */

$pageTitle = 'User Management';

require_once __DIR__ . '/../../config/settings.php';
require_once __DIR__ . '/../../src/functions.php';

startSession();
requireRole(ROLE_ADMIN);

$user = getCurrentUser();
$action = $_GET['action'] ?? null;
$userId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Track form submission state for error display
$showAddModal = false;
$showErrors = false;
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? null;
    
    if ($postAction === 'add_user') {
        // Add new user (counselor or admin)
        $errors = [];
        $showAddModal = true;
        
        $email = trim($_POST['email'] ?? '');
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $roleId = (int)($_POST['role_id'] ?? 0);
        
        // Validation
        if (empty($email)) $errors['email'] = 'Email is required';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email format';
        else {
            $existing = Database::fetchOne("SELECT user_id FROM users WHERE email = ? AND deleted_at IS NULL", [$email]);
            if ($existing) $errors['email'] = 'Email already in use';
        }
        
        if (empty($firstName)) $errors['first_name'] = 'First name is required';
        if (empty($lastName)) $errors['last_name'] = 'Last name is required';
        if (empty($password) || strlen($password) < 6) $errors['password'] = 'Password must be at least 6 characters';
        if (!in_array($roleId, [ROLE_COUNSELOR, ROLE_ADMIN])) $errors['role_id'] = 'Invalid role selected';
        
        if (empty($errors)) {
            try {
                require_once __DIR__ . '/../../src/validators.php';
                $passwordHash = hashPassword($password);
                
                Database::insert(
                    "INSERT INTO users (email, password_hash, first_name, last_name, role_id, is_active) 
                     VALUES (?, ?, ?, ?, ?, TRUE)",
                    [$email, $passwordHash, $firstName, $lastName, $roleId]
                );
                
                redirectWithMessage('/Wellnest_Sim_Web_Application/views/admin/users.php', 'User created successfully!', 'success');
            } catch (Exception $e) {
                $errors['general'] = 'Error creating user: ' . $e->getMessage();
            }
        }
    } elseif ($postAction === 'edit_user' && $userId) {
        // Edit user
        $errors = [];
        
        $email = trim($_POST['email'] ?? '');
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        // Validation
        if (empty($email)) $errors['email'] = 'Email is required';
        if (empty($firstName)) $errors['first_name'] = 'First name is required';
        if (empty($lastName)) $errors['last_name'] = 'Last name is required';
        
        // Check email uniqueness
        if (!empty($email)) {
            $existing = Database::fetchOne(
                "SELECT user_id FROM users WHERE email = ? AND user_id != ? AND deleted_at IS NULL", 
                [$email, $userId]
            );
            if ($existing) $errors['email'] = 'Email already in use';
        }
        
        if (empty($errors)) {
            try {
                Database::query(
                    "UPDATE users SET email = ?, first_name = ?, last_name = ?, is_active = ? WHERE user_id = ?",
                    [$email, $firstName, $lastName, $isActive, $userId]
                );
                
                redirectWithMessage('/Wellnest_Sim_Web_Application/views/admin/users.php', 'User updated successfully!', 'success');
            } catch (Exception $e) {
                $errors['general'] = 'Error updating user: ' . $e->getMessage();
            }
        }
    } elseif ($postAction === 'delete_user' && $userId) {
        // Soft delete user
        try {
            Database::query(
                "UPDATE users SET deleted_at = CURRENT_TIMESTAMP WHERE user_id = ? AND role_id IN (?, ?)",
                [$userId, ROLE_COUNSELOR, ROLE_ADMIN]
            );
            
            redirectWithMessage('/Wellnest_Sim_Web_Application/views/admin/users.php', 'User deleted successfully!', 'success');
        } catch (Exception $e) {
            redirectWithMessage('/Wellnest_Sim_Web_Application/views/admin/users.php', 'Error deleting user: ' . $e->getMessage(), 'error');
        }
    } elseif ($postAction === 'reset_password' && $userId) {
        // Reset password
        $newPassword = $_POST['new_password'] ?? '';
        
        if (empty($newPassword) || strlen($newPassword) < 6) {
            redirectWithMessage('/Wellnest_Sim_Web_Application/views/admin/users.php?action=edit&id=' . $userId, 'Password must be at least 6 characters', 'error');
        } else {
            try {
                require_once __DIR__ . '/../../src/validators.php';
                $passwordHash = hashPassword($newPassword);
                
                Database::query(
                    "UPDATE users SET password_hash = ? WHERE user_id = ?",
                    [$passwordHash, $userId]
                );
                
                redirectWithMessage('/Wellnest_Sim_Web_Application/views/admin/users.php?action=edit&id=' . $userId, 'Password reset successfully!', 'success');
            } catch (Exception $e) {
                redirectWithMessage('/Wellnest_Sim_Web_Application/views/admin/users.php?action=edit&id=' . $userId, 'Error resetting password: ' . $e->getMessage(), 'error');
            }
        }
    }
}

// Fetch users
try {
    $roleFilter = $_GET['role'] ?? 'all';
    
    if ($roleFilter === 'all') {
        $users = Database::fetchAll(
            "SELECT u.*, r.role_name FROM users u 
             JOIN roles r ON u.role_id = r.role_id 
             WHERE u.role_id IN (?, ?) AND u.deleted_at IS NULL
             ORDER BY r.role_name, u.first_name",
            [ROLE_COUNSELOR, ROLE_ADMIN]
        );
    } else {
        $roleId = $roleFilter === 'counselor' ? ROLE_COUNSELOR : ($roleFilter === 'admin' ? ROLE_ADMIN : null);
        if ($roleId) {
            $users = Database::fetchAll(
                "SELECT u.*, r.role_name FROM users u 
                 JOIN roles r ON u.role_id = r.role_id 
                 WHERE u.role_id = ? AND u.deleted_at IS NULL
                 ORDER BY u.first_name",
                [$roleId]
            );
        } else {
            $users = [];
        }
    }
    
    $editUser = null;
    if ($action === 'edit' && $userId) {
        $editUser = Database::fetchOne(
            "SELECT u.*, r.role_name FROM users u 
             JOIN roles r ON u.role_id = r.role_id 
             WHERE u.user_id = ? AND u.deleted_at IS NULL",
            [$userId]
        );
    }
} catch (Exception $e) {
    $users = [];
    $editUser = null;
}

// Include header
include __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-bronze">User Management</h1>
            <p class="text-body-gray mt-2">Add, edit, and manage counselors and administrators.</p>
        </div>
        <button onclick="document.getElementById('addUserModal').classList.remove('hidden')" class="bg-bronze text-white px-6 py-2 rounded-lg hover:bg-bronze-700">
            + Add User
        </button>
    </div>

    <!-- Filter Tabs -->
    <div class="flex gap-4 mb-6">
        <a href="?role=all" class="px-4 py-2 rounded-lg <?= ($roleFilter === 'all') ? 'bg-golden text-bronze font-semibold' : 'bg-white border border-gray-200 text-body-gray hover:bg-gray-50' ?>">
            All Users
        </a>
        <a href="?role=counselor" class="px-4 py-2 rounded-lg <?= ($roleFilter === 'counselor') ? 'bg-golden text-bronze font-semibold' : 'bg-white border border-gray-200 text-body-gray hover:bg-gray-50' ?>">
            Counselors
        </a>
        <a href="?role=admin" class="px-4 py-2 rounded-lg <?= ($roleFilter === 'admin') ? 'bg-golden text-bronze font-semibold' : 'bg-white border border-gray-200 text-body-gray hover:bg-gray-50' ?>">
            Administrators
        </a>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <?php if (empty($users)): ?>
            <div class="p-8 text-center">
                <p class="text-body-gray text-lg">No users found.</p>
            </div>
        <?php else: ?>
            <table class="w-full">
                <thead class="bg-cool-white border-b">
                    <tr>
                        <th class="text-left px-6 py-3 text-sm font-semibold text-bronze">Name</th>
                        <th class="text-left px-6 py-3 text-sm font-semibold text-bronze">Email</th>
                        <th class="text-left px-6 py-3 text-sm font-semibold text-bronze">Role</th>
                        <th class="text-left px-6 py-3 text-sm font-semibold text-bronze">Status</th>
                        <th class="text-left px-6 py-3 text-sm font-semibold text-bronze">Last Login</th>
                        <th class="text-left px-6 py-3 text-sm font-semibold text-bronze">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($users as $u): ?>
                        <tr class="hover:bg-cool-white transition">
                            <td class="px-6 py-4">
                                <span class="font-semibold text-bronze"><?= e($u['first_name'] . ' ' . $u['last_name']) ?></span>
                            </td>
                            <td class="px-6 py-4 text-body-gray text-sm"><?= e($u['email']) ?></td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-sm font-semibold <?= $u['role_name'] === 'counselor' ? 'bg-green-100 text-green-700' : 'bg-bronze-100 text-bronze-700' ?>">
                                    <?= ucfirst($u['role_name']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-sm font-semibold <?= $u['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                                    <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-body-gray text-sm">
                                <?= $u['last_login'] ? timeAgo($u['last_login']) : 'Never' ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    <a href="?action=edit&id=<?= $u['user_id'] ?>" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-bronze hover:bg-bronze-700 text-white text-xs font-semibold transition">
                                        <span>✏️</span> Edit
                                    </a>
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="id" value="<?= $u['user_id'] ?>">
                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-red-500 hover:bg-red-600 text-white text-xs font-semibold transition">
                                            <span>🗑️</span> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="<?= $showAddModal ? 'flex' : 'hidden' ?> fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg p-8 max-w-md w-full mx-4">
        <h2 class="text-2xl font-bold text-bronze mb-6">Add New User</h2>
        
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                <?php foreach ($errors as $error): ?>
                    <p class="text-sm"><?= e($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="action" value="add_user">
            
            <div class="mb-4">
                <label class="block text-sm font-semibold text-bronze mb-1">First Name</label>
                <input type="text" name="first_name" value="<?= e($firstName ?? '') ?>" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-golden">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-semibold text-bronze mb-1">Last Name</label>
                <input type="text" name="last_name" value="<?= e($lastName ?? '') ?>" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-golden">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-semibold text-bronze mb-1">Email</label>
                <input type="email" name="email" value="<?= e($email ?? '') ?>" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-golden">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-semibold text-bronze mb-1">Password</label>
                <input type="password" name="password" required minlength="6" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-golden">
                <p class="text-xs text-gray-500 mt-1">Minimum 6 characters</p>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-semibold text-bronze mb-1">Role</label>
                <select name="role_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-golden">
                    <option value="">-- Select Role --</option>
                    <option value="<?= ROLE_COUNSELOR ?>" <?= ($roleId ?? 0) == ROLE_COUNSELOR ? 'selected' : '' ?>>Counselor</option>
                    <option value="<?= ROLE_ADMIN ?>" <?= ($roleId ?? 0) == ROLE_ADMIN ? 'selected' : '' ?>>Administrator</option>
                </select>
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-bronze text-white px-4 py-2 rounded-lg hover:bg-bronze-700 font-semibold">
                    Create User
                </button>
                <button type="button" onclick="document.getElementById('addUserModal').classList.add('hidden'); document.getElementById('addUserModal').classList.remove('flex')" class="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<?php if ($editUser): ?>
<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg p-8 max-w-md w-full mx-4">
        <h2 class="text-2xl font-bold text-bronze mb-6">Edit User</h2>
        
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                <?php foreach ($errors as $error): ?>
                    <p class="text-sm"><?= e($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="mb-6 pb-6 border-b">
            <form method="POST">
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" name="id" value="<?= $editUser['user_id'] ?>">
                
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-bronze mb-1">First Name</label>
                    <input type="text" name="first_name" value="<?= e($editUser['first_name']) ?>" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-golden">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-bronze mb-1">Last Name</label>
                    <input type="text" name="last_name" value="<?= e($editUser['last_name']) ?>" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-golden">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-bronze mb-1">Email</label>
                    <input type="email" name="email" value="<?= e($editUser['email']) ?>" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-golden">
                </div>
                
                <div class="mb-6">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" <?= $editUser['is_active'] ? 'checked' : '' ?> class="w-4 h-4">
                        <span class="text-sm font-semibold text-bronze">Active</span>
                    </label>
                </div>
                
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-bronze text-white px-4 py-2 rounded-lg hover:bg-bronze-700 font-semibold">
                        Update User
                    </button>
                    <a href="?role=<?= $roleFilter ?>" class="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 font-semibold text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Reset Password Section -->
        <div>
            <h3 class="font-semibold text-bronze mb-3">Reset Password</h3>
            <form method="POST">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="id" value="<?= $editUser['user_id'] ?>">
                
                <div class="mb-3">
                    <label class="block text-sm font-semibold text-bronze mb-1">New Password</label>
                    <input type="password" name="new_password" minlength="6" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-golden">
                    <p class="text-xs text-gray-500 mt-1">Minimum 6 characters</p>
                </div>
                
                <button type="submit" class="w-full bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 font-semibold">
                    Reset Password
                </button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
