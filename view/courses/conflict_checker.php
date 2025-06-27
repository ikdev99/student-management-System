<?php if (empty($conflicts) && empty($prereqs)): ?>
    <p style="color: green;">✅ No schedule conflicts or prerequisite issues detected.</p>
<?php endif; ?>

<?php if (!empty($conflicts)): ?>
    <div style="color: red;">
        <h3>Schedule Conflicts:</h3>
        <ul>
            <?php foreach ($conflicts as $conflict): ?>
                <li><?= htmlspecialchars($conflict) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (!empty($prereqs)): ?>
    <div style="color: orange;">
        <h3>Missing Prerequisites:</h3>
        <ul>
            <?php foreach ($prereqs as $issue): ?>
                <li><?= htmlspecialchars($issue) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<br>
<a href="registration_wizard.php">← Back to Registration</a>
