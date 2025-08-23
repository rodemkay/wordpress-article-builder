import React from 'react';

const ButtonSettings = ({ block, onUpdate }) => {
  const handleTextChange = (e) => {
    onUpdate({ text: e.target.value });
  };

  const handleUrlChange = (e) => {
    onUpdate({ url: e.target.value });
  };

  const handleStyleChange = (e) => {
    onUpdate({ style: e.target.value });
  };

  const handleSizeChange = (e) => {
    onUpdate({ size: e.target.value });
  };

  const handleAlignmentChange = (e) => {
    onUpdate({ alignment: e.target.value });
  };

  const handleNewTabChange = (e) => {
    onUpdate({ openInNewTab: e.target.checked });
  };

  const styles = [
    { value: 'primary', label: 'Primär (Blau)' },
    { value: 'secondary', label: 'Sekundär (Grau)' },
    { value: 'outline', label: 'Umriss' },
    { value: 'ghost', label: 'Ghost (Transparent)' }
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

  return (
    <div className="button-settings">
      <div className="form-group">
        <label htmlFor="button-text">Button-Text:</label>
        <input
          type="text"
          id="button-text"
          value={block.text || ''}
          onChange={handleTextChange}
          placeholder="Button Text"
        />
      </div>
      
      <div className="form-group">
        <label htmlFor="button-url">Link-URL:</label>
        <input
          type="url"
          id="button-url"
          value={block.url || ''}
          onChange={handleUrlChange}
          placeholder="https://example.com"
        />
        <div className="help-text">
          Wohin soll der Button verlinken?
        </div>
      </div>
      
      <div className="form-group">
        <label htmlFor="button-style">Button-Stil:</label>
        <select
          id="button-style"
          value={block.style || 'primary'}
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
        <label htmlFor="button-size">Button-Größe:</label>
        <select
          id="button-size"
          value={block.size || 'medium'}
          onChange={handleSizeChange}
        >
          {sizes.map(size => (
            <option key={size.value} value={size.value}>
              {size.label}
            </option>
          ))}
        </select>
      </div>
      
      <div className="form-group">
        <label htmlFor="button-alignment">Ausrichtung:</label>
        <select
          id="button-alignment"
          value={block.alignment || 'left'}
          onChange={handleAlignmentChange}
        >
          {alignments.map(alignment => (
            <option key={alignment.value} value={alignment.value}>
              {alignment.label}
            </option>
          ))}
        </select>
      </div>
      
      <div className="form-group">
        <label>
          <input
            type="checkbox"
            checked={block.openInNewTab || false}
            onChange={handleNewTabChange}
            style={{ marginRight: '0.5rem' }}
          />
          In neuem Tab öffnen
        </label>
        <div className="help-text">
          Empfohlen für externe Links.
        </div>
      </div>
      
      <div className="form-group">
        <label>Vorschau:</label>
        <div style={{ 
          padding: '1rem', 
          background: '#f6f7f7', 
          borderRadius: '4px',
          marginTop: '0.5rem',
          textAlign: block.alignment || 'left'
        }}>
          <button
            style={{
              padding: 
                block.size === 'small' ? '0.25rem 0.75rem' :
                block.size === 'large' ? '0.75rem 1.5rem' : '0.5rem 1rem',
              fontSize:
                block.size === 'small' ? '0.75rem' :
                block.size === 'large' ? '1rem' : '0.875rem',
              backgroundColor:
                block.style === 'primary' ? '#0073aa' :
                block.style === 'secondary' ? '#ffffff' :
                block.style === 'outline' ? 'transparent' :
                block.style === 'ghost' ? 'transparent' : '#0073aa',
              color:
                block.style === 'primary' ? 'white' :
                block.style === 'secondary' ? '#1d2327' :
                block.style === 'outline' ? '#0073aa' :
                block.style === 'ghost' ? '#1d2327' : 'white',
              border:
                block.style === 'outline' ? '1px solid #0073aa' :
                block.style === 'secondary' ? '1px solid #dcdcde' :
                block.style === 'ghost' ? '1px solid transparent' : '1px solid transparent',
              borderRadius: '4px',
              cursor: 'pointer'
            }}
            disabled
          >
            {block.text || 'Button Text'}
          </button>
        </div>
      </div>
    </div>
  );
};

export default ButtonSettings;