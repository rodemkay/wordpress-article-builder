import React, { useState, useEffect } from 'react';

const QuoteBlock = ({ block, isEditing, onUpdate }) => {
  const [quote, setQuote] = useState(block.quote || '');
  const [author, setAuthor] = useState(block.author || '');
  const [isEditable, setIsEditable] = useState(false);

  useEffect(() => {
    setQuote(block.quote || '');
    setAuthor(block.author || '');
  }, [block]);

  const handleUpdate = (field, value) => {
    const updates = { quote, author, [field]: value };
    onUpdate && onUpdate(updates);
  };

  if (isEditing) {
    return (
      <div className="quote-block editing">
        <blockquote
          contentEditable={isEditable}
          suppressContentEditableWarning={true}
          onInput={(e) => {
            const newQuote = e.target.textContent;
            setQuote(newQuote);
            handleUpdate('quote', newQuote);
          }}
          onClick={() => setIsEditable(true)}
          onBlur={() => setIsEditable(false)}
          placeholder="Zitat eingeben..."
        >
          {quote || 'Zitat eingeben...'}
        </blockquote>
        <cite
          contentEditable
          suppressContentEditableWarning={true}
          onInput={(e) => {
            const newAuthor = e.target.textContent;
            setAuthor(newAuthor);
            handleUpdate('author', newAuthor);
          }}
          placeholder="— Autor (optional)"
        >
          {author ? `— ${author}` : '— Autor (optional)'}
        </cite>
      </div>
    );
  }

  return (
    <div className="quote-block">
      <blockquote>{quote}</blockquote>
      {author && <cite>— {author}</cite>}
    </div>
  );
};

export default QuoteBlock;