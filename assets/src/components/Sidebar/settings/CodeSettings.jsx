import React from 'react';

const CodeSettings = ({ block, onUpdate }) => {
  const handleCodeChange = (e) => {
    onUpdate({ code: e.target.value });
  };

  const handleLanguageChange = (e) => {
    onUpdate({ language: e.target.value });
  };

  const languages = [
    { value: 'javascript', label: 'JavaScript' },
    { value: 'html', label: 'HTML' },
    { value: 'css', label: 'CSS' },
    { value: 'php', label: 'PHP' },
    { value: 'python', label: 'Python' },
    { value: 'java', label: 'Java' },
    { value: 'bash', label: 'Bash/Shell' },
    { value: 'json', label: 'JSON' },
    { value: 'xml', label: 'XML' },
    { value: 'sql', label: 'SQL' },
    { value: 'typescript', label: 'TypeScript' },
    { value: 'jsx', label: 'JSX' },
    { value: 'scss', label: 'SCSS' },
    { value: 'yaml', label: 'YAML' },
    { value: 'markdown', label: 'Markdown' }
  ];

  return (
    <div className="code-settings">
      <div className="form-group">
        <label htmlFor="code-language">Programmiersprache:</label>
        <select
          id="code-language"
          value={block.language || 'javascript'}
          onChange={handleLanguageChange}
        >
          {languages.map(lang => (
            <option key={lang.value} value={lang.value}>
              {lang.label}
            </option>
          ))}
        </select>
        <div className="help-text">
          Die Sprache wird für Syntax-Highlighting verwendet.
        </div>
      </div>
      
      <div className="form-group">
        <label htmlFor="code-content">Code:</label>
        <textarea
          id="code-content"
          value={block.code || ''}
          onChange={handleCodeChange}
          placeholder="Code hier eingeben..."
          rows={8}
          style={{ fontFamily: 'monospace', fontSize: '0.875rem' }}
        />
        <div className="help-text">
          Der Code wird in einem formatierten Block angezeigt.
        </div>
      </div>
    </div>
  );
};

export default CodeSettings;