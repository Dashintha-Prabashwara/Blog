<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

$user = null;
if (is_logged_in()) {
    $stmt = $pdo->prepare("SELECT * FROM user WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Dynamic SEO Tags -->
    <?php
    $pageTitle = $pageTitle ?? 'Code & Canvas';
    $pageDescription = $pageDescription ?? 'A community platform for developers and designers to share stories and insights.';
    $pageImage = $pageImage ?? '/Blog/assets/images/default-og.jpg';
    ?>
    
    <title><?= sanitizeInput($pageTitle) ?></title>
    <meta name="description" content="<?= sanitizeInput($pageDescription) ?>">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?= sanitizeInput($pageTitle) ?>">
    <meta property="og:description" content="<?= sanitizeInput($pageDescription) ?>">
    <meta property="og:image" content="<?= sanitizeInput($pageImage) ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Code & Canvas">
    
    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= sanitizeInput($pageTitle) ?>">
    <meta name="twitter:description" content="<?= sanitizeInput($pageDescription) ?>">
    <meta name="twitter:image" content="<?= sanitizeInput($pageImage) ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/Blog/assets/images/favicon.png">
    
    <title>Code & Canvas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Blog/assets/css/main.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cream: '#FAF8F5',
                        charcoal: '#2C2C2C',
                        accent: '#8B6F47',
                        sage: '#6B7F69',
                        taupe: '#E8E4DF',
                        dark: '#1A1A1A'
                    },
                    fontFamily: {
                        serif: ['Fraunces', 'serif'],
                        sans: ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-cream text-charcoal" x-data="{ mobileMenuOpen: false }">
    <header class="fixed w-full z-50 bg-cream border-b border-taupe">
        <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8"> <!-- Reduced padding on mobile -->
            <nav class="flex items-center justify-between h-16 sm:h-20"> <!-- Reduced height on mobile -->
                <!-- Logo -->
                <a href="/Blog/" class="flex items-center">
                    <span class="font-serif text-lg sm:text-2xl">Code & Canvas</span> <!-- Smaller text on mobile -->
                </a>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="/Blog/posts.php" class="text-charcoal/80 hover:text-charcoal transition-colors">Stories</a>
                    <a href="/Blog/topics.php" class="text-charcoal/80 hover:text-charcoal transition-colors">Topics</a>
                    <?php if (is_logged_in()): ?>
                        <a href="/Blog/dashboard.php" class="text-charcoal/80 hover:text-charcoal transition-colors">Dashboard</a>
                        <a href="/Blog/editor.php" class="px-5 py-2 bg-dark text-white rounded-lg hover:bg-charcoal transition-all">Write</a>
                        <!-- Profile Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <a href="/Blog/account-settings.php" 
                               class="flex items-center space-x-2" 
                               @mouseenter="open = true">
                                <div class="w-8 h-8 rounded-full overflow-hidden bg-gray-100">
                                    <img src="<?= $user['profile_image'] ?? '/Blog/assets/images/default-avatar.png' ?>" 
                                         alt="<?= htmlspecialchars($_SESSION['username']) ?>"
                                         class="w-full h-full object-cover">
                                </div>
                                <span class="text-sm"><?= htmlspecialchars($_SESSION['username']) ?></span>
                            </a>
                            
                            <div x-show="open" 
                                 @mouseleave="open = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50"
                                 style="display: none;">
                                <a href="/Blog/account-settings.php" class="block px-4 py-2 hover:bg-gray-100">Account Settings</a>
                                <a href="/Blog/profile-settings.php" class="block px-4 py-2 hover:bg-gray-100">Profile Settings</a>
                                <a href="/Blog/security-settings.php" class="block px-4 py-2 hover:bg-gray-100">Security Settings</a>
                                <hr class="my-2">
                                <a href="/Blog/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="/Blog/login.php" class="text-charcoal/80 hover:text-charcoal transition-colors">Sign in</a>
                        <a href="/Blog/register.php" class="px-5 py-2 bg-dark text-white rounded-lg hover:bg-charcoal transition-all">Register</a>
                    <?php endif; ?>
                </div>

                <!-- Mobile menu button -->
                <button type="button" 
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="md:hidden inline-flex items-center justify-center p-2 rounded-lg hover:bg-white/50">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </nav>
        </div>

        <!-- Mobile Navigation Overlay -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 z-40 md:hidden"
             @click="mobileMenuOpen = false"
             style="display: none;">
        </div>

        <!-- Mobile Navigation Menu -->
        <div x-show="mobileMenuOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-x-full"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 -translate-x-full"
             class="fixed inset-y-0 left-0 w-3/4 max-w-sm bg-cream z-50 md:hidden"
             style="display: none;">
            
            <div class="p-6 space-y-6">
                <div class="flex items-center justify-between mb-8">
                    <a href="/Blog/" class="font-serif text-2xl">Code & Canvas</a>
                    <button @click="mobileMenuOpen = false" class="p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <nav class="space-y-4">
                    <a href="/Blog/posts.php" @click="mobileMenuOpen = false" class="block py-2">Stories</a>
                    <a href="/Blog/topics.php" @click="mobileMenuOpen = false" class="block py-2">Topics</a>
                    <?php if (is_logged_in()): ?>
                        <a href="/Blog/dashboard.php" @click="mobileMenuOpen = false" class="block py-2">Dashboard</a>
                        <a href="/Blog/editor.php" @click="mobileMenuOpen = false" class="block py-2">Write</a>
                        <hr class="my-4 border-taupe">
                        <a href="/Blog/account-settings.php" @click="mobileMenuOpen = false" class="block py-2">Account Settings</a>
                        <a href="/Blog/logout.php" @click="mobileMenuOpen = false" class="block py-2 text-red-600">Logout</a>
                    <?php else: ?>
                        <a href="/Blog/login.php" @click="mobileMenuOpen = false" class="block py-2">Sign in</a>
                        <a href="/Blog/register.php" @click="mobileMenuOpen = false" class="block py-2">Register</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <!-- Mobile Search - made more compact -->
    <div class="md:hidden px-2 sm:px-4 py-2 sm:py-3 bg-cream border-b border-taupe">
        <form action="/Blog/search.php">
            <div class="relative">
                <input type="search" name="q" 
                       placeholder="Search stories..." 
                       class="w-full pl-3 pr-8 py-1.5 sm:py-2 text-sm bg-white/50 border border-taupe rounded-lg">
                <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-charcoal/50">
                    <svg width="16" height="16" fill="none" stroke="currentColor"> <!-- Smaller icon -->
                        <path d="M19 19l-4.35-4.35M17 9A8 8 0 111 9a8 8 0 0116 0z" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>

    <!-- Adjusted main content padding with dynamic classes -->
    <main class="<?php
        $current_page = basename($_SERVER['PHP_SELF']);
        switch($current_page) {
            case 'index.php':
                echo 'pt-1'; // Hero section - smallest padding
                break;
            case 'posts.php':
            case 'topics.php':
                echo 'pt-10'; // Stories and Topics - more breathing room
                break;
            case 'login.php':
            case 'register.php':
                echo 'pt-16'; // Login/Register - medium padding
                break;
            default:
                echo 'pt-24'; // Default padding for other pages
        }
        ?> sm:pt-<?php
        switch($current_page) {
            case 'index.php':
                echo '1'; // Hero section - minimal padding on desktop
                break;
            case 'posts.php':
            case 'topics.php':
                echo '10'; // Stories and Topics - larger padding on desktop
                break;
            case 'login.php':
            case 'register.php':
                echo '16'; // Login/Register - medium padding on desktop
                break;
            default:
                echo '24'; // Default padding for other pages
        }
        ?>">
        <!-- Content will be loaded here -->
    </main>
</body>
</html>
