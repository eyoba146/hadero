<?php
/**
 * Hadero Gourmet Coffee - Admin Portal Login Gate
 * PHP Implementation for local XAMPP deployment (Apache + MySQL)
 */
require_once 'db.php';
session_start();

$error = null;

// Lock-out if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_input = trim($_POST['password'] ?? '');

    if (empty($password_input)) {
        $error = "Password field cannot be left blank.";
    } else {
        try {
            // Retrieve current password string secure from database
            $stmt = $pdo->prepare("SELECT setting_value FROM admin_settings WHERE setting_key = 'admin_password'");
            $stmt->execute();
            $admin_password = $stmt->fetchColumn();

            if ($admin_password === false) {
                // If the key is not in database for some reason, default to hadero_admin
                $admin_password = 'hadero_admin';
            }

            if ($password_input === $admin_password) {
                $_SESSION['admin_logged_in'] = true;
                header('Location: admin.php');
                exit;
            } else {
                $error = "Incorrect administrator password validation credentials.";
            }
        } catch (PDOException $e) {
            $error = "Database validation error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Console Sign In - Hadero Coffee</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Didact+Gothic&family=Oswald:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet" />
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'hadero-gold': '#9B9B45',
                        'hadero-gold-dark': '#7d7d37',
                        'hadero-cream': '#FDFBF7',
                        'hadero-dark': '#1F1F1F',
                    },
                    fontFamily: {
                        oswald: ['Oswald', 'sans-serif'],
                        didact: ['"Didact Gothic"', 'sans-serif'],
                        playfair: ['"Playfair Display"', 'serif'],
                    },
                }
            }
        }
    </script>
    <style>
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }
        .shake-once { animation: shake 0.45s ease-in-out; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fadeUp 0.5s ease-out; }

        .bean-texture {
            background-image: radial-gradient(circle at 1px 1px, rgba(155,155,69,0.08) 1px, transparent 0);
            background-size: 22px 22px;
        }

        input:focus {
            box-shadow: 0 0 0 3px rgba(155, 155, 69, 0.15);
        }
    </style>
</head>
<body class="bg-hadero-cream font-didact min-h-screen flex items-center justify-center p-4 bean-texture">

    <div class="max-w-md w-full fade-up">

        <div class="bg-white border border-[#f1efe6] p-7 sm:p-10 rounded-2xl shadow-xl shadow-black/5 space-y-8 relative overflow-hidden">

            <!-- decorative top accent -->
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-hadero-gold via-hadero-gold-dark to-hadero-gold"></div>

            <div class="text-center space-y-3">
                <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-hadero-gold/10 text-hadero-gold-dark ring-1 ring-hadero-gold/20">
                    <i data-lucide="lock" class="w-6 h-6"></i>
                </div>
                <p class="font-oswald text-[10px] uppercase tracking-[0.3em] text-hadero-gold-dark">
                    Hadero Gourmet Coffee
                </p>
                <h2 class="text-2xl sm:text-3xl font-playfair font-semibold text-hadero-dark">
                    Admin Console
                </h2>
                <p class="font-didact text-xs text-gray-500 leading-relaxed max-w-xs mx-auto">
                    Enter your administrator password to access the management dashboard.
                </p>
            </div>

            <?php if ($error): ?>
                <div class="p-3.5 bg-red-50 border-l-4 border-red-500 text-red-700 flex items-start gap-2 rounded shake-once">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 mt-0.5 text-red-500"></i>
                    <p class="font-didact text-xs"><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-6" id="loginForm">
                <div>
                    <label for="password" class="block text-[11px] uppercase font-oswald tracking-widest text-gray-600 font-semibold mb-2">
                        Console Password
                    </label>
                    <div class="relative">
                        <i data-lucide="key-round" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter admin password"
                            autocomplete="current-password"
                            class="w-full border border-gray-200 pl-10 pr-11 py-3 rounded-lg text-sm focus:outline-none focus:border-hadero-gold bg-gray-50 focus:bg-white transition placeholder:text-gray-400"
                            required
                            autofocus
                        />
                        <button type="button" id="togglePw" tabindex="-1"
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-hadero-gold-dark transition">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <button
                    type="submit"
                    id="submitBtn"
                    class="w-full py-3.5 bg-hadero-dark hover:bg-black text-white font-oswald text-xs uppercase tracking-widest font-semibold transition rounded-lg flex items-center justify-center gap-2 active:scale-[0.98]"
                >
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Open Session Console</span>
                </button>
            </form>

            <div class="flex items-center gap-3">
                <div class="h-px bg-gray-100 flex-1"></div>
                <span class="text-[10px] uppercase tracking-widest text-gray-300 font-oswald">Secured Area</span>
                <div class="h-px bg-gray-100 flex-1"></div>
            </div>

            <div class="text-center">
                <a href="index.php" class="text-[11px] font-oswald uppercase text-gray-400 hover:text-hadero-dark tracking-widest transition inline-flex items-center gap-1.5 group">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5 group-hover:-translate-x-0.5 transition-transform"></i>
                    <span>Return to menu index</span>
                </a>
            </div>
        </div>

        <p class="text-center text-[10px] text-gray-400 font-didact mt-6 tracking-wide">
            &copy; <?= date('Y') ?> Hadero Gourmet Coffee &mdash; Internal use only
        </p>
    </div>

    <script>
        lucide.createIcons();

        // Show/hide password
        const togglePw = document.getElementById('togglePw');
        const pwInput = document.getElementById('password');
        togglePw.addEventListener('click', () => {
            const isPw = pwInput.type === 'password';
            pwInput.type = isPw ? 'text' : 'password';
            togglePw.innerHTML = `<i data-lucide="${isPw ? 'eye-off' : 'eye'}" class="w-4 h-4"></i>`;
            lucide.createIcons();
        });

        // Loading state on submit (UX only, server still validates)
        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        form.addEventListener('submit', () => {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
            submitBtn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i><span>Authenticating...</span>`;
            lucide.createIcons();
        });
    </script>
</body>
</html>