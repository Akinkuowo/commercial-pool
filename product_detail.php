<?php
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.gc_maxlifetime', 3600);
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once 'config.php';

    // Get product ID from URL
    $product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($product_id <= 0) {
        header('Location: product.php');
        exit();
    }

    // Function to get product image with fallback
    function getProductImage($image, $category = '') {
        // If image exists in database and is not empty, use it
        if (!empty($image) && $image !== 'NULL') {
            return $image;
        }
        
        // Otherwise use category-based fallback
        $categoryLower = strtolower($category);
        $imageMap = [
            'awning' => 'assets/img/Products/product2.webp',
            'camping' => 'assets/img/Products/product1.webp',
            'caravan' => 'assets/img/Products/product8.jpg',
            'electrical' => 'assets/img/Products/product7.jpeg',
            'heating' => 'assets/img/Products/product4.jpg',
            'kitchen' => 'assets/img/Products/product3.jpg',
            'fridge' => 'assets/img/Products/product5.jpg',
            'water' => 'assets/img/Products/product6.jpeg'
        ];
        
        foreach ($imageMap as $key => $url) {
            if (strpos($categoryLower, $key) !== false) {
                return $url;
            }
        }
        
        return 'assets/img/Products/product1.webp'; // default
    }

    try {
        // Fetch product details
        $conn = getDbConnection();
        
        // First, let's check what columns exist in the database
        $sql = "SHOW COLUMNS FROM products";
        $result = $conn->query($sql);
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        // Build SELECT query based on available columns
        $selectFields = [
            'id',
            'product_name as name',
            'sku_number as sku',
            'price',
            'brand_name as brand',
            'stock_status as stock',
            'quantity',
            'product_description as description',
            'category'
        ];
        
        // Add optional columns if they exist
        if (in_array('size_variant_model', $columns)) {
            $selectFields[] = 'size_variant_model as size';
        }
        if (in_array('colour_type', $columns)) {
            $selectFields[] = 'colour_type as color';
        }
        if (in_array('is_new_product', $columns)) {
            $selectFields[] = 'is_new_product as is_new';
        }
        if (in_array('is_popular_product', $columns)) {
            $selectFields[] = 'is_popular_product as is_popular';
        }
        if (in_array('full_category_path', $columns)) {
            $selectFields[] = 'full_category_path';
        }
        if (in_array('image', $columns)) {
            $selectFields[] = 'image';
        }
        
        $sql = "SELECT " . implode(', ', $selectFields) . " FROM products WHERE id = ?";
        
        error_log("Executing SQL: " . $sql);
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            header('Location: product.php');
            exit();
        }
        
        $product = $result->fetch_assoc();
        
        // Debug: Check what we fetched
        error_log("Product fetched: " . print_r($product, true));
        
        // Ensure all expected fields exist
        $product['image'] = $product['image'] ?? '';
        $product['category'] = $product['category'] ?? '';
        $product['size'] = $product['size'] ?? '';
        $product['color'] = $product['color'] ?? '';
        $product['is_new'] = $product['is_new'] ?? 0;
        $product['is_popular'] = $product['is_popular'] ?? 0;
        $product['description'] = $product['description'] ?? '';
        
        // Use the image from database with fallback
        $product['image'] = getProductImage($product['image'], $product['category']);
        
        // Parse category path for breadcrumb
        $categoryParts = !empty($product['category']) ? explode('>', $product['category']) : ['Uncategorized'];
        $categoryParts = array_map('trim', $categoryParts);
        
        // Fetch related products
        $relatedSql = "SELECT 
                        id,
                        product_name as name,
                        price,
                        brand_name as brand,
                        stock_status as stock,
                        category" . 
                        (in_array('is_new_product', $columns) ? ", is_new_product as is_new" : "") .
                        (in_array('is_popular_product', $columns) ? ", is_popular_product as is_popular" : "") .
                        (in_array('image', $columns) ? ", image" : "") .
                       " FROM products 
                       WHERE category LIKE ? AND id != ?
                       LIMIT 5";
        
        $categorySearch = '%' . $categoryParts[0] . '%';
        $relatedStmt = $conn->prepare($relatedSql);
        if (!$relatedStmt) {
            throw new Exception("Prepare failed for related products: " . $conn->error);
        }
        
        $relatedStmt->bind_param("si", $categorySearch, $product_id);
        $relatedStmt->execute();
        $relatedResult = $relatedStmt->get_result();
        
        $relatedProducts = [];
        while ($row = $relatedResult->fetch_assoc()) {
            // Ensure all fields exist
            $row['image'] = $row['image'] ?? '';
            $row['category'] = $row['category'] ?? '';
            $row['is_new'] = $row['is_new'] ?? 0;
            $row['is_popular'] = $row['is_popular'] ?? 0;
            
            // Use the image from database with fallback
            $row['image'] = getProductImage($row['image'], $row['category']);
            $relatedProducts[] = $row;
        }
        
        closeDbConnection($conn);
        
    } catch (Exception $e) {
        error_log("Error in product_detail.php: " . $e->getMessage());
        
        // Show a user-friendly error
        $error = "An error occurred while loading the product details. Please try again later.";
        $product = [
            'name' => 'Product Not Found',
            'description' => '',
            'price' => 0,
            'stock' => 'Out of Stock',
            'brand' => '',
            'sku' => '',
            'category' => 'Uncategorized',
            'image' => 'assets/img/Products/product1.webp',
            'is_new' => 0,
            'is_popular' => 0,
            'size' => '',
            'color' => '',
            'quantity' => 0
        ];
        $categoryParts = ['Uncategorized'];
        $relatedProducts = [];
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Commercial Pool Equipment</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Adobe Fonts - Myriad Pro -->
    <link rel="stylesheet" href="https://use.typekit.net/yzr5vmg.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="assets/css/styles.css" rel="stylesheet" />
    <?php include('include/style.php') ?>
    
    <style>
        .thumbnail-active {
            border: 2px solid #2563eb;
        }
        
        .badge-new {
            background: #022658;
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            font-style: italic;
        }
        
        .accordion-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            opacity: 0;
        }
        
        .accordion-content.active {
            max-height: 1000px;
            opacity: 1;
        }
        
        .quantity-input {
            -moz-appearance: textfield;
        }
        
        .quantity-input::-webkit-outer-spin-button,
        .quantity-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        .error-alert {
            background-color: #fee2e2;
            border: 1px solid #fca5a5;
            color: #dc2626;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="bg-gray-50">
    
    <?php include('include/header.php'); ?>

    <!-- Breadcrumb -->
    <div class="bg-white border-b border-gray-200 py-3">
        <div class="container mx-auto px-4 max-w-7xl">
            <nav class="flex text-sm text-gray-500">
                <a href="/" class="hover:text-gray-700">HOME</a>
                <?php foreach ($categoryParts as $index => $part): ?>
                    <span class="mx-2">/</span>
                    <?php if ($index === count($categoryParts) - 1): ?>
                        <span class="text-gray-900 uppercase"><?php echo htmlspecialchars($part); ?></span>
                    <?php else: ?>
                        <a href="product.php?category=<?php echo urlencode(strtolower(str_replace(' ', '-', $part))); ?>" 
                           class="hover:text-gray-700 uppercase"><?php echo htmlspecialchars($part); ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>

    <!-- Product Details -->
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <?php if (isset($error)): ?>
            <div class="error-alert mb-6">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle mr-3 text-xl"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            
            <!-- Product Images -->
            <div class="bg-white rounded-lg p-6">
                <?php if ($product['is_new'] == 1): ?>
                <div class="mb-4">
                    <span class="badge-new">NEW</span>
                </div>
                <?php endif; ?>
                
                <!-- Main Image -->
                <div class="relative mb-4">
                    <img id="mainImage" src="<?php echo htmlspecialchars($product['image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="w-full h-96 object-cover rounded-lg"
                         onerror="this.onerror=null; this.src='assets/img/Products/product1.webp';">
                    
                    <!-- Navigation Arrows -->
                    <button id="prevImage" class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white p-3 rounded-full shadow-lg transition">
                        <i class="fas fa-chevron-left text-gray-700"></i>
                    </button>
                    <button id="nextImage" class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white p-3 rounded-full shadow-lg transition">
                        <i class="fas fa-chevron-right text-gray-700"></i>
                    </button>
                </div>
                
                <!-- Thumbnail Images -->
                <div class="flex gap-3 overflow-x-auto pb-2">
                    <?php
                    // Generate thumbnail variations
                    $thumbnails = [
                        $product['image'],
                        str_replace(['.jpg', '.jpeg', '.png', '.webp'], '_2.jpg', $product['image']),
                        str_replace(['.jpg', '.jpeg', '.png', '.webp'], '_3.jpg', $product['image'])
                    ];
                    ?>
                    <?php for ($i = 0; $i < 3; $i++): ?>
                    <img src="<?php echo htmlspecialchars($thumbnails[$i]); ?>" 
                         class="thumbnail <?php echo $i === 0 ? 'thumbnail-active' : ''; ?> w-24 h-24 object-cover rounded border-2 border-gray-200 cursor-pointer transition hover:border-blue-500" 
                         data-index="<?php echo $i; ?>"
                         onerror="this.onerror=null; this.src='assets/img/Products/product1.webp';">
                    <?php endfor; ?>
                    <div class="thumbnail w-24 h-24 bg-gray-200 rounded border-2 border-gray-200 cursor-pointer transition hover:border-blue-500 flex items-center justify-center" 
                         data-index="3">
                        <span class="text-sm font-medium text-gray-600">360°</span>
                    </div>
                </div>
            </div>

            <!-- Product Info -->
            <div>
                <div class="mb-2 flex items-center gap-2 text-sm text-gray-500 uppercase">
                    <span><?php echo htmlspecialchars($product['brand'] ?: 'DELUXE AIR'); ?></span>
                </div>
                
                <h1 class="text-3xl font-bold text-gray-900 mb-4">
                    <?php echo htmlspecialchars($product['name']); ?>
                </h1>
                
                <p class="text-gray-700 mb-6 leading-relaxed">
                    <?php echo htmlspecialchars($product['description'] ?: 'This product features durable, high-quality materials and excellent design. Perfect for outdoor adventures and leisure activities.'); ?>
                </p>
                
                <!-- Features List -->
                <ul class="space-y-2 mb-6">
                    <li class="flex items-start">
                        <i class="fas fa-check text-[#022658] mt-1 mr-3"></i>
                        <span class="text-gray-700">High-quality construction</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-[#022658] mt-1 mr-3"></i>
                        <span class="text-gray-700">Durable materials for long-lasting use</span>
                    </li>
                    <?php if (!empty($product['size'])): ?>
                    <li class="flex items-start">
                        <i class="fas fa-check text-[#022658] mt-1 mr-3"></i>
                        <span class="text-gray-700">Size: <?php echo htmlspecialchars($product['size']); ?></span>
                    </li>
                    <?php endif; ?>
                    <?php if (!empty($product['color'])): ?>
                    <li class="flex items-start">
                        <i class="fas fa-check text-[#022658] mt-1 mr-3"></i>
                        <span class="text-gray-700">Color: <?php echo htmlspecialchars($product['color']); ?></span>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <!-- View Details Toggle -->
                <button id="viewDetailsBtn" class="text-blue-600 hover:text-blue-700 font-medium mb-6 flex items-center gap-2">
                    <span>View details</span>
                    <i class="fas fa-chevron-down transition-transform" id="detailsChevron"></i>
                </button>
                
                <!-- Additional Details (Hidden by default) -->
                <div id="additionalDetails" class="hidden mb-6 space-y-2 text-sm text-gray-700">
                    <p><strong>SKU:</strong> <?php echo htmlspecialchars($product['sku'] ?: 'N/A'); ?></p>
                    <p><strong>Brand:</strong> <?php echo htmlspecialchars($product['brand'] ?: 'Generic'); ?></p>
                    <?php if (!empty($product['color'])): ?>
                    <p><strong>Color:</strong> <?php echo htmlspecialchars($product['color']); ?></p>
                    <?php endif; ?>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category']); ?></p>
                </div>
                
                <!-- Price Section -->
                <div class="border-t border-gray-200 pt-6 mb-6">
                    <div class="flex items-baseline gap-3 mb-2">
                        <span class="text-sm text-gray-500">RRP £<?php echo number_format($product['price'] * 1.17, 2); ?></span>
                    </div>
                    <div class="text-4xl font-bold text-orange-500 mb-4">
                        £<?php echo number_format($product['price'], 2); ?>
                    </div>
                </div>
                
                <!-- Quantity and Add to Cart -->
                <div class="border-t border-gray-200 pt-6 mb-6">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                            <button id="decreaseQty" class="px-4 py-3 hover:bg-gray-100 transition">
                                <i class="fas fa-minus text-gray-600"></i>
                            </button>
                            <input type="number" id="quantity" value="1" min="1" 
                                   class="quantity-input w-16 text-center border-x border-gray-300 py-3 focus:outline-none">
                            <button id="increaseQty" class="px-4 py-3 hover:bg-gray-100 transition">
                                <i class="fas fa-plus text-gray-600"></i>
                            </button>
                        </div>
                        
                        <?php if ($product['stock'] === 'In Stock' || $product['quantity'] > 0): ?>
                        <button id="addToCartBtn" class="add-to-cart-btn flex-1 bg-[#022658] hover:bg-[#011a3d] text-white font-semibold py-3 px-6 rounded-lg transition flex items-center justify-center gap-2">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Add to Cart</span>
                        </button>
                        <?php else: ?>
                        <button disabled class="flex-1 bg-gray-300 text-gray-500 font-semibold py-3 px-6 rounded-lg cursor-not-allowed">
                            Out of Stock
                        </button>
                        <?php endif; ?>

                        <!-- Click & Collect Option -->
                        <?php if ($product['stock'] === 'In Stock' || $product['quantity'] > 0): ?>
                        <div class="flex items-center gap-2 mt-2">
                            <input type="checkbox" id="clickCollectOption" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                            <label for="clickCollectOption" class="text-sm font-medium text-gray-900">Click & Collect from Store</label>
                        </div>
                        <?php endif; ?>
                        
                        <button id="wishlistBtn" data-wishlist-btn data-wishlist-id="<?php echo $product_id; ?>" class="p-3 border border-gray-300 rounded-lg hover:border-red-500 hover:text-red-500 transition">
                            <i class="far fa-heart text-xl"></i>
                        </button>
                    </div>
                    
                    <?php if ($product['stock'] === 'In Stock' || $product['quantity'] > 0): ?>
                    <div class="flex items-center gap-2 text-[#022658] mb-4">
                        <i class="fas fa-check-circle"></i>
                        <span class="text-sm font-medium">Available for immediate dispatch</span>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Payment Methods -->
                    <div class="flex items-center gap-3 mb-4">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" alt="Visa" class="h-6">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard" class="h-6">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg" alt="PayPal" class="h-6">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/Apple_logo_black.svg" alt="Apple Pay" class="h-6">
                    </div>
                </div>
                
                <!-- Additional Info -->
                <div class="space-y-3 text-sm">
                    <?php if ($product['stock'] !== 'In Stock' && $product['quantity'] <= 0): ?>
                    <!-- Notify me when available (only show if out of stock) -->
                    <div class="flex items-center gap-3">
                        <i class="fas fa-bell text-gray-600"></i>
                        <button id="notifyBtn" class="text-blue-600 hover:text-blue-700 underline font-medium">
                            Notify me when available
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Free delivery -->
                    <div class="flex items-center gap-3">
                        <i class="fas fa-truck text-gray-600"></i>
                        <span class="text-gray-700"><strong>Free delivery over £600</strong> - low shipping</span>
                    </div>
                    
                    <!-- Click and collect -->
                    <div class="flex items-center gap-3">
                        <i class="fas fa-store text-gray-600"></i>
                        <button id="clickCollectBtn" class="text-blue-600 hover:text-blue-700 underline font-medium">
                            Click & Collect - Available in these stores
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Information Tabs -->
        <div class="bg-white rounded-lg shadow-sm mb-12">
            <div class="border-b border-gray-200">
                <div class="divide-y divide-gray-200">
                    <!-- Description -->
                    <div class="accordion-item">
                        <button class="accordion-header w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition text-left">
                            <span class="font-semibold text-lg text-gray-900">DESCRIPTION</span>
                            <i class="fas fa-chevron-down text-gray-600 transition-transform duration-300"></i>
                        </button>
                        <div class="accordion-content">
                            <div class="px-6 pb-6">
                                <p class="text-gray-700 leading-relaxed">
                                    <?php echo htmlspecialchars($product['description'] ?: 'This high-quality product is designed for durability and performance. Perfect for outdoor enthusiasts looking for reliable equipment that can withstand various conditions while providing comfort and convenience.'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dimensions -->
                    <div class="accordion-item">
                        <button class="accordion-header w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition text-left">
                            <span class="font-semibold text-lg text-gray-900">DIMENSIONS</span>
                            <i class="fas fa-chevron-down text-gray-600 transition-transform duration-300"></i>
                        </button>
                        <div class="accordion-content">
                            <div class="px-6 pb-6">
                                <div class="grid grid-cols-2 gap-4">
                                    <?php if (!empty($product['size'])): ?>
                                    <div>
                                        <p class="text-sm text-gray-500">Size</p>
                                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($product['size']); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="text-sm text-gray-500">Weight</p>
                                        <p class="font-medium text-gray-900">Approx. 5-10 kg</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Specifications -->
                    <div class="accordion-item">
                        <button class="accordion-header w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition text-left">
                            <span class="font-semibold text-lg text-gray-900">SPECIFICATIONS</span>
                            <i class="fas fa-chevron-down text-gray-600 transition-transform duration-300"></i>
                        </button>
                        <div class="accordion-content">
                            <div class="px-6 pb-6">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Brand</p>
                                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($product['brand'] ?: 'Generic'); ?></p>
                                    </div>
                                    <?php if (!empty($product['color'])): ?>
                                    <div>
                                        <p class="text-sm text-gray-500">Color</p>
                                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($product['color']); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="text-sm text-gray-500">Material</p>
                                        <p class="font-medium text-gray-900">High-quality materials</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">SKU</p>
                                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($product['sku'] ?: 'N/A'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Features -->
                    <div class="accordion-item">
                        <button class="accordion-header w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition text-left">
                            <span class="font-semibold text-lg text-gray-900">FEATURES</span>
                            <i class="fas fa-chevron-down text-gray-600 transition-transform duration-300"></i>
                        </button>
                        <div class="accordion-content">
                            <div class="px-6 pb-6">
                                <ul class="space-y-2">
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-[#022658] mt-1 mr-3"></i>
                                        <span class="text-gray-700">Durable construction for long-term use</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-[#022658] mt-1 mr-3"></i>
                                        <span class="text-gray-700">Weather-resistant materials</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-[#022658] mt-1 mr-3"></i>
                                        <span class="text-gray-700">Easy to assemble and use</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-[#022658] mt-1 mr-3"></i>
                                        <span class="text-gray-700">Portable and lightweight design</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (count($relatedProducts) > 0): ?>
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">RELATED PRODUCTS</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                <?php foreach ($relatedProducts as $related): ?>
                <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition overflow-hidden">
                    <div class="relative">
                        <img src="<?php echo htmlspecialchars($related['image']); ?>" 
                             alt="<?php echo htmlspecialchars($related['name']); ?>" 
                             class="w-full h-48 object-cover"
                             onerror="this.onerror=null; this.src='assets/img/Products/product1.webp';">
                        <?php if ($related['is_popular'] == 1): ?>
                        <div class="absolute top-2 right-2">
                            <span class="bg-gray-800 text-white text-xs px-2 py-1 rounded uppercase">BESTSELLER</span>
                        </div>
                        <?php endif; ?>
                        <button class="absolute top-2 left-2 bg-white rounded-full w-10 h-10 flex items-center justify-center shadow-md hover:shadow-lg text-gray-600 hover:text-red-500 transition">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2"><?php echo htmlspecialchars($related['name']); ?></h3>
                        <div class="text-lg font-bold text-gray-900 mb-2">£<?php echo number_format($related['price'], 2); ?></div>
                        <a href="product_detail.php?id=<?php echo $related['id']; ?>" 
                           class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                            View Details →
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php include('include/footer.php'); ?>
    <?php include('include/script.php') ?>

    <script>
        // Image gallery
        const mainImage = document.getElementById('mainImage');
        const thumbnails = document.querySelectorAll('.thumbnail');
        let currentImageIndex = 0;
        
        thumbnails.forEach((thumb, index) => {
            thumb.addEventListener('click', () => {
                thumbnails.forEach(t => t.classList.remove('thumbnail-active'));
                thumb.classList.add('thumbnail-active');
                currentImageIndex = index;
                
                // If it's the 360° view placeholder (index 3)
                if (index === 3) {
                    // Show a message or implement 360° view functionality
                    alert('360° view feature coming soon!');
                    return;
                }
                
                mainImage.src = thumb.src;
            });
        });
        
        document.getElementById('prevImage').addEventListener('click', () => {
            currentImageIndex = (currentImageIndex - 1 + thumbnails.length) % thumbnails.length;
            thumbnails[currentImageIndex].click();
        });
        
        document.getElementById('nextImage').addEventListener('click', () => {
            currentImageIndex = (currentImageIndex + 1) % thumbnails.length;
            thumbnails[currentImageIndex].click();
        });
        
        // Quantity controls
        const qtyInput = document.getElementById('quantity');
        document.getElementById('decreaseQty').addEventListener('click', () => {
            if (qtyInput.value > 1) qtyInput.value = parseInt(qtyInput.value) - 1;
        });
        
        document.getElementById('increaseQty').addEventListener('click', () => {
            qtyInput.value = parseInt(qtyInput.value) + 1;
        });
        
        // View details toggle
        const viewDetailsBtn = document.getElementById('viewDetailsBtn');
        const additionalDetails = document.getElementById('additionalDetails');
        const detailsChevron = document.getElementById('detailsChevron');
        
        if (viewDetailsBtn && additionalDetails && detailsChevron) {
            viewDetailsBtn.addEventListener('click', () => {
                additionalDetails.classList.toggle('hidden');
                detailsChevron.style.transform = additionalDetails.classList.contains('hidden') 
                    ? 'rotate(0deg)' 
                    : 'rotate(180deg)';
            });
        }
        
        // Accordion functionality
        document.querySelectorAll('.accordion-header').forEach(header => {
            header.addEventListener('click', () => {
                const content = header.nextElementSibling;
                const icon = header.querySelector('i');
                
                content.classList.toggle('active');
                icon.style.transform = content.classList.contains('active') 
                    ? 'rotate(180deg)' 
                    : 'rotate(0deg)';
            });
        });
        
        // Add to cart with API integration
        document.getElementById('addToCartBtn')?.addEventListener('click', async () => {
            const quantity = document.getElementById('quantity').value;
            const button = document.getElementById('addToCartBtn');
            
            // Add loading state
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            button.disabled = true;
            
            try {
                const isCollection = document.getElementById('clickCollectOption')?.checked;
                
                const response = await fetch('api/cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'add',
                        product_id: <?php echo $product_id; ?>,
                        quantity: parseInt(quantity),
                        delivery_method: isCollection ? 'collection' : 'delivery'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message || `Added ${quantity} item(s) to cart`, 'success');
                    
                    // Update cart count in header
                    updateCartCount(data.cart_count || data.total_items);
                } else {
                    showNotification(data.message || 'Failed to add to cart', 'error');
                }
            } catch (error) {
                console.error('Error adding to cart:', error);
                showNotification('An error occurred. Please try again.', 'error');
            } finally {
                // Restore button state
                button.innerHTML = originalText;
                button.disabled = false;
            }
        });
        

        
        // Update wishlist count in header
        function updateWishlistCount(count) {
            const wishlistBadge = document.querySelector('.wishlist-count, #wishlistCount');
            if (wishlistBadge) {
                wishlistBadge.textContent = count;
                if (count > 0) {
                    wishlistBadge.classList.remove('hidden');
                } else {
                    wishlistBadge.classList.add('hidden');
                }
            }
        }
        
        // Update cart count in header
        function updateCartCount(count) {
            const cartBadge = document.querySelector('.cart-count, #cartCount');
            if (cartBadge) {
                cartBadge.textContent = count;
                if (count > 0) {
                    cartBadge.classList.remove('hidden');
                } else {
                    cartBadge.classList.add('hidden');
                }
            }
            
            // Also update cart icon badge if it exists
            const cartBadgeAlt = document.querySelector('[data-cart-count]');
            if (cartBadgeAlt) {
                cartBadgeAlt.setAttribute('data-cart-count', count);
                cartBadgeAlt.textContent = count;
            }
        }
        
        // Load wishlist on page load
        async function loadWishlistFromServer() {
            try {
                const response = await fetch('api/wishlist.php');
                const data = await response.json();
                
                if (data.success && data.wishlist) {
                    wishlist = data.wishlist.map(item => item.product_id);
                    localStorage.setItem('wishlist', JSON.stringify(wishlist));
                    updateWishlistBtn();
                    updateWishlistCount(data.wishlist_count || wishlist.length);
                }
            } catch (error) {
                console.error('Error loading wishlist:', error);
            }
        }
        
        // Load cart count on page load
        async function loadCartCount() {
            try {
                const response = await fetch('api/cart.php');
                const data = await response.json();
                
                if (data.success) {
                    updateCartCount(data.total_items || 0);
                }
            } catch (error) {
                console.error('Error loading cart count:', error);
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            updateWishlistBtn();
            loadWishlistFromServer();
            loadCartCount();
        });
        
        // Notification system
        function showNotification(message, type = 'info') {
            const colors = {
                success: 'bg-[#022658]',
                error: 'bg-red-500',
                warning: 'bg-yellow-500',
                info: 'bg-blue-500'
            };
            
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300`;
            notification.style.transform = 'translateX(400px)';
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 10);
            
            setTimeout(() => {
                notification.style.transform = 'translateX(400px)';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
        
        // Notify me when available
        const notifyBtn = document.getElementById('notifyBtn');
        if (notifyBtn) {
            notifyBtn.addEventListener('click', async () => {
                // Show email input modal
                const email = prompt('Enter your email address to be notified when this product is back in stock:');
                
                if (email && email.includes('@')) {
                    try {
                        const response = await fetch('api/notify.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                product_id: <?php echo $product_id; ?>,
                                email: email
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            showNotification('You will be notified when this product is back in stock!', 'success');
                        } else {
                            showNotification(data.message || 'Failed to subscribe for notifications', 'error');
                        }
                    } catch (error) {
                        console.error('Error subscribing for notification:', error);
                        showNotification('An error occurred. Please try again.', 'error');
                    }
                } else if (email) {
                    showNotification('Please enter a valid email address', 'warning');
                }
            });
        }
        
        // Click and collect
        const clickCollectBtn = document.getElementById('clickCollectBtn');
        if (clickCollectBtn) {
            clickCollectBtn.addEventListener('click', () => {
                // Show stores modal
                const storesList = `
                    <div style="background: rgba(0,0,0,0.5); position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999; display: flex; align-items: center; justify-content: center;" id="storesModal">
                        <div style="background: white; border-radius: 12px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; padding: 24px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <h3 style="font-size: 24px; font-weight: bold; color: #111;">Click & Collect Stores</h3>
                                <button onclick="document.getElementById('storesModal').remove()" style="font-size: 24px; color: #666; background: none; border: none; cursor: pointer;">&times;</button>
                            </div>
                            <div style="space-y: 16px;">
                                <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin-bottom: 12px;">
                                    <h4 style="font-weight: 600; margin-bottom: 8px;">Commercial Pool Equipment - Main Store</h4>
                                    <p style="color: #666; font-size: 14px; margin-bottom: 4px;">123 High Street, Town Center</p>
                                    <p style="color: #666; font-size: 14px; margin-bottom: 8px;">Open: Mon-Sat 9am-6pm, Sun 10am-4pm</p>
                                    <span style="background: #022658; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: 600;">Available</span>
                                </div>
                                <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin-bottom: 12px;">
                                    <h4 style="font-weight: 600; margin-bottom: 8px;">Commercial Pool Equipment - North Branch</h4>
                                    <p style="color: #666; font-size: 14px; margin-bottom: 4px;">456 Park Avenue, Northside</p>
                                    <p style="color: #666; font-size: 14px; margin-bottom: 8px;">Open: Mon-Sat 9am-6pm, Sun 10am-4pm</p>
                                    <span style="background: #022658; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: 600;">Available</span>
                                </div>
                                <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin-bottom: 12px;">
                                    <h4 style="font-weight: 600; margin-bottom: 8px;">Commercial Pool Equipment - South Location</h4>
                                    <p style="color: #666; font-size: 14px; margin-bottom: 4px;">789 Retail Park, Southgate</p>
                                    <p style="color: #666; font-size: 14px; margin-bottom: 8px;">Open: Mon-Sat 9am-6pm, Sun 10am-4pm</p>
                                    <span style="background: #ef4444; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: 600;">Out of Stock</span>
                                </div>
                            </div>
                            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                                <p style="color: #666; font-size: 14px;">Click & Collect orders are typically ready within 2 hours during store opening times.</p>
                            </div>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', storesList);
            });
        }
    </script>

</body>
</html>