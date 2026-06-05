<?php
/**
 * ============================================================
 * Project  : ClinicDesk - Clinic Management Dashboard
 * Name     : Abd alrahman Moen Hemaid | ID: 120240672
 * الجامعة  : الجامعة الإسلامية - غزة
 * Course   : SDEV 2106 / WDMM 2010 / MOBC 2102
 * Instructor: Eng. Mohammed Zuqlam | Semester 2, 2025-2026
 * ============================================================
 */

// Database.php - Singleton Pattern
// نسخة واحدة من الاتصال بقاعدة البيانات طول عمر الـ request

require_once dirname(__DIR__) . '/config/database.php';

class Database
{
    private static $instance = null;
    private $conn;

    private function __construct()
    {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($this->conn->connect_error) {
            error_log('DB Error: ' . $this->conn->connect_error);
            throw new RuntimeException('Database connection error. Please try again later.');
        }

        $this->conn->set_charset(DB_CHARSET);
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // query() - تنفيذ استعلام بـ prepared statements
    // بتحمي من SQL Injection لأننا ما بنحط المتغيرات مباشرة بالـ SQL
    public function query(string $sql, string $types = '', array $params = [])
    {
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            error_log('Query failed: ' . $this->conn->error . ' | SQL: ' . $sql);
            throw new RuntimeException('Database query error.');
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result !== false) {
            $stmt->close();
            return $result;
        } else {
            $stmt->close();
            return true;
        }
    }

    public function lastInsertId()
    {
        return $this->conn->insert_id;
    }

    private function __clone() {}
    public function __wakeup() {}  // public عشان PHP 8 ما يعطي warning
}
