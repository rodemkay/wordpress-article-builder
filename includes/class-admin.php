<?php
namespace ArticleBuilder;

/**
 * Admin Interface für Article Builder Plugin
 *
 * @package ArticleBuilder
 * @subpackage Admin
 */

// Sicherheitscheck - direkter Aufruf verhindern
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin-Klasse für Article Builder Plugin
 */
class Admin {

    /**
     * Plugin-Slug
     */
    private $plugin_slug = 'article-builder';

    /**
     * Admin-Seiten-Hook-Namen
     */
    private $admin_hooks = array();

    /**
     * Konstruktor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'register_admin_menus'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        // AJAX-Hooks werden in der AJAX-Klasse verwaltet
    }

    /**
     * Admin-Menüs registrieren
     */
    public function register_admin_menus() {
        // Hauptmenü hinzufügen
        $main_hook = add_menu_page(
            'Article Builder',
            'Article Builder',
            'edit_posts',
            $this->plugin_slug,
            array($this, 'display_main_page'),
            'dashicons-edit-page',
            30
        );

        // Untermenüs hinzufügen
        $this->admin_hooks['main'] = add_submenu_page(
            $this->plugin_slug,
            'Artikel erstellen',
            'Artikel erstellen',
            'edit_posts',
            $this->plugin_slug,
            array($this, 'display_main_page')
        );

        $this->admin_hooks['projects'] = add_submenu_page(
            $this->plugin_slug,
            'Meine Projekte',
            'Meine Projekte',
            'edit_posts',
            $this->plugin_slug . '-projects',
            array($this, 'display_projects_page')
        );

        $this->admin_hooks['settings'] = add_submenu_page(
            $this->plugin_slug,
            'Einstellungen',
            'Einstellungen',
            'manage_options',
            $this->plugin_slug . '-settings',
            array($this, 'display_settings_page')
        );
    }

    /**
     * Admin-Scripts und -Styles einbinden
     */
    public function enqueue_admin_scripts($hook) {
        // Nur auf unseren Admin-Seiten laden
        if (!in_array($hook, $this->admin_hooks)) {
            return;
        }

        $plugin_url = plugin_dir_url(dirname(__FILE__));
        
        // React Build Assets laden
        if (file_exists(AB_PLUGIN_DIR . 'assets/dist/')) {
            // Runtime
            wp_enqueue_script(
                'article-builder-runtime',
                $plugin_url . 'assets/dist/runtime.ec2ee5e556857e218407.js',
                array(),
                AB_PLUGIN_VERSION,
                true
            );
            
            // React Vendor Bundle
            wp_enqueue_script(
                'article-builder-react',
                $plugin_url . 'assets/dist/react.b418c45fc023c2a6c514.js',
                array('article-builder-runtime'),
                AB_PLUGIN_VERSION,
                true
            );
            
            // Vendors Bundle
            wp_enqueue_script(
                'article-builder-vendors',
                $plugin_url . 'assets/dist/vendors.550a0f6eefc8f16ff31e.js',
                array('article-builder-runtime', 'article-builder-react'),
                AB_PLUGIN_VERSION,
                true
            );
            
            // Common Bundle
            wp_enqueue_script(
                'article-builder-common',
                $plugin_url . 'assets/dist/common.84625698456138cf5ffc.js',
                array('article-builder-runtime', 'article-builder-react', 'article-builder-vendors'),
                AB_PLUGIN_VERSION,
                true
            );
            
            // Admin Bundle
            wp_enqueue_script(
                'article-builder-admin',
                $plugin_url . 'assets/dist/admin.c85b2a8b7a0934900eb7.js',
                array('article-builder-runtime', 'article-builder-react', 'article-builder-vendors', 'article-builder-common'),
                AB_PLUGIN_VERSION,
                true
            );
            
            // Admin CSS
            wp_enqueue_style(
                'article-builder-admin-css',
                $plugin_url . 'assets/dist/admin.8b168fb05fc652172c24.css',
                array(),
                AB_PLUGIN_VERSION
            );
        } else {
            // Fallback auf alte Dateien
            wp_enqueue_style(
                'article-builder-admin',
                $plugin_url . 'assets/css/admin.css',
                array(),
                AB_PLUGIN_VERSION
            );

            wp_enqueue_script(
                'article-builder-admin',
                $plugin_url . 'assets/js/admin.js',
                array('jquery', 'jquery-ui-sortable', 'wp-util'),
                AB_PLUGIN_VERSION,
                true
            );
        }

        // Lokalisierung für AJAX
        wp_localize_script('article-builder-admin', 'articleBuilder', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('article_builder_nonce'),
            'api_url' => rest_url('article-builder/v1'),
            'mcp_enabled' => true,
            'strings' => array(
                'save_success' => 'Projekt erfolgreich gespeichert!',
                'save_error' => 'Fehler beim Speichern des Projekts.',
                'delete_confirm' => 'Sind Sie sicher, dass Sie dieses Projekt löschen möchten?',
                'export_success' => 'HTML in die Zwischenablage kopiert!',
                'export_error' => 'Fehler beim Exportieren des HTML-Codes.'
            )
        ));

        // WordPress Media Uploader
        wp_enqueue_media();
    }

    /**
     * Hauptseite - Article Builder Interface
     */
    public function display_main_page() {
        // Berechtigungen prüfen
        if (!current_user_can('edit_posts')) {
            wp_die(__('Sie haben keine Berechtigung für diese Aktion.'));
        }

        // Projekt-ID aus URL Parameter
        $project_id = isset($_GET['project']) ? intval($_GET['project']) : 0;
        $project_data = null;

        if ($project_id > 0) {
            $project_data = $this->load_project($project_id);
        }

        ?>
        <div class="wrap article-builder-interface">
            <h1 class="wp-heading-inline">Article Builder</h1>
            
            <?php if ($project_id > 0): ?>
                <a href="<?php echo admin_url('admin.php?page=' . $this->plugin_slug); ?>" class="page-title-action">Neues Projekt</a>
            <?php endif; ?>

            <hr class="wp-header-end">

            <!-- React App Container -->
            <div id="article-builder-admin" 
                 data-project-id="<?php echo esc_attr($project_id); ?>"
                 data-project='<?php echo $project_data ? esc_attr(json_encode($project_data)) : '{}'; ?>'>
                <!-- React App wird hier geladen -->
                <div class="notice notice-info">
                    <p>Lade Article Builder Interface...</p>
                </div>
            </div>

            <!-- Fallback auf altes Interface wenn React nicht lädt -->
            <noscript>
            <div class="article-builder-container">
                <!-- Metadata Sektion -->
                <div class="metadata-section">
                    <div class="postbox">
                        <div class="postbox-header">
                            <h2 class="hndle">Artikel-Metadaten</h2>
                        </div>
                        <div class="inside">
                            <form id="article-metadata-form">
                                <?php wp_nonce_field('article_builder_save', 'article_builder_nonce'); ?>
                                <input type="hidden" id="project-id" value="<?php echo esc_attr($project_id); ?>">

                                <table class="form-table" role="presentation">
                                    <tr>
                                        <th scope="row">
                                            <label for="article-title">Artikel-Titel</label>
                                        </th>
                                        <td>
                                            <input type="text" id="article-title" name="article_title" class="regular-text" 
                                                   value="<?php echo esc_attr($project_data['title'] ?? ''); ?>" 
                                                   placeholder="Geben Sie den Artikel-Titel ein">
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row">
                                            <label for="article-type">Artikel-Typ</label>
                                        </th>
                                        <td>
                                            <select id="article-type" name="article_type">
                                                <option value="post" <?php selected($project_data['type'] ?? 'post', 'post'); ?>>Blog-Artikel</option>
                                                <option value="page" <?php selected($project_data['type'] ?? 'post', 'page'); ?>>Seite</option>
                                                <option value="analysis" <?php selected($project_data['type'] ?? 'post', 'analysis'); ?>>Markt-Analyse</option>
                                                <option value="news" <?php selected($project_data['type'] ?? 'post', 'news'); ?>>News-Artikel</option>
                                                <option value="guide" <?php selected($project_data['type'] ?? 'post', 'guide'); ?>>Trading-Guide</option>
                                            </select>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th scope="row">
                                            <label for="article-categories">Kategorien</label>
                                        </th>
                                        <td>
                                            <?php
                                            $categories = get_categories(array('hide_empty' => false));
                                            $selected_cats = isset($project_data['categories']) ? $project_data['categories'] : array();
                                            ?>
                                            <div class="categorydiv">
                                                <div class="tabs-panel">
                                                    <?php foreach ($categories as $category): ?>
                                                        <label class="selectit">
                                                            <input type="checkbox" name="article_categories[]" 
                                                                   value="<?php echo esc_attr($category->term_id); ?>"
                                                                   <?php checked(in_array($category->term_id, $selected_cats)); ?>>
                                                            <?php echo esc_html($category->name); ?>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th scope="row">
                                            <label for="article-tags">Tags</label>
                                        </th>
                                        <td>
                                            <input type="text" id="article-tags" name="article_tags" class="regular-text" 
                                                   value="<?php echo esc_attr($project_data['tags'] ?? ''); ?>" 
                                                   placeholder="Trading, Forex, Analyse (durch Komma getrennt)">
                                            <p class="description">Trennen Sie Tags durch Kommas.</p>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th scope="row">
                                            <label for="article-excerpt">Kurzbeschreibung</label>
                                        </th>
                                        <td>
                                            <textarea id="article-excerpt" name="article_excerpt" rows="3" class="large-text"
                                                      placeholder="Kurze Beschreibung des Artikels für SEO und Vorschau"><?php echo esc_textarea($project_data['excerpt'] ?? ''); ?></textarea>
                                        </td>
                                    </tr>
                                </table>

                                <p class="submit">
                                    <button type="button" id="save-project" class="button button-primary">
                                        <?php echo $project_id > 0 ? 'Projekt aktualisieren' : 'Projekt speichern'; ?>
                                    </button>
                                    <?php if ($project_id > 0): ?>
                                        <button type="button" id="export-html" class="button button-secondary">HTML exportieren</button>
                                    <?php endif; ?>
                                </p>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Block Management Sektion -->
                <div class="blocks-section">
                    <div class="postbox">
                        <div class="postbox-header">
                            <h2 class="hndle">Block-Management</h2>
                        </div>
                        <div class="inside">
                            <!-- Block-Bibliothek -->
                            <div class="block-library">
                                <h3>Verfügbare Blöcke</h3>
                                <div class="block-library-grid">
                                    <div class="block-item" data-block-type="introduction">
                                        <div class="block-icon">📝</div>
                                        <div class="block-title">Einleitung</div>
                                        <div class="block-description">Artikel-Einleitung mit Hook</div>
                                    </div>
                                    
                                    <div class="block-item" data-block-type="market_analysis">
                                        <div class="block-icon">📊</div>
                                        <div class="block-title">Markt-Analyse</div>
                                        <div class="block-description">Technische oder fundamentale Analyse</div>
                                    </div>
                                    
                                    <div class="block-item" data-block-type="trading_strategy">
                                        <div class="block-icon">🎯</div>
                                        <div class="block-title">Trading-Strategie</div>
                                        <div class="block-description">Konkrete Handelsstrategie</div>
                                    </div>
                                    
                                    <div class="block-item" data-block-type="risk_management">
                                        <div class="block-icon">⚠️</div>
                                        <div class="block-title">Risiko-Management</div>
                                        <div class="block-description">Risk Management Tipps</div>
                                    </div>
                                    
                                    <div class="block-item" data-block-type="conclusion">
                                        <div class="block-icon">✅</div>
                                        <div class="block-title">Fazit</div>
                                        <div class="block-description">Zusammenfassung und Ausblick</div>
                                    </div>
                                    
                                    <div class="block-item" data-block-type="cta">
                                        <div class="block-icon">🚀</div>
                                        <div class="block-title">Call-to-Action</div>
                                        <div class="block-description">Newsletter, Broker, Premium</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Aktuelle Blöcke -->
                            <div class="current-blocks">
                                <h3>Artikel-Struktur</h3>
                                <div id="blocks-container" class="blocks-sortable">
                                    <?php if (isset($project_data['blocks']) && !empty($project_data['blocks'])): ?>
                                        <?php foreach ($project_data['blocks'] as $index => $block): ?>
                                            <div class="block-item-active" data-block-id="<?php echo esc_attr($index); ?>" data-block-type="<?php echo esc_attr($block['type']); ?>">
                                                <div class="block-header">
                                                    <div class="block-info">
                                                        <span class="block-title"><?php echo esc_html($this->get_block_title($block['type'])); ?></span>
                                                        <span class="block-status"><?php echo !empty($block['content']) ? 'Generiert' : 'Leer'; ?></span>
                                                    </div>
                                                    <div class="block-actions">
                                                        <button type="button" class="button button-small edit-block" title="Bearbeiten">✏️</button>
                                                        <button type="button" class="button button-small generate-block" title="Neu generieren">🔄</button>
                                                        <button type="button" class="button button-small remove-block" title="Entfernen">🗑️</button>
                                                        <span class="block-handle" title="Ziehen zum Sortieren">⋮⋮</span>
                                                    </div>
                                                </div>
                                                <div class="block-content" style="display: none;">
                                                    <textarea class="block-content-editor"><?php echo esc_textarea($block['content'] ?? ''); ?></textarea>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="no-blocks-message">
                                            <p>Ziehen Sie Blöcke aus der Bibliothek hierher, um Ihren Artikel zu strukturieren.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview Sektion -->
                <div class="preview-section">
                    <div class="postbox">
                        <div class="postbox-header">
                            <h2 class="hndle">Vorschau</h2>
                            <div class="handle-actions">
                                <button type="button" id="refresh-preview" class="button button-small">Aktualisieren</button>
                                <button type="button" id="toggle-preview" class="button button-small">Ein-/Ausblenden</button>
                            </div>
                        </div>
                        <div class="inside">
                            <div id="article-preview" class="article-preview-content">
                                <div class="preview-placeholder">
                                    <p>Die Vorschau wird angezeigt, sobald Sie Inhalte hinzufügen.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Modal -->
            <div id="export-modal" class="export-modal" style="display: none;">
                <div class="export-modal-content">
                    <div class="export-modal-header">
                        <h3>HTML Export</h3>
                        <button type="button" class="export-modal-close">&times;</button>
                    </div>
                    <div class="export-modal-body">
                        <textarea id="export-html-content" readonly></textarea>
                        <p class="export-instructions">
                            Der HTML-Code wurde generiert. Verwenden Sie die Schaltfläche unten, um ihn in die Zwischenablage zu kopieren.
                        </p>
                    </div>
                    <div class="export-modal-footer">
                        <button type="button" id="copy-html-button" class="button button-primary">In Zwischenablage kopieren</button>
                        <button type="button" class="button export-modal-close">Schließen</button>
                    </div>
                </div>
            </div>
        </div>

        <style>
        /* Inline CSS für bessere UX - wird später in separate CSS-Datei ausgelagert */
        .article-builder-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: auto 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        
        .metadata-section {
            grid-column: 1 / -1;
        }
        
        .blocks-section {
            grid-column: 1;
        }
        
        .preview-section {
            grid-column: 2;
        }
        
        .block-library-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .block-item {
            border: 2px dashed #ddd;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .block-item:hover {
            border-color: #0073aa;
            background-color: #f8f9fa;
        }
        
        .block-item-active {
            border: 1px solid #ddd;
            margin-bottom: 10px;
            background: #fff;
            border-radius: 4px;
        }
        
        .block-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            background: #f8f9fa;
            border-bottom: 1px solid #ddd;
        }
        
        .block-actions button {
            margin-left: 5px;
        }
        
        .blocks-sortable {
            min-height: 100px;
            border: 2px dashed #ddd;
            border-radius: 4px;
            padding: 15px;
        }
        
        .export-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 100000;
        }
        
        .export-modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 4px;
            width: 80%;
            max-width: 800px;
            max-height: 80%;
        }
        
        .export-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .export-modal-body {
            padding: 20px;
        }
        
        #export-html-content {
            width: 100%;
            height: 400px;
            font-family: monospace;
            font-size: 12px;
        }
        
        .export-modal-footer {
            padding: 20px;
            border-top: 1px solid #ddd;
            text-align: right;
        }
        </style>
        </noscript>
        </div><!-- .wrap -->
        <?php
    }

    /**
     * Projekte-Übersichtsseite
     */
    public function display_projects_page() {
        if (!current_user_can('edit_posts')) {
            wp_die(__('Sie haben keine Berechtigung für diese Aktion.'));
        }

        // Projekt löschen (falls angefordert)
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['project'])) {
            if (!wp_verify_nonce($_GET['_wpnonce'], 'delete_project_' . $_GET['project'])) {
                wp_die(__('Sicherheitsüberprüfung fehlgeschlagen.'));
            }
            
            $this->delete_project(intval($_GET['project']));
            wp_redirect(admin_url('admin.php?page=' . $this->plugin_slug . '-projects'));
            exit;
        }

        $projects = $this->get_user_projects();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Meine Projekte</h1>
            <a href="<?php echo admin_url('admin.php?page=' . $this->plugin_slug); ?>" class="page-title-action">Neues Projekt</a>
            <hr class="wp-header-end">

            <?php if (empty($projects)): ?>
                <div class="notice notice-info">
                    <p>Sie haben noch keine Projekte erstellt. <a href="<?php echo admin_url('admin.php?page=' . $this->plugin_slug); ?>">Erstellen Sie jetzt Ihr erstes Projekt!</a></p>
                </div>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th scope="col" class="manage-column column-title">Titel</th>
                            <th scope="col" class="manage-column column-type">Typ</th>
                            <th scope="col" class="manage-column column-blocks">Blöcke</th>
                            <th scope="col" class="manage-column column-date">Erstellt</th>
                            <th scope="col" class="manage-column column-date">Geändert</th>
                            <th scope="col" class="manage-column column-actions">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $project): ?>
                            <tr>
                                <td class="column-title">
                                    <strong>
                                        <a href="<?php echo admin_url('admin.php?page=' . $this->plugin_slug . '&project=' . $project->id); ?>">
                                            <?php echo esc_html($project->title ?: 'Unbenanntes Projekt'); ?>
                                        </a>
                                    </strong>
                                </td>
                                <td class="column-type">
                                    <?php echo esc_html($this->get_article_type_label($project->article_type)); ?>
                                </td>
                                <td class="column-blocks">
                                    <?php 
                                    $blocks = json_decode($project->blocks, true);
                                    echo count($blocks ?: []) . ' Blöcke';
                                    ?>
                                </td>
                                <td class="column-date">
                                    <?php echo esc_html(mysql2date('d.m.Y H:i', $project->created_at)); ?>
                                </td>
                                <td class="column-date">
                                    <?php echo esc_html(mysql2date('d.m.Y H:i', $project->updated_at)); ?>
                                </td>
                                <td class="column-actions">
                                    <a href="<?php echo admin_url('admin.php?page=' . $this->plugin_slug . '&project=' . $project->id); ?>" class="button button-small">Bearbeiten</a>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=' . $this->plugin_slug . '-projects&action=delete&project=' . $project->id), 'delete_project_' . $project->id); ?>" 
                                       class="button button-small button-link-delete" 
                                       onclick="return confirm('Sind Sie sicher, dass Sie dieses Projekt löschen möchten?')">Löschen</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Einstellungsseite
     */
    public function display_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Sie haben keine Berechtigung für diese Aktion.'));
        }

        // Einstellungen speichern
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['settings_nonce'], 'article_builder_settings')) {
            update_option('article_builder_openai_api_key', sanitize_text_field($_POST['openai_api_key']));
            update_option('article_builder_openai_model', sanitize_text_field($_POST['openai_model']));
            update_option('article_builder_max_tokens', intval($_POST['max_tokens']));
            update_option('article_builder_temperature', floatval($_POST['temperature']));
            update_option('article_builder_default_language', sanitize_text_field($_POST['default_language']));
            update_option('article_builder_enable_caching', isset($_POST['enable_caching']) ? 1 : 0);
            
            echo '<div class="notice notice-success"><p>Einstellungen gespeichert!</p></div>';
        }

        $settings = array(
            'openai_api_key' => get_option('article_builder_openai_api_key', ''),
            'openai_model' => get_option('article_builder_openai_model', 'gpt-4'),
            'max_tokens' => get_option('article_builder_max_tokens', 1500),
            'temperature' => get_option('article_builder_temperature', 0.7),
            'default_language' => get_option('article_builder_default_language', 'de'),
            'enable_caching' => get_option('article_builder_enable_caching', 1)
        );
        ?>
        <div class="wrap">
            <h1>Article Builder - Einstellungen</h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('article_builder_settings', 'settings_nonce'); ?>
                
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label for="openai_api_key">OpenAI API Key</label>
                        </th>
                        <td>
                            <input type="password" id="openai_api_key" name="openai_api_key" class="regular-text" 
                                   value="<?php echo esc_attr($settings['openai_api_key']); ?>" 
                                   placeholder="sk-...">
                            <p class="description">
                                Ihr OpenAI API-Schlüssel. Erhalten Sie ihn auf <a href="https://platform.openai.com/api-keys" target="_blank">platform.openai.com</a>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="openai_model">OpenAI Model</label>
                        </th>
                        <td>
                            <select id="openai_model" name="openai_model">
                                <option value="gpt-4" <?php selected($settings['openai_model'], 'gpt-4'); ?>>GPT-4 (Empfohlen)</option>
                                <option value="gpt-4-turbo" <?php selected($settings['openai_model'], 'gpt-4-turbo'); ?>>GPT-4 Turbo</option>
                                <option value="gpt-3.5-turbo" <?php selected($settings['openai_model'], 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo (Günstiger)</option>
                            </select>
                            <p class="description">Das zu verwendende KI-Modell. GPT-4 liefert bessere Ergebnisse, ist aber teurer.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="max_tokens">Max. Tokens pro Block</label>
                        </th>
                        <td>
                            <input type="number" id="max_tokens" name="max_tokens" class="small-text" 
                                   value="<?php echo esc_attr($settings['max_tokens']); ?>" 
                                   min="100" max="4000" step="50">
                            <p class="description">Maximale Anzahl Tokens (Wörter) pro generiertem Block. 1500 ist ein guter Standardwert.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="temperature">Kreativität (Temperature)</label>
                        </th>
                        <td>
                            <input type="number" id="temperature" name="temperature" class="small-text" 
                                   value="<?php echo esc_attr($settings['temperature']); ?>" 
                                   min="0" max="1" step="0.1">
                            <p class="description">0.0 = sehr konsistent, 1.0 = sehr kreativ. 0.7 ist ein guter Mittelwert.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="default_language">Standard-Sprache</label>
                        </th>
                        <td>
                            <select id="default_language" name="default_language">
                                <option value="de" <?php selected($settings['default_language'], 'de'); ?>>Deutsch</option>
                                <option value="en" <?php selected($settings['default_language'], 'en'); ?>>English</option>
                                <option value="fr" <?php selected($settings['default_language'], 'fr'); ?>>Français</option>
                                <option value="es" <?php selected($settings['default_language'], 'es'); ?>>Español</option>
                            </select>
                            <p class="description">Die Sprache für generierte Inhalte.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Caching aktivieren</th>
                        <td>
                            <fieldset>
                                <label for="enable_caching">
                                    <input type="checkbox" id="enable_caching" name="enable_caching" value="1" 
                                           <?php checked($settings['enable_caching'], 1); ?>>
                                    Generierte Inhalte zwischenspeichern
                                </label>
                                <p class="description">Reduziert API-Kosten durch Zwischenspeicherung identischer Anfragen.</p>
                            </fieldset>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button('Einstellungen speichern'); ?>
            </form>
            
            <hr>
            
            <h2>API-Status</h2>
            <div class="api-status-section">
                <p>
                    <strong>Status:</strong> 
                    <span id="api-status">
                        <?php echo !empty($settings['openai_api_key']) ? '✅ API-Key konfiguriert' : '❌ Kein API-Key'; ?>
                    </span>
                </p>
                <button type="button" id="test-api" class="button button-secondary" 
                        <?php echo empty($settings['openai_api_key']) ? 'disabled' : ''; ?>>
                    API-Verbindung testen
                </button>
                <div id="api-test-result"></div>
            </div>
            
            <hr>
            
            <h2>Verwendung & Kosten</h2>
            <div class="usage-stats">
                <p><strong>Hinweis:</strong> Die Kosten für die OpenAI API werden direkt von Ihrem OpenAI-Konto abgebucht. 
                Typical costs per article:</p>
                <ul>
                    <li>GPT-3.5 Turbo: ~$0.05-0.15 pro Artikel</li>
                    <li>GPT-4: ~$0.30-0.90 pro Artikel</li>
                    <li>GPT-4 Turbo: ~$0.15-0.45 pro Artikel</li>
                </ul>
            </div>
        </div>
        <?php
    }

    // AJAX-Methoden wurden in die separate AJAX-Klasse ausgelagert

    /**
     * Hilfsmethoden
     */

    private function get_user_projects() {
        $database = new Database();
        $tables = $database->get_table_names();
        
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$tables['projects']} WHERE author_id = %d ORDER BY updated_at DESC",
            get_current_user_id()
        ));
    }

    private function load_project($project_id) {
        $database = new Database();
        $project = $database->get_project($project_id);
        
        if (!$project || $project->author_id != get_current_user_id()) {
            return null;
        }

        return array(
            'id' => $project->id,
            'title' => $project->title,
            'type' => $project->post_type,
            'categories' => $project->categories ?: [],
            'tags' => $project->tags,
            'excerpt' => $project->excerpt,
            'blocks' => json_decode($project->blocks, true) ?: []
        );
    }

    private function save_project($project_id, $data) {
        $database = new Database();
        
        $project_data = array(
            'title' => $data['title'],
            'post_type' => $data['type'],
            'categories' => $data['categories'],
            'tags' => $data['tags'],
            'excerpt' => $data['excerpt'],
            'blocks' => json_encode($data['blocks']),
            'author_id' => get_current_user_id()
        );

        if ($project_id > 0) {
            $project_data['id'] = $project_id;
        }

        return $database->save_project($project_data);
    }

    private function delete_project($project_id) {
        $database = new Database();
        
        // Erst prüfen ob Projekt dem User gehört
        $project = $database->get_project($project_id);
        if (!$project || $project->author_id != get_current_user_id()) {
            return false;
        }
        
        return $database->delete_project($project_id);
    }

    private function sanitize_blocks($blocks) {
        $sanitized = array();
        
        foreach ($blocks as $block) {
            $sanitized[] = array(
                'type' => sanitize_text_field($block['type']),
                'content' => wp_kses_post($block['content']),
                'order' => intval($block['order'])
            );
        }
        
        return $sanitized;
    }

    private function get_block_title($block_type) {
        $titles = array(
            'introduction' => 'Einleitung',
            'market_analysis' => 'Markt-Analyse',
            'trading_strategy' => 'Trading-Strategie',
            'risk_management' => 'Risiko-Management',
            'conclusion' => 'Fazit',
            'cta' => 'Call-to-Action'
        );
        
        return $titles[$block_type] ?? ucfirst($block_type);
    }

    private function get_article_type_label($type) {
        $labels = array(
            'post' => 'Blog-Artikel',
            'page' => 'Seite',
            'analysis' => 'Markt-Analyse',
            'news' => 'News-Artikel',
            'guide' => 'Trading-Guide'
        );
        
        return $labels[$type] ?? ucfirst($type);
    }

    private function generate_html_export($project) {
        $html = '';
        
        // Header
        $html .= '<article class="article-builder-export">' . "\n";
        $html .= '<header>' . "\n";
        $html .= '<h1>' . esc_html($project['title']) . '</h1>' . "\n";
        if (!empty($project['excerpt'])) {
            $html .= '<div class="excerpt">' . wpautop($project['excerpt']) . '</div>' . "\n";
        }
        $html .= '</header>' . "\n\n";
        
        // Content blocks
        if (!empty($project['blocks'])) {
            foreach ($project['blocks'] as $block) {
                if (!empty($block['content'])) {
                    $html .= '<section class="block block-' . esc_attr($block['type']) . '">' . "\n";
                    $html .= wpautop($block['content']);
                    $html .= '</section>' . "\n\n";
                }
            }
        }
        
        $html .= '</article>';
        
        return $html;
    }
}