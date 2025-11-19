<?php
include 'conn.php';
include 'header.php';

$student = $loggedUser;
$studentId = $student['id'];
$grade = $student['grade_level'];

// Check if activity_submissions table exists
$hasSubmissionsTable = false;
$resCheck = $conn->query("SHOW TABLES LIKE 'activity_submissions'");
if ($resCheck && $resCheck->num_rows > 0) {
    $hasSubmissionsTable = true;
}

// Modules count and list
$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM modules WHERE grade_level = ?");
$stmt->bind_param("s", $grade);
$stmt->execute();
$countRes = $stmt->get_result()->fetch_assoc();
$moduleCount = $countRes['cnt'] ?? 0;

$stmt = $conn->prepare("SELECT id, title, file_path FROM modules WHERE grade_level = ? ORDER BY id DESC LIMIT 5");
$stmt->bind_param("s", $grade);
$stmt->execute();
$modules = $stmt->get_result();

// Submissions count and recent
$submissionCount = 0;
$recentSubmissions = [];
if ($hasSubmissionsTable) {
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM activity_submissions WHERE student_id = ?");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $sc = $stmt->get_result()->fetch_assoc();
    $submissionCount = $sc['cnt'] ?? 0;

    $stmt = $conn->prepare("SELECT a.id, a.module_id, a.file_path, a.comments, a.status, a.submitted_at, m.title AS module_title
        FROM activity_submissions a
        LEFT JOIN modules m ON m.id = a.module_id
        WHERE a.student_id = ?
        ORDER BY a.submitted_at DESC
        LIMIT 5");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $recentSubmissions = $stmt->get_result();
}

?>

<h2>🧭 Student Dashboard</h2>

<div class="grid">
    <div class="card">
        <h3>Welcome, <?php echo htmlspecialchars($student['name']); ?></h3>
        <p><strong>Grade level:</strong> <?php echo htmlspecialchars($student['grade_level']); ?></p>
        <p><strong>Modules available:</strong> <?php echo intval($moduleCount); ?></p>
        <p><strong>Submissions:</strong> <?php echo intval($submissionCount); ?></p>
        <p><a href="student_modules.php">📘 Read Modules</a></p>
        <p><a href="submit_activity.php">📨 Submit Activity Sheet</a></p>
        <p><a href="my_submissions.php">📥 My Submissions</a></p>
    </div>

    <div class="card">
        <h4>Recent Modules</h4>
        <?php if ($modules->num_rows > 0): ?>
            <ul>
                <?php while ($m = $modules->fetch_assoc()): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($m['title']); ?></strong>
                        - <a href="<?php echo htmlspecialchars($m['file_path']); ?>" target="_blank">Read</a>
                        - <a href="submit_activity.php?module_id=<?php echo $m['id']; ?>">Submit</a>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <div>No modules found for your grade level.</div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h4>Recent Submissions</h4>
        <?php if (!$hasSubmissionsTable): ?>
            <p>No submissions table created yet.</p>
        <?php elseif ($recentSubmissions && $recentSubmissions->num_rows > 0): ?>
            <ul>
            <?php while ($s = $recentSubmissions->fetch_assoc()): ?>
                <li>
                    <span><?php echo htmlspecialchars($s['module_title'] ?? 'Unknown Module'); ?> - </span>
                    <a href="<?php echo htmlspecialchars($s['file_path']); ?>" target="_blank">View</a>
                    <span> (<?php echo htmlspecialchars($s['status']); ?>)</span>
                    <br><small><?php echo htmlspecialchars($s['submitted_at']); ?></small>
                </li>
            <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <div>No recent submissions.</div>
        <?php endif; ?>
    </div>
</div>

