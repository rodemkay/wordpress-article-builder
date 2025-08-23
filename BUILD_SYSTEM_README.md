# Article Builder Plugin - React Build System

## Übersicht

Das Article Builder Plugin verfügt über ein vollständiges React 18 Build-System mit Webpack 5, Babel und SCSS-Unterstützung für die Erstellung eines visuellen Block-Editors.

## Installation & Setup

### 1. Dependencies installieren

```bash
npm install
```

### 2. Entwicklung starten

```bash
npm run dev
```

Dies startet Webpack im Watch-Modus und kompiliert Änderungen automatisch.

### 3. Development Server starten

```bash
npm start
```

Startet den Webpack Dev Server mit Hot Module Replacement auf Port 3000.

### 4. Production Build

```bash
npm run build
```

Erstellt optimierte, produktionsreife Dateien im `assets/dist/` Verzeichnis.

## Projektstruktur

```
assets/src/
├── admin/                 # Admin Interface Entry Point
│   ├── index.js          # Admin App Bootstrap
│   └── AdminApp.jsx      # Haupt-Admin-Komponente
├── editor/               # Gutenberg Block Editor
│   └── index.js          # Editor Entry Point
├── frontend/             # Frontend Rendering
│   ├── index.js          # Frontend Entry Point
│   └── ArticleRenderer.jsx
├── components/           # React Komponenten
│   ├── VisualEditor/     # Haupteditor-Interface
│   ├── BlockEditor/      # Gutenberg Integration
│   ├── Toolbar/          # Editor-Toolbar
│   ├── Sidebar/          # Einstellungs-Sidebar
│   └── Blocks/           # Block-Komponenten
├── hooks/                # Custom React Hooks
│   └── useArticleBuilder.js
├── utils/                # Utility-Funktionen
│   └── blockUtils.js
└── styles/               # SCSS-Dateien
    ├── _variables.scss   # Design Tokens
    ├── _mixins.scss      # SCSS Mixins
    ├── admin.scss        # Admin Interface
    ├── editor.scss       # Editor Interface
    ├── frontend.scss     # Frontend Styling
    └── components/       # Komponenten-Styles
```

## Build-Konfiguration

### Entry Points

Das Build-System erstellt drei separate Bundles:

1. **Admin** (`admin.js`) - Admin Interface
2. **Editor** (`editor.js`) - Gutenberg Block Editor
3. **Frontend** (`frontend.js`) - Frontend Rendering

### Webpack Features

- **Hot Module Replacement** für Entwicklung
- **Code Splitting** für optimale Performance
- **SCSS/CSS Support** mit Autoprefixer
- **Image/Font Loading** als Assets
- **Source Maps** für Debugging
- **Minification** für Production

### Babel Configuration

- React 18 mit automatic JSX runtime
- ES2021+ Features
- Class Properties Support
- Runtime Transform für Polyfills

## Verfügbare Scripts

```bash
# Entwicklung mit Watch-Modus
npm run dev

# Development Server mit HMR
npm start

# Production Build
npm run build

# Production Build mit Watch
npm run build:watch

# Code Linting
npm run lint

# Code Linting mit Auto-Fix
npm run lint:fix
```

## Block-Komponenten

Das System umfasst folgende vorgefertigte Block-Typen:

### Text-Blöcke
- **Paragraph** - Einfacher Textblock
- **Heading** - Überschriften (H1-H6)
- **Quote** - Hervorgehobene Zitate

### Medien-Blöcke
- **Image** - Bilder mit Beschriftung

### Layout-Blöcke
- **List** - Aufzählungen und nummerierte Listen
- **Code** - Syntax-highlighted Code-Blöcke
- **Divider** - Horizontale Trenner
- **Button** - Call-to-Action Buttons

## Drag & Drop

Das System verwendet React DnD für:
- Drag & Drop zwischen Blöcken
- Sortierung durch Ziehen
- Drop-Zonen für neue Blöcke
- Visual Feedback während des Draggens

## Styling-System

### SCSS-Architektur
- **Variables** - Farben, Schriften, Abstände
- **Mixins** - Wiederverwendbare Style-Komponenten
- **Components** - Komponenten-spezifische Styles
- **Responsive** - Mobile-first Breakpoints

### Design-System
- WordPress-kompatible Farben
- Einheitliche Typografie
- Konsistente Abstände
- Barrierefreie Kontraste

## Integration in WordPress

### Admin Interface
Das Admin Interface wird über einen Container-Element initialisiert:

```html
<div id="article-builder-admin"></div>
```

### Gutenberg Block
Der Block wird automatisch registriert als `article-builder/visual-block`.

### Frontend Rendering
Frontend-Container werden automatisch erkannt:

```html
<div class="article-builder-container" data-article='{"blocks":[...]}'></div>
```

## Hooks & Utils

### useArticleBuilder Hook
Zentraler Hook für Block-Management:
- `addBlock()` - Block hinzufügen
- `removeBlock()` - Block entfernen
- `updateBlock()` - Block aktualisieren
- `moveBlock()` - Block verschieben
- `saveArticle()` - Artikel speichern

### Block Utils
Utility-Funktionen für:
- Block-Erstellung und -Validierung
- Daten-Sanitization
- Import/Export von Blöcken

## Performance-Optimierungen

- **Code Splitting** nach Entry Points
- **Vendor Chunks** für Third-Party Libraries
- **React Bundle** separate für besseres Caching
- **CSS Extraction** in Production
- **Asset Optimization** für Bilder und Fonts

## Browser-Unterstützung

- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- Mobile Browsers (iOS Safari, Chrome Mobile)

## Debugging

### Development
- Source Maps aktiviert
- React Developer Tools kompatibel
- Console-Logs in Development

### Production
- Minifizierte Bundles
- Keine Console-Logs
- Error Boundaries für Fehlerbehandlung

## Erweiterung

### Neue Block-Typen hinzufügen

1. **Block-Komponente erstellen** in `components/Blocks/`
2. **Settings-Komponente erstellen** in `components/Sidebar/settings/`
3. **Registrierung** in `BlockRenderer.jsx`
4. **Default-Daten** in `useArticleBuilder.js`

### Styling anpassen

1. **Variables** in `styles/_variables.scss` anpassen
2. **Komponenten-Styles** in `styles/components/` erweitern
3. **Responsive Breakpoints** in `styles/_mixins.scss`

## Troubleshooting

### Häufige Probleme

**Build schlägt fehl:**
- Node.js Version prüfen (16+)
- `npm install` erneut ausführen
- Cache leeren: `npm run build:clean`

**React Components laden nicht:**
- Webpack Dev Server läuft?
- Browser Console auf Fehler prüfen
- Network Tab für Asset-Loading prüfen

**Styling nicht sichtbar:**
- SCSS-Compilation erfolgreich?
- CSS-Dateien im `dist/` Verzeichnis?
- Cache-Busting bei CSS-Änderungen

## Support

Bei Problemen mit dem Build-System:
1. Node.js und npm Versionen prüfen
2. Dependencies neu installieren
3. Browser Cache leeren
4. Entwickler-Tools für Debug-Information nutzen