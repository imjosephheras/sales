<div id="q_labor_table" style="display:none; margin-top:20px;">

    <div class="section-title">Labor Calculation</div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Role</th>
                <th>Workers</th>
                <th>Hours</th>
                <th>Rate</th>
                <th>Days</th>
            </tr>
        </thead>
        <tbody>

            <!-- SUPERVISOR -->
            <tr>
                <td>
                    <input type="text"
                           name="labor_role[]"
                           value="Supervisor"
                           class="form-control"
                           readonly>
                    <span class="print-only">Supervisor</span>
                </td>

                <td>
                    <select name="labor_workers[]" class="form-control">
                        <?php for ($i = 0; $i <= 10; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                    <span class="print-only"></span>
                </td>

                <td>
                    <select name="labor_hours[]" class="form-control">
                        <?php for ($i = 1; $i <= 24; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                    <span class="print-only"></span>
                </td>

                <td>
                    <input type="number"
                           name="labor_rate[]"
                           class="form-control"
                           step="0.01">
                    <span class="print-only"></span>
                </td>

                <td>
                    <select name="labor_days[]" class="form-control">
                        <?php for ($i = 1; $i <= 30; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                    <span class="print-only"></span>
                </td>
            </tr>

            <!-- WORKER -->
            <tr>
                <td>
                    <input type="text"
                           name="labor_role[]"
                           value="Worker"
                           class="form-control"
                           readonly>
                    <span class="print-only">Worker</span>
                </td>

                <td>
                    <select name="labor_workers[]" class="form-control">
                        <?php for ($i = 0; $i <= 10; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                    <span class="print-only"></span>
                </td>

                <td>
                    <select name="labor_hours[]" class="form-control">
                        <?php for ($i = 1; $i <= 24; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                    <span class="print-only"></span>
                </td>

                <td>
                    <input type="number"
                           name="labor_rate[]"
                           class="form-control"
                           step="0.01">
                    <span class="print-only"></span>
                </td>

                <td>
                    <select name="labor_days[]" class="form-control">
                        <?php for ($i = 1; $i <= 30; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                    <span class="print-only"></span>
                </td>
            </tr>

        </tbody>
    </table>

</div>
