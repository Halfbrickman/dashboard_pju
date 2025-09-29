<main class="content">
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-bold">Daftar Pengguna</h1>
        <?php if (session()->get('role_id') == 1) : ?>
            <a href="<?= base_url('users/create') ?>" class="btn btn-primary btn-md fw-bold">Tambah Pengguna</a>
        <?php endif; ?>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover my-0">
                            <thead>
                                <tr>
                                    <th class="w-5">No.</th>
                                    <th class="w-20">Nama Lengkap</th> 
                                    <th class="w-15">Username</th> 
                                    <th class="w-15">Role</th>
                                    <th class="w-15">Sumber Data</th> 
                                    <?php if (session()->get('role_id') == 1) : ?>
                                        <th class="w-30 text-center">Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($users)) : ?>
                                    <?php $no = 1; ?>
                                    <?php foreach ($users as $user) : ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= esc($user['nama']); ?></td>
                                            <td><?= esc($user['username']); ?></td>
                                            <td><?= esc($user['nama_roles']); ?></td>
                                            <td><?= esc($user['nama_sumber'] ?? 'Global'); ?></td> 
                                            <?php if (session()->get('role_id') == 1) : ?>
                                                <td class="text-center">
                                                    <a href="<?= base_url('users/edit/' . esc($user['id'])) ?>" class="btn btn-warning btn-sm">Edit</a>
                                                    <a href="#" class="btn btn-danger btn-sm delete-btn ms-2" data-id="<?= esc($user['id']) ?>">Hapus</a>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada data pengguna yang ditemukan.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</main>

<form id="deleteForm" method="post" action="" style="display:none;">
    <?= csrf_field() ?>
    <input type="hidden" name="_method" value="DELETE">
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Ambil flash message dari CodeIgniter Session
    const successMessage = '<?= session()->getFlashdata('success') ?>';
    const errorMessage = '<?= session()->getFlashdata('error') ?>';

    // Notifikasi SweetAlert untuk operasi Simpan, Edit, dan Hapus yang berhasil
    if (successMessage) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            html: successMessage, // Menggunakan html agar bold tag berfungsi
            showConfirmButton: false,
            timer: 3000
        });
    }

    // Notifikasi SweetAlert untuk error (biasanya dari validasi)
    if (errorMessage) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: errorMessage,
            showConfirmButton: true,
        });
    }

    // Catatan: Jika ada error validasi (session('errors')), SweetAlert tidak digunakan
    // karena error tersebut sudah ditampilkan di form.
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    const deleteForm = document.getElementById('deleteForm');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.getAttribute('data-id');
            
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: "Anda yakin ingin menghapus pengguna ini? Aksi ini tidak dapat dibatalkan.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Set URL aksi dan submit form delete
                    deleteForm.setAttribute('action', '<?= base_url('users/delete/') ?>' + userId);
                    deleteForm.submit();
                }
            });
        });
    });
});
</script>