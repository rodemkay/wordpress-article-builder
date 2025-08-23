import React from 'react';
import { createRoot } from 'react-dom/client';
import ArticleRenderer from './ArticleRenderer';
import '../styles/frontend.scss';

// Initialize Frontend Components
document.addEventListener('DOMContentLoaded', () => {
  // Find all article builder containers
  const containers = document.querySelectorAll('.article-builder-container');
  
  containers.forEach((container) => {
    const articleData = JSON.parse(container.dataset.article || '{}');
    const root = createRoot(container);
    root.render(<ArticleRenderer article={articleData} />);
  });
});

// Make components available globally
window.ArticleBuilderFrontend = {
  ArticleRenderer
};