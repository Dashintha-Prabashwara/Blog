# Contributing to Code & Canvas

First off, thank you for considering contributing to Code & Canvas! It's people like you that make this platform better for everyone.

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
- [Development Setup](#development-setup)
- [Coding Standards](#coding-standards)
- [Commit Guidelines](#commit-guidelines)
- [Pull Request Process](#pull-request-process)

## 🤝 Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code. Please report unacceptable behavior to the project maintainers.

### Our Standards

- Use welcoming and inclusive language
- Be respectful of differing viewpoints
- Gracefully accept constructive criticism
- Focus on what is best for the community
- Show empathy towards other community members

## 🎯 How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check existing issues. When creating a bug report, include:

- **Clear title and description**
- **Steps to reproduce** the issue
- **Expected behavior** vs actual behavior
- **Screenshots** if applicable
- **Environment details** (OS, PHP version, browser)

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion:

- Use a clear and descriptive title
- Provide a detailed description of the proposed feature
- Explain why this enhancement would be useful
- Include mockups or examples if possible

### Your First Code Contribution

Unsure where to begin? Look for issues labeled:
- `good first issue` - Good for newcomers
- `help wanted` - Extra attention needed
- `bug` - Something isn't working
- `enhancement` - New feature or request

## 🛠️ Development Setup

1. **Fork and Clone**
   ```bash
   git clone https://github.com/YOUR-USERNAME/code-canvas.git
   cd code-canvas
   ```

2. **Set Up Development Environment**
   ```bash
   # Copy environment template
   cp .env.example .env
   
   # Edit .env and set your database credentials
   # Use a separate database for development
   ```
   
   Example `.env` for development:
   ```env
   DB_HOST=127.0.0.1
   DB_NAME=code_canvas_dev
   DB_USER=root
   DB_PASS=your_password
   APP_ENV=development
   ```

3. **Import Database**
   ```sql
   CREATE DATABASE code_canvas_dev;
   USE code_canvas_dev;
   SOURCE create_tables.sql;
   ```

4. **Create Uploads Directory**
   ```bash
   mkdir uploads
   ```

5. **Start Development Server**
   ```bash
   php -S localhost:8000
   ```

## 📝 Coding Standards

### PHP

- Follow PSR-12 coding standards
- Use meaningful variable and function names
- Add comments for complex logic
- Use prepared statements for all database queries
- Validate and sanitize all user inputs

**Example:**
```php
<?php
// Good
function getUserPosts($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM blogPost WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

// Bad
function gup($id) {
    $q = "SELECT * FROM blogPost WHERE user_id = $id";
    return mysql_query($q);
}
```

### HTML/CSS

- Use semantic HTML5 elements
- Follow Tailwind CSS utility-first approach
- Keep components modular and reusable
- Ensure responsive design (mobile-first)

### JavaScript

- Use modern ES6+ syntax
- Prefer `const` and `let` over `var`
- Use async/await for asynchronous operations
- Add error handling for API calls

**Example:**
```javascript
// Good
async function toggleLike(postId) {
    try {
        const response = await fetch('/api/toggle_like.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ post_id: postId })
        });
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error:', error);
    }
}
```

### Database

- Use meaningful table and column names
- Add appropriate indexes for performance
- Use foreign keys for referential integrity
- Document schema changes

## 💬 Commit Guidelines

### Commit Message Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

**Types:**
- `feat` - New feature
- `fix` - Bug fix
- `docs` - Documentation changes
- `style` - Code style changes (formatting)
- `refactor` - Code refactoring
- `test` - Adding tests
- `chore` - Maintenance tasks

**Examples:**
```bash
feat(editor): add auto-save functionality

- Implement auto-save every 30 seconds
- Show save status indicator
- Recover drafts on page reload

Closes #123
```

```bash
fix(auth): resolve session timeout issue

Fix issue where users were logged out prematurely
due to incorrect session configuration.

Fixes #456
```

## 🔄 Pull Request Process

1. **Update Documentation**
   - Update README.md if needed
   - Add comments to complex code
   - Update CHANGELOG.md

2. **Test Your Changes**
   - Test on different browsers
   - Test responsive design
   - Check for console errors
   - Verify database migrations

3. **Create Pull Request**
   - Use a clear title and description
   - Reference related issues
   - Include screenshots for UI changes
   - Ensure all tests pass

4. **Code Review**
   - Address review comments
   - Update your PR as needed
   - Be patient and respectful

### PR Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
Describe how you tested your changes

## Screenshots (if applicable)
Add screenshots here

## Checklist
- [ ] Code follows project style guidelines
- [ ] Self-review completed
- [ ] Comments added for complex code
- [ ] Documentation updated
- [ ] No new warnings generated
- [ ] Tests added/updated
```

## 🧪 Testing

Before submitting:

1. **Manual Testing**
   - Test all modified features
   - Check for edge cases
   - Verify error handling

2. **Browser Testing**
   - Chrome/Edge
   - Firefox
   - Safari (if possible)

3. **Responsive Testing**
   - Mobile devices
   - Tablets
   - Desktop

## 📚 Resources

- [PHP Documentation](https://www.php.net/docs.php)
- [Tailwind CSS Docs](https://tailwindcss.com/docs)
- [Alpine.js Documentation](https://alpinejs.dev/)
- [Marked.js Documentation](https://marked.js.org/)

## ❓ Questions?

Feel free to:
- Open an issue
- Contact the maintainer
- Join discussions

Thank you for contributing! 🎉
