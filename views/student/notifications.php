<?php
/**
 * Notifications Page
 * Wellnest Mental Health Web Application
 * 
 * Features:
 * - View all notifications
 * - Filter by status (read/unread)
 * - Mark as read/unread
 * - Delete notifications
 */

$pageTitle = 'Notifications';

require_once __DIR__ . '/../../config/settings.php';
require_once __DIR__ . '/../../src/functions.php';

startSession();
requireRole(ROLE_STUDENT);

$user = getCurrentUser();
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

try {
    if ($filter === 'unread') {
        $notifications = Database::fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? AND is_read = FALSE ORDER BY created_at DESC",
            [$user['user_id']]
        );
    } else {
        $notifications = Database::fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50",
            [$user['user_id']]
        );
    }
    
    $unreadCount = Database::fetchOne(
        "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE",
        [$user['user_id']]
    );
    
} catch (Exception $e) {
    logError('Notifications Error', ['error' => $e->getMessage()]);
    $notifications = [];
    $unreadCount = ['count' => 0];
}

// Handle AJAX requests
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    
    if ($_GET['ajax'] === 'mark_read' && isset($_GET['notification_id'])) {
        $notificationId = intval($_GET['notification_id']);
        try {
            Database::query(
                "UPDATE notifications SET is_read = TRUE, read_at = NOW() WHERE notification_id = ? AND user_id = ?",
                [$notificationId, $user['user_id']]
            );
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } elseif ($_GET['ajax'] === 'delete' && isset($_GET['notification_id'])) {
        $notificationId = intval($_GET['notification_id']);
        try {
            Database::query(
                "DELETE FROM notifications WHERE notification_id = ? AND user_id = ?",
                [$notificationId, $user['user_id']]
            );
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    exit;
}

include __DIR__ . '/../layouts/header.php';
?>

<style>
    .notification-card {
        border-left: 4px solid #10b981;
        transition: all 0.3s;
    }
    .notification-card.unread {
        background: #f0fdf4;
        border-left-color: #06b6d4;
    }
    .notification-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .notification-icon {
        font-size: 24px;
        margin-right: 12px;
    }
    .notification-badge {
        background: #ef4444;
        color: white;
        border-radius: 50%;
        padding: 2px 8px;
        font-size: 12px;
        font-weight: bold;
    }
    .filter-btn {
        padding: 8px 16px;
        border-radius: 6px;
        border: 1px solid #ddd;
        background: white;
        cursor: pointer;
        transition: all 0.3s;
    }
    .filter-btn.active {
        background: #10b981;
        color: white;
        border-color: #10b981;
    }
    .card-hover {
        transition: all 0.3s;
    }
    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
</style>

<div class="container mx-auto px-4 py-8">
    
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold text-bronze mb-2">🔔 Notifications</h1>
                <p class="text-gray-600">Stay updated on your wellness journey</p>
            </div>
            <?php if ($unreadCount['count'] > 0): ?>
            <div class="notification-badge" style="font-size: 16px; padding: 8px 12px;">
                <?= $unreadCount['count'] ?> <?= $unreadCount['count'] == 1 ? 'unread notification' : 'unread notifications' ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Filter Buttons -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-8">
        <p class="text-sm font-semibold text-gray-600 mb-4">FILTER</p>
        <div class="flex gap-3 flex-wrap">
            <a href="?filter=all" class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>">
                📋 All Notifications (<?= count($notifications) ?>)
            </a>
            <button onclick="filterNotifications('unread')" class="filter-btn <?= $filter === 'unread' ? 'active' : '' ?>">
                🔵 Unread (<?= $unreadCount['count'] ?>)
            </button>
            <button onclick="clearAllNotifications()" class="filter-btn" style="margin-left: auto;">
                🗑️ Clear All
            </button>
        </div>
    </div>
    
    <!-- Notifications List -->
    <div class="space-y-4 p-12">
        <?php if (empty($notifications)): ?>
        
            <div class="bg-white rounded-xl shadow-md p-12 text-center">
                <p style="font-size: 48px; margin-bottom: 12px;">📭</p>
                <h2 class="text-2xl font-bold text-gray-700 mb-2">No notifications</h2>
                <p class="text-gray-600 mb-6">You're all caught up! Check back for updates on your assessments and recommendations.</p>
            </div>
        
        <?php else: ?>
        
            <?php foreach ($notifications as $notif): ?> 
            <div class="notification-card bg-white rounded-xl shadow-md p-6 <?= !$notif['is_read'] ? 'unread' : '' ?>" id="notif-<?= $notif['notification_id'] ?>">
                <div class="flex items-start justify-between">
                    <div class="flex items-start flex-1">
                        <!-- Icon based on type -->
                        <span class="notification-icon">
                            <?php 
                            $icons = [
                                'assessment_due' => '📋',
                                'recommendation' => '💡',
                                'reminder' => '🔔',
                                'alert' => '⚠️',
                                'counselor_message' => '💬'
                            ];
                            echo $icons[$notif['notification_type']] ?? '📢';
                            ?>
                        </span>
                        
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-1">
                                <h3 class="font-bold text-gray-800">
                                    <?= e($notif['title']) ?>
                                </h3>
                                <?php if (!$notif['is_read']): ?>
                                <span style="display: inline-block; width: 8px; height: 8px; background: #06b6d4; border-radius: 50%;"></span>
                                <?php endif; ?>
                            </div>
                            <p class="text-gray-600 text-sm mb-2">
                                <?= e($notif['message']) ?>
                            </p>
                            <p class="text-gray-500 text-xs">
                                <?= timeAgo($notif['created_at']) ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex gap-2 ml-4">
                        <?php if (!$notif['is_read']): ?>
                        <button onclick="markAsRead(<?= $notif['notification_id'] ?>)" 
                                class="px-3 py-1 text-xs bg-cyan-100 text-cyan-700 rounded-lg hover:bg-cyan-200 transition"
                                title="Mark as read">
                            ✓
                        </button>
                        <?php endif; ?>
                        <button onclick="deleteNotification(<?= $notif['notification_id'] ?>)" 
                                class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition"
                                title="Delete">
                            ✕
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        
        <?php endif; ?>
    </div>
    
    <!-- Quick Links -->
    <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="/Wellnest_Sim_Web_Application/views/student/dashboard.php" class="bg-white rounded-xl shadow-md p-6 border-l-4 border-golden card-hover">
            <p style="font-size: 28px; margin-bottom: 8px;">📊</p>
            <h3 class="font-bold mb-1 text-bronze">Dashboard</h3>
            <p class="text-sm text-gray-600">View your wellness overview</p>
        </a>
        <a href="/Wellnest_Sim_Web_Application/views/student/games/frogger.php" class="bg-white rounded-xl shadow-md p-6 border-l-4 border-golden card-hover">
            <p style="font-size: 28px; margin-bottom: 8px;">🎮</p>
            <h3 class="font-bold mb-1 text-bronze">Play Game</h3>
            <p class="text-sm text-gray-600">Try the Frogger wellness game</p>
        </a>
        <a href="/Wellnest_Sim_Web_Application/views/student/profile.php" class="bg-white rounded-xl shadow-md p-6 border-l-4 border-golden card-hover">
            <p style="font-size: 28px; margin-bottom: 8px;">👤</p>
            <h3 class="font-bold mb-1 text-bronze">My Profile</h3>
            <p class="text-sm text-gray-600">View your assessment history</p>
        </a>
    </div>
    
</div>

<script>
function markAsRead(notificationId) {
    fetch(`?ajax=mark_read&notification_id=${notificationId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const card = document.getElementById(`notif-${notificationId}`);
                card.classList.remove('unread');
                card.querySelector('button').closest('.flex').remove();
                location.reload();
            }
        });
}

function deleteNotification(notificationId) {
    showConfirm('Are you sure you want to delete this notification?', function() {
        fetch(`?ajax=delete&notification_id=${notificationId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const card = document.getElementById(`notif-${notificationId}`);
                    card.style.animation = 'fadeOut 0.3s';
                    setTimeout(() => {
                        card.remove();
                        // Show empty state if no notifications left
                        const notificationsList = document.querySelector('.space-y-4');
                        if (!notificationsList.querySelector('.notification-card')) {
                            location.reload();
                        }
                    }, 300);
                }
            });
    });
}

function filterNotifications(type) {
    window.location.href = `?filter=${type}`;
}

function clearAllNotifications() {
    showConfirm('Clear all notifications? This cannot be undone.', function() {
        document.querySelectorAll('.notification-card').forEach(card => {
            const id = card.id.split('-')[1];
            fetch(`?ajax=delete&notification_id=${id}&bulk=true`)
                .then(res => res.json())
                .then(() => {
                    card.style.animation = 'fadeOut 0.3s';
                    setTimeout(() => card.remove(), 300);
                });
        });
        setTimeout(() => location.reload(), 500);
    });
}

// Add fade animation
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeOut {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(-20px); }
    }
`;
document.head.appendChild(style);
</script>

<?php include __DIR__ . '/../layouts/modal.php'; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
