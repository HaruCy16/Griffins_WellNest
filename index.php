<?php
/**
 * Landing Page
 * Wellnest Mental Health Web Application
 */

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/src/functions.php';

startSession();

// If logged in, redirect to dashboard
if (isLoggedIn()) {
    $role = getCurrentUserRole();
    switch ($role) {
        case ROLE_STUDENT:
            redirect('/Wellnest_Sim_Web_Application/views/student/dashboard.php');
            break;
        case ROLE_COUNSELOR:
            redirect('/Wellnest_Sim_Web_Application/views/counselor/dashboard.php');
            break;
        case ROLE_ADMIN:
            redirect('/Wellnest_Sim_Web_Application/views/admin/dashboard.php');
            break;
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e(APP_NAME) ?> - Mental Health Support</title>
  <link href="./css/output.css" rel="stylesheet">
</head>
<body class="bg-cool-white m-0 p-0 min-h-screen">
  <div class="flex flex-col items-center justify-center min-h-screen px-4">
    <!-- Logo/Brand -->
    <div class="mb-8 text-center">
      <img src="assets/logo_griffin.png" alt="Griffin's Wellnest Logo" class="w-24 h-24 mx-auto mb-4 object-contain">
      <h1 class="text-4xl font-bold text-bronze mb-2"><?= e(APP_NAME) ?></h1>
      <p class="text-lg text-body-gray">Mental Health Support System</p>
    </div>
    
    <!-- Main Card -->
    <div class="bg-white rounded-2xl shadow-xl p-8 max-w-md w-full text-center">
      <h2 class="text-2xl font-semibold text-bronze mb-4">Welcome, Student!</h2>
      <p class="text-body-gray mb-8">
        Take assessments, get personalized recommendations, and track your mental wellness journey.
      </p>
      
      <div class="space-y-4">
        <a href="views/login.php" class="block w-full px-6 py-3 bg-golden text-bronze-800 rounded-lg font-semibold hover:bg-golden-400 transition duration-300">
          Log In
        </a>
        <a href="views/register.php" class="block w-full px-6 py-3 border-2 border-electric-blue text-electric-blue rounded-lg font-semibold hover:bg-electric-blue-50 transition duration-300">
          Create Account 
        </a>
      </div>
    </div>
    
    <!-- Features -->
    <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl">
      <div class="bg-white/80 backdrop-blur rounded-xl p-6 text-center">
        <div class="w-12 h-12 bg-golden-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-6 h-6 text-golden-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <h3 class="font-semibold text-bronze mb-2">Self-Assessment</h3>
        <p class="text-sm text-body-gray">Take quick assessments to understand your mental wellness.</p>
      </div>
      
      <div class="bg-white/80 backdrop-blur rounded-xl p-6 text-center">
        <div class="w-12 h-12 bg-electric-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-6 h-6 text-electric-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
          </svg>
        </div>
        <h3 class="font-semibold text-bronze mb-2">AI Recommendations</h3>
        <p class="text-sm text-body-gray">Get personalized activities and resources based on your needs.</p>
      </div>
      
      <div class="bg-white/80 backdrop-blur rounded-xl p-6 text-center">
        <div class="w-12 h-12 bg-bronze-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-6 h-6 text-bronze" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
          </svg>
        </div>
        <h3 class="font-semibold text-bronze mb-2">Counselor Support</h3>
        <p class="text-sm text-body-gray">Connect with school counselors for professional guidance.</p>
      </div>
    </div>
    
    <!-- Footer -->
    <p class="mt-12 text-body-gray text-sm">
      &copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. Supporting student mental health.
    </p>
  </div>
</body>
</html>