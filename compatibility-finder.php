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
$pool_id = $_GET['pool_id'] ?? 0;

// Fetch pool profile
$pool_profile = null;
if ($conn && $pool_id) {
    $stmt = $conn->prepare("SELECT * FROM pool_profiles WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $pool_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $pool_profile = $result->fetch_assoc();
    $stmt->close();
    
    if (!$pool_profile) {
        header('Location: pool-finder.php');
        exit;
    }
} else {
    header('Location: pool-finder.php');
    exit;
}

// Fetch compatible products
$compatible_products = [
    'pumps' => [],
    'filters' => [],
    'heaters' => [],
    'cleaners' => [],
    'chemicals' => [],
    'accessories' => []
];

// Fetch trade pricing if applicable
$user_price_tier = 'retail';
if ($is_trader && $conn) {
    $stmt = $conn->prepare("SELECT price_tier FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    $user_price_tier = $user_data['price_tier'] ?? 'retail';
    $stmt->close();
}

// In a real application, you would query the database for actual compatible products
// For now, we'll simulate with static data based on pool profile
function getCompatibleProducts($pool_profile, $user_price_tier) {
    $products = [];
    
    // Determine pool characteristics
    $pool_volume = $pool_profile['pool_volume'] ?? 0;
    $pool_type = $pool_profile['pool_type'] ?? '';
    $pool_size = $pool_profile['pool_size'] ?? '';
    $is_commercial = stripos($pool_type, 'commercial') !== false || stripos($pool_size, 'olympic') !== false;
    $is_small = $pool_volume < 50000;
    $is_large = $pool_volume > 100000;
    $is_spa = stripos($pool_type, 'spa') !== false || stripos($pool_type, 'hot tub') !== false;
    
    // Calculate required flow rate (simplified: 1 turnover per 4-6 hours)
    $required_flow_rate = $pool_volume / 5; // litres per hour
    $required_flow_rate_m3 = $required_flow_rate / 1000; // m³ per hour
    
    // Price multiplier based on tier
    $price_multiplier = 1.0;
    if ($user_price_tier === 'Trade Tier 1') $price_multiplier = 0.85;
    if ($user_price_tier === 'Trade Tier 2') $price_multiplier = 0.80;
    if ($user_price_tier === 'Trade Tier 3') $price_multiplier = 0.75;
    if ($user_price_tier === 'VIP') $price_multiplier = 0.70;
    
    // Pumps
    if ($is_spa) {
        $products['pumps'][] = [
            'id' => 101,
            'name' => 'SpaJet Pro Circulation Pump',
            'description' => 'Compact jet pump for spas and hot tubs, quiet operation',
            'specs' => '0.5 HP, 50W, 1200 L/hr flow rate',
            'compatibility_score' => 95,
            'retail_price' => 299.99,
            'trade_price' => 299.99 * $price_multiplier,
            'image' => 'assets/img/products/spa-pump.jpg',
            'stock' => 25,
            'brand' => 'WaterMaster',
            'sku' => 'WP-SPA-050'
        ];
    } elseif ($is_commercial) {
        $products['pumps'][] = [
            'id' => 102,
            'name' => 'Commercial High-Flow Pump',
            'description' => 'Heavy-duty pump for commercial pools and water features',
            'specs' => '5 HP, 3.7kW, 25,000 L/hr flow rate',
            'compatibility_score' => 90,
            'retail_price' => 2499.99,
            'trade_price' => 2499.99 * $price_multiplier,
            'image' => 'assets/img/products/commercial-pump.jpg',
            'stock' => 8,
            'brand' => 'AquaForce',
            'sku' => 'WP-COM-500'
        ];
    } else {
        // Residential pumps based on size
        if ($is_small) {
            $products['pumps'][] = [
                'id' => 103,
                'name' => 'EcoFlow Variable Speed Pump',
                'description' => 'Energy-efficient variable speed pump for small to medium pools',
                'specs' => '1.0 HP, 750W, 8,000 L/hr flow rate',
                'compatibility_score' => 92,
                'retail_price' => 649.99,
                'trade_price' => 649.99 * $price_multiplier,
                'image' => 'assets/img/products/variable-pump.jpg',
                'stock' => 15,
                'brand' => 'EcoPool',
                'sku' => 'WP-VS-100'
            ];
        } else {
            $products['pumps'][] = [
                'id' => 104,
                'name' => 'PowerFlow High-Performance Pump',
                'description' => 'Reliable single-speed pump for medium to large residential pools',
                'specs' => '1.5 HP, 1.1kW, 12,000 L/hr flow rate',
                'compatibility_score' => 88,
                'retail_price' => 449.99,
                'trade_price' => 449.99 * $price_multiplier,
                'image' => 'assets/img/products/power-pump.jpg',
                'stock' => 12,
                'brand' => 'PowerFlow',
                'sku' => 'WP-PF-150'
            ];
        }
    }
    
    // Filters
    if ($is_spa) {
        $products['filters'][] = [
            'id' => 201,
            'name' => 'Spa Cartridge Filter System',
            'description' => 'Compact cartridge filter for spas and hot tubs',
            'specs' => '50 sq.ft. filter area, easy-clean cartridges',
            'compatibility_score' => 97,
            'retail_price' => 189.99,
            'trade_price' => 189.99 * $price_multiplier,
            'image' => 'assets/img/products/spa-filter.jpg',
            'stock' => 30,
            'brand' => 'CleanFlow',
            'sku' => 'FL-SPA-050'
        ];
    } elseif ($is_commercial) {
        $products['filters'][] = [
            'id' => 202,
            'name' => 'Commercial Sand Filter System',
            'description' => 'High-capacity sand filter for commercial pools',
            'specs' => '36" diameter, 500 kg sand capacity',
            'compatibility_score' => 93,
            'retail_price' => 1899.99,
            'trade_price' => 1899.99 * $price_multiplier,
            'image' => 'assets/img/products/commercial-filter.jpg',
            'stock' => 5,
            'brand' => 'ProFilter',
            'sku' => 'FL-COM-036'
        ];
    } else {
        // Residential filters
        if ($is_small) {
            $products['filters'][] = [
                'id' => 203,
                'name' => 'ClearWater Cartridge Filter',
                'description' => 'Easy-maintenance cartridge filter for small pools',
                'specs' => '75 sq.ft. filter area, 4 cartridges',
                'compatibility_score' => 90,
                'retail_price' => 349.99,
                'trade_price' => 349.99 * $price_multiplier,
                'image' => 'assets/img/products/cartridge-filter.jpg',
                'stock' => 18,
                'brand' => 'ClearWater',
                'sku' => 'FL-CW-075'
            ];
        } else {
            $products['filters'][] = [
                'id' => 204,
                'name' => 'SandMaster Sand Filter',
                'description' => 'High-performance sand filter for medium to large pools',
                'specs' => '24" diameter, 150 kg sand capacity',
                'compatibility_score' => 86,
                'retail_price' => 599.99,
                'trade_price' => 599.99 * $price_multiplier,
                'image' => 'assets/img/products/sand-filter.jpg',
                'stock' => 10,
                'brand' => 'SandMaster',
                'sku' => 'FL-SM-024'
            ];
        }
    }
    
    // Heaters
    $existing_heater = $pool_profile['heater_type'] ?? '';
    if ($existing_heater === 'None' || !$existing_heater) {
        if ($is_spa) {
            $products['heaters'][] = [
                'id' => 301,
                'name' => 'SpaTherm Electric Heater',
                'description' => 'Compact electric heater for spas and hot tubs',
                'specs' => '3 kW, digital thermostat, GFCI protected',
                'compatibility_score' => 96,
                'retail_price' => 499.99,
                'trade_price' => 499.99 * $price_multiplier,
                'image' => 'assets/img/products/spa-heater.jpg',
                'stock' => 20,
                'brand' => 'ThermoSpa',
                'sku' => 'HT-SPA-003'
            ];
        } elseif ($is_commercial) {
            $products['heaters'][] = [
                'id' => 302,
                'name' => 'Commercial Heat Pump',
                'description' => 'High-efficiency heat pump for commercial pools',
                'specs' => '35 kW heating capacity, COP 5.2',
                'compatibility_score' => 91,
                'retail_price' => 5499.99,
                'trade_price' => 5499.99 * $price_multiplier,
                'image' => 'assets/img/products/commercial-heatpump.jpg',
                'stock' => 4,
                'brand' => 'EcoTherm',
                'sku' => 'HT-COM-035'
            ];
        } else {
            // Residential heaters
            if ($is_small) {
                $products['heaters'][] = [
                    'id' => 303,
                    'name' => 'EcoHeat Heat Pump',
                    'description' => 'Energy-efficient heat pump for small to medium pools',
                    'specs' => '9 kW, COP 6.0, very quiet operation',
                    'compatibility_score' => 89,
                    'retail_price' => 2499.99,
                    'trade_price' => 2499.99 * $price_multiplier,
                    'image' => 'assets/img/products/heatpump.jpg',
                    'stock' => 8,
                    'brand' => 'EcoHeat',
                    'sku' => 'HT-EH-009'
                ];
            } else {
                $products['heaters'][] = [
                    'id' => 304,
                    'name' => 'GasMaster Pro Heater',
                    'description' => 'High-output gas heater for quick heating',
                    'specs' => '250,000 BTU, electronic ignition',
                    'compatibility_score' => 85,
                    'retail_price' => 1299.99,
                    'trade_price' => 1299.99 * $price_multiplier,
                    'image' => 'assets/img/products/gas-heater.jpg',
                    'stock' => 12,
                    'brand' => 'GasMaster',
                    'sku' => 'HT-GM-250'
                ];
            }
        }
    }
    
    // Cleaners
    if ($is_spa) {
        $products['cleaners'][] = [
            'id' => 401,
            'name' => 'Spa Vac Pro Cleaner',
            'description' => 'Manual vacuum cleaner designed for spas',
            'specs' => 'Compact design, flexible hose, fine mesh bag',
            'compatibility_score' => 98,
            'retail_price' => 79.99,
            'trade_price' => 79.99 * $price_multiplier,
            'image' => 'assets/img/products/spa-vacuum.jpg',
            'stock' => 40,
            'brand' => 'SpaCare',
            'sku' => 'CL-SPA-VAC'
        ];
    } else {
        $products['cleaners'][] = [
            'id' => 402,
            'name' => 'RoboClean Automatic Pool Cleaner',
            'description' => 'Robotic cleaner for automatic pool maintenance',
            'specs' => 'WiFi enabled, programmable cleaning cycles',
            'compatibility_score' => 87,
            'retail_price' => 899.99,
            'trade_price' => 899.99 * $price_multiplier,
            'image' => 'assets/img/products/robotic-cleaner.jpg',
            'stock' => 9,
            'brand' => 'RoboClean',
            'sku' => 'CL-RC-100'
        ];
        
        $products['cleaners'][] = [
            'id' => 403,
            'name' => 'Pressure-Side Pool Cleaner',
            'description' => 'Pressure-side cleaner for leaves and debris',
            'specs' => 'Works with filter pump, large debris bag',
            'compatibility_score' => 82,
            'retail_price' => 349.99,
            'trade_price' => 349.99 * $price_multiplier,
            'image' => 'assets/img/products/pressure-cleaner.jpg',
            'stock' => 15,
            'brand' => 'AquaClean',
            'sku' => 'CL-PS-200'
        ];
    }
    
    // Chemicals (always compatible)
    $products['chemicals'][] = [
        'id' => 501,
        'name' => 'Pool Care Starter Kit',
        'description' => 'Complete chemical kit for pool maintenance',
        'specs' => 'Chlorine, pH adjusters, algaecide, test strips',
        'compatibility_score' => 100,
        'retail_price' => 89.99,
        'trade_price' => 89.99 * $price_multiplier,
        'image' => 'assets/img/products/chemical-kit.jpg',
        'stock' => 50,
        'brand' => 'PoolCare',
        'sku' => 'CH-START-100'
    ];
    
    if ($is_spa) {
        $products['chemicals'][] = [
            'id' => 502,
            'name' => 'Spa Chemical Maintenance Kit',
            'description' => 'Specialty chemicals for spa/hot tub water care',
            'specs' => 'Spa chlorine, pH buffers, foam reducer',
            'compatibility_score' => 99,
            'retail_price' => 59.99,
            'trade_price' => 59.99 * $price_multiplier,
            'image' => 'assets/img/products/spa-chemicals.jpg',
            'stock' => 35,
            'brand' => 'SpaCare',
            'sku' => 'CH-SPA-KIT'
        ];
    }
    
    // Accessories
    $products['accessories'][] = [
        'id' => 601,
        'name' => 'Digital Water Test Kit',
        'description' => 'Digital pH and chlorine tester with app connectivity',
        'specs' => 'Tests 7 parameters, stores 1000 readings',
        'compatibility_score' => 100,
        'retail_price' => 149.99,
        'trade_price' => 149.99 * $price_multiplier,
        'image' => 'assets/img/products/test-kit.jpg',
        'stock' => 25,
        'brand' => 'AquaTest',
        'sku' => 'AC-TEST-DIG'
    ];
    
    if ($pool_volume > 30000) {
        $products['accessories'][] = [
            'id' => 602,
            'name' => 'Automatic Pool Cover',
            'description' => 'Motorized safety cover with remote control',
            'specs' => 'Safety certified, reduces evaporation by 95%',
            'compatibility_score' => 78,
            'retail_price' => 2499.99,
            'trade_price' => 2499.99 * $price_multiplier,
            'image' => 'assets/img/products/pool-cover.jpg',
            'stock' => 6,
            'brand' => 'CoverMaster',
            'sku' => 'AC-COV-AUTO'
        ];
    }
    
    return $products;
}

$compatible_products = getCompatibleProducts($pool_profile, $user_price_tier);

// Calculate compatibility metrics
$total_products = 0;
$avg_compatibility = 0;
foreach ($compatible_products as $category => $products) {
    $total_products += count($products);
    foreach ($products as $product) {
        $avg_compatibility += $product['compatibility_score'];
    }
}
if ($total_products > 0) {
    $avg_compatibility = round($avg_compatibility / $total_products, 1);
}

// Handle adding to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'] ?? 0;
    $quantity = $_POST['quantity'] ?? 1;
    
    // In a real application, you would add to cart here
    // For now, we'll simulate success
    $cart_success = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compatibility Finder - <?php echo htmlspecialchars($pool_profile['pool_name']); ?> - Commercial Pool Equipment</title>
    
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
        .compatibility-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .compatibility-score-90 { background-color: #10b981; color: white; }
        .compatibility-score-80 { background-color: #3b82f6; color: white; }
        .compatibility-score-70 { background-color: #f59e0b; color: white; }
        .compatibility-score-60 { background-color: #ef4444; color: white; }
        .compatibility-meter {
            height: 8px;
            border-radius: 4px;
            background-color: #e5e7eb;
            overflow: hidden;
        }
        .compatibility-fill {
            height: 100%;
            border-radius: 4px;
        }
        .category-card {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        .category-card:hover {
            border-color: #3b82f6;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        .product-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .product-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .price-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: bold;
        }
        .trade-price {
            color: #059669;
            font-weight: bold;
        }
        .retail-price {
            color: #6b7280;
            text-decoration: line-through;
            font-size: 0.9em;
        }
        .spec-badge {
            display: inline-block;
            background-color: #f3f4f6;
            color: #4b5563;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            margin-right: 4px;
            margin-bottom: 4px;
        }
        .comparison-table tr:hover {
            background-color: #f9fafb;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">

    <?php include('include/header.php'); ?>

    <div class="min-h-screen container mx-auto px-4 py-8 max-w-7xl">
        
        <!-- Header with Pool Info -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <a href="pool-finder.php" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h1 class="text-3xl md:text-4xl font-bold text-gray-900">
                            Compatibility Finder
                        </h1>
                    </div>
                    <div class="flex items-center gap-3 mt-2">
                        <div class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                            <i class="fas fa-swimming-pool mr-1"></i>
                            <?php echo htmlspecialchars($pool_profile['pool_name']); ?>
                        </div>
                        <span class="text-gray-500">•</span>
                        <span class="text-gray-600"><?php echo htmlspecialchars($pool_profile['pool_type']); ?></span>
                        <span class="text-gray-500">•</span>
                        <span class="text-gray-600"><?php echo number_format($pool_profile['pool_volume']); ?> litres</span>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button onclick="generateReport()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                        <i class="fas fa-download mr-2"></i> Export Report
                    </button>
                    <a href="pool-finder.php?edit=<?php echo $pool_id; ?>" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        <i class="fas fa-edit mr-2"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        <?php if (isset($cart_success) && $cart_success): ?>
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                <p class="text-green-700 font-medium">Product added to cart successfully!</p>
            </div>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Sidebar: Pool Specs & Filters -->
            <div class="lg:col-span-1">
                <div class="space-y-6">
                    <!-- Pool Specifications Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 bg-gradient-to-r from-blue-600 to-blue-800 text-white">
                            <h3 class="font-bold text-lg">Pool Specifications</h3>
                            <p class="text-blue-100 text-sm">Compatibility analysis based on these details</p>
                        </div>
                        
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Pool Type</span>
                                    <span class="font-medium"><?php echo htmlspecialchars($pool_profile['pool_type']); ?></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Size</span>
                                    <span class="font-medium"><?php echo htmlspecialchars($pool_profile['pool_size']); ?></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Volume</span>
                                    <span class="font-medium"><?php echo number_format($pool_profile['pool_volume']); ?> litres</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Usage</span>
                                    <span class="font-medium"><?php echo htmlspecialchars($pool_profile['pool_usage'] ?? 'Not specified'); ?></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Material</span>
                                    <span class="font-medium"><?php echo htmlspecialchars($pool_profile['pool_material'] ?? 'Not specified'); ?></span>
                                </div>
                                
                                <?php if ($pool_profile['filter_type']): ?>
                                <div class="pt-4 border-t border-gray-200">
                                    <h4 class="font-semibold text-gray-900 mb-2">Existing Equipment</h4>
                                    <div class="space-y-2">
                                        <?php if ($pool_profile['filter_type']): ?>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600">Filter</span>
                                            <span class="font-medium"><?php echo htmlspecialchars($pool_profile['filter_type']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if ($pool_profile['pump_type']): ?>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600">Pump</span>
                                            <span class="font-medium"><?php echo htmlspecialchars($pool_profile['pump_type']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if ($pool_profile['heater_type']): ?>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600">Heater</span>
                                            <span class="font-medium"><?php echo htmlspecialchars($pool_profile['heater_type']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Compatibility Summary -->
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <h4 class="font-semibold text-gray-900 mb-3">Compatibility Summary</h4>
                                <div class="space-y-3">
                                    <div>
                                        <div class="flex justify-between text-sm mb-1">
                                            <span class="text-gray-600">Average Score</span>
                                            <span class="font-bold"><?php echo $avg_compatibility; ?>%</span>
                                        </div>
                                        <div class="compatibility-meter">
                                            <div class="compatibility-fill" style="width: <?php echo $avg_compatibility; ?>%; background-color: #10b981;"></div>
                                        </div>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Products Found</span>
                                        <span class="font-bold"><?php echo $total_products; ?></span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Categories</span>
                                        <span class="font-bold">6</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filters Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-100">
                            <h3 class="font-bold text-gray-900">Filters</h3>
                        </div>
                        
                        <div class="p-6">
                            <div class="space-y-4">
                                <!-- Price Range -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Price Range</label>
                                    <div class="flex items-center gap-3">
                                        <input type="number" placeholder="Min" class="w-full px-3 py-2 border border-gray-300 rounded text-sm" id="priceMin">
                                        <span class="text-gray-400">to</span>
                                        <input type="number" placeholder="Max" class="w-full px-3 py-2 border border-gray-300 rounded text-sm" id="priceMax">
                                    </div>
                                </div>
                                
                                <!-- Compatibility Score -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Compatibility</label>
                                    <select class="w-full px-3 py-2 border border-gray-300 rounded text-sm" id="minCompatibility">
                                        <option value="0">Any Score</option>
                                        <option value="90">90%+ (Excellent)</option>
                                        <option value="80">80%+ (Good)</option>
                                        <option value="70">70%+ (Fair)</option>
                                        <option value="60">60%+ (Basic)</option>
                                    </select>
                                </div>
                                
                                <!-- Brands -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Brands</label>
                                    <div class="space-y-2">
                                        <?php
                                        $brands = ['WaterMaster', 'AquaForce', 'EcoPool', 'PowerFlow', 'CleanFlow', 'ProFilter', 'ClearWater', 'SandMaster', 'ThermoSpa', 'EcoTherm', 'EcoHeat', 'GasMaster', 'SpaCare', 'RoboClean', 'AquaClean', 'PoolCare', 'CoverMaster', 'AquaTest'];
                                        foreach ($brands as $brand): ?>
                                        <label class="flex items-center">
                                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" value="<?php echo $brand; ?>">
                                            <span class="ml-2 text-sm text-gray-700"><?php echo $brand; ?></span>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <!-- In Stock Only -->
                                <div class="flex items-center">
                                    <input type="checkbox" id="inStockOnly" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <label for="inStockOnly" class="ml-2 text-sm text-gray-700">In Stock Only</label>
                                </div>
                                
                                <button onclick="applyFilters()" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium mt-4">
                                    Apply Filters
                                </button>
                                <button onclick="resetFilters()" class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                                    Reset Filters
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6">
                            <h3 class="font-bold text-gray-900 mb-4">Quick Actions</h3>
                            <div class="space-y-3">
                                <button onclick="addAllCompatible()" class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium flex items-center justify-center gap-2">
                                    <i class="fas fa-cart-plus"></i> Add All Compatible to Cart
                                </button>
                                <button onclick="createPackage()" class="w-full px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium flex items-center justify-center gap-2">
                                    <i class="fas fa-box"></i> Create Equipment Package
                                </button>
                                <a href="energy-calculator.php?pool_id=<?php echo $pool_id; ?>" class="block w-full px-4 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 font-medium text-center">
                                    <i class="fas fa-bolt mr-2"></i> Energy Calculator
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content: Compatible Products -->
            <div class="lg:col-span-2">
                <!-- Category Navigation -->
                <div class="mb-6">
                    <div class="flex flex-wrap gap-2">
                        <button onclick="showCategory('all')" class="px-4 py-2 bg-blue-600 text-white rounded-full font-medium category-filter active" data-category="all">
                            All Products (<?php echo $total_products; ?>)
                        </button>
                        <?php foreach ($compatible_products as $category => $products): 
                            if (!empty($products)): 
                                $category_name = ucfirst($category);
                                $count = count($products);
                        ?>
                        <button onclick="showCategory('<?php echo $category; ?>')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full font-medium hover:bg-gray-200 category-filter" data-category="<?php echo $category; ?>">
                            <?php echo $category_name; ?> (<?php echo $count; ?>)
                        </button>
                        <?php endif; endforeach; ?>
                    </div>
                </div>
                
                <!-- Products Grid -->
                <div id="productsContainer">
                    <?php foreach ($compatible_products as $category => $products): 
                        if (empty($products)) continue;
                        $category_name = ucfirst($category);
                    ?>
                    <div class="category-section mb-8" data-category="<?php echo $category; ?>">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                <?php 
                                $icons = [
                                    'pumps' => 'fa-tint',
                                    'filters' => 'fa-filter',
                                    'heaters' => 'fa-fire',
                                    'cleaners' => 'fa-broom',
                                    'chemicals' => 'fa-flask',
                                    'accessories' => 'fa-tools'
                                ];
                                ?>
                                <i class="fas <?php echo $icons[$category] ?? 'fa-box'; ?> text-blue-600"></i>
                                <?php echo $category_name; ?>
                            </h3>
                            <span class="text-sm text-gray-500"><?php echo count($products); ?> compatible products</span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($products as $product): 
                                $compat_class = '';
                                if ($product['compatibility_score'] >= 90) $compat_class = 'compatibility-score-90';
                                elseif ($product['compatibility_score'] >= 80) $compat_class = 'compatibility-score-80';
                                elseif ($product['compatibility_score'] >= 70) $compat_class = 'compatibility-score-70';
                                else $compat_class = 'compatibility-score-60';
                                
                                $display_price = $is_trader ? $product['trade_price'] : $product['retail_price'];
                                $price_display = $is_trader ? 
                                    '<span class="trade-price">£' . number_format($product['trade_price'], 2) . '</span> <span class="retail-price">£' . number_format($product['retail_price'], 2) . '</span>' :
                                    '<span class="trade-price">£' . number_format($product['retail_price'], 2) . '</span>';
                            ?>
                            <div class="product-card bg-white" data-product-id="<?php echo $product['id']; ?>" 
                                 data-price="<?php echo $display_price; ?>" 
                                 data-compatibility="<?php echo $product['compatibility_score']; ?>"
                                 data-brand="<?php echo $product['brand']; ?>"
                                 data-stock="<?php echo $product['stock']; ?>">
                                <div class="p-5">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h4 class="font-bold text-gray-900 text-lg"><?php echo htmlspecialchars($product['name']); ?></h4>
                                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($product['brand']); ?> • SKU: <?php echo $product['sku']; ?></p>
                                        </div>
                                        <span class="compatibility-badge <?php echo $compat_class; ?>">
                                            <?php echo $product['compatibility_score']; ?>%
                                        </span>
                                    </div>
                                    
                                    <p class="text-gray-700 mb-4 text-sm"><?php echo htmlspecialchars($product['description']); ?></p>
                                    
                                    <!-- Specifications -->
                                    <div class="mb-4">
                                        <p class="text-xs text-gray-500 mb-2">Specifications:</p>
                                        <div class="flex flex-wrap gap-1">
                                            <?php 
                                            $specs = explode(', ', $product['specs']);
                                            foreach ($specs as $spec): 
                                                if (trim($spec)): ?>
                                            <span class="spec-badge"><?php echo htmlspecialchars(trim($spec)); ?></span>
                                            <?php endif; endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Price & Stock -->
                                    <div class="flex justify-between items-center mb-4">
                                        <div>
                                            <div class="text-xl font-bold">
                                                <?php echo $price_display; ?>
                                            </div>
                                            <?php if ($is_trader): ?>
                                            <div class="text-xs text-green-600 font-medium">
                                                Trade price applied (<?php echo $user_price_tier; ?>)
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm <?php echo $product['stock'] > 10 ? 'text-green-600' : ($product['stock'] > 0 ? 'text-orange-600' : 'text-red-600'); ?>">
                                                <?php if ($product['stock'] > 10): ?>
                                                <i class="fas fa-check-circle"></i> In Stock (<?php echo $product['stock']; ?>)
                                                <?php elseif ($product['stock'] > 0): ?>
                                                <i class="fas fa-exclamation-circle"></i> Low Stock (<?php echo $product['stock']; ?>)
                                                <?php else: ?>
                                                <i class="fas fa-times-circle"></i> Out of Stock
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div class="flex gap-2">
                                        <form method="POST" class="flex-1">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <input type="hidden" name="add_to_cart" value="1">
                                            <div class="flex gap-2">
                                                <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" 
                                                       class="w-20 px-3 py-2 border border-gray-300 rounded text-center" 
                                                       <?php if ($product['stock'] <= 0): ?>disabled<?php endif; ?>>
                                                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium <?php if ($product['stock'] <= 0): ?>opacity-50 cursor-not-allowed<?php endif; ?>"
                                                        <?php if ($product['stock'] <= 0): ?>disabled<?php endif; ?>>
                                                    <i class="fas fa-cart-plus mr-2"></i> Add to Cart
                                                </button>
                                            </div>
                                        </form>
                                        <button onclick="viewProduct(<?php echo $product['id']; ?>)" class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="addToWishlist(<?php echo $product['id']; ?>)" class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Comparison Table -->
                <div class="mt-12 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Product Comparison</h3>
                        <p class="text-gray-600 text-sm mt-1">Compare up to 3 products side by side</p>
                    </div>
                    
                    <div class="p-6">
                        <div id="comparisonTable">
                            <table class="w-full text-left text-sm text-gray-600 comparison-table">
                                <thead class="bg-gray-50 text-gray-900 font-semibold">
                                    <tr>
                                        <th class="px-4 py-3">Feature</th>
                                        <th class="px-4 py-3">Product 1</th>
                                        <th class="px-4 py-3">Product 2</th>
                                        <th class="px-4 py-3">Product 3</th>
                                    </tr>
                                </thead>
                                <tbody id="comparisonBody">
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                            <i class="fas fa-exchange-alt text-2xl text-gray-300 mb-2 block"></i>
                                            <p>Select products to compare using the "Compare" button</p>
                                            <p class="text-xs text-gray-400 mt-1">You can compare up to 3 products</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-6 flex justify-between items-center">
                            <div class="text-sm text-gray-600">
                                <i class="fas fa-info-circle mr-1"></i>
                                Select products and click "Add to Comparison"
                            </div>
                            <div class="flex gap-2">
                                <button onclick="clearComparison()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                                    Clear
                                </button>
                                <button onclick="updateComparison()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    Update Comparison
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recommendations -->
                <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">System Recommendations</h3>
                        <p class="text-gray-600 text-sm mt-1">Complete equipment packages for your pool</p>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Package -->
                            <div class="border border-gray-200 rounded-lg p-5 hover:border-blue-300 transition">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h4 class="font-bold text-gray-900">Essential Package</h4>
                                        <p class="text-sm text-gray-600">Basic equipment for pool operation</p>
                                    </div>
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                        Recommended
                                    </span>
                                </div>
                                <ul class="space-y-2 mb-4">
                                    <li class="flex items-center text-sm">
                                        <i class="fas fa-check text-green-500 mr-2"></i>
                                        Pump + Filter System
                                    </li>
                                    <li class="flex items-center text-sm">
                                        <i class="fas fa-check text-green-500 mr-2"></i>
                                        Basic Chemical Kit
                                    </li>
                                    <li class="flex items-center text-sm">
                                        <i class="fas fa-check text-green-500 mr-2"></i>
                                        Manual Cleaning Equipment
                                    </li>
                                </ul>
                                <div class="flex justify-between items-center">
                                    <div class="font-bold text-lg">
                                        <?php if ($is_trader): ?>
                                        <span class="text-gray-500 line-through">£1,299.99</span>
                                        <span class="text-green-600 ml-2">£<?php echo number_format(1299.99 * ($price_multiplier ?? 1), 2); ?></span>
                                        <?php else: ?>
                                        <span>£1,299.99</span>
                                        <?php endif; ?>
                                    </div>
                                    <button onclick="viewPackage('basic')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                        View Package
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Premium Package -->
                            <div class="border border-gray-200 rounded-lg p-5 hover:border-purple-300 transition">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h4 class="font-bold text-gray-900">Premium Package</h4>
                                        <p class="text-sm text-gray-600">Complete system with automation</p>
                                    </div>
                                    <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-medium">
                                        Best Value
                                    </span>
                                </div>
                                <ul class="space-y-2 mb-4">
                                    <li class="flex items-center text-sm">
                                        <i class="fas fa-check text-purple-500 mr-2"></i>
                                        Variable Speed Pump
                                    </li>
                                    <li class="flex items-center text-sm">
                                        <i class="fas fa-check text-purple-500 mr-2"></i>
                                        High-Efficiency Filter
                                    </li>
                                    <li class="flex items-center text-sm">
                                        <i class="fas fa-check text-purple-500 mr-2"></i>
                                        Heat Pump + Robotic Cleaner
                                    </li>
                                    <li class="flex items-center text-sm">
                                        <i class="fas fa-check text-purple-500 mr-2"></i>
                                        Digital Monitoring System
                                    </li>
                                </ul>
                                <div class="flex justify-between items-center">
                                    <div class="font-bold text-lg">
                                        <?php if ($is_trader): ?>
                                        <span class="text-gray-500 line-through">£3,999.99</span>
                                        <span class="text-purple-600 ml-2">£<?php echo number_format(3999.99 * ($price_multiplier ?? 1), 2); ?></span>
                                        <?php else: ?>
                                        <span>£3,999.99</span>
                                        <?php endif; ?>
                                    </div>
                                    <button onclick="viewPackage('premium')" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 text-sm">
                                        View Package
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Quick View Modal -->
    <div id="productModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-6 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-900" id="modalProductName">Product Details</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="p-6" id="modalProductContent">
                <!-- Content loaded via JavaScript -->
            </div>
        </div>
    </div>

    <?php include('include/footer.php') ?>
    
    <!-- Scripts -->
    <script>
        let comparisonProducts = [];
        
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize category filtering
            setupCategoryFiltering();
        });
        
        // Category Filtering
        function setupCategoryFiltering() {
            const filterButtons = document.querySelectorAll('.category-filter');
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Update active button
                    filterButtons.forEach(btn => btn.classList.remove('active', 'bg-blue-600', 'text-white'));
                    filterButtons.forEach(btn => btn.classList.add('bg-gray-100', 'text-gray-700'));
                    this.classList.remove('bg-gray-100', 'text-gray-700');
                    this.classList.add('active', 'bg-blue-600', 'text-white');
                    
                    const category = this.dataset.category;
                    showCategory(category);
                });
            });
        }
        
        function showCategory(category) {
            const sections = document.querySelectorAll('.category-section');
            
            if (category === 'all') {
                sections.forEach(section => {
                    section.style.display = 'block';
                });
            } else {
                sections.forEach(section => {
                    if (section.dataset.category === category) {
                        section.style.display = 'block';
                    } else {
                        section.style.display = 'none';
                    }
                });
            }
            
            // Scroll to top of products
            document.getElementById('productsContainer').scrollIntoView({ behavior: 'smooth' });
        }
        
        // Filter Products
        function applyFilters() {
            const minPrice = parseFloat(document.getElementById('priceMin').value) || 0;
            const maxPrice = parseFloat(document.getElementById('priceMax').value) || Infinity;
            const minCompatibility = parseInt(document.getElementById('minCompatibility').value) || 0;
            const inStockOnly = document.getElementById('inStockOnly').checked;
            
            // Get selected brands
            const selectedBrands = [];
            document.querySelectorAll('input[type="checkbox"]:checked').forEach(cb => {
                if (cb.value && !cb.id.includes('StockOnly')) {
                    selectedBrands.push(cb.value);
                }
            });
            
            const productCards = document.querySelectorAll('.product-card');
            
            productCards.forEach(card => {
                const price = parseFloat(card.dataset.price) || 0;
                const compatibility = parseInt(card.dataset.compatibility) || 0;
                const brand = card.dataset.brand || '';
                const stock = parseInt(card.dataset.stock) || 0;
                
                let visible = true;
                
                // Price filter
                if (price < minPrice || price > maxPrice) {
                    visible = false;
                }
                
                // Compatibility filter
                if (compatibility < minCompatibility) {
                    visible = false;
                }
                
                // Brand filter
                if (selectedBrands.length > 0 && !selectedBrands.includes(brand)) {
                    visible = false;
                }
                
                // Stock filter
                if (inStockOnly && stock <= 0) {
                    visible = false;
                }
                
                // Show/hide product
                card.style.display = visible ? 'block' : 'none';
                
                // Hide empty categories
                const categorySection = card.closest('.category-section');
                if (categorySection) {
                    const visibleProducts = categorySection.querySelectorAll('.product-card[style*="block"]');
                    categorySection.style.display = visibleProducts.length > 0 ? 'block' : 'none';
                }
            });
            
            // Update category counts
            updateCategoryCounts();
        }
        
        function resetFilters() {
            document.getElementById('priceMin').value = '';
            document.getElementById('priceMax').value = '';
            document.getElementById('minCompatibility').value = '0';
            document.getElementById('inStockOnly').checked = false;
            
            // Uncheck all brand checkboxes
            document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                cb.checked = false;
            });
            
            // Show all products
            const productCards = document.querySelectorAll('.product-card');
            productCards.forEach(card => {
                card.style.display = 'block';
            });
            
            // Show all categories
            const sections = document.querySelectorAll('.category-section');
            sections.forEach(section => {
                section.style.display = 'block';
            });
            
            updateCategoryCounts();
        }
        
        function updateCategoryCounts() {
            // This would update the count badges on category buttons
            // For simplicity, we'll just reset to show all
            showCategory('all');
        }
        
        // Product Actions
        function viewProduct(productId) {
            // In a real application, you would fetch product details from the server
            // For now, we'll simulate with the data we have
            
            // Find product in our data
            let product = null;
            <?php 
            $all_products = [];
            foreach ($compatible_products as $category => $products) {
                foreach ($products as $p) {
                    $all_products[] = $p;
                }
            }
            ?>
            
            const products = <?php echo json_encode($all_products); ?>;
            product = products.find(p => p.id == productId);
            
            if (!product) return;
            
            // Update modal content
            document.getElementById('modalProductName').textContent = product.name;
            
            const compat_class = product.compatibility_score >= 90 ? 'compatibility-score-90' :
                               product.compatibility_score >= 80 ? 'compatibility-score-80' :
                               product.compatibility_score >= 70 ? 'compatibility-score-70' : 'compatibility-score-60';
            
            const modalContent = `
                <div class="space-y-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="font-bold text-gray-900 text-lg">${product.name}</h4>
                            <p class="text-gray-600">${product.brand} • SKU: ${product.sku}</p>
                        </div>
                        <span class="compatibility-badge ${compat_class}">${product.compatibility_score}%</span>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h5 class="font-semibold text-gray-900 mb-2">Description</h5>
                        <p class="text-gray-700">${product.description}</p>
                    </div>
                    
                    <div>
                        <h5 class="font-semibold text-gray-900 mb-2">Specifications</h5>
                        <div class="grid grid-cols-2 gap-4">
                            ${product.specs.split(', ').map(spec => `
                                <div class="flex justify-between border-b border-gray-100 pb-1">
                                    <span class="text-gray-600">${spec.split(':')[0] || spec}</span>
                                    <span class="font-medium">${spec.split(':')[1] || ''}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h5 class="font-semibold text-gray-900 mb-1">Compatibility Score</h5>
                            <div class="flex items-center gap-2">
                                <div class="compatibility-meter flex-1">
                                    <div class="compatibility-fill" style="width: ${product.compatibility_score}%; background-color: #10b981;"></div>
                                </div>
                                <span class="font-bold">${product.compatibility_score}%</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">Based on your pool specifications</p>
                        </div>
                        
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h5 class="font-semibold text-gray-900 mb-1">Pricing</h5>
                            <div class="space-y-1">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Retail Price:</span>
                                    <span class="font-bold">£${product.retail_price.toFixed(2)}</span>
                                </div>
                                <?php if ($is_trader): ?>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Your Price:</span>
                                    <span class="font-bold text-green-600">£${product.trade_price.toFixed(2)}</span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex gap-3">
                        <form method="POST" class="flex-1">
                            <input type="hidden" name="product_id" value="${product.id}">
                            <input type="hidden" name="add_to_cart" value="1">
                            <div class="flex gap-2">
                                <input type="number" name="quantity" value="1" min="1" max="${product.stock}" 
                                       class="w-20 px-3 py-2 border border-gray-300 rounded text-center" 
                                       ${product.stock <= 0 ? 'disabled' : ''}>
                                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium ${product.stock <= 0 ? 'opacity-50 cursor-not-allowed' : ''}"
                                        ${product.stock <= 0 ? 'disabled' : ''}>
                                    <i class="fas fa-cart-plus mr-2"></i> Add to Cart
                                </button>
                            </div>
                        </form>
                        <button onclick="addToComparison(${product.id})" class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                            <i class="fas fa-exchange-alt mr-2"></i> Compare
                        </button>
                    </div>
                </div>
            `;
            
            document.getElementById('modalProductContent').innerHTML = modalContent;
            document.getElementById('productModal').classList.remove('hidden');
        }
        
        function closeModal() {
            document.getElementById('productModal').classList.add('hidden');
        }
        
        // Comparison Functions
        function addToComparison(productId) {
            if (comparisonProducts.includes(productId)) {
                alert('Product already in comparison');
                return;
            }
            
            if (comparisonProducts.length >= 3) {
                alert('Maximum 3 products can be compared at once');
                return;
            }
            
            comparisonProducts.push(productId);
            alert('Product added to comparison');
            updateComparison();
        }
        
        function updateComparison() {
            if (comparisonProducts.length === 0) {
                document.getElementById('comparisonBody').innerHTML = `
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-exchange-alt text-2xl text-gray-300 mb-2 block"></i>
                            <p>Select products to compare using the "Compare" button</p>
                            <p class="text-xs text-gray-400 mt-1">You can compare up to 3 products</p>
                        </td>
                    </tr>
                `;
                return;
            }
            
            // In a real application, you would fetch product details from the server
            // For now, we'll show a message
            document.getElementById('comparisonBody').innerHTML = `
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-spinner fa-spin text-2xl text-gray-300 mb-2 block"></i>
                        <p>Loading comparison data for ${comparisonProducts.length} products...</p>
                    </td>
                </tr>
            `;
            
            // Simulate API call
            setTimeout(() => {
                const comparisonRows = `
                    <tr>
                        <td class="px-4 py-3 font-medium">Product Name</td>
                        <td class="px-4 py-3">Product ${comparisonProducts[0]}</td>
                        ${comparisonProducts[1] ? `<td class="px-4 py-3">Product ${comparisonProducts[1]}</td>` : '<td class="px-4 py-3 text-gray-400">-</td>'}
                        ${comparisonProducts[2] ? `<td class="px-4 py-3">Product ${comparisonProducts[2]}</td>` : '<td class="px-4 py-3 text-gray-400">-</td>'}
                    </tr>
                    <tr>
                        <td class="px-4 py-3 font-medium">Compatibility</td>
                        <td class="px-4 py-3"><span class="compatibility-badge compatibility-score-90">95%</span></td>
                        ${comparisonProducts[1] ? `<td class="px-4 py-3"><span class="compatibility-badge compatibility-score-80">85%</span></td>` : '<td class="px-4 py-3 text-gray-400">-</td>'}
                        ${comparisonProducts[2] ? `<td class="px-4 py-3"><span class="compatibility-badge compatibility-score-70">78%</span></td>` : '<td class="px-4 py-3 text-gray-400">-</td>'}
                    </tr>
                    <tr>
                        <td class="px-4 py-3 font-medium">Price</td>
                        <td class="px-4 py-3 font-bold">£299.99</td>
                        ${comparisonProducts[1] ? `<td class="px-4 py-3 font-bold">£449.99</td>` : '<td class="px-4 py-3 text-gray-400">-</td>'}
                        ${comparisonProducts[2] ? `<td class="px-4 py-3 font-bold">£199.99</td>` : '<td class="px-4 py-3 text-gray-400">-</td>'}
                    </tr>
                `;
                
                document.getElementById('comparisonBody').innerHTML = comparisonRows;
            }, 1000);
        }
        
        function clearComparison() {
            comparisonProducts = [];
            updateComparison();
        }
        
        // Other Functions
        function addAllCompatible() {
            if (confirm('Add all compatible products to cart? This may add multiple items.')) {
                // In a real application, you would make an API call
                alert('All compatible products added to cart!');
            }
        }
        
        function createPackage() {
            alert('Package creation would open a configurator to build a complete equipment system.');
        }
        
        function viewPackage(packageType) {
            alert(`Viewing ${packageType} package details. This would show a detailed breakdown.`);
        }
        
        function generateReport() {
            alert('Generating compatibility report PDF... This would create a downloadable report.');
        }
        
        function addToWishlist(productId) {
            alert(`Product ${productId} added to wishlist!`);
        }
        
        // Close modal on outside click
        document.getElementById('productModal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('productModal')) {
                closeModal();
            }
        });
    </script>
</body>
</html>