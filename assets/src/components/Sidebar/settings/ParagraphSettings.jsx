import React from 'react';

const ParagraphSettings = ({ block, onUpdate }) => {
  const handleContentChange = (e) => {
    onUpdate({ content: e.target.value });
  };

  return (
    <div className="paragraph-settings">
      <div className="form-group">
        <label htmlFor="paragraph-content">Text-Inhalt:</label>
        <textarea
          id="paragraph-content"
          value={block.content || ''}
          onChange={handleContentChange}
          placeholder="Geben Sie hier Ihren Text ein..."
          rows={4}
        />
        <div className="help-text">
          Der Haupttext dieses Absatzes.
        </div>
      </div>
    </div>
  );
};

export default ParagraphSettings;