
<!-- Adobe Fonts -->
<link rel="stylesheet" href="https://use.typekit.net/yzr5vmg.css">

<style>
        *{
            font-family: 'myriad-pro', 'Myriad Pro', -apple-system, BlinkMacSystemFont, sans-serif;
        }

     /* Custom styles for announcement slider */
        .announcement-container {
            height: 24px;
            overflow: hidden;
            position: relative;
            width: 100%;
        }

        .announcement-track {
            display: flex;
            flex-direction: row;
            position: relative;
            width: 100%;
            height: 100%;
        }

        .announcement-track.paused {
            animation-play-state: paused;
        }

        .announcement-item {
            position: absolute;
            width: 100%;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            opacity: 0;
            animation: slideInOut 20s ease-in-out infinite;
        }

        .announcement-item:nth-child(1) {
            animation-delay: 0s;
        }

        .announcement-item:nth-child(2) {
            animation-delay: 5s;
        }

        .announcement-item:nth-child(3) {
            animation-delay: 10s;
        }

        .announcement-item:nth-child(4) {
            animation-delay: 15s;
        }

        @keyframes slideInOut {
            0% {
                transform: translateX(100%);
                opacity: 0;
            }
            5% {
                transform: translateX(0);
                opacity: 1;
            }
            20% {
                transform: translateX(0);
                opacity: 1;
            }
            25% {
                transform: translateX(-100%);
                opacity: 0;
            }
            100% {
                transform: translateX(-100%);
                opacity: 0;
            }
        }
        
        /* Flag icon styling */
        .flag-icon {
            flex-shrink: 0;
        }
        
        /* Custom font classes */
        .font-roboto {
            font-family: 'myriad-pro', 'Myriad Pro', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        /* Currency selector styling */
        .currency-dropdown {
            transition: all 0.3s ease-in-out;
        }

        /* Mobile menu animations */
    
        .mobile-menu {
            transition: all 0.3s ease;
        }

        .mobile-menu-nav {
            max-height: 70vh;
            overflow-y: auto;
        }

        .mobile-submenu {
            transition: all 0.3s ease;
            max-height: 0;
            overflow: hidden;
        }

        .mobile-submenu.block {
            max-height: 1000px;
        }

        .mobile-submenu-2 {
            transition: all 0.3s ease;
            max-height: 0;
            overflow: hidden;
        }

        .mobile-submenu-2.block {
            max-height: 1000px;
        }

        .mobile-menu-item.active {
            background-color: #f9f9f9;
        }

        /* Custom scrollbar for mobile menu */
        .mobile-menu::-webkit-scrollbar {
            width: 6px;
        }

        .mobile-menu::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .mobile-menu::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        .mobile-menu::-webkit-scrollbar-thumb:hover {
            background: #555;
        }


        .submenu {
            display: none;
            position: absolute;
            left: 100%;
            top: 0;
            min-width: 200px;
            background: white;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            z-index: 60;
        }
        
        .submenu-parent {
            position: relative;
        }
        
        .submenu-parent:hover > .submenu {
            display: block;
        }
        
        .submenu-indicator {
            margin-left: auto;
            transition: transform 0.2s;
        }
        
        .submenu-parent:hover .submenu-indicator {
            transform: rotate(90deg);
        }

        .submenu .submenu {
            left: 100%;
            top: -8px;
        }

        body {
            font-family: 'myriad-pro', 'Myriad Pro', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .carousel-container {
            position: relative;
            width: 100%;
            height: 350px;
            overflow: hidden;
        }
        
        .carousel-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }
        
        .carousel-slide.active {
            opacity: 1;
        }
        
        .carousel-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .carousel-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.2));
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .carousel-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.3);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }
        
        .carousel-arrow:hover {
            background: rgba(255, 255, 255, 0.5);
            transform: translateY(-50%) scale(1.1);
        }
        
        .carousel-arrow.left {
            left: 30px;
        }
        
        .carousel-arrow.right {
            right: 30px;
        }
        
        .carousel-indicators {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 12px;
            z-index: 10;
        }
        
        .indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .indicator.active {
            background: #022658;
            width: 40px;
            border-radius: 6px;
        }
        
        .shop-now-btn {
            background: #022658;
            color: white;
            padding: 16px 48px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(204, 69, 20, 0.4);
        }
        
        .shop-now-btn:hover {
            /* background: #b33d12; */
            background: #b33d12;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(204, 69, 20, 0.6);
        }
        
        @media (max-width: 768px) {
            .carousel-container {
                height: 400px;
            }
            
            .carousel-arrow {
                width: 40px;
                height: 40px;
            }
            
            .carousel-arrow.left {
                left: 15px;
            }
            
            .carousel-arrow.right {
                right: 15px;
            }
            
            .hero-title {
                font-size: 2rem !important;
            }
            
            .shop-now-btn {
                padding: 12px 32px;
                font-size: 16px;
            }
        }

        /* Hide language selector on very small screens */
        @media (max-width: 350px) {
            .hide-on-very-small {
                display: none !important;
            }
        }

        /* Mega menu responsive adjustments */
        @media (max-width: 1024px) {
            .mega-menu-container {
                width: 100% !important;
                left: 0 !important;
                transform: translateX(0) !important;
                max-width: 100% !important;
            }
        }

        /* Improve touch targets for mobile */
        @media (max-width: 768px) {
            .touch-target {
                min-height: 44px;
                min-width: 44px;
            }
        }

        .category-slider {
            position: relative;
            overflow: hidden;
        }
        
        .category-track {
            display: flex;
            gap: 16px;
            transition: transform 0.5s ease;
            padding: 0 60px;
        }
        
        .category-card {
            flex: 0 0 calc(25% - 12px);
            min-width: 280px;
            aspect-ratio: 4/5;
            position: relative;
            border-radius: 0;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .category-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.7) 100%);
            z-index: 1;
        }
        
        .category-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .category-card:hover img {
            transform: scale(1.08);
        }
        
        .category-title {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 30px 24px;
            color: white;
            font-size: 28px;
            font-weight: 700;
            z-index: 2;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .slider-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: white;
            color: #333;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .slider-nav:hover {
            background: #022658;
            color: white;
            transform: translateY(-50%) scale(1.1);
        }
        
        .slider-nav.left {
            left: 10px;
        }
        
        .slider-nav.right {
            right: 10px;
        }
        
        .slider-nav.disabled {
            opacity: 0.3;
            cursor: not-allowed;
            pointer-events: none;
        }
        
        @media (max-width: 1280px) {
            .category-card {
                flex: 0 0 calc(33.333% - 11px);
            }
        }
        
        @media (max-width: 1024px) {
            .category-card {
                flex: 0 0 calc(50% - 8px);
                min-width: 240px;
            }
            
            .category-title {
                font-size: 24px;
                padding: 24px 20px;
            }
        }
        
        @media (max-width: 640px) {
            .category-card {
                flex: 0 0 calc(100% - 0px);
                min-width: 100%;
            }
            
            .category-track {
                padding: 0 50px;
            }
            
            .slider-nav {
                width: 40px;
                height: 40px;
            }
            
            .category-title {
                font-size: 22px;
                padding: 20px 16px;
            }
        }
        .feature-icon {
            width: 60px;
            height: 60px;
            background: #fff5f2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            transition: all 0.3s ease;
        }
        
        .feature-box:hover .feature-icon {
            background: #022658;
            transform: scale(1.1);
        }
        
        .feature-box:hover .feature-icon svg {
            stroke: white;
        }
        
        .about-image {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
            position: relative;
        }
        
        .about-image::before {
            content: '';
            position: absolute;
            top: -20px;
            right: -20px;
            width: 200px;
            height: 200px;
            background: #022658;
            border-radius: 50%;
            z-index: -1;
            opacity: 0.1;
        }
        
        .about-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .about-image:hover img {
            transform: scale(1.05);
        }
       
        .campervan-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            position: relative;
            overflow: hidden;
        }
        
        .campervan-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(204, 69, 20, 0.05) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .campervan-content {
            position: relative;
            z-index: 1;
        }
        
        .conversion-image {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            position: relative;
        }
        
        .conversion-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }
        
        .conversion-image:hover img {
            transform: scale(1.05);
        }
        
        .feature-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: white;
            padding: 12px 20px;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 12px;
            transition: all 0.3s ease;
        }
        
        .feature-badge:hover {
            transform: translateX(8px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        }
        
        .feature-badge svg {
            flex-shrink: 0;
        }
        
        .btn-primary {
            background: #022658;
            color: white;
            padding: 16px 36px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            border: 2px solid #022658;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-primary:hover {
            background: #b33d12;
            border-color: #b33d12;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(204, 69, 20, 0.3);
        }
        
        .btn-secondary {
            background: transparent;
            color: #022658;
            padding: 16px 36px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            border: 2px solid #CC4514;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-secondary:hover {
            background: #022658
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(204, 69, 20, 0.3);
        }
        
        @media (max-width: 768px) {
            .btn-primary, .btn-secondary {
                padding: 14px 28px;
                font-size: 14px;
                width: 100%;
                text-align: center;
            }
            
            .feature-badge {
                font-size: 14px;
                padding: 10px 16px;
            }
        }

        .products-carousel {
            overflow: hidden;
            position: relative;
            padding: 20px 0;
        }
        
        .products-track {
            display: flex;
            gap: 24px;
            animation: scroll 40s linear infinite;
        }
        
        .products-track:hover {
            animation-play-state: paused;
        }
        
        @keyframes scroll {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-50%);
            }
        }
        
        .product-card {
            flex: 0 0 280px;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }
        
        .product-image {
            position: relative;
            width: 100%;
            height: 280px;
            overflow: hidden;
            background: #f8f9fa;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .product-card:hover .product-image img {
            transform: scale(1.1);
        }
        
        .new-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: #022658;
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 2;
        }

        .popular-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: #022658;
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 2;
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-name {
            font-size: 16px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 8px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-price {
            font-size: 24px;
            font-weight: 700;
            color: #022658;
            margin-bottom: 8px;
        }
        
        .product-rating {
            display: flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 16px;
        }
        
        .star {
            color: #fbbf24;
        }
        
        .star.empty {
            color: #d1d5db;
        }
        
        .rating-count {
            font-size: 14px;
            color: #6b7280;
            margin-left: 4px;
        }
        
        .add-to-cart {
            width: 100%;
            background: #022658;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            text-align: center;
            text-decoration: none;
            display: block;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .add-to-cart:hover {
            background: #b33d12;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(204, 69, 20, 0.3);
        }
        
        @media (max-width: 768px) {
            .product-card {
                flex: 0 0 240px;
            }
            
            .product-image {
                height: 240px;
            }
        }

        .brands-section {
            background: #f8f9fa;
            overflow: hidden;
            position: relative;
        }
        
        .brands-track {
            display: flex;
            gap: 80px;
            animation: scroll-brands 30s linear infinite;
            width: fit-content;
        }
        
        .brands-track:hover {
            animation-play-state: paused;
        }
        
        @keyframes scroll-brands {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-50%);
            }
        }
        
        .brand-logo {
            flex-shrink: 0;
            width: 180px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .brand-logo:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        
        .brand-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            /* filter: grayscale(100%); */
            opacity: 0.7;
            transition: all 0.3s ease;
        }
        
        .brand-logo:hover img {
            filter: grayscale(0%);
            opacity: 1;
        }
        
        /* Brand name fallback styling */
        .brand-name {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .brands-track {
                gap: 40px;
            }
            
            .brand-logo {
                width: 140px;
                height: 80px;
                padding: 15px;
            }
            
            .brand-name {
                font-size: 18px;
            }
        }

        /* Custom animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }
        
        /* Custom checkbox styling */
        .custom-checkbox {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }
        
        .checkmark {
            position: relative;
            height: 24px;
            width: 24px;
            background-color: white;
            border: 2px solid #f97316;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        
        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
            left: 7px;
            top: 3px;
            width: 6px;
            height: 12px;
            border: solid #ea580c;
            border-width: 0 3px 3px 0;
            transform: rotate(45deg);
        }
        
        .custom-checkbox:checked ~ .checkmark:after {
            display: block;
        }
        
        .custom-checkbox:checked ~ .checkmark {
            background-color: #ffedd5;
            border-color: #022658;
        }
        
        /* Focus styles */
        input:focus, button:focus {
            outline: 2px solid #f97316;
            outline-offset: 2px;
        }

        .footer-link {
            position: relative;
            transition: all 0.3s ease;
        }
        
        .footer-link:hover {
            color: #f97316;
            transform: translateX(5px);
        }
        
        .footer-link:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: #f97316;
            transition: width 0.3s ease;
        }
        
        .footer-link:hover:after {
            width: 100%;
        }
        
        /* Social icon hover effects */
        .social-icon {
            transition: all 0.3s ease;
        }
        
        .social-icon:hover {
            transform: translateY(-3px);
        }
        
        /* Payment icon styling */
        .payment-icon {
            filter: grayscale(100%);
            opacity: 0.7;
            transition: all 0.3s ease;
        }
        
        .payment-icon:hover {
            filter: grayscale(0%);
            opacity: 1;
        }
        
        /* Opening hours highlight */
        .opening-hours {
            background: linear-gradient(90deg, rgba(249, 115, 22, 0.1) 0%, rgba(249, 115, 22, 0.05) 100%);
        }
    </style>

<style>
        .filter-section {
            transition: all 0.3s ease;
        }
        .product-card:hover .product-image {
            transform: scale(1.03);
            transition: transform 0.3s ease;
        }
        .product-card:hover .quick-view {
            opacity: 1;
        }
        .stock-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
        }
        .stock-in {
            background-color: #10b981;
        }
        .stock-low {
            background-color: #f59e0b;
        }
        .stock-out {
            background-color: #ef4444;
        }
        .price-slider {
            -webkit-appearance: none;
            width: 100%;
            height: 4px;
            border-radius: 2px;
            background: #e5e7eb;
            outline: none;
        }
        .price-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #022658;
            cursor: pointer;
        }
        .price-slider::-moz-range-thumb {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #022658;
            cursor: pointer;
            border: none;
        }
        .grid-icon.active, .list-icon.active {
            color: #022658;
        }
         /* Custom styles for range slider */
         input[type="range"] {
            -webkit-appearance: none;
            appearance: none;
            width: 100%;
            height: 6px;
            border-radius: 3px;
            background: #e5e7eb;
            outline: none;
        }
        
        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #2563eb;
            cursor: pointer;
        }
        
        input[type="range"]::-moz-range-thumb {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #2563eb;
            cursor: pointer;
            border: none;
        }
        
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .loading-spinner {
            border: 3px solid #f3f4f6;
            border-top: 3px solid #022658;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        
    </style>

<style>
    mark {
        background-color: #fef08a;
        color: inherit;
        padding: 0;
    }
    
    #searchSuggestions {
        animation: fadeIn 0.2s ease-in-out;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    #searchSuggestions::-webkit-scrollbar {
        width: 6px;
    }
    
    #searchSuggestions::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    #searchSuggestions::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }
    
    #searchSuggestions::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>

<style>
        /* Custom styles for range slider */
        input[type="range"] {
            -webkit-appearance: none;
            appearance: none;
            width: 100%;
            height: 6px;
            border-radius: 3px;
            background: #e5e7eb;
            outline: none;
        }
        
        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #2563eb;
            cursor: pointer;
        }
        
        input[type="range"]::-moz-range-thumb {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #2563eb;
            cursor: pointer;
            border: none;
        }
        
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        /* Wishlist and compare button animations */
        .wishlist-btn, .compare-btn {
            transition: all 0.2s ease;
        }
        
        .wishlist-btn:hover, .compare-btn:hover {
            transform: scale(1.1);
        }
        
        .wishlist-btn.active {
            color: #ef4444;
        }
        
        .compare-btn.active {
            color: #3b82f6;
        }
        
        /* Compare floating badge */
        .compare-badge {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                transform: translateY(100px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>

<style>
        .form-input:focus {
            border-color: #022658;
            box-shadow: 0 0 0 3px rgba(2, 38, 88, 0.1);
        }
        .btn-primary {
            background-color: #022658;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #011a3d;
        }
        .error-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>