<?php

require_once 'db.php';
session_start();

// 1. Force Secure Session Gate Check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$error = null;
$success = null;
$active_tab = $_GET['tab'] ?? 'menu';

if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    session_destroy();
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($active_tab === 'menu') {
        
        if ($action === 'delete') {
            $id = intval($_POST['id'] ?? 0);
            try {
                $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
                $stmt->execute([$id]);
                $success = "Item successfully deleted from the MySQL database!";
            } catch (PDOException $e) {
                $error = "Failed to delete item: " . $e->getMessage();
            }
        }
        
        if ($action === 'save') {
            $id = intval($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $price = trim($_POST['price'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $image_url = trim($_POST['imageUrl'] ?? '');

            if (empty($name) || empty($category) || empty($price)) {
                $error = "Please fill in all mandatory fields (*): Name, Category, and Price.";
            } else {
                try {
                    if ($id > 0) {
                        // UPDATE RECORD
                        $stmt = $pdo->prepare("UPDATE menu_items SET name = ?, category = ?, price = ?, description = ?, image_url = ? WHERE id = ?");
                        $stmt->execute([$name, $category, $price, $description, $image_url, $id]);
                        $success = "Product record (ID #$id) successfully updated!";
                    } else {
                        // INSERT NEW RECORD
                        $stmt = $pdo->prepare("INSERT INTO menu_items (name, category, price, description, image_url) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$name, $category, $price, $description, $image_url]);
                        $success = "New menu item successfully cataloged in the database!";
                    }
                } catch (PDOException $e) {
                    $error = "Failed to modify record: " . $e->getMessage();
                }
            }
        }
    }
    
    if ($active_tab === 'categories') {
        
        if ($action === 'add_category') {
            $cat_name = trim($_POST['category_name'] ?? '');
            if (empty($cat_name)) {
                $error = "Category name cannot be set blank.";
            } else {
                try {
                    $check = $pdo->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(?)");
                    $check->execute([$cat_name]);
                    if ($check->fetch()) {
                        $error = "This category already exists.";
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
                        $stmt->execute([$cat_name]);
                        $success = "Category \"$cat_name\" registered successfully!";
                    }
                } catch (PDOException $e) {
                    $error = "Failed to insert category: " . $e->getMessage();
                }
            }
        }
        
        if ($action === 'delete_category') {
            $cat_name = trim($_POST['category_name'] ?? '');
            try {
                $stmt = $pdo->prepare("DELETE FROM categories WHERE name = ?");
                $stmt->execute([$cat_name]);
                $success = "Category \"$cat_name\" retired successfully.";
            } catch (PDOException $e) {
                $error = "Failed to delete category: " . $e->getMessage();
            }
        }

        if ($action === 'edit_category') {
            $old_name = trim($_POST['old_category_name'] ?? '');
            $new_name = trim($_POST['new_category_name'] ?? '');
            if (empty($old_name) || empty($new_name)) {
                $error = "Category names cannot be blank.";
            } else {
                try {
                    $check = $pdo->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(?) AND name != ?");
                    $check->execute([$new_name, $old_name]);
                    if ($check->fetch()) {
                        $error = "A category with that title already exists.";
                    } else {
                        $pdo->beginTransaction();
                        
                        $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE name = ?");
                        $stmt->execute([$new_name, $old_name]);
                        
                        $stmt2 = $pdo->prepare("UPDATE menu_items SET category = ? WHERE category = ?");
                        $stmt2->execute([$new_name, $old_name]);
                        
                        $pdo->commit();
                        $success = "Category \"$old_name\" successfully renamed to \"$new_name\"!";
                    }
                } catch (PDOException $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    $error = "Failed to rename category: " . $e->getMessage();
                }
            }
        }
    }
    
    if ($active_tab === 'settings') {
        
        if ($action === 'change_password') {
            $old_pass = trim($_POST['old_password'] ?? '');
            $new_pass = trim($_POST['new_password'] ?? '');
            $confirm_pass = trim($_POST['confirm_password'] ?? '');
            
            if (empty($old_pass) || empty($new_pass) || empty($confirm_pass)) {
                $error = "All password inputs are mandatory.";
            } else if ($new_pass !== $confirm_pass) {
                $error = "Confirm password verify match has failed.";
            } else {
                try {
                    $stmt = $pdo->prepare("SELECT setting_value FROM admin_settings WHERE setting_key = 'admin_password'");
                    $stmt->execute();
                    $current_password = $stmt->fetchColumn();
                    
                    if ($current_password === false) {
                        $current_password = 'hadero_admin';
                    }
                    
                    if ($old_pass !== $current_password) {
                        $error = "Your current administrator password input is incorrect.";
                    } else {
                        $upd_stmt = $pdo->prepare("UPDATE admin_settings SET setting_value = ? WHERE setting_key = 'admin_password'");
                        $upd_stmt->execute([$new_pass]);
                        $success = "Your Password changed successfully!";
                    }
                } catch (PDOException $e) {
                    $error = "Database setting change failed: " . $e->getMessage();
                }
            }
        }
    }
}

$items = [];
$categories = [];
$unique_images = [];

try {
    $items_stmt = $pdo->query("SELECT * FROM menu_items ORDER BY id DESC");
    $items = $items_stmt->fetchAll();
    
    $cat_stmt = $pdo->query("SELECT name FROM categories ORDER BY id ASC");
    $categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($categories)) {
        $categories = ['Coffee', 'Bakery', 'Light Bites'];
    }

    $img_stmt = $pdo->query("SELECT DISTINCT image_url FROM menu_items WHERE image_url IS NOT NULL AND image_url != ''");
    $unique_images = $img_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $error = "Data sync failed: " . $e->getMessage();
}

$edit_item = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    foreach ($items as $itm) {
        if (intval($itm['id']) === $edit_id) {
            $edit_item = $itm;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Hadero - Admin Panel Hub</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <link href="https://fonts.googleapis.com/css2?family=Didact+Gothic&family=Roboto:wght@300;400;500;700&family=Oswald:wght@400;500;600&family=Playfair+Display:wght@600&display=swap" rel="stylesheet" />
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
                        didact: ["'Didact Gothic'", 'sans-serif'],
                        roboto: ["'Roboto'", 'sans-serif'],
                        oswald: ["'Oswald'", 'sans-serif'],
                    },
                    screens: {
                        'xs': '400px'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-[#FDFBF7] font-sans antialiased text-gray-800">

   <header class="bg-[#121212] border-b border-zinc-800 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between gap-4">

        <div class="flex-shrink-0">
            <a href="https://hadero.et/" target="_blank" rel="noopener noreferrer"
                class="flex items-center gap-1.5 bg-zinc-950 border border-white/10 px-2.5 py-2 sm:px-4 sm:py-2.5 text-hadero-gold text-[9px] sm:text-xs uppercase tracking-[0.15em] sm:tracking-[0.2em] font-montserrat hover:border-hadero-gold hover:bg-zinc-900 transition-all duration-200">
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

    <img
        src="assets/logo.png"
        alt="Hadero Logo"
        class="w-8 h-8 sm:w-10 sm:h-10 object-contain flex-shrink-0"
        onerror="this.style.display='none';"
    />
</a>    

    </div>
</header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 py-6 sm:py-10">
        
        <div class="mb-6 sm:mb-8 border-b border-zinc-200 pb-5 sm:pb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-1.5 text-zinc-400 font-mono text-[9px] sm:text-[10px] tracking-widest uppercase mb-1">
                    <i data-lucide="fingerprint" class="w-3.5 h-3.5 text-hadero-gold"></i>
                    <span>ADMIN CONSOLE ENVIRONMENT</span>
                </div>
                <h1 class="font-oswald text-2xl sm:text-3xl uppercase font-semibold text-zinc-950 flex items-center gap-2">
                    <i data-lucide="activity" class="text-hadero-gold w-6 h-6 sm:w-7 sm:h-7"></i>
                    <span>Console Terminal</span>
                </h1>
                <p class="font-didact text-xs sm:text-sm text-zinc-500 mt-1">
                    Dynamic updates are written straight into MySQL. Clients view change logs instantly.
                </p>
            </div>
            
            <form action="admin.php?tab=<?= $active_tab ?>" method="POST">
                <input type="hidden" name="action" value="logout" />
                <button type="submit" class="inline-flex items-center gap-1.5 bg-black hover:bg-zinc-800 text-white font-oswald text-[11px] uppercase tracking-widest px-4 py-2 rounded shadow transition">
                    <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                    <span>Lock Session</span>
                </button>
            </form>
        </div>

        <div class="mb-6 flex border-b border-[#e1e0d8] overflow-x-auto scrollbar-none 
                    relative 
                    before:content-[''] before:absolute before:left-0 before:top-0 before:bottom-0 before:w-8 before:bg-gradient-to-r before:from-[#FDFBF7] before:to-transparent before:pointer-events-none before:z-10 
                    after:content-[''] after:absolute after:right-0 after:top-0 after:bottom-0 after:w-8 after:bg-gradient-to-l after:from-[#FDFBF7] after:to-transparent after:pointer-events-none after:z-10">
            <a href="admin.php?tab=menu" class="flex items-center gap-2 px-4 py-3 border-b-2 font-oswald text-xs uppercase tracking-wider font-semibold whitespace-nowrap transition <?= $active_tab === 'menu' ? 'border-hadero-gold text-black bg-[#9B9B45]/5' : 'border-transparent text-gray-400 hover:text-black' ?>">
                <i data-lucide="list" class="w-4 h-4"></i>
                <span>Gourmet Product Catalog</span>
            </a>
            <a href="admin.php?tab=categories" class="flex items-center gap-2 px-4 py-3 border-b-2 font-oswald text-xs uppercase tracking-wider font-semibold whitespace-nowrap transition <?= $active_tab === 'categories' ? 'border-hadero-gold text-black bg-[#9B9B45]/5' : 'border-transparent text-gray-400 hover:text-black' ?>">
                <i data-lucide="folder-plus" class="w-4 h-4"></i>
                <span>Dynamic Categories Editor</span>
            </a>
            <a href="admin.php?tab=settings" class="flex items-center gap-2 px-4 py-3 border-b-2 font-oswald text-xs uppercase tracking-wider font-semibold whitespace-nowrap transition <?= $active_tab === 'settings' ? 'border-hadero-gold text-black bg-[#9B9B45]/5' : 'border-transparent text-gray-400 hover:text-black' ?>">
                <i data-lucide="settings" class="w-4 h-4"></i>
                <span>Security Settings & Password</span>
            </a>
        </div>

        <?php if ($success): ?>
            <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 flex items-start gap-2 rounded text-xs animate-pulse">
                <i data-lucide="check" class="w-4 h-4 shrink-0 mt-0.5 text-emerald-500"></i>
                <div>
                    <strong class="font-bold block uppercase text-[10px] text-zinc-900 tracking-wider">Operation Successful</strong>
                    <p class="font-didact mt-0.5"><?= htmlspecialchars($success) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 flex items-start gap-2 rounded text-xs">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 mt-0.5 text-red-500"></i>
                <div>
                    <strong class="font-bold block uppercase text-[10px] text-zinc-900 tracking-wider">Operation Blocked</strong>
                    <p class="font-didact mt-0.5"><?= htmlspecialchars($error) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($active_tab === 'menu'): ?>
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <div class="lg:col-span-5 bg-white border border-[#f1efe6] shadow-md p-5 sm:p-6 rounded-xl space-y-6">
                    <h2 class="font-oswald text-base font-semibold text-black border-b border-gray-100 pb-2 uppercase tracking-widest flex items-center justify-between">
                        <span><?= $edit_item ? 'Edit Record (ID #'.$edit_item['id'].')' : 'Enroll Gourmet item' ?></span>
                        <?php if ($edit_item): ?>
                            <span class="text-[9px] bg-hadero-gold text-black font-semibold px-2 py-0.5 rounded uppercase">Editing</span>
                        <?php endif; ?>
                    </h2>

                    <form action="admin.php?tab=menu" method="POST" id="menu-form" class="space-y-4">
                        <input type="hidden" name="action" value="save" />
                        <input type="hidden" name="id" value="<?= $edit_item ? intval($edit_item['id']) : 0 ?>" />
                        <input type="hidden" name="imageUrl" id="imageUrl" value="<?= htmlspecialchars($edit_item['image_url'] ?? '') ?>" />

                        <div>
                          <label class="block text-[11px] uppercase font-oswald tracking-widest font-semibold text-gray-700 mb-1">
                              Product Name *
                          </label>
                          <input
                            type="text"
                            name="name"
                            value="<?= htmlspecialchars($edit_item['name'] ?? '') ?>"
                            placeholder="e.g. Ethiopia Jimma Batch"
                            class="w-full border border-gray-200 px-3 py-2 rounded focus:outline-none focus:border-hadero-gold font-didact text-sm bg-gray-50 focus:bg-white transition"
                            required
                          />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                          <div>
                            <label class="block text-[11px] uppercase font-oswald tracking-widest font-semibold text-gray-700 mb-1">
                                Category Tag *
                            </label>
                            <select
                              name="category"
                              class="w-full border border-gray-200 px-3 py-2 rounded focus:outline-none focus:border-hadero-gold font-didact text-sm bg-gray-50 focus:bg-white transition"
                            >
                              <?php foreach ($categories as $catName): ?>
                                  <option value="<?= htmlspecialchars($catName) ?>" <?= ($edit_item && $edit_item['category'] === $catName) ? 'selected' : '' ?>><?= htmlspecialchars($catName) ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>

                          <div>
                            <label class="block text-[11px] uppercase font-oswald tracking-widest font-semibold text-gray-700 mb-1">
                                Price (ETB) *
                            </label>
                            <input
                              type="text"
                              name="price"
                              value="<?= htmlspecialchars($edit_item['price'] ?? '') ?>"
                              placeholder="e.g. 484.50"
                              class="w-full border border-gray-200 px-3 py-2 rounded focus:outline-none focus:border-hadero-gold font-didact text-sm bg-gray-50 focus:bg-white transition"
                              required
                            />
                          </div>
                        </div>

                        <div>
                          <label class="block text-[11px] uppercase font-oswald tracking-widest font-semibold text-gray-700 mb-1">
                              Description / Flavor Notes
                          </label>
                          <textarea
                            name="description"
                            placeholder="Describe single batch details, flavor notes, density..."
                            rows="2"
                            class="w-full border border-gray-200 px-3 py-2 rounded focus:outline-none focus:border-hadero-gold font-didact text-sm bg-gray-50 focus:bg-white transition resize-none"
                          ><?= htmlspecialchars($edit_item['description'] ?? '') ?></textarea>
                        </div>

                        <div class="border border-dashed border-gray-200 p-4 rounded-lg bg-gray-50/50 space-y-4">
                            <span class="block text-[11px] uppercase font-oswald tracking-widest font-semibold text-gray-700">
                                Item Media Layout
                            </span>

                            <div class="relative aspect-[3/2] w-full border border-gray-200 bg-white rounded-lg flex items-center justify-center overflow-hidden">
                                <img id="form-preview" src="<?= htmlspecialchars($edit_item['image_url'] ?? '') ?>" class="w-full h-full object-cover <?= empty($edit_item['image_url']) ? 'hidden' : '' ?>" />
                                <div id="form-fallback-icon" class="text-center p-4 <?= !empty($edit_item['image_url']) ? 'hidden' : '' ?>">
                                    <i data-lucide="image" class="w-8 h-8 text-gray-300 mx-auto mb-1"></i>
                                    <p class="font-didact text-[10px] text-gray-400">Fallback vector layout details will cover this.</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" onclick="startCamera()" class="flex items-center justify-center gap-1 px-2.5 py-1.5 bg-black hover:bg-zinc-800 text-white text-[11px] uppercase font-oswald tracking-wider rounded select-none">
                                    <i data-lucide="camera" class="w-3.5 h-3.5"></i> Take photo
                                </button>
                                <button type="button" onclick="triggerFileSelect()" class="flex items-center justify-center gap-1 px-2.5 py-1.5 border border-black hover:bg-zinc-50 bg-white text-black text-[11px] uppercase font-oswald tracking-wider rounded select-none">
                                    <i data-lucide="upload" class="w-3.5 h-3.5"></i> Local file
                                </button>
                            </div>
                            <input type="file" id="local-file-ref" accept="image/*" class="hidden" onchange="convertLocalFileBase64(this)" />

                            <?php if (!empty($unique_images)): ?>
                                <div class="border-t border-gray-200 pt-3">
                                    <span class="block text-[11px] uppercase font-oswald tracking-widest font-semibold text-gray-500 mb-2 flex items-center gap-1">
                                        <i data-lucide="image" class="w-3 h-3 text-[#9b9b45]"></i>
                                        <span>Library selection picker</span>
                                    </span>
                                    <div class="grid grid-cols-4 xs:grid-cols-5 gap-2 max-h-[110px] overflow-y-auto pr-1">
                                        <?php foreach ($unique_images as $img): ?>
                                            <button 
                                                type="button" 
                                                onclick="assignLibraryPath('<?= htmlspecialchars($img) ?>')"
                                                class="aspect-square bg-white border border-gray-200 rounded overflow-hidden hover:border-gray-400 relative flex items-center justify-center text-center select-none cursor-pointer"
                                            >
                                                <?php if (strpos($img, 'images/') === 0): ?>
                                                    <span class="text-[9px] uppercase font-mono text-gray-400">Preset</span>
                                                <?php else: ?>
                                                    <img src="<?= htmlspecialchars($img) ?>" class="w-full h-full object-cover" />
                                                <?php endif; ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="flex gap-3 pt-1">
                            <button type="submit" class="flex-grow py-3 bg-hadero-gold hover:bg-[#b5b552] text-black text-xs font-oswald uppercase tracking-widest font-semibold shadow">
                                <?= $edit_item ? 'Update Database Info' : 'Save dynamic item' ?>
                            </button>
                            <?php if ($edit_item): ?>
                                <a href="admin.php?tab=menu" class="px-3.5 py-3 border border-gray-200 text-gray-500 hover:bg-zinc-100 text-xs font-oswald uppercase tracking-widest text-center rounded">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="lg:col-span-7 bg-white border border-[#f1efe6] shadow-md p-5 sm:p-6 rounded-xl space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h2 class="font-oswald text-base font-semibold text-black uppercase tracking-widest">
                            Database items (<?= count($items) ?>)
                        </h2>
                        <a href="admin.php?tab=menu" class="p-1.5 text-gray-400 hover:text-black rounded hover:bg-gray-100 transition" title="Reload list">
                            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 pb-2 border-b border-gray-100/60 pb-3">
                        <div class="sm:col-span-7 relative">
                            <input
                                type="text"
                                id="admin-search-input"
                                oninput="filterAdminCatalog()"
                                placeholder="Search products by name or flavor notes..."
                                class="w-full pl-8 pr-3 py-1.5 border border-gray-200 rounded font-didact bg-gray-50 focus:bg-white focus:outline-none focus:border-hadero-gold text-xs transition-all duration-200"
                            />
                            <div class="absolute left-2.5 top-2 text-zinc-400">
                                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>

                        <!-- Category Selector dropdown filter -->
                        <div class="sm:col-span-5">
                            <select
                                id="admin-category-filter"
                                onchange="filterAdminCatalog()"
                                class="w-full border border-gray-200 px-2.5 py-1.5 rounded focus:outline-none focus:border-hadero-gold font-didact text-xs bg-gray-50 focus:bg-white transition-all duration-200"
                            >
                                <option value="All">All Categories</option>
                                <?php foreach ($categories as $catName): ?>
                                    <option value="<?= htmlspecialchars($catName) ?>"><?= htmlspecialchars($catName) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <?php if (empty($items)): ?>
                        <div class="py-20 text-center text-gray-400 border border-dashed border-gray-100 rounded">
                            <p class="font-didact text-sm">No items stored. Fill in the registry on the left to write.</p>
                        </div>
                    <?php else: ?>
                        <div class="hidden md:block overflow-x-auto">
                            <table class="w-full text-left text-xs border-collapse divide-y divide-gray-100">
                                <thead>
                                    <tr class="bg-gray-50 text-gray-500 uppercase font-oswald tracking-widest text-[9px]">
                                        <th class="py-2.5 px-3 font-medium w-12 text-center">ID</th>
                                        <th class="py-2.5 px-3 font-medium">Product details</th>
                                        <th class="py-2.5 px-3 font-medium">Category</th>
                                        <th class="py-2.5 px-3 font-medium">Price</th>
                                        <th class="py-2.5 px-3 font-medium text-right w-20">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <?php foreach ($items as $item): ?>
                                        <tr class="admin-item-row hover:bg-gray-50/40 transition" 
                                            data-name="<?= htmlspecialchars(strtolower($item['name'])) ?>"
                                            data-category="<?= htmlspecialchars(strtolower($item['category'])) ?>"
                                            data-description="<?= htmlspecialchars(strtolower($item['description'] ?? '')) ?>">
                                            <td class="py-3 px-3 text-center font-mono text-gray-400">#<?= htmlspecialchars($item['id']) ?></td>
                                            <td class="py-3 px-3">
                                                <div class="flex items-center gap-2.5">
                                                    <div class="w-8 h-8 rounded border border-gray-150 overflow-hidden shrink-0 flex items-center justify-center bg-gray-50">
                                                        <?php if (!empty($item['image_url'])): ?>
                                                            <img src="<?= htmlspecialchars($item['image_url']) ?>" class="w-full h-full object-cover" />
                                                        <?php else: ?>
                                                            <i data-lucide="image" class="w-4 h-4 text-zinc-300"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <span class="block font-semibold uppercase text-zinc-950 font-oswald text-[11px]"><?= htmlspecialchars($item['name']) ?></span>
                                                        <span class="block font-didact text-[10px] text-gray-400 line-clamp-1 max-w-[170px]"><?= htmlspecialchars($item['description']) ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-3 px-3">
                                                <span class="inline-block bg-[#9B9B45]/15 text-[#72722b] px-2 py-0.5 rounded font-oswald text-[9px] uppercase tracking-wider font-semibold">
                                                    <?= htmlspecialchars($item['category']) ?>
                                                </span>
                                            </td>
                                            <td class="py-3 px-3 font-mono text-zinc-900 font-semibold"><?= htmlspecialchars($item['price']) ?></td>
                                            <td class="py-3 px-3 text-right">
                                                <div class="flex gap-1.5 justify-end">
                                                    <a href="admin.php?tab=menu&edit=<?= $item['id'] ?>" class="p-1 bg-zinc-55 border border-zinc-200 text-zinc-700 hover:bg-black hover:text-white rounded transition" title="Modify record">
                                                        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                                    </a>
                                                    <form action="admin.php?tab=menu" method="POST" data-confirm-message="Do you want to permanently withdraw this catalog item?" class="inline">
                                                        <input type="hidden" name="action" value="delete" />
                                                        <input type="hidden" name="id" value="<?= $item['id'] ?>" />
                                                        <button type="submit" class="p-1 bg-red-50 border border-red-100 text-red-600 hover:bg-red-600 hover:text-white rounded transition" title="Withdraw record">
                                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="grid grid-cols-1 gap-3.5 md:hidden">
                            <?php foreach ($items as $item): ?>
                                <div class="admin-item-card p-3.5 bg-[#fcfbf7]/45 border border-zinc-150 rounded-xl space-y-3"
                                     data-name="<?= htmlspecialchars(strtolower($item['name'])) ?>"
                                     data-category="<?= htmlspecialchars(strtolower($item['category'])) ?>"
                                     data-description="<?= htmlspecialchars(strtolower($item['description'] ?? '')) ?>">
                                    <div class="flex items-center justify-between">
                                        <span class="font-mono text-[10px] text-zinc-400">RECORD #<?= htmlspecialchars($item['id']) ?></span>
                                        <span class="bg-[#9B9B45]/20 text-[#606020] px-2 py-0.5 rounded text-[9px] uppercase font-oswald font-bold tracking-wider">
                                            <?= htmlspecialchars($item['category']) ?>
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded border border-gray-150 overflow-hidden shrink-0 flex items-center justify-center bg-white">
                                            <?php if (!empty($item['image_url'])): ?>
                                                <img src="<?= htmlspecialchars($item['image_url']) ?>" class="w-full h-full object-cover" />
                                            <?php else: ?>
                                                <i data-lucide="image" class="w-4 h-4 text-zinc-300"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <h4 class="font-oswald text-xs uppercase font-bold text-black"><?= htmlspecialchars($item['name']) ?></h4>
                                            <span class="block font-mono text-[11px] font-bold text-gray-800"><?= htmlspecialchars($item['price']) ?></span>
                                        </div>
                                    </div>

                                    <?php if (!empty($item['description'])): ?>
                                        <p class="font-didact text-zinc-500 text-[11px] leading-relaxed line-clamp-2">
                                            <?= htmlspecialchars($item['description']) ?>
                                        </p>
                                    <?php endif; ?>

                                    <div class="flex gap-2 justify-end border-t border-zinc-100 pt-2.5">
                                        <a href="admin.php?tab=menu&edit=<?= $item['id'] ?>" class="flex-1 py-1.5 border border-zinc-200 hover:bg-black hover:text-white rounded text-[11px] font-oswald text-center uppercase tracking-wider transition flex items-center justify-center gap-1 bg-white">
                                            <i data-lucide="edit-2" class="w-3 h-3"></i>
                                            <span>Edit</span>
                                        </a>
                                        <form action="admin.php?tab=menu" method="POST" data-confirm-message="Do you want to permanently withdraw this catalog item?" class="flex-1 select-none">
                                            <input type="hidden" name="action" value="delete" />
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>" />
                                            <button type="submit" class="w-full py-1.5 bg-red-50 text-red-600 border border-red-150 hover:bg-red-600 hover:text-white rounded text-[11px] font-oswald uppercase tracking-wider transition flex items-center justify-center gap-1">
                                                <i data-lucide="trash-2" class="w-3 h-3"></i>
                                                <span>Delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Real-time empty state fallback -->
                        <div id="admin-no-results-msg" class="hidden text-center py-16 border border-dashed border-gray-200 bg-gray-50/30 rounded-xl max-w-md mx-auto space-y-3 mt-4">
                            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mx-auto text-gray-400">
                                <i data-lucide="bookmark-x" class="w-5 h-5"></i>
                            </div>
                            <div class="space-y-1">
                                <h4 class="font-oswald text-xs uppercase tracking-wider text-black font-semibold">No catalog entries match</h4>
                                <p class="font-didact text-xs text-gray-400 px-4">There are no menu products matching your currently entered keyword or selected category tag.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        <?php endif; ?>

        <?php if ($active_tab === 'categories'): ?>
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">

                <div class="md:col-span-5 bg-white border border-[#f1efe6] shadow-md p-5 sm:p-6 rounded-xl space-y-4">
                    <h2 class="font-oswald text-base font-semibold text-black uppercase tracking-widest border-b border-gray-100 pb-2">Create category</h2>
                    
                    <form action="admin.php?tab=categories" method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="add_category" />
                        <div>
                            <label for="category_name" class="block text-[11px] uppercase font-oswald tracking-widest font-semibold text-gray-700 mb-1">Category Title</label>
                            <input type="text" id="category_name" name="category_name" placeholder="e.g. Cold Coffee" class="w-full border border-gray-200 px-3 py-2 rounded focus:outline-none focus:border-hadero-gold font-didact text-sm bg-gray-50 focus:bg-white transition" required />
                        </div>
                        <button type="submit" class="w-full py-2 bg-black hover:bg-zinc-800 text-white font-oswald text-xs uppercase tracking-widest font-semibold transition flex items-center justify-center gap-1.5 rounded">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                            <span>Register Category</span>
                        </button>
                    </form>
                </div>

                <div class="md:col-span-7 bg-white border border-[#f1efe6] shadow-md p-5 sm:p-6 rounded-xl space-y-4">
                    <h2 class="font-oswald text-base font-semibold text-black uppercase tracking-widest border-b border-gray-100 pb-2">Category Registry</h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pb-2">
                        <?php foreach ($categories as $catName): 
                            $isSystem = in_array($catName, ['Coffee', 'Bakery', 'Light Bites']);
                        ?>
                            <div class="p-3 bg-zinc-50 border border-zinc-150 rounded-xl flex items-center justify-between transition-all duration-200" id="cat-card-<?= md5($catName) ?>">
                                <div class="flex-grow flex items-center justify-between gap-3" id="cat-view-<?= md5($catName) ?>">
                                    <div>
                                        <span class="block font-oswald text-sm font-semibold uppercase text-zinc-950"><?= htmlspecialchars($catName) ?></span>
                                        <span class="block text-[9px] font-mono text-zinc-400 uppercase mt-0.5"><?= $isSystem ? 'Standard core' : 'Custom addon' ?></span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button type="button" onclick="startEditCategory('<?= md5($catName) ?>')" class="p-1 px-1.5 hover:bg-zinc-200 rounded transition text-zinc-500 hover:text-black" title="Rename category">
                                            <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <form action="admin.php?tab=categories" method="POST" data-confirm-message="Permanently retire the category tag '<?= htmlspecialchars($catName) ?>'? Menu items nested under this category will remain, but can be updated." class="inline-block m-0 p-0">
                                            <input type="hidden" name="action" value="delete_category" />
                                            <input type="hidden" name="category_name" value="<?= htmlspecialchars($catName) ?>" />
                                            <button type="submit" class="p-1 text-zinc-400 hover:text-red-650 hover:text-red-600 hover:bg-red-50 rounded transition">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Dynamic Edit Mode Form (Hidden initially) -->
                                <form action="admin.php?tab=categories" method="POST" id="cat-edit-form-<?= md5($catName) ?>" class="hidden w-full flex items-center justify-between gap-2 m-0 p-0">
                                    <input type="hidden" name="action" value="edit_category" />
                                    <input type="hidden" name="old_category_name" value="<?= htmlspecialchars($catName) ?>" />
                                    <input type="text" name="new_category_name" value="<?= htmlspecialchars($catName) ?>" class="bg-white border border-gray-300 text-xs py-1 px-2 rounded focus:outline-none focus:border-hadero-gold flex-grow font-didact" required />
                                    
                                    <div class="flex items-center gap-1">
                                        <button type="submit" class="p-1 text-green-600 hover:bg-green-50 rounded transition" title="Save changes">
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                        </button>
                                        <button type="button" onclick="cancelEditCategory('<?= md5($catName) ?>')" class="p-1 text-red-405 text-gray-400 hover:bg-gray-150 rounded transition" title="Cancel">
                                            <i data-lucide="x" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        <?php endif; ?>

        <?php if ($active_tab === 'settings'): ?>
            <div class="max-w-xl mx-auto bg-white border border-[#f1efe6] shadow-md p-5 sm:p-8 rounded-xl space-y-6">
                <div class="border-b border-gray-100 pb-3">
                    <h2 class="font-oswald text-base font-semibold text-black uppercase tracking-widest flex items-center gap-1.5">
                        <i data-lucide="settings" class="text-hadero-gold"></i>
                        <span>Security Password settings</span>
                    </h2>
                    <p class="font-didact text-xs text-gray-500 mt-1">Configure your personalized web developer security lock settings.</p>
                </div>

                <form action="admin.php?tab=settings" method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="change_password" />

                    <div>
                        <label for="old_password" class="block text-[11px] uppercase font-oswald tracking-widest font-semibold text-gray-700 mb-1">Current Password *</label>
                        <input type="password" id="old_password" name="old_password" placeholder="Verify current password" class="w-full border border-gray-200 px-3 py-2 rounded focus:outline-none focus:border-hadero-gold text-sm bg-gray-50 focus:bg-white transition" required />
                    </div>

                    <div>
                        <label for="new_password" class="block text-[11px] uppercase font-oswald tracking-widest font-semibold text-gray-700 mb-1">New Password requested *</label>
                        <input type="password" id="new_password" name="new_password" placeholder="Define new password string" class="w-full border border-gray-200 px-3 py-2 rounded focus:outline-none focus:border-hadero-gold text-sm bg-gray-50 focus:bg-white transition" required />
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-[11px] uppercase font-oswald tracking-widest font-semibold text-gray-700 mb-1">Confirm New Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter to verify match" class="w-full border border-gray-200 px-3 py-2 rounded focus:outline-none focus:border-hadero-gold text-sm bg-gray-50 focus:bg-white transition" required />
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-black hover:bg-zinc-800 text-white font-oswald text-xs uppercase tracking-widest font-semibold transition rounded mt-2">
                        Commit New Security Sequence
                    </button>
                </form>
            </div>
        <?php endif; ?>

    </div>

    <div id="cameraModal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white border border-gray-200 rounded-xl max-w-sm sm:max-w-md w-full overflow-hidden shadow-2xl p-5 sm:p-6 space-y-4">
            
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-oswald text-sm sm:text-base uppercase text-black flex items-center gap-1.5 font-bold">
                    <i data-lucide="camera" class="text-hadero-gold"></i>
                    <span>Live Webcam Viewfinder</span>
                </h3>
                <button type="button" onclick="stopCamera()" class="text-gray-400 hover:text-black font-semibold text-xs sm:text-sm">Cancel</button>
            </div>

            <div class="relative aspect-[3/2] w-full bg-black rounded-lg overflow-hidden border border-gray-950">
                <video id="videoElement" autoplay playsinline muted class="w-full h-full object-cover"></video>
                <span class="absolute top-2 left-2 px-2 py-0.5 bg-emerald-500 text-black text-[9px] font-mono rounded tracking-wider flex items-center gap-1">
                    <span class="w-1.5 h-1.5 bg-black rounded-full animate-ping"></span>
                    CAMERA ACTIVE
                </span>
            </div>

            <div class="flex gap-3 justify-end pt-1">
                <button type="button" onclick="stopCamera()" class="px-4 py-2 border border-gray-200 text-gray-500 hover:bg-gray-50 rounded text-xs font-oswald uppercase tracking-widest transition">Close</button>
                <button type="button" onclick="takeSnapshot()" class="px-5 py-2 bg-hadero-gold text-black font-semibold hover:bg-[#b5b552] rounded text-xs font-oswald uppercase tracking-widest transition">Snapshot</button>
            </div>

        </div>
    </div>

    <script src="js/admin-script.js"></script>

    <div id="general-confirm-modal" class="hidden fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-black/60 transition-opacity" aria-hidden="true" onclick="dismissConfirmModal()"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-[#FDFBF7] border border-[#e8e6dc] rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6 space-y-4">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-red-50 border border-red-150 flex items-center justify-center text-red-600 shrink-0">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    </div>
                    <div class="space-y-1.5 flex-grow">
                        <h3 class="font-oswald text-sm font-semibold uppercase tracking-wider text-black" id="modal-title">Confirm Database Action</h3>
                        <p class="font-didact text-xs text-gray-500 leading-relaxed font-light" id="confirm-modal-message">Are you sure you want to execute this change?</p>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" onclick="dismissConfirmModal()" class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-zinc-700 font-oswald text-[11px] uppercase tracking-wider rounded font-medium transition cursor-pointer select-none">
                        Cancel
                    </button>
                    <button type="button" id="confirm-modal-submit-btn" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-oswald text-[11px] uppercase tracking-wider rounded font-medium transition cursor-pointer select-none">
                        Confirm Action
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>