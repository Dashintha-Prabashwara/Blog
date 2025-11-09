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

1. **Install Required Software**
   - Download and install [XAMPP](https://www.apachefriends.org/download.html)
   - Download and install [Git](https://git-scm.com/downloads)
   - Download [Visual Studio Code](https://code.visualstudio.com/) (recommended editor)

2. **Start XAMPP**
   - Open XAMPP Control Panel
   - Click "Start" next to Apache
   - Click "Start" next to MySQL

### Step 2: Getting the Code

1. **Open Command Prompt (CMD)**
   - Press `Win + R`
   - Type `cmd` and press Enter

2. **Navigate to XAMPP's htdocs folder**
   ```bash
   cd C:\xampp\htdocs
   ```

3. **Clone the Repository**
   ```bash
   git clone https://github.com/yourusername/code-canvas.git Blog
   cd Blog
   ```

### Step 3: Database Setup

1. **Open phpMyAdmin**
   - Open your browser
   - Go to: http://localhost/phpmyadmin
   - Login (default username: 'root', no password)

2. **Create Database**
   - Click "New" on the left sidebar
   - Enter database name: `code_canvas`
   - Click "Create"

3. **Import Database Structure**
   - Select your new `code_canvas` database
   - Click "Import" at the top
   - Click "Choose File"
   - Select `create_tables.sql` from your Blog folder
   - Scroll down and click "Import"

### Step 4: Configuration

1. **Create Environment File**
   - Go to your Blog folder in File Explorer: `C:\xampp\htdocs\Blog`
   - Copy `.env.example` and rename the copy to `.env`
   - Open `.env` in a text editor
   - Update if needed (default settings should work for XAMPP)

2. **Create Uploads Directory**
   - In your Blog folder, create a new folder named `uploads`
   - Make sure it's writable (right-click → Properties → uncheck "Read-only")

### Step 5: Testing Your Setup

1. **Access the Website**
   - Open your browser
   - Go to: http://localhost/Blog
   - You should see the homepage

2. **Create an Account**
   - Click "Register" in the navigation
   - Fill in your details
   - Log in with your new account

### Step 6: Making Your First Change

1. **Fork the Repository** (one-time setup)
   - Go to the project's GitHub page
   - Click "Fork" in the top-right corner
   - This creates your own copy of the project

2. **Create a Branch**
   ```bash
   # Make sure you're in the Blog directory
   cd C:\xampp\htdocs\Blog

   # Create and switch to a new branch
   git checkout -b feature/your-feature-name
   ```

3. **Make Changes**
   - Open the project in Visual Studio Code
   - Make your changes
   - Test them locally at http://localhost/Blog

4. **Commit Your Changes**
   ```bash
   # See what files you changed
   git status

   # Add your changes
   git add .

   # Commit with a descriptive message
   git commit -m "Add: description of your changes"
   ```

5. **Push to GitHub**
   ```bash
   # Push your branch
   git push origin feature/your-feature-name
   ```

6. **Create Pull Request**
   - Go to the original repository on GitHub
   - Click "Pull requests"
   - Click "New pull request"
   - Choose your branch
   - Fill in the description
   - Submit the pull request

### Common Issues & Solutions

1. **"Database connection failed"**
   - Make sure XAMPP's MySQL is running
   - Check your `.env` file database credentials
   - Verify the database exists

2. **"Permission denied" for uploads**
   - Make sure the `uploads` folder exists
   - Check folder permissions
   - Try creating the folder manually

3. **"Page not found" errors**
   - Make sure Apache is running
   - Verify you're using the correct URL
   - Check file permissions

4. **Changes not showing up**
   - Clear your browser cache
   - Check if you're editing the right files
   - Verify Apache is serving from the correct directory

### Getting Help

If you're stuck:
1. Check the [Issues](https://github.com/yourusername/code-canvas/issues) page
2. Search existing discussions
3. Create a new issue with:
   - What you were trying to do
   - What happened instead
   - Your environment details
   - Any error messages

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


