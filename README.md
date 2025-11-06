# ETHCO CODERS WEBAPP

A comprehensive web application for Ethco Coders - a collective of Ethiopian software engineers. This application includes a landing page, user authentication, dashboard, project management, task assignment, chat system, and contact management.

## 🚀 Features

### Phase 1: Setup & Architecture ✅
- ✅ Complete folder structure (InfinityFree-compatible)
- ✅ Database configuration with secure credentials
- ✅ Reusable helper functions
- ✅ Complete database schema (users, messages, projects, tasks, contacts, notifications)
- ✅ `.gitignore` for sensitive files

### Phase 2: Landing Page Integration ✅
- ✅ Beautiful landing page with Ethiopian theme
- ✅ AJAX contact form submission
- ✅ Contact form data storage in database
- ✅ Professional footer with "Powered by Ethco Coders"
- ✅ ContactController for backend processing

### Phase 3: Authentication System ✅
- ✅ User registration with validation
- ✅ Secure login with session management
- ✅ Password hashing (bcrypt)
- ✅ User roles: Admin, Team Member, Regular User
- ✅ Forgot password functionality with token-based reset
- ✅ Session-based authentication

### Phase 4: Dashboard (Core UI) ✅
- ✅ Modern, responsive dashboard layout
- ✅ Reusable partials (header, sidebar, footer)
- ✅ User profile management
- ✅ Dashboard statistics and overview
- ✅ Light/dark theme support
- ✅ Client-side form validation

### Phase 5: Chat System ✅
- ✅ Real-time chat with AJAX polling
- ✅ Admin ↔ Admin communication
- ✅ User ↔ Admin support chat
- ✅ User ↔ User direct messaging
- ✅ Message read status
- ✅ ChatController for backend logic

### Phase 6: Project Submission System ✅
- ✅ Project submission form with file upload
- ✅ Secure file upload handling
- ✅ Project status tracking (Pending, Approved, Rejected)
- ✅ Admin review and approval system
- ✅ XSS protection and data sanitization
- ✅ Upload progress feedback

### Phase 7: Task Amplifier (Admin → Team) ✅
- ✅ Task creation and assignment
- ✅ Task status updates (To Do, In Progress, Done, Blocked)
- ✅ Task priority levels (High, Medium, Low)
- ✅ Due date management
- ✅ Dashboard notifications for task assignments
- ✅ Task filtering by status

### Phase 8: Contact Message Management ✅
- ✅ Admin interface for contact messages
- ✅ Message status tracking (New, Read, Replied, Archived)
- ✅ Search and filter functionality
- ✅ Admin notes on messages
- ✅ Contact statistics dashboard

### Phase 9: Notifications & UX Enhancements ✅
- ✅ In-dashboard notification dropdown
- ✅ Visual indicators for new messages, projects, tasks
- ✅ Toast notifications for user actions
- ✅ Smooth UI transitions
- ✅ Loader animations

## 📁 Project Structure

```
ethco/
├── app/
│   ├── api/
│   │   ├── chat.php
│   │   ├── contacts.php
│   │   └── notifications.php
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── ChatController.php
│   │   ├── ContactController.php
│   │   ├── ProjectController.php
│   │   └── TaskController.php
│   ├── config.php
│   └── functions.php
├── dashboard/
│   ├── assets/
│   │   ├── css/
│   │   │   ├── dashboard.css
│   │   │   └── chat.css
│   │   └── js/
│   │       ├── dashboard.js
│   │       └── chat.js
│   ├── partials/
│   │   ├── header.php
│   │   ├── sidebar.php
│   │   └── footer.php
│   ├── contacts.php
│   ├── chat.php
│   ├── index.php
│   ├── profile.php
│   ├── projects.php
│   └── tasks.php
├── database/
│   └── ethco_schema.sql
├── logs/
│   ├── .htaccess
│   └── index.php
├── uploads/
│   ├── projects/
│   ├── .htaccess
│   └── index.php
├── contact.php
├── forgot_password.php
├── index.html
├── login.php
├── logout.php
├── register.php
├── reset_password.php
├── .gitignore
├── README.md
└── TODO.md
```

## 🛠️ Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- PHP extensions: PDO, PDO_MySQL, mbstring

### Step 1: Database Setup
1. Create a MySQL database:
   ```sql
   CREATE DATABASE ethco_db;
   ```

2. Import the schema:
   ```bash
   mysql -u root -p ethco_db < database/ethco_schema.sql
   ```

3. Update database credentials in `app/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'ethco_db');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

### Step 2: File Permissions
```bash
chmod 755 uploads/
chmod 755 uploads/projects/
chmod 755 logs/
```

### Step 3: Configuration
1. Update `APP_URL` in `app/config.php` to match your domain
2. For production, set `display_errors` to `0` in `app/config.php`
3. Update session security settings if using HTTPS

### Step 4: Default Admin Account
The schema includes a default admin account:
- **Username:** admin
- **Email:** admin@ethcocoders.com
- **Password:** Admin123! (CHANGE THIS IN PRODUCTION!)

**IMPORTANT:** Change the default password immediately after first login!

## 📖 Usage Guide

### For Users
1. **Register:** Visit `register.php` to create an account
2. **Login:** Use `login.php` to access the dashboard
3. **Submit Projects:** Navigate to Projects → Submit New Project
4. **Chat:** Use the Chat section to communicate with team members
5. **View Tasks:** Check Tasks section for assigned tasks

### For Admins
1. **Review Projects:** Go to Projects → Review pending submissions
2. **Manage Tasks:** Create and assign tasks to team members
3. **Contact Messages:** View and respond to contact form submissions
4. **User Management:** Monitor user activity and manage accounts

### For Team Members
1. **View Tasks:** Check assigned tasks in the Tasks section
2. **Update Task Status:** Change task status as you work
3. **Chat:** Communicate with admins and other team members
4. **Submit Projects:** Submit your work through Projects section

## 🔒 Security Features

- ✅ Password hashing with `password_hash()` (bcrypt)
- ✅ Prepared statements (SQL injection prevention)
- ✅ Input sanitization (XSS prevention)
- ✅ CSRF token support (ready for implementation)
- ✅ Session security with httponly cookies
- ✅ File upload validation
- ✅ Secure file storage (direct access blocked)
- ✅ Role-based access control

## 🎨 Design Features

- Modern, responsive design
- Ethiopian color scheme (green, yellow, red)
- Dark theme optimized
- Smooth animations and transitions
- Mobile-friendly interface
- Accessible UI components

## 📝 API Endpoints

### Chat API (`app/api/chat.php`)
- `GET ?action=conversation&user_id=X` - Get conversation
- `POST action=send` - Send message
- `GET ?action=users` - Get chat users
- `GET ?action=unread_count` - Get unread count

### Notifications API (`app/api/notifications.php`)
- `GET ?action=list` - Get notifications
- `POST action=mark_read` - Mark notification as read

### Contacts API (`app/api/contacts.php`)
- `GET ?action=unread_count` - Get unread contact count (Admin only)

## 🐛 Troubleshooting

### Database Connection Issues
- Verify database credentials in `app/config.php`
- Ensure MySQL service is running
- Check database user permissions

### File Upload Issues
- Verify `uploads/` directory permissions (755)
- Check `MAX_FILE_SIZE` in `app/config.php`
- Ensure PHP `upload_max_filesize` is sufficient

### Session Issues
- Check PHP session configuration
- Verify session directory is writable
- Clear browser cookies if sessions persist incorrectly

## 📚 Development Notes

### Code Standards
- PSR-12 coding standards for PHP
- HTML5 semantic markup
- Responsive design principles
- Security-first approach

### Future Enhancements
- Email notifications (SMTP integration)
- Real-time notifications (WebSockets)
- Advanced search functionality
- File preview functionality
- Export/import features
- Analytics dashboard

## 🤝 Contributing

This is a private project for Ethco Coders. For issues or suggestions, contact the development team.

## 📄 License

Copyright © 2025 Ethco Coders. All rights reserved.

## 👥 Team

- **Natanel Ermias** - Full-Stack Developer & AI Specialist
- **Tadios Aschalew** - Mobile & Cloud Engineer
- **Yonas Asamere** - Backend Architect & Security Expert
- **Afomia Asheger** - UI/UX Designer & Frontend Developer

## 🌟 Acknowledgments

Built with ❤️ by Ethiopian software engineers for the Ethiopian tech community.

---

**Powered by Ethco Coders** | Est. 2025 — Coding the Future, Together.

