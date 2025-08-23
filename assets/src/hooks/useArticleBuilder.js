import { useState, useEffect, useCallback } from 'react';
import { v4 as uuidv4 } from 'uuid';

const defaultBlockData = {
  paragraph: { content: '' },
  heading: { content: '', level: 2 },
  image: { src: '', alt: '', caption: '', width: 'full' },
  quote: { quote: '', author: '' },
  list: { items: [''], listType: 'ul' },
  code: { code: '', language: 'javascript' },
  divider: { style: 'solid', width: 'full' },
  button: { text: 'Button Text', url: '', style: 'primary', size: 'medium', alignment: 'left', openInNewTab: false }
};

export const useArticleBuilder = (initialBlocks = []) => {
  const [blocks, setBlocks] = useState(initialBlocks);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState(null);

  // Load blocks from localStorage on mount
  useEffect(() => {
    const savedBlocks = localStorage.getItem('article-builder-blocks');
    if (savedBlocks) {
      try {
        const parsedBlocks = JSON.parse(savedBlocks);
        setBlocks(parsedBlocks);
      } catch (e) {
        console.error('Error loading saved blocks:', e);
      }
    }
  }, []);

  // Save blocks to localStorage when they change
  useEffect(() => {
    localStorage.setItem('article-builder-blocks', JSON.stringify(blocks));
  }, [blocks]);

  const addBlock = useCallback((blockType, position = -1) => {
    const newBlock = {
      id: uuidv4(),
      type: blockType,
      ...defaultBlockData[blockType]
    };

    setBlocks(prevBlocks => {
      if (position === -1) {
        return [...prevBlocks, newBlock];
      } else {
        const newBlocks = [...prevBlocks];
        newBlocks.splice(position, 0, newBlock);
        return newBlocks;
      }
    });
  }, []);

  const removeBlock = useCallback((index) => {
    setBlocks(prevBlocks => prevBlocks.filter((_, i) => i !== index));
  }, []);

  const updateBlock = useCallback((index, updatedData) => {
    setBlocks(prevBlocks => 
      prevBlocks.map((block, i) => 
        i === index ? { ...block, ...updatedData } : block
      )
    );
  }, []);

  const moveBlock = useCallback((fromIndex, toIndex) => {
    setBlocks(prevBlocks => {
      const newBlocks = [...prevBlocks];
      const [movedBlock] = newBlocks.splice(fromIndex, 1);
      newBlocks.splice(toIndex, 0, movedBlock);
      return newBlocks;
    });
  }, []);

  const duplicateBlock = useCallback((index) => {
    setBlocks(prevBlocks => {
      const blockToDuplicate = prevBlocks[index];
      const duplicatedBlock = {
        ...blockToDuplicate,
        id: uuidv4()
      };
      const newBlocks = [...prevBlocks];
      newBlocks.splice(index + 1, 0, duplicatedBlock);
      return newBlocks;
    });
  }, []);

  const clearAllBlocks = useCallback(() => {
    setBlocks([]);
  }, []);

  const saveArticle = useCallback(async () => {
    setIsLoading(true);
    setError(null);

    try {
      const response = await fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          action: 'save_article_builder_content',
          blocks: JSON.stringify(blocks),
          nonce: window.articleBuilderNonce || ''
        })
      });

      const result = await response.json();

      if (!result.success) {
        throw new Error(result.data || 'Fehler beim Speichern');
      }

      // Show success message
      setError(null);
      
    } catch (err) {
      setError(err.message);
    } finally {
      setIsLoading(false);
    }
  }, [blocks]);

  const loadArticle = useCallback(async (articleId) => {
    setIsLoading(true);
    setError(null);

    try {
      const response = await fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          action: 'load_article_builder_content',
          article_id: articleId,
          nonce: window.articleBuilderNonce || ''
        })
      });

      const result = await response.json();

      if (!result.success) {
        throw new Error(result.data || 'Fehler beim Laden');
      }

      setBlocks(result.data.blocks || []);
      
    } catch (err) {
      setError(err.message);
    } finally {
      setIsLoading(false);
    }
  }, []);

  return {
    blocks,
    addBlock,
    removeBlock,
    updateBlock,
    moveBlock,
    duplicateBlock,
    clearAllBlocks,
    saveArticle,
    loadArticle,
    isLoading,
    error
  };
};