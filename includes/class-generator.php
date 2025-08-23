<?php
namespace ArticleBuilder;

/**
 * Article Builder - HTML Generator
 * Processes blocks and generates final HTML output
 */

if (!defined('ABSPATH')) {
    exit;
}

class Generator {
    
    private $blocks_class;
    private $template_cache = array();
    
    public function __construct() {
        $this->blocks_class = new Blocks();
    }
    
    /**
     * Generate complete article HTML from blocks array
     */
    public function generate_article($blocks_data, $options = array()) {
        $defaults = array(
            'container_class' => 'article-builder-content',
            'wrap_sections' => true,
            'include_css' => true,
            'responsive' => true
        );
        
        $options = array_merge($defaults, $options);
        
        $html = '';
        
        if ($options['include_css']) {
            $html .= $this->get_base_css();
        }
        
        if ($options['wrap_sections']) {
            $html .= '<div class="' . esc_attr($options['container_class']) . '">';
        }
        
        foreach ($blocks_data as $block) {
            if (!isset($block['category'], $block['type'], $block['data'])) {
                continue;
            }
            
            $block_html = $this->generate_block($block['category'], $block['type'], $block['data']);
            if ($block_html) {
                $html .= $block_html;
            }
        }
        
        if ($options['wrap_sections']) {
            $html .= '</div>';
        }
        
        return $html;
    }
    
    /**
     * Generate single block HTML
     */
    public function generate_block($category, $block_type, $data) {
        $block_config = $this->blocks_class->get_block($category, $block_type);
        if (!$block_config) {
            return '';
        }
        
        // Merge with defaults
        $defaults = $this->blocks_class->get_block_defaults($category, $block_type);
        $data = array_merge($defaults, $data);
        
        // Validate data
        $validation = $this->blocks_class->validate_block_data($category, $block_type, $data);
        if (!$validation['valid']) {
            return '<!-- Block validation failed: ' . implode(', ', $validation['errors']) . ' -->';
        }
        
        // Get template
        $template = $this->get_template($block_config['template']);
        if (!$template) {
            return '<!-- Template not found: ' . $block_config['template'] . ' -->';
        }
        
        // Process template
        return $this->process_template($template, $data, $block_config);
    }
    
    /**
     * Get template content
     */
    private function get_template($template_name) {
        if (isset($this->template_cache[$template_name])) {
            return $this->template_cache[$template_name];
        }
        
        $template = $this->get_builtin_template($template_name);
        $this->template_cache[$template_name] = $template;
        
        return $template;
    }
    
    /**
     * Get built-in template
     */
    private function get_builtin_template($template_name) {
        switch ($template_name) {
            
            // HERO TEMPLATES
            case 'hero-classic':
                return '
                <section class="wsj-hero wsj-hero-classic">
                    <div class="wsj-hero-container">
                        {{#category}}<div class="wsj-category-badge">{{category}}</div>{{/category}}
                        <h1 class="wsj-hero-headline">{{headline}}</h1>
                        {{#subtitle}}<p class="wsj-hero-subtitle">{{subtitle}}</p>{{/subtitle}}
                        {{#image_url}}
                        <div class="wsj-hero-image">
                            {{#link_url}}<a href="{{link_url}}">{{/link_url}}
                            <img src="{{image_url}}" alt="{{headline}}" />
                            {{#link_url}}</a>{{/link_url}}
                        </div>
                        {{/image_url}}
                        {{#author}}<div class="wsj-hero-meta">
                            <span class="wsj-author">{{author}}</span>
                            {{#date}}<span class="wsj-date">{{date}}</span>{{/date}}
                        </div>{{/author}}
                    </div>
                </section>';
            
            case 'hero-image-overlay':
                return '
                <section class="wsj-hero wsj-hero-overlay" style="background-image: url({{background_image}}); color: {{text_color}};">
                    <div class="wsj-hero-overlay-bg" style="opacity: {{overlay_opacity_decimal}}"></div>
                    <div class="wsj-hero-container">
                        <h1 class="wsj-hero-headline">{{headline}}</h1>
                        {{#subtitle}}<p class="wsj-hero-subtitle">{{subtitle}}</p>{{/subtitle}}
                        {{#cta_text}}
                        <div class="wsj-hero-cta">
                            <a href="{{link_url}}" class="wsj-btn wsj-btn-primary">{{cta_text}}</a>
                        </div>
                        {{/cta_text}}
                    </div>
                </section>';
            
            case 'hero-split':
                return '
                <section class="wsj-hero wsj-hero-split">
                    <div class="wsj-hero-container">
                        <div class="wsj-hero-content">
                            <h1 class="wsj-hero-headline">{{headline}}</h1>
                            {{#subtitle}}<p class="wsj-hero-subtitle">{{subtitle}}</p>{{/subtitle}}
                            {{#description}}<div class="wsj-hero-description">{{description}}</div>{{/description}}
                            <div class="wsj-hero-cta">
                                {{#cta_primary}}<a href="{{cta_primary_url}}" class="wsj-btn wsj-btn-primary">{{cta_primary}}</a>{{/cta_primary}}
                                {{#cta_secondary}}<a href="{{cta_secondary_url}}" class="wsj-btn wsj-btn-secondary">{{cta_secondary}}</a>{{/cta_secondary}}
                            </div>
                        </div>
                        <div class="wsj-hero-image">
                            <img src="{{image_url}}" alt="{{image_alt}}" />
                        </div>
                    </div>
                </section>';
            
            case 'hero-video':
                return '
                <section class="wsj-hero wsj-hero-video">
                    <video {{#autoplay}}autoplay{{/autoplay}} {{#muted}}muted{{/muted}} loop {{#video_poster}}poster="{{video_poster}}"{{/video_poster}}>
                        <source src="{{video_url}}" type="video/mp4">
                    </video>
                    <div class="wsj-hero-overlay" style="background-color: {{overlay_color}}; opacity: {{overlay_opacity_decimal}}"></div>
                    <div class="wsj-hero-container">
                        <h1 class="wsj-hero-headline">{{headline}}</h1>
                        {{#subtitle}}<p class="wsj-hero-subtitle">{{subtitle}}</p>{{/subtitle}}
                    </div>
                </section>';
            
            case 'hero-minimal':
                return '
                <section class="wsj-hero wsj-hero-minimal" style="background-color: {{background_color}}; color: {{text_color}}; text-align: {{text_align}}; padding: {{padding_top}}px 0 {{padding_bottom}}px;">
                    <div class="wsj-hero-container">
                        <h1 class="wsj-hero-headline">{{headline}}</h1>
                        {{#subtitle}}<p class="wsj-hero-subtitle">{{subtitle}}</p>{{/subtitle}}
                    </div>
                </section>';
            
            case 'hero-stats':
                return '
                <section class="wsj-hero wsj-hero-stats {{#background_gradient}}wsj-gradient{{/background_gradient}}">
                    <div class="wsj-hero-container">
                        <h1 class="wsj-hero-headline">{{headline}}</h1>
                        {{#subtitle}}<p class="wsj-hero-subtitle">{{subtitle}}</p>{{/subtitle}}
                        <div class="wsj-stats-grid">
                            {{#stat_1_number}}<div class="wsj-stat"><span class="wsj-stat-number">{{stat_1_number}}</span><span class="wsj-stat-label">{{stat_1_label}}</span></div>{{/stat_1_number}}
                            {{#stat_2_number}}<div class="wsj-stat"><span class="wsj-stat-number">{{stat_2_number}}</span><span class="wsj-stat-label">{{stat_2_label}}</span></div>{{/stat_2_number}}
                            {{#stat_3_number}}<div class="wsj-stat"><span class="wsj-stat-number">{{stat_3_number}}</span><span class="wsj-stat-label">{{stat_3_label}}</span></div>{{/stat_3_number}}
                            {{#stat_4_number}}<div class="wsj-stat"><span class="wsj-stat-number">{{stat_4_number}}</span><span class="wsj-stat-label">{{stat_4_label}}</span></div>{{/stat_4_number}}
                        </div>
                    </div>
                </section>';
            
            case 'hero-countdown':
                return '
                <section class="wsj-hero wsj-hero-countdown" style="background-color: {{background_color}}; color: {{text_color}};">
                    <div class="wsj-hero-container">
                        <h1 class="wsj-hero-headline">{{headline}}</h1>
                        {{#subtitle}}<p class="wsj-hero-subtitle">{{subtitle}}</p>{{/subtitle}}
                        <div class="wsj-countdown" data-countdown="{{countdown_date}}" data-text="{{countdown_text}}" data-expired="{{expired_text}}">
                            <div class="wsj-countdown-timer">
                                <div class="wsj-countdown-item"><span class="wsj-countdown-number" id="days">00</span><span class="wsj-countdown-label">Tage</span></div>
                                <div class="wsj-countdown-item"><span class="wsj-countdown-number" id="hours">00</span><span class="wsj-countdown-label">Stunden</span></div>
                                <div class="wsj-countdown-item"><span class="wsj-countdown-number" id="minutes">00</span><span class="wsj-countdown-label">Minuten</span></div>
                                <div class="wsj-countdown-item"><span class="wsj-countdown-number" id="seconds">00</span><span class="wsj-countdown-label">Sekunden</span></div>
                            </div>
                        </div>
                    </div>
                </section>';
            
            case 'hero-testimonial':
                return '
                <section class="wsj-hero wsj-hero-testimonial" {{#background_image}}style="background-image: url({{background_image}})"{{/background_image}}>
                    <div class="wsj-hero-container">
                        <h1 class="wsj-hero-headline">{{headline}}</h1>
                        <div class="wsj-testimonial">
                            <blockquote class="wsj-testimonial-text">"{{testimonial_text}}"</blockquote>
                            <div class="wsj-testimonial-author">
                                {{#customer_image}}<img src="{{customer_image}}" alt="{{customer_name}}" class="wsj-author-image">{{/customer_image}}
                                <div class="wsj-author-info">
                                    <div class="wsj-author-name">{{customer_name}}</div>
                                    {{#customer_title}}<div class="wsj-author-title">{{customer_title}}</div>{{/customer_title}}
                                    <div class="wsj-rating">{{rating_stars}}</div>
                                </div>
                            </div>
                            {{#company_logo}}<img src="{{company_logo}}" alt="Company Logo" class="wsj-company-logo">{{/company_logo}}
                        </div>
                    </div>
                </section>';
            
            case 'hero-newsletter':
                return '
                <section class="wsj-hero wsj-hero-newsletter {{#background_gradient}}wsj-gradient{{/background_gradient}}">
                    <div class="wsj-hero-container">
                        <h1 class="wsj-hero-headline">{{headline}}</h1>
                        {{#subtitle}}<p class="wsj-hero-subtitle">{{subtitle}}</p>{{/subtitle}}
                        {{#form_text}}<p class="wsj-form-description">{{form_text}}</p>{{/form_text}}
                        <form class="wsj-newsletter-form" {{#mailchimp_action}}action="{{mailchimp_action}}"{{/mailchimp_action}} method="post">
                            <div class="wsj-form-group">
                                <input type="email" name="email" placeholder="{{placeholder_email}}" required class="wsj-form-input">
                                <button type="submit" class="wsj-btn wsj-btn-primary">{{button_text}}</button>
                            </div>
                            {{#privacy_text}}<p class="wsj-privacy-text">{{privacy_text}}</p>{{/privacy_text}}
                        </form>
                        <div class="wsj-form-success" style="display: none;">{{success_message}}</div>
                    </div>
                </section>';
            
            case 'hero-price-comparison':
                return '
                <section class="wsj-hero wsj-hero-price" style="--accent-color: {{accent_color}}">
                    <div class="wsj-hero-container">
                        <h1 class="wsj-hero-headline">{{headline}}</h1>
                        {{#subtitle}}<p class="wsj-hero-subtitle">{{subtitle}}</p>{{/subtitle}}
                        <div class="wsj-price-box">
                            <div class="wsj-price-comparison">
                                {{#old_price}}<span class="wsj-old-price">{{old_price}}{{currency}}</span>{{/old_price}}
                                <span class="wsj-new-price">{{new_price}}{{currency}}</span>
                                {{#discount_percent}}<span class="wsj-discount">{{discount_percent}}% Rabatt</span>{{/discount_percent}}
                            </div>
                            {{#features_list}}<ul class="wsj-features-list">{{features_list_items}}</ul>{{/features_list}}
                            <a href="{{cta_url}}" class="wsj-btn wsj-btn-primary wsj-btn-large">{{cta_text}}</a>
                            {{#guarantee_text}}<p class="wsj-guarantee">{{guarantee_text}}</p>{{/guarantee_text}}
                        </div>
                    </div>
                </section>';
            
            // CONTENT TEMPLATES
            case 'content-text':
                return '
                <section class="wsj-content wsj-content-text {{text_size}}" style="text-align: {{text_align}}; {{#background_color}}background-color: {{background_color}};{{/background_color}} padding: {{padding}}px;">
                    <div class="wsj-content-container">
                        {{content}}
                    </div>
                </section>';
            
            case 'content-two-thirds-one-third':
                return '
                <section class="wsj-content wsj-content-layout">
                    <div class="wsj-content-container">
                        <div class="wsj-layout-grid wsj-layout-2-3-1-3" style="gap: {{gap_size}}px;">
                            <div class="wsj-main-content">
                                {{main_content}}
                            </div>
                            <div class="wsj-sidebar-content" style="{{#sidebar_background}}background-color: {{sidebar_background}};{{/sidebar_background}}">
                                {{sidebar_content}}
                            </div>
                        </div>
                    </div>
                </section>';
            
            case 'content-half-half':
                return '
                <section class="wsj-content wsj-content-layout" style="{{#background_color}}background-color: {{background_color}};{{/background_color}}">
                    <div class="wsj-content-container">
                        <div class="wsj-layout-grid wsj-layout-half" style="gap: {{gap_size}}px; align-items: {{vertical_align}};">
                            <div class="wsj-half-content">
                                {{left_content}}
                            </div>
                            <div class="wsj-half-content">
                                {{right_content}}
                            </div>
                        </div>
                    </div>
                </section>';
            
            case 'content-pros-cons':
                return '
                <section class="wsj-content wsj-pros-cons">
                    <div class="wsj-content-container">
                        {{#title}}<h2 class="wsj-section-title">{{title}}</h2>{{/title}}
                        <div class="wsj-pros-cons-grid">
                            <div class="wsj-pros" style="--accent-color: {{pros_color}};">
                                <h3 class="wsj-pros-title">{{#show_icons}}<span class="wsj-icon wsj-icon-check"></span>{{/show_icons}}{{pros_title}}</h3>
                                <ul class="wsj-pros-list">{{pros_list_items}}</ul>
                            </div>
                            <div class="wsj-cons" style="--accent-color: {{cons_color}};">
                                <h3 class="wsj-cons-title">{{#show_icons}}<span class="wsj-icon wsj-icon-times"></span>{{/show_icons}}{{cons_title}}</h3>
                                <ul class="wsj-cons-list">{{cons_list_items}}</ul>
                            </div>
                        </div>
                    </div>
                </section>';
            
            case 'content-image-text':
                return '
                <section class="wsj-content wsj-image-text wsj-image-{{image_position}}">
                    <div class="wsj-content-container">
                        <div class="wsj-image-text-grid wsj-image-{{image_size}}">
                            <div class="wsj-image-container">
                                <img src="{{image_url}}" alt="{{image_alt}}" class="{{#image_border}}wsj-bordered{{/image_border}} {{#image_shadow}}wsj-shadow{{/image_shadow}}">
                            </div>
                            <div class="wsj-text-container">
                                {{text_content}}
                            </div>
                        </div>
                    </div>
                </section>';
            
            case 'content-quote':
                return '
                <section class="wsj-content wsj-quote wsj-quote-{{quote_style}}" style="background-color: {{background_color}}; color: {{text_color}}; --accent-color: {{accent_color}};">
                    <div class="wsj-content-container">
                        <blockquote class="wsj-quote-text">
                            "{{quote_text}}"
                        </blockquote>
                        {{#quote_author}}
                        <cite class="wsj-quote-author">
                            <span class="wsj-author-name">{{quote_author}}</span>
                            {{#quote_source}}<span class="wsj-quote-source">{{quote_source}}</span>{{/quote_source}}
                        </cite>
                        {{/quote_author}}
                    </div>
                </section>';
            
            case 'content-cta':
                return '
                <section class="wsj-content wsj-cta {{#background_gradient}}wsj-gradient{{/background_gradient}} {{#center_align}}wsj-center{{/center_align}}" style="background-color: {{background_color}}; color: {{text_color}};">
                    <div class="wsj-content-container">
                        <h2 class="wsj-cta-headline">{{headline}}</h2>
                        {{#description}}<p class="wsj-cta-description">{{description}}</p>{{/description}}
                        <a href="{{button_url}}" class="wsj-btn wsj-btn-{{button_style}} wsj-btn-large">{{button_text}}</a>
                    </div>
                </section>';
            
            case 'content-table':
                return '
                <section class="wsj-content wsj-table-section">
                    <div class="wsj-content-container">
                        {{#table_title}}<h2 class="wsj-section-title">{{table_title}}</h2>{{/table_title}}
                        <div class="wsj-table-container">
                            <table class="wsj-table {{#striped_rows}}wsj-striped{{/striped_rows}} {{#hover_effect}}wsj-hover{{/hover_effect}} {{#compact_style}}wsj-compact{{/compact_style}}" style="--header-bg: {{header_background}}; --header-color: {{header_text_color}};">
                                <thead>
                                    <tr>{{table_headers_html}}</tr>
                                </thead>
                                <tbody>
                                    {{table_rows_html}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>';
            
            case 'content-accordion':
                return '
                <section class="wsj-content wsj-accordion">
                    <div class="wsj-content-container">
                        {{#accordion_title}}<h2 class="wsj-section-title">{{accordion_title}}</h2>{{/accordion_title}}
                        <div class="wsj-accordion-container wsj-border-{{border_style}}" data-allow-multiple="{{allow_multiple}}" data-first-open="{{first_open}}">
                            {{accordion_items_html}}
                        </div>
                    </div>
                </section>';
            
            case 'content-steps':
                return '
                <section class="wsj-content wsj-steps wsj-steps-{{layout_style}}" style="--accent-color: {{accent_color}};">
                    <div class="wsj-content-container">
                        {{#steps_title}}<h2 class="wsj-section-title">{{steps_title}}</h2>{{/steps_title}}
                        {{#steps_intro}}<p class="wsj-steps-intro">{{steps_intro}}</p>{{/steps_intro}}
                        <div class="wsj-steps-container wsj-steps-numbers-{{number_style}}">
                            {{steps_html}}
                        </div>
                    </div>
                </section>';
            
            // TRADING TEMPLATES
            case 'trading-performance':
                return '
                <section class="wsj-content wsj-trading-performance">
                    <div class="wsj-content-container">
                        <h2 class="wsj-section-title">Performance: {{strategy_name}}</h2>
                        <div class="wsj-performance-grid">
                            <div class="wsj-performance-stats">
                                {{#total_return}}<div class="wsj-stat"><label>Gesamtrendite:</label><span class="wsj-positive">{{total_return}}</span></div>{{/total_return}}
                                {{#monthly_return}}<div class="wsj-stat"><label>Monatliche Rendite:</label><span>{{monthly_return}}</span></div>{{/monthly_return}}
                                {{#max_drawdown}}<div class="wsj-stat"><label>Max. Drawdown:</label><span class="wsj-negative">{{max_drawdown}}</span></div>{{/max_drawdown}}
                                {{#win_rate}}<div class="wsj-stat"><label>Trefferquote:</label><span>{{win_rate}}</span></div>{{/win_rate}}
                                {{#profit_factor}}<div class="wsj-stat"><label>Profit Faktor:</label><span>{{profit_factor}}</span></div>{{/profit_factor}}
                                {{#total_trades}}<div class="wsj-stat"><label>Anzahl Trades:</label><span>{{total_trades}}</span></div>{{/total_trades}}
                            </div>
                            {{#chart_image}}<div class="wsj-performance-chart"><img src="{{chart_image}}" alt="Performance Chart"></div>{{/chart_image}}
                        </div>
                        {{#risk_warning}}<div class="wsj-risk-warning">{{risk_warning}}</div>{{/risk_warning}}
                        {{#show_disclaimer}}<div class="wsj-disclaimer">Risikohinweis: Der Handel mit Finanzinstrumenten ist mit hohen Risiken verbunden. Vergangene Performance ist kein Indikator für zukünftige Ergebnisse.</div>{{/show_disclaimer}}
                    </div>
                </section>';
            
            case 'broker-comparison':
                return '
                <section class="wsj-content wsj-broker-comparison">
                    <div class="wsj-content-container">
                        {{#comparison_title}}<h2 class="wsj-section-title">{{comparison_title}}</h2>{{/comparison_title}}
                        <div class="wsj-comparison-table">
                            {{brokers_html}}
                        </div>
                    </div>
                </section>';
            
            case 'economic-calendar':
                return '
                <section class="wsj-content wsj-economic-calendar">
                    <div class="wsj-content-container">
                        <h2 class="wsj-section-title">{{calendar_title}}</h2>
                        {{#show_timezone}}<p class="wsj-timezone">Alle Zeiten in {{timezone}}</p>{{/show_timezone}}
                        <div class="wsj-calendar-container {{#compact_view}}wsj-compact{{/compact_view}}">
                            {{events_html}}
                        </div>
                    </div>
                </section>';
            
            default:
                return '';
        }
    }
    
    /**
     * Process template with data
     */
    private function process_template($template, $data, $block_config) {
        // Handle special processing for different field types
        $processed_data = $this->process_template_data($data, $block_config);
        
        // Replace mustache-style placeholders
        foreach ($processed_data as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        
        // Handle conditional blocks
        $template = $this->process_conditionals($template, $processed_data);
        
        // Clean up remaining placeholders
        $template = preg_replace('/\{\{[^}]+\}\}/', '', $template);
        
        return $template;
    }
    
    /**
     * Process template data for special cases
     */
    private function process_template_data($data, $block_config) {
        $processed = $data;
        
        foreach ($block_config['fields'] as $field_id => $field_config) {
            if (!isset($data[$field_id])) {
                continue;
            }
            
            $value = $data[$field_id];
            
            switch ($field_config['type']) {
                case 'range':
                    if ($field_id === 'overlay_opacity') {
                        $processed['overlay_opacity_decimal'] = $value / 100;
                    }
                    break;
                    
                case 'textarea':
                    if (strpos($field_id, '_list') !== false) {
                        $lines = explode("\n", trim($value));
                        $items = '';
                        foreach ($lines as $line) {
                            $line = trim($line);
                            if ($line) {
                                $items .= '<li>' . esc_html($line) . '</li>';
                            }
                        }
                        $processed[$field_id . '_items'] = $items;
                    }
                    break;
                    
                case 'text':
                    if ($field_id === 'table_headers') {
                        $headers = explode(',', $value);
                        $html = '';
                        foreach ($headers as $header) {
                            $html .= '<th>' . esc_html(trim($header)) . '</th>';
                        }
                        $processed['table_headers_html'] = $html;
                    }
                    break;
                    
                case 'repeater':
                    if ($field_id === 'items' || $field_id === 'steps') {
                        $items_html = '';
                        if (is_array($value)) {
                            foreach ($value as $index => $item) {
                                if ($field_id === 'steps') {
                                    $items_html .= $this->process_step_item($item, $index + 1);
                                } else {
                                    $items_html .= $this->process_accordion_item($item, $index);
                                }
                            }
                        }
                        $processed[$field_id . '_html'] = $items_html;
                    }
                    break;
            }
            
            // Special handling for rating stars
            if ($field_id === 'rating') {
                $stars = '';
                for ($i = 1; $i <= 5; $i++) {
                    $stars .= $i <= $value ? '★' : '☆';
                }
                $processed['rating_stars'] = $stars;
            }
        }
        
        // Handle table data
        if (isset($data['table_data'])) {
            $rows = explode("\n", trim($data['table_data']));
            $html = '';
            foreach ($rows as $row) {
                $row = trim($row);
                if ($row) {
                    $cells = explode(',', $row);
                    $html .= '<tr>';
                    foreach ($cells as $cell) {
                        $html .= '<td>' . esc_html(trim($cell)) . '</td>';
                    }
                    $html .= '</tr>';
                }
            }
            $processed['table_rows_html'] = $html;
        }
        
        return $processed;
    }
    
    /**
     * Process conditional blocks in template
     */
    private function process_conditionals($template, $data) {
        // Handle {{#variable}}...{{/variable}} blocks
        $template = preg_replace_callback('/\{\{#(\w+)\}\}(.*?)\{\{\/\1\}\}/s', function($matches) use ($data) {
            $variable = $matches[1];
            $content = $matches[2];
            
            if (isset($data[$variable]) && !empty($data[$variable])) {
                return $content;
            }
            return '';
        }, $template);
        
        return $template;
    }
    
    /**
     * Process step item for steps template
     */
    private function process_step_item($item, $number) {
        $html = '<div class="wsj-step">';
        $html .= '<div class="wsj-step-number">' . $number . '</div>';
        $html .= '<div class="wsj-step-content">';
        $html .= '<h3 class="wsj-step-title">' . esc_html($item['step_title']) . '</h3>';
        $html .= '<p class="wsj-step-description">' . esc_html($item['step_content']) . '</p>';
        if (!empty($item['step_image'])) {
            $html .= '<img src="' . esc_url($item['step_image']) . '" alt="' . esc_attr($item['step_title']) . '" class="wsj-step-image">';
        }
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Process accordion item
     */
    private function process_accordion_item($item, $index) {
        $html = '<div class="wsj-accordion-item">';
        $html .= '<h3 class="wsj-accordion-header"><button class="wsj-accordion-toggle" data-target="item-' . $index . '">';
        $html .= esc_html($item['title']);
        $html .= '<span class="wsj-accordion-icon">+</span>';
        $html .= '</button></h3>';
        $html .= '<div class="wsj-accordion-content" id="item-' . $index . '">';
        $html .= '<p>' . esc_html($item['content']) . '</p>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Get base CSS for all blocks
     */
    private function get_base_css() {
        return '
        <style>
        /* Article Builder Base Styles */
        .article-builder-content {
            font-family: "Source Sans Pro", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        
        /* WSJ Grid System */
        .wsj-content-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .wsj-layout-grid {
            display: grid;
            gap: 30px;
        }
        
        .wsj-layout-2-3-1-3 {
            grid-template-columns: 2fr 1fr;
        }
        
        .wsj-layout-half {
            grid-template-columns: 1fr 1fr;
        }
        
        /* Hero Styles */
        .wsj-hero {
            padding: 60px 0;
            margin-bottom: 40px;
        }
        
        .wsj-hero-headline {
            font-family: "Playfair Display", serif;
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 16px;
            color: #1a365d;
        }
        
        .wsj-hero-subtitle {
            font-size: 1.25rem;
            color: #666;
            margin-bottom: 24px;
        }
        
        .wsj-hero-split .wsj-hero-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }
        
        .wsj-hero-overlay {
            position: relative;
            background-size: cover;
            background-position: center;
            color: white;
        }
        
        .wsj-hero-overlay-bg {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: black;
        }
        
        .wsj-hero-video {
            position: relative;
            overflow: hidden;
        }
        
        .wsj-hero-video video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -2;
        }
        
        /* Buttons */
        .wsj-btn {
            display: inline-block;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
        }
        
        .wsj-btn-primary {
            background: #1a365d;
            color: white;
        }
        
        .wsj-btn-primary:hover {
            background: #2d3748;
            transform: translateY(-1px);
        }
        
        .wsj-btn-secondary {
            background: transparent;
            color: #1a365d;
            border: 2px solid #1a365d;
        }
        
        .wsj-btn-large {
            padding: 16px 32px;
            font-size: 18px;
        }
        
        /* Content Sections */
        .wsj-content {
            padding: 40px 0;
            margin-bottom: 40px;
        }
        
        .wsj-section-title {
            font-family: "Playfair Display", serif;
            font-size: 2rem;
            font-weight: 700;
            color: #1a365d;
            margin-bottom: 24px;
            border-bottom: 3px solid #3182ce;
            padding-bottom: 12px;
        }
        
        /* Pros & Cons */
        .wsj-pros-cons-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }
        
        .wsj-pros, .wsj-cons {
            padding: 24px;
            border-radius: 8px;
            border: 2px solid var(--accent-color);
        }
        
        .wsj-pros-title, .wsj-cons-title {
            color: var(--accent-color);
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 16px;
        }
        
        /* Tables */
        .wsj-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 14px;
        }
        
        .wsj-table th {
            background: var(--header-bg);
            color: var(--header-color);
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        
        .wsj-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .wsj-table.wsj-striped tbody tr:nth-child(even) {
            background: #f7fafc;
        }
        
        .wsj-table.wsj-hover tbody tr:hover {
            background: #edf2f7;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .wsj-layout-2-3-1-3,
            .wsj-layout-half,
            .wsj-pros-cons-grid {
                grid-template-columns: 1fr;
            }
            
            .wsj-hero-split .wsj-hero-container {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .wsj-hero-headline {
                font-size: 2rem;
            }
        }
        
        /* Statistics */
        .wsj-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
            margin-top: 40px;
        }
        
        .wsj-stat {
            text-align: center;
            padding: 20px;
        }
        
        .wsj-stat-number {
            display: block;
            font-size: 2.5rem;
            font-weight: 700;
            color: #3182ce;
        }
        
        .wsj-stat-label {
            display: block;
            font-size: 0.9rem;
            color: #666;
            margin-top: 8px;
        }
        
        /* Trading specific */
        .wsj-performance-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
            margin-top: 24px;
        }
        
        .wsj-performance-stats .wsj-stat {
            display: flex;
            justify-content: between;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .wsj-positive {
            color: #48bb78;
            font-weight: 600;
        }
        
        .wsj-negative {
            color: #f56565;
            font-weight: 600;
        }
        
        .wsj-risk-warning {
            background: #fed7d7;
            border: 1px solid #fc8181;
            border-radius: 4px;
            padding: 16px;
            margin-top: 24px;
            font-size: 0.9rem;
        }
        
        .wsj-disclaimer {
            background: #e2e8f0;
            padding: 12px;
            border-radius: 4px;
            font-size: 0.8rem;
            color: #666;
            margin-top: 16px;
        }
        </style>';
    }
    
    /**
     * Export article as HTML file
     */
    public function export_article($blocks_data, $filename = null, $options = array()) {
        if (!$filename) {
            $filename = 'article-' . date('Y-m-d-H-i-s') . '.html';
        }
        
        $defaults = array(
            'include_doctype' => true,
            'include_meta' => true,
            'include_title' => 'Generated Article',
            'include_viewport' => true
        );
        
        $options = array_merge($defaults, $options);
        
        $html = '';
        
        if ($options['include_doctype']) {
            $html .= '<!DOCTYPE html>' . "\n";
            $html .= '<html lang="de">' . "\n";
            $html .= '<head>' . "\n";
            
            if ($options['include_meta']) {
                $html .= '<meta charset="UTF-8">' . "\n";
            }
            
            if ($options['include_viewport']) {
                $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
            }
            
            if ($options['include_title']) {
                $html .= '<title>' . esc_html($options['include_title']) . '</title>' . "\n";
            }
            
            $html .= '</head>' . "\n";
            $html .= '<body>' . "\n";
        }
        
        $html .= $this->generate_article($blocks_data, $options);
        
        if ($options['include_doctype']) {
            $html .= '</body>' . "\n";
            $html .= '</html>' . "\n";
        }
        
        return array(
            'filename' => $filename,
            'content' => $html,
            'size' => strlen($html)
        );
    }
}