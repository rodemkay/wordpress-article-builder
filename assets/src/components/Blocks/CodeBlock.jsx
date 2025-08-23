import React, { useState, useEffect } from 'react';

const CodeBlock = ({ block, isEditing, onUpdate }) => {
  const [code, setCode] = useState(block.code || '');
  const [language, setLanguage] = useState(block.language || 'javascript');

  useEffect(() => {
    setCode(block.code || '');
    setLanguage(block.language || 'javascript');
  }, [block]);

  const handleUpdate = (field, value) => {
    const updates = { code, language, [field]: value };
    onUpdate && onUpdate(updates);
  };

  const languages = [
    { value: 'javascript', label: 'JavaScript' },
    { value: 'html', label: 'HTML' },
    { value: 'css', label: 'CSS' },
    { value: 'php', label: 'PHP' },
    { value: 'python', label: 'Python' },
    { value: 'bash', label: 'Bash' },
    { value: 'json', label: 'JSON' },
    { value: 'xml', label: 'XML' }
  ];

  if (isEditing) {
    return (
      <div className="code-block editing">
        <div className="code-controls">
          <label>Sprache:</label>
          <select
            value={language}
            onChange={(e) => {
              setLanguage(e.target.value);
              handleUpdate('language', e.target.value);
            }}
          >
            {languages.map(lang => (
              <option key={lang.value} value={lang.value}>
                {lang.label}
              </option>
            ))}
          </select>
        </div>
        
        <textarea
          value={code}
          onChange={(e) => {
            setCode(e.target.value);
            handleUpdate('code', e.target.value);
          }}
          placeholder="Code hier eingeben..."
          className="code-textarea"
          spellCheck={false}
        />
      </div>
    );
  }

  return (
    <div className="code-block">
      <div className="code-header">
        <span className="code-language">{language}</span>
      </div>
      <pre>
        <code className={`language-${language}`}>
          {code}
        </code>
      </pre>
    </div>
  );
};

export default CodeBlock;