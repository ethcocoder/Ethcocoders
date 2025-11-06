## 🧭 ETHCO CODERS WEBAPP – Project Development Roadmap
### _Strategic Overview & Actionable Tasks_

---

### 🏗️ **Phase 1: Setup & Architecture**
- [x] Define project goals & core module functionalities. (Priority: High, Assignee: Fullstack)
- [x] Establish InfinityFree-compatible folder structure. (Priority: High, Assignee: Fullstack)
- [ ] Initialize Git repository and configure `.gitignore` for sensitive files and dependencies. (Priority: High, Assignee: Fullstack)
- [ ] Create <mcfile name="config.php" path="c:\Users\Student\Downloads\ethco\app\config.php"></mcfile> for secure database credentials and environment variables. (Priority: High, Assignee: Backend)
- [ ] Develop reusable helper functions in <mcfile name="functions.php" path="c:\Users\Student\Downloads\ethco\app\functions.php"></mcfile> for common operations. (Priority: Medium, Assignee: Backend)
- [ ] Prepare <mcfile name="ethco_schema.sql" path="c:\Users\Student\Downloads\ethco\database\ethco_schema.sql"></mcfile> with initial database schema, including tables for users, messages, projects, tasks, and contacts. (Priority: High, Assignee: Backend)

---

### 🎨 **Phase 2: Landing Page Integration**
- [x] Retain existing <mcfile name="index.html" path="c:\Users\Student\Downloads\ethco\index.html"></mcfile> as the public-facing landing page. (Priority: High, Assignee: Frontend)
- [ ] Implement contact form submission logic to <mcfile name="contact.php" path="c:\Users\Student\Downloads\ethco\contact.php"></mcfile> using AJAX for a seamless user experience. (Priority: High, Assignee: Frontend)
- [ ] Integrate contact form data with the database via <mcsymbol name="ContactController" filename="ContactController.php" path="c:\Users\Student\Downloads\ethco\app\controllers\ContactController.php" startline="1" type="class"></mcsymbol> to store submissions and enable admin review. (Priority: High, Assignee: Backend)
- [ ] Add a professional footer to <mcfile name="index.html" path="c:\Users\Student\Downloads\ethco\index.html"></mcfile> including "Powered by Ethco Coders" and relevant links. (Priority: Medium, Assignee: Frontend)
- [ ] Implement optional auto-email notification upon contact form submission for user confirmation. (Priority: Low, Assignee: Backend)

---

### 🔐 **Phase 3: Authentication System**
- [ ] Develop user registration form and logic in <mcfile name="register.php" path="c:\Users\Student\Downloads\ethco\register.php"></mcfile>, utilizing <mcsymbol name="AuthController" filename="AuthController.php" path="c:\Users\Student\Downloads\ethco\app\controllers\AuthController.php" startline="1" type="class"></mcsymbol> for backend processing. (Priority: High, Assignee: Fullstack)
- [ ] Create user login interface and authentication mechanism in <mcfile name="login.php" path="c:\Users\Student\Downloads\ethco\login.php"></mcfile>, managed by <mcsymbol name="AuthController" filename="AuthController.php" path="c:\Users\Student\Downloads\ethco\app\controllers\AuthController.php" startline="1" type="class"></mcsymbol>. (Priority: High, Assignee: Fullstack)
- [ ] Implement robust session-based login management for secure user sessions. (Priority: High, Assignee: Backend)
- [ ] Define and implement user roles: Admin, Team Member, and Regular User, with appropriate access controls. (Priority: High, Assignee: Backend)
- [ ] Integrate strong password hashing (e.g., `password_hash`) and validation rules for enhanced security. (Priority: High, Assignee: Backend)
- [ ] **Enhancement**: Implement "Forgot Password" functionality with secure token-based reset. (Priority: Medium, Assignee: Fullstack)

---

### 🧑‍💻 **Phase 4: Dashboard (Core UI)**
- [ ] Develop the main dashboard layout in <mcfile name="index.php" path="c:\Users\Student\Downloads\ethco\dashboard\index.php"></mcfile> within the `/dashboard/` directory. (Priority: High, Assignee: Frontend)
- [ ] Integrate reusable partials for consistent UI:
    - <mcfile name="header.php" path="c:\Users\Student\Downloads\ethco\dashboard\partials\header.php"></mcfile> (navigation, branding)
    - <mcfile name="sidebar.php" path="c:\Users\Student\Downloads\ethco\dashboard\partials\sidebar.php"></mcfile> (main menu, user info)
    - <mcfile name="footer.php" path="c:\Users\Student\Downloads\ethco\dashboard\partials\footer.php"></mcfile> (copyright, quick links)
    (Priority: High, Assignee: Frontend)
- [ ] Implement user profile area in <mcfile name="profile.php" path="c:\Users\Student\Downloads\ethco\dashboard\profile.php"></mcfile> allowing users to view and update their information. (Priority: Medium, Assignee: Fullstack)
- [ ] Apply responsive dashboard styling using <mcfile name="dashboard.css" path="c:\Users\Student\Downloads\ethco\dashboard\assets\css\dashboard.css"></mcfile>, including support for light/dark themes. (Priority: High, Assignee: Frontend)
- [ ] **Enhancement**: Implement client-side form validation for profile updates in <mcfile name="dashboard.js" path="c:\Users\Student\Downloads\ethco\dashboard\assets\js\dashboard.js"></mcfile>. (Priority: Medium, Assignee: Frontend)

---

### 💬 **Phase 5: Chat System**
- [ ] Design and create chat-related tables in <mcfile name="ethco_schema.sql" path="c:\Users\Student\Downloads\ethco\database\ethco_schema.sql"></mcfile> (e.g., `messages`, `chat_participants`). (Priority: High, Assignee: Backend)
- [ ] Develop <mcsymbol name="ChatController" filename="ChatController.php" path="c:\Users\Student\Downloads\ethco\app\controllers\ChatController.php" startline="1" type="class"></mcsymbol> to handle chat logic, including sending, receiving, and retrieving messages. (Priority: High, Assignee: Backend)
- [ ] Implement real-time chat updates using AJAX polling or WebSockets (if feasible with InfinityFree) in <mcfile name="chat.js" path="c:\Users\Student\Downloads\ethco\dashboard\assets\js\chat.js"></mcfile>. (Priority: High, Assignee: Fullstack)
- [ ] Enable distinct chat functionalities:
    - Admin ↔ Admin communication
    - User ↔ Admin support chat
    - User ↔ User direct messaging
    (Priority: High, Assignee: Fullstack)
- [ ] Design and implement the chat user interface within <mcfile name="chat.php" path="c:\Users\Student\Downloads\ethco\dashboard\chat.php"></mcfile>, styled with <mcfile name="chat.css" path="c:\Users\Student\Downloads\ethco\dashboard\assets\css\chat.css"></mcfile>. (Priority: High, Assignee: Frontend)
- [ ] **Enhancement**: Implement message read receipts and typing indicators. (Priority: Medium, Assignee: Fullstack)

---

### 📦 **Phase 6: Project Submission System**
- [ ] Create a project submission form for users in <mcfile name="projects.php" path="c:\Users\Student\Downloads\ethco\dashboard\projects.php"></mcfile>. (Priority: High, Assignee: Frontend)
- [ ] Implement secure file upload functionality to the <mcfolder name="uploads" path="c:\Users\Student\Downloads\ethco\uploads"></mcfolder> directory, handling various file types and size limits. (Priority: High, Assignee: Backend)
- [ ] Store project metadata (title, description, file path, submitter ID, submission date) in the database via <mcsymbol name="ProjectController" filename="ProjectController.php" path="c:\Users\Student\Downloads\ethco\app\controllers\ProjectController.php" startline="1" type="class"></mcsymbol>. (Priority: High, Assignee: Backend)
- [ ] Develop an admin review and approval system for submitted projects. (Priority: Medium, Assignee: Fullstack)
- [ ] Implement project status tracking: Pending, Approved, Rejected. (Priority: Medium, Assignee: Backend)
- [ ] **Security**: Sanitize all user-submitted project data to prevent XSS attacks. (Priority: High, Assignee: Backend)
- [ ] **UX**: Provide clear feedback to users on upload progress and success/failure. (Priority: Medium, Assignee: Frontend)

---

### ⚙️ **Phase 7: Task Amplifier (Admin → Team)**
- [ ] Develop <mcsymbol name="TaskController" filename="TaskController.php" path="c:\Users\Student\Downloads\ethco\app\controllers\TaskController.php" startline="1" type="class"></mcsymbol> for managing task creation, assignment, and updates. (Priority: High, Assignee: Backend)
- [ ] Implement functionality for Admin users to assign tasks to specific team members. (Priority: High, Assignee: Fullstack)
- [ ] Enable team members to update task status (e.g., "To Do", "In Progress", "Done", "Blocked"). (Priority: High, Assignee: Fullstack)
- [ ] Add task priority levels (High, Medium, Low) and due dates. (Priority: Medium, Assignee: Backend)
- [ ] Integrate dashboard notifications for new task assignments and status changes. (Priority: Medium, Assignee: Fullstack)
- [ ] **Enhancement**: Implement task filtering and sorting options for team members. (Priority: Low, Assignee: Frontend)

---

### 📩 **Phase 8: Contact Message Management**
- [ ] Utilize <mcsymbol name="ContactController" filename="ContactController.php" path="c:\Users\Student\Downloads\ethco\app\controllers\ContactController.php" startline="1" type="class"></mcsymbol> to efficiently fetch and store messages from the landing page contact form. (Priority: High, Assignee: Backend)
- [ ] Develop an interface within the admin dashboard to display all contact submissions. (Priority: High, Assignee: Frontend)
- [ ] Implement features to mark messages as read/responded by admin users. (Priority: Medium, Assignee: Fullstack)
- [ ] **Optional**: Integrate a reply-to-email function directly from the admin dashboard. (Priority: Low, Assignee: Backend)
- [ ] **UX**: Provide a search and filter mechanism for contact messages. (Priority: Medium, Assignee: Frontend)

---

### 🔔 **Phase 9: Notifications & UX Enhancements**
- [ ] Implement an in-dashboard notification dropdown for system alerts and user-specific updates. (Priority: High, Assignee: Frontend)
- [ ] Display visual indicators for new messages, project submissions, and task assignments. (Priority: Medium, Assignee: Frontend)
- [ ] Integrate toast alerts or similar non-intrusive notifications for user actions (e.g., "Profile updated successfully"). (Priority: Medium, Assignee: Frontend)
- [ ] Ensure smooth UI transitions and implement loader animations for asynchronous operations to improve user perception. (Priority: Medium, Assignee: Frontend)
- [ ] **Optimization**: Optimize notification fetching to minimize database load. (Priority: Medium, Assignee: Backend)

---

### 🧰 **Phase 10: Database & Optimization**
- [ ] Finalize and review <mcfile name="ethco_schema.sql" path="c:\Users\Student\Downloads\ethco\database\ethco_schema.sql"></mcfile> for optimal performance and scalability. (Priority: High, Assignee: Backend)
- [ ] Optimize all database queries to adhere to InfinityFree hosting limits and best practices. (Priority: High, Assignee: Backend)
- [ ] Implement comprehensive data validation and security filters (e.g., prepared statements for SQLi prevention, input sanitization for XSS). (Priority: High, Assignee: Backend)
- [ ] Develop a robust backup and migration script for database management and disaster recovery. (Priority: Medium, Assignee: Backend)
- [ ] **Security**: Regularly audit database access logs for suspicious activities. (Priority: Low, Assignee: Admin)

---

### 🚀 **Phase 11: Testing & Deployment**
- [ ] Conduct full local testing (e.g., XAMPP / Laragon) to ensure all functionalities work as expected. (Priority: High, Assignee: Fullstack)
- [ ] Perform InfinityFree deployment testing to identify and resolve hosting-specific issues. (Priority: High, Assignee: Fullstack)
- [ ] Address and fix file upload permissions and session path configurations on the production environment. (Priority: High, Assignee: Admin)
- [ ] Conduct a final security audit, including penetration testing and vulnerability scanning. (Priority: High, Assignee: Admin)
- [ ] Go live 🎉: Deploy the application to the production server. (Priority: High, Assignee: Admin)

---

### 🧑‍🤝‍🧑 **Team Workflow Recommendations**
- Utilize **GitHub / GitLab** for version control, code collaboration, and pull request workflows. (Priority: High, Assignee: Admin)
- Implement a consistent system for tracking progress with **issue labels**: `frontend`, `backend`, `bug`, `enhancement`, `security`, `devops`. (Priority: High, Assignee: Admin)
- Conduct weekly sync meetings to review completed modules, discuss blockers, and plan upcoming sprints. (Priority: High, Assignee: Admin)
- Adhere to established coding standards (e.g., PSR-12 for PHP, HTML5 semantics, responsive UI principles) for code quality and maintainability. (Priority: High, Assignee: Fullstack)
- **Tooling**: Integrate a code linter and formatter into the development workflow. (Priority: Medium, Assignee: Fullstack)
