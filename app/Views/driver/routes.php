<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Route Planning' ?> - Truckers Africa</title>
<?= view('driver/templates/header', ['page_title' => $page_title]) ?>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Custom Styles -->
    <style>
        body { 
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        button#startNavigationBtn {
    display: none;
}
        /* Full screen map */
        #map {
            height: 100vh;
            width: 100%;
            z-index: 1;
        }
        
        /* Bottom sheet styles */
        .bottom-sheet {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-radius: 20px 20px 0 0;
            box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.2);
            transform: translateY(calc(100% - 180px));
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1100;
            max-height: calc(100vh - 100px);
            overflow: hidden;
        }

        .bottom-sheet.expanded {
            transform: translateY(0);
        }

        .bottom-sheet.collapsed {
            transform: translateY(calc(100% - 80px));
        }
        
        /* Drag handle */
        .drag-handle {
            width: 40px;
            height: 4px;
            background: #d1d5db;
            border-radius: 2px;
            margin: 12px auto;
            cursor: grab;
        }

        .drag-handle:active {
            cursor: grabbing;
        }

        /* Floating expand button */
        .expand-sheet-btn {
            position: fixed;
            bottom: 80px;
            right: 20px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 20px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
            cursor: pointer;
            z-index: 1050;
            transition: all 0.3s ease;
        }

        .expand-sheet-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.5);
        }

        .expand-sheet-btn:active {
            transform: scale(0.95);
        }

        .expand-sheet-btn i {
            color: white;
            font-size: 20px;
        }

        .expand-sheet-btn span {
            color: white;
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
        }

        /* Hide button when sheet is expanded */
        .bottom-sheet.expanded ~ .expand-sheet-btn {
            opacity: 0;
            pointer-events: none;
        }

        /* Show button when sheet is collapsed */
        .bottom-sheet.collapsed ~ .expand-sheet-btn {
            opacity: 1;
            pointer-events: all;
        }
        
        /* Header controls */
        .header-controls {
            position: fixed;
            top: 60px;
            left: 16px;
            right: 16px;
            z-index: 500;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .control-btn {
            background: white;
            border: none;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .control-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }
        
        /* Route info card */
        .route-info-card {
            position: fixed;
            top: 120px;
            left: 16px;
            right: 16px;
            background: white;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 500;
            display: none;
        }
        
        /* Content sections */
        .sheet-content {
            padding: 0 20px 20px;
            overflow-y: auto;
            max-height: calc(100vh - 200px);
        }

        /* Hide sheet content when collapsed */
        .bottom-sheet.collapsed .sheet-content {
            display: none !important;
        }

        .bottom-sheet:not(.collapsed) .sheet-content {
            display: block !important;
            visibility: visible !important;
        }

        /* Content visibility based on sheet state */
        .bottom-sheet.collapsed .expanded-only {
            display: none !important;
        }

        .bottom-sheet:not(.collapsed) .expanded-only {
            display: block;
        }

        /* Hidden sections */
        .section-hidden {
            display: none !important;
        }
        
        .section {
            margin-bottom: 24px;
            display: block !important;
            visibility: visible !important;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 8px;
            color: #2f855a; /* Dashboard green */
        }
        
        /* Suggestions styling */
        .suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            max-height: 200px;
            overflow-y: auto;
            display: none;
        }
        
        .suggestion-item {
            padding: 12px 16px;
            cursor: pointer;
            border-bottom: 1px solid #f3f4f6;
            transition: background-color 0.15s;
        }
        
        .suggestion-item:hover {
            background-color: #f9fafb;
        }
        
        .suggestion-item:last-child {
            border-bottom: none;
        }

        /* Input styles */
        .input-group {
            margin-bottom: 16px;
            position: relative;
        }
        
        .input-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }
        
        .input-field {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.2s;
        }
        
        .input-field:focus {
            outline: none;
            border-color: #2f855a; /* Dashboard green */
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            cursor: pointer;
        }
        
        .remove-stop-btn {
            position: absolute;
            right: 45px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #ef4444;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        
        .remove-stop-btn:hover {
            background-color: #fef2f2;
        }
        
        /* Button styles - Matching dashboard colors */
        .btn-primary {
            background: #2f855a; /* Green from dashboard */
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            width: 100%;
        }

        .btn-primary:hover {
            background: #276749; /* Darker green on hover */
        }

        .btn-success {
            background: #10b981; /* Success green */
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: not-allowed;
            opacity: 0.8;
            width: 100%;
        }

        .btn-secondary {
            background: #0e2140; /* Dark navy from dashboard */
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        /* Mobile button sizing tweaks */
        @media (max-width: 480px) {
            .control-btn { width: 40px; height: 40px; }
            .btn-primary { padding: 10px 14px; font-size: 14px; }
            .btn-secondary { padding: 8px 12px; font-size: 13px; }
        }

        .btn-secondary:hover {
            background: #1a3a5c; /* Lighter navy on hover */
        }
        
        /* Route card styles */
        .route-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .route-card:hover {
            background: #f3f4f6;
            transform: translateY(-1px);
        }
        
        /* Suggestions dropdown */
        .suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            z-index: 1001;
            max-height: 200px;
            overflow-y: auto;
            display: none;
        }
        
        .suggestion-item {
            padding: 12px 16px;
            cursor: pointer;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .suggestion-item:hover {
            background: #f9fafb;
        }
        
        .suggestion-item:last-child {
            border-bottom: none;
        }

        /* Directions list styles */
        .direction-step {
            display: flex;
            align-items: flex-start;
            padding: 12px;
            background: #f9fafb;
            border-radius: 8px;
            border-left: 3px solid #0e2140;
            transition: all 0.2s;
        }

        .direction-step:hover {
            background: #f3f4f6;
            transform: translateX(2px);
        }

        .direction-step-number {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            background: #0e2140;
            color: white;
            border-radius: 50%;
            font-weight: bold;
            font-size: 14px;
            margin-right: 12px;
        }

        .direction-step-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            background: #2f855a;
            color: white;
            border-radius: 50%;
            margin-right: 12px;
        }

        .direction-step-content {
            flex: 1;
        }

        .direction-step-instruction {
            font-size: 14px;
            font-weight: 500;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .direction-step-distance {
            font-size: 12px;
            color: #6b7280;
        }

        /* Drag and drop styles for stops */
        .stop-item {
            transition: all 0.2s ease;
            margin-bottom: 8px;
        }

        .stop-item:hover {
            cursor: move;
        }

        .stop-item.dragging {
            opacity: 0.5;
            transform: scale(0.98);
        }

        .stop-item.drag-over {
            border-color: #3b82f6 !important;
            border-width: 2px !important;
            background-color: #eff6ff !important;
        }

        /* Grip handle icon styles */
        .fa-grip-vertical {
            transition: color 0.2s ease;
        }

        .stop-item:hover .fa-grip-vertical {
            color: #3b82f6 !important;
        }
        .input-field {
    width: 100%;
    color: black;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.2s;
}
    </style>
</head>
<body>
    <!-- Full Screen Map -->
    <div id="map"></div>

    <!-- Navigation Banner (Geoapify-based) -->
    <div id="navBanner" style="position: fixed; top: 16px; left: 50%; transform: translateX(-50%); z-index: 1200; background: #ffffff; color: #111827; border-radius: 14px; padding: 10px 14px; box-shadow: 0 8px 24px rgba(0,0,0,0.18); display: none; min-width: 260px; max-width: 94vw;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="background:#e5e7eb; color:#111827; width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-turn-up"></i>
            </div>
            <div style="flex:1">
                <div id="navText" style="font-weight: 700; font-size: 14px; line-height: 1.2;">Navigation</div>
                <div id="navSub" style="font-size: 12px; color:#4b5563; margin-top:4px;"></div>
            </div>
            <button id="navClose" style="background: transparent; border: none; color: #6b7280; cursor: pointer;"><i class="fas fa-times"></i></button>
        </div>
    </div>
     
    <!-- Sliding Bottom Sheet -->
    <div id="bottomSheet" class="bottom-sheet">
        <!-- Drag Handle -->
        <div  id="toggleSheet" class="drag-handle"></div>  
        <!-- Sheet Content -->
        <div class="sheet-content">
            <!-- Route Planning Section (Always visible) -->
            <div class="section">
                <div class="section-title" style="justify-content: space-between;">
                    <div style="display: flex; align-items: center;">
                        <i class="fas fa-route"></i>
                        Plan Route
                    </div>
                    <button type="button" id="removeRouteBtn" onclick="clearRoute()" style="display: none; background: #ef4444; color: white; border: none; border-radius: 6px; padding: 6px 12px; font-size: 13px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">
                        <i class="fas fa-trash-alt" style="margin-right: 4px;"></i>Remove Route
                    </button>
                </div>

                <form id="routePlanningForm">
                    <!-- From Input -->
                    <div class="input-group">
                        <label class="input-label">From</label>
                        <div class="input-with-icon">
                            <input type="text" id="startAddress" class="input-field" placeholder="Enter pickup location">
                            <i class="fas fa-location-crosshairs input-icon" onclick="getCurrentLocationForStart()"></i>
                        </div>
                        <div id="startSuggestions" class="suggestions"></div>
                    </div>
                    
                    <!-- To Input -->
                    <div class="input-group">
                        <label class="input-label">To</label>
                        <div class="input-with-icon">
                            <input type="text" id="endAddress" class="input-field" placeholder="Enter destination">
                            <i class="fas fa-location-crosshairs input-icon" onclick="getCurrentLocationForEnd()"></i>
                        </div>
                        <div id="endSuggestions" class="suggestions"></div>
                    </div>
                    
                    <!-- Stops Section (Only in expanded) -->
                    <div id="stopsContainer" class="expanded-only">
                        <!-- Dynamic stops will be added here -->
                    </div>
                    
                    <!-- Add Stop Button -->
                    <button type="button" id="addStopBtn" class="btn-secondary mb-4 w-full">
                        <i class="fas fa-plus mr-2"></i>Add Stop
                    </button>
                    
                    <!-- Plan Route Button -->
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-route mr-2"></i>Plan Route
                    </button>
                </form>
            </div>
            
            <!-- Active Route Section (shown when route is planned) -->
            <div id="activeRouteSection" class="section expanded-only" style="display: none;">
                <div class="section-title">
                    <i class="fas fa-location-arrow"></i>
                    Active Route
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <!-- Route Summary -->
                    <div class="mb-4">
                        <div class="flex items-center text-sm text-gray-700 mb-2">
                            <i class="fas fa-map-marker-alt text-green-500 mr-2"></i>
                            <span class="font-medium">From:</span>
                            <span id="routeStartAddress" class="ml-2 truncate">-</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-700 mb-2">
                            <i class="fas fa-flag-checkered text-red-500 mr-2"></i>
                            <span class="font-medium">To:</span>
                            <span id="routeEndAddress" class="ml-2 truncate">-</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-700 mb-3">
                            <i class="fas fa-route text-blue-500 mr-2"></i>
                            <span class="font-medium">Stops:</span>
                            <span id="routeStopsCount" class="ml-2">0</span>
                        </div>
                    </div>
                    
                    <!-- Route Metrics -->
                    <div class="grid grid-cols-2 gap-4 text-sm mb-4 p-3 bg-white rounded-lg">
                        <div class="text-center">
                            <div class="text-gray-600 mb-1">Distance</div>
                            <div id="routeDistance" class="font-bold text-lg text-gray-900">-</div>
                        </div>
                        <div class="text-center">
                            <div class="text-gray-600 mb-1">Duration</div>
                            <div id="routeDuration" class="font-bold text-lg text-gray-900">-</div>
                        </div>
                        <div class="text-center">
                            <div class="text-gray-600 mb-1">Created</div>
                            <div id="routeCreatedTime" class="font-bold text-sm text-gray-700">-</div>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button id="saveRouteBtn" class="btn-primary flex-1">
                            <i class="fas fa-save mr-2"></i>Save Route
                        </button>
                         <button id="startNavigationBtn" class="btn-secondary flex-1">
                             <i class="fas fa-location-arrow mr-2"></i>Start Navigation
                         </button>
                        <button id="clearRouteBtn" class="btn-secondary">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Turn-by-Turn Directions Section -->
            <div id="directionsSection" class="section expanded-only section-hidden">
                <div class="section-title">
                    <i class="fas fa-list-ol"></i>
                    Turn-by-Turn Directions
                </div>
                <div id="directionsList" class="space-y-2 max-h-96 overflow-y-auto">
                    <!-- Directions will be populated here -->
                </div>
            </div>

            <!-- Saved Routes Section -->
            <div class="section expanded-only">
                <div class="section-title">
                    <i class="fas fa-bookmark"></i>
                    Saved Routes
                </div>
                
                <?php if (!empty($saved_routes)): ?>
                    <?php foreach ($saved_routes as $route): ?>
                        <div class="route-card cursor-pointer" onclick="window.location.href='<?= base_url('driver/routes/view/') ?><?= $route['id'] ?>'">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-800 mb-1">
                                        <?= esc($route['route_name'] ?? ($route['start_address'] . ' to ' . $route['end_address'])) ?>
                                    </h3>
                                    <p class="text-sm text-gray-600 mb-2">
                                        <?= esc($route['start_address']) ?> → <?= esc($route['end_address']) ?>
                                    </p>
                                    <div class="flex items-center space-x-4 text-xs text-gray-500">
                                        <?php if (isset($route['total_distance_km'])): ?>
                                            <span><i class="fas fa-route mr-1"></i><?= number_format($route['total_distance_km'], 1) ?> km</span>
                                        <?php endif; ?>
                                        <?php if (isset($route['estimated_duration_minutes'])): ?>
                                            <span><i class="fas fa-clock mr-1"></i><?= floor($route['estimated_duration_minutes'] / 60) ?>h <?= $route['estimated_duration_minutes'] % 60 ?>m</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="ml-3 flex items-center space-x-2">
                                    <button type="button"
                                        onclick="useSavedRoute(event, <?= $route['id'] ?>)" 
                                        class="px-3 py-1 text-xs rounded bg-blue-600 text-white hover:bg-blue-700 transition-colors"
                                        title="Use this route"
                                    >
                                        <i class="fas fa-play mr-1"></i>Use Route
                                    </button>
                                    <button type="button"
                                        onclick="event.stopPropagation(); toggleSaveRoute(<?= $route['id'] ?>, this)"
                                        class="p-2 rounded-full hover:bg-red-100 transition-colors"
                                        title="Remove from saved"
                                    >
                                        <i class="fas fa-trash text-red-500"></i>
                                    </button>
                                    <i class="fas fa-chevron-right text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-bookmark text-3xl mb-3"></i>
                        <p>No saved routes found</p>
                        <p class="text-sm">Save your favorite routes for quick access</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Floating Expand Button -->
    <div class="expand-sheet-btn" id="expandSheetBtn" onclick="setSheetPosition('expanded')">
        <i class="fas fa-route"></i>
        <span>Plan Route</span>
    </div>

    <!-- Bottom Navigation -->
    <?php
    $current_page = 'routes';
    echo view('driver/templates/bottom_nav', ['current_page' => $current_page]);
    ?>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Handle clicking Use Route on a saved route card without triggering navigation
function useSavedRoute(event, routeId) {
    if (event && typeof event.stopPropagation === 'function') {
        event.stopPropagation();
        event.preventDefault();
    }
    if (typeof repeatRoute === 'function') {
        repeatRoute(routeId);
        try { setSheetPosition && setSheetPosition('expanded'); } catch (e) {}
    }
}

// Address autocomplete functionality
// Prefer key injected from controller; fall back to environment variable as safety net
const apiKey = '<?= esc($geoapify_api_key ?? getenv('GEOAPIFY_API_KEY')) ?>';

// Fuel calculation settings from platform settings
const fuelSettings = <?= json_encode($fuel_settings ?? ['fuel_cost_per_liter_zar' => 23.50, 'km_per_liter' => 2.0, 'liters_per_100km' => 50.0, 'average_speed_kmh' => 65]) ?>;

let startDebounceTimer, endDebounceTimer;

// Debug API key availability
console.log('API Key Check:', {
    fromController: '<?= esc($geoapify_api_key ?? 'NOT_SET') ?>',
    fromEnv: '<?= getenv('GEOAPIFY_API_KEY') ? 'SET' : 'NOT_SET' ?>',
    finalKey: apiKey ? 'AVAILABLE' : 'MISSING',
    keyLength: apiKey ? apiKey.length : 0
});

if (!apiKey || apiKey.trim() === '') {
    console.warn('⚠️ GEOAPIFY API KEY NOT CONFIGURED!');
    console.warn('Autocomplete will not work. Please check your environment variables.');
}

// --- Unified suggestion helpers to ensure consistent show/hide behavior across duplicate implementations ---
function showSuggestions(arg1, arg2) {
    // Case 1: Direct container element passed
    if (arg1 instanceof HTMLElement) {
        arg1.style.display = 'block';
        return;
    }
    // Case 2: (suggestionsArray, typeString)
    if (Array.isArray(arg1) && typeof arg2 === 'string') {
        const suggestions = arg1;
        const type = arg2;
        const container = document.getElementById(type + 'Suggestions');
        if (!container) return;
        container.innerHTML = '';
        suggestions.forEach(suggestion => {
            const item = document.createElement('div');
            item.className = 'suggestion-item';
            item.textContent = suggestion.properties.formatted;
            item.addEventListener('click', () => {
                // Delegate to whichever select handler exists
                if (typeof selectSuggestion === 'function') {
                    selectSuggestion(suggestion, type);
                } else if (typeof selectAddress === 'function') {
                    selectAddress(suggestion, type);
                }
                hideSuggestions(type);
            });
            container.appendChild(item);
        });
        container.style.display = 'block';
    }
}

function hideSuggestions(arg) {
    // Case 1: Container element
    if (arg instanceof HTMLElement) {
        arg.style.display = 'none';
        return;
    }
    // Case 2: Type string
    if (typeof arg === 'string') {
        const container = document.getElementById(arg + 'Suggestions');
        if (container) container.style.display = 'none';
    }
}
// ---------------------------------------------------------------------------

// Initialize map
let map = L.map('map').setView([-26.2041, 28.0473], 6); // South Africa center

// Add tile layer
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: 'OpenStreetMap contributors'
}).addTo(map);

// Initialize address autocomplete for start address
function initializeStartAutocomplete() {
    const startInput = document.getElementById('startAddress');
    const startSuggestions = document.getElementById('startSuggestions');
    
    startInput.addEventListener('input', function() {
        clearTimeout(startDebounceTimer);
        const query = this.value.trim();
        
        if (query.length < 3) {
            hideSuggestions(startSuggestions);
            return;
        }
        
        startDebounceTimer = setTimeout(() => {
            fetchSuggestions(query, startSuggestions, 'start');
        }, 300);
    });
    
    startInput.addEventListener('blur', function() {
        setTimeout(() => hideSuggestions(startSuggestions), 200);
    });
}

// Initialize address autocomplete for end address
function initializeEndAutocomplete() {
    const endInput = document.getElementById('endAddress');
    const endSuggestions = document.getElementById('endSuggestions');
    
    endInput.addEventListener('input', function() {
        clearTimeout(endDebounceTimer);
        const query = this.value.trim();
        
        if (query.length < 3) {
            hideSuggestions(endSuggestions);
            return;
        }
        
        endDebounceTimer = setTimeout(() => {
            fetchSuggestions(query, endSuggestions, 'end');
        }, 300);
    });
    
    endInput.addEventListener('blur', function() {
        setTimeout(() => hideSuggestions(endSuggestions), 200);
    });
}

// Fetch address suggestions
async function fetchSuggestions(query, suggestionsContainer, type, stopIndex = null) {
    try {
        const response = await fetch(`https://api.geoapify.com/v1/geocode/autocomplete?text=${encodeURIComponent(query)}&apiKey=${apiKey}&limit=5&filter=countrycode:za`);
        const data = await response.json();
        displaySuggestions(data.features || [], suggestionsContainer, type, stopIndex);
    } catch (error) {
        console.error('Error fetching suggestions:', error);
    }
}

// Display suggestions
function displaySuggestions(suggestions, container, type, stopIndex = null) {
    container.innerHTML = '';
    
    if (suggestions.length === 0) {
        hideSuggestions(container);
        return;
    }
    
    suggestions.forEach((suggestion) => {
        const item = document.createElement('div');
        item.className = 'px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0';
        item.innerHTML = `
            <div class="font-medium text-gray-900">${suggestion.properties.formatted}</div>
            ${suggestion.properties.address_line2 ? `<div class="text-sm text-gray-600">${suggestion.properties.address_line2}</div>` : ''}
        `;
        
        item.addEventListener('click', () => {
            selectSuggestion(suggestion, type, stopIndex);
        });
        
        container.appendChild(item);
    });
    
    showSuggestions(container);
}

// Select a suggestion
function selectSuggestion(suggestion, type, stopIndex = null) {
    let input, suggestionsContainer;
    
    if (type === 'stop') {
        input = document.getElementById(`stop_${stopIndex}`);
        suggestionsContainer = document.getElementById(`stop_${stopIndex}_suggestions`);
    } else {
        const inputId = type === 'start' ? 'startAddress' : 'endAddress';
        input = document.getElementById(inputId);
        suggestionsContainer = document.getElementById(type + 'Suggestions');
    }
    
    input.value = suggestion.properties.formatted;
    hideSuggestions(suggestionsContainer);
    
    // Add marker to map
    const [longitude, latitude] = suggestion.geometry.coordinates;
    
    if (type === 'start') {
        // Handle start marker logic here if needed
        console.log('Start location selected:', suggestion.properties.formatted);
    } else if (type === 'end') {
        // Handle end marker logic here if needed
        console.log('End location selected:', suggestion.properties.formatted);
    }
}

// Show/hide suggestions
function showSuggestions(container) {
    container.classList.remove('hidden');
}

function hideSuggestions(container) {
    container.classList.add('hidden');
}

// Bottom sheet variables
let bottomSheet;
let isDragging = false;
let startY = 0;
let startTime = 0;
let currentTranslateY = 0;
const sheetStates = {
    collapsed: window.innerHeight - 80, // Show only the drag handle
    peek: window.innerHeight - 120,
    expanded: 0
};
let currentState = 'expanded';

// Initialize Bottom Sheet
function initBottomSheet() {
    bottomSheet = document.getElementById('bottomSheet');
    const dragHandle = document.getElementById('toggleSheet'); // Now the drag handle has the toggleSheet ID
    
    // Touch events for mobile
    dragHandle.addEventListener('touchstart', handleTouchStart, { passive: false });
    dragHandle.addEventListener('touchmove', handleTouchMove, { passive: false });
    dragHandle.addEventListener('touchend', handleTouchEnd, { passive: false });
    
    // Mouse events for desktop
    dragHandle.addEventListener('mousedown', handleMouseStart);
    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', handleMouseEnd);
    
    // Toggle button (same element as drag handle)
    dragHandle.addEventListener('click', toggleSheet);
    
    // Set initial position to expanded
    setSheetPosition('expanded');
}

// Touch/Mouse event handlers
function handleTouchStart(e) {
    isDragging = true;
    startY = e.touches[0].clientY;
    startTime = Date.now();
    bottomSheet.style.transition = 'none';
}

function handleTouchMove(e) {
    if (!isDragging) return;
    e.preventDefault();
    
    const currentY = e.touches[0].clientY;
    const deltaY = currentY - startY;
    const newTranslateY = Math.max(0, Math.min(sheetStates.collapsed, currentTranslateY + deltaY));
    
    bottomSheet.style.transform = `translateY(${newTranslateY}px)`;
}

function handleTouchEnd(e) {
    if (!isDragging) return;
    isDragging = false;
    
    const currentY = e.changedTouches[0].clientY;
    const deltaY = currentY - startY;
    const timeDiff = Date.now() - startTime;
    const velocity = timeDiff > 0 ? deltaY / timeDiff : 0;
    
    snapToNearestState(deltaY, velocity);
}

function handleMouseStart(e) {
    isDragging = true;
    startY = e.clientY;
    startTime = Date.now();
    bottomSheet.style.transition = 'none';
    e.preventDefault();
}

function handleMouseMove(e) {
    if (!isDragging) return;
    
    const deltaY = e.clientY - startY;
    const newTranslateY = Math.max(0, Math.min(sheetStates.collapsed, currentTranslateY + deltaY));
    
    bottomSheet.style.transform = `translateY(${newTranslateY}px)`;
}

function handleMouseEnd(e) {
    if (!isDragging) return;
    isDragging = false;
    
    const deltaY = e.clientY - startY;
    const timeDiff = Date.now() - startTime;
    const velocity = timeDiff > 0 ? deltaY / timeDiff : 0;
    snapToNearestState(deltaY, velocity);
}

// Snap to nearest state
function snapToNearestState(deltaY, velocity) {
    bottomSheet.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
    
    // Get current position
    const currentPos = currentTranslateY + deltaY;
    
    // Determine which state to snap to based on position and velocity
    if (velocity > 0.3 || (velocity > 0 && deltaY > 50)) {
        // Swipe down - collapse
        setSheetPosition('collapsed');
    } else if (velocity < -0.3 || (velocity < 0 && deltaY < -50)) {
        // Swipe up - expand
        setSheetPosition('expanded');
    } else {
        // Snap to nearest state based on position
        const expandedDist = Math.abs(currentPos - sheetStates.expanded);
        const peekDist = Math.abs(currentPos - sheetStates.peek);
        const collapsedDist = Math.abs(currentPos - sheetStates.collapsed);
        
        if (expandedDist < peekDist && expandedDist < collapsedDist) {
            setSheetPosition('expanded');
        } else if (peekDist < collapsedDist) {
            setSheetPosition('peek');
        } else {
            setSheetPosition('collapsed');
        }
    }
}

// Set sheet position
function setSheetPosition(state) {
    console.log('Setting sheet position to:', state);
    currentState = state;
    currentTranslateY = sheetStates[state];
    bottomSheet.style.transform = `translateY(${currentTranslateY}px)`;
    
    console.log('Sheet transform:', bottomSheet.style.transform);
    console.log('Sheet classes before:', bottomSheet.className);
    
    // Update toggle button icon (with null check)
    const toggleIcon = document.querySelector('#toggleSheet i');
    if (toggleIcon) {
        if (state === 'expanded') {
            toggleIcon.className = 'fas fa-chevron-down';
        } else if (state === 'collapsed') {
            toggleIcon.className = 'fas fa-chevron-up';
        } else {
            toggleIcon.className = 'fas fa-chevron-up';
        }
    }
    
    // Update sheet classes
    if (state === 'expanded') {
        bottomSheet.classList.add('expanded');
        bottomSheet.classList.remove('collapsed');
    } else if (state === 'collapsed') {
        bottomSheet.classList.add('collapsed');
        bottomSheet.classList.remove('expanded');
    } else {
        bottomSheet.classList.remove('expanded', 'collapsed');
    }
    
    console.log('Sheet classes after:', bottomSheet.className);
}

// Toggle sheet between expanded and collapsed
function toggleSheet() {
    if (currentState === 'expanded') {
        setSheetPosition('collapsed');
    } else {
        setSheetPosition('expanded');
    }
}

// Get current location for start address
function getCurrentLocationForStart() {
    const button = event.target.closest('i');
    const originalClass = button.className;
    
    // Show loading state
    button.className = 'fas fa-spinner fa-spin input-icon';
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            async (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                try {
                    // Reverse geocode to get address
                    const response = await fetch(`https://api.geoapify.com/v1/geocode/reverse?lat=${lat}&lon=${lng}&apiKey=${apiKey}`);
                    const data = await response.json();
                    
                    if (data.features && data.features.length > 0) {
                        const address = data.features[0].properties.formatted;
                        document.getElementById('startAddress').value = address;
                        
                        // Add marker to map
                        if (startMarker) map.removeLayer(startMarker);
                        
                        const startIcon = L.divIcon({
                            html: '<div style="background-color: #0e2140; color: white; width: 35px; height: 35px; border-radius: 50%; border: 3px solid white; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"><i class="fas fa-play"></i></div>',
                            iconSize: [35, 35],
                            className: 'start-marker'
                        });
                        
                        startMarker = L.marker([lat, lng], { icon: startIcon })
                            .addTo(map)
                            .bindPopup('Start (Current Location): ' + address);
                        
                        map.setView([lat, lng], 15);
                    }
                } catch (error) {
                    console.error('Error reverse geocoding:', error);
                    alert('Could not get address for current location');
                }
                
                // Reset button state
                button.className = originalClass;
            },
            (error) => {
                console.error('Error getting location:', error);
                alert('Could not get your current location. Please check your location settings.');
                button.className = originalClass;
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    } else {
        alert('Geolocation is not supported by this browser.');
        button.className = originalClass;
    }
}

// Get current location for end address
function getCurrentLocationForEnd() {
    const button = event.target.closest('i');
    const originalClass = button.className;
    
    // Show loading state
    button.className = 'fas fa-spinner fa-spin input-icon';
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            async (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                try {
                    // Reverse geocode to get address
                    const response = await fetch(`https://api.geoapify.com/v1/geocode/reverse?lat=${lat}&lon=${lng}&apiKey=${apiKey}`);
                    const data = await response.json();
                    
                    if (data.features && data.features.length > 0) {
                        const address = data.features[0].properties.formatted;
                        document.getElementById('endAddress').value = address;
                        
                        // Add marker to map
                        if (endMarker) map.removeLayer(endMarker);
                        
                        const endIcon = L.divIcon({
                            html: '<div style="background-color: #0e2140; color: white; width: 35px; height: 35px; border-radius: 50%; border: 3px solid white; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"><i class="fas fa-stop"></i></div>',
                            iconSize: [35, 35],
                            className: 'end-marker'
                        });
                        
                        endMarker = L.marker([lat, lng], { icon: endIcon })
                            .addTo(map)
                            .bindPopup('End (Current Location): ' + address);
                        
                        map.setView([lat, lng], 15);
                        
                        // If we have both start and end, plan route
                        if (startMarker && endMarker) {
                            setTimeout(() => planRoute(), 100);
                        }
                    }
                } catch (error) {
                    console.error('Error reverse geocoding:', error);
                    alert('Could not get address for current location');
                }
                
                // Reset button state
                button.className = originalClass;
            },
            (error) => {
                console.error('Error getting location:', error);
                alert('Could not get your current location. Please check your location settings.');
                button.className = originalClass;
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    } else {
        alert('Geolocation is not supported by this browser.');
        button.className = originalClass;
    }
}

// Repeat a saved route
function repeatRoute(routeId) {
    // Fetch route data and populate the form
    fetch(`/routes/get/${routeId}`)
        .then(response => response.json())
        .then(route => {
            if (route.success) {
                const data = route.data;
                
                // Populate form fields
                document.getElementById('startAddress').value = data.start_address;
                document.getElementById('endAddress').value = data.end_address;
                
                // Clear existing markers
                clearRoute();
                
                // Add start marker
                if (data.start_latitude && data.start_longitude) {
                    const startIcon = L.divIcon({
                        html: '<div style="background-color: #0e2140; color: white; width: 35px; height: 35px; border-radius: 50%; border: 3px solid white; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"><i class="fas fa-play"></i></div>',
                        iconSize: [35, 35],
                        className: 'start-marker'
                    });
                    
                    startMarker = L.marker([data.start_latitude, data.start_longitude], { icon: startIcon })
                        .addTo(map)
                        .bindPopup('Start: ' + data.start_address);
                }
                
                // Add end marker
                if (data.end_latitude && data.end_longitude) {
                    const endIcon = L.divIcon({
                        html: '<div style="background-color: #0e2140; color: white; width: 35px; height: 35px; border-radius: 50%; border: 3px solid white; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"><i class="fas fa-stop"></i></div>',
                        iconSize: [35, 35],
                        className: 'end-marker'
                    });
                    
                    endMarker = L.marker([data.end_latitude, data.end_longitude], { icon: endIcon })
                        .addTo(map)
                        .bindPopup('End: ' + data.end_address);
                }
                
                // Add stops if any
                if (data.stops && data.stops.length > 0) {
                    data.stops.forEach((stop, index) => {
                        if (stop.latitude && stop.longitude) {
                            const stopIcon = L.divIcon({
                                html: `<div style="background-color: #f59e0b; color: white; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; box-shadow: 0 2px 6px rgba(0,0,0,0.3);">${index + 1}</div>`,
                                iconSize: [30, 30],
                                className: 'stop-marker'
                            });
                            
                            const stopMarker = L.marker([stop.latitude, stop.longitude], { icon: stopIcon })
                                .addTo(map)
                                .bindPopup(`Stop ${index + 1}: ${stop.address}`);
                            
                            // Add to stops array
                            stops.push({
                                marker: stopMarker,
                                address: stop.address,
                                element: null // Will be created when adding to UI
                            });
                        }
                    });
                    
                    // Update stops UI
                    updateStopsUI();
                }
                
                // Draw route if polyline data exists
                if (data.polyline_data) {
                    try {
                        const polylineData = JSON.parse(data.polyline_data);
                        if (polylineData && polylineData.coordinates) {
                            currentRoute = L.polyline(polylineData.coordinates, {
                                color: '#3b82f6',
                                weight: 5,
                                opacity: 0.8,
                                lineJoin: 'round'
                            }).addTo(map);
                            
                            // Fit map to route
                            map.fitBounds(currentRoute.getBounds(), { padding: [20, 20] });
                        }
                    } catch (e) {
                        console.error('Error parsing polyline data:', e);
                        // Fallback to planning new route
                        setTimeout(() => planRoute(), 500);
                    }
                } else {
                    // Plan route if no polyline data
                    setTimeout(() => planRoute(), 500);
                }
                
                // Collapse bottom sheet to show more map
                setSheetPosition('collapsed');
            } else {
                alert('Could not load route: ' + (route.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error loading route:', error);
            alert('Could not load route. Please try again.');
        });
}

// Center map on user location
function centerOnUser() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                map.setView([lat, lng], 15);
                
                // Add or update user location marker
                if (window.userLocationMarker) {
                    map.removeLayer(window.userLocationMarker);
                }
                
                window.userLocationMarker = L.marker([lat, lng], {
                    icon: L.divIcon({
                        html: '<div style="background-color: #3b82f6; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>',
                        iconSize: [20, 20],
                        className: 'user-location-marker'
                    })
                }).addTo(map).bindPopup('Your Location');
            },
            (error) => {
                alert('Unable to get your location. Please check your location settings.');
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    } else {
        alert('Geolocation is not supported by this browser.');
    }
}

// Route planning variables
let startMarker, endMarker, routeControl;
let stops = [];
let currentRoute = null;
let routePolyline = null;
let waypoints = [];

// Merchant data from PHP (using business_locations to include branches)
const merchantData = <?= json_encode($business_locations ?? []) ?>;
const geoapifyApiKey = '<?= esc($geoapify_api_key ?? '') ?>';
let merchantMarkers = [];

// Route colors
const routeColors = {
    main: '#2563eb', // Nice blue color
    alternative: '#94a3b8'
};

// Debug API key
console.log('API Key loaded:', apiKey ? 'Yes' : 'No');
console.log('API Key length:', apiKey ? apiKey.length : 0);

// Add merchant markers to map
function addMerchantMarkers() {
    console.log('Adding business location markers to routes map...');
    let addedCount = 0;

    merchantData.forEach((location, index) => {
        console.log(`Processing location ${index + 1}:`, {
            id: location.id,
            name: location.location_name,
            business: location.business_name,
            latitude: location.latitude,
            longitude: location.longitude,
            is_active: location.is_active
        });

        // Only add locations with valid coordinates and active status
        if (location.latitude != null && location.longitude != null && location.is_active) {
            const lat = parseFloat(location.latitude);
            const lng = parseFloat(location.longitude);

            // Skip if both coordinates are exactly 0 (invalid location)
            if (lat === 0 && lng === 0) {
                console.log(`Skipped location ${location.location_name || 'Unknown'} - coordinates are 0,0 (invalid)`);
                return;
            }

            addedCount++;
            
            // Create custom merchant icon
            let merchantIcon;
            if (geoapifyApiKey) {
                // Use Geoapify custom icon
                merchantIcon = L.icon({
                    iconUrl: `https://api.geoapify.com/v1/icon/?type=material&color=%2316a34a&icon=store&iconSize=large&apiKey=${geoapifyApiKey}`,
                    iconSize: [31, 46],
                    iconAnchor: [15, 46],
                    popupAnchor: [0, -46]
                });
            } else {
                // Fallback to custom div icon
                merchantIcon = L.divIcon({
                    className: 'merchant-marker',
                    html: '<div class="bg-white rounded-full p-2 shadow-lg border-2 border-green-500"><i class="fas fa-store text-green-600 text-sm"></i></div>',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });
            }
            
            // Create marker
            const marker = L.marker([lat, lng], {
                icon: merchantIcon
            }).addTo(map);

            // Store location data on marker for filtering
            marker.locationData = location;

            // Create popup content
            const locationName = location.location_name || location.business_name;
            const businessName = location.business_name;
            const merchantId = location.merchant_id;
            const isPrimary = location.is_primary == 1;
            const branchBadge = isPrimary ? '<span class="bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded-full ml-2">Primary</span>' : '<span class="bg-gray-100 text-gray-800 text-xs px-2 py-0.5 rounded-full ml-2">Branch</span>';

            const isAlreadyAdded = stops.some(stop => stop.locationId === location.id);
            const addButtonClass = isAlreadyAdded ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-500 hover:bg-blue-600';
            const addButtonText = isAlreadyAdded ? 'Already Added' : 'Add Stop';
            const addButtonDisabled = isAlreadyAdded ? 'disabled' : '';

            const popupContent = `
                <div class="p-2">
                    <h3 class="font-bold text-gray-800 mb-1">${locationName}${branchBadge}</h3>
                    <p class="text-xs text-gray-600 mb-2">${businessName}</p>
                    <p class="text-sm text-gray-600 mb-2">${location.physical_address || 'Address not available'}</p>
                    <div class="flex space-x-2">
                        <a href="<?= base_url('driver/location/') ?>${location.id}"
                           style="background-color: #2f855a; color: white; padding: 0.25rem 0.75rem; border-radius: 0.25rem; font-size: 0.75rem; transition: background-color 0.2s;"
                           onmouseover="this.style.backgroundColor='#276749'"
                           onmouseout="this.style.backgroundColor='#2f855a'">
                            View Branch
                        </a>
                        <button onclick="addLocationToRoute(${location.id}, '${locationName.replace(/'/g, "\\'")}', ${lat}, ${lng})"
                                class="${addButtonClass} text-white px-3 py-1 rounded text-xs transition-colors" ${addButtonDisabled}>
                            ${addButtonText}
                        </button>
                    </div>
                </div>
            `;

            marker.bindPopup(popupContent);
            merchantMarkers.push(marker);

            console.log(`Added location marker for ${locationName} at [${lat}, ${lng}]`);
        } else {
            console.log(`Skipped location ${location.location_name || 'Unknown'} - missing coordinates or not active`);
        }
    });

    console.log(`Total location markers added: ${addedCount}`);
}

// Add location to route as a stop
function addLocationToRoute(locationId, locationName, lat, lng) {
    // Check if this location is already added as a stop
    const existingStop = stops.find(stop => stop.locationId === locationId);
    if (existingStop) {
        alert('This location is already added as a stop in your route.');
        return;
    }

    // Add a new stop with the location
    const stopIndex = stops.length;
    const stopContainer = document.getElementById('stopsContainer');

    const stopDiv = document.createElement('div');
    stopDiv.className = 'relative';
    stopDiv.innerHTML = `
        <div class="flex items-center space-x-3 p-3 border border-gray-300 rounded-lg">
            <i class="fas fa-map-marker-alt text-green-500"></i>
            <input type="text" id="stop_${stopIndex}" value="${locationName}" class="flex-1 outline-none text-gray-700" readonly>
            <button type="button" onclick="removeStop(this)" class="text-red-500 hover:text-red-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

    stopContainer.appendChild(stopDiv);

    // Add marker to map
    const stopNumber = stopIndex + 1;
    const stopIcon = L.divIcon({
        className: 'custom-stop-marker',
        html: `<div style="background-color: #f59e0b; color: white; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">${stopNumber}</div>`,
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    });

    const stopMarker = L.marker([lat, lng], { icon: stopIcon })
        .addTo(map)
        .bindPopup(`Stop ${stopNumber}: ${locationName}`);

    stops.push({
        element: stopDiv,
        input: document.getElementById(`stop_${stopIndex}`),
        marker: stopMarker,
        locationId: locationId,
        address: locationName,
        lat: lat,
        lng: lng
    });

    // If we have start and end markers, plan the route automatically
    if (startMarker && endMarker) {
        setTimeout(() => planRoute(), 100);
    }

    // Show success message
    const successMessage = document.createElement('div');
    successMessage.className = 'fixed top-4 right-4 text-white px-4 py-2 rounded-lg shadow-lg z-50';
    successMessage.style.backgroundColor = '#2f855a'; // Dashboard green
    successMessage.innerHTML = `<i class="fas fa-check mr-2"></i>Added ${locationName} as stop ${stopNumber}`;
    document.body.appendChild(successMessage);
    
    // Remove success message after 3 seconds
    setTimeout(() => {
        if (successMessage.parentNode) {
            successMessage.parentNode.removeChild(successMessage);
        }
    }, 3000);
    
    console.log(`Added merchant ${merchantName} as stop ${stopNumber}`);
}

// Initialize address autocomplete for start address
function initializeStartAutocomplete() {
    const startInput = document.getElementById('startAddress');
    const startSuggestions = document.getElementById('startSuggestions');
    
    startInput.addEventListener('input', function() {
        clearTimeout(startDebounceTimer);
        const query = this.value.trim();
        
        if (query.length < 3) {
            startSuggestions.style.display = 'none';
            return;
        }
        
        startDebounceTimer = setTimeout(() => {
            fetchSuggestions(query, startSuggestions, 'start');
        }, 300);
    });
    
    startInput.addEventListener('blur', function() {
        setTimeout(() => startSuggestions.style.display = 'none', 200);
    });
}

// Initialize address autocomplete for end address
function initializeEndAutocomplete() {
    const endInput = document.getElementById('endAddress');
    const endSuggestions = document.getElementById('endSuggestions');
    
    endInput.addEventListener('input', function() {
        clearTimeout(endDebounceTimer);
        const query = this.value.trim();
        
        if (query.length < 3) {
            endSuggestions.style.display = 'none';
            return;
        }
        
        endDebounceTimer = setTimeout(() => {
            fetchSuggestions(query, endSuggestions, 'end');
        }, 300);
    });
    
    endInput.addEventListener('blur', function() {
        setTimeout(() => endSuggestions.style.display = 'none', 200);
    });
}

// Fetch address suggestions
async function fetchSuggestions(query, suggestionsContainer, type, stopIndex = null) {
    try {
        const response = await fetch(`https://api.geoapify.com/v1/geocode/autocomplete?text=${encodeURIComponent(query)}&apiKey=${apiKey}&limit=5&filter=countrycode:za`);
        const data = await response.json();
        displaySuggestions(data.features || [], suggestionsContainer, type, stopIndex);
    } catch (error) {
        console.error('Error fetching suggestions:', error);
    }
}

// Display suggestions
function displaySuggestions(suggestions, container, type, stopIndex = null) {
    container.innerHTML = '';
    
    if (suggestions.length === 0) {
        container.style.display = 'none';
        return;
    }
    
    suggestions.forEach((suggestion) => {
        const item = document.createElement('div');
        item.className = 'px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0';
        item.innerHTML = `
            <div class="font-medium text-gray-900">${suggestion.properties.formatted}</div>
            ${suggestion.properties.address_line2 ? `<div class="text-sm text-gray-600">${suggestion.properties.address_line2}</div>` : ''}
        `;
        
        item.addEventListener('click', () => {
            selectSuggestion(suggestion, type, stopIndex);
        });
        
        container.appendChild(item);
    });
    
    container.style.display = 'block';
}

// Select a suggestion
function selectSuggestion(suggestion, type, stopIndex = null) {
    let input, suggestionsContainer;
    
    if (type === 'stop') {
        input = document.getElementById(`stop_${stopIndex}`);
        suggestionsContainer = document.getElementById(`stop_${stopIndex}_suggestions`);
    } else {
        const inputId = type === 'start' ? 'startAddress' : 'endAddress';
        input = document.getElementById(inputId);
        suggestionsContainer = document.getElementById(type + 'Suggestions');
    }
    
    input.value = suggestion.properties.formatted;
    suggestionsContainer.style.display = 'none';
    
    // Add marker to map
    const [longitude, latitude] = suggestion.geometry.coordinates;
    
    if (type === 'start') {
        if (startMarker) {
            map.removeLayer(startMarker);
        }
        
        // Create custom start marker icon
        const startIcon = L.divIcon({
            className: 'custom-start-marker',
            html: `<div style="background-color: #0e2140; color: white; border-radius: 50%; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.4);"><i class="fas fa-play" style="font-size: 14px;"></i></div>`,
            iconSize: [35, 35],
            iconAnchor: [17.5, 17.5]
        });
        
        startMarker = L.marker([latitude, longitude], { icon: startIcon })
            .addTo(map)
            .bindPopup('Start: ' + suggestion.properties.formatted);
        
        map.setView([latitude, longitude], 13);
    } else if (type === 'end') {
        if (endMarker) {
            map.removeLayer(endMarker);
        }
        
        // Create custom end marker icon
        const endIcon = L.divIcon({
            className: 'custom-end-marker',
            html: `<div style="background-color: #0e2140; color: white; border-radius: 50%; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.4);"><i class="fas fa-stop" style="font-size: 14px;"></i></div>`,
            iconSize: [35, 35],
            iconAnchor: [17.5, 17.5]
        });
        
        endMarker = L.marker([latitude, longitude], { icon: endIcon })
            .addTo(map)
            .bindPopup('End: ' + suggestion.properties.formatted);
        
        // If we have both start and end, plan the route automatically
        if (startMarker && endMarker) {
            setTimeout(() => planRoute(), 100); // Small delay to ensure DOM is updated
        }
    } else if (type === 'stop') {
        // Handle stop marker
        if (stops[stopIndex] && stops[stopIndex].marker) {
            map.removeLayer(stops[stopIndex].marker);
        }
        
        // Create a custom icon for stops with order number
        const stopNumber = stopIndex + 1;
        const stopIcon = L.divIcon({
            className: 'custom-stop-marker',
            html: `<div style="background-color: #f59e0b; color: white; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">${stopNumber}</div>`,
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        });
        
        const stopMarker = L.marker([latitude, longitude], { icon: stopIcon })
            .addTo(map)
            .bindPopup(`Stop ${stopNumber}: ${suggestion.properties.formatted}`);
        
        if (stops[stopIndex]) {
            stops[stopIndex].marker = stopMarker;
        }
        
        // If we have start, end, and this stop, plan the route automatically
        if (startMarker && endMarker) {
            setTimeout(() => planRoute(), 100); // Small delay to ensure DOM is updated
        }
    }
}

// Show/hide suggestions
function showSuggestions(container) {
    container.classList.remove('hidden');
}

function hideSuggestions(container) {
    container.classList.add('hidden');
}

// Get current location functionality
function initializeCurrentLocationButton() {
    const getCurrentLocationBtn = document.getElementById('getCurrentLocationBtn');
    if (!getCurrentLocationBtn) {
        console.error('getCurrentLocationBtn not found');
        return;
    }
    
    getCurrentLocationBtn.addEventListener('click', function() {
        const button = this;
        let icon = button.querySelector('i');
        
        // If icon not found, try to find it differently or create it
        if (!icon) {
            console.warn('Icon not found in getCurrentLocationBtn, trying to create one');
            icon = document.createElement('i');
            icon.className = 'fas fa-crosshairs text-lg';
            button.appendChild(icon);
        }
        
        // Show loading state
        const originalClass = icon.className;
        icon.className = 'fas fa-spinner fa-spin text-lg';
        button.disabled = true;
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            async function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                try {
                    // Reverse geocode to get address
                    const response = await fetch(`https://api.geoapify.com/v1/geocode/reverse?lat=${lat}&lon=${lng}&apiKey=${apiKey}`);
                    const data = await response.json();
                    
                    if (data.features && data.features.length > 0) {
                        const address = data.features[0].properties.formatted;
                        document.getElementById('startAddress').value = address;
                        
                        // Add marker to map with custom start icon
                        if (startMarker) {
                            map.removeLayer(startMarker);
                        }
                        
                        // Create custom start marker icon
                        const startIcon = L.divIcon({
                            className: 'custom-start-marker',
                            html: `<div style="background-color: #0e2140; color: white; border-radius: 50%; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.4);"><i class="fas fa-play" style="font-size: 14px;"></i></div>`,
                            iconSize: [35, 35],
                            iconAnchor: [17.5, 17.5]
                        });
                        
                        startMarker = L.marker([lat, lng], { icon: startIcon })
                            .addTo(map)
                            .bindPopup('Start (Current Location): ' + address);
                        map.setView([lat, lng], 15);
                    }
                } catch (error) {
                    console.error('Error reverse geocoding:', error);
                    alert('Could not get address for current location');
                }
                
                // Reset button state
                icon.className = originalClass || 'fas fa-crosshairs text-lg';
                button.disabled = false;
            },
            function(error) {
                console.error('Error getting location:', error);
                alert('Could not get your current location. Please check your location settings.');
                
                // Reset button state
                icon.className = originalClass || 'fas fa-crosshairs text-lg';
                button.disabled = false;
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000
            }
        );
    } else {
        alert('Geolocation is not supported by this browser.');
        
        // Reset button state
        icon.className = originalClass || 'fas fa-crosshairs text-lg';
        button.disabled = false;
    }
    });
}

// Update notification count in header
function updateHeaderNotificationCount() {
    fetch('<?= base_url('notifications/count') ?>', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const notificationCount = document.getElementById('notificationCount');
            const bottomNavBadge = document.getElementById('bottomNavNotificationBadge');
            
            if (data.unread_count > 0) {
                // Update header badge
                if (notificationCount) {
                    notificationCount.textContent = data.unread_count;
                    notificationCount.classList.remove('hidden');
                }
                // Update bottom nav badge
                if (bottomNavBadge) {
                    bottomNavBadge.textContent = data.unread_count;
                    bottomNavBadge.classList.remove('hidden');
                }
            } else {
                // Hide both badges
                if (notificationCount) {
                    notificationCount.classList.add('hidden');
                }
                if (bottomNavBadge) {
                    bottomNavBadge.classList.add('hidden');
                }
            }
        }
    })
    .catch(error => {
        console.error('Error fetching notification count:', error);
    });
}

// Initialize notification count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateHeaderNotificationCount();
    
    // Add merchant markers to map
    addMerchantMarkers();
    
    // Initialize control buttons
    const clearRouteBtn = document.getElementById('clearRouteBtn');
    const saveRouteBtn = document.getElementById('saveRouteBtn');
    
    if (clearRouteBtn) {
        clearRouteBtn.addEventListener('click', function() {
            clearRoute();
        });
    }
    
    if (saveRouteBtn) {
        saveRouteBtn.addEventListener('click', function() {
            saveCurrentRoute();
        });
    }
    
    // Initialize Add Stop button
    const addStopBtn = document.getElementById('addStopBtn');
    if (addStopBtn) {
        addStopBtn.addEventListener('click', function() {
            addStop();
        });
    }
    
    // Initialize bottom sheet first
    initBottomSheet();
    
    // Initialize form handlers (includes autocomplete)
    initFormHandlers();
    
    // Initialize route info card handlers
    initRouteInfoHandlers();
    
    // Set bottom sheet to expanded by default
    setSheetPosition('expanded');

    // Load saved route from localStorage if exists
    setTimeout(() => {
        const routeRestored = loadRouteFromLocalStorage();
        if (routeRestored) {
            // Collapse sheet to show the restored route
            setSheetPosition('collapsed');
        } else {
            // If no saved route, auto-detect and populate the "From" field with current location
            autoPopulateStartLocation();
        }
    }, 500);

    const routePlanningForm = document.getElementById('routePlanningForm');
    routePlanningForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Planning...';

        try {
            // Just plan the route, don't save it
            await planRoute();
        } catch (error) {
            console.error('Error planning route:', error);
            alert('Error planning route. Please try again.');
        } finally {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Plan Route';
        }
    });
});

// Add stop function
function addStop() {
    const stopIndex = stops.length;
    const stopContainer = document.getElementById('stopsContainer');

    const stopDiv = document.createElement('div');
    stopDiv.className = 'relative stop-item';
    stopDiv.draggable = true;
    stopDiv.setAttribute('data-stop-index', stopIndex);
    stopDiv.innerHTML = `
        <div class="flex items-center space-x-3 p-3 border border-gray-300 rounded-lg bg-white hover:bg-gray-50 transition-colors cursor-move">
            <i class="fas fa-grip-vertical text-gray-400 cursor-grab active:cursor-grabbing" title="Drag to reorder"></i>
            <input type="text" id="stop_${stopIndex}Address" placeholder="Add stop (optional)" class="flex-1 outline-none text-gray-700">
            <button type="button" onclick="useCurrentLocationForStop(${stopIndex})" class="text-blue-500 hover:text-blue-700 p-1" title="Use current location">
                <i class="fas fa-crosshairs text-sm"></i>
            </button>
            <button type="button" onclick="removeStop(this)" class="text-red-500 hover:text-red-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="stop_${stopIndex}Suggestions" class="absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-md shadow-lg z-50 max-h-60 overflow-y-auto hidden"></div>
    `;

    stopContainer.appendChild(stopDiv);

    // Initialize autocomplete for this stop
    const stopInput = document.getElementById(`stop_${stopIndex}Address`);
    const stopSuggestions = document.getElementById(`stop_${stopIndex}Suggestions`);
    let stopDebounceTimer;

    stopInput.addEventListener('input', function() {
        clearTimeout(stopDebounceTimer);
        const query = this.value.trim();

        if (query.length < 3) {
            hideSuggestions(`stop_${stopIndex}`);
            return;
        }

        stopDebounceTimer = setTimeout(() => {
            fetchAddressSuggestions(query, `stop_${stopIndex}`);
        }, 300);
    });

    stopInput.addEventListener('blur', function() {
        setTimeout(() => hideSuggestions(`stop_${stopIndex}`), 200);
    });

    // Add drag and drop event listeners
    stopDiv.addEventListener('dragstart', handleDragStart);
    stopDiv.addEventListener('dragover', handleDragOver);
    stopDiv.addEventListener('drop', handleDrop);
    stopDiv.addEventListener('dragenter', handleDragEnter);
    stopDiv.addEventListener('dragleave', handleDragLeave);
    stopDiv.addEventListener('dragend', handleDragEnd);

    stops.push({
        element: stopDiv,
        input: stopInput,
        suggestions: stopSuggestions,
        marker: null,
        debounceTimer: stopDebounceTimer
    });
}

// Drag and drop variables
let draggedElement = null;
let draggedIndex = -1;

// Drag and drop event handlers
function handleDragStart(e) {
    draggedElement = this;
    draggedIndex = Array.from(this.parentNode.children).indexOf(this);

    this.style.opacity = '0.5';
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', this.innerHTML);

    console.log('Drag started for stop index:', draggedIndex);
}

function handleDragOver(e) {
    if (e.preventDefault) {
        e.preventDefault();
    }
    e.dataTransfer.dropEffect = 'move';
    return false;
}

function handleDragEnter(e) {
    if (this !== draggedElement) {
        this.classList.add('border-blue-500', 'border-2', 'bg-blue-50');
    }
}

function handleDragLeave(e) {
    this.classList.remove('border-blue-500', 'border-2', 'bg-blue-50');
}

function handleDrop(e) {
    if (e.stopPropagation) {
        e.stopPropagation();
    }

    if (draggedElement !== this) {
        const dropIndex = Array.from(this.parentNode.children).indexOf(this);

        console.log('Drop:', { draggedIndex, dropIndex });

        // Reorder DOM elements
        const container = this.parentNode;
        if (draggedIndex < dropIndex) {
            container.insertBefore(draggedElement, this.nextSibling);
        } else {
            container.insertBefore(draggedElement, this);
        }

        // Reorder stops array
        reorderStops();

        // Update marker numbers
        updateStopMarkers();

        // Re-plan route if we have start and end
        if (startMarker && endMarker) {
            planRoute();
        }
    }

    this.classList.remove('border-blue-500', 'border-2', 'bg-blue-50');
    return false;
}

function handleDragEnd(e) {
    this.style.opacity = '1';

    // Remove all drag-over styles
    document.querySelectorAll('.stop-item').forEach(item => {
        item.classList.remove('border-blue-500', 'border-2', 'bg-blue-50');
    });

    draggedElement = null;
    draggedIndex = -1;
}

// Reorder stops array based on DOM order
function reorderStops() {
    const stopContainer = document.getElementById('stopsContainer');
    const stopElements = Array.from(stopContainer.children);

    const newStops = [];
    stopElements.forEach((element, index) => {
        // Find the corresponding stop in the old array
        const oldStop = stops.find(stop => stop.element === element);
        if (oldStop) {
            newStops.push(oldStop);
        }
    });

    // Replace stops array
    stops = newStops;

    console.log('Stops reordered. New count:', stops.length);

    // Recalculate route if start and end are set
    if (startMarker && endMarker && stops.length > 0) {
        console.log('Stops reordered - recalculating route...');
        setTimeout(() => planRoute(), 100);
    }
}

// Update stop marker numbers and popups
function updateStopMarkers() {
    stops.forEach((stop, index) => {
        if (stop.marker) {
            // Update marker icon with new number
            stop.marker.setIcon(L.divIcon({
                html: '<div style="background-color: #f97316; color: white; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; box-shadow: 0 2px 6px rgba(0,0,0,0.3);">' + (index + 1) + '</div>',
                iconSize: [30, 30],
                className: 'stop-marker'
            }));

            // Update popup text
            const address = stop.address || stop.input.value || 'Stop ' + (index + 1);
            stop.marker.setPopupContent('Stop ' + (index + 1) + ': ' + address);
        }
    });

    console.log('Stop markers updated');
}

// Remove stop function - handles both .input-group and .relative structures
function removeStop(button) {
    if (!button) {
        console.error('removeStop: button is null');
        return;
    }

    console.log('removeStop called with:', button);
    console.log('button element type:', button.nodeName);
    console.log('button parent:', button.parentElement);

    // Try multiple approaches to find the stop container
    // The container can be either '.input-group' or '.relative' depending on which addStop function was used
    let stopDiv = null;

    // Approach 1: Look for parent with class 'input-group' (newer structure used by addStop at line 3181)
    stopDiv = button.closest('.input-group');

    // Approach 2: Look for parent with class 'relative' (older structure used by addStop at line 1962)
    if (!stopDiv) {
        stopDiv = button.closest('.relative');
    }

    // Approach 3: If button is an icon (i tag), manually traverse up the DOM tree
    if (!stopDiv && button.nodeName === 'I') {
        let current = button.parentElement;
        let levels = 0;
        while (current && !stopDiv && levels < 10) {
            if (current.classList && (current.classList.contains('input-group') || current.classList.contains('relative'))) {
                stopDiv = current;
                break;
            }
            current = current.parentElement;
            levels++;
            // Stop if we reach the body or stopsContainer
            if (!current || current === document.body || current.id === 'stopsContainer') break;
        }
    }

    if (!stopDiv) {
        console.error('removeStop: could not find stop container after all attempts');
        console.error('Element structure:', button.outerHTML);
        console.error('Looked for classes: .input-group, .relative');
        return;
    }

    console.log('Found stop container:', stopDiv);
    console.log('Container class:', stopDiv.className);

    const stopContainer = document.getElementById('stopsContainer');
    if (!stopContainer) {
        console.error('stopsContainer element not found');
        return;
    }

    const stopIndex = Array.from(stopContainer.children).indexOf(stopDiv);

    console.log('Stop index:', stopIndex);

    if (stopIndex > -1 && stops[stopIndex]) {
        // Remove marker from map if exists
        if (stops[stopIndex].marker) {
            console.log('Removing marker from map');
            map.removeLayer(stops[stopIndex].marker);
        }

        // Remove from stops array
        console.log('Removing from stops array at index:', stopIndex);
        stops.splice(stopIndex, 1);
    }

    // Remove from DOM
    console.log('Removing from DOM');
    stopDiv.remove();

    // Update stop numbers if the function exists
    if (typeof updateStopNumbers === 'function') {
        updateStopNumbers();
    }

    // Re-plan route if we have start and end markers
    if (startMarker && endMarker) {
        planRoute();
    }

    console.log('Stop removed successfully. Remaining stops:', stops.length);
}

// Use current location for stop
function useCurrentLocationForStop(stopIndex) {
    const button = document.querySelector(`button[onclick="useCurrentLocationForStop(${stopIndex})"]`);
    const icon = button.querySelector('i');
    
    // Show loading state
    icon.className = 'fas fa-spinner fa-spin text-sm';
    button.disabled = true;
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            async function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                try {
                    // Reverse geocode to get address
                    const response = await fetch(`https://api.geoapify.com/v1/geocode/reverse?lat=${lat}&lon=${lng}&apiKey=${apiKey}`);
                    const data = await response.json();
                    
                    if (data.features && data.features.length > 0) {
                        const address = data.features[0].properties.formatted;
                        document.getElementById(`stop_${stopIndex}`).value = address;
                        
                        // Add marker to map with numbered icon
                        if (stops[stopIndex] && stops[stopIndex].marker) {
                            map.removeLayer(stops[stopIndex].marker);
                        }
                        
                        // Create a custom icon for stops with order number
                        const stopNumber = stopIndex + 1;
                        const stopIcon = L.divIcon({
                            className: 'custom-stop-marker',
                            html: `<div style="background-color: #f59e0b; color: white; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">${stopNumber}</div>`,
                            iconSize: [30, 30],
                            iconAnchor: [15, 15]
                        });
                        
                        const stopMarker = L.marker([lat, lng], { icon: stopIcon })
                            .addTo(map)
                            .bindPopup(`Stop ${stopNumber}: ${address}`);
                        
                        if (stops[stopIndex]) {
                            stops[stopIndex].marker = stopMarker;
                        }
                        
                        // If we have start, end, and this stop, plan the route automatically
                        if (startMarker && endMarker) {
                            setTimeout(() => planRoute(), 100); // Small delay to ensure DOM is updated
                        }
                        
                        map.setView([lat, lng], 15);
                    }
                } catch (error) {
                    console.error('Error reverse geocoding:', error);
                    alert('Could not get address for current location');
                }
                
                // Reset button state
                icon.className = 'fas fa-crosshairs text-sm';
                button.disabled = false;
            },
            function(error) {
                console.error('Error getting location:', error);
                alert('Could not get your current location. Please check your location settings.');
                
                // Reset button state
                icon.className = 'fas fa-crosshairs text-sm';
                button.disabled = false;
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000
            }
        );
    } else {
        alert('Geolocation is not supported by this browser.');
        
        // Reset button state
        icon.className = 'fas fa-crosshairs text-sm';
        button.disabled = false;
    }
}

// Duplicate removeStop function - commented out to avoid conflicts
// This is handled by the main removeStop function at line ~2017
/*
function removeStop(button) {
    const stopDiv = button.closest('.input-group');
    const index = Array.from(stopDiv.parentNode.children).indexOf(stopDiv);

    if (index > -1) {
        // Remove marker from map
        if (stops[index] && stops[index].marker) {
            map.removeLayer(stops[index].marker);
        }

        // Remove stop from array
        stops.splice(index, 1);

        // Update UI
        updateStopsUI();

        // Re-plan route if possible
        if (startMarker && endMarker) {
            planRoute();
        }
    }
}
*/

// Update stops UI to match the stops array
function updateStopsUI() {
    const stopsContainer = document.getElementById('stopsContainer');
    if (!stopsContainer) return;
    
    // Clear existing stops UI
    stopsContainer.innerHTML = '';
    
    // Recreate stops UI based on stops array
    stops.forEach((stop, index) => {
        const stopDiv = document.createElement('div');
        stopDiv.className = 'input-group';
        stopDiv.innerHTML = `
            <label class="input-label">Stop ${index + 1}</label>
            <div class="input-with-icon">
                <input type="text" id="stop_${index}" class="input-field" placeholder="Enter stop location" value="${stop.address || ''}">
                <i class="fas fa-location-crosshairs input-icon" onclick="useCurrentLocationForStop(${index})"></i>
                <button type="button" onclick="removeStop(this)" class="remove-stop-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="stop_${index}_suggestions" class="suggestions"></div>
        `;
        
        stopsContainer.appendChild(stopDiv);
        
        // Update the stop element reference
        stop.element = stopDiv;
        stop.input = document.getElementById(`stop_${index}`);
        
        // Initialize autocomplete for this stop
        initializeStopAutocomplete(index);
    });
}

// Initialize autocomplete for a specific stop
function initializeStopAutocomplete(stopIndex) {
    const stopInput = document.getElementById(`stop_${stopIndex}`);
    const stopSuggestions = document.getElementById(`stop_${stopIndex}_suggestions`);
    
    if (!stopInput || !stopSuggestions) return;
    
    let debounceTimer;
    
    stopInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();
        
        if (query.length < 3) {
            hideSuggestions(`stop_${stopIndex}`);
            return;
        }
        
        debounceTimer = setTimeout(() => {
            fetchAddressSuggestions(query, `stop_${stopIndex}`);
        }, 300);
    });
    
    stopInput.addEventListener('blur', function() {
        setTimeout(() => hideSuggestions(`stop_${stopIndex}`), 200);
    });
}

// Enhanced plan route function with Geoapify Routing API
async function planRoute() {
    console.log('planRoute called. Markers status:', {
        startMarker: !!startMarker,
        endMarker: !!endMarker,
        startMarkerType: startMarker ? typeof startMarker : 'undefined',
        endMarkerType: endMarker ? typeof endMarker : 'undefined'
    });

    if (!startMarker || !endMarker) {
        console.error('Missing markers:', {
            startMarker: !!startMarker,
            endMarker: !!endMarker,
            startAddress: document.getElementById('startAddress')?.value,
            endAddress: document.getElementById('endAddress')?.value
        });
        alert('Please select both start and end locations from the dropdown suggestions');
        return;
    }
    
    // Check if API key is available
    if (!apiKey || apiKey.trim() === '') {
        alert('Geoapify API key is not configured. Please check your environment settings.');
        return;
    }

    console.log('API Key available:', apiKey ? 'Yes' : 'No');

    // Clear existing route polyline only (keep start/end markers)
    clearRoutePolyline();

    // Show loading state (if routeInfo element exists)
    const routeInfo = document.getElementById('routeInfo');
    if (routeInfo) {
        routeInfo.innerHTML = `
            <div class="text-center text-gray-500 py-4">
                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                <p class="text-sm">Calculating route...</p>
            </div>
        `;
        routeInfo.style.display = 'block';
    }

    console.log('Starting route calculation...');
    
    try {
        // Build waypoints array in correct sequence
        waypoints = [startMarker.getLatLng()];
        
        // Add stops as waypoints in order (include all stops with valid coordinates)
        console.log('Processing stops:', stops.length);
        const validStops = [];
        
        stops.forEach((stop, index) => {
            console.log(`Stop ${index + 1}:`, {
                hasMarker: !!stop.marker,
                hasCoordinates: !!(stop.lat && stop.lng),
                address: stop.address || (stop.input ? stop.input.value : 'no address'),
                coordinates: stop.marker ? stop.marker.getLatLng() : (stop.lat && stop.lng ? {lat: stop.lat, lng: stop.lng} : 'no coordinates')
            });
            
            // Include stops that have either markers or direct coordinates
            if (stop.marker || (stop.lat && stop.lng)) {
                const coordinates = stop.marker ? stop.marker.getLatLng() : {lat: stop.lat, lng: stop.lng};
                const address = stop.address || (stop.input ? stop.input.value : 'Stop ' + (index + 1));
                
                validStops.push({
                    index: index,
                    marker: stop.marker,
                    address: address,
                    coordinates: coordinates
                });
                waypoints.push(coordinates);
            }
        });
        
        // Add destination as final waypoint
        waypoints.push(endMarker.getLatLng());
        
        console.log('Valid stops included in route:', validStops.length);
        console.log('Total waypoints:', waypoints.length, 'Expected:', 2 + validStops.length);
        
        // Log the complete waypoint sequence for debugging
        console.log('Waypoint sequence:');
        waypoints.forEach((wp, index) => {
            let label = 'Unknown';
            if (index === 0) label = 'START';
            else if (index === waypoints.length - 1) label = 'END';
            else label = `STOP ${index}`;
            console.log(`  ${index + 1}. ${label}: ${wp.lat.toFixed(6)}, ${wp.lng.toFixed(6)}`);
        });
        
        // Validate we have at least start and end waypoints
        if (waypoints.length < 2) {
            throw new Error('Route must have at least a start and end location');
        }
        
        // Build Geoapify Routing API request (coordinates must be in lat,lng order)
        const waypointsParam = waypoints.map(wp => `${wp.lat},${wp.lng}`).join('|');
        // Request turn-by-turn instructions from Geoapify
        const routingUrl = `https://api.geoapify.com/v1/routing?waypoints=${waypointsParam}&mode=drive&details=instruction_details&lang=en&apiKey=${apiKey}`;
        
        console.log('Routing API URL:', routingUrl);
        console.log('Waypoints:', waypoints);
        
        const response = await fetch(routingUrl);
        const data = await response.json();
        
        console.log('Routing API Response:', data);
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);
        console.log('Data features:', data.features);
        console.log('Features length:', data.features ? data.features.length : 'No features');
        
        if (!response.ok) {
            console.error('API Error - Status:', response.status);
            console.error('API Error - Data:', data);
            throw new Error(`API Error: ${response.status} - ${data.message || 'Unknown error'}`);
        }
        
        if (data.features && data.features.length > 0) {
            console.log('✅ Processing route data...');
            const route = data.features[0];
            console.log('Route object:', route);
            console.log('Route geometry:', route.geometry);
            console.log('Route properties:', route.properties);
            currentRoute = route;
            
            // Draw route on map with enhanced styling
            console.log('📍 Drawing route on map...');
            console.log('Geometry type:', route.geometry.type);
            
            let allCoordinates = [];
            
            // Handle both LineString and MultiLineString geometries
            if (route.geometry.type === 'MultiLineString') {
                console.log('🔗 Processing MultiLineString with', route.geometry.coordinates.length, 'segments');
                // Concatenate all coordinate arrays from all segments
                route.geometry.coordinates.forEach((segment, index) => {
                    console.log(`Segment ${index + 1}:`, segment.length, 'points');
                    const segmentCoords = segment.map(coord => [coord[1], coord[0]]);
                    allCoordinates = allCoordinates.concat(segmentCoords);
                });
            } else if (route.geometry.type === 'LineString') {
                console.log('🔗 Processing LineString');
                allCoordinates = route.geometry.coordinates.map(coord => [coord[1], coord[0]]);
            }
            
            console.log('Total processed coordinates:', allCoordinates.length, 'points');
            console.log('First coordinate:', allCoordinates[0]);
            console.log('Last coordinate:', allCoordinates[allCoordinates.length - 1]);
            
            // Verify route connects all waypoints
            console.log('🔍 Verifying waypoint connections:');
            console.log('Start waypoint:', waypoints[0]);
            console.log('Route starts at:', allCoordinates[0]);
            console.log('End waypoint:', waypoints[waypoints.length - 1]);
            console.log('Route ends at:', allCoordinates[allCoordinates.length - 1]);
            console.log('✅ Route connects', waypoints.length, 'waypoints via', allCoordinates.length, 'coordinate points');
            
            console.log('🎨 Creating route polyline...');
            routePolyline = L.polyline(allCoordinates, {
                color: routeColors.main,
                weight: 8, // Increased weight for better visibility
                opacity: 1.0, // Full opacity
                lineJoin: 'round',
                lineCap: 'round'
            }).addTo(map);
            console.log('✅ Main polyline added to map with color:', routeColors.main);
            console.log('🔍 Polyline bounds:', routePolyline.getBounds());
            
            // Add a subtle shadow effect
            console.log('🌫️ Adding shadow polyline...');
            const shadowPolyline = L.polyline(allCoordinates, {
                color: '#000000',
                weight: 8,
                opacity: 0.2,
                lineJoin: 'round',
                lineCap: 'round'
            }).addTo(map);
            console.log('✅ Shadow polyline added to map');
            
            // Ensure the main route is on top
            routePolyline.bringToFront();
            console.log('⬆️ Route brought to front');
            
            // Fit map to show the route
            console.log('🗺️ Fitting map bounds...');
            const allMarkers = [startMarker, endMarker, ...stops.filter(s => s.marker).map(s => s.marker)];
            console.log('All markers for bounds:', allMarkers.length);
            const group = new L.featureGroup([...allMarkers, routePolyline]);
            map.fitBounds(group.getBounds().pad(0.1));
            console.log('✅ Map bounds fitted');
            
            // Visual confirmation that route is drawn and store instructions if present
            console.log('🎉 ROUTE SUCCESSFULLY DRAWN ON MAP!');
            window.__geoapifyRoute = route;

            // Display turn-by-turn directions
            displayDirections(route);

            // Display turn-by-turn directions
            displayDirections(route);

            // Update route info
            const distance = (route.properties.distance / 1000).toFixed(1);
            // Calculate duration based on our average speed (65 km/h) instead of API duration
            const duration = Math.round((parseFloat(distance) / fuelSettings.average_speed_kmh) * 60);

            // Update route info elements (if they exist)
            const routeDistanceEl = document.getElementById('routeDistance');
            if (routeDistanceEl) routeDistanceEl.textContent = `${distance} km`;

            const routeDurationEl = document.getElementById('routeDuration');
            if (routeDurationEl) {
                const hours = Math.floor(duration / 60);
                const minutes = duration % 60;
                routeDurationEl.textContent = hours > 0 ? `${hours}h ${minutes}m` : `${minutes}m`;
            }

            if (routeInfo) routeInfo.style.display = 'block';

            // Show control buttons (if they exist)
            const clearRouteBtnEl = document.getElementById('clearRouteBtn');
            if (clearRouteBtnEl) clearRouteBtnEl.style.display = 'inline-block';

            const saveRouteBtnEl = document.getElementById('saveRouteBtn');
            if (saveRouteBtnEl) saveRouteBtnEl.style.display = 'inline-block';

            console.log('Route calculated successfully:', { distance, duration });

            // Save route to localStorage for persistence across page navigation
            saveRouteToLocalStorage();
            console.log('Route saved to localStorage for persistence');

            // Collapse the bottom sheet to show the route on the map
            setSheetPosition('collapsed');
            console.log('Bottom sheet collapsed to show route');

            // Show the Remove Route button
            const removeRouteBtn = document.getElementById('removeRouteBtn');
            if (removeRouteBtn) removeRouteBtn.style.display = 'inline-block';

        } else {
            console.warn('⚠️ No route features found in API response');
            console.log('Full API response for debugging:', JSON.stringify(data, null, 2));
            
            // Check if there's an error message in the response
            if (data.error) {
                console.error('❌ API returned error:', data.error);
                throw new Error(`Routing API Error: ${data.error.message || data.error}`);
            } else {
                console.error('❌ No route found in response');
                throw new Error('No route found - this may be due to locations being too far apart or in unreachable areas');
            }
        }
        
    } catch (error) {
        console.error('Error calculating route:', error);
        
        let errorMessage = 'Unable to calculate route';
        let errorDetails = 'Please check your locations and try again';
        
        if (error.message.includes('API Error: 401')) {
            errorMessage = 'Authentication Error';
            errorDetails = 'Invalid API key. Please check your Geoapify API configuration.';
        } else if (error.message.includes('API Error: 403')) {
            errorMessage = 'Access Denied';
            errorDetails = 'API key does not have routing permissions.';
        } else if (error.message.includes('API Error: 429')) {
            errorMessage = 'Rate Limit Exceeded';
            errorDetails = 'Too many requests. Please try again in a few minutes.';
        } else if (error.message.includes('No route found')) {
            errorMessage = 'No Route Available';
            errorDetails = 'No driving route found between these locations. Try different addresses.';
        }
        
        if (routeInfo) {
            routeInfo.innerHTML = `
                <div class="text-center text-red-500 py-4">
                    <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                    <p class="font-medium text-sm">${errorMessage}</p>
                    <p class="text-xs mt-1">${errorDetails}</p>
                </div>
            `;
            routeInfo.style.display = 'block';
        }
    }
}

// Clear route polyline only (keep start/end markers for re-planning)
function clearRoutePolyline() {
    // Store reference before clearing
    const polylineToRemove = routePolyline;

    if (routePolyline) {
        map.removeLayer(routePolyline);
        routePolyline = null;
    }

    // Clear ALL route-related polylines (main route and shadow polylines)
    // We need to collect layers first to avoid modifying during iteration
    const layersToRemove = [];
    map.eachLayer(function(layer) {
        if (layer instanceof L.Polyline && layer !== polylineToRemove) {
            // Remove polylines that match route characteristics:
            // - Shadow polylines (black color with low opacity)
            // - Blue route polylines (main route color)
            if ((layer.options.color === '#000000' && layer.options.opacity === 0.2) ||
                (layer.options.color === '#3b82f6') ||
                (layer.options.color === '#2563eb')) {
                layersToRemove.push(layer);
            }
        }
    });

    // Remove collected layers
    layersToRemove.forEach(function(layer) {
        map.removeLayer(layer);
    });

    currentRoute = null;
    waypoints = [];
}

// Note: Duplicate clearRoute() function removed
// The main clearRoute() function is defined later in the file around line 3988
// That version is complete and handles all cleanup including markers, localStorage, etc.



// Save current route function
async function saveCurrentRoute() {
    if (!currentRoute || !startMarker || !endMarker) {
        alert('Please plan a route first');
        return;
    }

    const button = document.getElementById('saveRouteBtn');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    button.disabled = true;

    // Check if route was already auto-saved
    const savedRouteId = currentRoute.savedRouteId;

    if (savedRouteId) {
        // Route already exists, just toggle it to saved
        try {
            const response = await fetch(`<?= base_url('routes/toggle-saved/') ?>${savedRouteId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (result.success) {
                showNotification('Route saved successfully!', 'success');
                button.innerHTML = '<i class="fas fa-check mr-2"></i>Saved';
                button.disabled = true;
                button.classList.remove('btn-primary');
                button.classList.add('btn-success');

                // Reload after a short delay to show in saved routes
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification(result.message || 'Failed to save route', 'error');
            }
        } catch (error) {
            console.error('Error saving route:', error);
            showNotification('Error saving route. Please try again.', 'error');
        } finally {
            button.innerHTML = originalText;
            button.disabled = false;
        }
    } else {
        // Route doesn't exist yet, create it with is_saved = 1
        const routeData = {
            route_name: document.getElementById('startAddress').value + ' to ' + document.getElementById('endAddress').value,
            start_address: document.getElementById('startAddress').value,
            start_lat: startMarker.getLatLng().lat,
            start_lng: startMarker.getLatLng().lng,
            end_address: document.getElementById('endAddress').value,
            end_lat: endMarker.getLatLng().lat,
            end_lng: endMarker.getLatLng().lng,
            total_distance_km: (currentRoute.properties.distance / 1000).toFixed(2),
            estimated_duration_minutes: Math.round(((currentRoute.properties.distance / 1000) / fuelSettings.average_speed_kmh) * 60),
            route_polyline: JSON.stringify(currentRoute.geometry.coordinates),
            is_saved: 1, // Explicitly mark as saved
            stops: stops.map(stop => ({
                address: stop.address || (stop.input ? stop.input.value : ''),
                lat: stop.marker ? stop.marker.getLatLng().lat : (stop.lat || null),
                lng: stop.marker ? stop.marker.getLatLng().lng : (stop.lng || null),
                type: stop.merchantId ? 'merchant_stop' : 'waypoint',
                merchant_id: stop.merchantId || null
            })).filter(stop => stop.address && stop.lat && stop.lng)
        };

        console.log('Saving route with stops:', routeData.stops);
        console.log('Total stops being saved:', routeData.stops.length);

        try {
            const response = await fetch("<?= base_url('routes/create') ?>", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(routeData)
            });

            const responseText = await response.text();
            let result;

            try {
                result = JSON.parse(responseText);
            } catch (e) {
                console.error("Failed to parse JSON:", responseText);
                showNotification('An unexpected error occurred. The server returned an invalid response.', 'error');
                return;
            }

            if (result.success) {
                showNotification('Route saved successfully!', 'success');
                button.innerHTML = '<i class="fas fa-check mr-2"></i>Saved';
                button.disabled = true;
                button.classList.remove('btn-primary');
                button.classList.add('btn-success');

                // Store the route ID
                if (typeof currentRoute === 'object') {
                    currentRoute.savedRouteId = result.route_id;
                }

                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('Error saving route: ' + result.message, 'error');
            }
        } catch (error) {
            console.error('Error saving route:', error);
            showNotification('Error saving route. Please try again.', 'error');
        } finally {
            button.innerHTML = originalText;
            button.disabled = false;
        }
    }
}

// Repeat route function
function repeatRoute(routeId) {
    fetch(`<?= base_url('routes/get/') ?>${routeId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const route = data.route;
                document.getElementById('startAddress').value = route.start_address;
                document.getElementById('endAddress').value = route.end_address;

                // Add markers to map
                if (startMarker) map.removeLayer(startMarker);
                if (endMarker) map.removeLayer(endMarker);

                startMarker = L.marker([route.start_lat, route.start_lng])
                    .addTo(map)
                    .bindPopup('Start: ' + route.start_address);

                endMarker = L.marker([route.end_lat, route.end_lng])
                    .addTo(map)
                    .bindPopup('End: ' + route.end_address);

                planRoute();
            }
        })
        .catch(error => console.error('Error:', error));
}

// Start route function
function startRoute(routeId) {
    // This would integrate with navigation or tracking
    alert('Starting route navigation...');
}

// Note: DOMContentLoaded initialization moved to main section above

// Initialize form handlers
function initFormHandlers() {
    const routeForm = document.getElementById('routePlanningForm');
    const addStopBtn = document.getElementById('addStopBtn');
    const startInput = document.getElementById('startAddress');
    const endInput = document.getElementById('endAddress');
    
    // Form submission
    routeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        planRoute();
    });
    
    // Add stop button
    addStopBtn.addEventListener('click', addStop);
    
    // Address input handlers
    startInput.addEventListener('input', function() {
        handleAddressInput(this, 'start');
    });
    
    endInput.addEventListener('input', function() {
        handleAddressInput(this, 'end');
    });
}

// Initialize route info card handlers
function initRouteInfoHandlers() {
    // Use a flag to prevent re-initialization
    if (window.routeInfoHandlersInitialized) {
        return;
    }

    const closeBtn = document.getElementById('closeRouteInfo');
    const saveBtn = document.getElementById('saveRouteBtn');
    const clearBtn = document.getElementById('clearRouteBtn');
    const startNavBtn = document.getElementById('startNavigationBtn');

    if (!closeBtn || !saveBtn || !clearBtn) return;

    closeBtn.addEventListener('click', function() {
        const routeInfoCardEl = document.getElementById('routeInfoCard');
        if (routeInfoCardEl) routeInfoCardEl.style.display = 'none';
    });

    saveBtn.addEventListener('click', saveCurrentRoute);
    clearBtn.addEventListener('click', clearRoute);
    if (startNavBtn) {
        startNavBtn.addEventListener('click', startTurnByTurnNavigation);
    }

    window.routeInfoHandlersInitialized = true;
}
// --- Live navigation ---
let liveNav = {
    watchId: null,
    carMarker: null,
    carIcon: L.divIcon({
        html: '<div style="transform: rotate(0deg); width: 32px; height: 32px;"><svg width="32" height="32" viewBox="0 0 24 24" fill="#2563eb" xmlns="http://www.w3.org/2000/svg"><path d="M12 2l4 10-4-2-4 2 4-10z"/><circle cx="12" cy="21" r="2" fill="#1e3a8a"/></svg></div>',
        iconSize: [32, 32],
        className: 'car-icon'
    }),
    lastHeading: 0
};

function startTurnByTurnNavigation() {
    if (!routePolyline) {
        alert('Plan a route first.');
        return;
    }

    // Create or reuse car marker at start
    const coords = routePolyline.getLatLngs();
    const startLatLng = coords[0];
    if (!liveNav.carMarker) {
        liveNav.carMarker = L.marker(startLatLng, { icon: liveNav.carIcon, zIndexOffset: 1000 }).addTo(map);
    } else {
        liveNav.carMarker.setLatLng(startLatLng);
    }
    map.setView(startLatLng, Math.max(map.getZoom(), 15));

    // Show nav banner if instructions available
    const navBanner = document.getElementById('navBanner');
    const navText = document.getElementById('navText');
    const navSub = document.getElementById('navSub');
    const navClose = document.getElementById('navClose');
    if (navBanner && window.__geoapifyRoute) {
        navBanner.style.display = 'block';
        if (navClose) navClose.onclick = () => (navBanner.style.display = 'none');
    }

    // Follow device location in real-time
    if (!navigator.geolocation) {
        alert('Geolocation not supported.');
        return;
    }

    // Clear any existing watcher
    if (liveNav.watchId) {
        navigator.geolocation.clearWatch(liveNav.watchId);
        liveNav.watchId = null;
    }

    const options = { enableHighAccuracy: true, maximumAge: 1000, timeout: 15000 };
    liveNav.watchId = navigator.geolocation.watchPosition(onLivePosition, onLiveError, options);
}

function onLivePosition(pos) {
    const lat = pos.coords.latitude;
    const lng = pos.coords.longitude;
    const speedMps = typeof pos.coords.speed === 'number' ? pos.coords.speed : null;
    const headingDeg = typeof pos.coords.heading === 'number' && pos.coords.heading >= 0 ? pos.coords.heading : liveNav.lastHeading;

    const latLng = L.latLng(lat, lng);
    if (liveNav.carMarker) {
        liveNav.carMarker.setLatLng(latLng);
        // Rotate icon using CSS transform
        const el = liveNav.carMarker.getElement();
        if (el) {
            el.firstChild && (el.firstChild.style.transform = `rotate(${headingDeg}deg)`);
        }
    }

    // Keep map centered ahead of movement
    map.setView(latLng, Math.max(map.getZoom(), 15), { animate: true });

    // Update next instruction from Geoapify if available
    if (window.__geoapifyRoute && window.__geoapifyRoute.properties && window.__geoapifyRoute.properties.legs) {
        try {
            const legs = window.__geoapifyRoute.properties.legs;
            // Flatten steps/instructions across legs
            const steps = [];
            for (const leg of legs) {
                if (leg.steps) steps.push(...leg.steps);
            }
            if (steps.length) {
                // Pick the nearest upcoming step by distance to current position
                let nearest = null;
                let minDist = Infinity;
                for (const s of steps) {
                    if (!s.from) continue;
                    const sLatLng = L.latLng(s.from.location[1], s.from.location[0]);
                    const d = latLng.distanceTo(sLatLng);
                    if (d < minDist) { minDist = d; nearest = s; }
                }
                const navBanner = document.getElementById('navBanner');
                const navText = document.getElementById('navText');
                const navSub = document.getElementById('navSub');
                if (nearest && navBanner && navText && navSub) {
                    const maneuver = nearest.instruction || nearest.maneuver || 'Continue';
                    const dist = nearest.distance ? Math.round(nearest.distance) : null;
                    navText.textContent = maneuver;
                    navSub.textContent = dist ? `${dist} m` : '';
                    if (navBanner.style.display !== 'block') navBanner.style.display = 'block';
                }
            }
        } catch (e) {
            // ignore parse errors silently
        }
    }

    // Snap progress to nearest point on polyline and optionally draw progress
    // Lightweight: just ensure we remain within bounds
    if (routePolyline) {
        const bounds = routePolyline.getBounds();
        if (!bounds.contains(latLng)) {
            map.fitBounds(bounds.pad(0.15));
        }
    }

    liveNav.lastHeading = headingDeg;

    // Send location to backend periodically
    throttleUpdateLocation(lat, lng);
}

function onLiveError(err) {
    console.error('Geolocation error:', err);
}

let lastUpdateTs = 0;
async function throttleUpdateLocation(lat, lng) {
    const now = Date.now();
    if (now - lastUpdateTs < 4000) return; // 4s throttle
    lastUpdateTs = now;
    try {
        await fetch('<?= base_url('api/driver/location') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: `latitude=${lat}&longitude=${lng}`
        });
    } catch (e) {
        console.warn('Failed to update location', e);
    }
}

function stopTurnByTurnNavigation() {
    if (liveNav.watchId) {
        navigator.geolocation.clearWatch(liveNav.watchId);
        liveNav.watchId = null;
    }
}

// Display turn-by-turn directions
function displayDirections(route) {
    console.log('=== DISPLAYING DIRECTIONS ===');
    console.log('Route object:', route);
    console.log('Route properties:', route?.properties);
    console.log('Route legs:', route?.properties?.legs);

    const directionsSection = document.getElementById('directionsSection');
    const directionsList = document.getElementById('directionsList');

    if (!directionsSection || !directionsList) {
        console.error('Directions section or list not found in DOM');
        return;
    }

    if (!route || !route.properties) {
        console.log('No route or route properties available');
        directionsSection.classList.add('section-hidden');
        return;
    }

    // Clear existing directions
    directionsList.innerHTML = '';

    // Extract all steps from all legs
    const allSteps = [];

    if (route.properties.legs && Array.isArray(route.properties.legs)) {
        console.log(`Processing ${route.properties.legs.length} legs`);
        route.properties.legs.forEach((leg, legIndex) => {
            console.log(`Leg ${legIndex}:`, leg);
            console.log(`Leg ${legIndex} steps:`, leg.steps);
            if (leg.steps && Array.isArray(leg.steps) && leg.steps.length > 0) {
                console.log(`Adding ${leg.steps.length} steps from leg ${legIndex}`);
                allSteps.push(...leg.steps);
            }
        });
    } else {
        console.log('No legs array found in route.properties');
    }

    console.log(`Total steps collected: ${allSteps.length}`);

    if (allSteps.length === 0) {
        console.log('No steps found in route - showing message');
        directionsList.innerHTML = `
            <div class="text-center text-gray-500 py-4">
                <i class="fas fa-info-circle text-2xl mb-2"></i>
                <p class="text-sm">No detailed directions available for this route.</p>
            </div>
        `;
        directionsSection.classList.remove('section-hidden');
        return;
    }

    console.log(`Creating ${allSteps.length} direction items`);

    // Create direction items
    allSteps.forEach((step, index) => {
        console.log(`Step ${index + 1}:`, step);

        const stepDiv = document.createElement('div');
        stepDiv.className = 'direction-step';

        // Get instruction text - try multiple possible properties
        const instruction = step.instruction?.text ||
                          step.instruction ||
                          step.text ||
                          step.name ||
                          'Continue';

        // Get distance
        const distance = step.distance ?
            (step.distance >= 1000 ?
                `${(step.distance / 1000).toFixed(1)} km` :
                `${Math.round(step.distance)} m`) :
            '';

        // Get maneuver icon
        const maneuver = step.maneuver?.type ||
                        step.type ||
                        step.maneuver ||
                        'straight';
        const icon = getManeuverIcon(maneuver);

        stepDiv.innerHTML = `
            <div class="direction-step-number">${index + 1}</div>
            <div class="direction-step-icon">
                <i class="fas ${icon}"></i>
            </div>
            <div class="direction-step-content">
                <div class="direction-step-instruction">${instruction}</div>
                ${distance ? `<div class="direction-step-distance">${distance}</div>` : ''}
            </div>
        `;

        directionsList.appendChild(stepDiv);
    });

    // Show the directions section
    directionsSection.classList.remove('section-hidden');
    console.log('✅ Directions displayed successfully with', allSteps.length, 'steps');
}

// Get appropriate icon for maneuver type
function getManeuverIcon(maneuver) {
    const iconMap = {
        'turn-left': 'fa-arrow-left',
        'turn-right': 'fa-arrow-right',
        'turn-slight-left': 'fa-arrow-left',
        'turn-slight-right': 'fa-arrow-right',
        'turn-sharp-left': 'fa-arrow-left',
        'turn-sharp-right': 'fa-arrow-right',
        'keep-left': 'fa-arrow-left',
        'keep-right': 'fa-arrow-right',
        'uturn': 'fa-undo',
        'roundabout': 'fa-circle-notch',
        'exit-roundabout': 'fa-sign-out-alt',
        'merge': 'fa-code-branch',
        'fork': 'fa-code-branch',
        'ramp': 'fa-arrow-up',
        'arrive': 'fa-flag-checkered',
        'depart': 'fa-play',
        'straight': 'fa-arrow-up'
    };

    return iconMap[maneuver] || 'fa-arrow-up';
}

// Handle address input with autocomplete
function handleAddressInput(input, type) {
    console.log('Address input handler called:', type, input.value);
    const query = input.value.trim();
    if (query.length < 3) {
        console.log('Query too short, hiding suggestions');
        hideSuggestions(type);
        return;
    }
    
    console.log('Debouncing API call for:', query);
    // Debounce the API call
    clearTimeout(window[type + 'DebounceTimer']);
    window[type + 'DebounceTimer'] = setTimeout(() => {
        console.log('Making API call for:', query);
        fetchAddressSuggestions(query, type);
    }, 300);
}

// Fetch address suggestions
async function fetchAddressSuggestions(query, type) {
    console.log('fetchAddressSuggestions called:', { query, type, apiKeyAvailable: !!apiKey });
    
    if (!apiKey) {
        console.error('API key not available for autocomplete');
        return;
    }
    
    try {
        const url = `https://api.geoapify.com/v1/geocode/autocomplete?text=${encodeURIComponent(query)}&countrycode=za&limit=5&apiKey=${apiKey}`;
        console.log('Making request to:', url.replace(apiKey, 'HIDDEN'));
        
        const response = await fetch(url);
        const data = await response.json();
        
        console.log('API response:', data);
        
        if (data.features && data.features.length > 0) {
            showSuggestions(data.features, type);
        } else {
            console.log('No suggestions found');
            hideSuggestions(type);
        }
    } catch (error) {
        console.error('Error fetching suggestions:', error);
        hideSuggestions(type);
    }
}

// Show address suggestions
function showSuggestions(suggestions, type) {
    const containerId = type + 'Suggestions';
    const suggestionsContainer = document.getElementById(containerId);
    console.log('showSuggestions called:', { type, containerId, containerFound: !!suggestionsContainer, suggestionsCount: suggestions.length });
    
    if (!suggestionsContainer) {
        console.error('Suggestions container not found:', containerId);
        return;
    }
    
    suggestionsContainer.innerHTML = '';
    
    suggestions.forEach(suggestion => {
        const item = document.createElement('div');
        item.className = 'px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0';
        item.innerHTML = `
            <div class="font-medium text-gray-900">${suggestion.properties.formatted}</div>
            ${suggestion.properties.address_line2 ? `<div class="text-sm text-gray-600">${suggestion.properties.address_line2}</div>` : ''}
        `;
        
        item.addEventListener('click', function() {
            selectAddress(suggestion, type);
            hideSuggestions(type);
        });
        
        suggestionsContainer.appendChild(item);
    });
    
    suggestionsContainer.style.display = 'block';
    console.log('Suggestions displayed:', { containerId, itemsAdded: suggestions.length, display: suggestionsContainer.style.display });
}

// Hide suggestions
function hideSuggestions(type) {
    const suggestionsContainer = document.getElementById(type + 'Suggestions');
    if (suggestionsContainer) {
        suggestionsContainer.style.display = 'none';
    }
}

// Select address from suggestions
function selectAddress(suggestion, type) {
    console.log('selectAddress called:', { type, formatted: suggestion.properties.formatted });

    const input = document.getElementById(type + 'Address');
    const coords = suggestion.geometry.coordinates;

    if (!input) {
        console.error('Input not found for type:', type);
        return;
    }

    input.value = suggestion.properties.formatted;

    // Store coordinates for planning
    if (type === 'start') {
        window.startCoordinates = { lat: coords[1], lng: coords[0] };
        console.log('Start coordinates stored:', window.startCoordinates);
    } else if (type === 'end') {
        window.endCoordinates = { lat: coords[1], lng: coords[0] };
        console.log('End coordinates stored:', window.endCoordinates);
    }

    // Add marker to map
    if (type === 'start') {
        if (startMarker) map.removeLayer(startMarker);
        startMarker = L.marker([coords[1], coords[0]], {
            icon: L.divIcon({
                html: '<div style="background-color: #0e2140; color: white; width: 35px; height: 35px; border-radius: 50%; border: 3px solid white; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"><i class="fas fa-play"></i></div>',
                iconSize: [35, 35],
                className: 'start-marker'
            })
        }).addTo(map).bindPopup('Start: ' + suggestion.properties.formatted);
        console.log('Start marker created and added to map:', startMarker);
    } else if (type === 'end') {
        if (endMarker) map.removeLayer(endMarker);
        endMarker = L.marker([coords[1], coords[0]], {
            icon: L.divIcon({
                html: '<div style="background-color: #0e2140; color: white; width: 35px; height: 35px; border-radius: 50%; border: 3px solid white; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"><i class="fas fa-stop"></i></div>',
                iconSize: [35, 35],
                className: 'end-marker'
            })
        }).addTo(map).bindPopup('End: ' + suggestion.properties.formatted);
        console.log('End marker created and added to map:', endMarker);
    } else if (type.startsWith('stop_')) {
        // Handle stop markers
        const stopIndex = parseInt(type.replace('stop_', ''));

        if (stops[stopIndex]) {
            // Remove old marker if exists
            if (stops[stopIndex].marker) {
                map.removeLayer(stops[stopIndex].marker);
            }

            // Create new marker for stop
            const stopMarker = L.marker([coords[1], coords[0]], {
                icon: L.divIcon({
                    html: '<div style="background-color: #f97316; color: white; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; box-shadow: 0 2px 6px rgba(0,0,0,0.3);">' + (stopIndex + 1) + '</div>',
                    iconSize: [30, 30],
                    className: 'stop-marker'
                })
            }).addTo(map).bindPopup('Stop ' + (stopIndex + 1) + ': ' + suggestion.properties.formatted);

            // Update stops array
            stops[stopIndex].marker = stopMarker;
            stops[stopIndex].lat = coords[1];
            stops[stopIndex].lng = coords[0];
            stops[stopIndex].address = suggestion.properties.formatted;

            console.log('Stop marker added:', { stopIndex, lat: coords[1], lng: coords[0] });

            // Auto-recalculate route if start and end are set
            if (startMarker && endMarker) {
                console.log('Stop added - recalculating route...');
                setTimeout(() => planRoute(), 100);
            }
        }
    }

    // Auto-zoom to show all markers
    const allMarkers = [];
    if (startMarker) allMarkers.push(startMarker);
    if (endMarker) allMarkers.push(endMarker);
    stops.forEach(stop => {
        if (stop.marker) allMarkers.push(stop.marker);
    });

    if (allMarkers.length > 1) {
        const group = new L.featureGroup(allMarkers);
        map.fitBounds(group.getBounds().pad(0.1));
    } else if (allMarkers.length === 1) {
        map.setView([coords[1], coords[0]], 13);
    }
}

// Get current location for input
function getCurrentLocationForStart() {
    getCurrentLocationForInput('start');
}

function getCurrentLocationForEnd() {
    getCurrentLocationForInput('end');
}

function getCurrentLocationForInput(type) {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            async (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                try {
                    // Reverse geocode to get address
                    const response = await fetch(`https://api.geoapify.com/v1/geocode/reverse?lat=${lat}&lon=${lng}&apiKey=${apiKey}`);
                    const data = await response.json();

                    if (data.features && data.features.length > 0) {
                        const address = data.features[0].properties.formatted;
                        document.getElementById(type + 'Address').value = address;

                        // Add marker
                        const suggestion = {
                            properties: { formatted: address },
                            geometry: { coordinates: [lng, lat] }
                        };
                        selectAddress(suggestion, type);
                    }
                } catch (error) {
                    console.error('Error reverse geocoding:', error);
                    alert('Unable to get address for current location');
                }
            },
            (error) => {
                alert('Unable to get your location. Please check your location settings.');
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    } else {
        alert('Geolocation is not supported by this browser.');
    }
}

// Automatically populate the "From" field with current location on page load
function autoPopulateStartLocation() {
    // Only auto-populate if the start address field is empty
    const startAddressField = document.getElementById('startAddress');
    if (startAddressField && !startAddressField.value) {
        console.log('Auto-populating start location...');

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    try {
                        // Reverse geocode to get address
                        const response = await fetch(`https://api.geoapify.com/v1/geocode/reverse?lat=${lat}&lon=${lng}&apiKey=${apiKey}`);
                        const data = await response.json();

                        if (data.features && data.features.length > 0) {
                            const address = data.features[0].properties.formatted;
                            startAddressField.value = address;

                            // Add marker to map
                            const suggestion = {
                                properties: { formatted: address },
                                geometry: { coordinates: [lng, lat] }
                            };
                            selectAddress(suggestion, 'start');

                            console.log('Start location auto-populated:', address);
                        }
                    } catch (error) {
                        console.error('Error auto-populating start location:', error);
                        // Silently fail - don't show alert on page load
                    }
                },
                (error) => {
                    console.log('Location permission not granted or unavailable:', error);
                    // Silently fail - don't show alert on page load
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 300000 }
            );
        } else {
            console.log('Geolocation is not supported by this browser.');
        }
    }
}

// Duplicate addStop function - commented out to avoid conflicts
// This is now handled by the main addStop function at line ~1962
/*
function addStop() {
    const stopCount = stops.length + 1;
    const stopContainer = document.getElementById('stopsContainer');

    const stopDiv = document.createElement('div');
    stopDiv.className = 'input-group';
    stopDiv.innerHTML = `
        <label class="input-label">Stop ${stopCount}</label>
        <div class="input-with-icon">
            <input type="text" class="input-field stop-input" placeholder="Enter stop location">
            <i class="fas fa-times input-icon text-red-500" onclick="removeStop(this)"></i>
        </div>
        <div class="suggestions"></div>
    `;

    stopContainer.appendChild(stopDiv);

    // Add event listener for the new input
    const input = stopDiv.querySelector('.stop-input');
    input.addEventListener('input', function() {
        handleStopInput(this);
    });

    stops.push({ element: stopDiv, marker: null });
}
*/

// Duplicate removeStop function - commented out to avoid conflicts
// This is handled by the main removeStop function at line ~2017
/*
function removeStop(button) {
    const stopDiv = button.closest('.input-group');
    const index = Array.from(stopDiv.parentNode.children).indexOf(stopDiv);

    // Remove marker if exists
    if (stops[index] && stops[index].marker) {
        map.removeLayer(stops[index].marker);
    }

    // Remove from DOM and array
    stopDiv.remove();
    stops.splice(index, 1);

    // Update stop numbers
    updateStopNumbers();
}
*/

// Update stop numbers
function updateStopNumbers() {
    const stopLabels = document.querySelectorAll('#stopsContainer .input-label');
    stopLabels.forEach((label, index) => {
        label.textContent = `Stop ${index + 1}`;
    });
}

// Handle stop input
function handleStopInput(input) {
    const query = input.value.trim();
    if (query.length < 3) return;
    
    // Simple autocomplete for stops
    clearTimeout(input.debounceTimer);
    input.debounceTimer = setTimeout(() => {
        fetchStopSuggestions(query, input);
    }, 300);
}

// Fetch stop suggestions
async function fetchStopSuggestions(query, input) {
    if (!apiKey) return;
    
    try {
        const response = await fetch(`https://api.geoapify.com/v1/geocode/autocomplete?text=${encodeURIComponent(query)}&countrycode=za&limit=5&apiKey=${apiKey}`);
        const data = await response.json();
        
        if (data.features && data.features.length > 0) {
            showStopSuggestions(data.features, input);
        }
    } catch (error) {
        console.error('Error fetching stop suggestions:', error);
    }
}

// Show stop suggestions
function showStopSuggestions(suggestions, input) {
    const suggestionsContainer = input.parentNode.nextElementSibling;
    suggestionsContainer.innerHTML = '';
    
    suggestions.forEach(suggestion => {
        const item = document.createElement('div');
        item.className = 'suggestion-item';
        item.textContent = suggestion.properties.formatted;
        
        item.addEventListener('click', function() {
            selectStopAddress(suggestion, input);
            suggestionsContainer.style.display = 'none';
        });
        
        suggestionsContainer.appendChild(item);
    });
    
    suggestionsContainer.style.display = 'block';
}

// Select stop address
function selectStopAddress(suggestion, input) {
    const coords = suggestion.geometry.coordinates;
    input.value = suggestion.properties.formatted;
    
    // Find stop index
    const stopDiv = input.closest('.input-group');
    const index = Array.from(stopDiv.parentNode.children).indexOf(stopDiv);
    
    // Remove existing marker
    if (stops[index] && stops[index].marker) {
        map.removeLayer(stops[index].marker);
    }
    
    // Add new marker
    const marker = L.marker([coords[1], coords[0]], {
        icon: L.divIcon({
            html: `<div style="background-color: #f59e0b; color: white; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; box-shadow: 0 2px 6px rgba(0,0,0,0.3);">${index + 1}</div>`,
            iconSize: [30, 30],
            className: 'stop-marker'
        })
    }).addTo(map).bindPopup(`Stop ${index + 1}: ${suggestion.properties.formatted}`);
    
    stops[index].marker = marker;
}

// Plan route with simplified logic
// Duplicate planRoute function - commented out to avoid conflicts
// This is handled by the main planRoute function at line ~2410
/*
async function planRoute() {
    if (!startMarker || !endMarker) {
        alert('Please select both start and end locations');
        return;
    }

    try {
        // Build waypoints
        const waypoints = [startMarker.getLatLng()];

        // Add stops
        stops.forEach(stop => {
            if (stop.marker) {
                waypoints.push(stop.marker.getLatLng());
            }
        });

        waypoints.push(endMarker.getLatLng());

        // Call Geoapify Routing API
        const waypointsParam = waypoints.map(wp => `${wp.lat},${wp.lng}`).join('|');
        const routingUrl = `https://api.geoapify.com/v1/routing?waypoints=${waypointsParam}&mode=drive&apiKey=${apiKey}`;

        const response = await fetch(routingUrl);
        const data = await response.json();

        if (data.features && data.features.length > 0) {
            const route = data.features[0];
            currentRoute = route;

            // Draw route on map
            if (routePolyline) {
                map.removeLayer(routePolyline);
            }

            let allCoordinates = [];
            if (route.geometry.type === 'MultiLineString') {
                route.geometry.coordinates.forEach(segment => {
                    const segmentCoords = segment.map(coord => [coord[1], coord[0]]);
                    allCoordinates = allCoordinates.concat(segmentCoords);
                });
            } else if (route.geometry.type === 'LineString') {
                allCoordinates = route.geometry.coordinates.map(coord => [coord[1], coord[0]]);
            }

            routePolyline = L.polyline(allCoordinates, {
                color: '#2563eb',
                weight: 6,
                opacity: 0.8
            }).addTo(map);

            // Fit map to route
            map.fitBounds(routePolyline.getBounds().pad(0.1));

            // Show route info
            showRouteInfo(route);

            // Collapse bottom sheet to show more of the map
            setSheetPosition('collapsed');

        } else {
            alert('No route found. Please try different locations.');
        }
    } catch (error) {
        console.error('Error planning route:', error);
        alert('Error planning route. Please try again.');
    }
}
*/

// Show route info card
function showRouteInfo(route) {
    const distance = (route.properties.distance / 1000).toFixed(1);
    // Calculate duration based on our average speed (65 km/h) instead of API duration
    const duration = Math.round((parseFloat(distance) / fuelSettings.average_speed_kmh) * 60);
    const hours = Math.floor(duration / 60);
    const minutes = duration % 60;

    // Basic route info
    document.getElementById('routeDistance').textContent = distance + ' km';
    document.getElementById('routeDuration').textContent = hours > 0 ? `${hours}h ${minutes}m` : `${minutes}m`;
    
    // Route addresses
    const startAddress = document.getElementById('startAddress').value || 'Not specified';
    const endAddress = document.getElementById('endAddress').value || 'Not specified';
    document.getElementById('routeStartAddress').textContent = startAddress;
    document.getElementById('routeEndAddress').textContent = endAddress;
    
    // Number of stops
    const stopsCount = stops.length;
    document.getElementById('routeStopsCount').textContent = stopsCount;

    // Current time
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit',
        hour12: true 
    });
    document.getElementById('routeCreatedTime').textContent = timeString;

    document.getElementById('activeRouteSection').style.display = 'block';

    // Display turn-by-turn directions
    displayDirections(route);

    // Initialize handlers for the buttons
    initRouteInfoHandlers();

    // Auto-save the route to database
    autoSaveRoute(route);

    // Save route state to localStorage for persistence
    saveRouteToLocalStorage();
}

// Auto-save route function (called after planning)
async function autoSaveRoute(route) {
    if (!currentRoute || !startMarker || !endMarker) {
        return;
    }

    const routeData = {
        route_name: document.getElementById('startAddress').value + ' to ' + document.getElementById('endAddress').value,
        start_address: document.getElementById('startAddress').value,
        start_lat: startMarker.getLatLng().lat,
        start_lng: startMarker.getLatLng().lng,
        end_address: document.getElementById('endAddress').value,
        end_lat: endMarker.getLatLng().lat,
        end_lng: endMarker.getLatLng().lng,
        total_distance_km: (currentRoute.properties.distance / 1000).toFixed(2),
        estimated_duration_minutes: Math.round(((currentRoute.properties.distance / 1000) / fuelSettings.average_speed_kmh) * 60),
        route_polyline: JSON.stringify(currentRoute.geometry.coordinates),
        stops: stops.map(stop => ({
            address: stop.address || (stop.input ? stop.input.value : ''),
            lat: stop.marker ? stop.marker.getLatLng().lat : (stop.lat || null),
            lng: stop.marker ? stop.marker.getLatLng().lng : (stop.lng || null),
            type: stop.merchantId ? 'merchant_stop' : 'waypoint',
            merchant_id: stop.merchantId || null
        })).filter(stop => stop.address && stop.lat && stop.lng)
    };

    try {
        const response = await fetch("<?= base_url('routes/create') ?>", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(routeData)
        });

        const responseText = await response.text();
        let result;

        try {
            result = JSON.parse(responseText);
        } catch (e) {
            console.error("Auto-save failed to parse JSON:", responseText);
            return;
        }

        if (result.success) {
            console.log('Route auto-saved successfully with ID:', result.route_id);
            // Store the route ID for future reference
            if (typeof currentRoute === 'object') {
                currentRoute.savedRouteId = result.route_id;
            }
        } else {
            console.error('Auto-save failed:', result.message);
        }
    } catch (error) {
        console.error('Error auto-saving route:', error);
    }
}

// Save current route state to localStorage for persistence
function saveRouteToLocalStorage() {
    if (!currentRoute || !startMarker || !endMarker) {
        return;
    }

    const routeState = {
        startAddress: document.getElementById('startAddress').value,
        endAddress: document.getElementById('endAddress').value,
        startLat: startMarker.getLatLng().lat,
        startLng: startMarker.getLatLng().lng,
        endLat: endMarker.getLatLng().lat,
        endLng: endMarker.getLatLng().lng,
        routeGeometry: currentRoute.geometry,
        routeProperties: currentRoute.properties,
        stops: stops.map(stop => ({
            address: stop.address || (stop.input ? stop.input.value : ''),
            lat: stop.marker ? stop.marker.getLatLng().lat : (stop.lat || null),
            lng: stop.marker ? stop.marker.getLatLng().lng : (stop.lng || null),
            merchantId: stop.merchantId || null
        })).filter(stop => stop.address && stop.lat && stop.lng),
        timestamp: new Date().toISOString()
    };

    try {
        localStorage.setItem('truckers_active_route', JSON.stringify(routeState));
        console.log('Route state saved to localStorage');
    } catch (error) {
        console.error('Error saving route to localStorage:', error);
    }
}

// Load and restore route from localStorage on page load
function loadRouteFromLocalStorage() {
    try {
        const savedState = localStorage.getItem('truckers_active_route');
        if (!savedState) {
            return false;
        }

        const routeState = JSON.parse(savedState);

        // Populate form fields
        document.getElementById('startAddress').value = routeState.startAddress;
        document.getElementById('endAddress').value = routeState.endAddress;

        // Create start marker
        const startIcon = L.divIcon({
            html: '<div style="background-color: #0e2140; color: white; width: 35px; height: 35px; border-radius: 50%; border: 3px solid white; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"><i class="fas fa-play"></i></div>',
            iconSize: [35, 35],
            className: 'start-marker'
        });

        startMarker = L.marker([routeState.startLat, routeState.startLng], { icon: startIcon })
            .addTo(map)
            .bindPopup('Start: ' + routeState.startAddress);

        // Create end marker
        const endIcon = L.divIcon({
            html: '<div style="background-color: #0e2140; color: white; width: 35px; height: 35px; border-radius: 50%; border: 3px solid white; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"><i class="fas fa-stop"></i></div>',
            iconSize: [35, 35],
            className: 'end-marker'
        });

        endMarker = L.marker([routeState.endLat, routeState.endLng], { icon: endIcon })
            .addTo(map)
            .bindPopup('End: ' + routeState.endAddress);

        // Restore stops if any
        if (routeState.stops && routeState.stops.length > 0) {
            routeState.stops.forEach((stop, index) => {
                const stopIcon = L.divIcon({
                    html: `<div style="background-color: #f59e0b; color: white; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; box-shadow: 0 2px 6px rgba(0,0,0,0.3);">${index + 1}</div>`,
                    iconSize: [30, 30],
                    className: 'stop-marker'
                });

                const stopMarker = L.marker([stop.lat, stop.lng], { icon: stopIcon })
                    .addTo(map)
                    .bindPopup(`Stop ${index + 1}: ${stop.address}`);

                stops.push({
                    marker: stopMarker,
                    address: stop.address,
                    lat: stop.lat,
                    lng: stop.lng,
                    merchantId: stop.merchantId
                });
            });
        }

        // Recreate route object
        currentRoute = {
            geometry: routeState.routeGeometry,
            properties: routeState.routeProperties
        };

        // Draw route polyline
        let allCoordinates = [];
        if (routeState.routeGeometry.type === 'MultiLineString') {
            routeState.routeGeometry.coordinates.forEach(segment => {
                const segmentCoords = segment.map(coord => [coord[1], coord[0]]);
                allCoordinates = allCoordinates.concat(segmentCoords);
            });
        } else if (routeState.routeGeometry.type === 'LineString') {
            allCoordinates = routeState.routeGeometry.coordinates.map(coord => [coord[1], coord[0]]);
        }

        routePolyline = L.polyline(allCoordinates, {
            color: '#2563eb',
            weight: 6,
            opacity: 0.8
        }).addTo(map);

        // Fit map to route
        map.fitBounds(routePolyline.getBounds().pad(0.1));

        // Show route info
        showRouteInfo(currentRoute);

        // Ensure active route section is visible
        const activeRouteSection = document.getElementById('activeRouteSection');
        if (activeRouteSection) {
            activeRouteSection.style.display = 'block';
        }

        // Show Remove Route button
        const removeRouteBtn = document.getElementById('removeRouteBtn');
        if (removeRouteBtn) {
            removeRouteBtn.style.display = 'inline-block';
        }

        console.log('Route restored from localStorage');
        return true;
    } catch (error) {
        console.error('Error loading route from localStorage:', error);
        // Clear corrupted data
        localStorage.removeItem('truckers_active_route');
        return false;
    }
}

// Clear route and localStorage
function clearRoute() {
    // Hide active route section
    document.getElementById('activeRouteSection').style.display = 'none';

    // Stop live navigation watcher if active
    if (typeof stopTurnByTurnNavigation === 'function') {
        stopTurnByTurnNavigation();
    }

    // Remove route polyline
    if (routePolyline) {
        map.removeLayer(routePolyline);
        routePolyline = null;
    }
    
    // Remove markers
    if (startMarker) {
        map.removeLayer(startMarker);
        startMarker = null;
    }
    
    if (endMarker) {
        map.removeLayer(endMarker);
        endMarker = null;
    }
    
    // Remove stop markers
    stops.forEach(stop => {
        if (stop.marker) {
            map.removeLayer(stop.marker);
        }
    });
    
    // Clear form
    document.getElementById('startAddress').value = '';
    document.getElementById('endAddress').value = '';
    document.getElementById('stopsContainer').innerHTML = '';
    stops = [];
    
    // Hide route info (if present)
    const routeInfoCardEl = document.getElementById('routeInfoCard');
    if (routeInfoCardEl) {
        routeInfoCardEl.style.display = 'none';
    }

    // Hide directions section
    const directionsSection = document.getElementById('directionsSection');
    if (directionsSection) {
        directionsSection.classList.add('section-hidden');
    }

    // Hide Remove Route button
    const removeRouteBtn = document.getElementById('removeRouteBtn');
    if (removeRouteBtn) {
        removeRouteBtn.style.display = 'none';
    }

    currentRoute = null;

    // Clear localStorage
    localStorage.removeItem('truckers_active_route');
    console.log('Route cleared and removed from localStorage');
}



// Repeat route function
function repeatRoute(routeId) {
    fetch(`<?= base_url('routes/get/') ?>${routeId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const route = data.route;
                
                // Clear existing route
                clearRoute();
                
                // Set addresses
                document.getElementById('startAddress').value = route.start_address;
                document.getElementById('endAddress').value = route.end_address;

                // Add markers
                const startSuggestion = {
                    properties: { formatted: route.start_address },
                    geometry: { coordinates: [route.start_lng, route.start_lat] }
                };
                
                const endSuggestion = {
                    properties: { formatted: route.end_address },
                    geometry: { coordinates: [route.end_lng, route.end_lat] }
                };
                
                selectAddress(startSuggestion, 'start');
                selectAddress(endSuggestion, 'end');

                // Plan the route
                setTimeout(() => planRoute(), 500);
                
                // Expand sheet to show form
                setSheetPosition('expanded');
            }
        })
        .catch(error => console.error('Error:', error));
}

// Autocomplete initialization is handled in the main DOMContentLoaded event above

// Toggle save route functionality (for removing saved routes)
async function toggleSaveRoute(routeId, buttonElement) {
    try {
        const response = await fetch(`<?= base_url('routes/toggle-saved/') ?>${routeId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        // Check if response is OK
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const responseText = await response.text();
        let result;

        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Failed to parse response:', responseText);
            throw new Error('Invalid server response');
        }

        if (result.success) {
            // Show success message based on the new saved state
            if (result.is_saved) {
                showNotification(result.message || 'Route saved successfully!', 'success');
            } else {
                showNotification(result.message || 'Route removed from saved routes', 'success');
            }

            // Refresh the page after a short delay to update the saved routes section
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showNotification(result.message || 'Failed to update route', 'error');
        }
    } catch (error) {
        console.error('Error toggling save route:', error);
        showNotification('An error occurred. Please try again.', 'error');
    }
}

// Simple notification function
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm text-white';
    // Use dashboard colors
    if (type === 'success') {
        notification.style.backgroundColor = '#2f855a'; // Dashboard green
    } else if (type === 'error') {
        notification.style.backgroundColor = '#ef4444'; // Red
    } else {
        notification.style.backgroundColor = '#3b82f6'; // Blue
    }
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'} mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Ensure a robust repeatRoute implementation overrides earlier duplicates
if (!window.repeatRoute || window.repeatRoute.name !== 'repeatRouteOverride') {
    window.repeatRoute = async function repeatRouteOverride(routeId) {
        try {
            const response = await fetch(`<?= base_url('routes/get/') ?>${routeId}`);
            const data = await response.json();
            const route = data?.route || data?.data;
            if (!data || !route) {
                showNotification && showNotification('Could not load route details', 'error');
                return;
            }

            // Clear any existing route on map/UI
            if (typeof clearRoute === 'function') {
                clearRoute();
            }

            // Set addresses
            const start = {
                properties: { formatted: route.start_address },
                geometry: { coordinates: [route.start_lng, route.start_lat] }
            };
            const end = {
                properties: { formatted: route.end_address },
                geometry: { coordinates: [route.end_lng, route.end_lat] }
            };

            document.getElementById('startAddress').value = route.start_address || '';
            document.getElementById('endAddress').value = route.end_address || '';

            if (typeof selectAddress === 'function') {
                selectAddress(start, 'start');
                selectAddress(end, 'end');
            }

            setTimeout(() => {
                if (typeof planRoute === 'function') {
                    planRoute();
                }
            }, 300);

            try { setSheetPosition && setSheetPosition('expanded'); } catch (e) {}
        } catch (error) {
            console.error('repeatRoute error:', error);
            showNotification && showNotification('Error loading route', 'error');
        }
    }
}

</script>

</body>
</html>
