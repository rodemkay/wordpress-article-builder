<?php
namespace ArticleBuilder;

/**
 * AJAX Handler Class
 * 
 * Handles all AJAX requests from the admin interface
 * 
 * @package ArticleBuilder
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ajax {
    
    /**
     * Initialize AJAX hooks
     */
    public function __construct() {
        add_action('wp_ajax_ab_save_project', array($this, 'save_project'));
        add_action('wp_ajax_ab_load_project', array($this, 'load_project'));
        add_action('wp_ajax_ab_delete_project', array($this, 'delete_project'));
        add_action('wp_ajax_ab_duplicate_project', array($this, 'duplicate_project'));
        add_action('wp_ajax_ab_generate_preview', array($this, 'generate_preview'));
        add_action('wp_ajax_ab_get_preview_html', array($this, 'get_preview_html'));
        add_action('wp_ajax_ab_create_post', array($this, 'create_post'));
        add_action('wp_ajax_ab_get_categories', array($this, 'get_categories'));
        add_action('wp_ajax_ab_get_tags', array($this, 'get_tags'));
        add_action('wp_ajax_ab_create_category', array($this, 'create_category'));
        add_action('wp_ajax_ab_create_tag', array($this, 'create_tag'));
    }
    
    /**
     * Verify nonce and user permissions
     */
    private function verify_request($capability = 'edit_posts') {
        if (!check_ajax_referer('article_builder_nonce', 'nonce', false)) {
            wp_die(__('Sicherheitsprüfung fehlgeschlagen.', 'article-builder'));
        }
        
        if (!current_user_can($capability)) {
            wp_die(__('Unzureichende Berechtigung.', 'article-builder'));
        }
    }
    
    /**
     * Save project data
     */
    public function save_project() {
        $this->verify_request();
        
        $project_id = isset($_POST['project_id']) ? intval($_POST['project_id']) : 0;
        $project_name = isset($_POST['project_name']) ? sanitize_text_field($_POST['project_name']) : '';
        $project_data = isset($_POST['project_data']) ? wp_unslash($_POST['project_data']) : '';
        
        if (empty($project_name)) {
            wp_send_json_error(array('message' => __('Projektname ist erforderlich.', 'article-builder')));
        }
        
        // Validate JSON data
        $decoded_data = json_decode($project_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error(array('message' => __('Ungültiges Projekt-Format.', 'article-builder')));
        }
        
        $database = new Database();
        $tables = $database->get_table_names();
        
        $data = array(
            'title' => $project_name,
            'blocks' => $project_data,
            'author_id' => get_current_user_id()
        );
        
        if ($project_id > 0) {
            $data['id'] = $project_id;
        }
        
        $result = $database->save_project($data);
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Fehler beim Speichern des Projekts.', 'article-builder')));
        }
        
        $message = $project_id > 0 ? __('Projekt erfolgreich aktualisiert.', 'article-builder') : __('Projekt erfolgreich erstellt.', 'article-builder');
        
        wp_send_json_success(array(
            'message' => $message,
            'project_id' => $result
        ));
    }
    
    /**
     * Load project data
     */
    public function load_project() {
        $this->verify_request();
        
        $project_id = isset($_POST['project_id']) ? intval($_POST['project_id']) : 0;
        
        if ($project_id <= 0) {
            wp_send_json_error(array('message' => __('Ungültige Projekt-ID.', 'article-builder')));
        }
        
        $database = new Database();
        $tables = $database->get_table_names();
        
        $project = $database->get_project($project_id);
        
        if (!$project || $project->author_id != get_current_user_id()) {
            wp_send_json_error(array('message' => __('Projekt nicht gefunden.', 'article-builder')));
        }
        
        wp_send_json_success(array(
            'project' => array(
                'id' => $project->id,
                'name' => $project->title,
                'data' => json_decode($project->blocks, true),
                'created_at' => $project->created_at,
                'updated_at' => $project->updated_at
            )
        ));
    }
    
    /**
     * Delete project
     */
    public function delete_project() {
        $this->verify_request();
        
        $project_id = isset($_POST['project_id']) ? intval($_POST['project_id']) : 0;
        
        if ($project_id <= 0) {
            wp_send_json_error(array('message' => __('Ungültige Projekt-ID.', 'article-builder')));
        }
        
        $database = new Database();
        
        // Erst prüfen ob Projekt existiert und dem User gehört
        $project = $database->get_project($project_id);
        if (!$project || $project->author_id != get_current_user_id()) {
            wp_send_json_error(array('message' => __('Projekt nicht gefunden.', 'article-builder')));
        }
        
        $result = $database->delete_project($project_id);
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Fehler beim Löschen des Projekts.', 'article-builder')));
        }
        
        wp_send_json_success(array('message' => __('Projekt erfolgreich gelöscht.', 'article-builder')));
    }
    
    /**
     * Duplicate project
     */
    public function duplicate_project() {
        $this->verify_request();
        
        $project_id = isset($_POST['project_id']) ? intval($_POST['project_id']) : 0;
        
        if ($project_id <= 0) {
            wp_send_json_error(array('message' => __('Ungültige Projekt-ID.', 'article-builder')));
        }
        
        $database = new Database();
        
        $original_project = $database->get_project($project_id);
        
        if (!$original_project || $original_project->author_id != get_current_user_id()) {
            wp_send_json_error(array('message' => __('Originalprojekt nicht gefunden.', 'article-builder')));
        }
        
        $new_name = $original_project->title . ' (Kopie)';
        
        $new_project_data = array(
            'title' => $new_name,
            'content' => $original_project->content,
            'blocks' => $original_project->blocks,
            'excerpt' => $original_project->excerpt,
            'post_type' => $original_project->post_type,
            'categories' => $original_project->categories,
            'tags' => $original_project->tags,
            'author_id' => get_current_user_id()
        );
        
        $result = $database->save_project($new_project_data);
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Fehler beim Duplizieren des Projekts.', 'article-builder')));
        }
        
        wp_send_json_success(array(
            'message' => __('Projekt erfolgreich dupliziert.', 'article-builder'),
            'project_id' => $result,
            'project_name' => $new_name
        ));
    }
    
    /**
     * Generate preview HTML
     */
    public function generate_preview() {
        $this->verify_request();
        
        $project_data = isset($_POST['project_data']) ? wp_unslash($_POST['project_data']) : '';
        
        if (empty($project_data)) {
            wp_send_json_error(array('message' => __('Keine Projektdaten übertragen.', 'article-builder')));
        }
        
        $decoded_data = json_decode($project_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error(array('message' => __('Ungültiges Projekt-Format.', 'article-builder')));
        }
        
        // Generate HTML from project data
        $html = $this->render_project_html($decoded_data);
        
        wp_send_json_success(array('html' => $html));
    }
    
    /**
     * Get preview HTML for copy functionality
     */
    public function get_preview_html() {
        $this->verify_request();
        
        $project_data = isset($_POST['project_data']) ? wp_unslash($_POST['project_data']) : '';
        
        if (empty($project_data)) {
            wp_send_json_error(array('message' => __('Keine Projektdaten übertragen.', 'article-builder')));
        }
        
        $decoded_data = json_decode($project_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error(array('message' => __('Ungültiges Projekt-Format.', 'article-builder')));
        }
        
        // Generate clean HTML for copying
        $html = $this->render_project_html($decoded_data, true);
        
        wp_send_json_success(array('html' => $html));
    }
    
    /**
     * Create WordPress post from project
     */
    public function create_post() {
        $this->verify_request('publish_posts');
        
        $project_data = isset($_POST['project_data']) ? wp_unslash($_POST['project_data']) : '';
        $post_title = isset($_POST['post_title']) ? sanitize_text_field($_POST['post_title']) : '';
        $post_status = isset($_POST['post_status']) ? sanitize_text_field($_POST['post_status']) : 'draft';
        $categories = isset($_POST['categories']) ? array_map('intval', (array) $_POST['categories']) : array();
        $tags = isset($_POST['tags']) ? array_map('sanitize_text_field', (array) $_POST['tags']) : array();
        
        if (empty($project_data) || empty($post_title)) {
            wp_send_json_error(array('message' => __('Titel und Projektdaten sind erforderlich.', 'article-builder')));
        }
        
        $decoded_data = json_decode($project_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error(array('message' => __('Ungültiges Projekt-Format.', 'article-builder')));
        }
        
        // Generate content HTML
        $content = $this->render_project_html($decoded_data, true);
        
        // Create post
        $post_data = array(
            'post_title' => $post_title,
            'post_content' => $content,
            'post_status' => $post_status,
            'post_author' => get_current_user_id(),
            'post_type' => 'post'
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            wp_send_json_error(array('message' => __('Fehler beim Erstellen des Beitrags.', 'article-builder')));
        }
        
        // Set categories
        if (!empty($categories)) {
            wp_set_post_categories($post_id, $categories);
        }
        
        // Set tags
        if (!empty($tags)) {
            wp_set_post_tags($post_id, $tags);
        }
        
        wp_send_json_success(array(
            'message' => __('Beitrag erfolgreich erstellt.', 'article-builder'),
            'post_id' => $post_id,
            'edit_url' => admin_url('post.php?post=' . $post_id . '&action=edit'),
            'view_url' => get_permalink($post_id)
        ));
    }
    
    /**
     * Get WordPress categories
     */
    public function get_categories() {
        $this->verify_request();
        
        $categories = get_categories(array(
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC'
        ));
        
        $formatted_categories = array();
        foreach ($categories as $category) {
            $formatted_categories[] = array(
                'id' => $category->term_id,
                'name' => $category->name,
                'slug' => $category->slug,
                'count' => $category->count
            );
        }
        
        wp_send_json_success(array('categories' => $formatted_categories));
    }
    
    /**
     * Get WordPress tags
     */
    public function get_tags() {
        $this->verify_request();
        
        $tags = get_tags(array(
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC'
        ));
        
        $formatted_tags = array();
        foreach ($tags as $tag) {
            $formatted_tags[] = array(
                'id' => $tag->term_id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'count' => $tag->count
            );
        }
        
        wp_send_json_success(array('tags' => $formatted_tags));
    }
    
    /**
     * Create new category
     */
    public function create_category() {
        $this->verify_request('manage_categories');
        
        $category_name = isset($_POST['category_name']) ? sanitize_text_field($_POST['category_name']) : '';
        $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
        
        if (empty($category_name)) {
            wp_send_json_error(array('message' => __('Kategoriename ist erforderlich.', 'article-builder')));
        }
        
        $result = wp_insert_category(array(
            'cat_name' => $category_name,
            'category_parent' => $parent_id
        ));
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        $category = get_category($result);
        
        wp_send_json_success(array(
            'message' => __('Kategorie erfolgreich erstellt.', 'article-builder'),
            'category' => array(
                'id' => $category->term_id,
                'name' => $category->name,
                'slug' => $category->slug,
                'count' => $category->count
            )
        ));
    }
    
    /**
     * Create new tag
     */
    public function create_tag() {
        $this->verify_request('manage_categories');
        
        $tag_name = isset($_POST['tag_name']) ? sanitize_text_field($_POST['tag_name']) : '';
        
        if (empty($tag_name)) {
            wp_send_json_error(array('message' => __('Tag-Name ist erforderlich.', 'article-builder')));
        }
        
        $result = wp_insert_term($tag_name, 'post_tag');
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        $tag = get_tag($result['term_id']);
        
        wp_send_json_success(array(
            'message' => __('Tag erfolgreich erstellt.', 'article-builder'),
            'tag' => array(
                'id' => $tag->term_id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'count' => $tag->count
            )
        ));
    }
    
    /**
     * Render project data as HTML
     */
    private function render_project_html($project_data, $clean = false) {
        if (!isset($project_data['elements']) || !is_array($project_data['elements'])) {
            return '';
        }
        
        $html = '';
        
        foreach ($project_data['elements'] as $element) {
            $html .= $this->render_element($element, $clean);
        }
        
        return $html;
    }
    
    /**
     * Render individual element
     */
    private function render_element($element, $clean = false) {
        if (!isset($element['type'])) {
            return '';
        }
        
        $type = $element['type'];
        $content = isset($element['content']) ? $element['content'] : '';
        $attributes = isset($element['attributes']) ? $element['attributes'] : array();
        
        switch ($type) {
            case 'heading':
                $level = isset($attributes['level']) ? intval($attributes['level']) : 2;
                $level = max(1, min(6, $level)); // Ensure valid heading level
                return "<h{$level}>" . esc_html($content) . "</h{$level}>\n";
                
            case 'paragraph':
                return "<p>" . wp_kses_post($content) . "</p>\n";
                
            case 'list':
                $list_type = isset($attributes['ordered']) && $attributes['ordered'] ? 'ol' : 'ul';
                $items = isset($element['items']) ? $element['items'] : array();
                
                $html = "<{$list_type}>\n";
                foreach ($items as $item) {
                    $html .= "<li>" . wp_kses_post($item) . "</li>\n";
                }
                $html .= "</{$list_type}>\n";
                
                return $html;
                
            case 'image':
                $src = isset($attributes['src']) ? esc_url($attributes['src']) : '';
                $alt = isset($attributes['alt']) ? esc_attr($attributes['alt']) : '';
                $caption = isset($attributes['caption']) ? wp_kses_post($attributes['caption']) : '';
                
                if (empty($src)) {
                    return '';
                }
                
                $html = "<img src=\"{$src}\" alt=\"{$alt}\"";
                
                if (isset($attributes['width'])) {
                    $html .= " width=\"" . esc_attr($attributes['width']) . "\"";
                }
                if (isset($attributes['height'])) {
                    $html .= " height=\"" . esc_attr($attributes['height']) . "\"";
                }
                
                $html .= " />\n";
                
                if (!empty($caption)) {
                    $html = "<figure>{$html}<figcaption>{$caption}</figcaption></figure>\n";
                }
                
                return $html;
                
            case 'blockquote':
                $cite = isset($attributes['cite']) ? esc_attr($attributes['cite']) : '';
                $html = "<blockquote";
                if (!empty($cite)) {
                    $html .= " cite=\"{$cite}\"";
                }
                $html .= ">" . wp_kses_post($content) . "</blockquote>\n";
                return $html;
                
            case 'code':
                if (isset($attributes['block']) && $attributes['block']) {
                    $language = isset($attributes['language']) ? esc_attr($attributes['language']) : '';
                    $class = !empty($language) ? " class=\"language-{$language}\"" : '';
                    return "<pre><code{$class}>" . esc_html($content) . "</code></pre>\n";
                } else {
                    return "<code>" . esc_html($content) . "</code>";
                }
                
            case 'link':
                $url = isset($attributes['url']) ? esc_url($attributes['url']) : '';
                $target = isset($attributes['target']) ? esc_attr($attributes['target']) : '';
                
                if (empty($url)) {
                    return esc_html($content);
                }
                
                $html = "<a href=\"{$url}\"";
                if (!empty($target)) {
                    $html .= " target=\"{$target}\"";
                }
                $html .= ">" . esc_html($content) . "</a>";
                
                return $html;
                
            case 'divider':
                return "<hr />\n";
                
            default:
                return '';
        }
    }
}