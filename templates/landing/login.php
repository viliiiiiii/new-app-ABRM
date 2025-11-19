<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ABRM Management - Login</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="login-body">
    <div class="login-card">
        <h1>ABRM Management</h1>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($token) ?>" />
            <label>Email</label>
            <input type="email" name="email" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <button type="submit">Login</button>
            <p><a href="#">Forgot password?</a></p>
        </form>
    </div>
</body>
</html>
