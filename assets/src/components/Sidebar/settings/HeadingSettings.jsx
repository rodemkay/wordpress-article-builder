import React from 'react';

const HeadingSettings = ({ block, onUpdate }) => {
  const handleContentChange = (e) => {
    onUpdate({ content: e.target.value });
  };

  const handleLevelChange = (e) => {
    onUpdate({ level: parseInt(e.target.value) });
  };

  return (
    <div className="heading-settings">
      <div className="form-group">
        <label htmlFor="heading-content">Überschrift:</label>
        <input
          type="text"
          id="heading-content"
          value={block.content || ''}
          onChange={handleContentChange}
          placeholder="Überschrift eingeben..."
        />
      </div>
      
      <div className="form-group">
        <label htmlFor="heading-level">Überschrift-Ebene:</label>
        <select
          id="heading-level"
          value={block.level || 2}
          onChange={handleLevelChange}
        >
          <option value={1}>H1 - Hauptüberschrift</option>
          <option value={2}>H2 - Unterüberschrift</option>
          <option value={3}>H3 - Abschnittsüberschrift</option>
          <option value={4}>H4 - Unterabschnitt</option>
          <option value={5}>H5 - Kleiner Titel</option>
          <option value={6}>H6 - Kleinster Titel</option>
        </select>
        <div className="help-text">
          H1 sollte nur einmal pro Seite verwendet werden.
        </div>
      </div>
    </div>
  );
};

export default HeadingSettings;