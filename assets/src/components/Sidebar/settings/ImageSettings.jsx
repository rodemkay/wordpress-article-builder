import React from 'react';

const ImageSettings = ({ block, onUpdate }) => {
  const handleSrcChange = (e) => {
    onUpdate({ src: e.target.value });
  };

  const handleAltChange = (e) => {
    onUpdate({ alt: e.target.value });
  };

  const handleCaptionChange = (e) => {
    onUpdate({ caption: e.target.value });
  };

  const handleWidthChange = (e) => {
    onUpdate({ width: e.target.value });
  };

  return (
    <div className="image-settings">
      <div className="form-group">
        <label htmlFor="image-src">Bild-URL:</label>
        <input
          type="url"
          id="image-src"
          value={block.src || ''}
          onChange={handleSrcChange}
          placeholder="https://example.com/image.jpg"
        />
        <div className="help-text">
          URL zum Bild oder Pfad in der Mediathek.
        </div>
      </div>
      
      <div className="form-group">
        <label htmlFor="image-alt">Alt-Text:</label>
        <input
          type="text"
          id="image-alt"
          value={block.alt || ''}
          onChange={handleAltChange}
          placeholder="Beschreibung des Bildes"
        />
        <div className="help-text">
          Wichtig für Barrierefreiheit und SEO.
        </div>
      </div>
      
      <div className="form-group">
        <label htmlFor="image-caption">Bildunterschrift:</label>
        <input
          type="text"
          id="image-caption"
          value={block.caption || ''}
          onChange={handleCaptionChange}
          placeholder="Optionale Bildunterschrift"
        />
      </div>
      
      <div className="form-group">
        <label htmlFor="image-width">Bildbreite:</label>
        <select
          id="image-width"
          value={block.width || 'full'}
          onChange={handleWidthChange}
        >
          <option value="small">Klein (25%)</option>
          <option value="medium">Mittel (50%)</option>
          <option value="large">Groß (75%)</option>
          <option value="full">Vollbreite (100%)</option>
        </select>
        <div className="help-text">
          Wie breit soll das Bild dargestellt werden?
        </div>
      </div>
    </div>
  );
};

export default ImageSettings;