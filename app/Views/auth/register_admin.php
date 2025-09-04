<!DOCTYPE html>
<html>
<head>
    <title>Daftar Akun Admin</title>
    <style>
        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Daftar Akun Admin</h2>
        
        <?php if(session()->getFlashdata('msg')):?>
            <div class="alert alert-danger">
                <?= session()->getFlashdata('msg') ?>
            </div>
        <?php endif;?>

        <form action="<?= base_url('auth/processRegisterAdmin') ?>" method="post">
            <div class="form-group mb-3">
                <label for="username">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            
            <div class="form-group mb-3">
                <label for="password">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="form-group mb-3">
                <label for="password_confirm">Konfirmasi Password</label>
                <input type="password" name="password_confirm" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Daftar Admin</button>
        </form>
    </div>
</body>
</html>