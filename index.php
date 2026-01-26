<?php
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.gc_maxlifetime', 3600);
    
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Fetch new products from database
    require_once 'config.php';
    
    $newProducts = [];
    $popularProducts = [];
    
    try {
        $conn = getDbConnection();
        
        // Fetch new products (limit 6)
        $newSql = "SELECT 
                    id,
                    product_name as name,
                    sku_number as sku,
                    price,
                    brand_name as brand,
                    stock_status as stock,
                    quantity,
                    product_description as description,
                    image,
                    category
                FROM products 
                WHERE is_new_product = 1
                ORDER BY id DESC
                LIMIT 6";
        
        $newResult = $conn->query($newSql);
        if ($newResult) {
            while ($row = $newResult->fetch_assoc()) {
                $image = $row['image'];
                
                $row['image'] = $image;
                $newProducts[] = $row;
            }
        }
        
        // Fetch popular products (limit 6)
        $popularSql = "SELECT 
                        id,
                        product_name as name,
                        sku_number as sku,
                        price,
                        brand_name as brand,
                        stock_status as stock,
                        quantity,
                        product_description as description,
                        image,
                        category
                    FROM products 
                    WHERE is_popular_product = 1
                    ORDER BY id DESC
                    LIMIT 6";
        
        $popularResult = $conn->query($popularSql);
        if ($popularResult) {
            while ($row = $popularResult->fetch_assoc()) {
                $image = $row['image'];
                
                $row['image'] = $image;
                $popularProducts[] = $row;
            }
        }
        
        closeDbConnection($conn);
    } catch (Exception $e) {
        error_log("Error fetching products: " . $e->getMessage());
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commercial Pool Equipment</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Adobe Fonts - Myriad Pro -->
    <link rel="stylesheet" href="https://use.typekit.net/yzr5vmg.css">
    <!-- Font Awesome for hamburger icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="assets/css/styles.css" rel="stylesheet" />

    <?php include('include/style.php') ?>
</head>
<body class="font-sans">
    <?php include('include/header.php') ?>

    <!-- Hero Banner  -->
    <section class="carousel-container">
        <!-- Single Hero Slide -->
        <div class="carousel-slide active">
            <img src="assets/img/dolphinpoolcleaner__19107.jpg" alt="Outdoor Leisure Equipment" onerror="this.src='https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?w=1920&h=600&fit=crop'">
            <div class="carousel-overlay">
                <div class="text-center px-4">
                    <h1 class="hero-title text-white  md:text-4xl lg:text-5xl font-bold mb-6 leading-tight drop-shadow-lg">
                        Welcome to <br />Commercial Pool Equipment
                    </h1>
                    <a href="product.php" class="shop-now-btn">Shop Now</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Category Section -->
    <section class="py-12 md:py-16">
        <div class="container mx-auto px-4 max-w-[1400px]">
            <!-- Section Header -->
            <div class="mb-8 px-8">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 uppercase tracking-wide">
                    Shop by Category
                </h2>
            </div>
            
            <!-- Category Slider -->
            <div class="category-slider">
                <!-- Left Arrow -->
                <button class="slider-nav left disabled" id="prevBtn" aria-label="Previous categories">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </button>
                
                <!-- Category Track -->
                <div class="category-track" id="categoryTrack">
                    <!-- Category 1: Pumps & Filters -->
                    <a href="product.php?category=bathroom" class="category-card">
                        <img src="assets/img/pumps-filters.jpg" alt="Pumps & Filters" onerror="this.src='https://images.unsplash.com/photo-1566073771259-6a8506099945?w=500&h=600&fit=crop&auto=format'">
                        <div class="category-title">
                            <h3 class="category-name">Pumps & Filters</h3>
                        </div>
                    </a>
                    
                    <!-- Category 2: Lights -->
                    <a href="product.php?category=kitchen" class="category-card">
                        <img src="assets/img/lights.jpg" alt="Lights" onerror="this.src='https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=500&h=600&fit=crop&auto=format'">
                        <div class="category-title">
                            <h3 class="category-name">Lights</h3>
                        </div>
                    </a>
                    
                    <!-- Category 3: Heaters -->
                    <a href="product.php?category=kitchen" class="category-card">
                        <img src="assets/img/heaters.jpg" alt="Heaters" onerror="this.src='https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=500&h=600&fit=crop&auto=format'">
                        <div class="category-title">
                            <h3 class="category-name">Heaters</h3>
                        </div>
                    </a>
                    
                    <!-- Category 4: Cleaners -->
                    <a href="product.php?category=kitchen" class="category-card">
                        <img src="assets/img/cleaners.jpg" alt="Cleaners" onerror="this.src='https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=500&h=600&fit=crop&auto=format'">
                        <div class="category-title">
                            <h3 class="category-name">Cleaners</h3>
                        </div>
                    </a>
                    
                    <!-- Category 5: Covers -->
                    <a href="product.php?category=gas-water" class="category-card">
                        <img src="assets/img/covers.jpg" alt="Covers" onerror="this.src='https://images.unsplash.com/photo-1575429198097-0414ec08e8cd?w=500&h=600&fit=crop&auto=format'">
                        <div class="category-title">
                            <h3 class="category-name">Covers</h3>
                        </div>
                    </a>
                    
                    <!-- Category 6: Competition -->
                    <a href="product.php?category=heater-air-cons" class="category-card">
                        <img src="assets/img/competition.jpg" alt="Competition Equipment" onerror="this.src='https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=500&h=600&fit=crop&auto=format'">
                        <div class="category-title">
                            <h3 class="category-name">Competition</h3>
                        </div>
                    </a>
                    
                    <!-- Category 7: Pool Side -->
                    <a href="product.php?category=awnings" class="category-card">
                        <img src="assets/img/pool-side.jpg" alt="Pool Side Equipment" onerror="this.src='https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=500&h=600&fit=crop&auto=format'">
                        <div class="category-title">
                            <h3 class="category-name">Pool Side</h3>
                        </div>
                    </a>
                    
                    <!-- Category 8: Sauna, Spa & Therapy -->
                    <a href="product.php?category=kitchen" class="category-card">
                        <img src="assets/img/sauna-spa.jpg" alt="Sauna, Spa & Therapy" onerror="this.src='https://images.unsplash.com/photo-1600706861265-0d69d48f3a1d?w=500&h=600&fit=crop&auto=format'">
                        <div class="category-title">
                            <h3 class="category-name">Sauna, Spa & Therapy</h3>
                        </div>
                    </a>
                </div>
                
                <!-- Right Arrow -->
                <button class="slider-nav right" id="nextBtn" aria-label="Next categories">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            </div>

            <div class="mx-auto max-w-xs text-center mt-10">
                <a href="product.php" class="shop-now-btn">View More</a>
            </div>
        </div>
    </section>

    <!-- Life Guide Equipment Section -->
    <section class="campervan-section py-16 md:py-24">
        <div class="container mx-auto px-4 max-w-7xl">
            <div class="campervan-content grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                
                <!-- Left Column: Image -->
                <div class="order-2 lg:order-1">
                    <div class="conversion-image" style="height: 500px;">
                        <img src="assets/img/lifeguard-equipment.jpg" alt="Life Guard Equipment" onerror="this.src='https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800&h=1000&fit=crop&auto=format'">
                    </div>
                </div>
                
                <!-- Right Column: Content -->
                <div class="order-1 lg:order-2">
                    <div class="mb-6">
                        <span class="text-[#022658] font-bold text-sm uppercase tracking-wider">Competition & Safety</span>
                        <h2 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mt-3 mb-6 leading-tight">
                            Professional Life Guard & Competition Equipment
                        </h2>
                    </div>
                    
                    <p class="text-gray-700 text-lg mb-8 leading-relaxed">
                        Ensure maximum safety and professional standards at your pool with our comprehensive range of life guard equipment. From rescue tubes and life rings to first aid kits and emergency response gear, we provide everything needed for effective pool supervision and water safety.
                    </p>
                    
                    <p class="text-gray-700 text-lg mb-10 leading-relaxed">
                        Our competition equipment meets international standards for swimming competitions, including lane ropes, starting blocks, turn indicators, and water polo goals. Trust our expertly curated selection for reliable performance during training and competitive events.
                    </p>
                    
                    <!-- Feature Badges -->
                    <div class="mb-10 space-y-3">
                        <div class="feature-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#022658" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <span class="text-gray-800 font-semibold">Professional Rescue Equipment</span>
                        </div>
                        <div class="feature-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#022658" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <span class="text-gray-800 font-semibold">Competition Standard Equipment</span>
                        </div>
                        <div class="feature-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#022658" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <span class="text-gray-800 font-semibold">Certified Safety Standards</span>
                        </div>
                        <div class="feature-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#022658" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <span class="text-gray-800 font-semibold">Durable & Weather Resistant</span>
                        </div>
                    </div>
                    
                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="product.php?category=heater-air-cons&subcategory=competition" class="shop-now-btn text-center text-md">
                            View Life Guard Equipment
                        </a>
                        <a href="product.php?category=heater-air-cons" class="text-md text-center btn-secondary">
                            Browse Competition Gear
                        </a>
                    </div>
                </div>
                
            </div>
        </div>
    </section>

    <!-- New Products Section -->
    <section class="py-16 md:py-20 bg-white">
        <div class="container mx-auto px-4 max-w-7xl">
            <!-- Section Header -->
            <div class="text-center mb-12">
                <span class="text-[#022658] font-bold text-sm uppercase tracking-wider">Just Arrived</span>
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mt-2 mb-4">
                    New Products
                </h2>
                <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                    Discover our latest additions to help you make the most of your outdoor adventures
                </p>
            </div>
            
            <!-- Products Carousel -->
            <div class="products-carousel">
                <div class="products-track" id="newProductsTrack">
                    <?php if (!empty($newProducts)): ?>
                        <?php foreach ($newProducts as $product): ?>
                            <?php 
                                $inStock = $product['stock'] === 'In Stock' || $product['quantity'] > 0;
                            ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <span class="new-badge">NEW</span>
                                    <a href="product_detail.php?id=<?php echo $product['id']; ?>">
                                      <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                                        onerror="this.onerror=null; this.src='assets/img/Products/product1.webp';"> 
                                    </a>
                                    <div class="absolute top-2 left-2 flex flex-col gap-2">
                                        <button class="wishlist-btn bg-white rounded-full w-10 h-10 flex items-center justify-center shadow-md hover:shadow-lg text-gray-600 hover:text-red-500" 
                                                data-wishlist-btn
                                                data-wishlist-id="<?php echo $product['id']; ?>" 
                                                title="Add to Wishlist">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="product-info">
                                    <a href="product_detail.php?id=<?php echo $product['id']; ?>">
                                        <p class="text-xs text-gray-500 mb-1"><?php echo htmlspecialchars($product['brand'] ?: 'Generic'); ?></p>
                                        <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                        <p class="text-sm text-gray-600 mb-3 line-clamp-2"><?php echo htmlspecialchars(substr($product['description'] ?: '', 0, 100)); ?></p>
                                    </a>
                                    <div class="flex justify-between items-center">
                                        <div class="product-price">£<?php echo number_format($product['price'], 2); ?></div>
                                        <?php if ($inStock): ?>
                                            <button class="add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                                                Add to Cart
                                            </button>
                                        <?php else: ?>
                                            <button disabled class="bg-gray-300 text-gray-500 px-4 py-2 rounded text-sm cursor-not-allowed">
                                                Out of Stock
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-span-full text-center py-8">
                            <p class="text-gray-500">No new products available at the moment.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- View All Button -->
            <div class="text-center mt-12">
                <a href="new-products.php" class="inline-block bg-transparent text-[#022658] px-10 py-4 rounded-full font-semibold text-lg border-2 border-[#022658] hover:bg-[#022658] hover:text-white transition-all duration-300 uppercase tracking-wide">
                    View All Products
                </a>
            </div>
        </div>
    </section>

    <!-- Brand Logos Section -->
    <section class="brands-section py-12 md:py-16">
        <div class="container mx-auto px-4 max-w-7xl">
            <!-- Section Header -->
            <div class="text-center mb-10">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3">Trusted Brands</h2>
                <p class="text-gray-600">We stock premium products from leading outdoor leisure brands</p>
            </div>
            
            <!-- Brands Carousel -->
            <div class="relative">
                <div class="brands-track">
                    <!-- Brand logos remain the same -->
                    <div class="brand-logo">
                        <img src="assets/img/Brands/truma logo.png" alt="Truma" onerror="this.parentElement.innerHTML='<div class=\'brand-name\'>TRUMA</div>'">
                    </div>
                    <div class="brand-logo">
                        <img src="assets/img/Brands/M6_lg_Fiamma-1.jpg" alt="Fiamma" onerror="this.parentElement.innerHTML='<div class=\'brand-name\'>FIAMMA</div>'">
                    </div>
                    <div class="brand-logo">
                        <img src="assets/img/Brands/Vitrifrigo logo.png" alt="Vitrifrigo" onerror="this.parentElement.innerHTML='<div class=\'brand-name\'>VITRIFRIGO</div>'">
                    </div>
                    <div class="brand-logo">
                        <img src="assets/img/Brands/certikin brand logo.png" alt="Certikin" onerror="this.parentElement.innerHTML='<div class=\'brand-name\'>CERTIKIN</div>'">
                    </div>
                    <div class="brand-logo">
                        <img src="assets/img/Brands/max logo.png" alt="MAX AIR" onerror="this.parentElement.innerHTML='<div class=\'brand-name\'>MAX AIR</div>'">
                    </div>
                    <div class="brand-logo">
                        <img src="assets/img/Brands/propex logo.png" alt="PROPEX" onerror="this.parentElement.innerHTML='<div class=\'brand-name\'>PROPEX</div>'">
                    </div>
                    <div class="brand-logo">
                        <img src="assets/img/Brands/reimo logo.jpg" alt="REIMO" onerror="this.parentElement.innerHTML='<div class=\'brand-name\'>REIMO</div>'">
                    </div>
                    <div class="brand-logo">
                        <img src="assets/img/Brands/Thule logo.png" alt="Thule" onerror="this.parentElement.innerHTML='<div class=\'brand-name\'>THULE</div>'">
                    </div>
                    <div class="brand-logo">
                        <img src="assets/img/Brands/thetford-vector-logo.png" alt="Thetford" onerror="this.parentElement.innerHTML='<div class=\'brand-name\'>THETFORD</div>'">
                    </div>
                    <div class="brand-logo">
                        <img src="assets/img/Brands/Sr Smith.jpg" alt="SR Smith" onerror="this.parentElement.innerHTML='<div class=\'brand-name\'>SR SMITH</div>'">
                    </div>
                    <!-- Duplicate for seamless loop -->
                    <div class="brand-logo">
                        <img src="assets/img/Brands/truma logo.png" alt="Truma" onerror="this.parentElement.innerHTML='<div class=\'brand-name\'>TRUMA</div>'">
                    </div>
                    <div class="brand-logo">
                        <img src="assets/img/Brands/M6_lg_Fiamma-1.jpg" alt="Fiamma" onerror="this.parentElement.innerHTML='<div class=\'brand-name\'>FIAMMA</div>'">
                    </div>
                    <div class="brand-logo">
                        <img src="assets/img/Brands/Vitrifrigo logo.png" alt="Vitrifrigo" onerror="this.parentElement.innerHTML='<div class=\'brand-name\'>VITRIFRIGO</div>'">
                    </div>
                    <div class="brand-logo">
                        <img src="assets/img/Brands/certikin brand logo.png" alt="Certikin" onerror="this.parentElement.innerHTML='<div class=\'brand-name\'>CERTIKIN</div>'">
                    </div>
                    <div class="brand-logo">
                        <img src="assets/img/Brands/max logo.png" alt="MAX AIR" onerror="this.parentElement.innerHTML='<div class=\'brand-name\'>MAX AIR</div>'">
                    </div>
                    <div class="brand-logo">
                        <img src="assets/img/Brands/propex logo.png" alt="PROPEX" onerror="this.parentElement.innerHTML='<div class=\'brand-name\'>PROPEX</div>'">
                    </div>
                    <div class="brand-logo">
                        <img src="assets/img/Brands/reimo logo.jpg" alt="REIMO" onerror="this.parentElement.innerHTML='<div class=\'brand-name\'>REIMO</div>'">
                    </div>
                    <div class="brand-logo">
                        <img src="assets/img/Brands/Thule logo.png" alt="Thule" onerror="this.parentElement.innerHTML='<div class=\'brand-name\'>THULE</div>'">
                    </div>
                    <div class="brand-logo">
                        <img src="assets/img/Brands/thetford-vector-logo.png" alt="Thetford" onerror="this.parentElement.innerHTML='<div class=\'brand-name\'>THETFORD</div>'">
                    </div>
                    <div class="brand-logo">
                        <img src="assets/img/Brands/Sr Smith.jpg" alt="SR Smith" onerror="this.parentElement.innerHTML='<div class=\'brand-name\'>SR SMITH</div>'">
                    </div>
                </div>
            </div>

            <!-- View All Button -->
            <div class="text-center mt-12">
                <a href="brands.php" class="inline-block bg-transparent text-[#022658] px-10 py-4 rounded-full font-semibold text-lg border-2 border-[#022658] hover:bg-[#022658] hover:text-white transition-all duration-300 uppercase tracking-wide">
                    SEE MORE
                </a>
            </div>
        </div>
    </section>

    <!-- Popular Products Section -->
    <section class="py-16 md:py-20 bg-white">
        <div class="container mx-auto px-4 max-w-7xl">
            <!-- Section Header -->
            <div class="text-center mb-12">
                <span class="text-[#022658] font-bold text-sm uppercase tracking-wider">Most Views</span>
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mt-2 mb-4">
                    Popular Products
                </h2>
                <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                    Discover what customers mainly buy to help you make the most of your outdoor adventures
                </p>
            </div>
            
            <!-- Products Carousel -->
            <div class="products-carousel">
                <div class="products-track" id="popularProductsTrack">
                    <?php if (!empty($popularProducts)): ?>
                        <?php foreach ($popularProducts as $product): ?>
                            <?php 
                                $inStock = $product['stock'] === 'In Stock' || $product['quantity'] > 0;
                            ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <span class="popular-badge">Popular</span>
                                    <a href="product_detail.php?id=<?php echo $product['id']; ?>">
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                                        onerror="this.onerror=null; this.src='assets/img/Products/product1.webp';"> 
                                    </a>
                                    <div class="absolute top-2 left-2 flex flex-col gap-2">
                                        <button class="wishlist-btn bg-white rounded-full w-10 h-10 flex items-center justify-center shadow-md hover:shadow-lg text-gray-600 hover:text-red-500" 
                                                data-wishlist-id="<?php echo $product['id']; ?>" 
                                                data-wishlist-btn
                                                title="Add to Wishlist">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="product-info">
                                    <a href="product_detail.php?id=<?php echo $product['id']; ?>">
                                        <p class="text-xs text-gray-500 mb-1"><?php echo htmlspecialchars($product['brand'] ?: 'Generic'); ?></p>
                                        <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                        <p class="text-sm text-gray-600 mb-3 line-clamp-2"><?php echo htmlspecialchars(substr($product['description'] ?: '', 0, 100)); ?></p>
                                    </a>
                                    <div class="flex justify-between items-center">
                                        <div class="product-price">£<?php echo number_format($product['price'], 2); ?></div>
                                        <?php if ($inStock): ?>
                                            <button class="add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                                                Add to Cart
                                            </button>
                                        <?php else: ?>
                                            <button disabled class="bg-gray-300 text-gray-500 px-4 py-2 rounded text-sm cursor-not-allowed">
                                                Out of Stock
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-span-full text-center py-8">
                            <p class="text-gray-500">No popular products available at the moment.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- View All Button -->
            <div class="text-center mt-12">
                <a href="product.php" class="inline-block bg-transparent text-[#022658] px-10 py-4 rounded-full font-semibold text-lg border-2 border-[#022658] hover:bg-[#022658] hover:text-white transition-all duration-300 uppercase tracking-wide">
                    View All Products
                </a>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="py-16 px-4 bg-[#f8f9fa] relative overflow-hidden">
        <!-- Background pattern (optional) -->
        <div class="absolute inset-0 opacity-5">
            <div class="absolute top-0 left-0 w-64 h-64 bg-[#022658] rounded-full -translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-[#022658] rounded-full translate-x-1/2 translate-y-1/2"></div>
        </div>
        
        <div class="max-w-4xl mx-auto relative z-10">
            <div class="text-center mb-12 fade-in">
                <h2 class="text-4xl md:text-5xl font-bold  mb-4">
                    Stay Connected with Jacksons
                </h2>
                <p class="text-xl text-[#022658] max-w-2xl mx-auto">
                    Subscribe to our newsletter and be the first to know about new products, exclusive offers, and camping tips.
                </p>
            </div>
            
            <form id="newsletterForm" class="bg-white rounded-2xl shadow-2xl p-8 md:p-10 max-w-2xl mx-auto fade-in" style="animation-delay: 0.2s;">
                <div class="space-y-6">
                    <!-- Name Field -->
                    <div class="space-y-2">
                        <label for="name" class="block text-gray-800 font-medium">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </span>
                            <input 
                                type="text" 
                                id="name" 
                                name="name"
                                required
                                placeholder="Enter your full name"
                                class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200"
                            >
                        </div>
                    </div>
                    
                    <!-- Email Field -->
                    <div class="space-y-2">
                        <label for="email" class="block text-gray-800 font-medium">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                            </span>
                            <input 
                                type="email" 
                                id="email" 
                                name="email"
                                required
                                placeholder="your.email@example.com"
                                class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200"
                            >
                        </div>
                    </div>
                    
                    <!-- Consent Checkbox -->
                    <div class="space-y-3">
                        <label class="flex items-start space-x-3 cursor-pointer">
                            <input type="checkbox" id="consent" name="consent" class="custom-checkbox" required>
                            <span class="checkmark mt-1 flex-shrink-0"></span>
                            <span class="text-gray-700 text-sm leading-relaxed">
                                I consent to receiving marketing emails from Welcome to Commercial Pool Equipment and Supplies Limited. I understand that I can unsubscribe at any time by clicking the link in the footer of our emails. For information about our privacy practices, please visit our 
                                <a href="/privacy-policy" class="text-orange-600 font-medium hover:text-orange-700 underline">Privacy Policy</a>.
                                <span class="text-red-500"> *</span>
                            </span>
                        </label>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button 
                            type="submit"
                            id="subscribeBtn"
                            class="w-full bg-[#022658] hover:bg-white hover:text-black text-white font-bold py-4 px-8 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 focus:ring-4 focus:ring-orange-300 focus:ring-opacity-50"
                        >
                            <span class="flex items-center justify-center space-x-3">
                                <span class="text-lg">Subscribe Now</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-send">
                                    <line x1="22" y1="2" x2="11" y2="13"></line>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                </svg>
                            </span>
                        </button>
                        <p class="text-center text-gray-600 text-sm mt-3">
                            Get 10% off your first order as a welcome gift!
                        </p>
                    </div>
                </div>
                
                <!-- Success Message (Hidden by default) -->
                <div id="successMessage" class="hidden mt-6 p-4 bg-[#022658]/10 border border-[#022658]/20 rounded-xl">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-[#022658]/20 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#022658" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-[#022658]">Welcome to Commercial Pool Equipment and Supplies Limited!</h3>
                            <p class="text-[#022658] text-sm">
                                Thank you for subscribing! Check your email for a special welcome message and your 10% discount code.
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Footer Section -->
    <?php include('include/footer.php') ?>

    <?php include('include/script.php') ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add to cart buttons
            document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    addToCart(parseInt(btn.dataset.productId), btn);
                });
            });
        });
        

        
        async function addToCart(productId, buttonElement) {
            if (buttonElement) {
                buttonElement.disabled = true;
                const originalText = buttonElement.textContent;
                buttonElement.textContent = 'Adding...';
            }
            
            try {
                const response = await fetch('api/cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'add', product_id: productId, quantity: 1 })
                });
                const data = await response.json();
                if (data.success) {
                    showNotification(data.message || 'Added to cart', 'success');
                    updateCartCount(data.cart_count || data.total_items);
                } else {
                    showNotification(data.message || 'Failed to add to cart', 'error');
                }
            } catch (error) {
                console.error('Error adding to cart:', error);
                showNotification('An error occurred.', 'error');
            } finally {
                if (buttonElement) {
                    buttonElement.disabled = false;
                    buttonElement.textContent = 'Add to Cart';
                }
            }
        }
        
        function updateCartCount(count) {
            const cartBadge = document.querySelector('.cart-count, #cartCount');
            if (cartBadge) {
                cartBadge.textContent = count;
                cartBadge.classList.toggle('hidden', count <= 0);
            }
        }
        
        function showNotification(message, type = 'info') {
            const colors = { success: 'bg-[#022658]', error: 'bg-red-500', warning: 'bg-yellow-500', info: 'bg-blue-500' };
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 translate-x-[400px]`;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.classList.remove('translate-x-[400px]'), 10);
            setTimeout(() => {
                notification.classList.add('translate-x-[400px]');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>