</main>
    
    <footer class="bg-dark text-white/90">
        <div class="max-w-7xl mx-auto px-6 py-20">
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-12 lg:gap-8">
                <div class="space-y-4">
                    <h4 class="font-serif text-2xl">Code & Canvas</h4>
                    <p class="text-white/60">A journal exploring the intersection of design and development.</p>
                </div>
                
                <div class="space-y-4">
                    <h5 class="font-medium">Categories</h5>
                    <div class="flex flex-col gap-2 text-white/60">
                        <a href="#" class="hover:text-white transition-colors">Design Systems</a>
                        <a href="#" class="hover:text-white transition-colors">Development</a>
                        <a href="#" class="hover:text-white transition-colors">Typography</a>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <h5 class="font-medium">Contact the Developer</h5>
                    <div class="flex flex-col gap-2 text-white/60">
                        <a href="https://dashijayawardana.vercel.app/" target="_blank" class="hover:text-white transition-colors">Portfolio</a>
                        <a href="https://github.com/Dashintha-Prabashwara" target="_blank" class="hover:text-white transition-colors">GitHub</a>
                        <a href="https://www.linkedin.com/in/dashintha-jayawardana-7b740b26b/" target="_blank" class="hover:text-white transition-colors">LinkedIn</a>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <h5 class="font-medium">Newsletter</h5>
                    <p class="text-white/60">Get the latest stories and updates.</p>
                    <form class="mt-4 flex">
                        <div class="flex-1 min-w-0"> <!-- Added min-w-0 to prevent input overflow -->
                            <input type="email" 
                                   placeholder="Enter your email" 
                                   class="w-full px-4 py-2 bg-white/10 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-accent"
                                   required>
                        </div>
                        <button type="submit" 
                                class="shrink-0 px-4 py-2 bg-accent text-white rounded-r-lg hover:bg-accent/90 transition-colors">
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="mt-16 pt-8 border-t border-white/10 text-white/60 text-sm">
                <p>&copy; <?= date('Y') ?> Code & Canvas. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scroll behavior
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>
