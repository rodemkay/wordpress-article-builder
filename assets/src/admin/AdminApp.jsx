import React, { useState, useEffect } from 'react';
import { DndProvider } from 'react-dnd';
import { HTML5Backend } from 'react-dnd-html5-backend';
import VisualEditor from '../components/VisualEditor/VisualEditor';
import Toolbar from '../components/Toolbar/Toolbar';
import Sidebar from '../components/Sidebar/Sidebar';
import { useArticleBuilder } from '../hooks/useArticleBuilder';

const AdminApp = () => {
  const {
    blocks,
    addBlock,
    removeBlock,
    updateBlock,
    moveBlock,
    saveArticle,
    isLoading,
    error
  } = useArticleBuilder();

  const [selectedBlock, setSelectedBlock] = useState(null);
  const [sidebarOpen, setSidebarOpen] = useState(true);

  return (
    <DndProvider backend={HTML5Backend}>
      <div className="article-builder-admin">
        {error && (
          <div className="article-builder-error">
            <p>{error}</p>
          </div>
        )}
        
        <Toolbar
          onAddBlock={addBlock}
          onSave={saveArticle}
          isLoading={isLoading}
          toggleSidebar={() => setSidebarOpen(!sidebarOpen)}
          sidebarOpen={sidebarOpen}
        />

        <div className="article-builder-content">
          <div className="editor-container">
            <VisualEditor
              blocks={blocks}
              onBlockSelect={setSelectedBlock}
              onBlockUpdate={updateBlock}
              onBlockRemove={removeBlock}
              onBlockMove={moveBlock}
              selectedBlock={selectedBlock}
            />
          </div>

          {sidebarOpen && (
            <Sidebar
              selectedBlock={selectedBlock}
              onBlockUpdate={updateBlock}
              blocks={blocks}
            />
          )}
        </div>
      </div>
    </DndProvider>
  );
};

export default AdminApp;