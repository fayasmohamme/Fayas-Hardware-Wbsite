-- Fix existing products that have '0' in the image column
-- This will set them to empty string (NULL) instead

UPDATE products SET image = NULL WHERE image = '0' OR image = 0;

-- Verify the fix
SELECT id, name, image FROM products WHERE image = '0' OR image = 0;



