import React, { useState, useEffect } from 'react';
import { DndProvider } from 'react-dnd';
import { HTML5Backend } from 'react-dnd-html5-backend';
import { useArticleBuilder } from '../../hooks/useArticleBuilder';

const BlockEditor = ({ attributes, setAttributes, isSelected }) => {
  const {
    blocks,
    addBlock,
    removeBlock,
    updateBlock,
    moveBlock,
    isLoading,
    error
  } = useArticleBuilder(attributes?.blocks || []);

  // Sync blocks with Gutenberg attributes
  useEffect(() => {
    if (setAttributes && JSON.stringify(blocks) !== JSON.stringify(attributes?.blocks)) {
      setAttributes({ blocks });
    }
  }, [blocks, setAttributes, attributes?.blocks]);

  const handleAddBlock = (blockType) => {
    addBlock(blockType);
  };

  if (!isSelected) {
    return (
      <div className="article-builder-editor">
        <div className="editor-placeholder">
          <p>Klicken Sie hier, um den Article Builder zu bearbeiten</p>
          <p>Anzahl Blöcke: {blocks.length}</p>
        </div>
      </div>
    );
  }

  return (
    <DndProvider backend={HTML5Backend}>
      <div className="article-builder-editor">
        {error && (
          <div className="error-message" style={{ 
            background: '#d63638', 
            color: 'white', 
            padding: '1rem', 
            marginBottom: '1rem',
            borderRadius: '4px'
          }}>
            {error}
          </div>
        )}

        <div className="editor-toolbar">
          <button
            onClick={() => handleAddBlock('paragraph')}
            className="editor-button"
            disabled={isLoading}
          >
            + Absatz
          </button>
          <button
            onClick={() => handleAddBlock('heading')}
            className="editor-button"
            disabled={isLoading}
          >
            + Überschrift
          </button>
          <button
            onClick={() => handleAddBlock('image')}
            className="editor-button"
            disabled={isLoading}
          >
            + Bild
          </button>
          <button
            onClick={() => handleAddBlock('button')}
            className="editor-button"
            disabled={isLoading}
          >
            + Button
          </button>
        </div>

        <div className="editor-wrapper">
          {blocks.length === 0 ? (
            <div className="editor-placeholder">
              <p>Noch keine Blöcke vorhanden. Fügen Sie den ersten Block hinzu!</p>
            </div>
          ) : (
            <div className="editor-blocks">
              {blocks.map((block, index) => (
                <div key={block.id} className="editor-block-wrapper">
                  <div className="block-controls">
                    <span className="block-type">{block.type}</span>
                    <button
                      onClick={() => removeBlock(index)}
                      className="remove-block"
                    >
                      ×
                    </button>
                  </div>
                  <div className="block-preview">
                    {block.type === 'paragraph' && (
                      <p>{block.content || 'Leerer Absatz'}</p>
                    )}
                    {block.type === 'heading' && (
                      React.createElement(
                        `h${block.level || 2}`,
                        {},
                        block.content || 'Leere Überschrift'
                      )
                    )}
                    {block.type === 'image' && (
                      <div>
                        {block.src ? (
                          <img src={block.src} alt={block.alt} style={{ maxWidth: '100%' }} />
                        ) : (
                          <div style={{ 
                            background: '#f0f0f1', 
                            padding: '2rem', 
                            textAlign: 'center',
                            border: '1px dashed #dcdcde'
                          }}>
                            Kein Bild ausgewählt
                          </div>
                        )}
                      </div>
                    )}
                    {block.type === 'button' && (
                      <button style={{ 
                        padding: '0.5rem 1rem',
                        background: '#0073aa',
                        color: 'white',
                        border: 'none',
                        borderRadius: '4px'
                      }}>
                        {block.text || 'Button'}
                      </button>
                    )}
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </DndProvider>
  );
};

export default BlockEditor;