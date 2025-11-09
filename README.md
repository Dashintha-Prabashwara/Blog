# 📝 Code & Canvas

> A modern, minimalist blogging platform built with PHP, MySQL, and Tailwind CSS

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

Code & Canvas is a full-featured blogging platform designed for developers and designers to share their stories, insights, and creative works. Built with a minimalist aesthetic and modern web technologies.

![Code & Canvas Preview](./assets/images/preview.png)

## ✨ Features

### Core Features
- 📱 **Responsive Design** - Mobile-first, works on all devices
- ✍️ **Markdown Editor** - Write posts with Markdown support and live preview
- 👤 **User Profiles** - Customizable profiles with avatar and bio
- 💬 **Comments System** - Nested comments with likes/dislikes
- ❤️ **Social Interactions** - Like posts, follow users, get notifications
- 🔔 **Real-time Notifications** - Stay updated with activity
- 🎨 **Topic Tags** - Organize content by topics
- 📊 **Dashboard Analytics** - Track your post performance
- 💾 **Draft System** - Auto-save drafts while writing

### Advanced Features
- 🔍 **Full-text Search** - Find posts quickly
- 🎯 **Featured Posts** - Algorithm-based post featuring
- 🖼️ **Image Uploads** - Support for post cover images
- 📝 **SEO Optimized** - Dynamic meta tags and Open Graph support
- 🔐 **Secure Authentication** - Password hashing with bcrypt
- 🚀 **Performance** - Optimized database queries with proper indexing

## 🚀 Complete Beginner's Guide

### Step 1: Setting Up Your Development Environment

1. **Install XAMPP**
   - Go to [https://www.apachefriends.org/download.html](https://www.apachefriends.org/download.html)
   - Download XAMPP for Windows (PHP 8.0 or higher)
   - Run the installer and install to `C:\xampp` (default location)
   - Click "Next" through all prompts (use default settings)

2. **Start XAMPP Services**
   - Open XAMPP Control Panel (search for it in Start menu)
   - Click "Start" button next to **Apache**
   - Click "Start" button next to **MySQL**
   - Both should show green "Running" status

### Step 2: Getting the Code

**Option A: Download ZIP (Easier for Beginners)**
1. Go to the GitHub repository page
2. Click the green "Code" button
3. Click "Download ZIP"
4. Extract the ZIP file
5. Rename the extracted folder to `Blog`
6. Move the `Blog` folder to `C:\xampp\htdocs\`
7. Final path should be: `C:\xampp\htdocs\Blog\`

**Option B: Using Git (If you installed Git)**
1. Press `Win + R` on your keyboard
2. Type `cmd` and press Enter
3. Copy and paste these commands one by one:
   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/Dashintha-Prabashwara/code-canvas.git Blog
   cd Blog
   ```

### Step 3: Database Setup

1. **Open phpMyAdmin**
   - Make sure Apache and MySQL are running in XAMPP
   - Open your web browser (Chrome, Firefox, Edge)
   - Go to: `http://localhost/phpmyadmin`
   - You should see the phpMyAdmin interface

2. **Create the Database**
   - Click "New" in the left sidebar
   - In the "Database name" field, type: `code_canvas`
   - From the "Collation" dropdown, select: `utf8mb4_general_ci`
   - Click "Create" button
   - You should see `code_canvas` appear in the left sidebar

3. **Import the Database Tables**
   - Click on `code_canvas` in the left sidebar (it should highlight in blue)
   - Click the "Import" tab at the top
   - Click "Choose File" button
   - Navigate to `C:\xampp\htdocs\Blog\`
   - Select the file named `create_tables.sql`
   - Click "Open"
   - Scroll to the bottom of the page
   - Click the "Import" button (might also say "Go")
   - Wait for success message: "Import has been successfully finished"
   - Click on `code_canvas` in left sidebar - you should now see multiple tables (user, blogpost, comment, etc.)

### Step 4: Configure the Application

1. **Create Environment File**
   - Open File Explorer
   - Navigate to `C:\xampp\htdocs\Blog\`
   - Find the file named `.env.example`
   - Right-click on it and select "Copy"
   - Right-click in empty space and select "Paste"
   - Rename the copied file from `.env.example - Copy` to `.env`
   - **Important**: The file should be named exactly `.env` (with the dot at the start, no `.txt` extension)

2. **Edit Configuration (Optional)**
   - Right-click on `.env` and open with Notepad
   - The default settings should work, but verify:
   ```env
   DB_HOST=127.0.0.1
   DB_NAME=code_canvas
   DB_USER=root
   DB_PASS=
   ```
   - If you set a MySQL password during XAMPP installation, add it after `DB_PASS=`
   - Save and close

3. **Create Uploads Folder**
   - In `C:\xampp\htdocs\Blog\`
   - Right-click in empty space
   - Select "New" → "Folder"
   - Name it exactly: `uploads`

### Step 5: Access Your Blog

1. **Open the Website**
   - Make sure Apache and MySQL are still running in XAMPP Control Panel
   - Open your web browser
   - Go to: `http://localhost/Blog`
   - You should see the Code & Canvas homepage!

2. **Create Your First Account**
   - Click "Register" in the top right
   - Fill in your details:
     - Username (at least 3 characters)
     - Email address
     - Password (at least 8 characters, one uppercase, one lowercase, one number)
     - Confirm password
   - Click "Create Account"
   - You're now logged in!

3. **Write Your First Post**
   - Click "Write" in the navigation
   - Add a title and content
   - You can use Markdown for formatting
   - Add topics separated by commas
   - Click "Publish"

### ⚠️ Troubleshooting Common Issues

#### "Database connection failed"
**Solution:**
1. Check XAMPP Control Panel - MySQL should be green/running
2. Open `.env` file and verify database name is `code_canvas`
3. If you set a MySQL password, make sure it's in `.env` after `DB_PASS=`
4. Try restarting MySQL in XAMPP Control Panel

#### "Environment file not found"
**Solution:**
1. Make sure you created `.env` file (not `.env.txt`)
2. It should be in `C:\xampp\htdocs\Blog\` folder
3. Windows might hide file extensions - in File Explorer, click View → Show → File name extensions

#### "Page not found" or blank page
**Solution:**
1. Verify Apache is running (green in XAMPP)
2. Check the URL is exactly: `http://localhost/Blog` (capital B)
3. Clear your browser cache (Ctrl + Shift + Delete)
4. Try a different browser

#### Images not uploading
**Solution:**
1. Verify `uploads` folder exists in `C:\xampp\htdocs\Blog\`
2. Right-click uploads folder → Properties → Uncheck "Read-only"
3. Try restarting Apache in XAMPP

#### Port 80 already in use (Apache won't start)
**Solution:**
1. Skype or other apps might use port 80
2. In XAMPP Control Panel, click "Config" next to Apache
3. Select "Apache (httpd.conf)"
4. Find `Listen 80` and change to `Listen 8080`
5. Save and restart Apache
6. Access site at: `http://localhost:8080/Blog`

### 🎯 Quick Reference

**Key Locations:**
- Project folder: `C:\xampp\htdocs\Blog\`
- Database: phpMyAdmin at `http://localhost/phpmyadmin`
- Website: `http://localhost/Blog`

**XAMPP Services:**
- Apache: Web server (must be running)
- MySQL: Database server (must be running)

**Important Files:**
- `.env` - Configuration file
- `create_tables.sql` - Database structure
- `uploads/` - User uploaded images

## 🛠️ Tech Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL 5.7+ / MariaDB 10.4+
- **Frontend**: HTML5, Tailwind CSS, Alpine.js
- **Markdown**: Marked.js
- **Icons**: Heroicons (SVG)

## 📋 Prerequisites

Before you begin, ensure you have the following installed:
- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.4+
- Apache/Nginx web server (XAMPP recommended for Windows)
- Composer (optional, for future dependencies)

## 🚀 Installation

### Option 1: Using XAMPP (Recommended for Windows)

1. **Clone the repository**
   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/yourusername/code-canvas.git Blog
   cd Blog
   ```

2. **Configure Environment Variables**
   ```bash
   # Copy the example environment file
   copy .env.example .env
   ```
   
   Edit `.env` file and update your database credentials:
   ```env
   DB_HOST=127.0.0.1
   DB_NAME=code_canvas
   DB_USER=root
   DB_PASS=your_mysql_password_here
   ```

3. **Start XAMPP**
   - Start Apache and MySQL from XAMPP Control Panel

4. **Create Database**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create a new database named `code_canvas` (or use the name you set in `.env`)
   - Import the SQL schema: `create_tables.sql`

5. **Create Uploads Directory**
   ```bash
   mkdir uploads
   ```

6. **Access the Application**
   Open browser: `http://localhost/Blog`

### Option 2: Using Command Line

```bash
# Clone repository
git clone https://github.com/yourusername/code-canvas.git
cd code-canvas

# Setup environment
cp .env.example .env
# Edit .env with your database credentials

# Setup database
mysql -u root -p
CREATE DATABASE code_canvas;
USE code_canvas;
SOURCE create_tables.sql;
exit;

# Create uploads directory
mkdir uploads
chmod 755 uploads

# Start PHP server (for development)
php -S localhost:8000
```

## 📖 Usage

### Creating Your First Post

1. Register a new account at `/register.php`
2. Navigate to the Dashboard
3. Click "Write New Post"
4. Write your content using Markdown
5. Add topics (comma-separated)
6. Upload a cover image (optional)
7. Publish or save as draft

### Managing Your Profile

1. Go to Dashboard
2. Click on your profile dropdown
3. Select "Account Settings" or "Profile Settings"
4. Update your information and avatar

## 🗂️ Project Structure

```
Blog/
├── api/                    # API endpoints
│   ├── add_comment.php
│   ├── toggle_like.php
│   ├── toggle_follow.php
│   ├── save_draft.php
│   └── ...
├── assets/                 # Static assets
│   ├── css/
│   ├── images/
│   └── scripts.js
├── auth/                   # Authentication handlers
│   ├── login_handler.php
│   ├── register.php
│   └── update_profile.php
├── includes/               # Shared PHP files
│   ├── db.php             # Database connection
│   ├── functions.php      # Helper functions
│   ├── header.php         # Site header
│   └── footer.php         # Site footer
├── uploads/               # User uploads
├── index.php              # Homepage
├── posts.php              # All posts
├── post.php               # Single post view
├── dashboard.php          # User dashboard
├── editor.php             # Post editor
├── public-profile.php     # User profiles
├── create_tables.sql      # Database schema
└── README.md
```

## 🔐 Security Features

- ✅ Password hashing with bcrypt
- ✅ Prepared statements (SQL injection prevention)
- ✅ XSS protection with htmlspecialchars
- ✅ CSRF token validation
- ✅ Session security
- ✅ Input validation and sanitization
- ✅ File upload validation
- ✅ Environment variables for sensitive data

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guidelines](CONTRIBUTING.md) for details.

### Quick Start for Contributors

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 🐛 Bug Reports & Feature Requests

- **Bug Reports**: Please use the [GitHub Issues](https://github.com/yourusername/code-canvas/issues) page
- **Feature Requests**: We'd love to hear your ideas! Open an issue with the "enhancement" label

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Developer

**Dashintha Jayawardana**

- Portfolio: [dashijayawardana.vercel.app](https://dashijayawardana.vercel.app/)
- GitHub: [@Dashintha-Prabashwara](https://github.com/Dashintha-Prabashwara)
- LinkedIn: [Dashintha Jayawardana](https://www.linkedin.com/in/dashintha-jayawardana-7b740b26b/)

## 🙏 Acknowledgments

- Design inspiration from Medium and DEV Community
- Icons by Heroicons
- Typography by Google Fonts (Fraunces & Inter)
- Markdown parsing by Marked.js

## 📊 Database Schema

The application uses a well-structured relational database with the following main tables:

- `user` - User accounts
- `blogpost` - Published posts
- `draft_post` - Draft posts
- `comment` - Post comments
- `post_like` - Post likes
- `follow` - User follows
- `notification` - User notifications

For detailed schema, see [create_tables.sql](create_tables.sql)

## 🔄 Changelog

See [CHANGELOG.md](CHANGELOG.md) for a list of changes and versions.

## 💬 Support

If you need help or have questions:
- Check the [Issues](https://github.com/yourusername/code-canvas/issues) page
- Read the documentation
- Contact the developer

---

Made with ❤️ by [Dashintha Jayawardana](https://dashijayawardana.vercel.app/)


