import React from 'react';
import { createRoot } from 'react-dom/client';
import AdminApp from './AdminApp';
import '../styles/admin.scss';

// Initialize Admin Interface
document.addEventListener('DOMContentLoaded', () => {
  const adminContainer = document.getElementById('article-builder-admin');
  
  if (adminContainer) {
    const root = createRoot(adminContainer);
    root.render(<AdminApp />);
  }
});

// Make components available globally for WordPress
window.ArticleBuilderAdmin = {
  AdminApp
};