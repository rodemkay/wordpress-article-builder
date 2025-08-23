<?php
namespace ArticleBuilder;

/**
 * REST API Class
 * 
 * Provides REST API endpoints for modern JavaScript integration
 * 
 * @package ArticleBuilder
 */

if (!defined('ABSPATH')) {
    exit;
}

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class API extends WP_REST_Controller {
    
    /**
     * API namespace
     */
    const NAMESPACE = 'article-builder/v1';
    
    /**
     * Initialize REST API routes
     */
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    /**
     * Register all REST API routes
     */
    public function register_routes() {
        // Projects endpoints
        register_rest_route(self::NAMESPACE, '/projects', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_projects'),
                'permission_callback' => array($this, 'check_permissions')
            ),
            array(
                'methods' => 'POST',
                'callback' => array($this, 'create_project'),
                'permission_callback' => array($this, 'check_permissions'),
                'args' => array(
                    'name' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field'
                    ),
                    'data' => array(
                        'required' => true,
                        'type' => 'object'
                    )
                )
            )
        ));
        
        register_rest_route(self::NAMESPACE, '/projects/(?P<id>\d+)', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_project'),
                'permission_callback' => array($this, 'check_permissions'),
                'args' => array(
                    'id' => array(
                        'validate_callback' => function($param) {
                            return is_numeric($param);
                        }
                    )
                )
            ),
            array(
                'methods' => 'PUT',
                'callback' => array($this, 'update_project'),
                'permission_callback' => array($this, 'check_permissions'),
                'args' => array(
                    'id' => array(
                        'validate_callback' => function($param) {
                            return is_numeric($param);
                        }
                    ),
                    'name' => array(
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field'
                    ),
                    'data' => array(
                        'type' => 'object'
                    )
                )
            ),
            array(
                'methods' => 'DELETE',
                'callback' => array($this, 'delete_project'),
                'permission_callback' => array($this, 'check_permissions'),
                'args' => array(
                    'id' => array(
                        'validate_callback' => function($param) {
                            return is_numeric($param);
                        }
                    )
                )
            )
        ));
        
        // Project duplication
        register_rest_route(self::NAMESPACE, '/projects/(?P<id>\d+)/duplicate', array(
            'methods' => 'POST',
            'callback' => array($this, 'duplicate_project'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ),
                'name' => array(
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));
        
        // Project export
        register_rest_route(self::NAMESPACE, '/projects/(?P<id>\d+)/export', array(
            'methods' => 'GET',
            'callback' => array($this, 'export_project'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ),
                'format' => array(
                    'default' => 'json',
                    'enum' => array('json', 'html'),
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));
        
        // Templates endpoints
        register_rest_route(self::NAMESPACE, '/templates', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_templates'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        register_rest_route(self::NAMESPACE, '/templates/(?P<name>[a-zA-Z0-9_-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_template'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'name' => array(
                    'validate_callback' => function($param) {
                        return preg_match('/^[a-zA-Z0-9_-]+$/', $param);
                    }
                )
            )
        ));
        
        // WordPress integration endpoints
        register_rest_route(self::NAMESPACE, '/wordpress/categories', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_wp_categories'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        register_rest_route(self::NAMESPACE, '/wordpress/tags', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_wp_tags'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        register_rest_route(self::NAMESPACE, '/wordpress/posts', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_wp_post'),
            'permission_callback' => array($this, 'check_publish_permissions'),
            'args' => array(
                'title' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'content' => array(
                    'required' => true,
                    'type' => 'string'
                ),
                'status' => array(
                    'default' => 'draft',
                    'enum' => array('draft', 'publish', 'private'),
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'categories' => array(
                    'type' => 'array',
                    'items' => array('type' => 'integer')
                ),
                'tags' => array(
                    'type' => 'array',
                    'items' => array('type' => 'string')
                )
            )
        ));
        
        // Preview endpoint
        register_rest_route(self::NAMESPACE, '/preview', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_preview'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'data' => array(
                    'required' => true,
                    'type' => 'object'
                ),
                'format' => array(
                    'default' => 'html',
                    'enum' => array('html', 'clean'),
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));
        
        // Import endpoint
        register_rest_route(self::NAMESPACE, '/import', array(
            'methods' => 'POST',
            'callback' => array($this, 'import_project'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'data' => array(
                    'required' => true,
                    'type' => 'object'
                ),
                'name' => array(
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));
    }
    
    /**
     * Check user permissions
     */
    public function check_permissions() {
        return current_user_can('edit_posts');
    }
    
    /**
     * Check publish permissions
     */
    public function check_publish_permissions() {
        return current_user_can('publish_posts');
    }
    
    /**
     * Get all projects for current user
     */
    public function get_projects($request) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'article_builder_projects';
        
        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 20;
        $search = $request->get_param('search');
        
        $offset = ($page - 1) * $per_page;
        
        $where_clause = "WHERE user_id = %d";
        $params = array(get_current_user_id());
        
        if ($search) {
            $where_clause .= " AND name LIKE %s";
            $params[] = '%' . $wpdb->esc_like($search) . '%';
        }
        
        $query = "SELECT * FROM {$table_name} {$where_clause} ORDER BY updated_at DESC LIMIT %d OFFSET %d";
        $params[] = $per_page;
        $params[] = $offset;
        
        $projects = $wpdb->get_results($wpdb->prepare($query, $params));
        
        // Get total count
        $count_query = "SELECT COUNT(*) FROM {$table_name} {$where_clause}";
        $total = $wpdb->get_var($wpdb->prepare($count_query, array_slice($params, 0, -2)));
        
        $formatted_projects = array();
        foreach ($projects as $project) {
            $formatted_projects[] = array(
                'id' => (int) $project->id,
                'name' => $project->name,
                'data' => json_decode($project->data, true),
                'created_at' => $project->created_at,
                'updated_at' => $project->updated_at
            );
        }
        
        return new WP_REST_Response(array(
            'projects' => $formatted_projects,
            'total' => (int) $total,
            'pages' => ceil($total / $per_page),
            'current_page' => (int) $page
        ), 200);
    }
    
    /**
     * Get single project
     */
    public function get_project($request) {
        $id = $request->get_param('id');
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'article_builder_projects';
        
        $project = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d AND user_id = %d",
            $id,
            get_current_user_id()
        ));
        
        if (!$project) {
            return new WP_Error('project_not_found', __('Projekt nicht gefunden.', 'article-builder'), array('status' => 404));
        }
        
        return new WP_REST_Response(array(
            'id' => (int) $project->id,
            'name' => $project->name,
            'data' => json_decode($project->data, true),
            'created_at' => $project->created_at,
            'updated_at' => $project->updated_at
        ), 200);
    }
    
    /**
     * Create new project
     */
    public function create_project($request) {
        $name = $request->get_param('name');
        $data = $request->get_param('data');
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'article_builder_projects';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'name' => $name,
                'data' => wp_json_encode($data),
                'user_id' => get_current_user_id(),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%s', '%s', '%d', '%s', '%s')
        );
        
        if ($result === false) {
            return new WP_Error('create_failed', __('Fehler beim Erstellen des Projekts.', 'article-builder'), array('status' => 500));
        }
        
        return new WP_REST_Response(array(
            'id' => $wpdb->insert_id,
            'name' => $name,
            'data' => $data,
            'message' => __('Projekt erfolgreich erstellt.', 'article-builder')
        ), 201);
    }
    
    /**
     * Update project
     */
    public function update_project($request) {
        $id = $request->get_param('id');
        $name = $request->get_param('name');
        $data = $request->get_param('data');
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'article_builder_projects';
        
        // Check if project exists and belongs to user
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE id = %d AND user_id = %d",
            $id,
            get_current_user_id()
        ));
        
        if (!$existing) {
            return new WP_Error('project_not_found', __('Projekt nicht gefunden.', 'article-builder'), array('status' => 404));
        }
        
        $update_data = array('updated_at' => current_time('mysql'));
        $format = array('%s');
        
        if ($name !== null) {
            $update_data['name'] = $name;
            $format[] = '%s';
        }
        
        if ($data !== null) {
            $update_data['data'] = wp_json_encode($data);
            $format[] = '%s';
        }
        
        $result = $wpdb->update(
            $table_name,
            $update_data,
            array('id' => $id),
            $format,
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('update_failed', __('Fehler beim Aktualisieren des Projekts.', 'article-builder'), array('status' => 500));
        }
        
        return new WP_REST_Response(array(
            'message' => __('Projekt erfolgreich aktualisiert.', 'article-builder')
        ), 200);
    }
    
    /**
     * Delete project
     */
    public function delete_project($request) {
        $id = $request->get_param('id');
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'article_builder_projects';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $id, 'user_id' => get_current_user_id()),
            array('%d', '%d')
        );
        
        if ($result === false || $result === 0) {
            return new WP_Error('delete_failed', __('Projekt nicht gefunden oder Löschen fehlgeschlagen.', 'article-builder'), array('status' => 404));
        }
        
        return new WP_REST_Response(array(
            'message' => __('Projekt erfolgreich gelöscht.', 'article-builder')
        ), 200);
    }
    
    /**
     * Duplicate project
     */
    public function duplicate_project($request) {
        $id = $request->get_param('id');
        $new_name = $request->get_param('name');
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'article_builder_projects';
        
        $original = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d AND user_id = %d",
            $id,
            get_current_user_id()
        ));
        
        if (!$original) {
            return new WP_Error('project_not_found', __('Originalprojekt nicht gefunden.', 'article-builder'), array('status' => 404));
        }
        
        if (!$new_name) {
            $new_name = $original->name . ' (Kopie)';
        }
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'name' => $new_name,
                'data' => $original->data,
                'user_id' => get_current_user_id(),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%s', '%s', '%d', '%s', '%s')
        );
        
        if ($result === false) {
            return new WP_Error('duplicate_failed', __('Fehler beim Duplizieren des Projekts.', 'article-builder'), array('status' => 500));
        }
        
        return new WP_REST_Response(array(
            'id' => $wpdb->insert_id,
            'name' => $new_name,
            'message' => __('Projekt erfolgreich dupliziert.', 'article-builder')
        ), 201);
    }
    
    /**
     * Export project
     */
    public function export_project($request) {
        $id = $request->get_param('id');
        $format = $request->get_param('format');
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'article_builder_projects';
        
        $project = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d AND user_id = %d",
            $id,
            get_current_user_id()
        ));
        
        if (!$project) {
            return new WP_Error('project_not_found', __('Projekt nicht gefunden.', 'article-builder'), array('status' => 404));
        }
        
        if ($format === 'html') {
            $data = json_decode($project->data, true);
            $html = $this->render_project_html($data);
            
            return new WP_REST_Response(array(
                'format' => 'html',
                'content' => $html,
                'filename' => sanitize_file_name($project->name) . '.html'
            ), 200);
        } else {
            // JSON format
            return new WP_REST_Response(array(
                'format' => 'json',
                'content' => array(
                    'name' => $project->name,
                    'data' => json_decode($project->data, true),
                    'exported_at' => current_time('mysql')
                ),
                'filename' => sanitize_file_name($project->name) . '.json'
            ), 200);
        }
    }
    
    /**
     * Get available templates
     */
    public function get_templates($request) {
        $templates_dir = ARTICLE_BUILDER_PLUGIN_DIR . 'templates/';
        $templates = array();
        
        if (is_dir($templates_dir)) {
            $files = glob($templates_dir . '*.json');
            
            foreach ($files as $file) {
                $filename = basename($file, '.json');
                $template_data = json_decode(file_get_contents($file), true);
                
                if ($template_data) {
                    $templates[] = array(
                        'name' => $filename,
                        'title' => isset($template_data['title']) ? $template_data['title'] : ucfirst(str_replace('-', ' ', $filename)),
                        'description' => isset($template_data['description']) ? $template_data['description'] : '',
                        'category' => isset($template_data['category']) ? $template_data['category'] : 'general'
                    );
                }
            }
        }
        
        return new WP_REST_Response(array('templates' => $templates), 200);
    }
    
    /**
     * Get specific template
     */
    public function get_template($request) {
        $name = $request->get_param('name');
        $template_file = ARTICLE_BUILDER_PLUGIN_DIR . 'templates/' . $name . '.json';
        
        if (!file_exists($template_file)) {
            return new WP_Error('template_not_found', __('Template nicht gefunden.', 'article-builder'), array('status' => 404));
        }
        
        $template_data = json_decode(file_get_contents($template_file), true);
        
        if (!$template_data) {
            return new WP_Error('template_invalid', __('Ungültiges Template-Format.', 'article-builder'), array('status' => 400));
        }
        
        return new WP_REST_Response($template_data, 200);
    }
    
    /**
     * Get WordPress categories
     */
    public function get_wp_categories($request) {
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
                'count' => $category->count,
                'parent' => $category->parent
            );
        }
        
        return new WP_REST_Response(array('categories' => $formatted_categories), 200);
    }
    
    /**
     * Get WordPress tags
     */
    public function get_wp_tags($request) {
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
        
        return new WP_REST_Response(array('tags' => $formatted_tags), 200);
    }
    
    /**
     * Create WordPress post
     */
    public function create_wp_post($request) {
        $title = $request->get_param('title');
        $content = $request->get_param('content');
        $status = $request->get_param('status');
        $categories = $request->get_param('categories') ?: array();
        $tags = $request->get_param('tags') ?: array();
        
        $post_data = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => $status,
            'post_author' => get_current_user_id(),
            'post_type' => 'post'
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return new WP_Error('post_creation_failed', $post_id->get_error_message(), array('status' => 500));
        }
        
        // Set categories
        if (!empty($categories)) {
            wp_set_post_categories($post_id, $categories);
        }
        
        // Set tags
        if (!empty($tags)) {
            wp_set_post_tags($post_id, $tags);
        }
        
        return new WP_REST_Response(array(
            'id' => $post_id,
            'edit_url' => admin_url('post.php?post=' . $post_id . '&action=edit'),
            'view_url' => get_permalink($post_id),
            'message' => __('Beitrag erfolgreich erstellt.', 'article-builder')
        ), 201);
    }
    
    /**
     * Generate preview
     */
    public function generate_preview($request) {
        $data = $request->get_param('data');
        $format = $request->get_param('format');
        
        $html = $this->render_project_html($data, $format === 'clean');
        
        return new WP_REST_Response(array(
            'html' => $html,
            'format' => $format
        ), 200);
    }
    
    /**
     * Import project
     */
    public function import_project($request) {
        $data = $request->get_param('data');
        $name = $request->get_param('name') ?: 'Importiertes Projekt ' . date('Y-m-d H:i:s');
        
        // Validate imported data structure
        if (!isset($data['elements']) || !is_array($data['elements'])) {
            return new WP_Error('invalid_import', __('Ungültige Importdaten.', 'article-builder'), array('status' => 400));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'article_builder_projects';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'name' => $name,
                'data' => wp_json_encode($data),
                'user_id' => get_current_user_id(),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%s', '%s', '%d', '%s', '%s')
        );
        
        if ($result === false) {
            return new WP_Error('import_failed', __('Fehler beim Importieren des Projekts.', 'article-builder'), array('status' => 500));
        }
        
        return new WP_REST_Response(array(
            'id' => $wpdb->insert_id,
            'name' => $name,
            'message' => __('Projekt erfolgreich importiert.', 'article-builder')
        ), 201);
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
                $level = max(1, min(6, $level));
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