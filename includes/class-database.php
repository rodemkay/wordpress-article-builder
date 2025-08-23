<?php
namespace ArticleBuilder;

/**
 * Database management for Article Builder Plugin
 *
 * @package ArticleBuilder
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Database {

    /**
     * Plugin version for database updates
     *
     * @var string
     */
    private $version = '1.0.0';

    /**
     * Table names
     *
     * @var array
     */
    private $tables = array();

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        
        $this->tables = array(
            'projects' => $wpdb->prefix . 'article_builder_projects',
            'templates' => $wpdb->prefix . 'article_builder_templates'
        );
    }

    /**
     * Create database tables on plugin activation
     *
     * @return bool Success status
     */
    public function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Projects table
        $sql_projects = "CREATE TABLE {$this->tables['projects']} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL DEFAULT '',
            slug varchar(200) NOT NULL DEFAULT '',
            content longtext NOT NULL,
            blocks longtext NOT NULL DEFAULT '',
            excerpt text NOT NULL DEFAULT '',
            status varchar(20) NOT NULL DEFAULT 'draft',
            post_type varchar(20) NOT NULL DEFAULT 'post',
            featured_image_id bigint(20) unsigned DEFAULT NULL,
            categories text DEFAULT NULL,
            tags text DEFAULT NULL,
            meta_title varchar(255) DEFAULT NULL,
            meta_description text DEFAULT NULL,
            meta_keywords text DEFAULT NULL,
            canonical_url varchar(255) DEFAULT NULL,
            social_title varchar(255) DEFAULT NULL,
            social_description text DEFAULT NULL,
            social_image_id bigint(20) unsigned DEFAULT NULL,
            custom_fields longtext DEFAULT NULL,
            author_id bigint(20) unsigned NOT NULL DEFAULT 1,
            scheduled_date datetime DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY status (status),
            KEY post_type (post_type),
            KEY author_id (author_id),
            KEY created_at (created_at),
            KEY scheduled_date (scheduled_date)
        ) $charset_collate;";

        // Templates table
        $sql_templates = "CREATE TABLE {$this->tables['templates']} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text DEFAULT NULL,
            category varchar(100) NOT NULL DEFAULT 'general',
            block_type varchar(100) NOT NULL,
            template_data longtext NOT NULL,
            is_default tinyint(1) NOT NULL DEFAULT 0,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            usage_count bigint(20) unsigned NOT NULL DEFAULT 0,
            created_by bigint(20) unsigned NOT NULL DEFAULT 1,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category (category),
            KEY block_type (block_type),
            KEY is_active (is_active),
            KEY created_by (created_by),
            KEY usage_count (usage_count)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $result1 = dbDelta($sql_projects);
        $result2 = dbDelta($sql_templates);

        // Update version option
        update_option('article_builder_db_version', $this->version);

        // Insert default templates
        $this->insert_default_templates();

        return !empty($result1) && !empty($result2);
    }

    /**
     * Insert default block templates
     *
     * @return bool Success status
     */
    public function insert_default_templates() {
        global $wpdb;

        $default_templates = array(
            array(
                'name' => 'Einfacher Titel-Block',
                'description' => 'Ein einfacher Titel-Block für Artikelüberschriften',
                'category' => 'content',
                'block_type' => 'title',
                'template_data' => json_encode(array(
                    'tag' => 'h2',
                    'style' => 'default',
                    'text' => 'Ihr Titel hier',
                    'css_class' => 'article-title'
                )),
                'is_default' => 1
            ),
            array(
                'name' => 'Absatz-Block',
                'description' => 'Standard Textblock für Fließtext',
                'category' => 'content',
                'block_type' => 'paragraph',
                'template_data' => json_encode(array(
                    'content' => 'Ihr Text hier...',
                    'style' => 'default',
                    'css_class' => 'article-paragraph'
                )),
                'is_default' => 1
            ),
            array(
                'name' => 'Bild-Block',
                'description' => 'Block für Bilder mit Beschreibung',
                'category' => 'media',
                'block_type' => 'image',
                'template_data' => json_encode(array(
                    'image_id' => null,
                    'alt_text' => '',
                    'caption' => '',
                    'alignment' => 'center',
                    'size' => 'large',
                    'css_class' => 'article-image'
                )),
                'is_default' => 1
            ),
            array(
                'name' => 'Listen-Block',
                'description' => 'Ungeordnete Liste für Aufzählungen',
                'category' => 'content',
                'block_type' => 'list',
                'template_data' => json_encode(array(
                    'list_type' => 'ul',
                    'items' => array('Punkt 1', 'Punkt 2', 'Punkt 3'),
                    'style' => 'default',
                    'css_class' => 'article-list'
                )),
                'is_default' => 1
            ),
            array(
                'name' => 'Quote-Block',
                'description' => 'Zitatblock für hervorgehobene Texte',
                'category' => 'content',
                'block_type' => 'quote',
                'template_data' => json_encode(array(
                    'quote_text' => 'Ihr Zitat hier...',
                    'author' => '',
                    'style' => 'default',
                    'css_class' => 'article-quote'
                )),
                'is_default' => 1
            ),
            array(
                'name' => 'Call-to-Action',
                'description' => 'Handlungsaufforderung mit Button',
                'category' => 'action',
                'block_type' => 'cta',
                'template_data' => json_encode(array(
                    'headline' => 'Jetzt handeln!',
                    'description' => 'Beschreibung der Aktion',
                    'button_text' => 'Klicken Sie hier',
                    'button_url' => '#',
                    'button_style' => 'primary',
                    'css_class' => 'article-cta'
                )),
                'is_default' => 1
            ),
            array(
                'name' => 'Code-Block',
                'description' => 'Codebeispiele mit Syntax-Highlighting',
                'category' => 'code',
                'block_type' => 'code',
                'template_data' => json_encode(array(
                    'code' => '// Ihr Code hier',
                    'language' => 'javascript',
                    'show_line_numbers' => true,
                    'css_class' => 'article-code'
                )),
                'is_default' => 1
            ),
            array(
                'name' => 'Tabellen-Block',
                'description' => 'Responsive Tabelle für strukturierte Daten',
                'category' => 'data',
                'block_type' => 'table',
                'template_data' => json_encode(array(
                    'headers' => array('Spalte 1', 'Spalte 2', 'Spalte 3'),
                    'rows' => array(
                        array('Zeile 1, Spalte 1', 'Zeile 1, Spalte 2', 'Zeile 1, Spalte 3'),
                        array('Zeile 2, Spalte 1', 'Zeile 2, Spalte 2', 'Zeile 2, Spalte 3')
                    ),
                    'style' => 'default',
                    'css_class' => 'article-table'
                )),
                'is_default' => 1
            )
        );

        foreach ($default_templates as $template) {
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$this->tables['templates']} WHERE name = %s AND is_default = 1",
                $template['name']
            ));

            if (!$existing) {
                $wpdb->insert($this->tables['templates'], $template);
            }
        }

        return true;
    }

    /**
     * Save or update an article project
     *
     * @param array $data Project data
     * @return int|false Project ID on success, false on failure
     */
    public function save_project($data) {
        global $wpdb;

        $defaults = array(
            'title' => '',
            'slug' => '',
            'content' => '',
            'blocks' => '',
            'excerpt' => '',
            'status' => 'draft',
            'post_type' => 'post',
            'featured_image_id' => null,
            'categories' => null,
            'tags' => null,
            'meta_title' => null,
            'meta_description' => null,
            'meta_keywords' => null,
            'canonical_url' => null,
            'social_title' => null,
            'social_description' => null,
            'social_image_id' => null,
            'custom_fields' => null,
            'author_id' => get_current_user_id(),
            'scheduled_date' => null
        );

        $data = wp_parse_args($data, $defaults);

        // Generate slug if empty
        if (empty($data['slug']) && !empty($data['title'])) {
            $data['slug'] = sanitize_title($data['title']);
        }

        // Ensure unique slug
        $data['slug'] = $this->get_unique_slug($data['slug'], isset($data['id']) ? $data['id'] : 0);

        // Serialize arrays and objects
        if (is_array($data['categories'])) {
            $data['categories'] = maybe_serialize($data['categories']);
        }
        if (is_array($data['tags'])) {
            $data['tags'] = maybe_serialize($data['tags']);
        }
        if (is_array($data['custom_fields'])) {
            $data['custom_fields'] = maybe_serialize($data['custom_fields']);
        }

        // Update existing project
        if (isset($data['id']) && $data['id'] > 0) {
            $id = $data['id'];
            unset($data['id']);
            
            $result = $wpdb->update(
                $this->tables['projects'],
                $data,
                array('id' => $id),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s'),
                array('%d')
            );

            return $result !== false ? $id : false;
        }

        // Insert new project
        $result = $wpdb->insert($this->tables['projects'], $data);
        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Get a project by ID
     *
     * @param int $project_id Project ID
     * @return object|null Project data or null if not found
     */
    public function get_project($project_id) {
        global $wpdb;

        $project = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->tables['projects']} WHERE id = %d",
            $project_id
        ));

        if ($project) {
            // Unserialize data
            $project->categories = maybe_unserialize($project->categories);
            $project->tags = maybe_unserialize($project->tags);
            $project->custom_fields = maybe_unserialize($project->custom_fields);
        }

        return $project;
    }

    /**
     * Delete a project
     *
     * @param int $project_id Project ID
     * @return bool Success status
     */
    public function delete_project($project_id) {
        global $wpdb;

        $result = $wpdb->delete(
            $this->tables['projects'],
            array('id' => $project_id),
            array('%d')
        );

        return $result !== false;
    }

    /**
     * Get all projects with optional filtering
     *
     * @param array $args Query arguments
     * @return array Projects list
     */
    public function get_all_projects($args = array()) {
        global $wpdb;

        $defaults = array(
            'status' => '',
            'post_type' => '',
            'author_id' => '',
            'limit' => 50,
            'offset' => 0,
            'orderby' => 'updated_at',
            'order' => 'DESC',
            'search' => ''
        );

        $args = wp_parse_args($args, $defaults);

        $where_conditions = array('1=1');
        $where_values = array();

        if (!empty($args['status'])) {
            $where_conditions[] = 'status = %s';
            $where_values[] = $args['status'];
        }

        if (!empty($args['post_type'])) {
            $where_conditions[] = 'post_type = %s';
            $where_values[] = $args['post_type'];
        }

        if (!empty($args['author_id'])) {
            $where_conditions[] = 'author_id = %d';
            $where_values[] = $args['author_id'];
        }

        if (!empty($args['search'])) {
            $where_conditions[] = '(title LIKE %s OR content LIKE %s)';
            $where_values[] = '%' . $wpdb->esc_like($args['search']) . '%';
            $where_values[] = '%' . $wpdb->esc_like($args['search']) . '%';
        }

        $where_clause = implode(' AND ', $where_conditions);

        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        if (!$orderby) {
            $orderby = 'updated_at DESC';
        }

        $limit_clause = '';
        if ($args['limit'] > 0) {
            $limit_clause = $wpdb->prepare(' LIMIT %d OFFSET %d', $args['limit'], $args['offset']);
        }

        $sql = "SELECT * FROM {$this->tables['projects']} WHERE {$where_clause} ORDER BY {$orderby}{$limit_clause}";

        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }

        $projects = $wpdb->get_results($sql);

        // Unserialize data for each project
        foreach ($projects as $project) {
            $project->categories = maybe_unserialize($project->categories);
            $project->tags = maybe_unserialize($project->tags);
            $project->custom_fields = maybe_unserialize($project->custom_fields);
        }

        return $projects;
    }

    /**
     * Get project count with optional filtering
     *
     * @param array $args Query arguments
     * @return int Project count
     */
    public function get_project_count($args = array()) {
        global $wpdb;

        $where_conditions = array('1=1');
        $where_values = array();

        if (!empty($args['status'])) {
            $where_conditions[] = 'status = %s';
            $where_values[] = $args['status'];
        }

        if (!empty($args['post_type'])) {
            $where_conditions[] = 'post_type = %s';
            $where_values[] = $args['post_type'];
        }

        if (!empty($args['author_id'])) {
            $where_conditions[] = 'author_id = %d';
            $where_values[] = $args['author_id'];
        }

        if (!empty($args['search'])) {
            $where_conditions[] = '(title LIKE %s OR content LIKE %s)';
            $where_values[] = '%' . $wpdb->esc_like($args['search']) . '%';
            $where_values[] = '%' . $wpdb->esc_like($args['search']) . '%';
        }

        $where_clause = implode(' AND ', $where_conditions);

        $sql = "SELECT COUNT(*) FROM {$this->tables['projects']} WHERE {$where_clause}";

        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }

        return (int) $wpdb->get_var($sql);
    }

    /**
     * Get template by ID
     *
     * @param int $template_id Template ID
     * @return object|null Template data or null if not found
     */
    public function get_template($template_id) {
        global $wpdb;

        $template = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->tables['templates']} WHERE id = %d AND is_active = 1",
            $template_id
        ));

        if ($template) {
            $template->template_data = json_decode($template->template_data, true);
        }

        return $template;
    }

    /**
     * Get templates by category or type
     *
     * @param array $args Query arguments
     * @return array Templates list
     */
    public function get_templates($args = array()) {
        global $wpdb;

        $defaults = array(
            'category' => '',
            'block_type' => '',
            'is_active' => 1,
            'orderby' => 'name',
            'order' => 'ASC'
        );

        $args = wp_parse_args($args, $defaults);

        $where_conditions = array('is_active = %d');
        $where_values = array($args['is_active']);

        if (!empty($args['category'])) {
            $where_conditions[] = 'category = %s';
            $where_values[] = $args['category'];
        }

        if (!empty($args['block_type'])) {
            $where_conditions[] = 'block_type = %s';
            $where_values[] = $args['block_type'];
        }

        $where_clause = implode(' AND ', $where_conditions);

        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        if (!$orderby) {
            $orderby = 'name ASC';
        }

        $sql = "SELECT * FROM {$this->tables['templates']} WHERE {$where_clause} ORDER BY {$orderby}";
        $sql = $wpdb->prepare($sql, $where_values);

        $templates = $wpdb->get_results($sql);

        // Decode template data
        foreach ($templates as $template) {
            $template->template_data = json_decode($template->template_data, true);
        }

        return $templates;
    }

    /**
     * Save template
     *
     * @param array $data Template data
     * @return int|false Template ID on success, false on failure
     */
    public function save_template($data) {
        global $wpdb;

        $defaults = array(
            'name' => '',
            'description' => '',
            'category' => 'general',
            'block_type' => '',
            'template_data' => '',
            'is_default' => 0,
            'is_active' => 1,
            'usage_count' => 0,
            'created_by' => get_current_user_id()
        );

        $data = wp_parse_args($data, $defaults);

        // Encode template data
        if (is_array($data['template_data'])) {
            $data['template_data'] = json_encode($data['template_data']);
        }

        // Update existing template
        if (isset($data['id']) && $data['id'] > 0) {
            $id = $data['id'];
            unset($data['id']);
            
            $result = $wpdb->update(
                $this->tables['templates'],
                $data,
                array('id' => $id),
                array('%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d'),
                array('%d')
            );

            return $result !== false ? $id : false;
        }

        // Insert new template
        $result = $wpdb->insert($this->tables['templates'], $data);
        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Increment template usage count
     *
     * @param int $template_id Template ID
     * @return bool Success status
     */
    public function increment_template_usage($template_id) {
        global $wpdb;

        $result = $wpdb->query($wpdb->prepare(
            "UPDATE {$this->tables['templates']} SET usage_count = usage_count + 1 WHERE id = %d",
            $template_id
        ));

        return $result !== false;
    }

    /**
     * Generate unique slug
     *
     * @param string $slug Base slug
     * @param int $exclude_id ID to exclude from uniqueness check
     * @return string Unique slug
     */
    private function get_unique_slug($slug, $exclude_id = 0) {
        global $wpdb;

        $original_slug = $slug;
        $counter = 1;

        while (true) {
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$this->tables['projects']} WHERE slug = %s AND id != %d",
                $slug,
                $exclude_id
            ));

            if (!$existing) {
                break;
            }

            $slug = $original_slug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Drop tables (for plugin uninstall)
     *
     * @return bool Success status
     */
    public function drop_tables() {
        global $wpdb;

        $sql1 = "DROP TABLE IF EXISTS {$this->tables['projects']}";
        $sql2 = "DROP TABLE IF EXISTS {$this->tables['templates']}";

        $result1 = $wpdb->query($sql1);
        $result2 = $wpdb->query($sql2);

        // Remove version option
        delete_option('article_builder_db_version');

        return $result1 !== false && $result2 !== false;
    }

    /**
     * Get table names
     *
     * @return array Table names
     */
    public function get_table_names() {
        return $this->tables;
    }
}