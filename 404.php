<?php 
$pageTitle = '404 Page Not Found — Code & Canvas';
require_once __DIR__ . '/includes/header.php'; 
?>

<div class="min-h-[55vh] flex items-center justify-center px-4 py-16">
    <div class="text-center max-w-md mx-auto">
        <div class="w-16 h-16 rounded-3xl bg-accent/10 text-accent flex items-center justify-center mx-auto mb-6 shadow-sm">
            <i data-lucide="compass" class="w-8 h-8"></i>
        </div>
        <h1 class="font-serif text-6xl font-bold mb-3 text-charcoal">404</h1>
        <h2 class="font-serif text-2xl font-bold mb-3 text-charcoal">Story Not Found</h2>
        <p class="text-sm text-charcoal/60 mb-8 leading-relaxed">
            The page or story you are looking for may have been removed, had its name changed, or is temporarily unavailable.
        </p>
        <div class="flex items-center justify-center gap-3">
            <a href="/Blog/" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-dark text-white hover:bg-charcoal transition-all text-xs font-semibold shadow-sm">
                <i data-lucide="home" class="w-4 h-4"></i>
                <span>Return Home</span>
            </a>
            <a href="/Blog/posts.php" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-taupe bg-white text-charcoal hover:bg-cream transition-all text-xs font-semibold shadow-sm">
                <i data-lucide="book-open" class="w-4 h-4"></i>
                <span>Explore Stories</span>
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
