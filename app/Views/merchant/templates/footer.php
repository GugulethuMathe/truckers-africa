        </main>
    </div>

<script>
  // Wait for DOM to be fully loaded
  document.addEventListener('DOMContentLoaded', function() {
    const apiKey = "<?= esc($geoapify_api_key ?? '') ?>";
    const physicalAddressInput = document.getElementById('physical_address');
    
    console.log('Initializing address autocomplete...');
    console.log('API Key available:', !!apiKey);
    console.log('Address input found:', !!physicalAddressInput);

    if (apiKey && physicalAddressInput) {
      let debounceTimer;
      let suggestionsList;
      
      // Create suggestions dropdown
      function createSuggestionsDropdown() {
        suggestionsList = document.createElement('div');
        suggestionsList.className = 'absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-md shadow-lg z-50 max-h-60 overflow-y-auto hidden';
        physicalAddressInput.parentNode.appendChild(suggestionsList);
      }
      
      // Hide suggestions
      function hideSuggestions() {
        if (suggestionsList) {
          suggestionsList.classList.add('hidden');
        }
      }
      
      // Show suggestions
      function showSuggestions() {
        if (suggestionsList) {
          suggestionsList.classList.remove('hidden');
        }
      }
      
      // Fetch address suggestions
      async function fetchSuggestions(query) {
        try {
          // Removed country restriction so suggestions are global
          const response = await fetch(`https://api.geoapify.com/v1/geocode/autocomplete?text=${encodeURIComponent(query)}&apiKey=${apiKey}&limit=5`);
          const data = await response.json();
          return data.features || [];
        } catch (error) {
          console.error('Error fetching suggestions:', error);
          return [];
        }
      }
      
      // Display suggestions
      function displaySuggestions(suggestions) {
        suggestionsList.innerHTML = '';
        
        if (suggestions.length === 0) {
          hideSuggestions();
          return;
        }
        
        suggestions.forEach((suggestion, index) => {
          const item = document.createElement('div');
          item.className = 'px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0';
          item.innerHTML = `
            <div class="font-medium text-gray-900">${suggestion.properties.formatted}</div>
            ${suggestion.properties.address_line2 ? `<div class="text-sm text-gray-600">${suggestion.properties.address_line2}</div>` : ''}
          `;
          
          item.addEventListener('click', () => {
            selectSuggestion(suggestion);
          });
          
          suggestionsList.appendChild(item);
        });
        
        showSuggestions();
      }
      
      // Select a suggestion
      function selectSuggestion(suggestion) {
        physicalAddressInput.value = suggestion.properties.formatted;
        
        // Store coordinates
        if (suggestion.geometry && suggestion.geometry.coordinates) {
          const [longitude, latitude] = suggestion.geometry.coordinates;
          
          const latField = document.getElementById('latitude');
          const lngField = document.getElementById('longitude');
          
          if (latField) latField.value = latitude;
          if (lngField) lngField.value = longitude;
          
          console.log('Address selected:', suggestion.properties.formatted);
          console.log('Coordinates stored:', { latitude, longitude });
        }
        
        hideSuggestions();
      }
      
      // Initialize
      createSuggestionsDropdown();
      
      // Input event listener
      physicalAddressInput.addEventListener('input', function(e) {
        const query = e.target.value.trim();
        
        clearTimeout(debounceTimer);
        
        if (query.length < 3) {
          hideSuggestions();
          return;
        }
        
        debounceTimer = setTimeout(async () => {
          const suggestions = await fetchSuggestions(query);
          displaySuggestions(suggestions);
        }, 300);
      });
      
      // Hide suggestions when clicking outside
      document.addEventListener('click', function(e) {
        if (!physicalAddressInput.contains(e.target) && !suggestionsList.contains(e.target)) {
          hideSuggestions();
        }
      });
      
      // Handle keyboard navigation
      physicalAddressInput.addEventListener('keydown', function(e) {
        const items = suggestionsList.querySelectorAll('div[class*="cursor-pointer"]');
        let selectedIndex = -1;
        
        // Find currently selected item
        items.forEach((item, index) => {
          if (item.classList.contains('bg-gray-100')) {
            selectedIndex = index;
          }
        });
        
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          selectedIndex = Math.max(selectedIndex - 1, 0);
        } else if (e.key === 'Enter' && selectedIndex >= 0) {
          e.preventDefault();
          items[selectedIndex].click();
          return;
        } else if (e.key === 'Escape') {
          hideSuggestions();
          return;
        }
        
        // Update selection
        items.forEach((item, index) => {
          if (index === selectedIndex) {
            item.classList.add('bg-gray-100');
          } else {
            item.classList.remove('bg-gray-100');
          }
        });
      });
      
      console.log('Address autocomplete initialized successfully');
      
    } else {
      if (!apiKey) console.warn('Geoapify API key not found');
      if (!physicalAddressInput) console.warn('Physical address input not found');
    }
  });
</script>
</body>
</html>
