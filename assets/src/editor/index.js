import React from 'react';
import { createRoot } from 'react-dom/client';
import BlockEditor from '../components/BlockEditor/BlockEditor';
import '../styles/editor.scss';

// Initialize Block Editor for Gutenberg
document.addEventListener('DOMContentLoaded', () => {
  const editorContainer = document.getElementById('article-builder-editor');
  
  if (editorContainer) {
    const root = createRoot(editorContainer);
    root.render(<BlockEditor />);
  }
});

// WordPress Block Registration
if (window.wp && window.wp.blocks) {
  const { registerBlockType } = window.wp.blocks;
  
  registerBlockType('article-builder/visual-block', {
    title: 'Article Builder Block',
    icon: 'edit',
    category: 'common',
    attributes: {
      content: {
        type: 'object',
        default: {}
      }
    },
    edit: BlockEditor,
    save: () => null // Server-side rendering
  });
}

// Make components available globally
window.ArticleBuilderEditor = {
  BlockEditor
};