<?php
    // admin/login.php
    // Admin login page

    require_once '../dbs/auth/auth.php';

    // If already logged in, redirect to admin panel
    if (isset($auth) && $auth->isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Shree Balalji BioFuels</title>
    <link rel="icon" type="image/png" href="../logo.png">
    <link rel="shortcut icon" href="../logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'jakarta': ['Plus Jakarta Sans', 'sans-serif']
                    },
                    colors: {
                        primary: '#10b981',
                        accent: '#059669'
                    }
                }
            }
        }
    </script>
</head>
<body class="font-jakarta antialiased bg-gradient-to-br from-primary/10 to-accent/10 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-md">
        <div class="bg-white/80 backdrop-blur-lg shadow-xl rounded-2xl p-8 border border-white/20">
            <!-- Logo and Title -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-primary rounded-xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <img src="../logo.png" alt="Logo" class="w-10 h-10">
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Admin Login</h1>
                <p class="text-gray-600">Sign in to access the admin panel</p>
            </div>

            <!-- Error Message -->
            <div id="errorMessage" class="mb-6 hidden">
                <div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm font-medium">
                    <span id="errorText"></span>
                </div>
            </div>

            <!-- Login Form -->
            <form id="loginForm" class="space-y-6">
                <div>
                    <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required
                           class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors"
                           placeholder="Enter your phone number">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors"
                           placeholder="Enter your password">
                </div>

                <button type="submit" id="loginBtn"
                        class="w-full bg-primary hover:bg-accent text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200 focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    Sign In
                </button>
            </form>

            <!-- Footer -->
            <div class="text-center mt-6">
                <p class="text-sm text-gray-500">
                    © 2024 Shree Balalji BioFuels. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    <script>
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        const errorMessage = document.getElementById('errorMessage');
        const errorText = document.getElementById('errorText');

        function showError(message) {
            errorText.textContent = message;
            errorMessage.classList.remove('hidden');
        }

        function hideError() {
            errorMessage.classList.add('hidden');
        }

        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            hideError();

            const formData = new FormData(loginForm);

            loginBtn.textContent = 'Signing In...';
            loginBtn.disabled = true;

            try {
                const response = await fetch('../dbs/auth/login_process.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    window.location.href = 'index.php';
                } else {
                    showError(result.message || 'Login failed. Please check your credentials.');
                }
            } catch (error) {
                showError('An error occurred. Please try again.');
            } finally {
                loginBtn.textContent = 'Sign In';
                loginBtn.disabled = false;
            }
        });

        // Auto-focus phone field
        document.getElementById('phone').focus();
    </script>
</body>
</html>
