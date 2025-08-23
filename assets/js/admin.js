/*
 * Article Builder Plugin - Admin JavaScript
 * Version: 1.0.0
 */

(function($) {
    'use strict';

    // Global object for Article Builder
    window.ArticleBuilderAdmin = {
        init: function() {
            this.bindEvents();
            this.initSortable();
        },

        bindEvents: function() {
            // Save project button
            $(document).on('click', '#save-project', this.saveProject);
            
            // Export HTML button
            $(document).on('click', '#export-html', this.exportHTML);
            
            // Modal close events
            $(document).on('click', '.export-modal-close', this.closeModal);
            $(document).on('click', '#copy-html-button', this.copyHTML);
            
            // Block events
            $(document).on('click', '.block-item', this.addBlock);
            $(document).on('click', '.edit-block', this.editBlock);
            $(document).on('click', '.generate-block', this.generateBlock);
            $(document).on('click', '.remove-block', this.removeBlock);
            
            // Preview events
            $(document).on('click', '#refresh-preview', this.refreshPreview);
            $(document).on('click', '#toggle-preview', this.togglePreview);
        },

        initSortable: function() {
            if ($('.blocks-sortable').length) {
                $('.blocks-sortable').sortable({
                    handle: '.block-handle',
                    placeholder: 'block-placeholder',
                    update: function(event, ui) {
                        ArticleBuilderAdmin.refreshPreview();
                    }
                });
            }
        },

        saveProject: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var originalText = $button.text();
            
            $button.text('Speichert...').prop('disabled', true);
            
            var projectData = ArticleBuilderAdmin.collectProjectData();
            
            $.ajax({
                url: articleBuilder.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ab_save_project',
                    nonce: articleBuilder.nonce,
                    project_id: $('#project-id').val(),
                    project_name: $('#article-title').val(),
                    project_data: JSON.stringify(projectData)
                },
                success: function(response) {
                    if (response.success) {
                        ArticleBuilderAdmin.showNotice('success', response.data.message);
                        if (response.data.project_id) {
                            $('#project-id').val(response.data.project_id);
                        }
                    } else {
                        ArticleBuilderAdmin.showNotice('error', response.data.message);
                    }
                },
                error: function() {
                    ArticleBuilderAdmin.showNotice('error', articleBuilder.strings.error);
                },
                complete: function() {
                    $button.text(originalText).prop('disabled', false);
                }
            });
        },

        exportHTML: function(e) {
            e.preventDefault();
            
            var projectData = ArticleBuilderAdmin.collectProjectData();
            
            $.ajax({
                url: articleBuilder.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ab_get_preview_html',
                    nonce: articleBuilder.nonce,
                    project_data: JSON.stringify(projectData)
                },
                success: function(response) {
                    if (response.success) {
                        $('#export-html-content').val(response.data.html);
                        $('#export-modal').show();
                    } else {
                        ArticleBuilderAdmin.showNotice('error', response.data.message);
                    }
                },
                error: function() {
                    ArticleBuilderAdmin.showNotice('error', articleBuilder.strings.error);
                }
            });
        },

        copyHTML: function(e) {
            e.preventDefault();
            
            var textarea = document.getElementById('export-html-content');
            textarea.select();
            
            try {
                document.execCommand('copy');
                ArticleBuilderAdmin.showNotice('success', articleBuilder.strings.copied);
            } catch (err) {
                ArticleBuilderAdmin.showNotice('error', 'Kopieren fehlgeschlagen');
            }
        },

        closeModal: function(e) {
            e.preventDefault();
            $('#export-modal').hide();
        },

        addBlock: function(e) {
            e.preventDefault();
            
            var blockType = $(this).data('block-type');
            var blockTitle = $(this).find('.block-title').text();
            
            var blockHtml = ArticleBuilderAdmin.createBlockElement(blockType, blockTitle);
            
            $('#blocks-container').append(blockHtml);
            ArticleBuilderAdmin.refreshPreview();
        },

        editBlock: function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $block = $(this).closest('.block-item-active');
            var $content = $block.find('.block-content');
            
            $content.toggle();
        },

        generateBlock: function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // TODO: AI-Integration für Block-Generierung
            ArticleBuilderAdmin.showNotice('info', 'KI-Generierung noch nicht implementiert');
        },

        removeBlock: function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (confirm(articleBuilder.strings.confirmDelete)) {
                $(this).closest('.block-item-active').remove();
                ArticleBuilderAdmin.refreshPreview();
            }
        },

        refreshPreview: function() {
            var projectData = ArticleBuilderAdmin.collectProjectData();
            
            $.ajax({
                url: articleBuilder.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ab_generate_preview',
                    nonce: articleBuilder.nonce,
                    project_data: JSON.stringify(projectData)
                },
                success: function(response) {
                    if (response.success) {
                        $('#article-preview').html(response.data.html);
                    }
                }
            });
        },

        togglePreview: function(e) {
            e.preventDefault();
            $('#article-preview').toggle();
        },

        collectProjectData: function() {
            var elements = [];
            
            $('.block-item-active').each(function() {
                var $block = $(this);
                var content = $block.find('.block-content-editor').val() || '';
                
                elements.push({
                    type: $block.data('block-type'),
                    content: content,
                    attributes: {}
                });
            });
            
            return {
                title: $('#article-title').val(),
                excerpt: $('#article-excerpt').val(),
                categories: $('input[name="article_categories[]"]:checked').map(function() {
                    return this.value;
                }).get(),
                tags: $('#article-tags').val(),
                elements: elements
            };
        },

        createBlockElement: function(blockType, blockTitle) {
            var blockId = 'block_' + Date.now();
            
            return '<div class="block-item-active" data-block-id="' + blockId + '" data-block-type="' + blockType + '">' +
                '<div class="block-header">' +
                    '<div class="block-info">' +
                        '<span class="block-title">' + blockTitle + '</span>' +
                        '<span class="block-status">Leer</span>' +
                    '</div>' +
                    '<div class="block-actions">' +
                        '<button type="button" class="button button-small edit-block" title="Bearbeiten">✏️</button>' +
                        '<button type="button" class="button button-small generate-block" title="Neu generieren">🔄</button>' +
                        '<button type="button" class="button button-small remove-block" title="Entfernen">🗑️</button>' +
                        '<span class="block-handle" title="Ziehen zum Sortieren">⋮⋮</span>' +
                    '</div>' +
                '</div>' +
                '<div class="block-content" style="display: none;">' +
                    '<textarea class="block-content-editor" placeholder="Inhalt für ' + blockTitle + ' eingeben..."></textarea>' +
                '</div>' +
            '</div>';
        },

        showNotice: function(type, message) {
            var noticeClass = 'notice-' + type;
            var $notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
            
            $('.wrap h1').after($notice);
            
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        ArticleBuilderAdmin.init();
    });

})(jQuery);