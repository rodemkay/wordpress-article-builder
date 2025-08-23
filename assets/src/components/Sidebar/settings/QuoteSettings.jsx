import React from 'react';

const QuoteSettings = ({ block, onUpdate }) => {
  const handleQuoteChange = (e) => {
    onUpdate({ quote: e.target.value });
  };

  const handleAuthorChange = (e) => {
    onUpdate({ author: e.target.value });
  };

  return (
    <div className="quote-settings">
      <div className="form-group">
        <label htmlFor="quote-text">Zitat-Text:</label>
        <textarea
          id="quote-text"
          value={block.quote || ''}
          onChange={handleQuoteChange}
          placeholder="Das zu zitierende Text..."
          rows={3}
        />
      </div>
      
      <div className="form-group">
        <label htmlFor="quote-author">Autor (optional):</label>
        <input
          type="text"
          id="quote-author"
          value={block.author || ''}
          onChange={handleAuthorChange}
          placeholder="Name des Autors"
        />
        <div className="help-text">
          Der Name wird mit "—" vorangestellt angezeigt.
        </div>
      </div>
    </div>
  );
};

export default QuoteSettings;