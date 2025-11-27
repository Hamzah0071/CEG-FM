<?php
$matieres = ["Malagasy", "Français", "Anglais", "H-G", "MATH", "PC", "SVT", "TICE", "EPS"];
$classes  = ["6ème", "5ème", "4ème", "3ème"];
$initiales = ['A', 'B', 'C', 'D', 'E', 'F'];

$matieres = $_POST['matiere'] ?? '';
$classes  = $_POST['classe'] ?? '';
$initiales = $_POST['initiale'] ?? '';
?>

<!-- Select matière -->
<select name="matiere" required>
    <option value="">-- Choisir une matière --</option>
    <?php foreach ($matieres as $m): ?>
        <option value="<?= htmlspecialchars($m) ?>" 
            <?= (($_POST['matiere'] ?? '') === $m) ? 'selected' : '' ?>>
            <?= htmlspecialchars($m) ?>
        </option>
    <?php endforeach; ?>
</select>