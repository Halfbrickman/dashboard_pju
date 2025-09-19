<main class="content">
    <div class="container-fluid p-0">

        <h1 class="h3 mb-3">GALERI FOTO</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <?php 
                            if (!empty($photos)): 
                            ?>
                                <?php foreach ($photos as $photo): ?>
                                    <div class="col-md-4 mb-4">
                                        <div class="card h-100">
                                            <a href="#" class="d-block" data-bs-toggle="modal" data-bs-target="#imageModal" 
                                               onclick="showImageModal('<?= base_url($photo['file_path']) ?>', '<?= esc($photo['nama_photo']) ?>')">
                                                <img src="<?= base_url($photo['file_path']) ?>" class="card-img-top" alt="<?= esc($photo['nama_photo']) ?>">
                                            </a>
                                            <div class="card-body">
                                                <h5 class="card-title text-center"><?= esc($photo['nama_photo']) ?></h5>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <p class="text-center text-muted">Belum ada foto yang diunggah.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="imageModalLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img src="" id="modalImage" class="img-fluid" alt="">
      </div>
    </div>
  </div>
</div>
<script>
    function showImageModal(imageSrc, imageTitle) {
        // Ambil elemen gambar dan judul dari modal
        const modalImage = document.getElementById('modalImage');
        const modalTitle = document.getElementById('imageModalLabel');

        // Isi atribut src dan textContent dengan data dari foto yang diklik
        modalImage.src = imageSrc;
        modalTitle.textContent = imageTitle;
    }
</script>