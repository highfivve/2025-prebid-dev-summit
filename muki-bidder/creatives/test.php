<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Creative Test Page</title></head>
<body style="margin:0;padding:0;background:#fff;">
<h2>Creative Test Page</h2>
<form method="get" action="">
  <label for="creative">Creative file:</label>
  <?php
  $creativeFiles = array_filter(scandir(__DIR__), function($f) {
    return preg_match('/^creative_.*\.html$/', $f);
  });
  $selectedCreative = isset($_GET['creative']) ? $_GET['creative'] : 'creative_300x250.html';
  ?>
  <select id="creative" name="creative" required>
    <?php foreach ($creativeFiles as $file): ?>
      <option value="<?= htmlspecialchars($file) ?>"<?= $file === $selectedCreative ? ' selected' : '' ?>><?= htmlspecialchars($file) ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit">Load</button>
</form>
<hr>
<div id="creative-container">
<?php
$creative = isset($_GET['creative']) ? $_GET['creative'] : '';
if ($creative && preg_match('/^[^\/]+$/', $creative)) {
    $file = __DIR__ . '/' . $creative;
    if (file_exists($file)) {
        readfile($file);
    } else {
        echo '<p style="color:red;">Creative not found.</p>';
    }
} elseif ($creative) {
    echo '<p style="color:red;">Invalid creative filename.</p>';
}
?>
</div>
</body>
</html>
