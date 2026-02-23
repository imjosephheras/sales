<?php $t = $t ?? []; ?>

<!-- ================================ -->
<!-- Section: Notes                   -->
<!-- ================================ -->

<!-- Title -->
<div class="question-block" id="note_title_block">
  <label for="note_title" class="question-label">
    <?= $t["wr_note_title_label"] ?? "Title" ?>
  </label>
  <input
      type="text"
      name="note_title"
      id="note_title"
      class="form-control"
      placeholder="<?= $t["wr_note_title_placeholder"] ?? "Enter a title for the note" ?>">
</div>

<!-- Description -->
<div class="question-block" id="note_description_block">
  <label for="note_description" class="question-label">
    <?= $t["wr_note_description_label"] ?? "Description" ?>
  </label>
  <textarea
      name="note_description"
      id="note_description"
      class="form-control"
      rows="4"
      placeholder="<?= $t["wr_note_description_placeholder"] ?? "Enter additional details or observations" ?>"></textarea>
</div>
