# 🚀 Quick Setup Checklist

Use this checklist to ensure everything is set up correctly.

## ✅ Pre-Installation

- [ ] XAMPP installed at `C:\xampp\`
- [ ] Apache started (green in XAMPP Control Panel)
- [ ] MySQL started (green in XAMPP Control Panel)

## ✅ Code Setup

- [ ] Project folder is at `C:\xampp\htdocs\Blog\`
- [ ] File `.env` exists (copied from `.env.example`)
- [ ] Folder `uploads` exists in project directory

## ✅ Database Setup

- [ ] Opened phpMyAdmin (`http://localhost/phpmyadmin`)
- [ ] Created database named `code_canvas`
- [ ] Imported `create_tables.sql` successfully
- [ ] Can see tables in left sidebar (user, blogpost, comment, etc.)

## ✅ Testing

- [ ] Can access `http://localhost/Blog`
- [ ] Homepage loads without errors
- [ ] Can click "Register" and see registration form
- [ ] Can create a new account
- [ ] Can log in successfully
- [ ] Can access dashboard after login

## 🎉 Success Indicators

If all checkboxes are checked, your installation is complete!

**You should be able to:**
- View the homepage
- Register a new account
- Log in and see the dashboard
- Click "Write" to create a post
- Upload images for posts and profile

## ❌ If Something Doesn't Work

1. Check [README.md](README.md) Troubleshooting section
2. Verify all checkboxes above are completed
3. Restart Apache and MySQL in XAMPP
4. Clear your browser cache
5. Check for error messages in browser console (F12)

## 📝 Common File Locations

```
C:\xampp\htdocs\Blog\          ← Your project
C:\xampp\htdocs\Blog\.env      ← Configuration
C:\xampp\htdocs\Blog\uploads\  ← User uploads
```

## 🔗 Important URLs

- **Your Blog**: http://localhost/Blog
- **phpMyAdmin**: http://localhost/phpmyadmin
- **XAMPP Control**: Search "XAMPP Control Panel" in Start menu
