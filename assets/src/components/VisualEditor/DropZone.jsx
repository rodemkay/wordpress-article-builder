import React from 'react';
import { useDrop } from 'react-dnd';
import classNames from 'classnames';

const DropZone = ({ index, onDrop }) => {
  const [{ isOver, canDrop }, drop] = useDrop({
    accept: 'block',
    drop: (item) => {
      onDrop(item);
    },
    collect: (monitor) => ({
      isOver: monitor.isOver(),
      canDrop: monitor.canDrop()
    })
  });

  const dropZoneClasses = classNames('drop-zone', {
    'drop-zone--active': isOver && canDrop,
    'drop-zone--can-drop': canDrop
  });

  return (
    <div ref={drop} className={dropZoneClasses}>
      {isOver && canDrop && (
        <div className="drop-zone-indicator">
          Block hier einfügen
        </div>
      )}
    </div>
  );
};

export default DropZone;