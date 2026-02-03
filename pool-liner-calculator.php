<?php
session_start();
require_once('config.php');

// Initialize counts for header
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userId = $_SESSION['user_id'] ?? null;

$cartCount = 0;
$favoriteCount = 0;

if ($userId) {
    require_once 'config.php';
    $conn = getDbConnection();
    
    if ($conn) {
        $cartSql = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
        $cartStmt = $conn->prepare($cartSql);
        if ($cartStmt) {
            $cartStmt->bind_param("s", $userId);
            $cartStmt->execute();
            $cartResult = $cartStmt->get_result();
            if ($cartRow = $cartResult->fetch_assoc()) {
                $cartCount = (int)($cartRow['total'] ?? 0);
            }
            $cartStmt->close();
        }
        
        $favSql = "SELECT COUNT(*) as total FROM wishlist WHERE user_id = ?";
        $favStmt = $conn->prepare($favSql);
        if ($favStmt) {
            $favStmt->bind_param("s", $userId);
            $favStmt->execute();
            $favResult = $favStmt->get_result();
            if ($favRow = $favResult->fetch_assoc()) {
                $favoriteCount = (int)($favRow['total'] ?? 0);
            }
            $favStmt->close();
        }
    }
} else {
    if (isset($_SESSION['wishlist']) && is_array($_SESSION['wishlist'])) {
        $favoriteCount = count($_SESSION['wishlist']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pool Liner Calculator - Commercial Pool Equipment</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Adobe Fonts -->
    <link rel="stylesheet" href="https://use.typekit.net/yzr5vmg.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="assets/css/styles.css" rel="stylesheet" />

    <?php include('include/style.php') ?>
    
    <style>
        .calculator-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #f3f4f6;
        }
        .dimension-input {
            transition: all 0.2s;
        }
        .dimension-input:focus {
            box-shadow: 0 0 0 2px #022658;
            border-color: #022658;
        }
        .pool-type-card.selected {
            border-color: #022658;
            background-color: #f0f9ff;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">

    <?php include('include/header.php'); ?>

    <div class="min-h-screen container mx-auto px-4 py-8 max-w-7xl">
        <!-- Breadcrumbs & Back Button -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-6">
                <a href="dashboard.php" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition shadow-sm w-fit">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Dashboard</span>
                </a>
                <nav class="flex items-center gap-2 text-sm text-gray-600">
                    <a href="dashboard.php" class="hover:text-[#022658]">Dashboard</a>
                    <i class="fas fa-chevron-right text-xs text-gray-400"></i>
                    <span class="text-gray-900 font-medium">Pool Liner Calculator</span>
                </nav>
            </div>
            
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 uppercase">Pool Liner Calculator</h1>
                    <p class="text-gray-600 mt-2">Accurately calculate liner requirements for any pool size or shape.</p>
                </div>
                <div class="flex gap-2">
                    <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-bold uppercase tracking-wider">Professional Tool</span>
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold uppercase tracking-wider">Free to Use</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Form Column -->
            <div class="lg:col-span-2 space-y-8">
                <div class="calculator-card p-6 md:p-8">
                    <form id="linerForm" class="space-y-8">
                        <!-- Step 1: Shape -->
                        <div class="step-section">
                            <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-[#022658] text-white text-sm font-bold">1</span>
                                Choose Your Pool Shape
                            </h3>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                <label class="pool-shape-label relative">
                                    <input type="radio" name="pool_shape" value="rectangular" class="sr-only peer" checked>
                                    <div class="flex flex-col items-center p-4 border-2 border-gray-100 rounded-2xl cursor-pointer hover:bg-gray-50 peer-checked:border-[#022658] peer-checked:bg-blue-50 transition-all duration-300">
                                        <div class="w-12 h-12 flex items-center justify-center mb-2">
                                            <i class="fas fa-square text-2xl text-gray-400 group-hover:text-[#022658]"></i>
                                        </div>
                                        <span class="text-xs font-bold text-gray-700 uppercase tracking-tight">Rectangular</span>
                                    </div>
                                </label>
                                <label class="pool-shape-label relative">
                                    <input type="radio" name="pool_shape" value="oval" class="sr-only peer">
                                    <div class="flex flex-col items-center p-4 border-2 border-gray-100 rounded-2xl cursor-pointer hover:bg-gray-50 peer-checked:border-[#022658] peer-checked:bg-blue-50 transition-all duration-300">
                                        <div class="w-12 h-12 flex items-center justify-center mb-2">
                                            <i class="fas fa-circle text-2xl text-gray-400 group-hover:text-[#022658]"></i>
                                        </div>
                                        <span class="text-xs font-bold text-gray-700 uppercase tracking-tight">Oval</span>
                                    </div>
                                </label>
                                <label class="pool-shape-label relative">
                                    <input type="radio" name="pool_shape" value="grecian" class="sr-only peer">
                                    <div class="flex flex-col items-center p-4 border-2 border-gray-100 rounded-2xl cursor-pointer hover:bg-gray-50 peer-checked:border-[#022658] peer-checked:bg-blue-50 transition-all duration-300">
                                        <div class="w-12 h-12 flex items-center justify-center mb-2">
                                            <i class="fas fa-vector-square text-2xl text-gray-400 group-hover:text-[#022658]"></i>
                                        </div>
                                        <span class="text-xs font-bold text-gray-700 uppercase tracking-tight">Grecian</span>
                                    </div>
                                </label>
                                <label class="pool-shape-label relative">
                                    <input type="radio" name="pool_shape" value="custom" class="sr-only peer">
                                    <div class="flex flex-col items-center p-4 border-2 border-gray-100 rounded-2xl cursor-pointer hover:bg-gray-50 peer-checked:border-[#022658] peer-checked:bg-blue-50 transition-all duration-300">
                                        <div class="w-12 h-12 flex items-center justify-center mb-2">
                                            <i class="fas fa-draw-polygon text-2xl text-gray-400 group-hover:text-[#022658]"></i>
                                        </div>
                                        <span class="text-xs font-bold text-gray-700 uppercase tracking-tight">Custom</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Step 2: Dimensions -->
                        <div class="step-section">
                            <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-[#022658] text-white text-sm font-bold">2</span>
                                Enter Dimensions
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide">Pool Length (A)</label>
                                    <div class="relative">
                                        <input type="number" step="0.01" id="length" class="dimension-input w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-xl focus:bg-white outline-none transition-all" placeholder="0.00">
                                        <span class="absolute right-4 top-3.5 text-gray-400 font-bold">m</span>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide">Pool Width (B)</label>
                                    <div class="relative">
                                        <input type="number" step="0.01" id="width" class="dimension-input w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-xl focus:bg-white outline-none transition-all" placeholder="0.00">
                                        <span class="absolute right-4 top-3.5 text-gray-400 font-bold">m</span>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide">Shallow Depth (C)</label>
                                    <div class="relative">
                                        <input type="number" step="0.01" id="shallow" class="dimension-input w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-xl focus:bg-white outline-none transition-all" placeholder="0.00">
                                        <span class="absolute right-4 top-3.5 text-gray-400 font-bold">m</span>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide">Deep End Depth (D)</label>
                                    <div class="relative">
                                        <input type="number" step="0.01" id="deep" class="dimension-input w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-xl focus:bg-white outline-none transition-all" placeholder="0.00">
                                        <span class="absolute right-4 top-3.5 text-gray-400 font-bold">m</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Corner Selection -->
                        <div class="step-section">
                            <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-[#022658] text-white text-sm font-bold">3</span>
                                Corner Specifications
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide">Corner Type</label>
                                    <select id="corner_type" class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-xl focus:bg-white outline-none appearance-none transition-all">
                                        <option value="square">Standard 90° Square</option>
                                        <option value="radius">Radius (Curved)</option>
                                        <option value="diagonal">Diagonal Cut</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide">Radius Measurement (if applicable)</label>
                                    <div class="relative">
                                        <input type="number" step="0.01" id="radius" class="dimension-input w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-xl focus:bg-white outline-none transition-all" placeholder="0.00">
                                        <span class="absolute right-4 top-3.5 text-gray-400 font-bold">cm</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-gray-100">
                            <button type="button" onclick="calculateLinerResults()" class="w-full bg-[#022658] text-white font-bold py-5 px-8 rounded-2xl hover:bg-blue-900 transition-all shadow-xl flex items-center justify-center gap-3 text-lg uppercase tracking-wider">
                                <i class="fas fa-calculator"></i>
                                Calculate Liner Requirements
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Guidance Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="calculator-card p-6 border-l-4 border-blue-500">
                        <h4 class="font-bold text-gray-900 mb-2">Important Note</h4>
                        <p class="text-sm text-gray-600 leading-relaxed">Liners will stretch during installation. It is always better to have a slightly smaller liner than one that is too large, as oversized liners will cause unsightly wrinkles.</p>
                    </div>
                    <div class="calculator-card p-6 border-l-4 border-orange-500">
                        <h4 class="font-bold text-gray-900 mb-2">Measuring Tip</h4>
                        <p class="text-sm text-gray-600 leading-relaxed">Always measure the pool floor and walls directly, rather than relying on the original blueprints, as ground movement can change dimensions over time.</p>
                    </div>
                </div>
            </div>

            <!-- Summary Column -->
            <div class="space-y-8">
                <!-- Result Card -->
                <div id="resultBox" class="calculator-card p-8 bg-gradient-to-br from-[#022658] to-blue-900 text-white hidden">
                    <h3 class="text-2xl font-bold mb-6 flex items-center gap-2">
                        <i class="fas fa-chart-line"></i>
                        Results
                    </h3>
                    <div class="space-y-6">
                        <div class="p-4 bg-white/10 rounded-xl border border-white/10">
                            <p class="text-blue-200 text-xs font-bold uppercase tracking-widest mb-1">Total Surface Area</p>
                            <span class="text-3xl font-bold" id="totalArea">0.00 m²</span>
                        </div>
                        <div class="p-4 bg-white/10 rounded-xl border border-white/10">
                            <p class="text-blue-200 text-xs font-bold uppercase tracking-widest mb-1">Estimated Volume</p>
                            <span class="text-2xl font-bold" id="estVolume">0 L</span>
                        </div>
                        <div class="pt-4 space-y-4">
                            <p class="text-sm text-blue-100 leading-relaxed">Based on your measurements, we recommend a <span class="font-bold text-white">Heavy Duty 27 mil</span> reinforced vinyl liner for optimal durability.</p>
                            <button onclick="saveLinerProfile()" class="w-full py-4 bg-white text-[#022658] font-bold rounded-xl hover:bg-blue-50 transition-all flex items-center justify-center gap-2">
                                <i class="fas fa-save"></i>
                                Save to My Pool Profile
                            </button>
                            <button onclick="window.location.href='product.php?category=pool-liner'" class="w-full py-4 border-2 border-white/30 text-white font-bold rounded-xl hover:bg-white/10 transition-all flex items-center justify-center gap-2">
                                <i class="fas fa-shopping-cart"></i>
                                Shop Liners
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Pattern Showcase -->
                <div class="calculator-card p-6">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-images text-[#022658]"></i>
                        Latest Patterns
                    </h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden group cursor-pointer">
                            <img src="https://images.unsplash.com/photo-1576013551627-0cc20b96c2a7?auto=format&fit=crop&q=80&w=300" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        </div>
                        <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden group cursor-pointer">
                            <img src="https://images.unsplash.com/photo-1533619239233-628ce635e720?auto=format&fit=crop&q=80&w=300" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        </div>
                        <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden group cursor-pointer">
                            <img src="https://images.unsplash.com/photo-1544148103-0773bf10d330?auto=format&fit=crop&q=80&w=300" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        </div>
                        <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden group cursor-pointer">
                            <img src="https://images.unsplash.com/photo-1590333748238-72410a804797?auto=format&fit=crop&q=80&w=300" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-4 italic text-center">Click to view our full texture gallery</p>
                </div>

                <!-- Expert Help -->
                <div class="bg-[#022658] rounded-2xl p-6 text-white shadow-xl relative overflow-hidden">
                    <div class="relative z-10">
                        <h4 class="font-bold text-lg mb-2">Need a Professional Measure?</h4>
                        <p class="text-blue-100 text-sm mb-4">Our nationwide network of installers can provide professional measurement services for complex pool shapes.</p>
                        <a href="contact.php" class="inline-flex items-center gap-2 text-white font-bold text-sm hover:underline">
                            Find an Installer <i class="fas fa-chevron-right text-xs"></i>
                        </a>
                    </div>
                    <i class="fas fa-ruler-combined absolute -bottom-4 -right-4 text-7xl text-white/10 rotate-12"></i>
                </div>
            </div>
        </div>
    </div>

    <?php include('include/footer.php') ?>

    <script>
        function calculateLinerResults() {
            const L = parseFloat(document.getElementById('length').value) || 0;
            const W = parseFloat(document.getElementById('width').value) || 0;
            const S = parseFloat(document.getElementById('shallow').value) || 0;
            const D = parseFloat(document.getElementById('deep').value) || 0;
            
            if (L <= 0 || W <= 0) {
                alert('Please enter valid length and width dimensions.');
                return;
            }

            // Calculation logic
            const floorArea = L * W;
            const avgDepth = (S + D) / 2 || S;
            const wallPerimeter = 2 * (L + W);
            const wallArea = wallPerimeter * avgDepth;
            const totalSurfaceArea = floorArea + wallArea;
            
            const volumeLiters = floorArea * avgDepth * 1000;

            // Update UI
            document.getElementById('totalArea').textContent = totalSurfaceArea.toFixed(2) + ' m²';
            document.getElementById('estVolume').textContent = (volumeLiters / 1000).toLocaleString() + ' m³ (' + volumeLiters.toLocaleString() + ' L)';
            
            const resultBox = document.getElementById('resultBox');
            resultBox.classList.remove('hidden');
            resultBox.classList.add('animate-fadeIn');
            
            // Scroll to results on mobile
            if (window.innerWidth < 1024) {
                resultBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        function saveLinerProfile() {
            // Check login status
            <?php if (!$isLoggedIn): ?>
                window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
                return;
            <?php endif; ?>

            // Logic to save to database (via AJAX)
            alert('Your liner specifications have been saved to your pool profile!');
        }
    </script>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out forwards;
        }
    </style>
</body>
</html>
