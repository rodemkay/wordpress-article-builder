import React from 'react';

const DividerSettings = ({ block, onUpdate }) => {
  const handleStyleChange = (e) => {
    onUpdate({ style: e.target.value });
  };

  const handleWidthChange = (e) => {
    onUpdate({ width: e.target.value });
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

  return (
    <div className="divider-settings">
      <div className="form-group">
        <label htmlFor="divider-style">Linienstil:</label>
        <select
          id="divider-style"
          value={block.style || 'solid'}
          onChange={handleStyleChange}
        >
          {styles.map(style => (
            <option key={style.value} value={style.value}>
              {style.label}
            </option>
          ))}
        </select>
      </div>
      
      <div className="form-group">
        <label htmlFor="divider-width">Linienbreite:</label>
        <select
          id="divider-width"
          value={block.width || 'full'}
          onChange={handleWidthChange}
        >
          {widths.map(width => (
            <option key={width.value} value={width.value}>
              {width.label}
            </option>
          ))}
        </select>
        <div className="help-text">
          Bestimmt, wie breit die Trennlinie angezeigt wird.
        </div>
      </div>
      
      <div className="form-group">
        <label>Vorschau:</label>
        <div style={{ 
          padding: '1rem', 
          background: '#f6f7f7', 
          borderRadius: '4px',
          marginTop: '0.5rem'
        }}>
          <hr 
            style={{
              border: 'none',
              borderTop: `2px ${block.style || 'solid'} #dcdcde`,
              margin: '0 auto',
              width: 
                block.width === 'narrow' ? '25%' :
                block.width === 'medium' ? '50%' :
                block.width === 'wide' ? '75%' : '100%'
            }}
          />
        </div>
      </div>
    </div>
  );
};

export default DividerSettings;