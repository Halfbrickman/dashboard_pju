<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Form Input Koordinat</title>
    <style>
        .hidden { display: none; }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<h1>Form Input Koordinat</h1>

<form action="<?= base_url('koordinat/simpan') ?>" method="post">
    <div>
        <label for="latitude">Latitude:</label>
        <input type="text" id="latitude" name="latitude" required>
    </div>
    <br>
    <div>
        <label for="longitude">Longitude:</label>
        <input type="text" id="longitude" name="longitude" required>
    </div>
    <br>
    <div>
        <label for="id_sumberdata">Pilih Sumber Data:</label>
        <select id="id_sumberdata" name="id_sumberdata" required>
            <option value="">--Pilih--</option>
            <?php foreach ($sumberData as $sd): ?>
                <option value="<?= $sd['id_sumberdata'] ?>"><?= $sd['nama_sumber'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <br>

    <div id="isiKeteranganContainer" class="hidden">
        <h3>Input Keterangan</h3>
        </div>
    <br>
    <button type="submit">Simpan</button>
</form>

<script>
$(document).ready(function() {
    $('#id_sumberdata').on('change', function() {
        var id_sumberdata = $(this).val();
        var container = $('#isiKeteranganContainer');
        
        if (id_sumberdata) {
            $.ajax({
                url: '<?= base_url('koordinat/getJudulKeterangan') ?>',
                method: 'POST',
                data: { id_sumberdata: id_sumberdata },
                dataType: 'json',
                success: function(response) {
                    container.html('<h3>Input Keterangan</h3>'); // Reset container
                    if (response.length > 0) {
                        $.each(response, function(index, item) {
                            var html = `
                                <div>
                                    <label for="isi_keterangan_${item.id_jdlketerangan}">${item.jdl_keterangan}:</label>
                                    <input type="text" id="isi_keterangan_${item.id_jdlketerangan}" name="isi_keterangan[]" required>
                                    <input type="hidden" name="id_jdlketerangan[]" value="${item.id_jdlketerangan}">
                                </div>
                                <br>
                            `;
                            container.append(html);
                        });
                        container.removeClass('hidden');
                    } else {
                        container.addClass('hidden');
                    }
                }
            });
        } else {
            container.html('').addClass('hidden');
        }
    });
});
</script>

</body>
</html>