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
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
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
<body class="bg-cream text-charcoal min-h-screen flex flex-col justify-between" x-data="{ mobileMenuOpen: false }">
    <header class="fixed top-0 left-0 w-full z-50 bg-cream/95 backdrop-blur-md border-b border-taupe">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center justify-between h-16 sm:h-20">
                <!-- Logo -->
                <a href="/Blog/" class="flex items-center gap-2.5 group">
                    <span class="w-9 h-9 rounded-xl bg-dark text-white flex items-center justify-center group-hover:bg-accent transition-colors shadow-sm">
                        <i data-lucide="feather" class="w-5 h-5"></i>
                    </span>
                    <div class="flex flex-col">
                        <span class="font-serif text-lg sm:text-xl font-bold tracking-tight leading-tight">Code &amp; Canvas</span>
                        <span class="text-[10px] uppercase font-mono tracking-wider text-charcoal/50 leading-none">by Dashintha Jayawardana</span>
                    </div>
                </a>

                <!-- Desktop Search -->
                <div class="hidden lg:block w-72">
                    <form action="/Blog/search.php" method="GET">
                        <div class="relative">
                            <i data-lucide="search" class="w-4 h-4 text-charcoal/40 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            <input type="search" name="q" placeholder="Search stories..." 
                                   class="w-full pl-10 pr-4 py-2 text-sm bg-white/70 border border-taupe rounded-full focus:outline-none focus:ring-2 focus:ring-accent/50 focus:bg-white transition-all">
                        </div>
                    </form>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-7">
                    <a href="/Blog/posts.php" class="text-charcoal/80 hover:text-charcoal transition-colors flex items-center gap-1.5 text-sm font-medium">
                        <i data-lucide="book-open" class="w-4 h-4 text-charcoal/60"></i>
                        <span>Stories</span>
                    </a>
                    <a href="/Blog/topics.php" class="text-charcoal/80 hover:text-charcoal transition-colors flex items-center gap-1.5 text-sm font-medium">
                        <i data-lucide="tag" class="w-4 h-4 text-charcoal/60"></i>
                        <span>Topics</span>
                    </a>
                    <?php if (is_logged_in()): ?>
                        <a href="/Blog/dashboard.php" class="text-charcoal/80 hover:text-charcoal transition-colors flex items-center gap-1.5 text-sm font-medium">
                            <i data-lucide="layout-dashboard" class="w-4 h-4 text-charcoal/60"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="/Blog/editor.php" class="inline-flex items-center gap-2 px-4 py-2 bg-dark text-white rounded-lg hover:bg-charcoal transition-all text-sm font-medium shadow-sm">
                            <i data-lucide="pen-tool" class="w-4 h-4"></i>
                            <span>Write</span>
                        </a>
                        <!-- Profile Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" 
                                    @click.away="open = false"
                                    class="flex items-center gap-2 p-1 rounded-full hover:ring-2 hover:ring-taupe transition-all">
                                <div class="w-9 h-9 rounded-full overflow-hidden bg-taupe border border-taupe">
                                    <img src="<?= $user['profile_image'] ?? '/Blog/assets/images/default-avatar.png' ?>" 
                                         alt="<?= htmlspecialchars($_SESSION['username']) ?>"
                                         class="w-full h-full object-cover">
                                </div>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-charcoal/60"></i>
                            </button>
                            
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50 divide-y divide-gray-100"
                                 style="display: none;">
                                <div class="px-4 py-2">
                                    <p class="text-xs text-charcoal/50">Signed in as</p>
                                    <p class="text-sm font-semibold truncate"><?= htmlspecialchars($_SESSION['username']) ?></p>
                                </div>
                                <div class="py-1">
                                    <a href="/Blog/dashboard.php" class="flex items-center gap-2.5 px-4 py-2 text-sm text-charcoal/80 hover:bg-cream hover:text-charcoal transition-colors">
                                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                        <span>Dashboard</span>
                                    </a>
                                    <a href="/Blog/account-settings.php" class="flex items-center gap-2.5 px-4 py-2 text-sm text-charcoal/80 hover:bg-cream hover:text-charcoal transition-colors">
                                        <i data-lucide="user" class="w-4 h-4"></i>
                                        <span>Account Settings</span>
                                    </a>
                                    <a href="/Blog/profile-settings.php" class="flex items-center gap-2.5 px-4 py-2 text-sm text-charcoal/80 hover:bg-cream hover:text-charcoal transition-colors">
                                        <i data-lucide="image" class="w-4 h-4"></i>
                                        <span>Profile Settings</span>
                                    </a>
                                    <a href="/Blog/security-settings.php" class="flex items-center gap-2.5 px-4 py-2 text-sm text-charcoal/80 hover:bg-cream hover:text-charcoal transition-colors">
                                        <i data-lucide="shield" class="w-4 h-4"></i>
                                        <span>Security Settings</span>
                                    </a>
                                </div>
                                <div class="py-1">
                                    <a href="/Blog/logout.php" class="flex items-center gap-2.5 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                        <i data-lucide="log-out" class="w-4 h-4"></i>
                                        <span>Logout</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="https://dashijay.dev" target="_blank" rel="noopener noreferrer" 
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full border border-taupe text-xs font-semibold text-charcoal/80 hover:bg-white hover:text-dark transition-all shadow-sm">
                            <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 text-accent"></i>
                            <span>dashijay.dev</span>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile menu button -->
                <button type="button" 
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="md:hidden inline-flex items-center justify-center p-2 rounded-lg hover:bg-taupe/40 text-charcoal">
                    <i data-lucide="menu" class="w-6 h-6"></i>
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

        <!-- Mobile Navigation Drawer -->
        <div x-show="mobileMenuOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-x-full"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 -translate-x-full"
             class="fixed inset-y-0 left-0 w-3/4 max-w-sm bg-cream z-50 md:hidden shadow-2xl p-6 flex flex-col justify-between"
             style="display: none;">
            
            <div class="space-y-6">
                <div class="flex items-center justify-between pb-4 border-b border-taupe">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-dark text-white flex items-center justify-center">
                            <i data-lucide="feather" class="w-4 h-4"></i>
                        </span>
                        <span class="font-serif text-xl">Code &amp; Canvas</span>
                    </div>
                    <button @click="mobileMenuOpen = false" class="p-2 text-charcoal/60 hover:text-charcoal">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Mobile Search -->
                <form action="/Blog/search.php" method="GET">
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 text-charcoal/40 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input type="search" name="q" placeholder="Search stories..." 
                               class="w-full pl-9 pr-3 py-2 text-sm bg-white/70 border border-taupe rounded-lg">
                    </div>
                </form>

                <nav class="space-y-2">
                    <a href="/Blog/posts.php" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-charcoal hover:bg-taupe/40">
                        <i data-lucide="book-open" class="w-5 h-5 text-charcoal/60"></i>
                        <span class="font-medium">Stories</span>
                    </a>
                    <a href="/Blog/topics.php" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-charcoal hover:bg-taupe/40">
                        <i data-lucide="tag" class="w-5 h-5 text-charcoal/60"></i>
                        <span class="font-medium">Topics</span>
                    </a>
                    <?php if (is_logged_in()): ?>
                        <a href="/Blog/dashboard.php" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-charcoal hover:bg-taupe/40">
                            <i data-lucide="layout-dashboard" class="w-5 h-5 text-charcoal/60"></i>
                            <span class="font-medium">Dashboard</span>
                        </a>
                        <a href="/Blog/editor.php" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-dark text-white hover:bg-charcoal">
                            <i data-lucide="pen-tool" class="w-5 h-5"></i>
                            <span class="font-medium">Write Story</span>
                        </a>
                        <hr class="my-3 border-taupe">
                        <a href="/Blog/account-settings.php" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-charcoal/80 hover:bg-taupe/40 text-sm">
                            <i data-lucide="user" class="w-4 h-4"></i>
                            <span>Account Settings</span>
                        </a>
                        <a href="/Blog/profile-settings.php" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-charcoal/80 hover:bg-taupe/40 text-sm">
                            <i data-lucide="image" class="w-4 h-4"></i>
                            <span>Profile Settings</span>
                        </a>
                        <a href="/Blog/security-settings.php" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-charcoal/80 hover:bg-taupe/40 text-sm">
                            <i data-lucide="shield" class="w-4 h-4"></i>
                            <span>Security Settings</span>
                        </a>
                    <?php else: ?>
                        <div class="pt-4">
                            <a href="https://dashijay.dev" target="_blank" rel="noopener noreferrer" @click="mobileMenuOpen = false" 
                               class="flex items-center justify-center gap-2 w-full py-2.5 bg-white border border-taupe rounded-xl text-sm font-semibold text-charcoal shadow-sm hover:bg-cream">
                                <i data-lucide="arrow-up-right" class="w-4 h-4 text-accent"></i>
                                <span>Portfolio (dashijay.dev)</span>
                            </a>
                        </div>
                    <?php endif; ?>
                </nav>
            </div>

            <?php if (is_logged_in()): ?>
                <div class="pt-4 border-t border-taupe">
                    <a href="/Blog/logout.php" @click="mobileMenuOpen = false" class="flex items-center gap-2 text-red-600 font-medium px-3 py-2 rounded-lg hover:bg-red-50">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        <span>Logout</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Main Content Container with safe header spacing -->
    <main class="flex-grow pt-20 sm:pt-24">
