import React from 'react';
import { useDrag } from 'react-dnd';

const blockTypes = [
  {
    type: 'paragraph',
    name: 'Absatz',
    icon: '📝',
    description: 'Einfacher Textblock'
  },
  {
    type: 'heading',
    name: 'Überschrift',
    icon: 'H',
    description: 'Überschrift (H1-H6)'
  },
  {
    type: 'image',
    name: 'Bild',
    icon: '🖼️',
    description: 'Bild mit Beschreibung'
  },
  {
    type: 'quote',
    name: 'Zitat',
    icon: '💬',
    description: 'Hervorgehobenes Zitat'
  },
  {
    type: 'list',
    name: 'Liste',
    icon: '📋',
    description: 'Aufzählung oder nummerierte Liste'
  },
  {
    type: 'code',
    name: 'Code',
    icon: '💻',
    description: 'Code-Block'
  },
  {
    type: 'divider',
    name: 'Trenner',
    icon: '➖',
    description: 'Horizontaler Trenner'
  },
  {
    type: 'button',
    name: 'Button',
    icon: '🔘',
    description: 'Call-to-Action Button'
  }
];

const BlockPaletteItem = ({ blockType, onSelect }) => {
  const [{ isDragging }, drag] = useDrag({
    type: 'palette-block',
    item: { blockType: blockType.type },
    collect: (monitor) => ({
      isDragging: monitor.isDragging()
    })
  });

  return (
    <div
      ref={drag}
      className="block-palette-item"
      onClick={() => onSelect(blockType.type)}
      style={{ opacity: isDragging ? 0.5 : 1 }}
    >
      <div className="block-palette-icon">{blockType.icon}</div>
      <div className="block-palette-content">
        <div className="block-palette-name">{blockType.name}</div>
        <div className="block-palette-description">{blockType.description}</div>
      </div>
    </div>
  );
};

const BlockPalette = ({ onSelectBlock, onClose }) => {
  return (
    <div className="block-palette">
      <div className="block-palette-header">
        <h3>Block auswählen</h3>
        <button className="block-palette-close" onClick={onClose}>×</button>
      </div>
      
      <div className="block-palette-content">
        <div className="block-palette-section">
          <h4>Text</h4>
          <div className="block-palette-grid">
            {blockTypes.filter(b => ['paragraph', 'heading', 'quote'].includes(b.type)).map(blockType => (
              <BlockPaletteItem
                key={blockType.type}
                blockType={blockType}
                onSelect={onSelectBlock}
              />
            ))}
          </div>
        </div>
        
        <div className="block-palette-section">
          <h4>Medien</h4>
          <div className="block-palette-grid">
            {blockTypes.filter(b => ['image'].includes(b.type)).map(blockType => (
              <BlockPaletteItem
                key={blockType.type}
                blockType={blockType}
                onSelect={onSelectBlock}
              />
            ))}
          </div>
        </div>
        
        <div className="block-palette-section">
          <h4>Layout</h4>
          <div className="block-palette-grid">
            {blockTypes.filter(b => ['list', 'code', 'divider', 'button'].includes(b.type)).map(blockType => (
              <BlockPaletteItem
                key={blockType.type}
                blockType={blockType}
                onSelect={onSelectBlock}
              />
            ))}
          </div>
        </div>
      </div>
    </div>
  );
};

export default BlockPalette;