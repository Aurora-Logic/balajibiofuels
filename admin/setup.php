<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Admin - Shree Balalji BioFuels</title>
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
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Setup Admin Account</h1>
                <p class="text-gray-600">Create the first admin user for your system</p>
            </div>

            <!-- Error/Success Messages -->
            <div id="message" class="mb-6 hidden">
                <div id="messageContent" class="px-4 py-3 rounded-lg text-sm font-medium"></div>
            </div>

            <!-- Setup Form -->
            <form id="setupForm" class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Full Name</label>
                    <input type="text" id="name" name="name" required 
                           class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors"
                           placeholder="Enter your full name">
                </div>

                <div>
                    <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required minlength="10" maxlength="10"
                           class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors"
                           placeholder="Enter phone number" inputmode="numeric" pattern="[0-9]*">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                    <input type="password" id="password" name="password" required minlength="6"
                           class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors"
                           placeholder="Enter password (min 6 characters)">
                </div>

                <div>
                    <label for="confirmPassword" class="block text-sm font-semibold text-gray-700 mb-2">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required minlength="6"
                           class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors"
                           placeholder="Confirm your password">
                </div>

                <button type="submit" id="setupBtn" 
                        class="w-full bg-primary hover:bg-accent text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200 focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    Create Admin Account
                </button>
            </form>
        </div>
    </div>

    <script>
        const setupForm = document.getElementById('setupForm');
        const setupBtn = document.getElementById('setupBtn');
        const messageDiv = document.getElementById('message');
        const messageContent = document.getElementById('messageContent');

        function showMessage(message, type = 'error') {
            messageContent.textContent = message;
            messageContent.className = `px-4 py-3 rounded-lg text-sm font-medium ${type === 'error' ? 'bg-red-100 text-red-800 border border-red-200' : 'bg-green-100 text-green-800 border border-green-200'}`;
            messageDiv.classList.remove('hidden');
        }

        function hideMessage() {
            messageDiv.classList.add('hidden');
        }

        setupForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            hideMessage();

            const formData = new FormData(setupForm);
            const password = formData.get('password');
            const confirmPassword = formData.get('confirmPassword');
            const phone = formData.get('phone');
            // Phone number regex: 10-15 digits, optional + at start
            const phoneRegex = /^\d{10}$/;
            if (!phoneRegex.test(phone)) {
                showMessage('Please enter a valid 10-digit phone number!', 'error');
                return;
            }

            // Validate passwords match
            if (password !== confirmPassword) {
                showMessage('Passwords do not match!', 'error');
                return;
            }

            // Validate password length
            if (password.length < 6) {
                showMessage('Password must be at least 6 characters long!', 'error');
                return;
            }

            setupBtn.textContent = 'Creating Account...';
            setupBtn.disabled = true;

            try {
                const response = await fetch('../dbs/auth/setup_process.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showMessage('Admin account created successfully! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    showMessage(result.message || 'Failed to create admin account!', 'error');
                }
            } catch (error) {
                showMessage('An error occurred. Please try again.', 'error');
            } finally {
                setupBtn.textContent = 'Create Admin Account';
                setupBtn.disabled = false;
            }
        });

        document.getElementById('phone').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>
