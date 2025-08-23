import React from 'react';
import ParagraphSettings from './settings/ParagraphSettings';
import HeadingSettings from './settings/HeadingSettings';
import ImageSettings from './settings/ImageSettings';
import QuoteSettings from './settings/QuoteSettings';
import ListSettings from './settings/ListSettings';
import CodeSettings from './settings/CodeSettings';
import DividerSettings from './settings/DividerSettings';
import ButtonSettings from './settings/ButtonSettings';

const settingsComponents = {
  paragraph: ParagraphSettings,
  heading: HeadingSettings,
  image: ImageSettings,
  quote: QuoteSettings,
  list: ListSettings,
  code: CodeSettings,
  divider: DividerSettings,
  button: ButtonSettings
};

const BlockSettings = ({ block, onUpdate }) => {
  const SettingsComponent = settingsComponents[block.type];

  if (!SettingsComponent) {
    return (
      <div className="sidebar-section">
        <p>Keine Einstellungen für diesen Block-Typ verfügbar.</p>
      </div>
    );
  }

  return (
    <div className="sidebar-section">
      <h4 className="section-title">Einstellungen</h4>
      <div className="section-content">
        <SettingsComponent
          block={block}
          onUpdate={onUpdate}
        />
      </div>
    </div>
  );
};

export default BlockSettings;