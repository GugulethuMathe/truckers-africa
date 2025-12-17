    </main>
    <!-- =================================== -->
    <!-- DESKTOP FOOTER (Simplified)         -->
    <!-- =================================== -->
    <footer class="bg-gray-900 border-t border-gray-700/50 py-12 hidden md:block">
      <div class="container mx-auto px-4 grid grid-cols-1 md:grid-cols-4 gap-8">
        <div>
          <!-- CHANGED: Corrected link -->
          <a href="<?= site_url('/') ?>" class="text-2xl font-condensed tracking-wider text-white mb-4 inline-block">TRUCKERS AFRICA</a>
          <p class="text-slate-400 text-sm">Built for drivers, by drivers.</p>
        </div>
        <!-- ... (other footer columns) ... -->
      </div>
      <div class="container mx-auto px-4 mt-8 border-t border-gray-800 pt-6 text-center text-sm">
        <p class="text-slate-500">© 2024 Truckers Africa. All Rights Reserved.</p>
      </div>
    
                        <div class="text-center py-4 flex justify-center space-x-6">
                            <a href="<?= site_url('packages') ?>" class="text-gray-400 hover:text-white text-sm">Pricing</a>
                            <a href="<?= site_url('contact-us') ?>" class="text-gray-400 hover:text-white text-sm">Contact Us</a>
                            <a href="<?= site_url('login') ?>" class="text-gray-400 hover:text-white text-sm">Login</a>
                            <a href="<?= site_url('signup') ?>" class="text-gray-400 hover:text-white text-sm">Sign Up</a>
                            <a href="<?= site_url('terms') ?>" class="text-gray-400 hover:text-white text-sm">Terms & Conditions</a>
                        </div>
</footer>

    <!-- =================================== -->
    <!-- MOBILE FOOTER (Utilitarian)         -->
    <!-- =================================== -->
    <footer class="bg-gray-900/80 backdrop-blur-lg border-t border-gray-700/50 p-2 md:hidden z-50">
        <div class="flex justify-around items-center">
            <!-- CHANGED: Corrected links -->
            <a href="<?= site_url('/') ?>" class="flex flex-col items-center text-secondary w-1/4">
                <i class="ri-dashboard-fill text-2xl"></i><span class="text-xs font-bold">Home</span>
            </a>
            <a href="#" class="flex flex-col items-center text-slate-400 hover:text-secondary w-1/4">
                <i class="ri-road-map-fill text-2xl"></i><span class="text-xs font-bold">Route</span>
            </a>
            <a href="#" class="flex flex-col items-center text-slate-400 hover:text-secondary w-1/4">
                <i class="ri-search-line text-2xl"></i><span class="text-xs font-bold">Search</span>
            </a>
            <a href="<?= site_url('packages') ?>" class="flex flex-col items-center text-slate-400 hover:text-secondary w-1/4">
                <i class="ri-box-3-fill text-2xl"></i><span class="text-xs font-bold">Packages</span>
            </a>
            <a href="<?= site_url('login') ?>" class="flex flex-col items-center text-slate-400 hover:text-secondary w-1/4">
                <i class="ri-user-fill text-2xl"></i><span class="text-xs font-bold">Login</span>
            </a>
        </div>
    
                        <div class="text-center py-4 flex justify-center space-x-6">
                            <a href="<?= site_url('contact-us') ?>" class="text-gray-400 hover:text-white text-sm">Contact Us</a>
                            <a href="<?= site_url('signup') ?>" class="text-gray-400 hover:text-white text-sm">Sign Up</a>
                            <a href="<?= site_url('terms') ?>" class="text-gray-400 hover:text-white text-sm">Terms & Conditions</a>
                        </div>
</footer>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@geoapify/geocoder-autocomplete@2.0.1/dist/index.min.js"></script>

</body>
</html>