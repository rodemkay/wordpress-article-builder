<?php
namespace ArticleBuilder;

/**
 * Article Builder - Block System
 * Defines all available block types and their configurations
 */

if (!defined('ABSPATH')) {
    exit;
}

class Blocks {
    
    private $blocks = array();
    
    public function __construct() {
        $this->init_blocks();
    }
    
    /**
     * Initialize all available blocks
     */
    private function init_blocks() {
        
        // HERO SECTION VARIATIONS (10 Types)
        $this->blocks['hero'] = array(
            'hero_classic' => array(
                'name' => 'Classic Hero',
                'description' => 'Traditional WSJ-style hero with large headline and subtitle',
                'fields' => array(
                    'headline' => array('type' => 'text', 'label' => 'Hauptüberschrift', 'required' => true),
                    'subtitle' => array('type' => 'textarea', 'label' => 'Unterüberschrift'),
                    'image_url' => array('type' => 'url', 'label' => 'Bild URL'),
                    'link_url' => array('type' => 'url', 'label' => 'Link URL'),
                    'category' => array('type' => 'text', 'label' => 'Kategorie'),
                    'date' => array('type' => 'date', 'label' => 'Datum'),
                    'author' => array('type' => 'text', 'label' => 'Autor')
                ),
                'template' => 'hero-classic'
            ),
            
            'hero_image_overlay' => array(
                'name' => 'Image Overlay Hero',
                'description' => 'Hero mit Bild-Hintergrund und Text-Overlay',
                'fields' => array(
                    'headline' => array('type' => 'text', 'label' => 'Hauptüberschrift', 'required' => true),
                    'subtitle' => array('type' => 'textarea', 'label' => 'Unterüberschrift'),
                    'background_image' => array('type' => 'url', 'label' => 'Hintergrundbild URL', 'required' => true),
                    'overlay_opacity' => array('type' => 'range', 'label' => 'Overlay-Transparenz', 'min' => 0, 'max' => 100, 'default' => 50),
                    'text_color' => array('type' => 'color', 'label' => 'Textfarbe', 'default' => '#ffffff'),
                    'link_url' => array('type' => 'url', 'label' => 'Link URL'),
                    'cta_text' => array('type' => 'text', 'label' => 'CTA Button Text')
                ),
                'template' => 'hero-image-overlay'
            ),
            
            'hero_split' => array(
                'name' => 'Split Hero',
                'description' => 'Geteilter Hero: Text links, Bild rechts',
                'fields' => array(
                    'headline' => array('type' => 'text', 'label' => 'Hauptüberschrift', 'required' => true),
                    'subtitle' => array('type' => 'textarea', 'label' => 'Unterüberschrift'),
                    'description' => array('type' => 'textarea', 'label' => 'Beschreibung'),
                    'image_url' => array('type' => 'url', 'label' => 'Bild URL', 'required' => true),
                    'image_alt' => array('type' => 'text', 'label' => 'Bild Alt-Text'),
                    'cta_primary' => array('type' => 'text', 'label' => 'Primärer CTA'),
                    'cta_primary_url' => array('type' => 'url', 'label' => 'Primärer CTA URL'),
                    'cta_secondary' => array('type' => 'text', 'label' => 'Sekundärer CTA'),
                    'cta_secondary_url' => array('type' => 'url', 'label' => 'Sekundärer CTA URL')
                ),
                'template' => 'hero-split'
            ),
            
            'hero_video' => array(
                'name' => 'Video Hero',
                'description' => 'Hero mit Video-Hintergrund',
                'fields' => array(
                    'headline' => array('type' => 'text', 'label' => 'Hauptüberschrift', 'required' => true),
                    'subtitle' => array('type' => 'textarea', 'label' => 'Unterüberschrift'),
                    'video_url' => array('type' => 'url', 'label' => 'Video URL (MP4)', 'required' => true),
                    'video_poster' => array('type' => 'url', 'label' => 'Video Poster Bild'),
                    'overlay_color' => array('type' => 'color', 'label' => 'Overlay Farbe', 'default' => '#000000'),
                    'overlay_opacity' => array('type' => 'range', 'label' => 'Overlay Transparenz', 'min' => 0, 'max' => 100, 'default' => 40),
                    'autoplay' => array('type' => 'checkbox', 'label' => 'Autoplay aktivieren'),
                    'muted' => array('type' => 'checkbox', 'label' => 'Stumm abspielen', 'default' => true)
                ),
                'template' => 'hero-video'
            ),
            
            'hero_minimal' => array(
                'name' => 'Minimal Hero',
                'description' => 'Minimalistischer Hero nur mit Text',
                'fields' => array(
                    'headline' => array('type' => 'text', 'label' => 'Hauptüberschrift', 'required' => true),
                    'subtitle' => array('type' => 'textarea', 'label' => 'Unterüberschrift'),
                    'background_color' => array('type' => 'color', 'label' => 'Hintergrundfarbe', 'default' => '#f8f9fa'),
                    'text_color' => array('type' => 'color', 'label' => 'Textfarbe', 'default' => '#333333'),
                    'text_align' => array('type' => 'select', 'label' => 'Textausrichtung', 'options' => array('left' => 'Links', 'center' => 'Zentriert', 'right' => 'Rechts'), 'default' => 'center'),
                    'padding_top' => array('type' => 'range', 'label' => 'Padding oben (px)', 'min' => 20, 'max' => 200, 'default' => 80),
                    'padding_bottom' => array('type' => 'range', 'label' => 'Padding unten (px)', 'min' => 20, 'max' => 200, 'default' => 80)
                ),
                'template' => 'hero-minimal'
            ),
            
            'hero_stats' => array(
                'name' => 'Statistics Hero',
                'description' => 'Hero mit Statistiken und Zahlen',
                'fields' => array(
                    'headline' => array('type' => 'text', 'label' => 'Hauptüberschrift', 'required' => true),
                    'subtitle' => array('type' => 'textarea', 'label' => 'Unterüberschrift'),
                    'stat_1_number' => array('type' => 'text', 'label' => 'Statistik 1 - Zahl'),
                    'stat_1_label' => array('type' => 'text', 'label' => 'Statistik 1 - Label'),
                    'stat_2_number' => array('type' => 'text', 'label' => 'Statistik 2 - Zahl'),
                    'stat_2_label' => array('type' => 'text', 'label' => 'Statistik 2 - Label'),
                    'stat_3_number' => array('type' => 'text', 'label' => 'Statistik 3 - Zahl'),
                    'stat_3_label' => array('type' => 'text', 'label' => 'Statistik 3 - Label'),
                    'stat_4_number' => array('type' => 'text', 'label' => 'Statistik 4 - Zahl'),
                    'stat_4_label' => array('type' => 'text', 'label' => 'Statistik 4 - Label'),
                    'background_gradient' => array('type' => 'checkbox', 'label' => 'Gradient Hintergrund aktivieren')
                ),
                'template' => 'hero-stats'
            ),
            
            'hero_countdown' => array(
                'name' => 'Countdown Hero',
                'description' => 'Hero mit Countdown-Timer',
                'fields' => array(
                    'headline' => array('type' => 'text', 'label' => 'Hauptüberschrift', 'required' => true),
                    'subtitle' => array('type' => 'textarea', 'label' => 'Unterüberschrift'),
                    'countdown_date' => array('type' => 'datetime-local', 'label' => 'Countdown Datum', 'required' => true),
                    'countdown_text' => array('type' => 'text', 'label' => 'Countdown Text', 'default' => 'Noch'),
                    'expired_text' => array('type' => 'text', 'label' => 'Text nach Ablauf', 'default' => 'Angebot abgelaufen'),
                    'background_color' => array('type' => 'color', 'label' => 'Hintergrundfarbe', 'default' => '#1a365d'),
                    'text_color' => array('type' => 'color', 'label' => 'Textfarbe', 'default' => '#ffffff'),
                    'accent_color' => array('type' => 'color', 'label' => 'Akzentfarbe', 'default' => '#3182ce')
                ),
                'template' => 'hero-countdown'
            ),
            
            'hero_testimonial' => array(
                'name' => 'Testimonial Hero',
                'description' => 'Hero mit Kundenbewertung',
                'fields' => array(
                    'headline' => array('type' => 'text', 'label' => 'Hauptüberschrift', 'required' => true),
                    'testimonial_text' => array('type' => 'textarea', 'label' => 'Testimonial Text', 'required' => true),
                    'customer_name' => array('type' => 'text', 'label' => 'Kundenname', 'required' => true),
                    'customer_title' => array('type' => 'text', 'label' => 'Kundentitel/Position'),
                    'customer_image' => array('type' => 'url', 'label' => 'Kundenbild URL'),
                    'rating' => array('type' => 'range', 'label' => 'Bewertung (Sterne)', 'min' => 1, 'max' => 5, 'default' => 5),
                    'background_image' => array('type' => 'url', 'label' => 'Hintergrundbild URL'),
                    'company_logo' => array('type' => 'url', 'label' => 'Firmenlogo URL')
                ),
                'template' => 'hero-testimonial'
            ),
            
            'hero_newsletter' => array(
                'name' => 'Newsletter Hero',
                'description' => 'Hero mit Newsletter-Anmeldung',
                'fields' => array(
                    'headline' => array('type' => 'text', 'label' => 'Hauptüberschrift', 'required' => true),
                    'subtitle' => array('type' => 'textarea', 'label' => 'Unterüberschrift'),
                    'form_text' => array('type' => 'text', 'label' => 'Formular Beschreibung'),
                    'placeholder_email' => array('type' => 'text', 'label' => 'E-Mail Placeholder', 'default' => 'Ihre E-Mail Adresse'),
                    'button_text' => array('type' => 'text', 'label' => 'Button Text', 'default' => 'Jetzt anmelden'),
                    'privacy_text' => array('type' => 'textarea', 'label' => 'Datenschutz Text'),
                    'success_message' => array('type' => 'textarea', 'label' => 'Erfolgsmeldung'),
                    'mailchimp_action' => array('type' => 'url', 'label' => 'Mailchimp Action URL'),
                    'background_gradient' => array('type' => 'checkbox', 'label' => 'Gradient Hintergrund')
                ),
                'template' => 'hero-newsletter'
            ),
            
            'hero_price_comparison' => array(
                'name' => 'Price Comparison Hero',
                'description' => 'Hero mit Preisvergleich (Trading-fokussiert)',
                'fields' => array(
                    'headline' => array('type' => 'text', 'label' => 'Hauptüberschrift', 'required' => true),
                    'subtitle' => array('type' => 'textarea', 'label' => 'Unterüberschrift'),
                    'old_price' => array('type' => 'text', 'label' => 'Alter Preis'),
                    'new_price' => array('type' => 'text', 'label' => 'Neuer Preis', 'required' => true),
                    'currency' => array('type' => 'text', 'label' => 'Währung', 'default' => '€'),
                    'discount_percent' => array('type' => 'text', 'label' => 'Rabatt Prozent'),
                    'features_list' => array('type' => 'textarea', 'label' => 'Features (eine pro Zeile)'),
                    'cta_text' => array('type' => 'text', 'label' => 'CTA Button Text', 'default' => 'Jetzt kaufen'),
                    'cta_url' => array('type' => 'url', 'label' => 'CTA URL'),
                    'guarantee_text' => array('type' => 'text', 'label' => 'Garantie Text'),
                    'accent_color' => array('type' => 'color', 'label' => 'Akzentfarbe', 'default' => '#28a745')
                ),
                'template' => 'hero-price-comparison'
            )
        );
        
        // CONTENT LAYOUT BLOCKS
        $this->blocks['content'] = array(
            'content_text' => array(
                'name' => 'Text Block',
                'description' => 'Einfacher Textblock mit WSJ-Formatierung',
                'fields' => array(
                    'content' => array('type' => 'editor', 'label' => 'Inhalt', 'required' => true),
                    'text_size' => array('type' => 'select', 'label' => 'Textgröße', 'options' => array('small' => 'Klein', 'normal' => 'Normal', 'large' => 'Groß'), 'default' => 'normal'),
                    'text_align' => array('type' => 'select', 'label' => 'Textausrichtung', 'options' => array('left' => 'Links', 'center' => 'Zentriert', 'right' => 'Rechts', 'justify' => 'Blocksatz'), 'default' => 'left'),
                    'background_color' => array('type' => 'color', 'label' => 'Hintergrundfarbe'),
                    'padding' => array('type' => 'range', 'label' => 'Innenabstand', 'min' => 0, 'max' => 100, 'default' => 20)
                ),
                'template' => 'content-text'
            ),
            
            'content_two_thirds_one_third' => array(
                'name' => '2/3 + 1/3 Layout',
                'description' => 'Zwei Drittel Hauptinhalt, ein Drittel Sidebar',
                'fields' => array(
                    'main_content' => array('type' => 'editor', 'label' => 'Hauptinhalt (2/3)', 'required' => true),
                    'sidebar_content' => array('type' => 'editor', 'label' => 'Sidebar Inhalt (1/3)', 'required' => true),
                    'sidebar_background' => array('type' => 'color', 'label' => 'Sidebar Hintergrund', 'default' => '#f8f9fa'),
                    'gap_size' => array('type' => 'range', 'label' => 'Abstand zwischen Spalten', 'min' => 10, 'max' => 60, 'default' => 30),
                    'mobile_stack' => array('type' => 'checkbox', 'label' => 'Auf Mobile untereinander', 'default' => true)
                ),
                'template' => 'content-two-thirds-one-third'
            ),
            
            'content_half_half' => array(
                'name' => '1/2 + 1/2 Layout',
                'description' => 'Zwei gleichgroße Spalten',
                'fields' => array(
                    'left_content' => array('type' => 'editor', 'label' => 'Linker Inhalt', 'required' => true),
                    'right_content' => array('type' => 'editor', 'label' => 'Rechter Inhalt', 'required' => true),
                    'vertical_align' => array('type' => 'select', 'label' => 'Vertikale Ausrichtung', 'options' => array('top' => 'Oben', 'center' => 'Mitte', 'bottom' => 'Unten'), 'default' => 'top'),
                    'gap_size' => array('type' => 'range', 'label' => 'Abstand zwischen Spalten', 'min' => 10, 'max' => 60, 'default' => 30),
                    'background_color' => array('type' => 'color', 'label' => 'Hintergrundfarbe')
                ),
                'template' => 'content-half-half'
            ),
            
            'content_pros_cons' => array(
                'name' => 'Pros & Cons',
                'description' => 'Vor- und Nachteile Vergleich',
                'fields' => array(
                    'title' => array('type' => 'text', 'label' => 'Überschrift'),
                    'pros_title' => array('type' => 'text', 'label' => 'Vorteile Titel', 'default' => 'Vorteile'),
                    'pros_list' => array('type' => 'textarea', 'label' => 'Vorteile (eine pro Zeile)', 'required' => true),
                    'cons_title' => array('type' => 'text', 'label' => 'Nachteile Titel', 'default' => 'Nachteile'),
                    'cons_list' => array('type' => 'textarea', 'label' => 'Nachteile (eine pro Zeile)', 'required' => true),
                    'pros_color' => array('type' => 'color', 'label' => 'Vorteile Farbe', 'default' => '#28a745'),
                    'cons_color' => array('type' => 'color', 'label' => 'Nachteile Farbe', 'default' => '#dc3545'),
                    'show_icons' => array('type' => 'checkbox', 'label' => 'Icons anzeigen', 'default' => true)
                ),
                'template' => 'content-pros-cons'
            ),
            
            'content_image_text' => array(
                'name' => 'Bild + Text',
                'description' => 'Bild mit danebenstehenden Text',
                'fields' => array(
                    'image_url' => array('type' => 'url', 'label' => 'Bild URL', 'required' => true),
                    'image_alt' => array('type' => 'text', 'label' => 'Bild Alt-Text'),
                    'image_position' => array('type' => 'select', 'label' => 'Bild Position', 'options' => array('left' => 'Links', 'right' => 'Rechts'), 'default' => 'left'),
                    'image_size' => array('type' => 'select', 'label' => 'Bildgröße', 'options' => array('small' => 'Klein (30%)', 'medium' => 'Mittel (40%)', 'large' => 'Groß (50%)'), 'default' => 'medium'),
                    'text_content' => array('type' => 'editor', 'label' => 'Text Inhalt', 'required' => true),
                    'image_border' => array('type' => 'checkbox', 'label' => 'Bildrahmen anzeigen'),
                    'image_shadow' => array('type' => 'checkbox', 'label' => 'Bildschatten anzeigen')
                ),
                'template' => 'content-image-text'
            ),
            
            'content_quote' => array(
                'name' => 'Zitat Block',
                'description' => 'Hervorgehobenes Zitat',
                'fields' => array(
                    'quote_text' => array('type' => 'textarea', 'label' => 'Zitat Text', 'required' => true),
                    'quote_author' => array('type' => 'text', 'label' => 'Autor'),
                    'quote_source' => array('type' => 'text', 'label' => 'Quelle'),
                    'quote_style' => array('type' => 'select', 'label' => 'Zitat Stil', 'options' => array('classic' => 'Klassisch', 'modern' => 'Modern', 'boxed' => 'Umrahmt'), 'default' => 'classic'),
                    'background_color' => array('type' => 'color', 'label' => 'Hintergrundfarbe', 'default' => '#f8f9fa'),
                    'text_color' => array('type' => 'color', 'label' => 'Textfarbe', 'default' => '#333333'),
                    'accent_color' => array('type' => 'color', 'label' => 'Akzentfarbe', 'default' => '#3182ce')
                ),
                'template' => 'content-quote'
            ),
            
            'content_cta' => array(
                'name' => 'Call-to-Action',
                'description' => 'Handlungsaufforderung Block',
                'fields' => array(
                    'headline' => array('type' => 'text', 'label' => 'Überschrift', 'required' => true),
                    'description' => array('type' => 'textarea', 'label' => 'Beschreibung'),
                    'button_text' => array('type' => 'text', 'label' => 'Button Text', 'required' => true),
                    'button_url' => array('type' => 'url', 'label' => 'Button URL', 'required' => true),
                    'button_style' => array('type' => 'select', 'label' => 'Button Stil', 'options' => array('primary' => 'Primär', 'secondary' => 'Sekundär', 'outline' => 'Umrandet'), 'default' => 'primary'),
                    'background_gradient' => array('type' => 'checkbox', 'label' => 'Gradient Hintergrund'),
                    'center_align' => array('type' => 'checkbox', 'label' => 'Zentriert ausrichten', 'default' => true),
                    'background_color' => array('type' => 'color', 'label' => 'Hintergrundfarbe', 'default' => '#1a365d'),
                    'text_color' => array('type' => 'color', 'label' => 'Textfarbe', 'default' => '#ffffff')
                ),
                'template' => 'content-cta'
            ),
            
            'content_table' => array(
                'name' => 'Tabelle',
                'description' => 'Daten-Tabelle mit WSJ-Styling',
                'fields' => array(
                    'table_title' => array('type' => 'text', 'label' => 'Tabellen Titel'),
                    'table_headers' => array('type' => 'text', 'label' => 'Spalten-Überschriften (Komma-getrennt)', 'required' => true),
                    'table_data' => array('type' => 'textarea', 'label' => 'Tabellen-Daten (eine Zeile pro Reihe, Komma-getrennt)', 'required' => true),
                    'striped_rows' => array('type' => 'checkbox', 'label' => 'Abwechselnde Zeilenfarben', 'default' => true),
                    'hover_effect' => array('type' => 'checkbox', 'label' => 'Hover-Effekt', 'default' => true),
                    'compact_style' => array('type' => 'checkbox', 'label' => 'Kompakter Stil'),
                    'header_background' => array('type' => 'color', 'label' => 'Kopfzeilen Hintergrund', 'default' => '#1a365d'),
                    'header_text_color' => array('type' => 'color', 'label' => 'Kopfzeilen Textfarbe', 'default' => '#ffffff')
                ),
                'template' => 'content-table'
            ),
            
            'content_accordion' => array(
                'name' => 'Accordion/FAQ',
                'description' => 'Aufklappbare Inhalts-Bereiche',
                'fields' => array(
                    'accordion_title' => array('type' => 'text', 'label' => 'Accordion Titel'),
                    'items' => array('type' => 'repeater', 'label' => 'Accordion Items', 'fields' => array(
                        'title' => array('type' => 'text', 'label' => 'Item Titel', 'required' => true),
                        'content' => array('type' => 'textarea', 'label' => 'Item Inhalt', 'required' => true)
                    )),
                    'allow_multiple' => array('type' => 'checkbox', 'label' => 'Mehrere Items gleichzeitig öffnen'),
                    'first_open' => array('type' => 'checkbox', 'label' => 'Erstes Item standardmäßig öffnen', 'default' => true),
                    'border_style' => array('type' => 'select', 'label' => 'Rahmen Stil', 'options' => array('none' => 'Kein', 'light' => 'Hell', 'dark' => 'Dunkel'), 'default' => 'light')
                ),
                'template' => 'content-accordion'
            ),
            
            'content_steps' => array(
                'name' => 'Schritt-für-Schritt',
                'description' => 'Nummerierte Anleitung',
                'fields' => array(
                    'steps_title' => array('type' => 'text', 'label' => 'Schritte Titel'),
                    'steps_intro' => array('type' => 'textarea', 'label' => 'Einleitungstext'),
                    'steps' => array('type' => 'repeater', 'label' => 'Schritte', 'fields' => array(
                        'step_title' => array('type' => 'text', 'label' => 'Schritt Titel', 'required' => true),
                        'step_content' => array('type' => 'textarea', 'label' => 'Schritt Beschreibung', 'required' => true),
                        'step_image' => array('type' => 'url', 'label' => 'Schritt Bild (optional)')
                    )),
                    'layout_style' => array('type' => 'select', 'label' => 'Layout Stil', 'options' => array('vertical' => 'Vertikal', 'horizontal' => 'Horizontal'), 'default' => 'vertical'),
                    'number_style' => array('type' => 'select', 'label' => 'Nummern Stil', 'options' => array('circle' => 'Kreis', 'square' => 'Quadrat', 'none' => 'Keine'), 'default' => 'circle'),
                    'accent_color' => array('type' => 'color', 'label' => 'Akzentfarbe', 'default' => '#3182ce')
                ),
                'template' => 'content-steps'
            )
        );
        
        // SPECIAL BLOCKS FOR FOREX/TRADING
        $this->blocks['trading'] = array(
            'trading_performance' => array(
                'name' => 'Trading Performance',
                'description' => 'Performance-Statistiken für Trading-Strategien',
                'fields' => array(
                    'strategy_name' => array('type' => 'text', 'label' => 'Strategie Name', 'required' => true),
                    'total_return' => array('type' => 'text', 'label' => 'Gesamtrendite'),
                    'monthly_return' => array('type' => 'text', 'label' => 'Monatliche Rendite'),
                    'max_drawdown' => array('type' => 'text', 'label' => 'Maximaler Drawdown'),
                    'win_rate' => array('type' => 'text', 'label' => 'Trefferquote'),
                    'profit_factor' => array('type' => 'text', 'label' => 'Profit Faktor'),
                    'total_trades' => array('type' => 'text', 'label' => 'Anzahl Trades'),
                    'chart_image' => array('type' => 'url', 'label' => 'Performance Chart'),
                    'risk_warning' => array('type' => 'textarea', 'label' => 'Risikohinweis'),
                    'show_disclaimer' => array('type' => 'checkbox', 'label' => 'Standard-Disclaimer anzeigen', 'default' => true)
                ),
                'template' => 'trading-performance'
            ),
            
            'broker_comparison' => array(
                'name' => 'Broker Vergleich',
                'description' => 'Vergleichstabelle für Forex Broker',
                'fields' => array(
                    'comparison_title' => array('type' => 'text', 'label' => 'Vergleich Titel'),
                    'brokers' => array('type' => 'repeater', 'label' => 'Broker', 'fields' => array(
                        'broker_name' => array('type' => 'text', 'label' => 'Broker Name', 'required' => true),
                        'broker_logo' => array('type' => 'url', 'label' => 'Broker Logo'),
                        'min_deposit' => array('type' => 'text', 'label' => 'Mindesteinzahlung'),
                        'spread_eurusd' => array('type' => 'text', 'label' => 'EUR/USD Spread'),
                        'leverage' => array('type' => 'text', 'label' => 'Hebel'),
                        'regulation' => array('type' => 'text', 'label' => 'Regulierung'),
                        'rating' => array('type' => 'range', 'label' => 'Bewertung', 'min' => 1, 'max' => 5),
                        'bonus' => array('type' => 'text', 'label' => 'Bonus'),
                        'review_url' => array('type' => 'url', 'label' => 'Review URL'),
                        'signup_url' => array('type' => 'url', 'label' => 'Anmelde URL')
                    )),
                    'show_rating' => array('type' => 'checkbox', 'label' => 'Bewertungen anzeigen', 'default' => true),
                    'highlight_best' => array('type' => 'checkbox', 'label' => 'Besten Broker hervorheben', 'default' => true)
                ),
                'template' => 'broker-comparison'
            ),
            
            'economic_calendar' => array(
                'name' => 'Wirtschaftskalender',
                'description' => 'Kommende Wirtschaftsereignisse',
                'fields' => array(
                    'calendar_title' => array('type' => 'text', 'label' => 'Kalender Titel', 'default' => 'Wirtschaftskalender'),
                    'events' => array('type' => 'repeater', 'label' => 'Ereignisse', 'fields' => array(
                        'event_time' => array('type' => 'time', 'label' => 'Uhrzeit', 'required' => true),
                        'event_currency' => array('type' => 'text', 'label' => 'Währung', 'required' => true),
                        'event_name' => array('type' => 'text', 'label' => 'Ereignis Name', 'required' => true),
                        'event_impact' => array('type' => 'select', 'label' => 'Auswirkung', 'options' => array('low' => 'Niedrig', 'medium' => 'Mittel', 'high' => 'Hoch'), 'default' => 'medium'),
                        'previous_value' => array('type' => 'text', 'label' => 'Vorheriger Wert'),
                        'forecast_value' => array('type' => 'text', 'label' => 'Prognose'),
                        'actual_value' => array('type' => 'text', 'label' => 'Aktueller Wert')
                    )),
                    'show_timezone' => array('type' => 'checkbox', 'label' => 'Zeitzone anzeigen', 'default' => true),
                    'timezone' => array('type' => 'text', 'label' => 'Zeitzone', 'default' => 'CET'),
                    'compact_view' => array('type' => 'checkbox', 'label' => 'Kompakte Ansicht')
                ),
                'template' => 'economic-calendar'
            )
        );
    }
    
    /**
     * Get all available blocks
     */
    public function get_blocks() {
        return $this->blocks;
    }
    
    /**
     * Get blocks by category
     */
    public function get_blocks_by_category($category) {
        return isset($this->blocks[$category]) ? $this->blocks[$category] : array();
    }
    
    /**
     * Get specific block configuration
     */
    public function get_block($category, $block_id) {
        return isset($this->blocks[$category][$block_id]) ? $this->blocks[$category][$block_id] : null;
    }
    
    /**
     * Get all block categories
     */
    public function get_categories() {
        return array_keys($this->blocks);
    }
    
    /**
     * Validate block data against its configuration
     */
    public function validate_block_data($category, $block_id, $data) {
        $block = $this->get_block($category, $block_id);
        if (!$block) {
            return array('valid' => false, 'errors' => array('Block nicht gefunden'));
        }
        
        $errors = array();
        
        foreach ($block['fields'] as $field_id => $field_config) {
            if (isset($field_config['required']) && $field_config['required']) {
                if (empty($data[$field_id])) {
                    $errors[] = $field_config['label'] . ' ist erforderlich';
                }
            }
            
            if (!empty($data[$field_id])) {
                switch ($field_config['type']) {
                    case 'url':
                        if (!filter_var($data[$field_id], FILTER_VALIDATE_URL)) {
                            $errors[] = $field_config['label'] . ' muss eine gültige URL sein';
                        }
                        break;
                    case 'email':
                        if (!filter_var($data[$field_id], FILTER_VALIDATE_EMAIL)) {
                            $errors[] = $field_config['label'] . ' muss eine gültige E-Mail sein';
                        }
                        break;
                    case 'color':
                        if (!preg_match('/^#[a-f0-9]{6}$/i', $data[$field_id])) {
                            $errors[] = $field_config['label'] . ' muss ein gültiger Hex-Farbcode sein';
                        }
                        break;
                    case 'range':
                        $value = intval($data[$field_id]);
                        if ($value < $field_config['min'] || $value > $field_config['max']) {
                            $errors[] = $field_config['label'] . ' muss zwischen ' . $field_config['min'] . ' und ' . $field_config['max'] . ' liegen';
                        }
                        break;
                }
            }
        }
        
        return array(
            'valid' => empty($errors),
            'errors' => $errors
        );
    }
    
    /**
     * Get default values for a block
     */
    public function get_block_defaults($category, $block_id) {
        $block = $this->get_block($category, $block_id);
        if (!$block) {
            return array();
        }
        
        $defaults = array();
        foreach ($block['fields'] as $field_id => $field_config) {
            if (isset($field_config['default'])) {
                $defaults[$field_id] = $field_config['default'];
            }
        }
        
        return $defaults;
    }
}