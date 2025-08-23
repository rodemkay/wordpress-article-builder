import React, { useState, useEffect } from 'react';

const ImageBlock = ({ block, isEditing, onUpdate }) => {
  const [src, setSrc] = useState(block.src || '');
  const [alt, setAlt] = useState(block.alt || '');
  const [caption, setCaption] = useState(block.caption || '');
  const [width, setWidth] = useState(block.width || 'full');

  useEffect(() => {
    setSrc(block.src || '');
    setAlt(block.alt || '');
    setCaption(block.caption || '');
    setWidth(block.width || 'full');
  }, [block]);

  const handleImageChange = (field, value) => {
    const updates = { src, alt, caption, width, [field]: value };
    onUpdate && onUpdate(updates);
  };

  const handleImageUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (event) => {
        const newSrc = event.target.result;
        setSrc(newSrc);
        handleImageChange('src', newSrc);
      };
      reader.readAsDataURL(file);
    }
  };

  if (isEditing) {
    return (
      <div className="image-block editing">
        <div className="image-controls">
          <div className="control-group">
            <label>Bild auswählen:</label>
            <input
              type="file"
              accept="image/*"
              onChange={handleImageUpload}
            />
          </div>
          
          <div className="control-group">
            <label>Oder URL eingeben:</label>
            <input
              type="url"
              value={src}
              onChange={(e) => {
                setSrc(e.target.value);
                handleImageChange('src', e.target.value);
              }}
              placeholder="https://example.com/image.jpg"
            />
          </div>
          
          <div className="control-group">
            <label>Alt-Text:</label>
            <input
              type="text"
              value={alt}
              onChange={(e) => {
                setAlt(e.target.value);
                handleImageChange('alt', e.target.value);
              }}
              placeholder="Beschreibung des Bildes"
            />
          </div>
          
          <div className="control-group">
            <label>Bildunterschrift:</label>
            <input
              type="text"
              value={caption}
              onChange={(e) => {
                setCaption(e.target.value);
                handleImageChange('caption', e.target.value);
              }}
              placeholder="Optionale Bildunterschrift"
            />
          </div>
          
          <div className="control-group">
            <label>Breite:</label>
            <select
              value={width}
              onChange={(e) => {
                setWidth(e.target.value);
                handleImageChange('width', e.target.value);
              }}
            >
              <option value="small">Klein (25%)</option>
              <option value="medium">Mittel (50%)</option>
              <option value="large">Groß (75%)</option>
              <option value="full">Vollbreite (100%)</option>
            </select>
          </div>
        </div>
        
        {src && (
          <div className={`image-preview width-${width}`}>
            <img src={src} alt={alt} />
            {caption && <figcaption>{caption}</figcaption>}
          </div>
        )}
        
        {!src && (
          <div className="image-placeholder">
            <div className="placeholder-content">
              <span>📷</span>
              <p>Bild hochladen oder URL eingeben</p>
            </div>
          </div>
        )}
      </div>
    );
  }

  if (!src) {
    return null;
  }

  return (
    <div className="image-block">
      <figure className={`image-figure width-${width}`}>
        <img src={src} alt={alt} />
        {caption && <figcaption>{caption}</figcaption>}
      </figure>
    </div>
  );
};

export default ImageBlock;