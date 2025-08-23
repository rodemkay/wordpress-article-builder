<?php
namespace ArticleBuilder;

/**
 * MCP Connector Class for Claude Code Integration
 * 
 * Ermöglicht die Nutzung von Claude über das bestehende Claude-Abo
 * ohne separate API-Keys
 * 
 * @package ArticleBuilder
 * @since 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class MCPConnector {
    
    /**
     * MCP Endpoint URL
     */
    private $mcp_endpoint = 'http://localhost:3001/mcp';
    
    /**
     * Session ID für MCP Kommunikation
     */
    private $session_id;
    
    /**
     * User ID
     */
    private $user_id;
    
    /**
     * WebSocket Connection
     */
    private $ws_connection = null;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->user_id = get_current_user_id();
        $this->session_id = $this->get_or_create_session();
    }
    
    /**
     * Session erstellen oder abrufen
     */
    private function get_or_create_session() {
        $session_key = 'ab_mcp_session_' . $this->user_id;
        $session = get_transient($session_key);
        
        if (!$session) {
            $session = wp_generate_password(32, false);
            set_transient($session_key, $session, HOUR_IN_SECONDS * 24);
        }
        
        return $session;
    }
    
    /**
     * MCP Request senden
     * 
     * @param string $prompt Der Prompt für Claude
     * @param array $context Zusätzlicher Kontext
     * @param array $options Optionen (model, temperature, max_tokens)
     * @return array|WP_Error
     */
    public function send_request($prompt, $context = [], $options = []) {
        // Default-Optionen
        $defaults = [
            'model' => 'claude-3-sonnet-20240229',
            'temperature' => 0.7,
            'max_tokens' => 2000,
            'stream' => false
        ];
        
        $options = wp_parse_args($options, $defaults);
        
        // Request-Payload erstellen
        $payload = [
            'jsonrpc' => '2.0',
            'id' => uniqid('ab_', true),
            'method' => 'claude.complete',
            'params' => [
                'prompt' => $this->build_prompt($prompt, $context),
                'model' => $options['model'],
                'temperature' => $options['temperature'],
                'max_tokens' => $options['max_tokens'],
                'session_id' => $this->session_id,
                'metadata' => [
                    'source' => 'article-builder',
                    'user_id' => $this->user_id,
                    'timestamp' => current_time('mysql')
                ]
            ]
        ];
        
        // HTTP Request senden
        $response = wp_remote_post($this->mcp_endpoint, [
            'timeout' => 60,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Session-ID' => $this->session_id
            ],
            'body' => json_encode($payload)
        ]);
        
        if (is_wp_error($response)) {
            return new \WP_Error('mcp_request_failed', 
                'MCP Request fehlgeschlagen: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['error'])) {
            return new \WP_Error('mcp_error', 
                'MCP Fehler: ' . $data['error']['message']);
        }
        
        return $data['result'] ?? [];
    }
    
    /**
     * Streaming Request senden (für längere Generierungen)
     */
    public function send_streaming_request($prompt, $context = [], $callback = null) {
        // WebSocket-URL
        $ws_url = str_replace('http', 'ws', $this->mcp_endpoint) . '/stream';
        
        // Streaming über Server-Sent Events (SSE) als Alternative
        $sse_url = $this->mcp_endpoint . '/stream';
        
        $payload = [
            'prompt' => $this->build_prompt($prompt, $context),
            'session_id' => $this->session_id,
            'stream' => true
        ];
        
        // SSE Request
        $context_options = [
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/json',
                    'X-Session-ID: ' . $this->session_id,
                    'Accept: text/event-stream'
                ],
                'content' => json_encode($payload)
            ]
        ];
        
        $stream_context = stream_context_create($context_options);
        $stream = @fopen($sse_url, 'r', false, $stream_context);
        
        if (!$stream) {
            return new \WP_Error('stream_failed', 'Streaming-Verbindung fehlgeschlagen');
        }
        
        $response_text = '';
        
        while (!feof($stream)) {
            $line = fgets($stream);
            
            if (strpos($line, 'data: ') === 0) {
                $data = substr($line, 6);
                $json = json_decode(trim($data), true);
                
                if ($json && isset($json['text'])) {
                    $response_text .= $json['text'];
                    
                    if ($callback && is_callable($callback)) {
                        call_user_func($callback, $json['text'], $response_text);
                    }
                }
            }
        }
        
        fclose($stream);
        
        return $response_text;
    }
    
    /**
     * Prompt mit Kontext aufbauen
     */
    private function build_prompt($prompt, $context = []) {
        $full_prompt = '';
        
        // System-Prompt für Article Builder
        $full_prompt .= "Du bist ein KI-Assistent für die Erstellung von Artikeln im ForexSignale Magazine. ";
        $full_prompt .= "Erstelle professionelle, SEO-optimierte Inhalte auf Deutsch für Trading und Forex Themen.\n\n";
        
        // Kontext hinzufügen
        if (!empty($context)) {
            if (isset($context['article_type'])) {
                $full_prompt .= "Artikel-Typ: " . $context['article_type'] . "\n";
            }
            if (isset($context['target_audience'])) {
                $full_prompt .= "Zielgruppe: " . $context['target_audience'] . "\n";
            }
            if (isset($context['keywords'])) {
                $full_prompt .= "Keywords: " . implode(', ', $context['keywords']) . "\n";
            }
            if (isset($context['tone'])) {
                $full_prompt .= "Tonalität: " . $context['tone'] . "\n";
            }
            $full_prompt .= "\n";
        }
        
        // User-Prompt
        $full_prompt .= "Aufgabe: " . $prompt;
        
        return $full_prompt;
    }
    
    /**
     * Content für verschiedene Block-Typen generieren
     */
    public function generate_block_content($block_type, $parameters = []) {
        $prompts = [
            'headline' => 'Erstelle eine packende Überschrift für einen Artikel über: {topic}',
            'introduction' => 'Schreibe eine einleitende Zusammenfassung (150-200 Wörter) über: {topic}',
            'paragraph' => 'Schreibe einen informativen Absatz über: {topic}',
            'pros_cons' => 'Liste die Vor- und Nachteile von {topic} auf (jeweils 4-5 Punkte)',
            'conclusion' => 'Verfasse ein überzeugendes Fazit für einen Artikel über: {topic}',
            'cta' => 'Erstelle einen Call-to-Action Text für: {action}',
            'faq' => 'Erstelle 5 häufig gestellte Fragen und Antworten zu: {topic}',
            'trading_strategy' => 'Erkläre die Trading-Strategie: {strategy} mit Ein- und Ausstiegspunkten',
            'market_analysis' => 'Analysiere den aktuellen Markt für: {market}',
            'broker_comparison' => 'Vergleiche die Broker: {brokers} in einer strukturierten Übersicht'
        ];
        
        if (!isset($prompts[$block_type])) {
            return new \WP_Error('invalid_block_type', 'Unbekannter Block-Typ: ' . $block_type);
        }
        
        // Prompt mit Parametern füllen
        $prompt = $prompts[$block_type];
        foreach ($parameters as $key => $value) {
            $prompt = str_replace('{' . $key . '}', $value, $prompt);
        }
        
        // Kontext für bessere Ergebnisse
        $context = [
            'article_type' => $parameters['article_type'] ?? 'blog',
            'target_audience' => $parameters['audience'] ?? 'Trading-Anfänger und Fortgeschrittene',
            'tone' => $parameters['tone'] ?? 'professionell und verständlich'
        ];
        
        return $this->send_request($prompt, $context);
    }
    
    /**
     * Artikel-Struktur vorschlagen
     */
    public function suggest_article_structure($topic, $article_type = 'blog') {
        $prompt = "Erstelle eine strukturierte Gliederung für einen {$article_type}-Artikel über: {$topic}. ";
        $prompt .= "Die Gliederung sollte folgende Elemente enthalten:\n";
        $prompt .= "1. Hauptüberschrift\n";
        $prompt .= "2. Meta-Beschreibung (160 Zeichen)\n";
        $prompt .= "3. Einleitung\n";
        $prompt .= "4. 4-6 Hauptabschnitte mit Unterüberschriften\n";
        $prompt .= "5. Fazit\n";
        $prompt .= "6. Call-to-Action\n\n";
        $prompt .= "Formatiere die Ausgabe als JSON.";
        
        $response = $this->send_request($prompt, [
            'article_type' => $article_type,
            'output_format' => 'json'
        ]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        // JSON aus der Antwort extrahieren
        if (isset($response['text'])) {
            $json_match = [];
            if (preg_match('/\{.*\}/s', $response['text'], $json_match)) {
                return json_decode($json_match[0], true);
            }
        }
        
        return $response;
    }
    
    /**
     * SEO-Metadaten generieren
     */
    public function generate_seo_metadata($title, $content_summary) {
        $prompt = "Generiere SEO-Metadaten für folgenden Artikel:\n\n";
        $prompt .= "Titel: {$title}\n";
        $prompt .= "Zusammenfassung: {$content_summary}\n\n";
        $prompt .= "Erstelle:\n";
        $prompt .= "1. SEO-Titel (max. 60 Zeichen)\n";
        $prompt .= "2. Meta-Beschreibung (max. 160 Zeichen)\n";
        $prompt .= "3. 5-7 relevante Keywords\n";
        $prompt .= "4. 3-5 LSI Keywords\n";
        $prompt .= "5. Open Graph Beschreibung\n\n";
        $prompt .= "Formatiere als JSON.";
        
        return $this->send_request($prompt, [
            'output_format' => 'json',
            'language' => 'de'
        ]);
    }
    
    /**
     * Connection Status prüfen
     */
    public function check_connection() {
        $response = wp_remote_get($this->mcp_endpoint . '/status', [
            'timeout' => 5,
            'headers' => [
                'X-Session-ID' => $this->session_id
            ]
        ]);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        return $status_code === 200;
    }
    
    /**
     * Verfügbare Modelle abrufen
     */
    public function get_available_models() {
        // Claude-Modelle die über MCP verfügbar sind
        return [
            'claude-3-opus-20240229' => 'Claude 3 Opus (Leistungsstärkste)',
            'claude-3-sonnet-20240229' => 'Claude 3 Sonnet (Ausgewogen)',
            'claude-3-haiku-20240307' => 'Claude 3 Haiku (Schnell)',
            'claude-2.1' => 'Claude 2.1 (Legacy)',
            'claude-instant-1.2' => 'Claude Instant (Sehr schnell)'
        ];
    }
    
    /**
     * Usage Statistics abrufen
     */
    public function get_usage_stats() {
        $stats_key = 'ab_mcp_usage_' . $this->user_id;
        $stats = get_option($stats_key, [
            'requests_today' => 0,
            'tokens_used' => 0,
            'last_request' => null
        ]);
        
        return $stats;
    }
    
    /**
     * Usage updaten
     */
    private function update_usage($tokens_used) {
        $stats_key = 'ab_mcp_usage_' . $this->user_id;
        $stats = $this->get_usage_stats();
        
        // Prüfe ob neuer Tag
        $today = date('Y-m-d');
        $last_date = $stats['last_request'] ? date('Y-m-d', strtotime($stats['last_request'])) : '';
        
        if ($today !== $last_date) {
            $stats['requests_today'] = 0;
            $stats['tokens_used'] = 0;
        }
        
        $stats['requests_today']++;
        $stats['tokens_used'] += $tokens_used;
        $stats['last_request'] = current_time('mysql');
        
        update_option($stats_key, $stats);
    }
}