<?php ob_start(); ?>
<h1>CMS / Settings</h1>
<p>Manage branding, theme presets, and maintenance hooks from this section.</p>
<ul>
    <li>Upload logos to MinIO bucket defined in config.</li>
    <li>Configure theme colors and publish to users.</li>
    <li>Trigger database backup hook.</li>
</ul>
<?php $content = ob_get_clean(); $title = 'CMS'; include __DIR__ . '/../layout/main_layout.php';
