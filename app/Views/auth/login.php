<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <?php if(session()->getFlashdata('msg')):?>
        <div>
            <?= session()->getFlashdata('msg') ?>
        </div>
    <?php endif;?>
    <form action="<?= base_url('auth/processLogin') ?>" method="post">
        <label for="username">Username:</label><br>
        <input type="text" id="username" name="username"><br><br>
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password"><br><br>
        <input type="submit" value="Login">
    </form>
    <div class="text-center">
    <a href="<?= base_url('register'); ?>" class="btn btn-sm btn-outline-secondary">Buat Akun</a>
    </div>
</body>
</html>