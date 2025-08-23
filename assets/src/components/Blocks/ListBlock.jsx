import React, { useState, useEffect } from 'react';

const ListBlock = ({ block, isEditing, onUpdate }) => {
  const [items, setItems] = useState(block.items || ['']);
  const [listType, setListType] = useState(block.listType || 'ul');

  useEffect(() => {
    setItems(block.items || ['']);
    setListType(block.listType || 'ul');
  }, [block]);

  const handleItemChange = (index, value) => {
    const newItems = [...items];
    newItems[index] = value;
    setItems(newItems);
    onUpdate && onUpdate({ items: newItems, listType });
  };

  const addItem = () => {
    const newItems = [...items, ''];
    setItems(newItems);
    onUpdate && onUpdate({ items: newItems, listType });
  };

  const removeItem = (index) => {
    if (items.length > 1) {
      const newItems = items.filter((_, i) => i !== index);
      setItems(newItems);
      onUpdate && onUpdate({ items: newItems, listType });
    }
  };

  const handleTypeChange = (newType) => {
    setListType(newType);
    onUpdate && onUpdate({ items, listType: newType });
  };

  if (isEditing) {
    return (
      <div className="list-block editing">
        <div className="list-controls">
          <label>
            <input
              type="radio"
              checked={listType === 'ul'}
              onChange={() => handleTypeChange('ul')}
            />
            Aufzählung
          </label>
          <label>
            <input
              type="radio"
              checked={listType === 'ol'}
              onChange={() => handleTypeChange('ol')}
            />
            Nummerierte Liste
          </label>
        </div>
        
        <div className="list-items">
          {items.map((item, index) => (
            <div key={index} className="list-item-editor">
              <input
                type="text"
                value={item}
                onChange={(e) => handleItemChange(index, e.target.value)}
                placeholder={`Element ${index + 1}`}
              />
              {items.length > 1 && (
                <button
                  type="button"
                  onClick={() => removeItem(index)}
                  className="remove-item"
                >
                  ×
                </button>
              )}
            </div>
          ))}
        </div>
        
        <button
          type="button"
          onClick={addItem}
          className="add-item"
        >
          + Element hinzufügen
        </button>
      </div>
    );
  }

  const ListTag = listType;
  
  return (
    <div className="list-block">
      <ListTag>
        {items.filter(item => item.trim()).map((item, index) => (
          <li key={index}>{item}</li>
        ))}
      </ListTag>
    </div>
  );
};

export default ListBlock;