<main class="content">
    <div class="container-fluid p-0">
        <div class="d-flex">
            <h1 class="h3 mb-3 fw-bold">Master Data Judul Keterangan</h1>
            <?php if (session()->get('role_id') == 1) : ?>
                <div class="ms-auto card-tools">
                    <a href="/judul-keterangan/form" class="btn btn-primary btn-md fw-bold">Tambah Data</a>
                </div>
            <?php endif; ?>
        </div>

        <?php foreach ($groupedJudulKeterangan as $sumberData => $rows) : ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title fw-bold">Sumber Data: <?= esc($sumberData); ?></h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-hover my-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Keterangan</th>
                                        <?php if (session()->get('role_id') == 1) : ?>
                                            <th class="text-center">Aksi</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; ?>
                                    <?php foreach ($rows as $row) : ?>
                                        <tr>
                                            <td><?= esc($i++); ?></td>
                                            <td><?= esc($row['jdl_keterangan']); ?></td>
                                            <?php if (session()->get('role_id') == 1) : ?>
                                                <td class="text-end" style="width: 150px;">
                                                    <a href="/judul-keterangan/form/<?= esc($row['id_jdlketerangan']); ?>" class="btn btn-warning btn-sm" style="border-radius: 10px;">Edit</a>
                                                    <form action="/judul-keterangan/delete/<?= esc($row['id_jdlketerangan']); ?>" method="post" class="d-inline delete-form">
                                                        <?= csrf_field(); ?>
                                                        <input type="hidden" name="_method" value="DELETE">
                                                        <button type="submit" class="btn btn-danger btn-sm" style="border-radius: 10px;">Hapus</button>
                                                    </form>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<!-- Tambahkan SweetAlert2 CDN di bagian akhir file, sebelum </body> -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Script untuk menangani form delete dengan SweetAlert -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteForms = document.querySelectorAll('.delete-form');

        deleteForms.forEach(form => {
            form.addEventListener('submit', function(event) {
                event.preventDefault(); // Mencegah form untuk langsung di-submit

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Anda tidak akan bisa mengembalikan data ini!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Jika user mengkonfirmasi, submit form secara manual
                        this.submit();
                    }
                });
            });
        });
    });

    // Menampilkan pesan SweetAlert dari flashdata setelah halaman dimuat ulang
    const flashdata = <?php echo json_encode(session()->getFlashdata('pesan_swal')); ?>;
    if (flashdata) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: flashdata,
            timer: 3000, // Menutup otomatis setelah 3 detik
            showConfirmButton: false
        });
    }
</script>
