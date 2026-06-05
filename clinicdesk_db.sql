-- ============================================================
-- Project  : ClinicDesk - Clinic Management Dashboard
-- الاسم    : عبد الرحمن معين حميد
-- Name     : Abd alrahman Moen Hemaid | ID: 120240672
-- الجامعة  : الجامعة الإسلامية - غزة
-- Course   : SDEV 2106 / WDMM 2010 / MOBC 2102
-- Semester : Semester 2, 2025-2026
-- Instructor: Eng. Mohammed Zuqlam
-- ============================================================

-- =============================================================
-- ملف قاعدة البيانات لمشروع ClinicDesk
-- بنشغله في phpMyAdmin أو MySQL Workbench
-- لازم ننفذ الجداول بالترتيب لأن في foreign keys
-- =============================================================

-- إنشاء قاعدة البيانات أولاً
CREATE DATABASE IF NOT EXISTS clinicdesk_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- استخدام قاعدة البيانات
USE clinicdesk_db;

-- =============================================================
-- جدول 1: users - جدول المستخدمين الرئيسي
-- كل واحد بيسجل هون سواء admin أو doctor أو patient
-- =============================================================
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,             -- UNIQUE يعني ما فيش إيميلين متشابهين
    password VARCHAR(255) NOT NULL,                  -- هون بنحفظ الهاش مش الباسورد الحقيقي
    role ENUM('admin','doctor','patient') NOT NULL DEFAULT 'patient',
    phone VARCHAR(20) DEFAULT NULL,
    avatar VARCHAR(255) DEFAULT NULL,               -- اسم ملف الصورة بس مش المسار كامل
    is_active TINYINT(1) NOT NULL DEFAULT 1,        -- 1 = نشط، 0 = معطل
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- إضافة حساب الأدمن الأول
-- الباسورد: Admin@1234
-- هاش مولود بـ password_hash("Admin@1234", PASSWORD_BCRYPT)
INSERT INTO users (name, email, password, role)
VALUES (
    'Admin',
    'admin@clinic.local',
    '$2y$10$TKh8H1.PfBKBy7EEV5eFpe9YKBe1YdaglqBNFIL9SzxKBFg.e7PH2',
    'admin'
);
-- الباسورد: Admin@1234
-- إذا ما اشتغل، شغّل generate_hash.php وحدّث الهاش يدوياً

-- =============================================================
-- جدول 2: specializations - التخصصات الطبية
-- لازم يجي قبل doctors لأن doctors بيرجعلو
-- =============================================================
CREATE TABLE specializations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- إضافة التخصصات الافتراضية
INSERT INTO specializations (name) VALUES
    ('General Practice'),
    ('Cardiology'),
    ('Dermatology'),
    ('Pediatrics'),
    ('Orthopedics'),
    ('Neurology'),
    ('Ophthalmology'),
    ('ENT'),
    ('Psychiatry');

-- =============================================================
-- جدول 3: doctors - معلومات الأطباء الإضافية
-- كل دكتور عندو record بالـ users وrecord هون
-- =============================================================
CREATE TABLE doctors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,           -- UNIQUE لأن كل مستخدم دكتور واحد بس
    specialization_id INT UNSIGNED NOT NULL,
    bio TEXT DEFAULT NULL,
    consultation_fee DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    available_days VARCHAR(50) NOT NULL DEFAULT 'Sun,Mon,Tue,Wed,Thu',  -- بنحفظها كـ string مفصولة بفواصل
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (specialization_id) REFERENCES specializations(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================================
-- جدول 4: appointments - المواعيد
-- هاد الجدول الأهم بالمشروع
-- =============================================================
CREATE TABLE appointments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id INT UNSIGNED NOT NULL,
    doctor_id INT UNSIGNED NOT NULL,
    appt_date DATE NOT NULL,
    appt_time TIME NOT NULL,
    status ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
    reason VARCHAR(255) DEFAULT NULL,
    doctor_notes TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -- هاد الـ UNIQUE KEY بيمنع حجز نفس الموعد مرتين عند نفس الدكتور
    UNIQUE KEY no_double_booking (doctor_id, appt_date, appt_time),
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================================
-- جدول 5: prescriptions - الوصفات الطبية
-- بتجي بعد appointments لأنها بترجعلها
-- =============================================================
CREATE TABLE prescriptions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT UNSIGNED NOT NULL UNIQUE,    -- موعد واحد = وصفة واحدة بس
    diagnosis TEXT NOT NULL,
    medications TEXT NOT NULL,
    notes TEXT DEFAULT NULL,
    file_path VARCHAR(255) DEFAULT NULL,            -- مسار ملف الـ PDF إذا رفع الدكتور واحد
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
