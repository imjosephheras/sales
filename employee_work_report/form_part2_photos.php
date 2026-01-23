<?php $t = $t ?? []; ?>

<div class="section-title">
  <?= $t["wr_sec2_title"] ?? "SECTION 2: BEFORE & AFTER PHOTOS" ?>
</div>

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
</style>

<div id="photo-rows-container"></div>

<button type="button" class="add-row-btn" id="add-row-btn">
  <?= $t["wr_add_row"] ?? "+ Add Before & After" ?>
</button>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById("photo-rows-container");
    const addBtn = document.getElementById("add-row-btn");

    const labels = {
        before: "<?= $t['wr_before'] ?? 'BEFORE' ?>",
        after: "<?= $t['wr_after'] ?? 'AFTER' ?>",
        plus: "<?= $t['wr_plus'] ?? '+' ?>"
    };

    function createPhotoBox(type) {
        const box = document.createElement("div");
        box.className = "photo-box";

        const input = document.createElement("input");
        input.type = "file";
        input.accept = "image/*";
        input.capture = "environment";
        input.name = type + "[]";
        input.style.display = "none";

        const placeholder = document.createElement("div");
        placeholder.textContent = labels.plus;
        placeholder.style.fontSize = "40px";
        placeholder.style.color = "#999";

        box.appendChild(placeholder);
        box.appendChild(input);

        box.addEventListener("click", () => input.click());

        input.addEventListener("change", () => {
            const file = input.files[0];
            if (!file) return;

            placeholder.remove();

            const img = document.createElement("img");
            img.src = URL.createObjectURL(file);
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
});
</script>
