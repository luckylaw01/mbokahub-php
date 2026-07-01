# DomPDF Installation Instructions

## Option 1: Composer Installation (Recommended)

1. Open terminal/command prompt in your project root directory
2. Run the following command:
   ```
   composer require dompdf/dompdf
   ```
3. The library will be installed in `vendor/dompdf/` directory
4. Update the require path in `generate_documentation_pdf.php`:
   ```php
   require_once 'vendor/autoload.php';
   ```

## Option 2: Manual Installation

1. Download dompdf from: https://github.com/dompdf/dompdf/releases
2. Extract the files to `src/dompdf/` directory in your project
3. Make sure the autoload.inc.php file exists at `src/dompdf/autoload.inc.php`

## Troubleshooting

- If you get memory errors, increase PHP memory limit in php.ini:
  ```
  memory_limit = 256M
  ```
  
- For font rendering issues, make sure the font cache directory is writable:
  ```
  chmod 755 src/dompdf/lib/fonts/
  ```

## Testing

After installation, visit: http://localhost/seed_1/generate_documentation_pdf.php
to test if the PDF generation works.