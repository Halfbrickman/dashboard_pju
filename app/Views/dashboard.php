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
													<span class="text-muted">Data seluruh kordinat</span>
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
									<h5 class="card-title mb-0">Recent Movement</h5>
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
						<div class="col-12 col-lg-8 col-xxl-9 d-flex">
							<div class="card flex-fill">
								<div class="card-header">

									<h5 class="card-title mb-0">Latest Projects</h5>
								</div>
								<table class="table table-hover my-0">
									<thead>
										<tr>
											<th>Name</th>
											<th class="d-none d-xl-table-cell">Start Date</th>
											<th class="d-none d-xl-table-cell">End Date</th>
											<th>Status</th>
											<th class="d-none d-md-table-cell">Assignee</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>Project Apollo</td>
											<td class="d-none d-xl-table-cell">01/01/2023</td>
											<td class="d-none d-xl-table-cell">31/06/2023</td>
											<td><span class="badge bg-success">Done</span></td>
											<td class="d-none d-md-table-cell">Vanessa Tucker</td>
										</tr>
										<tr>
											<td>Project Fireball</td>
											<td class="d-none d-xl-table-cell">01/01/2023</td>
											<td class="d-none d-xl-table-cell">31/06/2023</td>
											<td><span class="badge bg-danger">Cancelled</span></td>
											<td class="d-none d-md-table-cell">William Harris</td>
										</tr>
										<tr>
											<td>Project Hades</td>
											<td class="d-none d-xl-table-cell">01/01/2023</td>
											<td class="d-none d-xl-table-cell">31/06/2023</td>
											<td><span class="badge bg-success">Done</span></td>
											<td class="d-none d-md-table-cell">Sharon Lessman</td>
										</tr>
										<tr>
											<td>Project Nitro</td>
											<td class="d-none d-xl-table-cell">01/01/2023</td>
											<td class="d-none d-xl-table-cell">31/06/2023</td>
											<td><span class="badge bg-warning">In progress</span></td>
											<td class="d-none d-md-table-cell">Vanessa Tucker</td>
										</tr>
										<tr>
											<td>Project Phoenix</td>
											<td class="d-none d-xl-table-cell">01/01/2023</td>
											<td class="d-none d-xl-table-cell">31/06/2023</td>
											<td><span class="badge bg-success">Done</span></td>
											<td class="d-none d-md-table-cell">William Harris</td>
										</tr>
										<tr>
											<td>Project X</td>
											<td class="d-none d-xl-table-cell">01/01/2023</td>
											<td class="d-none d-xl-table-cell">31/06/2023</td>
											<td><span class="badge bg-success">Done</span></td>
											<td class="d-none d-md-table-cell">Sharon Lessman</td>
										</tr>
										<tr>
											<td>Project Romeo</td>
											<td class="d-none d-xl-table-cell">01/01/2023</td>
											<td class="d-none d-xl-table-cell">31/06/2023</td>
											<td><span class="badge bg-success">Done</span></td>
											<td class="d-none d-md-table-cell">Christina Mason</td>
										</tr>
										<tr>
											<td>Project Wombat</td>
											<td class="d-none d-xl-table-cell">01/01/2023</td>
											<td class="d-none d-xl-table-cell">31/06/2023</td>
											<td><span class="badge bg-warning">In progress</span></td>
											<td class="d-none d-md-table-cell">William Harris</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
						<div class="col-12 col-lg-4 col-xxl-3 d-flex">
							<div class="card flex-fill w-100">
								<div class="card-header">

									<h5 class="card-title mb-0">Monthly Sales</h5>
								</div>
								<div class="card-body d-flex w-100">
									<div class="align-self-center chart chart-lg">
										<canvas id="chartjs-dashboard-bar"></canvas>
									</div>
								</div>
							</div>
						</div>
					</div>

				</div>
			</main>