<?php
include 'conn.php';
include 'header.php';

if (!isset($_SESSION)) {
    session_start();
}

$studentId = $loggedUser['id'];

// Accept module_id as GET parameter
$moduleId = isset($_GET['module_id']) ? intval($_GET['module_id']) : 0;

// Get module info for display
$moduleTitle = '';
if ($moduleId > 0) {
    $stmt = $conn->prepare("SELECT id, title FROM modules WHERE id = ?");
    $stmt->bind_param("i", $moduleId);
    $stmt->execute();
    $modRes = $stmt->get_result();
    if ($modRes->num_rows > 0) {
        $row = $modRes->fetch_assoc();
        $moduleTitle = $row['title'];
    }
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic validation
    $mId = intval($_POST['module_id']);
    $comments = isset($_POST['comments']) ? trim($_POST['comments']) : '';

    if (!isset($_FILES['activity_sheet'])) {
        $errors[] = 'No file uploaded.';
    } else {
        $file = $_FILES['activity_sheet'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload error.';
        } else {
            // Validate size (max 10MB)
            if ($file['size'] > 10 * 1024 * 1024) {
                $errors[] = 'File too large (max 10 MB).';
            }

            // Validate type
            $allowed = ['application/pdf', 'image/png', 'image/jpeg', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $allowed)) {
                $errors[] = 'File type not allowed. Use PDF, DOC, DOCX, JPG, PNG.';
            }

            if (empty($errors)) {
                $uploadDir = __DIR__ . '/uploads/activity_sheets/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $baseName = pathinfo($file['name'], PATHINFO_FILENAME);
                $newName = $studentId . '_' . $mId . '_' . time() . '_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $baseName) . '.' . $ext;

                $dest = $uploadDir . $newName;

                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    // Save metadata in DB (create table if not exists)
                    $createSql = "CREATE TABLE IF NOT EXISTS activity_submissions (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        student_id INT NOT NULL,
                        module_id INT NOT NULL,
                        file_path VARCHAR(255) NOT NULL,
                        comments TEXT,
                        status VARCHAR(50) DEFAULT 'submitted',
                        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                    $conn->query($createSql);

                    $relPath = 'uploads/activity_sheets/' . $newName;
                    $insStmt = $conn->prepare("INSERT INTO activity_submissions (student_id, module_id, file_path, comments) VALUES (?, ?, ?, ?)");
                    $insStmt->bind_param("iiss", $studentId, $mId, $relPath, $comments);
                    $ok = $insStmt->execute();

                    if ($ok) {
                        $success = 'Activity sheet submitted successfully and sent to Tanglaw Facilitator.';
                    } else {
                        $errors[] = 'Failed to record submission: ' . $conn->error;
                    }
                } else {
                    $errors[] = 'Failed to move uploaded file.';
                }
            }
        }
    }
}

// Fetch modules for dropdown
$modules = [];
$stmt = $conn->prepare("SELECT id, title FROM modules WHERE grade_level = ?");
$stmt->bind_param("s", $loggedUser['grade_level']);
$stmt->execute();
$modResult = $stmt->get_result();
while ($m = $modResult->fetch_assoc()) {
    $modules[] = $m;
}
?>

<h2>✉️ Submit Activity Sheet</h2>

<?php if ($success): ?>
    <div class="card" style="background:#ecfdf5; border-color:#bbf7d0;">
        <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="card" style="background:#fff1f2; border-color:#fecaca;">
        <?php foreach ($errors as $e): ?>
            <div>- <?php echo htmlspecialchars($e); ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" style="margin-top:15px;" class="card">
    <label>Module:</label><br>
    <select name="module_id" required style="width:90%; padding:6px;">
        <option value="">-- choose a module --</option>
        <?php foreach ($modules as $m): ?>
            <option value="<?php echo $m['id']; ?>" <?php echo ($m['id'] == $moduleId) ? 'selected' : ''; ?>><?php echo htmlspecialchars($m['title']); ?></option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <label>Attach Activity Sheet (PDF, DOC, DOCX, JPG, PNG):</label><br>
    <input type="file" name="activity_sheet" accept=".pdf, .doc, .docx, .jpg, .jpeg, .png" required>
    <br><br>

    <label>Comments (optional):</label><br>
    <textarea name="comments" rows="5" style="width:90%;"></textarea>
    <br><br>

    <button type="submit" class="btn">Submit to Tanglaw Facilitator</button>
</form>

<hr>

<p style="margin-top:12px;"><a href="student_modules.php">← Back to Modules</a></p>

<?php include 'footer.php'; ?>