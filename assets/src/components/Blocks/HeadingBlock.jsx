import React, { useState, useEffect } from 'react';

const HeadingBlock = ({ block, isEditing, onUpdate }) => {
  const [content, setContent] = useState(block.content || '');
  const [level, setLevel] = useState(block.level || 2);
  const [isEditable, setIsEditable] = useState(false);

  useEffect(() => {
    setContent(block.content || '');
    setLevel(block.level || 2);
  }, [block.content, block.level]);

  const handleContentChange = (e) => {
    const newContent = e.target.textContent;
    setContent(newContent);
    onUpdate && onUpdate({ content: newContent, level });
  };

  const handleLevelChange = (newLevel) => {
    setLevel(newLevel);
    onUpdate && onUpdate({ content, level: newLevel });
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

  const HeadingTag = `h${level}`;

  if (isEditing) {
    return (
      <div className={`heading-block ${isEditable ? 'editing' : ''}`}>
        <div className="heading-controls">
          {[1, 2, 3, 4, 5, 6].map(lvl => (
            <button
              key={lvl}
              className={`heading-level ${level === lvl ? 'active' : ''}`}
              onClick={() => handleLevelChange(lvl)}
            >
              H{lvl}
            </button>
          ))}
        </div>
        <HeadingTag
          contentEditable={isEditable}
          suppressContentEditableWarning={true}
          onInput={handleContentChange}
          onBlur={handleBlur}
          onKeyDown={handleKeyDown}
          onClick={handleClick}
          placeholder="Überschrift eingeben..."
        >
          {content || 'Überschrift eingeben...'}
        </HeadingTag>
      </div>
    );
  }

  return (
    <div className="heading-block">
      <HeadingTag>{content}</HeadingTag>
    </div>
  );
};

export default HeadingBlock;