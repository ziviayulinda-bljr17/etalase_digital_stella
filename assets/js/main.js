// JS Etalase Digital Stella
document.addEventListener('DOMContentLoaded', function () {

    // 1. Konfirmasi sebelum submit form yang punya data-confirm (hapus, reset)
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!confirm(form.dataset.confirm)) e.preventDefault();
        });
    });

    // 2. Pratinjau gambar saat memilih file (input[data-preview])
    document.querySelectorAll('input[type="file"][data-preview]').forEach(function (input) {
        const img = document.querySelector(input.dataset.preview);
        if (!img) return;
        input.addEventListener('change', function () {
            const file = input.files[0];
            if (!file) return;
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran gambar maksimal 2MB.');
                input.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = function () {
                img.src = reader.result;
                img.hidden = false;
            };
            reader.readAsDataURL(file);
        });
    });

    // 4. Pop-up detail project (hanya ada di halaman etalase)
    const modal = document.getElementById('modal');
    if (modal) {
        const tutup = function () {
            modal.hidden = true;
            document.body.classList.remove('modal-terbuka');
        };

        document.querySelectorAll('a.buka-modal').forEach(function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                const d = a.closest('.card').dataset;
                document.getElementById('m-gambar').src = d.gambar;
                document.getElementById('m-gambar').alt = d.judul;
                document.getElementById('m-judul').textContent = d.judul;
                document.getElementById('m-oleh').textContent = 'Oleh ' + d.oleh + ' · ' + d.tanggal;
                document.getElementById('m-deskripsi').textContent = d.deskripsi;
                document.getElementById('m-link').href = d.link;
                const demo = document.getElementById('m-demo');
                demo.href = d.demo || '#';
                demo.hidden = !d.demo;
                modal.hidden = false;
                document.body.classList.add('modal-terbuka');
            });
        });

        document.getElementById('m-tutup').addEventListener('click', tutup);
        modal.addEventListener('click', function (e) {
            if (e.target === modal) tutup(); // klik area gelap di luar kotak
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.hidden) tutup();
        });
    }

    // 3. Notifikasi hilang otomatis
    document.querySelectorAll('.alert').forEach(function (a) {
        setTimeout(function () { a.style.display = 'none'; }, 5000);
    });
});