<div id="q_hood_vent">

  <div class="section-title">Hood Vent Table</div>

  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Qty</th>
        <th>Size</th>
        <th>Price ($)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>
          <select name="hood_qty[]" class="form-control">
            <?php for ($i = 1; $i <= 100; $i++): ?>
              <option value="<?= $i ?>"><?= $i ?></option>
            <?php endfor; ?>
          </select>
        </td>

        <td>
          <select name="hood_size[]" class="form-control">
            <option value="Small">Small</option>
            <option value="Medium">Medium</option>
            <option value="Large">Large</option>
          </select>
        </td>

        <td>
          <select name="hood_price[]" class="form-control">
            <?php for ($p = 0; $p <= 1000; $p += 50): ?>
              <option value="<?= $p ?>"><?= number_format($p, 2) ?></option>
            <?php endfor; ?>
          </select>
        </td>
      </tr>
    </tbody>
  </table>

</div>
