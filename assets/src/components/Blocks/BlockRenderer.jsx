import React from 'react';
import ParagraphBlock from './ParagraphBlock';
import HeadingBlock from './HeadingBlock';
import ImageBlock from './ImageBlock';
import QuoteBlock from './QuoteBlock';
import ListBlock from './ListBlock';
import CodeBlock from './CodeBlock';
import DividerBlock from './DividerBlock';
import ButtonBlock from './ButtonBlock';

const blockComponents = {
  paragraph: ParagraphBlock,
  heading: HeadingBlock,
  image: ImageBlock,
  quote: QuoteBlock,
  list: ListBlock,
  code: CodeBlock,
  divider: DividerBlock,
  button: ButtonBlock
};

const BlockRenderer = ({ block, isEditing = false, onUpdate }) => {
  const Component = blockComponents[block.type];

  if (!Component) {
    return (
      <div className="block-error">
        <p>Unbekannter Block-Typ: {block.type}</p>
      </div>
    );
  }

  return (
    <div className={`block-wrapper block-wrapper--${block.type}`}>
      <Component
        block={block}
        isEditing={isEditing}
        onUpdate={onUpdate}
      />
    </div>
  );
};

export default BlockRenderer;