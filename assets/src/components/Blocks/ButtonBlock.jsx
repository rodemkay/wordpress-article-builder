import React, { useState, useEffect } from 'react';

const ButtonBlock = ({ block, isEditing, onUpdate }) => {
  const [text, setText] = useState(block.text || 'Button Text');
  const [url, setUrl] = useState(block.url || '');
  const [style, setStyle] = useState(block.style || 'primary');
  const [size, setSize] = useState(block.size || 'medium');
  const [alignment, setAlignment] = useState(block.alignment || 'left');
  const [openInNewTab, setOpenInNewTab] = useState(block.openInNewTab || false);

  useEffect(() => {
    setText(block.text || 'Button Text');
    setUrl(block.url || '');
    setStyle(block.style || 'primary');
    setSize(block.size || 'medium');
    setAlignment(block.alignment || 'left');
    setOpenInNewTab(block.openInNewTab || false);
  }, [block]);

  const handleUpdate = (field, value) => {
    const updates = { 
      text, url, style, size, alignment, openInNewTab,
      [field]: value 
    };
    onUpdate && onUpdate(updates);
  };

  const styles = [
    { value: 'primary', label: 'Primär' },
    { value: 'secondary', label: 'Sekundär' },
    { value: 'outline', label: 'Umriss' },
    { value: 'ghost', label: 'Ghost' }
  ];

  const sizes = [
    { value: 'small', label: 'Klein' },
    { value: 'medium', label: 'Mittel' },
    { value: 'large', label: 'Groß' }
  ];

  const alignments = [
    { value: 'left', label: 'Links' },
    { value: 'center', label: 'Zentriert' },
    { value: 'right', label: 'Rechts' }
  ];

  if (isEditing) {
    return (
      <div className="button-block editing">
        <div className="button-controls">
          <div className="control-group">
            <label>Button Text:</label>
            <input
              type="text"
              value={text}
              onChange={(e) => {
                setText(e.target.value);
                handleUpdate('text', e.target.value);
              }}
              placeholder="Button Text"
            />
          </div>
          
          <div className="control-group">
            <label>Link URL:</label>
            <input
              type="url"
              value={url}
              onChange={(e) => {
                setUrl(e.target.value);
                handleUpdate('url', e.target.value);
              }}
              placeholder="https://example.com"
            />
          </div>
          
          <div className="control-row">
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
              <label>Größe:</label>
              <select
                value={size}
                onChange={(e) => {
                  setSize(e.target.value);
                  handleUpdate('size', e.target.value);
                }}
              >
                {sizes.map(s => (
                  <option key={s.value} value={s.value}>
                    {s.label}
                  </option>
                ))}
              </select>
            </div>
            
            <div className="control-group">
              <label>Ausrichtung:</label>
              <select
                value={alignment}
                onChange={(e) => {
                  setAlignment(e.target.value);
                  handleUpdate('alignment', e.target.value);
                }}
              >
                {alignments.map(a => (
                  <option key={a.value} value={a.value}>
                    {a.label}
                  </option>
                ))}
              </select>
            </div>
          </div>
          
          <div className="control-group">
            <label>
              <input
                type="checkbox"
                checked={openInNewTab}
                onChange={(e) => {
                  setOpenInNewTab(e.target.checked);
                  handleUpdate('openInNewTab', e.target.checked);
                }}
              />
              In neuem Tab öffnen
            </label>
          </div>
        </div>
        
        <div className={`button-preview alignment-${alignment}`}>
          <button 
            className={`btn btn-${style} btn-${size}`}
            disabled
          >
            {text}
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="button-block">
      <div className={`button-wrapper alignment-${alignment}`}>
        {url ? (
          <a
            href={url}
            className={`btn btn-${style} btn-${size}`}
            target={openInNewTab ? '_blank' : '_self'}
            rel={openInNewTab ? 'noopener noreferrer' : ''}
          >
            {text}
          </a>
        ) : (
          <button className={`btn btn-${style} btn-${size}`}>
            {text}
          </button>
        )}
      </div>
    </div>
  );
};

export default ButtonBlock;