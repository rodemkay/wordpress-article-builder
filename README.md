# Article Builder Plugin für WordPress

## 🎯 Übersicht

Das **Article Builder Plugin** ist ein professionelles Backend-Tool für WordPress zur Erstellung von HTML-Artikeln mit einem modularen Block-System. Es wurde speziell für Trading/Forex-Websites entwickelt und bietet KI-Integration für automatische Content-Generierung.

## ✨ Features

### Block-System
- **22+ vordefinierte Block-Typen**
- **10 Hero-Section Varianten** (Classic, Split, Video, Countdown, etc.)
- **Content-Layouts** (2/3+1/3, 1/2+1/2, Pros/Cons, etc.)
- **Trading-spezifische Blöcke** (Performance, Broker-Vergleich, Market Data)
- **Drag & Drop Interface** für einfache Block-Anordnung

### WordPress-Integration
- **Post-Type Auswahl** (Blog-Beitrag oder Seite)
- **Kategorie- und Tag-Management**
- **SEO-Metadaten** (Title, Description, Keywords)
- **Featured Image** Einstellung
- **Autor-Zuordnung**

### KI-Integration
- **Claude API** (bevorzugt) oder **OpenAI API** Support
- **Content-Generierung** in deutscher Sprache
- **Trading/Forex-spezialisierte Prompts**
- **Artikel-Struktur-Vorschläge**
- **SEO-Optimierung** automatisch

### Export-Funktionen
- **HTML-Export** mit Copy-to-Clipboard
- **WordPress-Metadaten** als Kommentare
- **JSON Import/Export** für Backup
- **Als Draft speichern** Option

## 📦 Installation

1. Plugin-Ordner nach `/wp-content/plugins/` kopieren
2. Im WordPress-Admin unter "Plugins" aktivieren
3. Neuer Menüpunkt "Article Builder" erscheint im Admin

## 🚀 Verwendung

### Neuen Artikel erstellen

1. **WordPress Admin** → **Article Builder** → **Neuer Artikel**
2. **WordPress-Settings** konfigurieren (Titel, Kategorien, Tags)
3. **Blöcke hinzufügen** aus der Block-Bibliothek
4. **Inhalte bearbeiten** oder mit **KI generieren**
5. **HTML kopieren** oder als **Draft speichern**

### Block-Typen

#### Hero Sections
- Classic Hero (WSJ-Style)
- Image Overlay Hero
- Split Hero (Text/Bild)
- Video Hero
- Minimal Hero
- Statistics Hero
- Countdown Hero
- Testimonial Hero
- Newsletter Hero
- Price Comparison Hero

#### Content Blocks
- Text Block
- 2/3 + 1/3 Layout
- 1/2 + 1/2 Layout
- Pros & Cons
- Image + Text
- Quote Block
- Call-to-Action
- Table
- Accordion/FAQ
- Steps

#### Trading Blocks
- Trading Performance
- Broker Comparison
- Economic Calendar
- Market Data
- Trading Tips

## 🤖 KI-Integration einrichten

1. **Article Builder** → **Einstellungen**
2. **Claude API Key** oder **OpenAI API Key** eingeben
3. **Model** auswählen (claude-3-sonnet oder gpt-4)
4. **Sprache** auf Deutsch setzen
5. **Speichern**

### KI-Features nutzen

- **Content generieren**: Bei jedem Block "KI-Generieren" Button
- **Struktur-Vorschlag**: "KI-Struktur" für komplette Artikel-Struktur
- **SEO-Optimierung**: Automatische Meta-Tags und Keywords
- **Kategorien**: KI schlägt passende Kategorien vor

## 📊 Datenbank-Schema

Das Plugin erstellt zwei Tabellen:

### wp_article_builder_projects
- Speichert Artikel-Projekte
- Block-Konfigurationen als JSON
- WordPress-Metadaten
- SEO-Einstellungen

### wp_article_builder_templates
- Wiederverwendbare Block-Templates
- Vorkonfigurierte Layouts
- Usage-Statistiken

## 🔧 Entwickler-Informationen

### Dateistruktur
```
article-builder-plugin/
├── article-builder.php      # Haupt-Plugin-Datei
├── includes/
│   ├── class-admin.php      # Admin-Interface
│   ├── class-ajax.php       # AJAX-Handler
│   ├── class-api.php        # REST API
│   ├── class-ai.php         # KI-Integration
│   ├── class-blocks.php     # Block-Definitionen
│   ├── class-database.php   # Datenbank-Operationen
│   └── class-generator.php  # HTML-Generator
├── assets/
│   ├── css/
│   │   └── admin.css        # Admin-Styles
│   └── js/
│       └── builder.js       # JavaScript
└── templates/               # Block-Templates
```

### REST API Endpoints
- `GET /wp-json/article-builder/v1/projects` - Alle Projekte
- `POST /wp-json/article-builder/v1/projects` - Neues Projekt
- `GET /wp-json/article-builder/v1/templates` - Templates
- `POST /wp-json/article-builder/v1/preview` - HTML-Preview

### AJAX Actions
- `ab_save_project` - Projekt speichern
- `ab_load_project` - Projekt laden
- `ab_generate_preview` - Preview generieren
- `ab_get_categories` - Kategorien abrufen

## 📝 Beispiel-Workflow

### Trading-Strategie Artikel

1. **Neuer Artikel** → Typ: "Blog-Beitrag"
2. **Titel**: "Scalping-Strategie für EUR/USD"
3. **Kategorien**: Trading Strategien, Forex
4. **Block 1**: Hero Classic mit Chart-Bild
5. **Block 2**: 2/3 Einleitung + 1/3 Inhaltsverzeichnis
6. **Block 3**: Trading Performance Block
7. **Block 4**: Pros & Cons
8. **Block 5**: Step-by-Step Anleitung
9. **Block 6**: Call-to-Action (Newsletter)
10. **HTML kopieren** und in WordPress einfügen

## 🔐 Sicherheit

- **Nonce-Verification** für alle AJAX-Requests
- **Capability-Checks** für Benutzerberechtigungen
- **Sanitization** aller Eingaben
- **Prepared Statements** für Datenbank-Queries
- **API-Key Verschlüsselung** in WordPress Options

## 📈 Performance

- **Lazy Loading** für Block-Bibliothek
- **Object Caching** für häufige Queries
- **Minified Assets** für schnelles Laden
- **Transient-basiertes** Rate-Limiting

## 🆘 Support

Bei Fragen oder Problemen:
- **Admin** → **Article Builder** → **Hilfe**
- GitHub Issues: [Link zum Repository]
- E-Mail: support@forexsignale.trade

## 📄 Lizenz

GPL v2 oder später

## 🏆 Credits

Entwickelt für **ForexSignale Magazine**
- Website: https://forexsignale.trade
- Version: 1.0.0
- Autor: ForexSignale Team

---

**Hinweis**: Dieses Plugin arbeitet ausschließlich im Backend und nimmt KEINE Änderungen an der Live-Website vor. Alle generierten Inhalte müssen manuell eingefügt werden.