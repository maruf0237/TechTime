<?php
session_start();

/**
 * Absolute base URL of the app ("/techtime"), works no matter what the
 * project folder is named and no matter how deep the current script is
 * (root pages or admin/*). This is what makes every image and link work
 * from anywhere in the site.
 */
function base_url(): string {
    static $base = null;
    if ($base === null) {
        // Compare the on-disk path of the script that's actually running
        // against the on-disk path of this app's root folder (one level up
        // from includes/), then apply the same trim to the URL. This avoids
        // relying on DOCUMENT_ROOT matching exactly, which can fail on some
        // Windows/XAMPP setups (symlinks, drive-letter casing, etc.) and was
        // causing images, CSS and JS to silently fail to load.
        $scriptFile = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
        $appRoot    = str_replace('\\', '/', dirname(__DIR__)); // .../techtime
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

        if ($scriptFile !== '' && strpos($scriptFile, $appRoot) === 0) {
            $diskSuffix = substr($scriptFile, strlen($appRoot)); // e.g. /admin/dashboard.php
            if ($diskSuffix !== '' && substr($scriptName, -strlen($diskSuffix)) === $diskSuffix) {
                $base = substr($scriptName, 0, strlen($scriptName) - strlen($diskSuffix));
            }
        }

        // Fallback to the original DOCUMENT_ROOT comparison if the above
        // couldn't work out a base path for some reason.
        if ($base === null) {
            $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
            $appRootReal = str_replace('\\', '/', realpath(__DIR__ . '/..'));
            $base = ($docRoot && $appRootReal) ? rtrim(substr($appRootReal, strlen($docRoot)), '/') : '';
        }
    }
    return $base;
}
function asset(string $path): string {
    return base_url() . '/' . ltrim($path, '/');
}

function clean($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function money($amount): string {
    return '$' . number_format((float)$amount, 2);
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}
function is_admin(): bool {
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}
function require_login(): void {
    if (!is_logged_in()) {
        header('Location: ' . asset('login.php'));
        exit;
    }
}
function require_admin(): void {
    if (!is_admin()) {
        header('Location: ' . asset('admin/login.php'));
        exit;
    }
}

/** Maps a category name to the bundled illustration file name. */
function category_icon_slug(?string $categoryName): string {
    $map = [
        'laptops'     => 'laptop',
        'smartphones' => 'smartphone',
        'monitors'    => 'monitor',
        'keyboards'   => 'keyboard',
        'headphones'  => 'headphones',
        'accessories' => 'accessory',
    ];
    $key = strtolower(trim((string)$categoryName));
    return $map[$key] ?? 'generic';
}

/**
 * Always returns a real, working image URL for a product:
 *  1. the admin-uploaded photo, if the file actually exists on disk
 *  2. otherwise the bundled illustration matching the product's category
 *  3. otherwise the generic illustration
 * This is what fixes the broken/missing product images.
 */
function product_image_path(array $product): string {
    if (!empty($product['image'])) {
        $diskPath = __DIR__ . '/../uploads/products/' . $product['image'];
        if (is_file($diskPath)) {
            return asset('uploads/products/' . rawurlencode($product['image']));
        }
    }
    $slug = category_icon_slug($product['category_name'] ?? null);
    $diskIcon = __DIR__ . '/../assets/images/categories/' . $slug . '.svg';
    if (!is_file($diskIcon)) {
        $slug = 'generic';
    }
    return asset('assets/images/categories/' . $slug . '.svg');
}

/**
 * Every photo for a product's gallery, in order: the extra photos saved in
 * product_images first, then the main "products.image" thumbnail, then (only
 * if there are no real photos at all) the category illustration. Pass the
 * PDO connection and the product row (must include category_name for the
 * fallback icon).
 */
function product_gallery(PDO $pdo, array $product): array {
    $images = [];
    $stmt = $pdo->prepare('SELECT image FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC');
    $stmt->execute([$product['id']]);
    foreach ($stmt->fetchAll() as $row) {
        if (is_file(__DIR__ . '/../uploads/products/' . $row['image'])) {
            $images[] = asset('uploads/products/' . rawurlencode($row['image']));
        }
    }
    if (!empty($product['image']) && is_file(__DIR__ . '/../uploads/products/' . $product['image'])) {
        array_unshift($images, asset('uploads/products/' . rawurlencode($product['image'])));
    }
    $images = array_values(array_unique($images));
    if (!$images) {
        $images[] = product_image_path($product);
    }
    return $images;
}

function render_stars($avgRating, $count = null): string {
    $avg   = round((float)$avgRating * 2) / 2; // nearest half star
    $html  = '<div class="stars" aria-label="' . clean(number_format((float)$avgRating, 1)) . ' out of 5 stars">';
    for ($i = 1; $i <= 5; $i++) {
        if ($avg >= $i)        $html .= '<i class="fa-solid fa-star"></i>';
        elseif ($avg >= $i - 0.5) $html .= '<i class="fa-solid fa-star-half-stroke"></i>';
        else                    $html .= '<i class="fa-regular fa-star"></i>';
    }
    if ($count !== null) {
        $html .= '<span class="stars-count">(' . (int)$count . ')</span>';
    }
    $html .= '</div>';
    return $html;
}

function flash_set(string $key, string $message): void {
    $_SESSION['flash'][$key] = $message;
}
function flash_get(string $key): ?string {
    if (!empty($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}
