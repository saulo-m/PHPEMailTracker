# EmailTracker

A modern, full-featured email tracking application that lets you monitor when your emails are opened. This is a PHP implementation inspired by [polarspetroll's EmailTracker](https://github.com/polarspetroll/EmailTracker/) project written in Go.

> An email tracking pixel is a 1px by 1px square image created by a line of code that is inserted into an email message. It's not obvious to the recipient that email tracking pixels are present because they are often transparent and placed somewhere discreet in the header or footer of the email. "*nutshell.com*"

## Features

- 📧 Generate unique tracking tokens for your emails
- 🔍 Create tracking pixel URLs to embed in emails
- 📊 Monitor when and how many times your emails are opened
- 📱 Track detailed information about the recipient:
  - IP address and geolocation
  - Device and browser details
  - Operating system
  - Timestamp of each open
- 📈 View a timeline of all email opens
- 🔄 Track multiple opens per email (not just the first)
- 🗂️ Access your recently used tokens easily
- 📋 One-click copying of tracking URLs
- 🌙 Modern dark-mode interface
- 🔒 Secure, database-backed storage

## Installation

1. Clone this repository to your local machine or server:
   ```bash
   git clone https://github.com/saulo-m/PHPEMailTracker.git
   ```

2. Upload the `EmailTracker` directory to your PHP-enabled web server

3. Copy `.env.example` to `.env` and update the database configuration:
   ```
   DB_HOST=localhost
   DB_NAME=your_database_name
   DB_USER=your_database_user
   DB_PASS=your_database_password
   ```

4. Make sure your PHP environment meets the requirements:
   - PHP 7.4 or higher
   - MySQL/MariaDB
   - PHP cURL extension enabled
   - PHP mysqli extension enabled

5. Run the installation script by visiting:
   ```
   http://your-domain.com/path/to/EmailTracker/install.php
   ```

6. After successful installation, delete or rename `install.php` for security

## Usage

### Creating a Tracking Pixel

1. Visit the application in your web browser
2. Click "Generate New Token" to create a unique tracking token
3. Copy the generated Image URL 
4. Insert the image URL in your email HTML:
   ```html
   <img src="http://your-domain.com/path/to/EmailTracker/image/?token=YOUR_TOKEN" width="1" height="1" alt="">
   ```
5. Send your email

### Tracking Email Opens

1. Go to the "Track Results" tab
2. Enter your tracking token in the search box or select from your recent tokens
3. View detailed information about when and how many times your email was opened
4. See a timeline of all email opens with details

## Security and Privacy Considerations

- This application is for educational and legitimate business purposes only
- Always comply with relevant privacy laws and regulations in your jurisdiction, such as:
  - GDPR in Europe
  - CCPA in California
  - CAN-SPAM Act in the USA
- Always inform email recipients that tracking may be in use
- Keep your tokens secure to prevent unauthorized access to tracking data
- Consider implementing additional security measures such as:
  - Access controls
  - Rate limiting
  - IP filtering

## Database Structure

The application uses three tables:

### tokens
- `id`: Auto-increment primary key
- `token`: Unique 40-character token
- `created_at`: Timestamp of token creation
- `total_opens`: Counter for total number of opens

### tracking_data
- `id`: Auto-increment primary key
- `token_id`: Foreign key reference to tokens.id
- `ip_address`: IP address of the recipient
- `user_agent`: User agent string
- `device_info`: Parsed device information
- `geo_location`: Geolocation data
- `opened_at`: Timestamp of when the email was opened

### token_history
- `id`: Auto-increment primary key
- `user_ip`: IP address of the user viewing token data
- `token_id`: Foreign key reference to tokens.id
- `last_viewed`: Timestamp of when token data was last viewed

## License

This project is licensed under the [Creative Commons Attribution-NonCommercial 4.0 International License](http://creativecommons.org/licenses/by-nc/4.0/) - see the LICENSE file for details.

This means you are free to:
- Share: copy and redistribute the material in any medium or format
- Adapt: remix, transform, and build upon the material

Under the following terms:
- **Attribution**: You must give appropriate credit, provide a link to the license, and indicate if changes were made.
- **NonCommercial**: You may not use the material for commercial purposes.

![CC BY-NC 4.0](https://i.creativecommons.org/l/by-nc/4.0/88x31.png)

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Acknowledgements

- [polarspetroll/EmailTracker](https://github.com/polarspetroll/EmailTracker/) - The original Go implementation that inspired this PHP version
- [ip-api.com](https://ip-api.com/) for geolocation data
- [Bootstrap](https://getbootstrap.com/) for UI components
- [Font Awesome](https://fontawesome.com/) for icons
- [Moment.js](https://momentjs.com/) for date formatting
