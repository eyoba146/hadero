<?php

require_once 'db.php';

try {
    $stmt = $pdo->query("SELECT * FROM menu_items ORDER BY id ASC");
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error gathering menu items: " . $e->getMessage());
}

$grouped_items = [];
foreach ($items as $item) {
    $cat = !empty($item['category']) ? $item['category'] : 'Coffee';
    $grouped_items[$cat][] = $item;
}

$category_order = ['Coffee', 'Bakery', 'Light Bites'];
uksort($grouped_items, function($a, $b) use ($category_order) {
    $idxA = array_search($a, $category_order);
    $idxB = array_search($b, $category_order);
    if ($idxA !== false && $idxB !== false) return $idxA - $idxB;
    if ($idxA !== false) return -1;
    if ($idxB !== false) return 1;
    return strcmp($a, $b);
});

$category_images = [
    'Coffee' => 'https://images.unsplash.com/photo-1497935586351-b67a49e012bf?auto=format&fit=crop&q=80&w=800',
    'Bakery' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&q=80&w=800',
    'Light Bites' => 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?auto=format&fit=crop&q=80&w=800',
    
    'Hot drinks' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&q=80&w=800', 
    
    'Favorite' => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&q=80&w=800',
    
    'Tea selection' => 'https://images.unsplash.com/photo-1511920170033-f8396924c348?auto=format&fit=crop&q=80&w=800',
    
    'Iced' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&q=80&w=800', 
    
    'Iced favorite' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&q=80&w=800',
    'Frappe' => 'https://images.unsplash.com/photo-1594631252845-29fc4cc8cde9?auto=format&fit=crop&q=80&w=800',
    'Mojito' => 'https://images.unsplash.com/photo-1551024601-bec78aea704b?auto=format&fit=crop&q=80&w=800',
    'Juice' => 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?auto=format&fit=crop&q=80&w=800',
    'Soft drinks' => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?auto=format&fit=crop&q=80&w=800',
    
    'Take away hot drinks' => 'https://images.unsplash.com/photo-1511920170033-f8396924c348?auto=format&fit=crop&q=80&w=800',
    
    'Take away iced' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&q=80&w=800',
    
    'Take away mojito' => 'https://images.unsplash.com/photo-1621506289937-a8e4df240d0b?auto=format&fit=crop&q=80&w=800',
    'Take away frappe' => 'https://images.unsplash.com/photo-1572490122747-3968b75cc699?auto=format&fit=crop&q=80&w=800',
    'Package coffee' => 'https://images.unsplash.com/photo-1447933601403-0c6688de566e?auto=format&fit=crop&q=80&w=800'
];
$default_category_image = 'https://images.unsplash.com/photo-1447933601403-0c6688de566e?auto=format&fit=crop&q=80&w=800';
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <title>Our Menu - Hadero Coffee</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    
    <link href="https://fonts.googleapis.com/css2?family=Marcellus&family=Montserrat:wght@300;400;500;600;700&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/index-style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'hadero-gold': '#9B9B45',
                        'hadero-cream': '#FDFBF7',
                        'hadero-dark': '#1F1F1F',
                    },
                    fontFamily: {
                        marcellus: ["'Marcellus'", 'serif'],
                        montserrat: ["'Montserrat'", 'sans-serif'],
                        serif: ["'Cormorant Garamond'", 'serif'],
                    },
                    screens: {
                        'xs': '400px'
                    }
                }
            }
        }
    </script>

</head>
<body class="bg-[#FDFBF7] font-montserrat antialiased text-gray-800 selection:bg-hadero-gold selection:text-black">

    <header class="absolute top-0 left-0 w-full z-50 bg-transparent border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
            
            <div class="flex-shrink-0">
                <a href="https://hadero.et/" target="_blank" rel="noopener noreferrer" class="flex items-center gap-1.5 bg-zinc-950/40 backdrop-blur-md border border-white/10 px-2.5 py-2 sm:px-4 sm:py-2.5 text-hadero-gold text-[9px] sm:text-xs uppercase tracking-[0.15em] sm:tracking-[0.2em] font-montserrat hover:border-hadero-gold hover:bg-zinc-900 transition-all duration-200">
                    <span>Hadero Home</span>
                    <i data-lucide="external-link" class="w-3 h-3 text-zinc-400"></i>
                </a>
            </div>

            <a href="index.php" class="flex items-center gap-2 sm:gap-3 group text-right flex-shrink-0">
                <div class="flex flex-col justify-center leading-none items-end">
                    <span class="font-marcellus text-sm sm:text-xl tracking-[0.1em] sm:tracking-[0.15em] font-bold text-white uppercase">
                        HADERO
                    </span>
                    <span class="text-[8px] sm:text-[10px] font-montserrat tracking-[0.2em] sm:tracking-[0.3em] text-hadero-gold block mt-0.5">
                        COFFEE
                    </span>
                </div>
                <img src="assets/logo.png" alt="Hadero Logo" class="w-8 h-8 sm:w-10 sm:h-10 object-contain flex-shrink-0" />
            </a>

        </div>
    </header>

    <section class="menu-hero-shell" aria-labelledby="menu-page-title">
        <div class="menu-hero-shell__bg">
            <div class="hero-slide is-active" data-slide="0">
                <div class="hero-slide__img" data-kb="0" style="background-image: linear-gradient(to right, rgba(18,18,18,0.85) 0%, rgba(18,18,18,0.35) 100%), url('./assets/slides/slide-1.jpeg');"></div>
            </div>
            <div class="hero-slide" data-slide="1">
                <div class="hero-slide__img" data-kb="1" style="background-image: linear-gradient(to right, rgba(18,18,18,0.85) 0%, rgba(18,18,18,0.35) 100%), url('./assets/slides/slide-2.png');"></div>
            </div>
            <div class="hero-slide" data-slide="2">
                <div class="hero-slide__img" data-kb="2" style="background-image: linear-gradient(to right, rgba(18,18,18,0.85) 0%, rgba(18,18,18,0.35) 100%), url('./assets/slides/slide-3.png');"></div>
            </div>
            <div class="hero-slide" data-slide="3">
                <div class="hero-slide__img" data-kb="3" style="background-image: linear-gradient(to right, rgba(18,18,18,0.85) 0%, rgba(18,18,18,0.35) 100%), url('https://hadero.et/wp-content/uploads/2025/03/Hadero-8504-scaled-e1703130901955.jpg');"></div>
            </div>
            <div class="hero-slide" data-slide="4">
                <div class="hero-slide__img" data-kb="4" style="background-image: linear-gradient(to right, rgba(18,18,18,0.85) 0%, rgba(18,18,18,0.35) 100%), url('./assets/slides/slide-5.png');"></div>
            </div>
            <div class="hero-slide" data-slide="5">
                <div class="hero-slide__img" data-kb="5" style="background-image: linear-gradient(to right, rgba(18,18,18,0.85) 0%, rgba(18,18,18,0.35) 100%), url('./assets/slides/slide-6.png');"></div>
            </div>
        </div>

        <div class="slideshow-progress">
            <div class="slideshow-progress__fill" id="slideshowProgressFill"></div>
        </div>

        <div class="slideshow-dots" id="slideshowDots" role="tablist" aria-label="Slideshow navigation">
            <button class="slideshow-dot is-active" data-dot="0" aria-label="Slide 1" role="tab"></button>
            <button class="slideshow-dot" data-dot="1" aria-label="Slide 2" role="tab"></button>
            <button class="slideshow-dot" data-dot="2" aria-label="Slide 3" role="tab"></button>
            <button class="slideshow-dot" data-dot="3" aria-label="Slide 4" role="tab"></button>
            <button class="slideshow-dot" data-dot="4" aria-label="Slide 5" role="tab"></button>
            <button class="slideshow-dot" data-dot="5" aria-label="Slide 6" role="tab"></button>
        </div>

        <!-- Arrow controls -->
        <button class="slideshow-arrow slideshow-arrow--prev" id="slidePrev" aria-label="Previous slide">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
        </button>
        <button class="slideshow-arrow slideshow-arrow--next" id="slideNext" aria-label="Next slide">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </button>

        <div class="max-w-7xl mx-auto relative px-4 sm:px-6 w-full z-10">
            <div class="max-w-3xl text-left flex flex-col items-start space-y-6">
                
                <div class="space-y-2">
                    <h2 style="font-size:30px; style="text-align:left;" class="font-marcellus text-3xl md:text-5xl text-white uppercase tracking-[0.15em] leading-snug drop-shadow-lg">
                        Welcome To<br />
                        <span style="font-size:20px;" class="text-hadero-gold text-2xl md:text-4xl mt-1 inline-block">
                            Hadero Coffee
                        </span>
                    </h2>
                    
                    <div class="w-24 h-[1px] bg-white/20 my-4"></div>

                    <h1 style="font-size:12px;" class="font-marcellus text-2xl md:text-4xl leading-tight text-white uppercase tracking-wider" id="menu-page-title">
                        <span style="font-size:18px !important;" class="text-hadero-gold italic font-serif normal-case mr-1">
                            Ethiopian
                        </span>
                        Gourmet Coffee
                    </h1>
                </div>

                <div class="pt-4">
                   <a href="#menu-section"
   style="border:no<a href="#menu-section"
   class="menu-hero-btn inline-flex items-center gap-3 px-8 py-4 rounded-full backdrop-blur-sm font-montserrat uppercase tracking-[0.2em] text-xs font-semibold">
    Explore Our Menu
    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
    </svg>
</a> 
                </div>

            </div>
        </div>
    </section>

    <div class="menu-page__body max-w-7xl mx-auto px-4 sm:px-6 md:px-8" id="menu-section">
        
        <?php if (!empty($grouped_items)): ?>
            <div class="max-w-6xl mx-auto mb-14 pb-4 border-b border-zinc-200/80 flex flex-col md:flex-row gap-6 justify-between items-stretch md:items-end">
                
                <div class="relative max-w-md w-full">
                    <i data-lucide="search" class="absolute left-0 bottom-2.5 text-zinc-400 w-4 h-4"></i>
                    <input type="text" id="menuSearchInput" placeholder="SEARCH SELECTION..." class="w-full pl-7 pb-2 bg-transparent text-xs font-marcellus tracking-[0.2em] text-zinc-900 placeholder-zinc-400 focus:outline-none border-b border-transparent focus:border-hadero-gold transition-all uppercase" />
                </div>
                
                <div class="relative flex-grow md:max-w-xl lg:max-w-2xl overflow-hidden">
                    <div class="absolute left-0 top-0 bottom-0 w-8 bg-gradient-to-r from-[#FDFBF7] to-transparent pointer-events-none z-10"></div>
                    
                    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1 px-6 flex-nowrap">
                        <button class="filter-trigger active text-xs font-marcellus uppercase tracking-[0.2em] px-3 pb-2 pt-1 transition-all border-b-2 border-hadero-gold text-zinc-900 whitespace-nowrap shrink-0" data-target-cat="all">
                            All Items
                        </button>
                        <?php foreach (array_keys($grouped_items) as $categoryName): ?>
                            <button class="filter-trigger text-xs font-marcellus uppercase tracking-[0.2em] px-3 pb-2 pt-1 transition-all border-b-2 border-transparent text-zinc-400 hover:text-zinc-900 whitespace-nowrap shrink-0" data-target-cat="<?= htmlspecialchars($categoryName) ?>">
                                <?= htmlspecialchars($categoryName) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="absolute right-0 top-0 bottom-0 w-8 bg-gradient-to-l from-[#FDFBF7] to-transparent pointer-events-none z-10"></div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($grouped_items)): ?>
            <div class="text-center py-24 bg-white border border-[#f1efe6] max-w-xl mx-auto my-8 p-8 rounded-xl shadow-sm">
                <i data-lucide="alert-circle" class="w-12 h-12 text-zinc-300 mx-auto mb-4"></i>
                <p class="font-marcellus text-[#a8a698] uppercase tracking-widest text-xs">No Items Cataloged</p>
                <p class="font-montserrat text-sm text-gray-500 mt-2">Menu cataloging parameters are presently empty.</p>
            </div>
        <?php else: ?>
            
            <div id="categoryCardsWrapper" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 max-w-6xl mx-auto mb-16">
                <?php foreach (array_keys($grouped_items) as $catName): 
                    $bgImg = isset($category_images[$catName]) ? $category_images[$catName] : $default_category_image;
                ?>
                <div class="category-card-node relative rounded-xl overflow-hidden cursor-pointer group h-64 shadow-sm" data-trigger-cat="<?= htmlspecialchars($catName) ?>">
                    <img src="<?= $bgImg ?>" alt="<?= htmlspecialchars($catName) ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
                    <div class="absolute inset-0 bg-black/40 group-hover:bg-black/50 transition-colors duration-300"></div>
                    <div class="absolute inset-0 flex items-end justify-start p-6">
                        <h3 class="text-hadero-gold font-marcellus text-xl sm:text-2xl tracking-[0.15em] uppercase text-left transition-all duration-300 group-hover:text-white">
                            <?= htmlspecialchars($catName) ?>
                        </h3>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div id="menuCatalogWrapper" class="space-y-16 sm:space-y-20">
                <?php foreach ($grouped_items as $cat => $grp_items): ?>
                    <section class="menu-catalog-section max-w-6xl mx-auto transition-all duration-300" data-cat-group="<?= htmlspecialchars($cat) ?>">
                        <h2 class="menu-section__title text-zinc-900"><?= htmlspecialchars($cat) ?></h2>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 mt-6">
                            <?php foreach ($grp_items as $item): ?>
                                <article class="menu-card catalog-item-node" data-item-title="<?= htmlspecialchars(strtolower($item['name'])) ?>" data-item-details="<?= htmlspecialchars(strtolower($item['description'])) ?>">
                                    <div class="menu-card__media">
                                        <?php if (!empty($item['image_url'])): ?>
                                            <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
                                        <?php endif; ?>
                                        
                                        <div class="menu-card__placeholder" style="<?= !empty($item['image_url']) ? 'display: none;' : '' ?>">
                                            <div class="w-10 h-10 opacity-70">
                                                <svg viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                                                    <rect x="8" y="8" width="80" height="80" rx="18" stroke="currentColor" stroke-width="2.5"/>
                                                    <path d="M30 38h32v18c0 8.837-7.163 16-16 16s-16-7.163-16-16V38Z" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"/>
                                                    <path d="M34 58h24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                                                    <path d="M58 42h10c3 0 5 2.5 5 5.5S71 53 68 53h-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                                                    <path d="M40 24c-3 4-3 8 0 12M50 24c-3 4-3 8 0 12M60 24c-3 4-3 8 0 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                                                </svg>
                                            </div>
                                            <span class="text-[10px] tracking-wider font-montserrat text-zinc-400 mt-2">Hadero Fallback Image</span>
                                        </div>
                                    </div>
                                    
                                    <div class="p-5 sm:p-6 flex flex-col flex-grow">
                                        <h3 class="font-marcellus text-base sm:text-lg font-medium uppercase text-zinc-900 tracking-wider mb-2">
                                            <?= htmlspecialchars($item['name']) ?>
                                        </h3>
                                        <p class="font-montserrat font-light text-xs sm:text-sm text-zinc-500 leading-relaxed mb-6">
                                            <?= htmlspecialchars($item['description']) ?>
                                        </p>
                                        <div class="flex justify-between items-center border-t border-dashed border-zinc-200 pt-4 mt-auto">
                                            <span class="bg-zinc-100 text-zinc-800 px-2.5 py-1 rounded text-[10px] uppercase tracking-wider font-marcellus">
                                                <?= htmlspecialchars($item['category']) ?>
                                            </span>
                                            <span class="font-marcellus text-sm sm:text-base font-semibold text-zinc-900">
                                                <?= htmlspecialchars($item['price']) ?>&nbsp; ETB
                                            </span>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
                
                <div id="zeroResultsMessage" class="hidden text-center py-16 bg-white border border-[#f1efe6] max-w-xl mx-auto rounded-xl shadow-sm">
                    <i data-lucide="search-x" class="w-10 h-10 text-zinc-300 mx-auto mb-3"></i>
                    <p class="font-marcellus text-[#a8a698] uppercase tracking-widest text-xs">No Matching Items</p>
                    <p class="font-montserrat text-xs text-gray-400 mt-1">Try adjusting your dynamic search criteria or category filter.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer class="bg-[#121212] border-t border-zinc-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 py-16 grid grid-cols-1 md:grid-cols-2 gap-12">
            <div>
                <h4 class="font-marcellus text-xl tracking-wider mb-6 text-white uppercase">
                    <span class="text-hadero-gold">FEEL THE</span> ORIGIN!
                </h4>
                <p class="font-montserrat font-light text-xs sm:text-sm text-gray-400 leading-relaxed mb-6">
                    Ethiopia is the birthplace of coffee, a major player in green coffee production, and the origin of Arabica coffee. Coffee is celebrated for its contribution to the cultural and social life of Ethiopia.
                </p>
                <p class="font-montserrat font-light text-xs sm:text-sm text-gray-400 leading-relaxed">
                    The name Hadero originates from a tiny town in Southern Ethiopia. Our brand is heavily inspired by the absolute craftsmanship of local crop growers of the region.
                </p>
            </div>

            <div class="flex flex-col justify-center items-center md:items-end text-center md:text-right">
                <div class="border border-zinc-800 p-6 sm:p-8 max-w-sm rounded-lg bg-zinc-900">
                    <span class="font-serif italic text-2xl sm:text-3xl text-hadero-gold block mb-2">Since 2023</span>
                    <p class="font-montserrat text-[10px] sm:text-xs text-gray-500 uppercase tracking-widest leading-loose">
                        Directly sourcing premium Arabica beans from the Southern highlands of Ethiopia, roasted in our facilities, served fresh to you.
                    </p>
                </div>
            </div>
        </div>

        <div class="border-t border-zinc-900 bg-zinc-950 py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 flex flex-col md:flex-row justify-between items-center gap-4 text-center md:text-left">
                <span class="text-[10px] uppercase tracking-wider font-marcellus text-gray-500">Global sourcing</span>
                <div class="flex flex-wrap justify-center gap-4 text-[10px] font-montserrat text-gray-500">
                    <a href="#privacy" class="hover:text-hadero-gold transition uppercase tracking-wider">Privacy</a>
                    <a href="#terms" class="hover:text-hadero-gold transition uppercase tracking-wider">Terms</a>
                    <a href="#sustainability" class="hover:text-hadero-gold transition uppercase tracking-wider">Cookies</a>
                </div>
            </div>
        </div>

        <div class="bg-black py-4 text-center text-[10px] font-montserrat tracking-widest text-zinc-600 border-t border-zinc-900">
            &copy; 2023 HADERO COFFEE. ALL RIGHTS RESERVED
        </div>
    </footer>

    <script src="js/index-script.js"></script>
</body>
</html>