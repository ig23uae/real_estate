<?php
require_once '../config.php';
require_once '../middleware.php';

redirectIfAuthenticated(); // Уже вошёл? Не пускать сюда
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = htmlspecialchars(trim($_POST['full_name']));
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];

    if (!$email) $errors[] = "Некорректный email";
    if (strlen($password) < 6) $errors[] = "Пароль должен быть не короче 6 символов";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = "Email уже зарегистрирован";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (role_id, full_name, email, password_hash) VALUES (2, ?, ?, ?)");
            $stmt->execute([$full_name, $email, $hash]);
            header("Location: login.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <div class="row">
        <div class="col-4"></div>
        <div class="col-4">
            <h2>Регистрация</h2>
            <?php foreach ($errors as $error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endforeach; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="full_name" class="form-label">ФИО</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Пароль</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button class="btn btn-primary">Зарегистрироваться</button>
                <a href="login.php" class="btn btn-link">Уже есть аккаунт?</a>
            </form>
        </div>
    </div>
</body>
</html>
