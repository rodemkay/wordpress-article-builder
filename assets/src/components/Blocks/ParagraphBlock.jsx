import React, { useState, useEffect } from 'react';

const ParagraphBlock = ({ block, isEditing, onUpdate }) => {
  const [content, setContent] = useState(block.content || '');
  const [isEditable, setIsEditable] = useState(false);

  useEffect(() => {
    setContent(block.content || '');
  }, [block.content]);

  const handleContentChange = (e) => {
    const newContent = e.target.textContent;
    setContent(newContent);
    onUpdate && onUpdate({ content: newContent });
  };

  const handleClick = () => {
    if (isEditing) {
      setIsEditable(true);
    }
  };

  const handleBlur = () => {
    setIsEditable(false);
  };

  const handleKeyDown = (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      setIsEditable(false);
    }
  };

  if (isEditing) {
    return (
      <div
        className={`paragraph-block ${isEditable ? 'editing' : ''}`}
        onClick={handleClick}
      >
        <p
          contentEditable={isEditable}
          suppressContentEditableWarning={true}
          onInput={handleContentChange}
          onBlur={handleBlur}
          onKeyDown={handleKeyDown}
          placeholder="Hier klicken zum Bearbeiten..."
        >
          {content || 'Hier klicken zum Bearbeiten...'}
        </p>
      </div>
    );
  }

  return (
    <div className="paragraph-block">
      <p>{content}</p>
    </div>
  );
};

export default ParagraphBlock;