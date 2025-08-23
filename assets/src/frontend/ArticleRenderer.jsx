import React from 'react';
import BlockRenderer from '../components/Blocks/BlockRenderer';

const ArticleRenderer = ({ article }) => {
  if (!article || !article.blocks) {
    return null;
  }

  return (
    <div className="article-builder-frontend">
      <div className="article-content">
        {article.blocks.map((block, index) => (
          <BlockRenderer
            key={block.id || index}
            block={block}
            index={index}
          />
        ))}
      </div>
    </div>
  );
};

export default ArticleRenderer;