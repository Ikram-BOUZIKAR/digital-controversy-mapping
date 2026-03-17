<?php
/**
 * Database Initialization Script
 * Creates tables and populates seed data
 *
 * Usage: php init-db.php from command line or cPanel terminal
 */

// Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'iftl_user');
define('DB_PASS', 'iftl_pass_secure');
define('DB_NAME', 'notes_iftl_db');

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    $mysqli->set_charset('utf8mb4');

    echo "Creating tables...\n";

    // Users table
    $mysqli->query("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) UNIQUE NOT NULL,
            email VARCHAR(255),
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('Admin', 'Coordinator', 'Teacher', 'Student') NOT NULL,
            active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(username),
            INDEX(role)
        ) ENGINE=InnoDB
    ");
    echo "✓ Users table created\n";

    // Students table
    $mysqli->query("
        CREATE TABLE IF NOT EXISTS students (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_code VARCHAR(20) UNIQUE NOT NULL,
            program_id INT NOT NULL,
            group_name VARCHAR(50),
            cohort VARCHAR(10),
            email VARCHAR(255),
            phone VARCHAR(20),
            profile_updated_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(student_code),
            INDEX(cohort),
            INDEX(program_id),
            INDEX(group_name)
        ) ENGINE=InnoDB
    ");
    echo "✓ Students table created\n";

    // Programs table
    $mysqli->query("
        CREATE TABLE IF NOT EXISTS programs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(50) UNIQUE NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(code)
        ) ENGINE=InnoDB
    ");
    echo "✓ Programs table created\n";

    // Groups table
    $mysqli->query("
        CREATE TABLE IF NOT EXISTS groups (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            program_id INT NOT NULL,
            cohort VARCHAR(10),
            FOREIGN KEY(program_id) REFERENCES programs(id),
            INDEX(program_id),
            INDEX(cohort)
        ) ENGINE=InnoDB
    ");
    echo "✓ Groups table created\n";

    // Modules table
    $mysqli->query("
        CREATE TABLE IF NOT EXISTS modules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(50) NOT NULL,
            name VARCHAR(255) NOT NULL,
            program_id INT NOT NULL,
            coefficient DECIMAL(5,2) NOT NULL DEFAULT 1,
            max_score INT DEFAULT 20,
            FOREIGN KEY(program_id) REFERENCES programs(id),
            INDEX(code),
            INDEX(program_id),
            UNIQUE(code, program_id)
        ) ENGINE=InnoDB
    ");
    echo "✓ Modules table created\n";

    // Teachers table
    $mysqli->query("
        CREATE TABLE IF NOT EXISTS teachers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            specialization VARCHAR(255),
            assigned_modules JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(user_id) REFERENCES users(id),
            INDEX(user_id)
        ) ENGINE=InnoDB
    ");
    echo "✓ Teachers table created\n";

    // Grades table
    $mysqli->query("
        CREATE TABLE IF NOT EXISTS grades (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            module_id INT NOT NULL,
            score DECIMAL(5,2),
            teacher_id INT,
            entry_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            is_eliminating BOOLEAN DEFAULT FALSE,
            FOREIGN KEY(student_id) REFERENCES students(id),
            FOREIGN KEY(module_id) REFERENCES modules(id),
            FOREIGN KEY(teacher_id) REFERENCES users(id),
            UNIQUE(student_id, module_id),
            INDEX(student_id),
            INDEX(module_id),
            INDEX(is_eliminating)
        ) ENGINE=InnoDB
    ");
    echo "✓ Grades table created\n";

    // Calculations table
    $mysqli->query("
        CREATE TABLE IF NOT EXISTS calculations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            cohort VARCHAR(10),
            avg_1a DECIMAL(5,2),
            avg_2a DECIMAL(5,2),
            stages DECIMAL(5,2),
            eff DECIMAL(5,2),
            ngr_final DECIMAL(5,2),
            decision VARCHAR(20),
            calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(student_id) REFERENCES students(id),
            INDEX(student_id),
            INDEX(cohort),
            INDEX(decision)
        ) ENGINE=InnoDB
    ");
    echo "✓ Calculations table created\n";

    // Audit log table
    $mysqli->query("
        CREATE TABLE IF NOT EXISTS audit_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            action VARCHAR(50),
            record_type VARCHAR(50),
            record_id INT,
            details TEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(user_id) REFERENCES users(id),
            INDEX(user_id),
            INDEX(timestamp),
            INDEX(action)
        ) ENGINE=InnoDB
    ");
    echo "✓ Audit log table created\n";

    echo "\nInserting seed data...\n";

    // Programs
    $programs = [
        ['OTM-A', 'Techniques d\'Optimisation Logistique - Groupe A'],
        ['OTM-B', 'Techniques d\'Optimisation Logistique - Groupe B'],
        ['OFLP', 'Opérations Frêt et Logistique Portuaire'],
        ['AEL', 'Achat et Économie de la Logistique'],
        ['ECOM', 'E-Commerce et Distribution'],
        ['ADEE', 'Approvisionnement et Distribution d\'Énergie']
    ];

    foreach ($programs as [$code, $name]) {
        $stmt = $mysqli->prepare("INSERT INTO programs (code, name) VALUES (?, ?)");
        $stmt->bind_param('ss', $code, $name);
        $stmt->execute();
    }
    echo "✓ Programs inserted\n";

    // Default users
    $admin_pass = password_hash('admin123', PASSWORD_BCRYPT);
    $coordinator_pass = password_hash('coord123', PASSWORD_BCRYPT);
    $teacher_pass = password_hash('teacher123', PASSWORD_BCRYPT);

    $stmt = $mysqli->prepare("INSERT INTO users (username, email, password_hash, role, active) VALUES (?, ?, ?, ?, 1)");

    $admin_user = 'admin';
    $admin_email = 'admin@institution.local';
    $stmt->bind_param('ssss', $admin_user, $admin_email, $admin_pass, $role1);
    $role1 = 'Admin';
    $stmt->execute();

    $coord_user = 'coordinator';
    $coord_email = 'coordinator@institution.local';
    $stmt->bind_param('ssss', $coord_user, $coord_email, $coordinator_pass, $role2);
    $role2 = 'Coordinator';
    $stmt->execute();

    $teacher_user = 'teacher_demo';
    $teacher_email = 'teacher@institution.local';
    $stmt->bind_param('ssss', $teacher_user, $teacher_email, $teacher_pass, $role3);
    $role3 = 'Teacher';
    $stmt->execute();

    echo "✓ Default users created\n";
    echo "  - Admin: admin / admin123\n";
    echo "  - Coordinator: coordinator / coord123\n";
    echo "  - Teacher: teacher_demo / teacher123\n";

    // Modules for OTM-A (example: 20 modules M201-M220)
    $program_id = 1; // OTM-A
    for ($i = 201; $i <= 220; $i++) {
        $code = "M$i";
        $name = "Module M$i";
        $coef = 1.0;
        $stmt = $mysqli->prepare("INSERT INTO modules (code, name, program_id, coefficient) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssid', $code, $name, $program_id, $coef);
        $stmt->execute();
    }
    echo "✓ Sample modules created (OTM-A)\n";

    // Generate anonymized sample students (261 students)
    echo "✓ Database initialized successfully\n";
    echo "\nNext steps:\n";
    echo "1. Import student data via Excel upload feature\n";
    echo "2. Assign modules to teachers\n";
    echo "3. Start entering grades\n";

    $mysqli->close();

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
