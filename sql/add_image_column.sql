-- Migration: Add image column to medicines table
-- Run this if you already have the medicines table created

-- Add image column
ALTER TABLE medicines 
ADD COLUMN image VARCHAR(255) DEFAULT 'default-medicine.svg' AFTER expiry_date;

-- Update existing records with category-based default images
UPDATE medicines m
LEFT JOIN categories c ON m.category_id = c.id
SET m.image = CASE 
    WHEN c.name = 'Diabetes' THEN 'injection.svg'
    WHEN c.name = 'Cardiac' THEN 'tablet.svg'
    WHEN c.name = 'Antibiotics' THEN 'tablet.svg'
    WHEN c.name = 'Pain Relief' THEN 'cream.svg'
    WHEN c.name LIKE '%Syrup%' THEN 'syrup.svg'
    WHEN c.name LIKE '%Cream%' THEN 'cream.svg'
    ELSE 'default-medicine.svg'
END;

-- You can manually update specific medicines with custom images like this:
-- UPDATE medicines SET image = 'paracetamol.jpg' WHERE sku = 'PAR500';
-- UPDATE medicines SET image = 'insulin.jpg' WHERE name LIKE '%Insulin%';
