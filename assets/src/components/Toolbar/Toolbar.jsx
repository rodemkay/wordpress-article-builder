import React, { useState } from 'react';
import BlockPalette from './BlockPalette';
import './Toolbar.scss';

const Toolbar = ({
  onAddBlock,
  onSave,
  isLoading,
  toggleSidebar,
  sidebarOpen
}) => {
  const [showBlockPalette, setShowBlockPalette] = useState(false);

  const handleAddBlock = (blockType) => {
    onAddBlock(blockType);
    setShowBlockPalette(false);
  };

  return (
    <div className="article-builder-toolbar">
      <div className="toolbar-section toolbar-section--left">
        <button
          className="toolbar-button toolbar-button--primary"
          onClick={() => setShowBlockPalette(!showBlockPalette)}
        >
          + Block hinzufügen
        </button>
        
        {showBlockPalette && (
          <BlockPalette
            onSelectBlock={handleAddBlock}
            onClose={() => setShowBlockPalette(false)}
          />
        )}
      </div>

      <div className="toolbar-section toolbar-section--center">
        <div className="toolbar-title">
          <h2>Article Builder</h2>
        </div>
      </div>

      <div className="toolbar-section toolbar-section--right">
        <button
          className="toolbar-button"
          onClick={toggleSidebar}
          title={sidebarOpen ? 'Sidebar schließen' : 'Sidebar öffnen'}
        >
          {sidebarOpen ? '→' : '←'}
        </button>
        
        <button
          className="toolbar-button toolbar-button--secondary"
          onClick={() => window.location.reload()}
        >
          Vorschau
        </button>
        
        <button
          className="toolbar-button toolbar-button--success"
          onClick={onSave}
          disabled={isLoading}
        >
          {isLoading ? 'Speichere...' : 'Speichern'}
        </button>
      </div>
    </div>
  );
};

export default Toolbar;