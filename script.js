/**
 * script.js — BluePulse Student Panel
 * Spinner, view toggle, photo preview, lightbox, form submit lock, confirm dialog
 */

/* ── 1. Page Loading Spinner ───────────────────────────────────── */
window.addEventListener('load', () => {
    const spinner = document.getElementById('page-spinner');
    if (spinner) {
        spinner.classList.add('hidden');
        setTimeout(() => { spinner.style.display = 'none'; }, 500);
    }
});

/* Tampilkan spinner saat form submit */
document.querySelectorAll('form.show-spinner').forEach(form => {
    form.addEventListener('submit', () => {
        const spinner = document.getElementById('page-spinner');
        if (spinner) {
            spinner.style.display  = 'flex';
            spinner.style.opacity  = '1';
            spinner.classList.remove('hidden');
        }
    });
});

/* ── 2. View Toggle (Tabel ↔ Kartu) ───────────────────────────── */
const btnTable  = document.getElementById('btn-view-table');
const btnCards  = document.getElementById('btn-view-cards');
const viewTable = document.getElementById('view-table');
const viewCards = document.getElementById('view-cards');

function setView(mode) {
    if (!btnTable || !btnCards || !viewTable || !viewCards) return;
    if (mode === 'cards') {
        viewTable.style.display = 'none';
        viewCards.style.display = '';
        btnTable.classList.remove('active');
        btnCards.classList.add('active');
        localStorage.setItem('bp_view', 'cards');
    } else {
        viewTable.style.display = '';
        viewCards.style.display = 'none';
        btnTable.classList.add('active');
        btnCards.classList.remove('active');
        localStorage.setItem('bp_view', 'table');
    }
}

if (btnTable) {
    btnTable.addEventListener('click', () => setView('table'));
    btnCards.addEventListener('click', () => setView('cards'));
    // Restore dari localStorage
    const saved = localStorage.getItem('bp_view') || 'table';
    setView(saved);
}

/* ── 3. Photo Preview + Drag-over ─────────────────────────────── */
(function initPhotoUpload() {
    const dropzone    = document.querySelector('.photo-dropzone');
    const fileInput   = document.getElementById('photo-input');
    const previewImg  = document.getElementById('photo-preview-img');
    const removeBtn   = document.getElementById('photo-remove-btn');
    const placeholder = document.querySelector('.dropzone-placeholder');
    const photoField  = document.getElementById('photo-remove-flag');   // hidden input

    if (!dropzone || !fileInput) return;

    function showPreview(src) {
        if (!previewImg) return;
        previewImg.src = src;
        previewImg.classList.add('active');
        if (placeholder) placeholder.style.display = 'none';
        if (removeBtn) removeBtn.classList.add('active');
    }

    function clearPreview() {
        if (!previewImg) return;
        previewImg.src = '';
        previewImg.classList.remove('active');
        if (placeholder) placeholder.style.display = '';
        if (removeBtn) removeBtn.classList.remove('active');
        fileInput.value = '';
        if (photoField) photoField.value = '1'; // tandai hapus foto lama
    }

    fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;
        if (!file.type.startsWith('image/')) {
            showAlert('File harus berupa gambar (JPG, PNG, WEBP, GIF).', 'danger');
            fileInput.value = '';
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            showAlert('Ukuran file melebihi 5 MB.', 'danger');
            fileInput.value = '';
            return;
        }
        if (photoField) photoField.value = ''; // tidak hapus foto lama
        const reader = new FileReader();
        reader.onload = (ev) => showPreview(ev.target.result);
        reader.readAsDataURL(file);
    });

    if (removeBtn) {
        removeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            clearPreview();
        });
    }

    // Drag-over styling
    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('drag-over');
    });
    dropzone.addEventListener('dragleave', () => dropzone.classList.remove('drag-over'));
    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            // Transfer file ke input
            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            fileInput.dispatchEvent(new Event('change'));
        }
    });
})();

/* ── 4. Lightbox (klik thumbnail foto) ────────────────────────── */
const lightbox      = document.getElementById('lightbox');
const lightboxImg   = document.getElementById('lightbox-img');
const lightboxClose = document.getElementById('lightbox-close');

document.querySelectorAll('[data-lightbox]').forEach(el => {
    el.addEventListener('click', () => {
        if (!lightbox || !lightboxImg) return;
        lightboxImg.src = el.dataset.lightbox;
        lightbox.classList.add('active');
    });
});

if (lightbox) {
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox || e.target === lightboxClose) {
            lightbox.classList.remove('active');
        }
    });
}
if (lightboxClose) {
    lightboxClose.addEventListener('click', () => lightbox.classList.remove('active'));
}
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && lightbox) lightbox.classList.remove('active');
});

/* ── 5. Konfirmasi Hapus ───────────────────────────────────────── */
document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', (e) => {
        const msg = el.dataset.confirm || 'Yakin ingin menghapus data ini?';
        if (!confirm(msg)) e.preventDefault();
    });
});

/* ── 6. Auto-submit filter saat select berubah ─────────────────── */
document.querySelectorAll('.auto-submit').forEach(el => {
    el.addEventListener('change', () => {
        el.closest('form')?.submit();
    });
});

/* ── 7. Inline attendance auto-submit ──────────────────────────── */
document.querySelectorAll('.attendance-select').forEach(sel => {
    sel.addEventListener('change', () => {
        sel.closest('form')?.submit();
    });
});

/* ── 8. Flash alert auto-dismiss ───────────────────────────────── */
document.querySelectorAll('.alert[data-autohide]').forEach(el => {
    setTimeout(() => {
        el.style.transition = 'opacity 0.5s';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 500);
    }, 4000);
});

/* ── 9. Helper: tampilkan flash di atas halaman ─────────────────── */
function showAlert(msg, type = 'danger') {
    const existing = document.querySelector('.js-alert');
    if (existing) existing.remove();
    const div = document.createElement('div');
    div.className = `alert alert-${type} js-alert`;
    div.setAttribute('data-autohide', '1');
    div.textContent = msg;
    const wrapper = document.querySelector('.page-wrapper');
    if (wrapper) wrapper.prepend(div);
    // retrigger auto-dismiss
    setTimeout(() => {
        div.style.transition = 'opacity 0.5s';
        div.style.opacity = '0';
        setTimeout(() => div.remove(), 500);
    }, 4000);
}