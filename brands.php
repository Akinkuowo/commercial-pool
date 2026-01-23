<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

ini_set('session.cookie_path', '/');
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_lifetime', 0);
ini_set('session.gc_maxlifetime', 3600);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brands - Jacksons Leisure</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="assets/css/styles.css" rel="stylesheet" />

    <?php include('include/style.php') ?>
    
    <style>
        .brand-section {
            transition: all 0.3s ease;
        }
        
        .brand-letter {
            scroll-margin-top: 100px;
        }
        
        .brand-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 12px;
        }
        
        .brand-item {
            padding: 12px 16px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .brand-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-color: #3b82f6;
        }
        
        .alphabet-nav {
            top: 80px;
            z-index: 40;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
        }
        
        .alphabet-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        
        .alphabet-link:hover {
            background-color: #3b82f6;
            color: white;
        }
        
        .alphabet-link.active {
            background-color: #3b82f6;
            color: white;
        }
        
        .no-brands {
            padding: 32px;
            text-align: center;
            color: #6b7280;
            font-style: italic;
        }
        
        @media (max-width: 640px) {
            .brand-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
            
            .alphabet-link {
                width: 32px;
                height: 32px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    
    <?php include('include/header.php'); ?>

    <!-- Breadcrumb -->
    <div class="bg-white border-b border-gray-200 py-3">
        <div class="container mx-auto px-4 max-w-7xl">
            <nav class="flex text-sm">
                <a href="/" class="text-gray-500 hover:text-gray-700">Home</a>
                <span class="mx-2 text-gray-400">/</span>
                <span class="text-gray-900">Brands</span>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Our Brands</h1>
            <p class="text-gray-600">Browse our extensive collection of premium brands in alphabetical order</p>
        </div>

        <!-- Alphabet Navigation -->
        <div class="alphabet-nav py-4 border-b border-gray-200 mb-8">
            <div class="flex flex-wrap gap-1 justify-center">
                <?php
                $alphabet = range('A', 'Z');
                foreach ($alphabet as $letter) {
                    echo '<a href="#letter-' . strtolower($letter) . '" class="alphabet-link text-gray-700 hover:text-white">' . $letter . '</a>';
                }
                ?>
            </div>
        </div>

        <!-- Brands List -->
        <div class="space-y-12">
            <?php
            // Define all brands organized by first letter
            $brandsByLetter = [
                'a' => ['Adventurer', 'Ainsworth', 'Alde', 'Astral', 'ATOM', 'Avtex'],
                'b' => ['Berg', 'Bestway', 'Blu Line', 'Blue Diamond', 'Bonus', 'Bosta', 'Bowmans', 'Brunner', 'Bullfinch'],
                'c' => ['Cadac', 'Calor', 'Campko', 'CAN', 'Cara', 'CBE', 'Certikin', 'CF Parker', 'Coleman', 'Comet', 'Continental', 'Cramer', 'Crewsaver', 'Crusader', 'CTA'],
                'd' => ['DellCool', 'Dimatec', 'Dodo', 'Dolphin', 'Dometic', 'Dorema', 'Doughboy', 'DREHMEISTER', 'Durite'],
                'e' => ['Easy Camp', 'Eberspächer', 'Elecro', 'EvoMatic'],
                'f' => ['FAWO', 'Femo', 'Fiamma', 'Flojet', 'Fogstar', 'Foxygen'],
                'g' => ['GOK'],
                'h' => ['Hayward', 'Heatek', 'Horrex'],
                'i' => ['Igloo', 'Intex', 'Isabella'],
                'j' => ['JacTone', 'JLS', 'John Guest', 'JP Australia'],
                'k' => ['Kampa', 'Kengo', 'Kettler'],
                'l' => ['Lagun', 'Lavanda', 'Leisurewize', 'Liberty', 'Lumo'],
                'm' => ['Maxview', 'Maxxair', 'Maypole', 'Mestic', 'Morland', 'MPK'],
                'n' => ['Navy Load', 'Ninja', 'NRF'],
                'o' => ['Outdoor Play', 'Outdoor Revolution', 'Outwell'],
                'p' => ['Pentair', 'Plastica', 'Propex', 'Puky', 'PV Logic', 'Pyranha', 'Pyranha Venture'],
                'q' => ['Quest'],
                'r' => ['Reich', 'Reimo', 'Relax', 'Remis', 'ReVace', 'Ridge Monkey', 'Robens', 'Royal'],
                's' => ['S R Smith', 'Sandbanks SUP Style', 'Sargent', 'Sat-Fi', 'Scanstrut', 'Shurflo', 'SiC', 'Smev', 'Smoby', 'Solar Technologies', 'Sportscraft', 'Sta-Rite', 'Sterling', 'Streetwize', 'Sunncamp'],
                't' => ['Tambourline', 'Thetford', 'Thule', 'Travellite', 'Trígano', 'Trím-Fix', 'Truma'],
                'u' => [],
                'v' => ['Vamosse', 'Vango', 'Venture', 'Victron Energy', 'Vision Plus', 'Vitrifrigo'],
                'w' => ['Waterco', 'Webasto', 'Westfield Outdoors', 'Whale', 'Wild Country', 'Winbond', 'Wolfrace'],
                'x' => [],
                'y' => ['YAK', 'YETI', 'Yílkar'],
                'z' => ['Zodiac']
            ];

            // Sort each letter's brands alphabetically
            foreach ($brandsByLetter as &$brands) {
                sort($brands);
            }
            unset($brands);

            // Display brands by letter
            foreach ($brandsByLetter as $letter => $brands) {
                if (!empty($brands)) {
                    echo '<div id="letter-' . $letter . '" class="brand-section">';
                    echo '<h2 class="text-2xl font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200 brand-letter">' . strtoupper($letter) . '</h2>';
                    echo '<div class="brand-grid">';
                    
                    foreach ($brands as $brand) {
                        echo '<a href="product.php?brand=' . urlencode($brand) . '" class="brand-item block hover:no-underline">';
                        echo '<div class="font-medium text-gray-900 hover:text-blue-600 transition">' . htmlspecialchars($brand) . '</div>';
                        echo '</a>';
                    }
                    
                    echo '</div>';
                    echo '</div>';
                }
            }
            ?>
        </div>

        <!-- No Brands Message for empty letters -->
        <div class="mt-12 bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Looking for a specific brand?</h3>
            <p class="text-gray-600 mb-4">If you don't see a brand listed here, please contact our customer service team.</p>
            <a href="contact.php" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium">
                Contact Us
                <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>

    <?php include('include/footer.php'); ?>
    <?php include('include/script.php') ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Highlight active letter in alphabet navigation
            function highlightActiveLetter() {
                const letters = document.querySelectorAll('.brand-letter');
                const navLinks = document.querySelectorAll('.alphabet-link');
                
                let activeLetter = null;
                const scrollPosition = window.scrollY + 120; // Offset for sticky header
                
                // Find which letter section is currently in view
                letters.forEach(letter => {
                    const rect = letter.getBoundingClientRect();
                    const letterTop = rect.top + window.scrollY;
                    const letterBottom = letterTop + rect.height;
                    
                    if (scrollPosition >= letterTop && scrollPosition < letterBottom) {
                        activeLetter = letter.id.replace('letter-', '');
                    }
                });
                
                // Update navigation links
                navLinks.forEach(link => {
                    const linkLetter = link.getAttribute('href').replace('#letter-', '');
                    if (linkLetter === activeLetter) {
                        link.classList.add('active');
                    } else {
                        link.classList.remove('active');
                    }
                });
            }
            
            // Smooth scroll for alphabet navigation
            document.querySelectorAll('.alphabet-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);
                    
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 100,
                            behavior: 'smooth'
                        });
                    }
                });
            });
            
            // Update active letter on scroll
            window.addEventListener('scroll', highlightActiveLetter);
            
            // Initial highlight
            highlightActiveLetter();
            
            // Search functionality (optional enhancement)
            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.placeholder = 'Search brands...';
            searchInput.className = 'w-full max-w-md px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 mb-6';
            
            const pageHeader = document.querySelector('.mb-8');
            pageHeader.appendChild(searchInput);
            
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                const brandItems = document.querySelectorAll('.brand-item');
                const brandSections = document.querySelectorAll('.brand-section');
                
                let hasVisibleBrands = false;
                
                // Hide/show brand items based on search
                brandItems.forEach(item => {
                    const brandName = item.textContent.toLowerCase();
                    if (brandName.includes(searchTerm)) {
                        item.style.display = 'block';
                        hasVisibleBrands = true;
                    } else {
                        item.style.display = 'none';
                    }
                });
                
                // Hide/show entire letter sections
                brandSections.forEach(section => {
                    const brandsInSection = section.querySelectorAll('.brand-item');
                    const hasVisibleItems = Array.from(brandsInSection).some(item => 
                        item.style.display !== 'none'
                    );
                    
                    if (hasVisibleItems || searchTerm === '') {
                        section.style.display = 'block';
                    } else {
                        section.style.display = 'none';
                    }
                });
                
                // Show message if no brands found
                let noResultsMessage = document.querySelector('.no-results-message');
                if (!hasVisibleBrands && searchTerm !== '') {
                    if (!noResultsMessage) {
                        noResultsMessage = document.createElement('div');
                        noResultsMessage.className = 'no-results-message bg-yellow-50 border border-yellow-200 rounded-lg p-6 mt-6';
                        noResultsMessage.innerHTML = `
                            <div class="flex items-center">
                                <i class="fas fa-search text-yellow-600 mr-3"></i>
                                <div>
                                    <h4 class="font-semibold text-yellow-800">No brands found</h4>
                                    <p class="text-yellow-700">No brands match your search for "${searchTerm}"</p>
                                </div>
                            </div>
                        `;
                        document.querySelector('.space-y-12').appendChild(noResultsMessage);
                    }
                } else if (noResultsMessage) {
                    noResultsMessage.remove();
                }
            });
        });
    </script>

</body>
</html>