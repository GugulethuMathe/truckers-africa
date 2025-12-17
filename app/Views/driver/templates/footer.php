    </div> <!-- Closes container -->

    <!-- =================================== -->
    <!-- BOTTOM NAVIGATION BAR (LOGGED-IN VERSION)  -->
    <!-- =================================== -->
    <footer class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-2 md:hidden">
      <div class="flex justify-around items-center">
        <a href="<?= site_url('dashboard/driver') ?>" class="flex flex-col items-center text-brand-blue w-1/4">
            <svg class="w-6 h-6 mb-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
            <span class="text-xs font-medium">Home</span>
        </a>
        <a href="#" class="flex flex-col items-center text-gray-500 hover:text-gray-900 w-1/4">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m-6 3l6-3m0 0l6 3m-6-3v10"></path></svg>
            <span class="text-xs font-medium">Map</span>
        </a>
        <a href="#" class="flex flex-col items-center text-gray-500 hover:text-gray-900 w-1/4">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
            <span class="text-xs font-medium">Services</span>
        </a>
        <a href="<?= site_url('profile/driver/edit') ?>" class="flex flex-col items-center text-gray-500 hover:text-gray-900 w-1/4">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            <span class="text-xs font-medium">Profile</span>
        </a>
      </div>
    </footer>
  </body>
</html>
