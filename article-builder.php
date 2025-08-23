<?php
/**
 * Plugin Name: Article Builder
 * Plugin URI: https://forexsignale.trade
 * Description: Professioneller Article Builder mit HTML-Block-System und KI-Integration für die Erstellung von Trading-Artikeln
 * Version: 1.0.0
 * Author: ForexSignale Magazine
 * Author URI: https://forexsignale.trade
 * License: GPL v2 or later
 * Text Domain: article-builder
 */

// Sicherheit: Direktzugriff verhindern
if (!defined('ABSPATH')) {
    exit;
}

// Plugin-Konstanten definieren
define('AB_PLUGIN_VERSION', '1.0.0');
define('ARTICLE_BUILDER_VERSION', '1.0.0'); // Zusätzliche Konstante für Kompatibilität
define('AB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AB_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader für Klassen
spl_autoload_register(function ($class) {
    $prefix = 'ArticleBuilder\\';
    $base_dir = AB_PLUGIN_DIR . 'includes/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    // Einfachere Pfad-Konvertierung ohne Backslash-Probleme
    $class_name = strtolower($relative_class);
    $file = $base_dir . 'class-' . $class_name . '.php';
    
    if (file_exists($file)) {
        require_once $file;
    }
});

// Plugin-Aktivierung
register_activation_hook(__FILE__, 'ab_activate_plugin');
function ab_activate_plugin() {
    // Datenbank-Tabellen erstellen
    require_once AB_PLUGIN_DIR . 'includes/class-database.php';
    $database = new ArticleBuilder\Database();
    $database->create_tables();
    
    // Capabilities hinzufügen
    $role = get_role('administrator');
    if ($role) {
        $role->add_cap('manage_article_builder');
    }
    
    $role = get_role('editor');
    if ($role) {
        $role->add_cap('use_article_builder');
    }
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Plugin-Deaktivierung
register_deactivation_hook(__FILE__, 'ab_deactivate_plugin');
function ab_deactivate_plugin() {
    // Cleanup tasks
    flush_rewrite_rules();
}

// Plugin initialisieren
add_action('init', 'ab_init_plugin');
function ab_init_plugin() {
    // Text Domain für Übersetzungen laden
    load_plugin_textdomain('article-builder', false, dirname(AB_PLUGIN_BASENAME) . '/languages');
}

// Admin-Bereich initialisieren
if (is_admin()) {
    require_once AB_PLUGIN_DIR . 'includes/class-admin.php';
    new ArticleBuilder\Admin();
}

// AJAX-Handler registrieren
require_once AB_PLUGIN_DIR . 'includes/class-ajax.php';
new ArticleBuilder\Ajax();

// REST API Endpoints registrieren
add_action('rest_api_init', function() {
    require_once AB_PLUGIN_DIR . 'includes/class-api.php';
    $api = new ArticleBuilder\API();
    $api->register_routes();
});

// Shortcode für Frontend-Tests (optional)
add_shortcode('article_builder_preview', 'ab_preview_shortcode');
function ab_preview_shortcode($atts) {
    $atts = shortcode_atts([
        'id' => 0,
    ], $atts);
    
    if (!$atts['id']) {
        return '';
    }
    
    // Preview-HTML generieren
    require_once AB_PLUGIN_DIR . 'includes/class-generator.php';
    $generator = new ArticleBuilder\Generator();
    return $generator->generate_preview($atts['id']);
}

// Admin-Notices für wichtige Meldungen
add_action('admin_notices', 'ab_admin_notices');
function ab_admin_notices() {
    // Prüfe auf Claude API Key
    $api_key = get_option('ab_claude_api_key');
    if (empty($api_key) && current_user_can('manage_options')) {
        $settings_url = admin_url('admin.php?page=article-builder-settings');
        ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <strong><?php _e('Article Builder:', 'article-builder'); ?></strong>
                <?php 
                printf(
                    __('Für die KI-Integration benötigen Sie einen Claude API Key. <a href="%s">Jetzt konfigurieren</a>', 'article-builder'),
                    esc_url($settings_url)
                );
                ?>
            </p>
        </div>
        <?php
    }
}

// Enqueue Admin Scripts und Styles
add_action('admin_enqueue_scripts', 'ab_enqueue_admin_assets');
function ab_enqueue_admin_assets($hook) {
    // Nur auf unseren Plugin-Seiten laden
    if (strpos($hook, 'article-builder') === false) {
        return;
    }
    
    // CSS
    wp_enqueue_style(
        'article-builder-admin',
        AB_PLUGIN_URL . 'assets/css/admin.css',
        [],
        AB_PLUGIN_VERSION
    );
    
    // JavaScript
    wp_enqueue_script(
        'article-builder-admin',
        AB_PLUGIN_URL . 'assets/js/builder.js',
        ['jquery', 'jquery-ui-sortable', 'wp-api', 'wp-i18n'],
        AB_PLUGIN_VERSION,
        true
    );
    
    // Lokalisierung für JavaScript
    wp_localize_script('article-builder-admin', 'articleBuilder', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'apiUrl' => rest_url('article-builder/v1/'),
        'nonce' => wp_create_nonce('article-builder-nonce'),
        'strings' => [
            'confirmDelete' => __('Sind Sie sicher, dass Sie diesen Block löschen möchten?', 'article-builder'),
            'copying' => __('Kopiere HTML...', 'article-builder'),
            'copied' => __('HTML kopiert!', 'article-builder'),
            'error' => __('Ein Fehler ist aufgetreten.', 'article-builder'),
            'generating' => __('KI generiert Inhalte...', 'article-builder'),
        ],
        'categories' => get_categories(['hide_empty' => false]),
        'tags' => get_tags(['hide_empty' => false]),
    ]);
    
    // WordPress Media Uploader
    wp_enqueue_media();
    
    // CodeMirror für HTML-Ansicht
    wp_enqueue_code_editor(['type' => 'text/html']);
}