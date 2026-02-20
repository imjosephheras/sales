<?php $t = $t ?? []; ?>

<style>
.photo-row {
    display: flex;
    justify-content: center;
    gap: 40px;
    margin-bottom: 25px;
}

.photo-column {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.photo-label {
    font-weight: 700;
    font-size: 14px;
    margin-bottom: 8px;
    letter-spacing: 0.5px;
    color: #333;
}

.photo-box {
    width: 180px;
    height: 220px;
    border: 2px dashed #999;
    border-radius: 10px;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    background: #fafafa;
}

.photo-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.delete-btn {
    position: absolute;
    top: 5px;
    right: 5px;
    background: rgba(0,0,0,0.6);
    color: white;
    font-weight: bold;
    width: 22px;
    height: 22px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 50%;
    cursor: pointer;
}

.add-row-btn {
    display: block;
    margin: 20px auto;
    background: #001f54;
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 20px;
    cursor: pointer;
    border: none;
}

/* BULK UPLOAD STYLES */
.bulk-upload-container {
    display: none;
    text-align: center;
    padding: 20px;
}

.bulk-upload-box {
    width: 100%;
    max-width: 400px;
    min-height: 200px;
    border: 3px dashed #999;
    border-radius: 15px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    margin: 0 auto 20px;
    background: #fafafa;
    transition: all 0.3s ease;
}

.bulk-upload-box:hover {
    border-color: #001f54;
    background: #f0f4ff;
}

.bulk-upload-box .upload-icon {
    font-size: 50px;
    color: #999;
    margin-bottom: 10px;
}

.bulk-upload-box .upload-text {
    font-size: 16px;
    color: #666;
    font-weight: 600;
}

.bulk-upload-box .upload-hint {
    font-size: 12px;
    color: #999;
    margin-top: 5px;
}

.bulk-photos-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    justify-content: center;
    margin-top: 20px;
}

.bulk-photo-item {
    width: 120px;
    height: 120px;
    border-radius: 10px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.bulk-photo-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.bulk-photo-item .delete-btn {
    position: absolute;
    top: 5px;
    right: 5px;
    background: rgba(0,0,0,0.7);
    color: white;
    font-weight: bold;
    width: 24px;
    height: 24px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 50%;
    cursor: pointer;
    font-size: 16px;
}

.bulk-photo-item .delete-btn:hover {
    background: #a30000;
}

.photos-count {
    font-size: 14px;
    color: #666;
    margin-top: 10px;
}

.compress-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    justify-content: center;
    align-items: center;
    z-index: 9999;
}
.compress-overlay.active {
    display: flex;
}
.compress-overlay-content {
    background: white;
    padding: 30px 40px;
    border-radius: 12px;
    text-align: center;
    font-family: Arial, sans-serif;
}
.compress-spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #a30000;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: compress-spin 1s linear infinite;
    margin: 0 auto 15px;
}
@keyframes compress-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<!-- BEFORE & AFTER MODE -->
<div id="before-after-container">
    <div id="photo-rows-container"></div>
    <button type="button" class="add-row-btn" id="add-row-btn">
      <?= $t["wr_add_row"] ?? "+ Add Before & After" ?>
    </button>
</div>

<!-- ALL PHOTOS MODE (BULK UPLOAD) -->
<div id="bulk-upload-container" class="bulk-upload-container">
    <div class="bulk-upload-box" id="bulk-upload-box">
        <div class="upload-icon">+</div>
        <div class="upload-text"><?= $t["wr_bulk_upload_text"] ?? "Click to add photos" ?></div>
        <div class="upload-hint"><?= $t["wr_bulk_upload_hint"] ?? "Select multiple photos at once" ?></div>
    </div>
    <input type="file" id="bulk-photo-input" name="all_photos[]" accept="image/*" multiple style="display:none;">
    <div class="bulk-photos-grid" id="bulk-photos-grid"></div>
    <div class="photos-count" id="photos-count"></div>
</div>

<!-- COMPRESS LOADING OVERLAY -->
<div id="compress-overlay" class="compress-overlay">
    <div class="compress-overlay-content">
        <div class="compress-spinner"></div>
        <div id="compress-status"><?= $t["wr_compressing"] ?? "Optimizing photos..." ?></div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById("photo-rows-container");
    const addBtn = document.getElementById("add-row-btn");
    const beforeAfterContainer = document.getElementById("before-after-container");
    const bulkUploadContainer = document.getElementById("bulk-upload-container");
    const bulkUploadBox = document.getElementById("bulk-upload-box");
    const bulkPhotoInput = document.getElementById("bulk-photo-input");
    const bulkPhotosGrid = document.getElementById("bulk-photos-grid");
    const photosCountEl = document.getElementById("photos-count");

    const labels = {
        before: "<?= $t['wr_before'] ?? 'Before' ?>",
        after: "<?= $t['wr_after'] ?? 'After' ?>",
        plus: "<?= $t['wr_plus'] ?? '+' ?>",
        photosSelected: "<?= $t['wr_photos_selected'] ?? 'photos selected' ?>",
        maxPhotosReached: "<?= $t['wr_max_photos_reached'] ?? 'Maximum 100 photos allowed' ?>"
    };

    // Store bulk uploaded files
    let bulkFiles = [];
    const MAX_PHOTOS = 100;

    // ==========================================
    // CLIENT-SIDE IMAGE COMPRESSION
    // ==========================================
    const COMPRESS_MAX_DIM = 1920;
    const COMPRESS_QUALITY = 0.7;

    const compressOverlay = document.getElementById("compress-overlay");
    const compressStatusEl = document.getElementById("compress-status");

    function showCompressOverlay(msg) {
        compressStatusEl.textContent = msg || "<?= $t['wr_compressing'] ?? 'Optimizing photos...' ?>";
        compressOverlay.classList.add("active");
    }

    function hideCompressOverlay() {
        compressOverlay.classList.remove("active");
    }

    function compressImageClient(file) {
        return new Promise(function(resolve) {
            if (!file.type.startsWith("image/")) {
                resolve(file);
                return;
            }

            var reader = new FileReader();
            reader.onload = function(e) {
                var img = new Image();
                img.onload = function() {
                    var canvas = document.createElement("canvas");
                    var width = img.width;
                    var height = img.height;

                    // Resize if exceeds max dimension
                    if (width > COMPRESS_MAX_DIM || height > COMPRESS_MAX_DIM) {
                        var ratio = Math.min(COMPRESS_MAX_DIM / width, COMPRESS_MAX_DIM / height);
                        width = Math.round(width * ratio);
                        height = Math.round(height * ratio);
                    }

                    canvas.width = width;
                    canvas.height = height;
                    var ctx = canvas.getContext("2d");
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob(function(blob) {
                        var baseName = file.name.replace(/\.[^.]+$/, "") + ".jpg";
                        var compressed = new File([blob], baseName, {
                            type: "image/jpeg",
                            lastModified: Date.now()
                        });
                        resolve(compressed);
                    }, "image/jpeg", COMPRESS_QUALITY);
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    // ==========================================
    // BEFORE & AFTER MODE FUNCTIONS
    // ==========================================
    function createPhotoBox(type) {
        const box = document.createElement("div");
        box.className = "photo-box";

        const input = document.createElement("input");
        input.type = "file";
        input.accept = "image/*";
        input.name = type + "[]";
        input.style.display = "none";

        const placeholder = document.createElement("div");
        placeholder.textContent = labels.plus;
        placeholder.style.fontSize = "40px";
        placeholder.style.color = "#999";

        box.appendChild(placeholder);
        box.appendChild(input);

        box.addEventListener("click", () => input.click());

        input.addEventListener("change", async () => {
            const file = input.files[0];
            if (!file) return;

            showCompressOverlay();
            const compressed = await compressImageClient(file);
            hideCompressOverlay();

            // Replace file in input with compressed version
            const dt = new DataTransfer();
            dt.items.add(compressed);
            input.files = dt.files;

            placeholder.remove();

            const img = document.createElement("img");
            img.src = URL.createObjectURL(compressed);
            box.appendChild(img);

            const del = document.createElement("div");
            del.className = "delete-btn";
            del.textContent = "×";

            del.onclick = (e) => {
                e.stopPropagation();
                input.value = "";
                img.remove();
                del.remove();
                box.appendChild(placeholder);
            };

            box.appendChild(del);
        });

        return box;
    }

    function addRow() {
        const row = document.createElement("div");
        row.className = "photo-row";

        // BEFORE
        const beforeCol = document.createElement("div");
        beforeCol.className = "photo-column";
        const beforeLabel = document.createElement("div");
        beforeLabel.className = "photo-label";
        beforeLabel.textContent = labels.before;
        beforeCol.appendChild(beforeLabel);
        beforeCol.appendChild(createPhotoBox("before"));

        // AFTER
        const afterCol = document.createElement("div");
        afterCol.className = "photo-column";
        const afterLabel = document.createElement("div");
        afterLabel.className = "photo-label";
        afterLabel.textContent = labels.after;
        afterCol.appendChild(afterLabel);
        afterCol.appendChild(createPhotoBox("after"));

        row.appendChild(beforeCol);
        row.appendChild(afterCol);
        container.appendChild(row);
    }

    // Fila inicial
    addRow();
    addBtn.addEventListener("click", addRow);

    // ==========================================
    // BULK UPLOAD MODE FUNCTIONS
    // ==========================================
    bulkUploadBox.addEventListener("click", () => bulkPhotoInput.click());

    bulkPhotoInput.addEventListener("change", async () => {
        const newFiles = Array.from(bulkPhotoInput.files);

        // Check if adding new files would exceed the limit
        const remainingSlots = MAX_PHOTOS - bulkFiles.length;
        if (remainingSlots <= 0) {
            alert(labels.maxPhotosReached);
            return;
        }

        // Only add files up to the limit
        const filesToAdd = newFiles.slice(0, remainingSlots);
        if (filesToAdd.length < newFiles.length) {
            alert(labels.maxPhotosReached + " (" + (newFiles.length - filesToAdd.length) + " photos not added)");
        }

        // Compress all selected photos before adding
        showCompressOverlay();
        const compressedFiles = [];
        for (let i = 0; i < filesToAdd.length; i++) {
            compressStatusEl.textContent = "<?= $t['wr_compressing_progress'] ?? 'Optimizing photo' ?> " + (i + 1) + " / " + filesToAdd.length + "...";
            compressedFiles.push(await compressImageClient(filesToAdd[i]));
        }
        hideCompressOverlay();

        bulkFiles = bulkFiles.concat(compressedFiles);
        renderBulkPhotos();
        updateFileInput();
    });

    function renderBulkPhotos() {
        bulkPhotosGrid.innerHTML = "";

        bulkFiles.forEach((file, index) => {
            const item = document.createElement("div");
            item.className = "bulk-photo-item";

            const img = document.createElement("img");
            img.src = URL.createObjectURL(file);
            item.appendChild(img);

            const del = document.createElement("div");
            del.className = "delete-btn";
            del.textContent = "×";
            del.onclick = () => {
                bulkFiles.splice(index, 1);
                renderBulkPhotos();
                updateFileInput();
            };
            item.appendChild(del);

            bulkPhotosGrid.appendChild(item);
        });

        // Update count
        if (bulkFiles.length > 0) {
            photosCountEl.textContent = bulkFiles.length + " / " + MAX_PHOTOS + " " + labels.photosSelected;
            if (bulkFiles.length >= MAX_PHOTOS) {
                photosCountEl.style.color = "#a30000";
                photosCountEl.style.fontWeight = "bold";
            } else {
                photosCountEl.style.color = "#666";
                photosCountEl.style.fontWeight = "normal";
            }
        } else {
            photosCountEl.textContent = "";
        }
    }

    function updateFileInput() {
        // Create a new DataTransfer to hold the files
        const dataTransfer = new DataTransfer();
        bulkFiles.forEach(file => dataTransfer.items.add(file));
        bulkPhotoInput.files = dataTransfer.files;
    }

    // ==========================================
    // MODE SWITCHING (called from index.php)
    // ==========================================
    window.switchPhotoMode = function(mode) {
        if (mode === "all_photos") {
            beforeAfterContainer.style.display = "none";
            bulkUploadContainer.style.display = "block";
        } else {
            beforeAfterContainer.style.display = "block";
            bulkUploadContainer.style.display = "none";
        }
    };
});
</script>
