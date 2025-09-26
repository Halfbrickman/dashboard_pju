<main class="content">
	<div class="container-fluid p-0">

		<h1 class="h3 mb-3"><strong>Dashboard PJU</strong></h1>

		<div class="row">
			<div class="col-xl-6 col-xxl-5 d-flex">
				<div class="w-100">
					<div class="row">
						<!-- Kartu untuk seluruh data koordinat (tetap statis) -->
						<div class="col-sm-6">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col mt-0">
											<h5 class="card-title">Seluruh Data Kordinat</h5>
										</div>
									</div>
									<h1 class="mt-1 mb-3"><?php echo number_format($dataKordinat, 0, '', '.'); ?></h1>
									<div class="mb-0">
										<span class="text-muted">semua koordinat</span>
									</div>
								</div>
							</div>
						</div>

						<!-- Kartu-kartu yang dihasilkan secara dinamis dari perulangan -->
						<?php foreach ($dataPerSumber as $sumber) : ?>
							<div class="col-sm-6">
								<div class="card">
									<div class="card-body">
										<div class="row">
											<div class="col mt-0">
												<!-- Menampilkan nama sumber data dari perulangan -->
												<h5 class="card-title">Data Kordinat <?= $sumber['nama'] ?></h5>
											</div>
										</div>
										<!-- Menampilkan jumlah data dari perulangan -->
										<h1 class="mt-1 mb-3"><?php echo number_format($sumber['jumlah'], 0, '', '.'); ?></h1>
										<div class="mb-0">
											<span class="text-muted">Data Kordinat <?= $sumber['nama'] ?></span>
										</div>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<div class="col-xl-6 col-xxl-7">
				<div class="card flex-fill w-100">
					<div class="card-header">
						<h5 class="card-title mb-0">Range Data</h5>
					</div>
					<div class="card-body py-3">
						<div class="chart chart-sm">
							<canvas id="chartjs-dashboard-line"></canvas>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-12 col-md-12 col-xxl-12 d-flex order-3 order-xxl-2">
				<div class="card flex-fill w-100">
					<div class="card-header">

						<h5 class="card-title mb-0">Map Marker</h5>
					</div>
					<div class="card-body px-4 pt-0">
						<div id="world_map" style="height:390px;"></div>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-header">
						<h5 class="card-title mb-0">
							<i class="align-middle" data-feather="bell"></i> Notifikasi Upload Terbaru
						</h5>
					</div>
					<div class="list-group list-group-flush">
						<?php if (!empty($notifikasi)) : ?>
							<?php foreach ($notifikasi as $notif) : ?>
								<div class="list-group-item">
									<div class="d-flex align-items-center">
										<div class="flex-grow-1">
											<div class="d-flex justify-content-between">
												<div>
													<strong><?= esc($notif['pesan']); ?></strong>
													<div class="text-muted small mt-1">
														File: <?= esc($notif['nama_file']); ?> | Tipe: <?= ucfirst(esc($notif['tipe'])); ?> | Waktu: <?= date('d M Y, H:i', strtotime($notif['created_at'])); ?>
													</div>
												</div>
												<div class="ms-3">
													<a href="<?= site_url('dashboard/downloadNotificationFile/' . $notif['id']); ?>" class="btn btn-primary btn-sm">
														<i class="align-middle" data-feather="download"></i> Download
													</a>
												</div>
											</div>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						<?php else : ?>
							<div class="list-group-item">
								<div class="text-center text-muted py-3">
									Tidak ada notifikasi baru.
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>