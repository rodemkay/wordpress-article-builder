import React, { useRef } from 'react';
import { useDrag, useDrop } from 'react-dnd';
import classNames from 'classnames';
import BlockRenderer from '../Blocks/BlockRenderer';

const BlockItem = ({
  block,
  index,
  isSelected,
  onClick,
  onUpdate,
  onRemove,
  onMove
}) => {
  const ref = useRef(null);

  const [{ isDragging }, drag] = useDrag({
    type: 'block',
    item: { index, block },
    collect: (monitor) => ({
      isDragging: monitor.isDragging()
    })
  });

  const [, drop] = useDrop({
    accept: 'block',
    hover: (item, monitor) => {
      if (!ref.current) return;
      
      const dragIndex = item.index;
      const hoverIndex = index;
      
      if (dragIndex === hoverIndex) return;
      
      const hoverBoundingRect = ref.current.getBoundingClientRect();
      const hoverMiddleY = (hoverBoundingRect.bottom - hoverBoundingRect.top) / 2;
      const clientOffset = monitor.getClientOffset();
      const hoverClientY = clientOffset.y - hoverBoundingRect.top;
      
      if (dragIndex < hoverIndex && hoverClientY < hoverMiddleY) return;
      if (dragIndex > hoverIndex && hoverClientY > hoverMiddleY) return;
      
      onMove(dragIndex, hoverIndex);
      item.index = hoverIndex;
    }
  });

  drag(drop(ref));

  const blockClasses = classNames('block-item', {
    'block-item--selected': isSelected,
    'block-item--dragging': isDragging,
    [`block-item--${block.type}`]: block.type
  });

  const handleBlockUpdate = (updatedData) => {
    onUpdate({ ...block, ...updatedData });
  };

  return (
    <div
      ref={ref}
      className={blockClasses}
      onClick={onClick}
      style={{ opacity: isDragging ? 0.5 : 1 }}
    >
      <div className="block-controls">
        <button
          className="block-control block-control--drag"
          title="Block verschieben"
        >
          ⋮⋮
        </button>
        <span className="block-type">{block.type}</span>
        <button
          className="block-control block-control--remove"
          onClick={(e) => {
            e.stopPropagation();
            onRemove();
          }}
          title="Block löschen"
        >
          ×
        </button>
      </div>
      
      <div className="block-content">
        <BlockRenderer 
          block={block}
          isEditing={true}
          onUpdate={handleBlockUpdate}
        />
      </div>
      
      {isSelected && (
        <div className="block-selection-indicator" />
      )}
    </div>
  );
};

export default BlockItem;