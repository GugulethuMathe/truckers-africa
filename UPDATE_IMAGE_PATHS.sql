-- ========================================
-- SQL Script to Update Image Paths
-- Remove 'public/' prefix from all image paths
-- ========================================
--
-- Run this after moving images from public/uploads/ to uploads/
-- This updates the database to match the new structure
--

-- Update merchant_listings main_image_path
UPDATE merchant_listings
SET main_image_path = REPLACE(main_image_path, 'public/uploads/', 'uploads/')
WHERE main_image_path LIKE 'public/uploads/%';

-- Update merchant_listing_images image_path
UPDATE merchant_listing_images
SET image_path = REPLACE(image_path, 'public/uploads/', 'uploads/')
WHERE image_path LIKE 'public/uploads/%';

-- Update listing_requests main_image
UPDATE listing_requests
SET main_image = REPLACE(main_image, 'public/uploads/', 'uploads/')
WHERE main_image LIKE 'public/uploads/%';

-- Update listing_requests gallery_images JSON
UPDATE listing_requests
SET gallery_images = REPLACE(gallery_images, 'public/uploads/', 'uploads/')
WHERE gallery_images LIKE '%public/uploads/%';

-- Update merchants profile_image_path
UPDATE merchants
SET profile_image_path = REPLACE(profile_image_path, 'public/uploads/', 'uploads/')
WHERE profile_image_path LIKE 'public/uploads/%';

-- Update merchants business_image_path
UPDATE merchants
SET business_image_path = REPLACE(business_image_path, 'public/uploads/', 'uploads/')
WHERE business_image_path LIKE 'public/uploads/%';

-- Update truck_drivers profile_picture
UPDATE truck_drivers
SET profile_picture = REPLACE(profile_picture, 'public/uploads/', 'uploads/')
WHERE profile_picture LIKE 'public/uploads/%';

-- Check results (run these after the updates)
-- SELECT id, main_image_path FROM merchant_listings WHERE main_image_path LIKE '%uploads/%' LIMIT 10;
-- SELECT id, image_path FROM merchant_listing_images WHERE image_path LIKE '%uploads/%' LIMIT 10;
-- SELECT id, main_image FROM listing_requests WHERE main_image LIKE '%uploads/%' LIMIT 10;

-- ========================================
-- Verification Queries
-- ========================================

-- Check if any 'public/' prefixes remain
SELECT 'merchant_listings with public/' as table_name, COUNT(*) as count
FROM merchant_listings
WHERE main_image_path LIKE 'public/uploads/%'
UNION ALL
SELECT 'merchant_listing_images with public/', COUNT(*)
FROM merchant_listing_images
WHERE image_path LIKE 'public/uploads/%'
UNION ALL
SELECT 'listing_requests with public/', COUNT(*)
FROM listing_requests
WHERE main_image LIKE 'public/uploads/%'
UNION ALL
SELECT 'merchants profile with public/', COUNT(*)
FROM merchants
WHERE profile_image_path LIKE 'public/uploads/%'
UNION ALL
SELECT 'merchants business with public/', COUNT(*)
FROM merchants
WHERE business_image_path LIKE 'public/uploads/%'
UNION ALL
SELECT 'truck_drivers with public/', COUNT(*)
FROM truck_drivers
WHERE profile_picture LIKE 'public/uploads/%';
