<?php
include 'conn.php';
include 'header.php';


$studentGrade = $loggedUser['grade_level'];

// Get modules for this student's grade level using prepared statement
$stmt = $conn->prepare("SELECT id, title, file_path FROM modules WHERE grade_level = ?");
$stmt->bind_param("s", $studentGrade);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>📘 My Modules</h2>

<div style="display:flex; gap:12px; align-items:center; margin-bottom:12px;">
    <a class="btn" href="student_dashboard.php">🧭 Go to Dashboard</a>
    <a class="btn secondary" href="my_submissions.php">📥 View my Activity Submissions</a>
</div>

<?php if ($result->num_rows > 0): ?>
    <div class="grid">
    <?php while($row = $result->fetch_assoc()): ?>
        <div class="module-card card">
            <strong><?= htmlspecialchars($row['title']); ?></strong><br>
            <a href="<?= htmlspecialchars($row['file_path']); ?>" target="_blank">📖 Read Module</a>
            <br>
            <a class="btn" href="submit_activity.php?module_id=<?= $row['id'] ?>">📨 Submit Activity Sheet</a>
        </div>
    <?php endwhile; ?>
    </div>
<?php else: ?>
    <p>No modules available for your grade level.</p>
<?php endif; ?>

<?php include 'footer.php'; ?>
