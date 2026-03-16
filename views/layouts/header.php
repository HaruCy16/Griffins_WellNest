<?php
/**
 * Base Layout Header
 * Include this at the top of all pages
 * 
 * Variables to set before including:
 * - $pageTitle (string) - Page title
 * - $bodyClass (string, optional) - Additional body classes
 */

require_once __DIR__ . '/../../config/settings.php';
require_once __DIR__ . '/../../src/functions.php';

startSession();

// Get current user if logged in
$currentUser = getCurrentUser();
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Dashboard') ?> - <?= e(APP_NAME) ?></title>
    <link href="<?= APP_URL ?>/css/output.css" rel="stylesheet">
</head>
<body class="bg-cool-white min-h-screen flex flex-col <?= e($bodyClass ?? '') ?>">
    
    <!-- Navigation -->
    <nav class="bg-bronze text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="<?= APP_URL ?>" class="text-xl font-bold">
                        <?= e(APP_NAME) ?>
                    </a>
                </div>
                
                <!-- Navigation Links -->
                <?php if ($currentUser): ?>
                <div class="hidden md:flex items-center space-x-4">
                    <?php if (isStudent()): ?>
                        <a href="<?= APP_URL ?>/views/student/dashboard.php" class="px-3 py-2 rounded-md hover:bg-bronze-600 transition">Dashboard</a>
                        <a href="<?= APP_URL ?>/views/student/dashboard.php#assessments" class="px-3 py-2 rounded-md hover:bg-bronze-600 transition">Assessments</a>
                        <a href="<?= APP_URL ?>/views/student/dashboard.php#recommendations" class="px-3 py-2 rounded-md hover:bg-bronze-600 transition">Recommendations</a>
                    <?php elseif (isCounselor()): ?>
                        <a href="<?= APP_URL ?>/views/counselor/dashboard.php" class="px-3 py-2 rounded-md hover:bg-bronze-600 transition">Dashboard</a>
                        <a href="<?= APP_URL ?>/views/counselor/dashboard.php#attention" class="px-3 py-2 rounded-md hover:bg-bronze-600 transition">Students</a>
                        <a href="<?= APP_URL ?>/views/counselor/dashboard.php#recent-assessments" class="px-3 py-2 rounded-md hover:bg-bronze-600 transition">Reports</a>
                    <?php elseif (isAdmin()): ?>
                        <a href="<?= APP_URL ?>/views/admin/dashboard.php" class="px-3 py-2 rounded-md hover:bg-bronze-600 transition">Dashboard</a>
                        <a href="<?= APP_URL ?>/views/admin/dashboard.php#recent-activity" class="px-3 py-2 rounded-md hover:bg-bronze-600 transition">Users</a>
                        <a href="<?= APP_URL ?>/views/admin/dashboard.php#recent-activity" class="px-3 py-2 rounded-md hover:bg-bronze-600 transition">Settings</a>
                    <?php endif; ?>
                </div>
                
                <!-- User Menu -->
                <div class="flex items-center space-x-4">
                    <div class="hidden md:block">
                        <span class="text-bronze-100 text-sm">Hello,</span>
                        <span class="font-semibold"><?= e($currentUser['first_name']) ?></span>
                    </div>
                    <a 
                        href="<?= APP_URL ?>/src/auth.php?action=logout" 
                        class="bg-golden text-bronze-800 px-4 py-2 rounded-lg hover:bg-golden-400 transition text-sm font-semibold"
                    >
                        Logout
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <!-- Flash Message -->
    <?php if ($flash): ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="p-4 rounded-lg <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700 border border-green-200' : ($flash['type'] === 'error' ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-blue-100 text-blue-700 border border-blue-200') ?>">
            <?= e($flash['message']) ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1">
