import React, { useCallback } from 'react';
import { useDrop } from 'react-dnd';
import BlockItem from './BlockItem';
import DropZone from './DropZone';
import './VisualEditor.scss';

const VisualEditor = ({
  blocks,
  onBlockSelect,
  onBlockUpdate,
  onBlockRemove,
  onBlockMove,
  selectedBlock
}) => {
  const [{ isOver }, drop] = useDrop({
    accept: 'block',
    drop: (item, monitor) => {
      if (!monitor.didDrop()) {
        // Handle drop at the end of the list
        onBlockMove(item.index, blocks.length);
      }
    },
    collect: (monitor) => ({
      isOver: monitor.isOver()
    })
  });

  const moveBlock = useCallback((dragIndex, hoverIndex) => {
    onBlockMove(dragIndex, hoverIndex);
  }, [onBlockMove]);

  const handleBlockClick = useCallback((block, index) => {
    onBlockSelect(block);
  }, [onBlockSelect]);

  return (
    <div 
      ref={drop}
      className={`visual-editor ${isOver ? 'drop-active' : ''}`}
    >
      <div className="editor-content">
        {blocks.length === 0 ? (
          <div className="editor-empty">
            <div className="empty-state">
              <h3>Beginne mit dem Erstellen deines Artikels</h3>
              <p>Ziehe Blöcke aus der Toolbar hierher oder klicke auf "Block hinzufügen"</p>
            </div>
          </div>
        ) : (
          <>
            {blocks.map((block, index) => (
              <React.Fragment key={block.id}>
                <DropZone 
                  index={index}
                  onDrop={(item) => moveBlock(item.index, index)}
                />
                <BlockItem
                  block={block}
                  index={index}
                  isSelected={selectedBlock?.id === block.id}
                  onClick={() => handleBlockClick(block, index)}
                  onUpdate={(updatedBlock) => onBlockUpdate(index, updatedBlock)}
                  onRemove={() => onBlockRemove(index)}
                  onMove={moveBlock}
                />
              </React.Fragment>
            ))}
            <DropZone 
              index={blocks.length}
              onDrop={(item) => moveBlock(item.index, blocks.length)}
            />
          </>
        )}
      </div>
    </div>
  );
};

export default VisualEditor;