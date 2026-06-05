# ClinicDesk — Clinic Management Dashboard

## معلومات الطالب
| | |
|---|---|
| **الاسم** | عبد الرحمن معين حميد |
| **Name** | Abd alrahman Moen Hemaid |
| **الرقم الجامعي** | 120240672 |

## معلومات المشروع
- **المادة:** SDEV 2106 / WDMM 2010 / MOBC 2102
- **الجامعة:** الجامعة الإسلامية - غزة
- **المدرس:** المهندس محمد زقلام

---

## متطلبات التشغيل
- PHP 8.0+
- MySQL 5.7+ أو MariaDB
- Apache مع mod_rewrite مفعّل (XAMPP يشتغل تمام)
- AdminLTE 3.x

---

## خطوات التثبيت

### 1. إعداد قاعدة البيانات
```sql
-- شغّل الملف التالي في phpMyAdmin
clinicdesk_db.sql
```

### 2. إعداد ملف الاتصال
```php
// عدّل config/database.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'clinicdesk_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 3. إعداد الـ BASE_URL
```php
// في config/config.php عدّل حسب مجلدك
define('BASE_URL', '/clinicdesk/');
```

### 4. تحميل AdminLTE
- حمّل من: https://adminlte.io (v3.x)
- فكّه في: `public/assets/adminlte/`

### 5. صلاحيات مجلدات الرفع
```bash
chmod 755 public/uploads/
chmod 755 public/uploads/avatars/
chmod 755 public/uploads/doctor_photos/
chmod 755 public/uploads/prescriptions/
```

---

## بيانات الدخول الافتراضية

| الدور | الإيميل | الباسورد |
|-------|---------|---------|
| Admin | admin@clinic.local | Admin@1234 |

---

## هيكل المشروع

```
clinicdesk/
├── index.php              ← Front Controller
├── .htaccess              ← توجيه كل الطلبات
├── clinicdesk_db.sql      ← قاعدة البيانات
├── config/
│   ├── config.php         ← إعدادات عامة
│   └── database.php       ← بيانات الاتصال (لا ترفعه على GitHub!)
├── core/
│   ├── Database.php       ← Singleton
│   ├── Auth.php           ← تسجيل الدخول والصلاحيات
│   ├── CSRF.php           ← حماية النماذج
│   ├── Paginator.php      ← الترقيم
│   └── helpers.php        ← وظائف مساعدة
├── models/                ← التعامل مع قاعدة البيانات
├── controllers/           ← منطق التطبيق
├── views/                 ← واجهة المستخدم
└── public/
    ├── assets/adminlte/   ← AdminLTE (لا ترفعه على GitHub!)
    └── uploads/           ← ملفات المستخدمين
```

---

## الأمان المطبّق
- ✅ Prepared Statements لكل الاستعلامات
- ✅ CSRF Protection لكل النماذج
- ✅ XSS Protection بـ htmlspecialchars()
- ✅ Session Fixation Protection
- ✅ Role-Based Access Control
- ✅ Ownership verification
- ✅ File type validation (getimagesize + finfo)
- ✅ Prescription files protected from direct access
