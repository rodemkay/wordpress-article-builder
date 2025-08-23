import React from 'react';
import BlockSettings from './BlockSettings';
import './Sidebar.scss';

const Sidebar = ({ selectedBlock, onBlockUpdate, blocks }) => {
  return (
    <div className="sidebar">
      <div className="sidebar-header">
        <h3>Block-Einstellungen</h3>
      </div>
      
      <div className="sidebar-content">
        {selectedBlock ? (
          <>
            <div className="block-info">
              <div className="block-type-badge">{selectedBlock.type}</div>
              <div className="block-id">ID: {selectedBlock.id}</div>
            </div>
            
            <BlockSettings
              block={selectedBlock}
              onUpdate={onBlockUpdate}
            />
          </>
        ) : (
          <div className="no-selection">
            <p>Wähle einen Block aus, um ihn zu bearbeiten</p>
          </div>
        )}
      </div>
      
      <div className="sidebar-section">
        <h4 className="section-title">Artikel-Info</h4>
        <div className="section-content">
          <div className="form-group">
            <label>Anzahl Blöcke:</label>
            <span>{blocks.length}</span>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Sidebar;