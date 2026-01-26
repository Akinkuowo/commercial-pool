<?php
session_start();
require_once('config.php');

// Redirect if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$conn = getDbConnection();
$user_id = $_SESSION['user_id'];
$is_trader = isset($_SESSION['trader_account']) && $_SESSION['trader_account'] === true;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pool_name = $_POST['pool_name'] ?? '';
    $pool_type = $_POST['pool_type'] ?? '';
    $pool_size = $_POST['pool_size'] ?? '';
    $pool_volume = $_POST['pool_volume'] ?? '';
    $pool_usage = $_POST['pool_usage'] ?? '';
    $pool_material = $_POST['pool_material'] ?? '';
    $filter_type = $_POST['filter_type'] ?? '';
    $heater_type = $_POST['heater_type'] ?? '';
    $pump_type = $_POST['pump_type'] ?? '';
    $existing_equipment = $_POST['existing_equipment'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    // Save to database
    if ($conn) {
        $stmt = $conn->prepare("INSERT INTO pool_profiles 
                               (user_id, pool_name, pool_type, pool_size, pool_volume, pool_usage, 
                                pool_material, filter_type, heater_type, pump_type, 
                                existing_equipment, notes, created_at, updated_at) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("ssssssssssss", $user_id, $pool_name, $pool_type, $pool_size, $pool_volume, 
                         $pool_usage, $pool_material, $filter_type, $heater_type, 
                         $pump_type, $existing_equipment, $notes);
        
        if ($stmt->execute()) {
            $pool_id = $stmt->insert_id;
            $success_message = "Pool profile saved successfully!";
            
            // Redirect to compatibility results
            header("Location: compatibility-finder.php?pool_id=" . $pool_id);
            exit;
        } else {
            $error_message = "Failed to save pool profile. Please try again.";
        }
        $stmt->close();
    }
}

// Fetch existing pool profiles
$pool_profiles = [];
if ($conn) {
    $stmt = $conn->prepare("SELECT * FROM pool_profiles WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $pool_profiles[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pool Finder - Commercial Pool Equipment</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Adobe Fonts - Myriad Pro -->
    <link rel="stylesheet" href="https://use.typekit.net/yzr5vmg.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="assets/css/styles.css" rel="stylesheet" />

    <?php include('include/style.php') ?>
    
    <style>
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            background-color: #f3f4f6;
            color: #6b7280;
            transition: all 0.3s ease;
        }
        .step-circle.active {
            background-color: #2563eb;
            color: white;
        }
        .step-circle.completed {
            background-color: #022658;
            color: white;
        }
        .step-content {
            display: none;
        }
        .step-content.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .pool-type-card {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .pool-type-card:hover {
            border-color: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
        }
        .pool-type-card.selected {
            border-color: #2563eb;
            background-color: #eff6ff;
        }
        .calculator-result {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
        }
        .equipment-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
        }
        .equipment-card:hover {
            border-color: #2563eb;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .compatibility-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .compatible {
            background-color: rgba(2, 38, 88, 0.1);
            color: #022658;
        }
        .incompatible {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .requires-check {
            background-color: #fef3c7;
            color: #92400e;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">

    <?php include('include/header.php'); ?>

    <div class="min-h-screen container mx-auto px-4 py-8 max-w-7xl">
        
       <!-- Replace the existing header section in pool-finder.php (around line 169) -->

<div class="min-h-screen container mx-auto px-4 py-8 max-w-7xl">
    
    <!-- Enhanced Header with Back Button and Breadcrumbs -->
    <div class="mb-8">
        <!-- Back Button & Breadcrumbs -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-6">
            <a href="dashboard.php#pool-profile" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition shadow-sm w-fit">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Dashboard</span>
            </a>
            
            <!-- Breadcrumb Navigation -->
            <nav class="flex items-center gap-2 text-sm text-gray-600 overflow-x-auto">
                <a href="dashboard.php" class="hover:text-blue-600 whitespace-nowrap">Dashboard</a>
                <i class="fas fa-chevron-right text-xs text-gray-400"></i>
                <a href="dashboard.php#pool-profile" class="hover:text-blue-600 whitespace-nowrap">Pool Profiles</a>
                <i class="fas fa-chevron-right text-xs text-gray-400"></i>
                <span class="text-gray-900 font-medium whitespace-nowrap">Pool Finder</span>
            </nav>
        </div>
        
        <!-- Page Title -->
        <div>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900">Pool Finder & Compatibility Tool</h1>
            <p class="text-gray-600 mt-2">Find compatible equipment for your pool by entering specifications</p>
        </div>
    </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Sidebar: Progress & Navigation -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden sticky top-8">
                    <div class="p-6 bg-gradient-to-r from-blue-600 to-blue-800 text-white">
                        <h3 class="font-bold text-lg">Pool Finder</h3>
                        <p class="text-blue-100 text-sm mt-1">Step-by-step configuration</p>
                    </div>
                    
                    <!-- Progress Steps -->
                    <div class="p-6">
                        <div class="space-y-6">
                            <div class="flex items-center gap-4">
                                <div id="step1-circle" class="step-circle active">1</div>
                                <div>
                                    <p class="font-medium text-gray-900">Pool Details</p>
                                    <p class="text-sm text-gray-500">Type, size, and usage</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-4">
                                <div id="step2-circle" class="step-circle">2</div>
                                <div>
                                    <p class="font-medium text-gray-700">Existing Equipment</p>
                                    <p class="text-sm text-gray-500">Current setup details</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-4">
                                <div id="step3-circle" class="step-circle">3</div>
                                <div>
                                    <p class="font-medium text-gray-700">Compatibility Check</p>
                                    <p class="text-sm text-gray-500">Find matching products</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-4">
                                <div id="step4-circle" class="step-circle">4</div>
                                <div>
                                    <p class="font-medium text-gray-700">Save Profile</p>
                                    <p class="text-sm text-gray-500">Store for future reference</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Saved Profiles -->
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <h4 class="font-semibold text-gray-900 mb-3">Saved Pool Profiles</h4>
                            <div class="space-y-2">
                                <?php if (empty($pool_profiles)): ?>
                                    <p class="text-sm text-gray-500">No saved profiles yet</p>
                                <?php else: ?>
                                    <?php foreach ($pool_profiles as $profile): ?>
                                    <a href="compatibility-finder.php?pool_id=<?php echo $profile['id']; ?>" 
                                       class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition group">
                                        <div>
                                            <p class="font-medium text-gray-900 text-sm"><?php echo htmlspecialchars($profile['pool_name']); ?></p>
                                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($profile['pool_type']); ?> • <?php echo htmlspecialchars($profile['pool_size']); ?></p>
                                        </div>
                                        <i class="fas fa-chevron-right text-gray-400 group-hover:text-blue-600 transition"></i>
                                    </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Quick Tools -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="font-semibold text-gray-900 mb-3">Quick Tools</h4>
                            <div class="space-y-2">
                                <a href="energy-calculator.php" class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-lg transition">
                                    <div class="w-8 h-8 bg-[#022658]/10 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-bolt text-[#022658]"></i>
                                    </div>
                                    <span class="text-sm font-medium">Energy Calculator</span>
                                </a>
                                <a href="pool-configurator.php" class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-lg transition">
                                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-sliders-h text-purple-600"></i>
                                    </div>
                                    <span class="text-sm font-medium">System Configurator</span>
                                </a>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="font-semibold text-gray-900 mb-3">Actions</h4>
                            <div class="space-y-2">
                                <button onclick="window.location.href='dashboard.php#pool-profile'" 
                                        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                                    <i class="fas fa-times"></i>
                                    <span>Cancel & Return</span>
                                </button>
                                
                                <button onclick="saveAsDraft()" 
                                        class="w-full flex items-center justify-center gap-2 px-4 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium">
                                    <i class="fas fa-save"></i>
                                    <span>Save as Draft</span>
                                </button>
                            </div>
                        </div>

                        <!-- Help Section -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="bg-blue-50 rounded-lg p-4">
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-info-circle text-blue-600 mt-1"></i>
                                    <div>
                                        <p class="text-sm font-medium text-blue-900">Need Help?</p>
                                        <p class="text-xs text-blue-700 mt-1">Contact our experts for personalized pool equipment recommendations.</p>
                                        <a href="contact.php" class="text-xs text-blue-600 hover:text-blue-800 font-medium mt-2 inline-block">
                                            Get Expert Advice →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Main Content: Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    
                    <!-- Messages -->
                    <?php if (isset($success_message)): ?>
                    <div class="bg-[#022658]/10 border-l-4 border-[#022658] p-4 mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-[#022658] mr-3"></i>
                            <p class="text-[#022658]"><?php echo $success_message; ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                            <p class="text-red-700"><?php echo $error_message; ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <form id="poolFinderForm" method="POST" class="p-6">
                        
                        <!-- Step 1: Pool Details -->
                        <div id="step1-content" class="step-content active">
                            <h3 class="text-xl font-bold text-gray-900 mb-6">Step 1: Pool Details</h3>
                            
                            <div class="space-y-6">
                                <!-- Pool Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Pool Name/Identifier</label>
                                    <input type="text" name="pool_name" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Main Pool, Backyard Pool, Client Project Name" required>
                                    <p class="text-xs text-gray-500 mt-1">Give this pool a name for easy reference</p>
                                </div>
                                
                                <!-- Pool Type Selection -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-3">Pool Type</label>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                        <label class="pool-type-card">
                                            <input type="radio" name="pool_type" value="In-ground" class="hidden" required>
                                            <div class="mb-3">
                                                <i class="fas fa-swimming-pool text-3xl text-blue-600"></i>
                                            </div>
                                            <p class="font-medium">In-ground</p>
                                            <p class="text-xs text-gray-500 mt-1">Concrete, vinyl, fiberglass</p>
                                        </label>
                                        
                                        <label class="pool-type-card">
                                            <input type="radio" name="pool_type" value="Above-ground" class="hidden" required>
                                            <div class="mb-3">
                                                <i class="fas fa-water text-3xl text-[#022658]"></i>
                                            </div>
                                            <p class="font-medium">Above-ground</p>
                                            <p class="text-xs text-gray-500 mt-1">Steel, resin, inflatable</p>
                                        </label>
                                        
                                        <label class="pool-type-card">
                                            <input type="radio" name="pool_type" value="Spa/Hot Tub" class="hidden" required>
                                            <div class="mb-3">
                                                <i class="fas fa-hot-tub text-3xl text-purple-600"></i>
                                            </div>
                                            <p class="font-medium">Spa/Hot Tub</p>
                                            <p class="text-xs text-gray-500 mt-1">Portable or built-in</p>
                                        </label>
                                        
                                        <label class="pool-type-card">
                                            <input type="radio" name="pool_type" value="Commercial" class="hidden" required>
                                            <div class="mb-3">
                                                <i class="fas fa-building text-3xl text-orange-600"></i>
                                            </div>
                                            <p class="font-medium">Commercial</p>
                                            <p class="text-xs text-gray-500 mt-1">Public, hotel, school pools</p>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Pool Size & Volume -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Pool Size</label>
                                        <select name="pool_size" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                            <option value="">Select size...</option>
                                            <option value="Small (< 10m)">Small (< 10m)</option>
                                            <option value="Medium (10-20m)">Medium (10-20m)</option>
                                            <option value="Large (20-30m)">Large (20-30m)</option>
                                            <option value="Olympic (50m)">Olympic (50m)</option>
                                            <option value="Custom">Custom size</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Pool Volume (Litres)</label>
                                        <div class="flex gap-3">
                                            <input type="number" name="pool_volume" id="pool_volume" class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 50000" required>
                                            <button type="button" onclick="calculateVolume()" class="px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium">
                                                Calculate
                                            </button>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">Enter volume or use calculator</p>
                                    </div>
                                </div>
                                
                                <!-- Volume Calculator (Hidden by default) -->
                                <div id="volumeCalculator" class="hidden p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <h4 class="font-medium text-gray-900 mb-3">Volume Calculator</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Length (m)</label>
                                            <input type="number" step="0.1" id="calc_length" class="w-full px-3 py-2 border border-gray-300 rounded" placeholder="10.0">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Width (m)</label>
                                            <input type="number" step="0.1" id="calc_width" class="w-full px-3 py-2 border border-gray-300 rounded" placeholder="5.0">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Avg Depth (m)</label>
                                            <input type="number" step="0.1" id="calc_depth" class="w-full px-3 py-2 border border-gray-300 rounded" placeholder="1.5">
                                        </div>
                                    </div>
                                    <div class="mt-3 flex items-center justify-between">
                                        <button type="button" onclick="doVolumeCalculation()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                            Calculate Volume
                                        </button>
                                        <div class="text-right">
                                            <p class="text-xs text-gray-500">Estimated Volume:</p>
                                            <p id="calculatedVolume" class="font-bold text-blue-600">0 litres</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Pool Usage & Material -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Primary Usage</label>
                                        <select name="pool_usage" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                            <option value="">Select usage...</option>
                                            <option value="Residential">Residential/Family</option>
                                            <option value="Commercial">Commercial/Public</option>
                                            <option value="Competition">Competition/Swim Training</option>
                                            <option value="Therapy">Therapy/Rehabilitation</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Pool Material</label>
                                        <select name="pool_material" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="">Select material...</option>
                                            <option value="Concrete">Concrete/Gunite</option>
                                            <option value="Vinyl">Vinyl Liner</option>
                                            <option value="Fiberglass">Fiberglass</option>
                                            <option value="Steel">Steel Frame</option>
                                            <option value="Resin">Resin/Plastic</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Navigation -->
                                <div class="flex justify-end pt-6 border-t border-gray-200">
                                    <button type="button" onclick="nextStep(2)" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                                        Next: Existing Equipment <i class="fas fa-arrow-right ml-2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Step 2: Existing Equipment -->
                        <div id="step2-content" class="step-content">
                            <h3 class="text-xl font-bold text-gray-900 mb-6">Step 2: Existing Equipment</h3>
                            
                            <div class="space-y-6">
                                <p class="text-gray-600 mb-4">Tell us about your current equipment to ensure compatibility.</p>
                                
                                <!-- Filter System -->
                                <div class="border border-gray-200 rounded-lg p-5">
                                    <h4 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                        <i class="fas fa-filter text-blue-600"></i>
                                        Filter System
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Filter Type</label>
                                            <select name="filter_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                <option value="">Select filter type...</option>
                                                <option value="Sand">Sand Filter</option>
                                                <option value="Cartridge">Cartridge Filter</option>
                                                <option value="Diatomaceous Earth">D.E. Filter</option>
                                                <option value="None">No Filter</option>
                                                <option value="Unknown">Don't Know</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Filter Size/Model</label>
                                            <input type="text" name="filter_model" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Hayward S210T">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Pump System -->
                                <div class="border border-gray-200 rounded-lg p-5">
                                    <h4 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                        <i class="fas fa-tint text-blue-600"></i>
                                        Pump System
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Pump Type</label>
                                            <select name="pump_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                <option value="">Select pump type...</option>
                                                <option value="Single Speed">Single Speed</option>
                                                <option value="Variable Speed">Variable Speed</option>
                                                <option value="Two Speed">Two Speed</option>
                                                <option value="Booster">Booster Pump</option>
                                                <option value="None">No Pump</option>
                                                <option value="Unknown">Don't Know</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Pump Horsepower (HP)</label>
                                            <input type="text" name="pump_hp" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 1.5 HP">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Heating System -->
                                <div class="border border-gray-200 rounded-lg p-5">
                                    <h4 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                        <i class="fas fa-fire text-orange-600"></i>
                                        Heating System
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Heater Type</label>
                                            <select name="heater_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                <option value="">Select heater type...</option>
                                                <option value="Gas">Gas Heater</option>
                                                <option value="Electric">Electric Heater</option>
                                                <option value="Heat Pump">Heat Pump</option>
                                                <option value="Solar">Solar Heater</option>
                                                <option value="None">No Heater</option>
                                                <option value="Unknown">Don't Know</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Heater Capacity (kW/BTU)</label>
                                            <input type="text" name="heater_capacity" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 150,000 BTU">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Other Equipment -->
                                <div class="border border-gray-200 rounded-lg p-5">
                                    <h4 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                        <i class="fas fa-cogs text-gray-600"></i>
                                        Other Existing Equipment
                                    </h4>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">List existing equipment or notes</label>
                                        <textarea name="existing_equipment" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Salt chlorinator, UV system, automatic cleaner, etc."></textarea>
                                    </div>
                                </div>
                                
                                <!-- Navigation -->
                                <div class="flex justify-between pt-6 border-t border-gray-200">
                                    <button type="button" onclick="prevStep(1)" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                                        <i class="fas fa-arrow-left mr-2"></i> Back
                                    </button>
                                    <button type="button" onclick="nextStep(3)" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                                        Next: Compatibility Check <i class="fas fa-arrow-right ml-2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Step 3: Compatibility Check (Preview) -->
                        <div id="step3-content" class="step-content">
                            <h3 class="text-xl font-bold text-gray-900 mb-6">Step 3: Compatibility Preview</h3>
                            
                            <div class="space-y-6">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-5">
                                    <div class="flex items-start gap-3">
                                        <i class="fas fa-lightbulb text-blue-600 text-xl mt-1"></i>
                                        <div>
                                            <p class="font-medium text-blue-900">Based on your specifications, here are recommended equipment categories:</p>
                                            <p class="text-sm text-blue-700 mt-1">Full compatibility check will run when you save the profile.</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Recommended Equipment Categories -->
                                <div id="compatibilityPreview">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Dynamically populated via JavaScript -->
                                    </div>
                                </div>
                                
                                <!-- Quick Compatibility Tips -->
                                <div class="border border-gray-200 rounded-lg p-5">
                                    <h4 class="font-semibold text-gray-900 mb-4">Compatibility Tips</h4>
                                    <div class="space-y-3">
                                        <div class="flex items-start gap-3">
                                            <i class="fas fa-check-circle text-[#022658] mt-1"></i>
                                            <p class="text-sm text-gray-700">Ensure pump and filter flow rates are compatible</p>
                                        </div>
                                        <div class="flex items-start gap-3">
                                            <i class="fas fa-check-circle text-[#022658] mt-1"></i>
                                            <p class="text-sm text-gray-700">Heater capacity should match pool volume for efficient heating</p>
                                        </div>
                                        <div class="flex items-start gap-3">
                                            <i class="fas fa-check-circle text-[#022658] mt-1"></i>
                                            <p class="text-sm text-gray-700">Consider energy efficiency ratings for long-term savings</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Notes -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Additional Notes</label>
                                    <textarea name="notes" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Any special requirements, installation notes, or additional information..."></textarea>
                                </div>
                                
                                <!-- Navigation -->
                                <div class="flex justify-between pt-6 border-t border-gray-200">
                                    <button type="button" onclick="prevStep(2)" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                                        <i class="fas fa-arrow-left mr-2"></i> Back
                                    </button>
                                    <button type="button" onclick="nextStep(4)" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                                        Next: Save Profile <i class="fas fa-arrow-right ml-2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Step 4: Save Profile -->
                        <div id="step4-content" class="step-content">
                            <h3 class="text-xl font-bold text-gray-900 mb-6">Step 4: Save Pool Profile</h3>
                            
                            <div class="space-y-6">
                                <div class="calculator-result">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-blue-100 text-sm">Your Pool Profile</p>
                                            <h4 id="profileSummaryName" class="text-2xl font-bold mt-1">Pool Name</h4>
                                            <p id="profileSummaryDetails" class="text-blue-100 mt-1">Details will appear here</p>
                                        </div>
                                        <i class="fas fa-swimming-pool text-4xl opacity-50"></i>
                                    </div>
                                </div>
                                
                                <div class="bg-white border border-gray-200 rounded-lg p-5">
                                    <h4 class="font-semibold text-gray-900 mb-4">Profile Options</h4>
                                    
                                    <div class="space-y-4">
                                        <div class="flex items-start gap-3">
                                            <input type="checkbox" id="save_to_dashboard" name="save_to_dashboard" checked class="mt-1">
                                            <label for="save_to_dashboard" class="text-sm text-gray-700">
                                                <span class="font-medium">Save to My Dashboard</span>
                                                <p class="text-gray-500 mt-1">Access this profile anytime from your dashboard for quick reordering and compatibility checks.</p>
                                            </label>
                                        </div>
                                        
                                        <div class="flex items-start gap-3">
                                            <input type="checkbox" id="receive_recommendations" name="receive_recommendations" checked class="mt-1">
                                            <label for="receive_recommendations" class="text-sm text-gray-700">
                                                <span class="font-medium">Receive Recommendations</span>
                                                <p class="text-gray-500 mt-1">Get email notifications about new products and upgrades compatible with your pool.</p>
                                            </label>
                                        </div>
                                        
                                        <div class="flex items-start gap-3">
                                            <input type="checkbox" id="share_with_trader" name="share_with_trader" class="mt-1">
                                            <label for="share_with_trader" class="text-sm text-gray-700">
                                                <span class="font-medium">Share with Trade Account</span>
                                                <p class="text-gray-500 mt-1">Allow your trade account manager to view this profile for better service (trade customers only).</p>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Final Actions -->
                                <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200">
                                    <button type="button" onclick="prevStep(3)" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                                        <i class="fas fa-arrow-left mr-2"></i> Back
                                    </button>
                                    <button type="submit" class="flex-1 px-6 py-3 bg-[#022658] text-white rounded-lg hover:bg-[#022658]/90 font-medium">
                                        <i class="fas fa-save mr-2"></i> Save Pool Profile
                                    </button>
                                    <button type="button" onclick="generateCompatibilityReport()" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                                        <i class="fas fa-download mr-2"></i> Generate Report
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                    </form>
                </div>
                
                <!-- Pool Compatibility Database -->
                <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Pool Compatibility Database</h3>
                        <p class="text-gray-600 text-sm mt-1">Common pool configurations and their compatible equipment</p>
                    </div>
                    
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-gray-600">
                                <thead class="bg-gray-50 text-gray-900 font-semibold uppercase text-xs">
                                    <tr>
                                        <th class="px-6 py-3">Pool Type</th>
                                        <th class="px-6 py-3">Recommended Filter</th>
                                        <th class="px-6 py-3">Pump Size</th>
                                        <th class="px-6 py-3">Heater Type</th>
                                        <th class="px-6 py-3">Compatibility</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr>
                                        <td class="px-6 py-4 font-medium">Residential In-ground (Small)</td>
                                        <td class="px-6 py-4">Sand or Cartridge</td>
                                        <td class="px-6 py-4">0.75 - 1.5 HP</td>
                                        <td class="px-6 py-4">Gas or Heat Pump</td>
                                        <td class="px-6 py-4"><span class="compatibility-badge compatible">High</span></td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 font-medium">Above-ground Family Pool</td>
                                        <td class="px-6 py-4">Cartridge</td>
                                        <td class="px-6 py-4">0.5 - 1 HP</td>
                                        <td class="px-6 py-4">Electric or Solar</td>
                                        <td class="px-6 py-4"><span class="compatibility-badge compatible">High</span></td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 font-medium">Commercial Pool</td>
                                        <td class="px-6 py-4">Sand or D.E.</td>
                                        <td class="px-6 py-4">2 - 5 HP</td>
                                        <td class="px-6 py-4">Commercial Heat Pump</td>
                                        <td class="px-6 py-4"><span class="compatibility-badge requires-check">Verify</span></td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 font-medium">Spa/Hot Tub</td>
                                        <td class="px-6 py-4">Cartridge (Small)</td>
                                        <td class="px-6 py-4">Jet Pump</td>
                                        <td class="px-6 py-4">Spa Heater</td>
                                        <td class="px-6 py-4"><span class="compatibility-badge compatible">High</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('include/footer.php') ?>
    
    <!-- Scripts -->
    <script>
        let currentStep = 1;
        
        // Initialize steps
        document.addEventListener('DOMContentLoaded', () => {
            updateStepCircles();
            updateProfileSummary();
            setupPoolTypeSelection();
        });
        
        // Step Navigation
        function nextStep(step) {
            // Validate current step before proceeding
            if (!validateStep(currentStep)) {
                return;
            }
            
            currentStep = step;
            updateStepCircles();
            showStepContent(step);
            
            // Update preview on step 3
            if (step === 3) {
                generateCompatibilityPreview();
            }
            
            // Update profile summary on step 4
            if (step === 4) {
                updateProfileSummary();
            }
        }
        
        function prevStep(step) {
            currentStep = step;
            updateStepCircles();
            showStepContent(step);
        }
        
        function showStepContent(step) {
            document.querySelectorAll('.step-content').forEach(el => {
                el.classList.remove('active');
            });
            document.getElementById(`step${step}-content`).classList.add('active');
        }
        
        function updateStepCircles() {
            for (let i = 1; i <= 4; i++) {
                const circle = document.getElementById(`step${i}-circle`);
                circle.classList.remove('active', 'completed');
                
                if (i === currentStep) {
                    circle.classList.add('active');
                } else if (i < currentStep) {
                    circle.classList.add('completed');
                }
            }
        }
        
        function validateStep(step) {
            switch(step) {
                case 1:
                    const poolName = document.querySelector('input[name="pool_name"]').value;
                    const poolType = document.querySelector('input[name="pool_type"]:checked');
                    const poolSize = document.querySelector('select[name="pool_size"]').value;
                    const poolVolume = document.querySelector('input[name="pool_volume"]').value;
                    
                    if (!poolName.trim()) {
                        alert('Please enter a pool name');
                        return false;
                    }
                    if (!poolType) {
                        alert('Please select a pool type');
                        return false;
                    }
                    if (!poolSize) {
                        alert('Please select pool size');
                        return false;
                    }
                    if (!poolVolume) {
                        alert('Please enter pool volume');
                        return false;
                    }
                    return true;
                    
                case 2:
                    // Step 2 validation (optional)
                    return true;
                    
                case 3:
                    // Step 3 validation (optional)
                    return true;
                    
                default:
                    return true;
            }
        }
        
        // Pool Type Selection
        function setupPoolTypeSelection() {
            const poolTypeCards = document.querySelectorAll('.pool-type-card');
            poolTypeCards.forEach(card => {
                card.addEventListener('click', function() {
                    // Deselect all
                    poolTypeCards.forEach(c => c.classList.remove('selected'));
                    // Select this one
                    this.classList.add('selected');
                    // Check the radio input
                    const radio = this.querySelector('input[type="radio"]');
                    if (radio) radio.checked = true;
                });
            });
        }
        
        // Volume Calculator
        function calculateVolume() {
            const calculator = document.getElementById('volumeCalculator');
            calculator.classList.toggle('hidden');
        }
        
        function doVolumeCalculation() {
            const length = parseFloat(document.getElementById('calc_length').value) || 0;
            const width = parseFloat(document.getElementById('calc_width').value) || 0;
            const depth = parseFloat(document.getElementById('calc_depth').value) || 0;
            
            if (length && width && depth) {
                // Volume = length × width × average depth × 1000 (to convert m³ to litres)
                const volume = Math.round(length * width * depth * 1000);
                document.getElementById('calculatedVolume').textContent = volume.toLocaleString() + ' litres';
                
                // Update the main volume field
                document.getElementById('pool_volume').value = volume;
                
                // Close calculator after successful calculation
                setTimeout(() => {
                    document.getElementById('volumeCalculator').classList.add('hidden');
                }, 1500);
            } else {
                alert('Please enter all dimensions');
            }
        }
        
        // Compatibility Preview
        function generateCompatibilityPreview() {
            const poolType = document.querySelector('input[name="pool_type"]:checked')?.value || '';
            const poolSize = document.querySelector('select[name="pool_size"]').value;
            const poolVolume = document.querySelector('input[name="pool_volume"]').value;
            
            const previewContainer = document.getElementById('compatibilityPreview');
            let recommendations = [];
            
            // Generate recommendations based on pool type and size
            if (poolType && poolSize && poolVolume) {
                const volume = parseInt(poolVolume) || 0;
                
                if (poolType.includes('In-ground') || poolType.includes('Commercial')) {
                    recommendations = [
                        {
                            category: 'Filtration',
                            recommendation: volume > 100000 ? 'Large Sand Filter System' : 'Standard Sand Filter',
                            compatibility: 'High',
                            icon: 'filter',
                            color: 'blue'
                        },
                        {
                            category: 'Pumps',
                            recommendation: volume > 100000 ? '2-3 HP Variable Speed Pump' : '1-1.5 HP Pump',
                            compatibility: 'High',
                            icon: 'tint',
                            color: 'blue'
                        },
                        {
                            category: 'Heating',
                            recommendation: volume > 100000 ? 'Commercial Heat Pump' : 'Efficient Gas Heater',
                            compatibility: volume > 150000 ? 'Verify' : 'High',
                            icon: 'fire',
                            color: 'blue'
                        },
                        {
                            category: 'Maintenance',
                            recommendation: 'Manual Vacuum Kit',
                            compatibility: 'High',
                            icon: 'broom',
                            color: 'blue'
                        }
                    ];
                } else if (poolType.includes('Spa')) {
                    recommendations = [
                        {
                            category: 'Filtration',
                            recommendation: 'Compact Cartridge Filter',
                            compatibility: 'High',
                            icon: 'filter',
                            color: 'purple'
                        },
                        {
                            category: 'Pumps',
                            recommendation: 'Jet Pump System',
                            compatibility: 'High',
                            icon: 'tint',
                            color: 'purple'
                        },
                        {
                            category: 'Heating',
                            recommendation: 'Spa Heater (3-6 kW)',
                            compatibility: 'High',
                            icon: 'hot-tub',
                            color: 'purple'
                        },
                        {
                            category: 'Chemicals',
                            recommendation: 'Spa Chemical Kit',
                            compatibility: 'High',
                            icon: 'flask',
                            color: 'purple'
                        }
                    ];
                }
                
                // Add volume-specific recommendations
                if (volume > 150000) {
                    recommendations.push({
                        category: 'Circulation',
                        recommendation: 'High-Flow Return Jets',
                        compatibility: 'High',
                        icon: 'water',
                        color: 'blue'
                    });
                }
            }
            
            // Generate HTML
            let html = '';
            recommendations.forEach(rec => {
                const compatClass = rec.compatibility === 'High' ? 'compatible' : 
                                  rec.compatibility === 'Verify' ? 'requires-check' : 'incompatible';
                
                html += `
                    <div class="equipment-card">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-${rec.color}-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-${rec.icon} text-${rec.color}-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900">${rec.category}</h4>
                                    <p class="text-sm text-gray-600">${rec.recommendation}</p>
                                </div>
                            </div>
                            <span class="compatibility-badge ${compatClass}">${rec.compatibility}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>Recommended based on pool specifications</span>
                            <a href="#" class="text-blue-600 hover:text-blue-800">View options →</a>
                        </div>
                    </div>
                `;
            });
            
            previewContainer.innerHTML = html || '<p class="text-gray-500">Enter pool details to see recommendations</p>';
        }
        
        // Profile Summary
        function updateProfileSummary() {
            const poolName = document.querySelector('input[name="pool_name"]').value || 'Unnamed Pool';
            const poolType = document.querySelector('input[name="pool_type"]:checked')?.value || 'Not specified';
            const poolSize = document.querySelector('select[name="pool_size"]').value || 'Not specified';
            const poolVolume = document.querySelector('input[name="pool_volume"]').value || '0';
            
            document.getElementById('profileSummaryName').textContent = poolName;
            document.getElementById('profileSummaryDetails').textContent = 
                `${poolType} • ${poolSize} • ${parseInt(poolVolume).toLocaleString()} litres`;
        }
        
        // Generate Report
        function generateCompatibilityReport() {
            alert('Compatibility report generation would create a PDF with all specifications and recommendations. This feature would connect to a reporting service.');
        }
        
        // Form submission validation
        document.getElementById('poolFinderForm').addEventListener('submit', function(e) {
            if (!validateStep(1)) {
                e.preventDefault();
                alert('Please complete all required fields in Step 1');
                nextStep(1);
            }
        });

        // Handle successful save redirect with confirmation
        document.getElementById('poolFinderForm').addEventListener('submit', function(e) {
            if (!validateStep(1)) {
                e.preventDefault();
                alert('Please complete all required fields in Step 1');
                nextStep(1);
                return;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving Profile...';
            
            // Note: Form will submit naturally, but we can add visual feedback
            // The actual redirect happens via PHP header() after successful save
        });

        // Alternative: AJAX save with better UX
        async function savePoolProfileAjax() {
            const form = document.getElementById('poolFinderForm');
            const formData = new FormData(form);
            
            try {
                const response = await fetch('api/save_pool_profile.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Show success message
                    showNotification('success', 'Pool profile saved successfully!');
                    
                    // Redirect to dashboard after short delay
                    setTimeout(() => {
                        window.location.href = 'dashboard.php#pool-profile';
                    }, 1500);
                } else {
                    showNotification('error', result.message || 'Failed to save pool profile');
                }
            } catch (error) {
                console.error('Save error:', error);
                showNotification('error', 'An error occurred while saving');
            }
        }

        // Notification system
        function showNotification(type, message) {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transition-all transform translate-x-0 ${
                type === 'success' ? 'bg-[#022658]' : 'bg-red-600'
            } text-white`;
            
            notification.innerHTML = `
                <div class="flex items-center gap-3">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} text-xl"></i>
                    <p class="font-medium">${message}</p>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => notification.classList.add('translate-x-0'), 10);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Add cancel/discard button handler
        function cancelPoolProfile() {
            if (confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')) {
                window.location.href = 'dashboard.php#pool-profile';
            }
        }
        // Save as draft functionality
        async function saveAsDraft() {
            const poolName = document.querySelector('input[name="pool_name"]').value;
            
            if (!poolName.trim()) {
                alert('Please enter a pool name before saving as draft');
                return;
            }
            
            if (confirm('Save this pool profile as draft? You can complete it later from your dashboard.')) {
                // Add draft flag to form
                const form = document.getElementById('poolFinderForm');
                const draftInput = document.createElement('input');
                draftInput.type = 'hidden';
                draftInput.name = 'save_as_draft';
                draftInput.value = '1';
                form.appendChild(draftInput);
                
                // Submit form
                form.submit();
            }
        }
    </script>
</body>
</html>