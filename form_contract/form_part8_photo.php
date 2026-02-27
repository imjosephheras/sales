<!-- ====================================== -->
<!-- Section 8: Photos -->
<!-- ====================================== -->

<div class="question-block" id="q29">
  <label class="question-label">
    <?= ($lang=='en')
        ? "29. Upload or take photos"
        : "29. Subir o tomar fotos"; ?>
  </label>

  <div class="photo-upload-section">
    <div class="upload-instructions">
        <p><?= ($lang=='en') ? "Click the button below to upload photos related to the service request" : "Haz clic en el boton de abajo para subir fotos relacionadas con la solicitud de servicio"; ?></p>
    </div>

    <div class="photo-upload-container">
        <input
            type="file"
            id="photo-input"
            name="photos[]"
            accept="image/*"
            multiple
            style="display: none;"
        >

        <button type="button" class="add-photo-btn" id="add-photo-box">
            <span class="plus-icon">+</span>
            <?= ($lang=='en') ? "Add Photos" : "Agregar Fotos"; ?>
        </button>

        <div class="photo-preview-grid" id="photo-container">
            <!-- Las fotos se mostraran aqui -->
        </div>
    </div>
  </div>
</div>

<!-- Photo Lightbox Modal -->
<div class="photo-lightbox-overlay" id="photoLightbox">
    <button type="button" class="lightbox-close" id="lightboxClose">&times;</button>
    <button type="button" class="lightbox-nav lightbox-prev" id="lightboxPrev">&#10094;</button>
    <div class="lightbox-content">
        <img src="" alt="" id="lightboxImage">
        <div class="lightbox-info" id="lightboxInfo">
            <span class="lightbox-filename" id="lightboxFilename"></span>
            <span class="lightbox-counter" id="lightboxCounter"></span>
        </div>
    </div>
    <button type="button" class="lightbox-nav lightbox-next" id="lightboxNext">&#10095;</button>
</div>

<style>
.photo-upload-section {
    margin-top: 15px;
}

.upload-instructions {
    margin-bottom: 20px;
    padding: 15px;
    background: #f0f7ff;
    border-left: 4px solid #001f54;
    border-radius: 8px;
}

.upload-instructions p {
    margin: 0;
    color: #001f54;
    font-weight: 500;
    font-size: 14px;
}

.photo-upload-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.add-photo-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 15px 30px;
    background: linear-gradient(135deg, #001f54 0%, #003080 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,31,84,0.2);
    width: fit-content;
}

.add-photo-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,31,84,0.3);
    background: linear-gradient(135deg, #003080 0%, #004099 100%);
}

.plus-icon {
    font-size: 24px;
    font-weight: bold;
    line-height: 1;
}

.photo-preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
}

.photo-card {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    background: white;
}

.photo-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.photo-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    display: block;
    cursor: pointer;
}

.photo-card-actions {
    position: absolute;
    top: 10px;
    right: 10px;
    display: flex;
    gap: 8px;
}

.photo-card-btn {
    background: rgba(255,255,255,0.9);
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.photo-card-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.photo-card-btn.preview {
    color: #001f54;
}

.photo-card-btn.preview:hover {
    background: #001f54;
    color: white;
}

.photo-card-btn.delete {
    color: #dc3545;
}

.photo-card-btn.delete:hover {
    background: #dc3545;
    color: white;
}

.photo-info {
    padding: 12px;
    background: white;
}

.photo-filename {
    font-size: 13px;
    color: #1a202c;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.photo-size {
    font-size: 11px;
    color: #718096;
    margin-top: 4px;
}

/* ===== LIGHTBOX STYLES ===== */
.photo-lightbox-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.92);
    z-index: 10002;
    align-items: center;
    justify-content: center;
    animation: lightboxFadeIn 0.25s ease;
}

.photo-lightbox-overlay.active {
    display: flex;
}

@keyframes lightboxFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.lightbox-close {
    position: absolute;
    top: 20px;
    right: 28px;
    background: none;
    border: none;
    color: #fff;
    font-size: 40px;
    cursor: pointer;
    z-index: 10003;
    line-height: 1;
    transition: color 0.2s ease, transform 0.2s ease;
    padding: 0 8px;
}

.lightbox-close:hover {
    color: #ff6b6b;
    transform: scale(1.15);
}

.lightbox-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255,255,255,0.12);
    border: none;
    color: #fff;
    font-size: 30px;
    width: 52px;
    height: 52px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s ease, transform 0.2s ease;
    z-index: 10003;
}

.lightbox-nav:hover {
    background: rgba(255,255,255,0.25);
    transform: translateY(-50%) scale(1.1);
}

.lightbox-prev {
    left: 20px;
}

.lightbox-next {
    right: 20px;
}

.lightbox-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    max-width: 90vw;
    max-height: 90vh;
}

.lightbox-content img {
    max-width: 85vw;
    max-height: 80vh;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.5);
    animation: lightboxImgIn 0.2s ease;
}

@keyframes lightboxImgIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}

.lightbox-info {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-top: 16px;
    padding: 8px 20px;
    background: rgba(255,255,255,0.1);
    border-radius: 20px;
    min-width: 200px;
}

.lightbox-filename {
    color: #fff;
    font-size: 14px;
    font-weight: 500;
    max-width: 300px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.lightbox-counter {
    color: rgba(255,255,255,0.7);
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
}

@media (max-width: 600px) {
    .lightbox-nav {
        width: 40px;
        height: 40px;
        font-size: 22px;
    }
    .lightbox-prev { left: 8px; }
    .lightbox-next { right: 8px; }
    .lightbox-close { top: 12px; right: 16px; font-size: 32px; }
    .lightbox-content img {
        max-width: 95vw;
        max-height: 75vh;
    }
}
</style>

<script>
(function() {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPhotoUpload);
    } else {
        initPhotoUpload();
    }

    function initPhotoUpload() {
        const addPhotoBtn = document.getElementById("add-photo-box");
        const photoInput = document.getElementById("photo-input");
        const photoContainer = document.getElementById("photo-container");

        if (!addPhotoBtn || !photoInput || !photoContainer) {
            console.error("Photo upload elements not found");
            return;
        }

        let photoFiles = new DataTransfer();

        // === LIGHTBOX SETUP ===
        const lightbox = document.getElementById("photoLightbox");
        const lightboxImg = document.getElementById("lightboxImage");
        const lightboxFilename = document.getElementById("lightboxFilename");
        const lightboxCounter = document.getElementById("lightboxCounter");
        const lightboxClose = document.getElementById("lightboxClose");
        const lightboxPrev = document.getElementById("lightboxPrev");
        const lightboxNext = document.getElementById("lightboxNext");
        let currentLightboxIndex = 0;

        function getPhotoCards() {
            return Array.from(photoContainer.querySelectorAll('.photo-card'));
        }

        function openLightbox(index) {
            const cards = getPhotoCards();
            if (cards.length === 0) return;
            currentLightboxIndex = Math.max(0, Math.min(index, cards.length - 1));
            updateLightboxImage();
            lightbox.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            lightbox.classList.remove('active');
            document.body.style.overflow = '';
        }

        function updateLightboxImage() {
            const cards = getPhotoCards();
            if (cards.length === 0) { closeLightbox(); return; }

            const card = cards[currentLightboxIndex];
            const img = card.querySelector('img');
            const filenameEl = card.querySelector('.photo-filename');

            lightboxImg.src = img.src;
            lightboxImg.alt = img.alt || '';
            lightboxFilename.textContent = filenameEl ? filenameEl.textContent : '';
            lightboxCounter.textContent = (currentLightboxIndex + 1) + ' / ' + cards.length;

            lightboxPrev.style.display = cards.length <= 1 ? 'none' : 'flex';
            lightboxNext.style.display = cards.length <= 1 ? 'none' : 'flex';
        }

        function lightboxNavigate(direction) {
            const cards = getPhotoCards();
            if (cards.length === 0) return;
            currentLightboxIndex = (currentLightboxIndex + direction + cards.length) % cards.length;
            updateLightboxImage();
        }

        lightboxClose.addEventListener('click', closeLightbox);
        lightboxPrev.addEventListener('click', function() { lightboxNavigate(-1); });
        lightboxNext.addEventListener('click', function() { lightboxNavigate(1); });

        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox) closeLightbox();
        });

        document.addEventListener('keydown', function(e) {
            if (!lightbox.classList.contains('active')) return;
            if (e.key === 'Escape') closeLightbox();
            else if (e.key === 'ArrowLeft') lightboxNavigate(-1);
            else if (e.key === 'ArrowRight') lightboxNavigate(1);
        });

        // Expose openLightbox globally for server-loaded photos
        window.openPhotoLightbox = openLightbox;

        // === PHOTO UPLOAD ===
        addPhotoBtn.addEventListener("click", (e) => {
            e.preventDefault();
            e.stopPropagation();
            photoInput.click();
        });

        photoInput.addEventListener("change", (e) => {
            const files = Array.from(e.target.files);

            files.forEach(file => {
                if (file.type.startsWith('image/')) {
                    photoFiles.items.add(file);
                    addPhotoPreview(file);
                }
            });

            e.target.value = '';
            photoInput.files = photoFiles.files;
        });

        function addPhotoPreview(file) {
            const reader = new FileReader();

            reader.onload = (e) => {
                const photoCard = document.createElement("div");
                photoCard.className = "photo-card";
                photoCard.dataset.filename = file.name;

                const safeFilename = file.name.replace(/'/g, "\\'");

                photoCard.innerHTML = `
                    <img src="${e.target.result}" alt="${safeFilename}">
                    <div class="photo-card-actions">
                        <button type="button" class="photo-card-btn preview" title="Preview">
                            &#128269;
                        </button>
                        <button type="button" class="photo-card-btn delete" data-filename="${safeFilename}">
                            &#128465;
                        </button>
                    </div>
                    <div class="photo-info">
                        <div class="photo-filename" title="${safeFilename}">${file.name}</div>
                        <div class="photo-size">${formatFileSize(file.size)}</div>
                    </div>
                `;

                // Preview button opens lightbox
                const previewBtn = photoCard.querySelector('.photo-card-btn.preview');
                previewBtn.addEventListener('click', function(ev) {
                    ev.stopPropagation();
                    const cards = getPhotoCards();
                    const idx = cards.indexOf(photoCard);
                    openLightbox(idx >= 0 ? idx : 0);
                });

                // Clicking image also opens lightbox
                const imgEl = photoCard.querySelector('img');
                imgEl.addEventListener('click', function() {
                    const cards = getPhotoCards();
                    const idx = cards.indexOf(photoCard);
                    openLightbox(idx >= 0 ? idx : 0);
                });

                // Delete button
                const deleteBtn = photoCard.querySelector('.photo-card-btn.delete');
                deleteBtn.addEventListener('click', function() {
                    removePhoto(this);
                });

                photoContainer.appendChild(photoCard);
            };

            reader.readAsDataURL(file);
        }

        function removePhoto(button) {
            const filename = button.dataset.filename;

            const photoCard = button.closest('.photo-card');
            if (photoCard) {
                photoCard.remove();
            }

            const newFileList = new DataTransfer();
            Array.from(photoFiles.files).forEach(file => {
                if (file.name !== filename) {
                    newFileList.items.add(file);
                }
            });
            photoFiles = newFileList;
            photoInput.files = photoFiles.files;
        }

        function formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(1) + ' MB';
        }
    }
})();
</script>