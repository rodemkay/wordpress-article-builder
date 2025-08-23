import React, { useState, useEffect } from 'react';

const DividerBlock = ({ block, isEditing, onUpdate }) => {
  const [style, setStyle] = useState(block.style || 'solid');
  const [width, setWidth] = useState(block.width || 'full');

  useEffect(() => {
    setStyle(block.style || 'solid');
    setWidth(block.width || 'full');
  }, [block]);

  const handleUpdate = (field, value) => {
    const updates = { style, width, [field]: value };
    onUpdate && onUpdate(updates);
  };

  const styles = [
    { value: 'solid', label: 'Durchgezogen' },
    { value: 'dashed', label: 'Gestrichelt' },
    { value: 'dotted', label: 'Gepunktet' },
    { value: 'double', label: 'Doppelt' }
  ];

  const widths = [
    { value: 'narrow', label: 'Schmal (25%)' },
    { value: 'medium', label: 'Mittel (50%)' },
    { value: 'wide', label: 'Breit (75%)' },
    { value: 'full', label: 'Vollbreite (100%)' }
  ];

  if (isEditing) {
    return (
      <div className="divider-block editing">
        <div className="divider-controls">
          <div className="control-group">
            <label>Stil:</label>
            <select
              value={style}
              onChange={(e) => {
                setStyle(e.target.value);
                handleUpdate('style', e.target.value);
              }}
            >
              {styles.map(s => (
                <option key={s.value} value={s.value}>
                  {s.label}
                </option>
              ))}
            </select>
          </div>
          
          <div className="control-group">
            <label>Breite:</label>
            <select
              value={width}
              onChange={(e) => {
                setWidth(e.target.value);
                handleUpdate('width', e.target.value);
              }}
            >
              {widths.map(w => (
                <option key={w.value} value={w.value}>
                  {w.label}
                </option>
              ))}
            </select>
          </div>
        </div>
        
        <div className="divider-preview">
          <hr 
            className={`divider-line style-${style} width-${width}`}
            style={{ borderStyle: style }}
          />
        </div>
      </div>
    );
  }

  return (
    <div className="divider-block">
      <hr 
        className={`divider-line style-${style} width-${width}`}
        style={{ borderStyle: style }}
      />
    </div>
  );
};

export default DividerBlock;