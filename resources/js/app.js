import './bootstrap';
function previewImage() {
    const file = document.getElementById('photo').files[0];
    const preview = document.getElementById('preview');

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
}

document.addEventListener("DOMContentLoaded", function() {
        const popup = document.querySelector('.popup-message');
        if (popup) {
            // Tampilkan
            setTimeout(() => popup.classList.add('show'), 100);

            // Sembunyikan setelah 4 detik
            setTimeout(() => {
                popup.classList.remove('show');
            }, 4000);
        }
    });