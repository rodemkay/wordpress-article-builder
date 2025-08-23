import React from 'react';

const ListSettings = ({ block, onUpdate }) => {
  const handleTypeChange = (e) => {
    onUpdate({ listType: e.target.value });
  };

  const handleItemChange = (index, value) => {
    const newItems = [...(block.items || [])];
    newItems[index] = value;
    onUpdate({ items: newItems });
  };

  const addItem = () => {
    const newItems = [...(block.items || []), ''];
    onUpdate({ items: newItems });
  };

  const removeItem = (index) => {
    if (block.items && block.items.length > 1) {
      const newItems = block.items.filter((_, i) => i !== index);
      onUpdate({ items: newItems });
    }
  };

  return (
    <div className="list-settings">
      <div className="form-group">
        <label>Listen-Typ:</label>
        <div style={{ display: 'flex', gap: '1rem', marginTop: '0.5rem' }}>
          <label style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
            <input
              type="radio"
              value="ul"
              checked={(block.listType || 'ul') === 'ul'}
              onChange={handleTypeChange}
            />
            Aufzählung
          </label>
          <label style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
            <input
              type="radio"
              value="ol"
              checked={(block.listType || 'ul') === 'ol'}
              onChange={handleTypeChange}
            />
            Nummerierte Liste
          </label>
        </div>
      </div>
      
      <div className="form-group">
        <label>Listen-Elemente:</label>
        <div style={{ marginTop: '0.5rem' }}>
          {(block.items || ['']).map((item, index) => (
            <div key={index} style={{ display: 'flex', gap: '0.5rem', marginBottom: '0.5rem' }}>
              <input
                type="text"
                value={item}
                onChange={(e) => handleItemChange(index, e.target.value)}
                placeholder={`Element ${index + 1}`}
                style={{ flex: 1 }}
              />
              {block.items && block.items.length > 1 && (
                <button
                  type="button"
                  onClick={() => removeItem(index)}
                  style={{
                    background: '#d63638',
                    color: 'white',
                    border: 'none',
                    borderRadius: '4px',
                    width: '32px',
                    height: '32px',
                    cursor: 'pointer'
                  }}
                >
                  ×
                </button>
              )}
            </div>
          ))}
          <button
            type="button"
            onClick={addItem}
            style={{
              background: '#0073aa',
              color: 'white',
              border: 'none',
              borderRadius: '4px',
              padding: '0.5rem 1rem',
              cursor: 'pointer',
              fontSize: '0.875rem'
            }}
          >
            + Element hinzufügen
          </button>
        </div>
      </div>
    </div>
  );
};

export default ListSettings;