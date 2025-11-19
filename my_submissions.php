<?php
include 'conn.php';
include 'header.php';

$studentId = $loggedUser['id'];

$stmt = $conn->prepare("SELECT a.id, a.module_id, a.file_path, a.comments, a.status, a.submitted_at, m.title AS module_title
FROM activity_submissions a
LEFT JOIN modules m ON m.id = a.module_id
WHERE a.student_id = ?
ORDER BY a.submitted_at DESC");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$res = $stmt->get_result();
?>

<h2>📥 My Activity Submissions</h2>

<?php if ($res->num_rows > 0): ?>
    <table class="card" border="0" cellpadding="8" style="width:100%; max-width:800px; border-collapse:collapse;">
        <tr style="background:#f7f7f7; font-weight:bold;"><td>Module</td><td>File</td><td>Comments</td><td>Status</td><td>Submitted At</td></tr>
        <?php while ($r = $res->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($r['module_title']); ?></td>
                <td><a href="<?php echo htmlspecialchars($r['file_path']); ?>" target="_blank">View</a></td>
                <td><?php echo nl2br(htmlspecialchars($r['comments'])); ?></td>
                <td><?php echo htmlspecialchars($r['status']); ?></td>
                <td><?php echo htmlspecialchars($r['submitted_at']); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>No submissions found.</p>
<?php endif; ?>

<hr>
<p><a href="student_modules.php">← Back to Modules</a></p>

<?php include 'footer.php'; ?>
