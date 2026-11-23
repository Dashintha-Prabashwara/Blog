    </main>
    
    <footer class="bg-dark text-white/90 mt-20 border-t border-white/5">
        <div class="max-w-7xl mx-auto px-6 py-16">
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-12 lg:gap-8">
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-accent text-white flex items-center justify-center">
                            <i data-lucide="feather" class="w-4 h-4"></i>
                        </span>
                        <h4 class="font-serif text-2xl text-white">Code &amp; Canvas</h4>
                    </div>
                    <p class="text-white/60 text-sm leading-relaxed">A modern journal exploring the intersection of creative design and modern digital engineering.</p>
                </div>
                
                <div class="space-y-4">
                    <h5 class="font-medium text-sm tracking-wider uppercase text-white/80">Explore</h5>
                    <div class="flex flex-col gap-2.5 text-sm text-white/60">
                        <a href="/Blog/posts.php" class="hover:text-white transition-colors flex items-center gap-1.5">
                            <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
                            <span>All Stories</span>
                        </a>
                        <a href="/Blog/topics.php" class="hover:text-white transition-colors flex items-center gap-1.5">
                            <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                            <span>Topics Directory</span>
                        </a>
                        <a href="/Blog/editor.php" class="hover:text-white transition-colors flex items-center gap-1.5">
                            <i data-lucide="pen-tool" class="w-3.5 h-3.5"></i>
                            <span>Write a Story</span>
                        </a>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <h5 class="font-medium text-sm tracking-wider uppercase text-white/80">Developer</h5>
                    <div class="flex flex-col gap-2.5 text-sm text-white/60">
                        <a href="https://dashijay.dev" target="_blank" rel="noopener noreferrer" class="hover:text-white transition-colors flex items-center gap-1.5">
                            <i data-lucide="globe" class="w-3.5 h-3.5"></i>
                            <span>Portfolio (dashijay.dev)</span>
                        </a>
                        <a href="https://github.com/Dashintha-Prabashwara" target="_blank" rel="noopener noreferrer" class="hover:text-white transition-colors flex items-center gap-1.5">
                            <i data-lucide="github" class="w-3.5 h-3.5"></i>
                            <span>GitHub</span>
                        </a>
                        <a href="https://www.linkedin.com/in/dashintha-jayawardana-7b740b26b/" target="_blank" rel="noopener noreferrer" class="hover:text-white transition-colors flex items-center gap-1.5">
                            <i data-lucide="linkedin" class="w-3.5 h-3.5"></i>
                            <span>LinkedIn</span>
                        </a>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <h5 class="font-medium text-sm tracking-wider uppercase text-white/80">Newsletter</h5>
                    <p class="text-white/60 text-sm">Get the freshest stories and insights weekly.</p>
                    <form class="mt-4 flex" onsubmit="event.preventDefault(); alert('Thank you for subscribing!');">
                        <div class="flex-1 min-w-0">
                            <input type="email" 
                                   placeholder="Enter your email" 
                                   class="w-full px-4 py-2.5 text-sm bg-white/10 text-white placeholder-white/40 rounded-l-lg border border-white/10 focus:outline-none focus:ring-2 focus:ring-accent"
                                   required>
                        </div>
                        <button type="submit" 
                                class="shrink-0 px-4 py-2.5 bg-accent text-white rounded-r-lg hover:bg-accent/90 transition-colors flex items-center gap-1.5 text-sm font-medium">
                            <i data-lucide="send" class="w-3.5 h-3.5"></i>
                            <span>Join</span>
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="mt-12 pt-8 border-t border-white/10 text-white/50 text-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                <p>&copy; <?= date('Y') ?> Code &amp; Canvas. Crafted for creators &amp; engineers.</p>
                <div class="flex items-center gap-6">
                    <a href="/Blog/posts.php" class="hover:text-white transition-colors">Privacy</a>
                    <a href="/Blog/posts.php" class="hover:text-white transition-colors">Terms</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        // Smooth scroll behavior
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>
