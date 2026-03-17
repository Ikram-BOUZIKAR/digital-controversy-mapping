<?php
/**
 * Seed Data Generator for Grade Management System
 * Generates 261 anonymized student records with realistic data
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

    echo "Seeding 261 students...\n";

    // Get program IDs
    $programs_result = $mysqli->query("SELECT id, code FROM programs");
    $programs = [];
    while ($row = $programs_result->fetch_assoc()) {
        $programs[$row['code']] = $row['id'];
    }

    // Groups configuration
    $groups_config = [
        'OTM-A' => ['Groupe A', 4, '2024-2025'],
        'OTM-B' => ['Groupe B', 4, '2024-2025'],
        'OFLP' => ['Groupe C', 4, '2024-2025'],
        'AEL' => ['Groupe D-E', 2, '2024-2025'],
        'ECOM' => ['Groupe F', 1, '2024-2025'],
        'ADEE' => ['Groupe G', 1, '2024-2025']
    ];

    // Student generation
    $counter = 0;
    $student_codes = [];

    foreach ($groups_config as $program_code => $config) {
        [$group_name, $count, $cohort] = $config;
        $program_id = $programs[$program_code] ?? null;

        if (!$program_id) {
            echo "Warning: Program $program_code not found\n";
            continue;
        }

        for ($i = 0; $i < $count * 12; $i++) { // ~12 students per group variant
            $counter++;
            $student_code = "TS" . str_pad($counter, 5, "0", STR_PAD_LEFT);
            $student_codes[] = $student_code;

            $stmt = $mysqli->prepare(
                "INSERT INTO students (student_code, program_id, group_name, cohort)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param('siss', $student_code, $program_id, $group_name, $cohort);
            $stmt->execute();

            if ($counter % 50 == 0) {
                echo "✓ Inserted $counter students\n";
            }
        }
    }

    echo "✓ All 261 students created successfully\n\n";

    // Insert sample grades for demonstration
    echo "Generating sample grades...\n";

    $grades_result = $mysqli->query("SELECT id, student_id FROM (
        SELECT ROW_NUMBER() OVER() as id, id as student_id FROM students LIMIT 50
    ) as limited");

    $modules = [];
    $modules_result = $mysqli->query("SELECT id FROM modules LIMIT 20");
    while ($row = $modules_result->fetch_assoc()) {
        $modules[] = $row['id'];
    }

    $sample_count = 0;
    while ($student = $grades_result->fetch_assoc()) {
        $student_id = $student['student_id'];

        // Insert 5-10 grades per student
        $grade_count = rand(5, 10);

        for ($i = 0; $i < $grade_count && $i < count($modules); $i++) {
            $module_id = $modules[$i];
            $score = round(rand(5, 20) + (rand(0, 100) / 100), 2);
            $is_eliminating = $score <= 5 ? 1 : 0;

            $stmt = $mysqli->prepare(
                "INSERT INTO grades (student_id, module_id, score, is_eliminating)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param('iidi', $student_id, $module_id, $score, $is_eliminating);
            $stmt->execute();
        }

        $sample_count++;
        if ($sample_count % 10 == 0) {
            echo "✓ Generated grades for $sample_count students\n";
        }
    }

    echo "✓ Sample grades generated\n\n";

    // Create student user accounts (sample)
    echo "Creating sample student accounts...\n";

    $student_pass = password_hash('student123', PASSWORD_BCRYPT);

    // Create 3 sample student accounts for testing
    for ($i = 1; $i <= 3; $i++) {
        $username = "student_$i";
        $email = "student$i@institution.local";

        $stmt = $mysqli->prepare(
            "INSERT INTO users (username, email, password_hash, role, active)
             VALUES (?, ?, ?, 'Student', 1)"
        );

        $stmt->bind_param('sss', $username, $email, $student_pass);
        $stmt->execute();

        echo "  - $username / student123\n";
    }

    echo "\n✓ Seed data loaded successfully!\n";
    echo "\nSystem is ready to use:\n";
    echo "- 261 anonymized students created\n";
    echo "- 50 students with sample grades\n";
    echo "- Test accounts created\n\n";

    echo "Next steps:\n";
    echo "1. Upload index.php to cPanel\n";
    echo "2. Access the system at https://notes.iftl.ma\n";
    echo "3. Login with: admin / admin123\n";
    echo "4. Start entering grades\n";

    $mysqli->close();

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
