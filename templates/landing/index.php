<?php
ob_start();
?>
<div class="landing-blank">
    <h1>Welcome to ABRM Management</h1>
    <p>Select a module from the sidebar to begin.</p>
</div>
<?php
$content = ob_get_clean();
$title = 'Landing';
include __DIR__ . '/../layout/main_layout.php';
