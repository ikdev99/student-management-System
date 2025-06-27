<?php if ($message): ?>
    <p class="message success"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<?php if ($error): ?>
    <p class="message error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" action="../../controller/CourseController.php">
    <input type="hidden" name="register_courses" value="1">

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Select</th>
                <th>Course Code</th>
                <th>Name</th>
                <th>Credits</th>
                <th>Schedule</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($courses as $course): ?>
                <tr>
                    <td><input type="checkbox" name="courses[]" value="<?= $course['id'] ?>"></td>
                    <td><?= htmlspecialchars($course['course_code']) ?></td>
                    <td><?= htmlspecialchars($course['course_name']) ?></td>
                    <td><?= htmlspecialchars($course['credits']) ?></td>
                    <td><?= htmlspecialchars($course['schedule']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <br>
    <button type="submit">Submit Registration</button>
</form>

<br>
<a href="../students/dashboard.php">← Back to Dashboard</a>
