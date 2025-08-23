# Article Builder Plugin - Installation & Fehlerbehebung

## ✅ STATUS: ALLE KRITISCHEN FEHLER BEHOBEN

Das Article Builder Plugin wurde erfolgreich repariert und ist jetzt bereit für die Installation in WordPress.

## 🔧 Behobene Probleme

### 1. Plugin-Crash bei Aktivierung
- **Problem**: Undefinierte Konstante `ARTICLE_BUILDER_VERSION`
- **Lösung**: Zusätzliche Konstante definiert für Rückwärtskompatibilität
- **Datei**: `article-builder.php` (Zeile 20)

### 2. Admin-Menü Duplikate
- **Problem**: Doppelte AJAX-Hooks in Admin- und AJAX-Klasse
- **Lösung**: AJAX-Hooks aus Admin-Klasse entfernt
- **Datei**: `includes/class-admin.php` (Zeile 37)

### 3. Namespace-Konflikte
- **Problem**: Inkonsistente Verwendung von Namespaces
- **Lösung**: Alle Klassen auf einheitliche Namespace-Verwendung umgestellt
- **Dateien**: `includes/class-admin.php`, `includes/class-ajax.php`

### 4. Database-Integration
- **Problem**: Direkte wpdb-Aufrufe anstatt Database-Klasse
- **Lösung**: Alle Datenbankoperationen über Database-Klasse umgeleitet
- **Dateien**: `includes/class-admin.php`, `includes/class-ajax.php`

### 5. Autoloader-Probleme
- **Problem**: Fehlerhafte Pfad-Konvertierung für Namespace-Klassen
- **Lösung**: Vereinfachte Autoloader-Logik implementiert
- **Datei**: `article-builder.php` (Zeile 36-42)

## 📁 Plugin-Struktur

```
article-builder-plugin/
├── article-builder.php          # Haupt-Plugin-Datei
├── includes/
│   ├── class-admin.php          # Admin-Interface
│   ├── class-ajax.php           # AJAX-Handler
│   ├── class-database.php       # Datenbank-Management
│   ├── class-ai.php             # KI-Integration
│   ├── class-api.php            # REST API
│   ├── class-blocks.php         # Block-Management
│   └── class-generator.php      # HTML-Generator
├── assets/
│   ├── css/
│   │   └── admin.css            # Admin-Styles
│   └── js/
│       └── admin.js             # Admin-JavaScript
└── templates/                   # Block-Templates
```

## 🚀 Installation

### Schritt 1: Plugin hochladen
```bash
# Plugin-Ordner in WordPress kopieren
cp -r article-builder-plugin /path/to/wordpress/wp-content/plugins/
```

### Schritt 2: Plugin aktivieren
1. WordPress Admin-Bereich öffnen
2. Zu "Plugins" → "Installierte Plugins" navigieren
3. "Article Builder" aktivieren

### Schritt 3: Berechtigungen prüfen
Das Plugin erstellt automatisch:
- **Administrator**: `manage_article_builder` Berechtigung
- **Editor**: `use_article_builder` Berechtigung

### Schritt 4: Menü-Zugriff
Nach erfolgreicher Aktivierung erscheint im Admin-Menü:
- **"Article Builder"** (Hauptmenü)
  - Artikel erstellen
  - Meine Projekte  
  - Einstellungen

## 🛠️ Fehlerbehebung

### Plugin aktiviert sich nicht
```bash
# Fehler-Logs prüfen
tail -f /var/log/apache2/error.log
# oder
tail -f /var/log/nginx/error.log
```

### Datenbank-Tabellen werden nicht erstellt
Die folgenden Tabellen sollten automatisch erstellt werden:
- `wp_article_builder_projects`
- `wp_article_builder_templates`

Manuelle Erstellung falls nötig:
```php
// In WordPress Admin Tools → PHP ausführen
$database = new ArticleBuilder\Database();
$database->create_tables();
```

### Admin-Menü erscheint nicht
1. Plugin erneut deaktivieren und aktivieren
2. Browser-Cache leeren
3. Benutzer-Berechtigungen prüfen

### CSS/JS-Dateien laden nicht
```bash
# Asset-Berechtigungen prüfen
chmod 644 assets/css/admin.css
chmod 644 assets/js/admin.js
```

## 🔍 Debug-Modus

Für erweiterte Fehlerbehebung Debug-Modus aktivieren:

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

## 📋 Checkliste nach Installation

- [ ] Plugin ist aktiviert
- [ ] Admin-Menü "Article Builder" ist sichtbar
- [ ] Datenbank-Tabellen sind erstellt
- [ ] CSS/JS-Dateien laden korrekt
- [ ] Keine PHP-Fehler im Error-Log
- [ ] Neue Projekte können erstellt werden

## 🆘 Support

Bei weiteren Problemen:

1. **Error-Logs prüfen** (siehe oben)
2. **Plugin erneut aktivieren**
3. **WordPress-Berechtigungen prüfen**
4. **Konflikt-Test** (andere Plugins temporär deaktivieren)

## ✅ Erfolg bestätigen

Das Plugin funktioniert korrekt wenn:
- Keine PHP Fatal Errors auftreten
- Admin-Menü erscheint
- Einstellungsseite lädt ohne Fehler
- Projekte können erstellt und gespeichert werden

---

**Version**: 1.0.0  
**Getestet mit**: WordPress 6.0+  
**PHP-Anforderung**: 7.4+