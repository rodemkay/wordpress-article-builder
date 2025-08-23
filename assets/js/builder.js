/**
 * Article Builder Plugin - Main JavaScript
 * Moderne, interaktive Builder-Funktionalität für WordPress
 */

(function($) {
    'use strict';

    /**
     * Article Builder Hauptklasse
     */
    class ArticleBuilder {
        constructor() {
            this.blocks = [];
            this.blockIdCounter = 0;
            this.displayedPosts = [];
            this.previewMode = 'visual';
            this.isDirty = false;
            
            this.init();
        }

        /**
         * Initialisierung
         */
        init() {
            this.bindEvents();
            this.initSortable();
            this.initDragDrop();
            this.loadSavedBlocks();
            
            // Auto-save alle 30 Sekunden
            setInterval(() => {
                if (this.isDirty) {
                    this.autoSave();
                }
            }, 30000);
        }

        /**
         * Event-Binding
         */
        bindEvents() {
            const self = this;

            // Draggable Blocks aus Sidebar
            $('.draggable-block').on('click', function(e) {
                e.preventDefault();
                const blockType = $(this).data('block-type');
                self.addBlock(blockType);
            });

            // Preview Toggle
            $('.preview-toggle button').on('click', function() {
                $('.preview-toggle button').removeClass('active');
                $(this).addClass('active');
                self.previewMode = $(this).data('mode');
                self.updatePreview();
            });

            // Save Button
            $('#save-article').on('click', function(e) {
                e.preventDefault();
                self.saveArticle();
            });

            // Clear Button
            $('#clear-builder').on('click', function(e) {
                e.preventDefault();
                if (confirm('Alle Blöcke löschen? Diese Aktion kann nicht rückgängig gemacht werden.')) {
                    self.clearBuilder();
                }
            });

            // Copy to Clipboard
            $('#copy-content').on('click', function(e) {
                e.preventDefault();
                self.copyToClipboard();
            });

            // Export JSON
            $('#export-json').on('click', function(e) {
                e.preventDefault();
                self.exportJSON();
            });

            // Import JSON
            $('#import-json').on('change', function() {
                self.importJSON(this.files[0]);
            });

            // Keyboard Shortcuts
            $(document).on('keydown', function(e) {
                if (e.ctrlKey || e.metaKey) {
                    switch (e.key) {
                        case 's':
                            e.preventDefault();
                            self.saveArticle();
                            break;
                        case 'z':
                            e.preventDefault();
                            self.undo();
                            break;
                        case 'y':
                            e.preventDefault();
                            self.redo();
                            break;
                    }
                }
            });
        }

        /**
         * Sortable Funktionalität
         */
        initSortable() {
            const self = this;
            
            $('.article-drop-zone').sortable({
                placeholder: 'sortable-placeholder',
                helper: 'clone',
                tolerance: 'pointer',
                cursor: 'grabbing',
                opacity: 0.8,
                
                start: function(event, ui) {
                    ui.helper.addClass('ui-sortable-helper');
                },
                
                update: function(event, ui) {
                    self.updateBlockOrder();
                    self.markDirty();
                }
            });
        }

        /**
         * Drag & Drop von Sidebar zu Builder
         */
        initDragDrop() {
            const self = this;

            // Draggable machen
            $('.draggable-block').draggable({
                helper: 'clone',
                appendTo: 'body',
                cursor: 'grabbing',
                opacity: 0.8,
                zIndex: 1000,
                
                start: function(event, ui) {
                    $(this).addClass('dragging');
                }
            });

            // Drop Zone
            $('.article-drop-zone').droppable({
                accept: '.draggable-block',
                tolerance: 'pointer',
                
                over: function(event, ui) {
                    $(this).addClass('drag-over');
                },
                
                out: function(event, ui) {
                    $(this).removeClass('drag-over');
                },
                
                drop: function(event, ui) {
                    $(this).removeClass('drag-over');
                    $('.draggable-block').removeClass('dragging');
                    
                    const blockType = ui.draggable.data('block-type');
                    self.addBlock(blockType, event.pageY);
                }
            });
        }

        /**
         * Block hinzufügen
         */
        addBlock(blockType, insertY = null) {
            const blockId = this.generateBlockId();
            const blockData = this.getBlockTemplate(blockType);
            
            if (!blockData) {
                this.showNotification('Unbekannter Block-Typ', 'error');
                return;
            }

            const block = {
                id: blockId,
                type: blockType,
                ...blockData
            };

            // Position bestimmen
            let insertIndex = this.blocks.length;
            if (insertY) {
                insertIndex = this.calculateInsertPosition(insertY);
            }

            this.blocks.splice(insertIndex, 0, block);
            this.renderBlock(block, insertIndex);
            this.updatePreview();
            this.updateBlockCount();
            this.markDirty();
            
            this.showNotification(`Block "${blockData.title}" hinzugefügt`, 'success');
        }

        /**
         * Block-Template abrufen
         */
        getBlockTemplate(blockType) {
            const templates = {
                headline: {
                    title: 'Headline',
                    icon: 'format-text',
                    content: {
                        text: 'Neue Headline',
                        level: 'h2'
                    }
                },
                paragraph: {
                    title: 'Absatz',
                    icon: 'text',
                    content: {
                        text: 'Neuer Absatz...'
                    }
                },
                market_data: {
                    title: 'Marktdaten',
                    icon: 'chart-line',
                    content: {
                        symbols: ['EURUSD', 'GBPUSD', 'GOLD'],
                        display: 'table'
                    }
                },
                quote: {
                    title: 'Zitat',
                    icon: 'format-quote',
                    content: {
                        text: 'Inspirierendes Zitat...',
                        author: 'Autor'
                    }
                },
                trading_tip: {
                    title: 'Trading Tipp',
                    icon: 'lightbulb',
                    content: {
                        title: 'Tipp Titel',
                        text: 'Hilfreicher Trading-Tipp...',
                        level: 'info'
                    }
                },
                broker_comparison: {
                    title: 'Broker Vergleich',
                    icon: 'analytics',
                    content: {
                        brokers: ['IG', 'Plus500', 'eToro'],
                        criteria: ['Spread', 'Hebel', 'Regulierung']
                    }
                },
                call_to_action: {
                    title: 'Call-to-Action',
                    icon: 'button',
                    content: {
                        text: 'Jetzt handeln!',
                        url: '#',
                        style: 'primary'
                    }
                },
                divider: {
                    title: 'Trennlinie',
                    icon: 'minus',
                    content: {
                        style: 'simple'
                    }
                }
            };

            return templates[blockType] || null;
        }

        /**
         * Block rendern
         */
        renderBlock(block, index = null) {
            const blockHtml = this.generateBlockHtml(block);
            
            if (index !== null && index < $('.article-block').length) {
                $(blockHtml).insertBefore($(`.article-block:eq(${index})`));
            } else {
                $('.article-drop-zone').append(blockHtml);
            }

            // Event-Listener für neuen Block
            this.bindBlockEvents(block.id);
            
            // Leere Drop Zone ausblenden
            this.toggleEmptyState();
        }

        /**
         * Block HTML generieren
         */
        generateBlockHtml(block) {
            const contentHtml = this.generateBlockContent(block);
            
            return `
                <div class="article-block" data-block-id="${block.id}" data-block-type="${block.type}">
                    <div class="block-header">
                        <div class="block-title">
                            <span class="dashicons dashicons-${block.icon}"></span>
                            ${block.title}
                        </div>
                        <div class="block-controls">
                            <button class="block-control-btn duplicate" title="Duplizieren">
                                <span class="dashicons dashicons-admin-page"></span>
                            </button>
                            <button class="block-control-btn edit" title="Bearbeiten">
                                <span class="dashicons dashicons-edit"></span>
                            </button>
                            <button class="block-control-btn delete" title="Löschen">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    </div>
                    <div class="block-content">
                        ${contentHtml}
                    </div>
                </div>
            `;
        }

        /**
         * Block Content generieren
         */
        generateBlockContent(block) {
            switch (block.type) {
                case 'headline':
                    return `
                        <div class="ab-form-group">
                            <label class="ab-label">Headline Text:</label>
                            <input type="text" class="block-input headline-text" value="${block.content.text}" placeholder="Headline eingeben...">
                        </div>
                        <div class="ab-form-group">
                            <label class="ab-label">Headline Größe:</label>
                            <select class="ab-select headline-level">
                                <option value="h1" ${block.content.level === 'h1' ? 'selected' : ''}>H1 - Sehr groß</option>
                                <option value="h2" ${block.content.level === 'h2' ? 'selected' : ''}>H2 - Groß</option>
                                <option value="h3" ${block.content.level === 'h3' ? 'selected' : ''}>H3 - Mittel</option>
                                <option value="h4" ${block.content.level === 'h4' ? 'selected' : ''}>H4 - Klein</option>
                            </select>
                        </div>
                    `;

                case 'paragraph':
                    return `
                        <div class="ab-form-group">
                            <label class="ab-label">Absatz Text:</label>
                            <textarea class="block-input block-textarea paragraph-text" placeholder="Absatz eingeben...">${block.content.text}</textarea>
                        </div>
                    `;

                case 'market_data':
                    return `
                        <div class="ab-form-group">
                            <label class="ab-label">Währungspaare (kommagetrennt):</label>
                            <input type="text" class="block-input market-symbols" value="${block.content.symbols.join(', ')}" placeholder="EURUSD, GBPUSD, GOLD">
                        </div>
                        <div class="ab-form-group">
                            <label class="ab-label">Darstellung:</label>
                            <select class="ab-select market-display">
                                <option value="table" ${block.content.display === 'table' ? 'selected' : ''}>Tabelle</option>
                                <option value="cards" ${block.content.display === 'cards' ? 'selected' : ''}>Karten</option>
                                <option value="ticker" ${block.content.display === 'ticker' ? 'selected' : ''}>Ticker</option>
                            </select>
                        </div>
                    `;

                case 'quote':
                    return `
                        <div class="ab-form-group">
                            <label class="ab-label">Zitat Text:</label>
                            <textarea class="block-input block-textarea quote-text" placeholder="Zitat eingeben...">${block.content.text}</textarea>
                        </div>
                        <div class="ab-form-group">
                            <label class="ab-label">Autor:</label>
                            <input type="text" class="block-input quote-author" value="${block.content.author}" placeholder="Autor des Zitats">
                        </div>
                    `;

                case 'trading_tip':
                    return `
                        <div class="ab-form-group">
                            <label class="ab-label">Tipp Titel:</label>
                            <input type="text" class="block-input tip-title" value="${block.content.title}" placeholder="Tipp Titel">
                        </div>
                        <div class="ab-form-group">
                            <label class="ab-label">Tipp Text:</label>
                            <textarea class="block-input block-textarea tip-text" placeholder="Trading-Tipp eingeben...">${block.content.text}</textarea>
                        </div>
                        <div class="ab-form-group">
                            <label class="ab-label">Tipp Level:</label>
                            <select class="ab-select tip-level">
                                <option value="info" ${block.content.level === 'info' ? 'selected' : ''}>Info (Blau)</option>
                                <option value="success" ${block.content.level === 'success' ? 'selected' : ''}>Erfolg (Grün)</option>
                                <option value="warning" ${block.content.level === 'warning' ? 'selected' : ''}>Warnung (Gelb)</option>
                                <option value="danger" ${block.content.level === 'danger' ? 'selected' : ''}>Gefahr (Rot)</option>
                            </select>
                        </div>
                    `;

                case 'call_to_action':
                    return `
                        <div class="ab-form-group">
                            <label class="ab-label">Button Text:</label>
                            <input type="text" class="block-input cta-text" value="${block.content.text}" placeholder="Button Text">
                        </div>
                        <div class="ab-form-group">
                            <label class="ab-label">Link URL:</label>
                            <input type="url" class="block-input cta-url" value="${block.content.url}" placeholder="https://...">
                        </div>
                        <div class="ab-form-group">
                            <label class="ab-label">Button Style:</label>
                            <select class="ab-select cta-style">
                                <option value="primary" ${block.content.style === 'primary' ? 'selected' : ''}>Primär (Blau)</option>
                                <option value="secondary" ${block.content.style === 'secondary' ? 'selected' : ''}>Sekundär (Grau)</option>
                                <option value="success" ${block.content.style === 'success' ? 'selected' : ''}>Erfolg (Grün)</option>
                                <option value="warning" ${block.content.style === 'warning' ? 'selected' : ''}>Warnung (Gelb)</option>
                            </select>
                        </div>
                    `;

                default:
                    return `<p>Block-Typ "${block.type}" nicht unterstützt.</p>`;
            }
        }

        /**
         * Block Events binden
         */
        bindBlockEvents(blockId) {
            const blockElement = $(`.article-block[data-block-id="${blockId}"]`);
            const self = this;

            // Input Events
            blockElement.find('.block-input, .ab-select').on('input change', function() {
                self.updateBlockData(blockId);
                self.updatePreview();
                self.markDirty();
            });

            // Control Buttons
            blockElement.find('.delete').on('click', function() {
                if (confirm('Block löschen?')) {
                    self.deleteBlock(blockId);
                }
            });

            blockElement.find('.duplicate').on('click', function() {
                self.duplicateBlock(blockId);
            });

            blockElement.find('.edit').on('click', function() {
                self.toggleBlockEdit(blockId);
            });

            // Block Selection
            blockElement.on('click', function(e) {
                if (!$(e.target).hasClass('block-control-btn') && !$(e.target).parent().hasClass('block-control-btn')) {
                    $('.article-block').removeClass('selected');
                    $(this).addClass('selected');
                }
            });
        }

        /**
         * Block-Daten aktualisieren
         */
        updateBlockData(blockId) {
            const block = this.getBlockById(blockId);
            const blockElement = $(`.article-block[data-block-id="${blockId}"]`);
            
            if (!block) return;

            switch (block.type) {
                case 'headline':
                    block.content.text = blockElement.find('.headline-text').val();
                    block.content.level = blockElement.find('.headline-level').val();
                    break;
                    
                case 'paragraph':
                    block.content.text = blockElement.find('.paragraph-text').val();
                    break;
                    
                case 'market_data':
                    block.content.symbols = blockElement.find('.market-symbols').val().split(',').map(s => s.trim());
                    block.content.display = blockElement.find('.market-display').val();
                    break;
                    
                case 'quote':
                    block.content.text = blockElement.find('.quote-text').val();
                    block.content.author = blockElement.find('.quote-author').val();
                    break;
                    
                case 'trading_tip':
                    block.content.title = blockElement.find('.tip-title').val();
                    block.content.text = blockElement.find('.tip-text').val();
                    block.content.level = blockElement.find('.tip-level').val();
                    break;
                    
                case 'call_to_action':
                    block.content.text = blockElement.find('.cta-text').val();
                    block.content.url = blockElement.find('.cta-url').val();
                    block.content.style = blockElement.find('.cta-style').val();
                    break;
            }
        }

        /**
         * Block löschen
         */
        deleteBlock(blockId) {
            const index = this.blocks.findIndex(block => block.id === blockId);
            if (index !== -1) {
                this.blocks.splice(index, 1);
                $(`.article-block[data-block-id="${blockId}"]`).fadeOut(300, function() {
                    $(this).remove();
                });
                
                this.updatePreview();
                this.updateBlockCount();
                this.markDirty();
                this.toggleEmptyState();
                
                this.showNotification('Block gelöscht', 'success');
            }
        }

        /**
         * Block duplizieren
         */
        duplicateBlock(blockId) {
            const originalBlock = this.getBlockById(blockId);
            if (!originalBlock) return;

            const duplicatedBlock = {
                ...originalBlock,
                id: this.generateBlockId()
            };

            const originalIndex = this.blocks.findIndex(block => block.id === blockId);
            this.blocks.splice(originalIndex + 1, 0, duplicatedBlock);
            
            this.renderBlock(duplicatedBlock, originalIndex + 1);
            this.updatePreview();
            this.updateBlockCount();
            this.markDirty();
            
            this.showNotification('Block dupliziert', 'success');
        }

        /**
         * Preview aktualisieren
         */
        updatePreview() {
            const previewContent = $('.preview-content');
            
            if (this.previewMode === 'visual') {
                previewContent.html(this.generateVisualPreview());
            } else {
                previewContent.html(`<pre><code>${this.escapeHtml(this.generateHTMLPreview())}</code></pre>`);
            }
        }

        /**
         * Visual Preview generieren
         */
        generateVisualPreview() {
            let html = '';
            
            this.blocks.forEach(block => {
                switch (block.type) {
                    case 'headline':
                        html += `<${block.content.level}>${this.escapeHtml(block.content.text)}</${block.content.level}>`;
                        break;
                        
                    case 'paragraph':
                        html += `<p>${this.escapeHtml(block.content.text).replace(/\n/g, '<br>')}</p>`;
                        break;
                        
                    case 'market_data':
                        html += this.generateMarketDataPreview(block.content);
                        break;
                        
                    case 'quote':
                        html += `
                            <blockquote style="border-left: 4px solid #0073aa; padding-left: 20px; margin: 20px 0; font-style: italic;">
                                <p>"${this.escapeHtml(block.content.text)}"</p>
                                <cite>— ${this.escapeHtml(block.content.author)}</cite>
                            </blockquote>
                        `;
                        break;
                        
                    case 'trading_tip':
                        const tipColors = {
                            info: '#0073aa',
                            success: '#46b450',
                            warning: '#ffb900',
                            danger: '#dc3232'
                        };
                        html += `
                            <div style="border: 1px solid ${tipColors[block.content.level]}; border-radius: 4px; padding: 15px; margin: 20px 0; background: ${tipColors[block.content.level]}15;">
                                <h4 style="margin: 0 0 10px 0; color: ${tipColors[block.content.level]};">${this.escapeHtml(block.content.title)}</h4>
                                <p style="margin: 0;">${this.escapeHtml(block.content.text)}</p>
                            </div>
                        `;
                        break;
                        
                    case 'call_to_action':
                        const btnColors = {
                            primary: '#0073aa',
                            secondary: '#50575e',
                            success: '#46b450',
                            warning: '#ffb900'
                        };
                        html += `
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="${this.escapeHtml(block.content.url)}" style="display: inline-block; background: ${btnColors[block.content.style]}; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: 600;">
                                    ${this.escapeHtml(block.content.text)}
                                </a>
                            </div>
                        `;
                        break;
                        
                    case 'divider':
                        html += '<hr style="margin: 30px 0; border: none; border-top: 1px solid #ddd;">';
                        break;
                }
            });
            
            return html || '<p style="color: #666; text-align: center; padding: 40px;">Keine Blöcke hinzugefügt. Ziehen Sie Blöcke aus der Sidebar hierher.</p>';
        }

        /**
         * HTML Preview generieren
         */
        generateHTMLPreview() {
            let html = '';
            
            this.blocks.forEach(block => {
                switch (block.type) {
                    case 'headline':
                        html += `<${block.content.level}>${block.content.text}</${block.content.level}>\n\n`;
                        break;
                        
                    case 'paragraph':
                        html += `<p>${block.content.text}</p>\n\n`;
                        break;
                        
                    case 'quote':
                        html += `<blockquote>\n\t<p>"${block.content.text}"</p>\n\t<cite>— ${block.content.author}</cite>\n</blockquote>\n\n`;
                        break;
                        
                    case 'call_to_action':
                        html += `<div class="cta-block">\n\t<a href="${block.content.url}" class="btn btn-${block.content.style}">${block.content.text}</a>\n</div>\n\n`;
                        break;
                        
                    case 'divider':
                        html += '<hr>\n\n';
                        break;
                }
            });
            
            return html;
        }

        /**
         * Marktdaten Preview generieren
         */
        generateMarketDataPreview(content) {
            if (content.display === 'table') {
                let html = '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">';
                html += '<thead><tr><th style="border: 1px solid #ddd; padding: 8px; background: #f5f5f5;">Symbol</th><th style="border: 1px solid #ddd; padding: 8px; background: #f5f5f5;">Kurs</th><th style="border: 1px solid #ddd; padding: 8px; background: #f5f5f5;">Änderung</th></tr></thead>';
                html += '<tbody>';
                
                content.symbols.forEach(symbol => {
                    const change = (Math.random() * 2 - 1).toFixed(4);
                    const color = change >= 0 ? '#46b450' : '#dc3232';
                    html += `<tr>
                        <td style="border: 1px solid #ddd; padding: 8px; font-weight: 600;">${symbol}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">${(Math.random() * 2 + 0.5).toFixed(4)}</td>
                        <td style="border: 1px solid #ddd; padding: 8px; color: ${color};">${change >= 0 ? '+' : ''}${change}</td>
                    </tr>`;
                });
                
                html += '</tbody></table>';
                return html;
            }
            
            return '<div style="padding: 20px; background: #f5f5f5; border-radius: 4px; margin: 20px 0;">Marktdaten: ' + content.symbols.join(', ') + '</div>';
        }

        /**
         * Copy to Clipboard
         */
        copyToClipboard() {
            const content = this.previewMode === 'visual' 
                ? this.generateHTMLPreview() 
                : this.generateHTMLPreview();
                
            navigator.clipboard.writeText(content).then(() => {
                const btn = $('#copy-content');
                btn.addClass('copy-success');
                btn.find('span').text('Kopiert!');
                
                setTimeout(() => {
                    btn.removeClass('copy-success');
                    btn.find('span').text('Kopieren');
                }, 2000);
                
                this.showNotification('Inhalt in Zwischenablage kopiert', 'success');
            }).catch(() => {
                this.showNotification('Fehler beim Kopieren', 'error');
            });
        }

        /**
         * Article speichern via AJAX
         */
        saveArticle() {
            const data = {
                action: 'save_article_builder',
                nonce: articleBuilderAjax.nonce,
                blocks: this.blocks,
                post_id: $('#post_ID').val() || 0
            };

            this.setLoading(true);

            $.post(articleBuilderAjax.ajaxurl, data)
                .done((response) => {
                    if (response.success) {
                        this.showNotification('Artikel gespeichert', 'success');
                        this.isDirty = false;
                    } else {
                        this.showNotification('Fehler beim Speichern: ' + response.data, 'error');
                    }
                })
                .fail(() => {
                    this.showNotification('AJAX Fehler beim Speichern', 'error');
                })
                .always(() => {
                    this.setLoading(false);
                });
        }

        /**
         * Auto-Save
         */
        autoSave() {
            if (this.blocks.length === 0) return;
            
            const data = {
                action: 'auto_save_article_builder',
                nonce: articleBuilderAjax.nonce,
                blocks: this.blocks
            };

            $.post(articleBuilderAjax.ajaxurl, data)
                .done((response) => {
                    if (response.success) {
                        this.isDirty = false;
                        this.showNotification('Auto-gespeichert', 'success', 2000);
                    }
                });
        }

        /**
         * JSON Export
         */
        exportJSON() {
            const data = {
                version: '1.0',
                timestamp: new Date().toISOString(),
                blocks: this.blocks
            };

            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            
            a.href = url;
            a.download = `article-builder-${Date.now()}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            
            this.showNotification('JSON exportiert', 'success');
        }

        /**
         * JSON Import
         */
        importJSON(file) {
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                try {
                    const data = JSON.parse(e.target.result);
                    
                    if (data.blocks && Array.isArray(data.blocks)) {
                        this.blocks = data.blocks;
                        this.renderAllBlocks();
                        this.updatePreview();
                        this.updateBlockCount();
                        this.markDirty();
                        
                        this.showNotification(`${data.blocks.length} Blöcke importiert`, 'success');
                    } else {
                        this.showNotification('Ungültiges JSON Format', 'error');
                    }
                } catch (error) {
                    this.showNotification('Fehler beim JSON parsen', 'error');
                }
            };
            
            reader.readAsText(file);
        }

        /**
         * Alle Blöcke rendern
         */
        renderAllBlocks() {
            $('.article-drop-zone').empty();
            this.blocks.forEach(block => {
                this.renderBlock(block);
            });
            this.toggleEmptyState();
        }

        /**
         * Builder leeren
         */
        clearBuilder() {
            this.blocks = [];
            $('.article-drop-zone').empty();
            this.updatePreview();
            this.updateBlockCount();
            this.toggleEmptyState();
            this.markDirty();
            
            this.showNotification('Builder geleert', 'success');
        }

        /**
         * Hilfsfunktionen
         */
        generateBlockId() {
            return 'block_' + (++this.blockIdCounter) + '_' + Date.now();
        }

        getBlockById(blockId) {
            return this.blocks.find(block => block.id === blockId);
        }

        updateBlockOrder() {
            const newOrder = [];
            $('.article-block').each(function() {
                const blockId = $(this).data('block-id');
                const block = articleBuilder.getBlockById(blockId);
                if (block) {
                    newOrder.push(block);
                }
            });
            this.blocks = newOrder;
        }

        updateBlockCount() {
            $('.block-count').text(this.blocks.length);
        }

        toggleEmptyState() {
            if (this.blocks.length === 0) {
                $('.drop-zone-empty').show();
            } else {
                $('.drop-zone-empty').hide();
            }
        }

        markDirty() {
            this.isDirty = true;
        }

        setLoading(loading) {
            if (loading) {
                $('.article-builder-wrap').addClass('ab-loading');
            } else {
                $('.article-builder-wrap').removeClass('ab-loading');
            }
        }

        showNotification(message, type = 'info', duration = 5000) {
            const notification = $(`
                <div class="ab-notification ${type}">
                    <span class="dashicons dashicons-${this.getNotificationIcon(type)}"></span>
                    <span>${message}</span>
                </div>
            `);

            $('.article-builder-header').after(notification);
            
            setTimeout(() => {
                notification.fadeOut(300, function() {
                    $(this).remove();
                });
            }, duration);
        }

        getNotificationIcon(type) {
            const icons = {
                success: 'yes',
                error: 'no',
                warning: 'warning',
                info: 'info'
            };
            return icons[type] || 'info';
        }

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        calculateInsertPosition(y) {
            let insertIndex = 0;
            $('.article-block').each(function(index) {
                const blockTop = $(this).offset().top;
                const blockHeight = $(this).height();
                
                if (y > blockTop + blockHeight / 2) {
                    insertIndex = index + 1;
                }
            });
            return insertIndex;
        }

        /**
         * Gespeicherte Blöcke laden
         */
        loadSavedBlocks() {
            const savedBlocks = $('#article-builder-data').val();
            if (savedBlocks) {
                try {
                    this.blocks = JSON.parse(savedBlocks);
                    this.renderAllBlocks();
                    this.updatePreview();
                    this.updateBlockCount();
                } catch (error) {
                    console.error('Fehler beim Laden gespeicherter Blöcke:', error);
                }
            }
        }

        /**
         * Undo/Redo Funktionalität (Basis-Implementation)
         */
        undo() {
            // TODO: Implementiere History-Stack
            this.showNotification('Undo wird in zukünftiger Version verfügbar sein', 'info');
        }

        redo() {
            // TODO: Implementiere History-Stack
            this.showNotification('Redo wird in zukünftiger Version verfügbar sein', 'info');
        }
    }

    /**
     * Initialisierung wenn DOM ready
     */
    $(document).ready(function() {
        // Globale Instanz erstellen
        window.articleBuilder = new ArticleBuilder();
        
        // Unsaved Changes Warning
        window.addEventListener('beforeunload', function(e) {
            if (articleBuilder.isDirty) {
                e.preventDefault();
                e.returnValue = 'Sie haben ungespeicherte Änderungen. Wirklich verlassen?';
                return e.returnValue;
            }
        });
    });

})(jQuery);