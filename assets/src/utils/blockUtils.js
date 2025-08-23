import { v4 as uuidv4 } from 'uuid';

/**
 * Block utility functions
 */

export const createBlock = (type, data = {}) => {
  const defaultData = {
    paragraph: { content: '' },
    heading: { content: '', level: 2 },
    image: { src: '', alt: '', caption: '', width: 'full' },
    quote: { quote: '', author: '' },
    list: { items: [''], listType: 'ul' },
    code: { code: '', language: 'javascript' },
    divider: { style: 'solid', width: 'full' },
    button: { 
      text: 'Button Text', 
      url: '', 
      style: 'primary', 
      size: 'medium', 
      alignment: 'left', 
      openInNewTab: false 
    }
  };

  return {
    id: uuidv4(),
    type,
    ...defaultData[type],
    ...data
  };
};

export const validateBlock = (block) => {
  if (!block.id || !block.type) {
    return false;
  }

  switch (block.type) {
    case 'paragraph':
      return typeof block.content === 'string';
    
    case 'heading':
      return typeof block.content === 'string' && 
             typeof block.level === 'number' && 
             block.level >= 1 && 
             block.level <= 6;
    
    case 'image':
      return typeof block.src === 'string';
    
    case 'quote':
      return typeof block.quote === 'string';
    
    case 'list':
      return Array.isArray(block.items) && 
             ['ul', 'ol'].includes(block.listType);
    
    case 'code':
      return typeof block.code === 'string' && 
             typeof block.language === 'string';
    
    case 'divider':
      return ['solid', 'dashed', 'dotted', 'double'].includes(block.style) &&
             ['narrow', 'medium', 'wide', 'full'].includes(block.width);
    
    case 'button':
      return typeof block.text === 'string' &&
             typeof block.url === 'string' &&
             ['primary', 'secondary', 'outline', 'ghost'].includes(block.style) &&
             ['small', 'medium', 'large'].includes(block.size) &&
             ['left', 'center', 'right'].includes(block.alignment);
    
    default:
      return false;
  }
};

export const sanitizeBlock = (block) => {
  const sanitized = { ...block };

  switch (block.type) {
    case 'paragraph':
      sanitized.content = sanitized.content.replace(/<[^>]*>/g, '');
      break;
    
    case 'heading':
      sanitized.content = sanitized.content.replace(/<[^>]*>/g, '');
      sanitized.level = Math.max(1, Math.min(6, parseInt(sanitized.level) || 2));
      break;
    
    case 'image':
      // Validate URL format
      if (sanitized.src && !isValidUrl(sanitized.src) && !sanitized.src.startsWith('data:')) {
        sanitized.src = '';
      }
      sanitized.alt = sanitized.alt.replace(/<[^>]*>/g, '');
      sanitized.caption = sanitized.caption.replace(/<[^>]*>/g, '');
      break;
    
    case 'quote':
      sanitized.quote = sanitized.quote.replace(/<[^>]*>/g, '');
      sanitized.author = sanitized.author.replace(/<[^>]*>/g, '');
      break;
    
    case 'list':
      sanitized.items = sanitized.items.map(item => 
        typeof item === 'string' ? item.replace(/<[^>]*>/g, '') : ''
      );
      if (!['ul', 'ol'].includes(sanitized.listType)) {
        sanitized.listType = 'ul';
      }
      break;
    
    case 'code':
      // Keep code as-is, don't sanitize
      break;
    
    case 'button':
      sanitized.text = sanitized.text.replace(/<[^>]*>/g, '');
      if (sanitized.url && !isValidUrl(sanitized.url)) {
        sanitized.url = '';
      }
      break;
  }

  return sanitized;
};

export const isValidUrl = (string) => {
  try {
    new URL(string);
    return true;
  } catch (_) {
    return false;
  }
};

export const exportBlocks = (blocks) => {
  return JSON.stringify(blocks.map(sanitizeBlock), null, 2);
};

export const importBlocks = (jsonString) => {
  try {
    const blocks = JSON.parse(jsonString);
    if (!Array.isArray(blocks)) {
      throw new Error('Invalid format: expected array of blocks');
    }
    
    return blocks
      .map(sanitizeBlock)
      .filter(validateBlock);
  } catch (error) {
    throw new Error(`Import failed: ${error.message}`);
  }
};

export const getBlockPreview = (block, maxLength = 100) => {
  switch (block.type) {
    case 'paragraph':
      return block.content.length > maxLength 
        ? block.content.substring(0, maxLength) + '...'
        : block.content;
    
    case 'heading':
      return `H${block.level}: ${block.content}`;
    
    case 'image':
      return block.caption || block.alt || 'Bild';
    
    case 'quote':
      const quote = block.quote.length > maxLength 
        ? block.quote.substring(0, maxLength) + '...'
        : block.quote;
      return `"${quote}"${block.author ? ` - ${block.author}` : ''}`;
    
    case 'list':
      const itemCount = block.items.filter(item => item.trim()).length;
      return `${block.listType === 'ol' ? 'Nummerierte Liste' : 'Aufzählung'} (${itemCount} Elemente)`;
    
    case 'code':
      return `Code (${block.language})`;
    
    case 'divider':
      return 'Trenner';
    
    case 'button':
      return `Button: ${block.text}`;
    
    default:
      return 'Unbekannter Block';
  }
};