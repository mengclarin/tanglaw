<?php
// Minimal header include: requires connection session handled by conn.php included before this.
if (!isset($loggedUser)) {
    // if header is included without conn.php, try to include
    include 'conn.php';
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Tanglaw LMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="header">
    <div class="container" style="display:flex; justify-content:space-between; align-items:center;">
        <a href="student_dashboard.php" class="brand">Tanglaw LMS — Student</a>
        <div class="nav">
            <a href="student_dashboard.php">Dashboard</a>
            <a href="student_modules.php">Modules</a>
            <a href="submit_activity.php">Submit</a>
            <a href="my_submissions.php">My Submissions</a>
        </div>
    </div>
</div>
<div class="container">
