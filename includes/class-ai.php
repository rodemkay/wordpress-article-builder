<?php
namespace ArticleBuilder;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AI Integration Class für Article Builder Plugin
 * 
 * Unterstützt MCP (bevorzugt), Claude API und OpenAI API als Fallback
 * MCP nutzt das bestehende Claude-Abo ohne separate API-Keys
 * Spezialisiert auf Forex/Trading-Content in deutscher Sprache
 */
class AI {

    private $claude_api_key;
    private $openai_api_key;
    private $preferred_provider = 'mcp';
    private $usage_stats = array();
    private $mcp_connector = null;
    private $rate_limits = array(
        'mcp' => array('requests_per_minute' => 120, 'tokens_per_minute' => 80000),
        'claude' => array('requests_per_minute' => 60, 'tokens_per_minute' => 40000),
        'openai' => array('requests_per_minute' => 60, 'tokens_per_minute' => 90000)
    );

    public function __construct() {
        $this->claude_api_key = get_option('article_builder_claude_api_key', '');
        $this->openai_api_key = get_option('article_builder_openai_api_key', '');
        $this->preferred_provider = get_option('article_builder_preferred_ai', 'mcp');
        $this->load_usage_stats();
        
        // MCP Connector initialisieren
        if (class_exists('\ArticleBuilder\MCPConnector')) {
            $this->mcp_connector = new MCPConnector();
        }
        
        add_action('wp_ajax_ab_generate_content', array($this, 'ajax_generate_content'));
        add_action('wp_ajax_ab_suggest_structure', array($this, 'ajax_suggest_structure'));
        add_action('wp_ajax_ab_generate_seo', array($this, 'ajax_generate_seo'));
        add_action('wp_ajax_ab_suggest_tags', array($this, 'ajax_suggest_tags'));
        add_action('wp_ajax_ab_test_connection', array($this, 'ajax_test_connection'));
        add_action('wp_ajax_ab_get_providers', array($this, 'ajax_get_providers'));
        add_action('wp_ajax_ab_update_provider_settings', array($this, 'ajax_update_provider_settings'));
    }

    /**
     * Generiert Content basierend auf User-Prompt
     */
    public function generate_content($prompt, $content_type = 'paragraph', $topic = 'forex') {
        // Wenn MCP verfügbar ist, nutze die spezielle Block-Content-Generierung
        if ($this->get_available_provider() === 'mcp' && $this->mcp_connector) {
            $parameters = array(
                'topic' => $prompt,
                'article_type' => $this->map_content_type_to_article_type($content_type),
                'audience' => 'Trading-Anfänger und Fortgeschrittene',
                'tone' => 'professionell und verständlich'
            );
            
            $mcp_response = $this->mcp_connector->generate_block_content($content_type, $parameters);
            
            if (!is_wp_error($mcp_response)) {
                $usage = array(
                    'prompt_tokens' => 0,
                    'completion_tokens' => 0,
                    'total_tokens' => str_word_count($mcp_response['text'] ?? '') * 1.3 // Approximation
                );
                $this->track_usage($content_type, $usage);
                return $mcp_response['text'] ?? $mcp_response['content'] ?? '';
            }
        }
        
        // Fallback zur Standard-Methode
        $system_prompt = $this->get_system_prompt($content_type, $topic);
        $full_prompt = $system_prompt . "\n\nUser-Anfrage: " . $prompt;

        $response = $this->make_ai_request($full_prompt, array(
            'max_tokens' => $this->get_max_tokens_for_type($content_type),
            'temperature' => 0.7
        ));

        if ($response && !is_wp_error($response)) {
            $this->track_usage($content_type, $response['usage']);
            return $response['content'];
        }

        return new WP_Error('ai_generation_failed', 'Content-Generierung fehlgeschlagen: ' . 
            (is_wp_error($response) ? $response->get_error_message() : 'Unbekannter Fehler'));
    }

    /**
     * Mappt Content-Typen auf Artikel-Typen für MCP
     */
    private function map_content_type_to_article_type($content_type) {
        $mapping = array(
            'headline' => 'headline',
            'introduction' => 'introduction',
            'paragraph' => 'paragraph',
            'strategy_analysis' => 'trading_strategy',
            'market_analysis' => 'market_analysis',
            'news_summary' => 'news',
            'educational' => 'educational'
        );
        
        return $mapping[$content_type] ?? 'paragraph';
    }

    /**
     * Schlägt Artikel-Strukturen vor
     */
    public function suggest_article_structure($topic, $article_type = 'analysis') {
        // Wenn MCP verfügbar ist, nutze die spezielle MCP-Methode
        if ($this->get_available_provider() === 'mcp' && $this->mcp_connector) {
            $structure = $this->mcp_connector->suggest_article_structure($topic, $article_type);
            if (!is_wp_error($structure)) {
                return $structure;
            }
        }
        
        // Fallback zur Standard-Methode
        $prompt = $this->get_structure_prompt($topic, $article_type);
        
        $response = $this->make_ai_request($prompt, array(
            'max_tokens' => 2000,
            'temperature' => 0.6
        ));

        if ($response && !is_wp_error($response)) {
            return $this->parse_structure_response($response['content']);
        }

        return new WP_Error('structure_suggestion_failed', 'Struktur-Vorschlag fehlgeschlagen');
    }

    /**
     * Generiert SEO-Metadaten
     */
    public function generate_seo_metadata($content, $focus_keyword = '') {
        // Wenn MCP verfügbar ist, nutze die spezielle MCP-Methode
        if ($this->get_available_provider() === 'mcp' && $this->mcp_connector) {
            $title = wp_trim_words($content, 10);
            $summary = wp_trim_words($content, 50);
            $seo_data = $this->mcp_connector->generate_seo_metadata($title, $summary);
            if (!is_wp_error($seo_data)) {
                return $seo_data;
            }
        }
        
        // Fallback zur Standard-Methode
        $prompt = $this->get_seo_prompt($content, $focus_keyword);
        
        $response = $this->make_ai_request($prompt, array(
            'max_tokens' => 1000,
            'temperature' => 0.5
        ));

        if ($response && !is_wp_error($response)) {
            return $this->parse_seo_response($response['content']);
        }

        return new WP_Error('seo_generation_failed', 'SEO-Generierung fehlgeschlagen');
    }

    /**
     * Generiert Kategorie- und Tag-Vorschläge
     */
    public function suggest_categories_and_tags($content) {
        $prompt = $this->get_taxonomy_prompt($content);
        
        $response = $this->make_ai_request($prompt, array(
            'max_tokens' => 500,
            'temperature' => 0.4
        ));

        if ($response && !is_wp_error($response)) {
            return $this->parse_taxonomy_response($response['content']);
        }

        return array('categories' => array(), 'tags' => array());
    }

    /**
     * Zentrale AI-Request-Funktion
     */
    private function make_ai_request($prompt, $options = array()) {
        // Rate Limiting prüfen
        if (!$this->check_rate_limits()) {
            return new WP_Error('rate_limit_exceeded', 'Rate Limit erreicht. Bitte warten Sie.');
        }

        // Provider wählen basierend auf Verfügbarkeit
        $provider = $this->get_available_provider();
        if (!$provider) {
            return new WP_Error('no_provider', 'Kein AI-Provider verfügbar');
        }

        if ($provider === 'mcp') {
            return $this->make_mcp_request($prompt, $options);
        } elseif ($provider === 'claude') {
            return $this->make_claude_request($prompt, $options);
        } else {
            return $this->make_openai_request($prompt, $options);
        }
    }

    /**
     * MCP Request über MCPConnector
     */
    private function make_mcp_request($prompt, $options) {
        if (!$this->mcp_connector) {
            return new WP_Error('mcp_not_available', 'MCP Connector nicht verfügbar');
        }

        // MCP Connector Verbindung testen
        if (!$this->mcp_connector->check_connection()) {
            return new WP_Error('mcp_connection_failed', 'MCP Verbindung fehlgeschlagen');
        }

        // Optionen für MCP anpassen
        $mcp_options = array(
            'model' => get_option('article_builder_mcp_model', 'claude-3-sonnet-20240229'),
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 2000
        );

        // Kontext für bessere Ergebnisse
        $context = array(
            'article_type' => 'forex_trading',
            'target_audience' => 'Trading-Anfänger und Fortgeschrittene',
            'tone' => 'professionell und verständlich',
            'language' => 'de'
        );

        $response = $this->mcp_connector->send_request($prompt, $context, $mcp_options);

        if (is_wp_error($response)) {
            return $response;
        }

        // Response-Format standardisieren
        return array(
            'content' => $response['text'] ?? $response['content'] ?? '',
            'usage' => array(
                'prompt_tokens' => $response['usage']['prompt_tokens'] ?? 0,
                'completion_tokens' => $response['usage']['completion_tokens'] ?? 0,
                'total_tokens' => $response['usage']['total_tokens'] ?? 0
            ),
            'provider' => 'mcp'
        );
    }

    /**
     * Claude API Request
     */
    private function make_claude_request($prompt, $options) {
        $url = 'https://api.anthropic.com/v1/messages';
        
        $body = array(
            'model' => get_option('article_builder_claude_model', 'claude-3-haiku-20240307'),
            'max_tokens' => $options['max_tokens'] ?? 2000,
            'temperature' => $options['temperature'] ?? 0.7,
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            )
        );

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'x-api-key' => $this->claude_api_key,
                'anthropic-version' => '2023-06-01'
            ),
            'body' => json_encode($body),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (wp_remote_retrieve_response_code($response) !== 200) {
            return new WP_Error('claude_api_error', 
                $body['error']['message'] ?? 'Claude API Fehler');
        }

        return array(
            'content' => $body['content'][0]['text'],
            'usage' => array(
                'prompt_tokens' => $body['usage']['input_tokens'],
                'completion_tokens' => $body['usage']['output_tokens'],
                'total_tokens' => $body['usage']['input_tokens'] + $body['usage']['output_tokens']
            ),
            'provider' => 'claude'
        );
    }

    /**
     * OpenAI API Request
     */
    private function make_openai_request($prompt, $options) {
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $body = array(
            'model' => get_option('article_builder_openai_model', 'gpt-3.5-turbo'),
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'max_tokens' => $options['max_tokens'] ?? 2000,
            'temperature' => $options['temperature'] ?? 0.7
        );

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->openai_api_key
            ),
            'body' => json_encode($body),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (wp_remote_retrieve_response_code($response) !== 200) {
            return new WP_Error('openai_api_error', 
                $body['error']['message'] ?? 'OpenAI API Fehler');
        }

        return array(
            'content' => $body['choices'][0]['message']['content'],
            'usage' => $body['usage'],
            'provider' => 'openai'
        );
    }

    /**
     * System-Prompts für verschiedene Content-Typen
     */
    private function get_system_prompt($content_type, $topic) {
        $base_prompt = "Du bist ein Experte für Forex-Trading und Finanzmarkt-Analyse. ";
        $base_prompt .= "Schreibe ausschließlich in deutscher Sprache. ";
        $base_prompt .= "Verwende professionelle, aber verständliche Sprache für Trader aller Erfahrungsstufen.\n\n";

        $prompts = array(
            'headline' => $base_prompt . "Erstelle eine aussagekräftige Schlagzeile für einen Trading-Artikel. Max. 70 Zeichen für SEO.",
            
            'introduction' => $base_prompt . "Schreibe eine fesselnde Einleitung (150-200 Wörter), die das Interesse der Leser weckt und den Artikel-Inhalt zusammenfasst.",
            
            'paragraph' => $base_prompt . "Erstelle einen informativen Absatz (100-150 Wörter) mit wertvollen Trading-Insights.",
            
            'strategy_analysis' => $base_prompt . "Erkläre Trading-Strategien mit konkreten Ein- und Ausstiegspunkten, Risikomanagement und praktischen Beispielen.",
            
            'market_analysis' => $base_prompt . "Analysiere aktuelle Marktbewegungen mit technischer und fundamentaler Analyse. Berücksichtige wichtige Wirtschaftsdaten.",
            
            'news_summary' => $base_prompt . "Fasse Finanznachrichten prägnant zusammen und erkläre deren Auswirkungen auf die Märkte.",
            
            'educational' => $base_prompt . "Erkläre Trading-Konzepte verständlich für Anfänger, aber mit ausreichend Tiefe für fortgeschrittene Trader."
        );

        return $prompts[$content_type] ?? $prompts['paragraph'];
    }

    /**
     * Struktur-Prompt für Artikel-Typen
     */
    private function get_structure_prompt($topic, $article_type) {
        $prompt = "Erstelle eine detaillierte Artikel-Struktur für einen {$article_type}-Artikel zum Thema '{$topic}' in deutscher Sprache.\n\n";
        
        $structures = array(
            'analysis' => "
                1. Einleitung (Marktkontext und aktuelle Situation)
                2. Technische Analyse (Charts, Indikatoren, Trends)
                3. Fundamentale Faktoren (Wirtschaftsdaten, News)
                4. Trading-Gelegenheiten (Ein-/Ausstiegspunkte)
                5. Risikomanagement
                6. Fazit und Ausblick
            ",
            'strategy' => "
                1. Strategie-Überblick und Zielsetzung
                2. Voraussetzungen und Zeitrahmen
                3. Setup-Bedingungen (technische Signale)
                4. Ein- und Ausstiegsregeln
                5. Risiko-Ertrags-Verhältnis
                6. Beispiel-Trades
                7. Häufige Fehler vermeiden
            ",
            'news' => "
                1. Breaking News Zusammenfassung
                2. Marktreaktion und erste Bewegungen
                3. Analyse der Auswirkungen
                4. Trading-Implikationen
                5. Wichtige Termine/Events im Ausblick
            ",
            'educational' => "
                1. Definition und Grundlagen
                2. Warum ist das wichtig?
                3. Praktische Anwendung
                4. Schritt-für-Schritt Anleitung
                5. Häufige Anfängerfehler
                6. Weiterführende Ressourcen
            "
        );

        $prompt .= "Verwende diese Basis-Struktur:\n" . ($structures[$article_type] ?? $structures['analysis']);
        $prompt .= "\n\nErstelle für jeden Punkt spezifische Überschriften und 2-3 Unterpunkte mit konkreten Inhalten.";
        $prompt .= "\nFormat: JSON mit 'sections' Array containing 'title', 'subsections' und 'word_count'.";

        return $prompt;
    }

    /**
     * SEO-Prompt
     */
    private function get_seo_prompt($content, $focus_keyword) {
        $prompt = "Erstelle SEO-optimierte Metadaten für folgenden Forex/Trading-Content:\n\n";
        $prompt .= substr($content, 0, 500) . "...\n\n";
        
        if ($focus_keyword) {
            $prompt .= "Focus-Keyword: {$focus_keyword}\n\n";
        }
        
        $prompt .= "Erstelle:\n";
        $prompt .= "1. SEO-Title (max. 60 Zeichen, inkl. Focus-Keyword)\n";
        $prompt .= "2. Meta-Description (150-160 Zeichen, verkaufsorientiert)\n";
        $prompt .= "3. 5-8 relevante Keywords für Trading/Forex\n";
        $prompt .= "4. Suggested URL-Slug (max. 5 Wörter)\n\n";
        $prompt .= "Format: JSON mit 'title', 'description', 'keywords' (Array), 'slug'";

        return $prompt;
    }

    /**
     * Taxonomie-Prompt für Kategorien und Tags
     */
    private function get_taxonomy_prompt($content) {
        $prompt = "Analysiere folgenden Trading/Forex-Content und schlage passende WordPress-Kategorien und Tags vor:\n\n";
        $prompt .= substr($content, 0, 800) . "...\n\n";
        
        $prompt .= "Verfügbare Kategorien:\n";
        $prompt .= "- Forex Analysis, Stock Analysis, Crypto Analysis\n";
        $prompt .= "- Trading Strategies, Day Trading, Swing Trading, Scalping\n";
        $prompt .= "- Market News, Economic Calendar, Broker Reviews\n";
        $prompt .= "- Technical Analysis, Fundamental Analysis\n";
        $prompt .= "- Risk Management, Money Management\n";
        $prompt .= "- Trading Psychology, Market Education\n\n";
        
        $prompt .= "Erstelle:\n";
        $prompt .= "1. 1-2 Hauptkategorien\n";
        $prompt .= "2. 3-6 spezifische Tags (Währungspaare, Indikatoren, etc.)\n\n";
        $prompt .= "Format: JSON mit 'categories' (Array) und 'tags' (Array)";

        return $prompt;
    }

    /**
     * Response-Parser für Struktur-Vorschläge
     */
    private function parse_structure_response($response) {
        // JSON extrahieren falls vorhanden
        if (preg_match('/\{.*\}/s', $response, $matches)) {
            $json = json_decode($matches[0], true);
            if ($json && isset($json['sections'])) {
                return $json;
            }
        }

        // Fallback: Text-basierte Struktur parsen
        $lines = explode("\n", $response);
        $sections = array();
        $current_section = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match('/^\d+\.\s*(.+)/', $line, $matches)) {
                if ($current_section) {
                    $sections[] = $current_section;
                }
                $current_section = array(
                    'title' => $matches[1],
                    'subsections' => array(),
                    'word_count' => 200
                );
            } elseif ($current_section && preg_match('/^[-•]\s*(.+)/', $line, $matches)) {
                $current_section['subsections'][] = $matches[1];
            }
        }

        if ($current_section) {
            $sections[] = $current_section;
        }

        return array('sections' => $sections);
    }

    /**
     * Response-Parser für SEO-Metadaten
     */
    private function parse_seo_response($response) {
        // JSON extrahieren falls vorhanden
        if (preg_match('/\{.*\}/s', $response, $matches)) {
            $json = json_decode($matches[0], true);
            if ($json) {
                return $json;
            }
        }

        // Fallback: Text-basierte Extraktion
        $seo_data = array(
            'title' => '',
            'description' => '',
            'keywords' => array(),
            'slug' => ''
        );

        $lines = explode("\n", $response);
        foreach ($lines as $line) {
            if (preg_match('/title:?\s*(.+)/i', $line, $matches)) {
                $seo_data['title'] = trim($matches[1], '"');
            } elseif (preg_match('/description:?\s*(.+)/i', $line, $matches)) {
                $seo_data['description'] = trim($matches[1], '"');
            } elseif (preg_match('/slug:?\s*(.+)/i', $line, $matches)) {
                $seo_data['slug'] = trim($matches[1], '"');
            } elseif (preg_match('/keywords?:?\s*(.+)/i', $line, $matches)) {
                $keywords = explode(',', $matches[1]);
                $seo_data['keywords'] = array_map('trim', $keywords);
            }
        }

        return $seo_data;
    }

    /**
     * Response-Parser für Taxonomien
     */
    private function parse_taxonomy_response($response) {
        // JSON extrahieren falls vorhanden
        if (preg_match('/\{.*\}/s', $response, $matches)) {
            $json = json_decode($matches[0], true);
            if ($json) {
                return $json;
            }
        }

        // Fallback: Text-basierte Extraktion
        $taxonomy_data = array(
            'categories' => array(),
            'tags' => array()
        );

        $lines = explode("\n", $response);
        foreach ($lines as $line) {
            if (preg_match('/categories?:?\s*(.+)/i', $line, $matches)) {
                $categories = explode(',', $matches[1]);
                $taxonomy_data['categories'] = array_map('trim', $categories);
            } elseif (preg_match('/tags?:?\s*(.+)/i', $line, $matches)) {
                $tags = explode(',', $matches[1]);
                $taxonomy_data['tags'] = array_map('trim', $tags);
            }
        }

        return $taxonomy_data;
    }

    /**
     * Rate Limiting prüfen
     */
    private function check_rate_limits() {
        $provider = $this->get_available_provider();
        if (!$provider) return false;

        $transient_key = "article_builder_rate_limit_{$provider}";
        $usage = get_transient($transient_key);

        if (!$usage) {
            $usage = array('requests' => 0, 'tokens' => 0, 'start_time' => time());
        }

        $elapsed = time() - $usage['start_time'];
        if ($elapsed >= 60) {
            // Reset nach einer Minute
            $usage = array('requests' => 0, 'tokens' => 0, 'start_time' => time());
        }

        $limits = $this->rate_limits[$provider];
        if ($usage['requests'] >= $limits['requests_per_minute'] || 
            $usage['tokens'] >= $limits['tokens_per_minute']) {
            return false;
        }

        return true;
    }

    /**
     * Verfügbaren Provider ermitteln
     */
    private function get_available_provider() {
        // MCP hat Priorität wenn verfügbar und bevorzugt
        if ($this->preferred_provider === 'mcp' && $this->is_mcp_available()) {
            return 'mcp';
        } elseif ($this->preferred_provider === 'claude' && !empty($this->claude_api_key)) {
            return 'claude';
        } elseif ($this->preferred_provider === 'openai' && !empty($this->openai_api_key)) {
            return 'openai';
        }
        
        // Fallback-Reihenfolge: MCP -> Claude -> OpenAI
        if ($this->is_mcp_available()) {
            return 'mcp';
        } elseif (!empty($this->claude_api_key)) {
            return 'claude';
        } elseif (!empty($this->openai_api_key)) {
            return 'openai';
        }
        
        return false;
    }

    /**
     * Max Tokens basierend auf Content-Typ
     */
    private function get_max_tokens_for_type($content_type) {
        $tokens = array(
            'headline' => 100,
            'introduction' => 400,
            'paragraph' => 300,
            'strategy_analysis' => 1000,
            'market_analysis' => 800,
            'news_summary' => 500,
            'educational' => 800
        );

        return $tokens[$content_type] ?? 500;
    }

    /**
     * Usage-Statistiken laden
     */
    private function load_usage_stats() {
        $this->usage_stats = get_option('article_builder_ai_usage_stats', array(
            'total_requests' => 0,
            'total_tokens' => 0,
            'costs' => array('claude' => 0, 'openai' => 0),
            'monthly_usage' => array()
        ));
    }

    /**
     * Usage-Tracking
     */
    private function track_usage($content_type, $usage) {
        $current_month = date('Y-m');
        
        $this->usage_stats['total_requests']++;
        $this->usage_stats['total_tokens'] += $usage['total_tokens'];
        
        if (!isset($this->usage_stats['monthly_usage'][$current_month])) {
            $this->usage_stats['monthly_usage'][$current_month] = array(
                'requests' => 0,
                'tokens' => 0,
                'content_types' => array()
            );
        }
        
        $this->usage_stats['monthly_usage'][$current_month]['requests']++;
        $this->usage_stats['monthly_usage'][$current_month]['tokens'] += $usage['total_tokens'];
        
        if (!isset($this->usage_stats['monthly_usage'][$current_month]['content_types'][$content_type])) {
            $this->usage_stats['monthly_usage'][$current_month]['content_types'][$content_type] = 0;
        }
        $this->usage_stats['monthly_usage'][$current_month]['content_types'][$content_type]++;

        // Kosten berechnen (approximativ)
        $provider = $usage['provider'] ?? $this->get_available_provider();
        $cost = $this->calculate_cost($usage['total_tokens'], $provider);
        
        if (!isset($this->usage_stats['costs'][$provider])) {
            $this->usage_stats['costs'][$provider] = 0;
        }
        $this->usage_stats['costs'][$provider] += $cost;

        update_option('article_builder_ai_usage_stats', $this->usage_stats);

        // Rate Limit Tracking aktualisieren
        $transient_key = "article_builder_rate_limit_{$provider}";
        $rate_usage = get_transient($transient_key) ?: array('requests' => 0, 'tokens' => 0, 'start_time' => time());
        $rate_usage['requests']++;
        $rate_usage['tokens'] += $usage['total_tokens'];
        set_transient($transient_key, $rate_usage, 60);
    }

    /**
     * Kosten berechnen
     */
    private function calculate_cost($tokens, $provider) {
        // MCP nutzt das bestehende Claude-Abo, daher keine zusätzlichen Kosten
        if ($provider === 'mcp') {
            return 0;
        }
        
        $pricing = array(
            'claude' => array('input' => 0.00025, 'output' => 0.00125), // per 1K tokens
            'openai' => array('input' => 0.0005, 'output' => 0.0015)    // per 1K tokens
        );

        if (!isset($pricing[$provider])) return 0;

        // Approximation: 60% input, 40% output
        $input_tokens = $tokens * 0.6;
        $output_tokens = $tokens * 0.4;

        $cost = ($input_tokens / 1000) * $pricing[$provider]['input'] + 
                ($output_tokens / 1000) * $pricing[$provider]['output'];

        return $cost;
    }

    /**
     * AJAX: Content generieren
     */
    public function ajax_generate_content() {
        check_ajax_referer('article_builder_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Keine Berechtigung');
        }

        $prompt = sanitize_textarea_field($_POST['prompt'] ?? '');
        $content_type = sanitize_text_field($_POST['content_type'] ?? 'paragraph');
        $topic = sanitize_text_field($_POST['topic'] ?? 'forex');

        if (empty($prompt)) {
            wp_send_json_error('Prompt fehlt');
        }

        $content = $this->generate_content($prompt, $content_type, $topic);

        if (is_wp_error($content)) {
            wp_send_json_error($content->get_error_message());
        }

        wp_send_json_success(array(
            'content' => $content,
            'usage' => $this->get_current_usage_summary()
        ));
    }

    /**
     * AJAX: Struktur vorschlagen
     */
    public function ajax_suggest_structure() {
        check_ajax_referer('article_builder_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Keine Berechtigung');
        }

        $topic = sanitize_text_field($_POST['topic'] ?? '');
        $article_type = sanitize_text_field($_POST['article_type'] ?? 'analysis');

        if (empty($topic)) {
            wp_send_json_error('Thema fehlt');
        }

        $structure = $this->suggest_article_structure($topic, $article_type);

        if (is_wp_error($structure)) {
            wp_send_json_error($structure->get_error_message());
        }

        wp_send_json_success($structure);
    }

    /**
     * AJAX: SEO generieren
     */
    public function ajax_generate_seo() {
        check_ajax_referer('article_builder_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Keine Berechtigung');
        }

        $content = sanitize_textarea_field($_POST['content'] ?? '');
        $focus_keyword = sanitize_text_field($_POST['focus_keyword'] ?? '');

        if (empty($content)) {
            wp_send_json_error('Content fehlt');
        }

        $seo_data = $this->generate_seo_metadata($content, $focus_keyword);

        if (is_wp_error($seo_data)) {
            wp_send_json_error($seo_data->get_error_message());
        }

        wp_send_json_success($seo_data);
    }

    /**
     * AJAX: Tags vorschlagen
     */
    public function ajax_suggest_tags() {
        check_ajax_referer('article_builder_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Keine Berechtigung');
        }

        $content = sanitize_textarea_field($_POST['content'] ?? '');

        if (empty($content)) {
            wp_send_json_error('Content fehlt');
        }

        $taxonomy_data = $this->suggest_categories_and_tags($content);

        wp_send_json_success($taxonomy_data);
    }

    /**
     * AJAX: Verbindung testen
     */
    public function ajax_test_connection() {
        check_ajax_referer('article_builder_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Keine Berechtigung');
        }

        $provider = sanitize_text_field($_POST['provider'] ?? '');
        $result = $this->test_api_connection($provider);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success($result);
    }

    /**
     * AJAX: Verfügbare Provider abrufen
     */
    public function ajax_get_providers() {
        check_ajax_referer('article_builder_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Keine Berechtigung');
        }

        $providers = $this->get_available_providers();
        $current_provider = $this->get_available_provider();

        wp_send_json_success(array(
            'providers' => $providers,
            'current' => $current_provider,
            'preferred' => $this->preferred_provider
        ));
    }

    /**
     * Aktuelle Usage-Zusammenfassung
     */
    public function get_current_usage_summary() {
        $current_month = date('Y-m');
        $monthly = $this->usage_stats['monthly_usage'][$current_month] ?? array(
            'requests' => 0,
            'tokens' => 0
        );

        $provider = $this->get_available_provider();
        $costs = array_sum($this->usage_stats['costs'] ?? array());
        
        // Bei MCP sind die Kosten durch das bestehende Abo abgedeckt
        if ($provider === 'mcp') {
            $costs = 0;
        }

        return array(
            'monthly_requests' => $monthly['requests'],
            'monthly_tokens' => $monthly['tokens'],
            'total_cost' => $costs,
            'provider' => $provider,
            'provider_name' => $this->get_provider_display_name($provider)
        );
    }

    /**
     * Provider-Anzeigename
     */
    private function get_provider_display_name($provider) {
        $names = array(
            'mcp' => 'MCP (Claude Code)',
            'claude' => 'Claude API',
            'openai' => 'OpenAI GPT'
        );
        
        return $names[$provider] ?? $provider;
    }

    /**
     * Detaillierte Usage-Statistiken
     */
    public function get_detailed_usage_stats() {
        return $this->usage_stats;
    }

    /**
     * Prüft ob MCP verfügbar ist
     */
    private function is_mcp_available() {
        return $this->mcp_connector && $this->mcp_connector->check_connection();
    }

    /**
     * Gibt alle verfügbaren Provider zurück
     */
    public function get_available_providers() {
        $providers = array();
        
        if ($this->is_mcp_available()) {
            $providers['mcp'] = array(
                'name' => 'MCP (Claude Code)',
                'description' => 'Nutzt Ihr bestehendes Claude-Abo ohne API-Keys',
                'recommended' => true,
                'requires_key' => false
            );
        }
        
        if (!empty($this->claude_api_key)) {
            $providers['claude'] = array(
                'name' => 'Claude API',
                'description' => 'Direkte Claude API-Verbindung',
                'recommended' => false,
                'requires_key' => true
            );
        }
        
        if (!empty($this->openai_api_key)) {
            $providers['openai'] = array(
                'name' => 'OpenAI GPT',
                'description' => 'OpenAI GPT-Modelle',
                'recommended' => false,
                'requires_key' => true
            );
        }
        
        return $providers;
    }

    /**
     * Test AI-Verbindung
     */
    public function test_api_connection($provider = null) {
        $provider = $provider ?: $this->get_available_provider();
        
        if (!$provider) {
            return new WP_Error('no_provider', 'Kein Provider verfügbar');
        }

        $test_prompt = "Teste die AI-Verbindung. Antworte mit 'Verbindung erfolgreich' auf Deutsch.";
        
        $response = $this->make_ai_request($test_prompt, array(
            'max_tokens' => 50,
            'temperature' => 0.1
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        return array(
            'success' => true,
            'provider' => $provider,
            'response' => $response['content']
        );
    }

    /**
     * Provider wechseln
     */
    public function set_preferred_provider($provider) {
        $available_providers = array_keys($this->get_available_providers());
        
        if (!in_array($provider, $available_providers)) {
            return new WP_Error('invalid_provider', 'Ungültiger Provider: ' . $provider);
        }
        
        $this->preferred_provider = $provider;
        update_option('article_builder_preferred_ai', $provider);
        
        return true;
    }

    /**
     * MCP-spezifische Einstellungen
     */
    public function get_mcp_settings() {
        return array(
            'available_models' => $this->mcp_connector ? $this->mcp_connector->get_available_models() : array(),
            'current_model' => get_option('article_builder_mcp_model', 'claude-3-sonnet-20240229'),
            'usage_stats' => $this->mcp_connector ? $this->mcp_connector->get_usage_stats() : array(),
            'connection_status' => $this->is_mcp_available()
        );
    }

    /**
     * Alle Provider-Informationen für Admin-Interface
     */
    public function get_provider_info() {
        return array(
            'available' => $this->get_available_providers(),
            'current' => $this->get_available_provider(),
            'preferred' => $this->preferred_provider,
            'mcp_settings' => $this->get_mcp_settings(),
            'usage_summary' => $this->get_current_usage_summary()
        );
    }

    /**
     * AJAX: Provider-Einstellungen aktualisieren
     */
    public function ajax_update_provider_settings() {
        check_ajax_referer('article_builder_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Keine Berechtigung');
        }

        $preferred_provider = sanitize_text_field($_POST['preferred_provider'] ?? '');
        $mcp_model = sanitize_text_field($_POST['mcp_model'] ?? '');

        if (!empty($preferred_provider)) {
            $result = $this->set_preferred_provider($preferred_provider);
            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            }
        }

        if (!empty($mcp_model)) {
            update_option('article_builder_mcp_model', $mcp_model);
        }

        wp_send_json_success(array(
            'message' => 'Einstellungen gespeichert',
            'provider_info' => $this->get_provider_info()
        ));
    }
}