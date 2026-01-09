# Database Schema Design for EMS (High-Level)

## 1. Landlord Database (Central System)
This database manages the SaaS aspect: Institutes (Tenants), Subscriptions, and Super Admins.

### Tables
*   **tenants**
    *   `id` (PK): UUID
    *   `name`: Institute Name
    *   `domain`: Subdomain (e.g., royal.ems.com) or Custom Domain
    *   `database`: Database name for this tenant
    *   `status`: Active/Suspended
    *   `plan_id`: FK
*   **domains** (stancl/tenancy requirement)
    *   `domain`: e.g., "institute.com"
    *   `tenant_id`: FK
*   **plans**
    *   `id` (PK)
    *   `name`: "Starter", "Pro", "Enterprise"
    *   `features`: JSON (max_students, storage_limit, modules_enabled)
    *   `price`: Decimal
*   **central_payments**
    *   Record of institutes paying the platform owner.

---

## 2. Tenant Database (Per Institute)
This is where the actual EMS data lives. Every institute has its own separate database (Privacy & Security).

### A. User Management (RBAC)
*   **users**
    *   `id` (PK), `name`, `email`, `password`, `phone`
    *   `role`: 'admin', 'teacher', 'student', 'parent'
    *   `avatar_url`: User profile image
    *   `is_active`: Boolean
    *   `last_login_at`: Timestamp
*   **student_profiles**
    *   `user_id`: FK
    *   `barcode_id`: Unique printable ID
    *   `guardian_id`: FK (parent)
    *   `batch_id`: FK (Current batch)
    *   `wallet_balance`: Decimal (for canteen/store/fees)
    *   `points`: Integer (Gamification)
*   **teacher_profiles**
    *   `user_id`: FK
    *   `qualifications`: Text
    *   `bank_details`: JSON (for salary)
*   **parent_profiles**
    *   `user_id`: FK
    *   `students`: JSON (Quick look up)

### B. Academic Structure
*   **academic_years**: e.g., "2026", "2027"
*   **batches**: e.g., "2026 A/L Theory", "2027 O/L Revision"
*   **subjects**: e.g., "Physics", "Combined Maths"
*   **courses** (The core unit)
    *   `id` (PK)
    *   `name`: "Physics 2026 Theory - Group A"
    *   `teacher_id`: FK
    *   `batch_id`: FK
    *   `subject_id`: FK
    *   `schedule`: JSON (Days & Times)
    *   `fee_amount`: Decimal

### C. Learning Management (LMS)
*   **course_modules**
    *   `course_id`: FK
    *   `title`: e.g., "Unit 1: Mechanics"
    *   `order`: Integer
*   **contents**
    *   `module_id`: FK
    *   `type`: 'video_vod', 'live_link', 'pdf', 'quiz'
    *   `title`: String
    *   `resource_url`: Path to file/video
    *   `settings`: JSON (e.g., DRM keys, watermarking configs, download_allowed)
    *   `release_at`: Timed release date

### D. Attendance & Physical Class
*   **class_sessions**
    *   `course_id`: FK
    *   `date`: Date
    *   `start_time`: Time
    *   `qr_code_token`: Dynamic token for the screen
*   **attendances**
    *   `session_id`: FK
    *   `student_id`: FK
    *   `status`: 'present', 'late', 'absent'
    *   `method`: 'qr', 'nfc', 'manual', 'face_id'
    *   `in_time`: Timestamp

### E. Finance & Payments
*   **fee_invoices**
    *   `student_id`: FK
    *   `course_id`: FK
    *   `month`: "January 2026"
    *   `amount`: Decimal
    *   `due_date`: Date
    *   `status`: 'paid', 'pending', 'overdue'
*   **transactions**
    *   `invoice_id`: FK
    *   `method`: 'online_gateway', 'bank_transfer', 'cash', 'e_wallet'
    *   `slip_image_url`: For manual verification
    *   `ocr_data`: JSON (Extracted text from slip)
    *   `verified_by`: FK (Admin user)

### F. Exams & Gamification
*   **exams**
    *   `course_id`: FK
    *   `type`: 'mcq_online', 'paper_based'
*   **mcq_questions**
    *   `exam_id`: FK
    *   `question_text`: Rich Text
    *   `options`: JSON
    *   `correct_option`: Index
*   **student_marks**
    *   `student_id`: FK
    *   `exam_id`: FK
    *   `marks`: Decimal
    *   `rank`: Integer (Calculated)
*   **leaderboards**
    *   Cached table for fast syncing of ranks.

## 3. Technology Alignment
*   **Multi-tenancy**: `stancl/tenancy` will automate the creation of Tenant DBs.
*   **High Performance**: Frequently accessed data like "User Points" or "Leaderboard" will be cached in Redis.
*   **Security**: Video URLs in `contents` table will be encrypted tokens, not direct paths.
