# Installation Guide

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- WooCommerce plugin (for product integration)

## Installation Steps

### Method 1: Upload via WordPress Admin

1. Download the `interactive-product-showcase.zip` file
2. Log in to your WordPress admin panel
3. Navigate to **Plugins → Add New**
4. Click **Upload Plugin** button
5. Choose the downloaded ZIP file
6. Click **Install Now**
7. After installation, click **Activate Plugin**

### Method 2: FTP Upload

1. Extract the `interactive-product-showcase.zip` file
2. Upload the `interactive-product-showcase` folder to `/wp-content/plugins/`
3. Log in to your WordPress admin panel
4. Navigate to **Plugins**
5. Find **Interactive Product Showcase** and click **Activate**

### Method 3: Manual Installation

1. Download and extract the plugin files
2. Copy the entire `interactive-product-showcase` folder
3. Paste it into your `/wp-content/plugins/` directory
4. Activate the plugin from the WordPress Plugins page

## Post-Installation Setup

1. After activation, you'll see **Product Showcase** in your WordPress admin menu
2. Click on **Product Showcase**
3. Go to the **Design** tab to customize colors and button text
4. Navigate to **Add Image** tab to create your first showcase
5. Use the **Images** tab to manage all your showcases

## Database Tables

The plugin will automatically create the following tables:

- `wp_ips_showcases` - Stores showcase data
- `wp_ips_settings` - Stores plugin settings

## First Showcase

1. Go to **Product Showcase → Add Image**
2. Enter a showcase title
3. Click **Select Image** and choose an image
4. Click on the image where you want to add product hotspots
5. Search for a product in the modal that appears
6. Select the product and customize the description if needed
7. Click **Add Hotspot**
8. Repeat for all products you want to showcase
9. Click **Save Showcase**
10. Copy the generated shortcode

## Using the Shortcode

Add the shortcode to any page or post:
```
[ips_showcase id="1"]
```

Replace `1` with your showcase ID.

## Troubleshooting

### Hotspots not appearing

- Clear your browser cache
- Check if WooCommerce is activated
- Verify that products exist in your store

### Images not loading

- Check file permissions on uploads directory
- Verify image URLs are accessible
- Try re-uploading the image

### Shortcode not working

- Make sure the showcase ID is correct
- Check if the shortcode is properly formatted
- Verify the showcase exists in the Images tab

## Uninstallation

If you need to remove the plugin:

1. Deactivate the plugin from the Plugins page
2. Click **Delete** to remove all plugin files and database tables
3. All showcases and settings will be permanently deleted

## Support

For support, please visit: https://praxtify.com/support
```

Şimdi eklentiyi ZIP dosyası olarak paketlemek için gereken son bir dosya - **.gitignore** (opsiyonel):
```
# OS Files
.DS_Store
Thumbs.db

# IDE Files
.idea/
.vscode/
*.sublime-project
*.sublime-workspace

# Node modules
node_modules/
npm-debug.log

# Build files
*.map
dist/

# WordPress
wp-config.php
wp-content/uploads/
wp-content/cache/

# Temporary files
*.tmp
*.bak
*.swp
*~
```

Tüm dosyaları hazırladım. İşte kullanım talimatları:

## Eklenti Kurulum Adımları:

1. **Klasör yapısını oluşturun:**
   - `interactive-product-showcase` ana klasörü oluşturun
   - İçine tüm dosyaları ve alt klasörleri yerleştirin

2. **Dosyaları yerleştirin:**
```
   interactive-product-showcase/
   ├── interactive-product-showcase.php (ana dosya)
   ├── readme.txt
   ├── uninstall.php
   ├── includes/ (3 PHP dosyası)
   ├── assets/css/ (2 CSS dosyası)
   ├── assets/js/ (2 JS dosyası)
   └── languages/ (.pot dosyası)