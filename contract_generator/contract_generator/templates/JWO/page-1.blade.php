<!-- ============================================
     PAGE 1: Header, Client Info, Services, Totals, Scope + Footer A
     ============================================ -->
<div class="page page-1">
    <table class="page-table">
        <tr>
            <td class="page-content">

                <!-- TOP SPACER (replicate original 4.5cm top margin) -->
                <div class="page-1-spacer"></div>

                <!-- HEADER -->
                <?php
                $dept = strtolower(trim($data['Service_Type'] ?? ''));
                if (strpos($dept, 'hospitality') !== false) {
                    $logo_file = __DIR__ . '/../../../../Images/phospitality.png';
                } else {
                    $logo_file = __DIR__ . '/../../../../Images/pfacility.png';
                }
                $logo_base64 = '';
                if (file_exists($logo_file)) {
                    $logo_base64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logo_file));
                }
                ?>
                <div class="header">
                    <div class="header-left">
                        <?php if ($logo_base64): ?>
                        <img class="header-logo" src="<?php echo $logo_base64; ?>" alt="Prime Facility Services Group">
                        <?php endif; ?>
                    </div>
                    <div class="header-right">
                        <div class="doc-title">JOB WORK ORDER</div>
                        <div class="doc-subtitle">"The best services in the industry or nothing at all"</div>
                    </div>
                </div>

                <!-- CLIENT & WORK INFO - 7 COLUMNS INVISIBLE -->
                <?php
                $client_name = htmlspecialchars($data['client_name'] ?? $data['Client_Name'] ?? 'N/A');
                $client_title = htmlspecialchars($data['Client_Title'] ?? '');
                $client_email = htmlspecialchars($data['Email'] ?? 'N/A');
                $client_phone = htmlspecialchars($data['Number_Phone'] ?? 'N/A');

                $company_name = htmlspecialchars($data['Company_Name'] ?? 'N/A');
                $company_address = htmlspecialchars($data['Company_Address'] ?? 'N/A');

                $seller = htmlspecialchars($data['Seller'] ?? 'N/A');
                $work_date = date('m/d/Y');
                $department = htmlspecialchars($data['Service_Type'] ?? 'N/A');

                $freq_map = [
                    '15' => 'Net 15',
                    '30' => 'Net 30',
                    '50_deposit' => '50% Deposit',
                    'completion' => 'Upon Completion'
                ];
                $payment_terms = $freq_map[$data['Invoice_Frequency'] ?? ''] ?? 'Upon Completion';

                $wo_number = htmlspecialchars($data['docnum'] ?? '');
                ?>
                <table class="info-columns">
                    <tr>
                        <td class="col-header">BILL TO</td>
                        <td class="col-header">WORK SITE</td>
                        <td class="col-header">SALES PERSON</td>
                        <td class="col-header">WORK DATE</td>
                        <td class="col-header">DEPARTMENT</td>
                        <td class="col-header">PAYMENT TERMS</td>
                        <td class="col-header">W.O. NO.</td>
                    </tr>
                    <tr>
                        <td class="col-content">
                            <?php echo $client_name; ?><br>
                            <?php if ($client_title): ?><?php echo $client_title; ?><br><?php endif; ?>
                            <?php echo $client_email; ?><br>
                            <?php echo $client_phone; ?>
                        </td>
                        <td class="col-content">
                            <?php echo $company_name; ?><br>
                            <?php echo $company_address; ?>
                        </td>
                        <td class="col-content">
                            <?php echo $seller; ?>
                        </td>
                        <td class="col-content">
                            <?php echo $work_date; ?>
                        </td>
                        <td class="col-content">
                            <?php echo $department; ?>
                        </td>
                        <td class="col-content">
                            <?php echo $payment_terms; ?>
                        </td>
                        <td class="col-content">
                            <?php echo $wo_number ?: '-'; ?>
                        </td>
                    </tr>
                </table>

                <!-- SERVICES TABLE -->
                <table class="services-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">TYPE OF SERVICES</th>
                            <th style="width: 12%;">SERVICE TIME</th>
                            <th style="width: 12%;">FREQUENCY</th>
                            <th style="width: 36%;">SERVICE DESCRIPTION</th>
                            <th style="width: 15%;">SUBTOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allServiceRows as $row): ?>
                        <tr>
                            <td class="service-desc"><?php echo htmlspecialchars($row['type']); ?></td>
                            <td><?php echo htmlspecialchars($row['time']); ?></td>
                            <td><?php echo htmlspecialchars($row['freq']); ?></td>
                            <td class="service-desc"><?php echo htmlspecialchars($row['desc']); ?></td>
                            <td class="amount" style="text-align: right;">$<?php echo number_format($row['subtotal'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- TOTALS TABLE -->
                <table class="totals-table">
                    <tr>
                        <td class="label-cell">TOTAL</td>
                        <td class="value-cell">$<?php echo number_format($subtotal, 2); ?></td>
                    </tr>
                    <tr>
                        <td class="label-cell">TAXES (8.25%)</td>
                        <td class="value-cell">$<?php echo number_format($taxes, 2); ?></td>
                    </tr>
                    <tr>
                        <td class="label-cell">GRAND TOTAL</td>
                        <td class="value-cell">$<?php echo number_format($grand_total, 2); ?></td>
                    </tr>
                </table>

                <!-- SCOPE OF WORK -->
                <div class="scope-section">
                    <div class="scope-header">SCOPE OF WORK - <?php echo strtoupper(htmlspecialchars($data['Requested_Service'] ?? 'SERVICE DESCRIPTION')); ?></div>
                    <div class="scope-content">
                        <h4>WORK TO BE PERFORMED:</h4>
                        <?php if (!empty($scopeOfWorkTasks)): ?>
                            <ul>
                            <?php foreach ($scopeOfWorkTasks as $task): ?>
                                <li><?php echo htmlspecialchars($task); ?></li>
                            <?php endforeach; ?>
                            </ul>
                        <?php elseif (!empty($data['Scope_Of_Work']) && is_array($data['Scope_Of_Work'])): ?>
                            <ul>
                            <?php foreach ($data['Scope_Of_Work'] as $task): ?>
                                <li><?php echo htmlspecialchars($task); ?></li>
                            <?php endforeach; ?>
                            </ul>
                        <?php elseif (!empty($data['scope_of_work'])): ?>
                            <?php echo $data['scope_of_work']; ?>
                        <?php else: ?>
                            <ul>
                                <li>Professional service as per client requirements</li>
                                <li>All work performed to industry standards with quality assurance</li>
                                <li>Final inspection to ensure satisfactory completion</li>
                            </ul>
                        <?php endif; ?>

                        <?php if (!empty($data['Additional_Comments'])): ?>
                            <h4>ADDITIONAL NOTES:</h4>
                            <p><?php echo nl2br(htmlspecialchars($data['Additional_Comments'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

            </td>
        </tr>
        <tr>
            <td class="page-footer-cell">
                <!-- FOOTER A: Two-tone split (same as original) -->
                <div class="footer-a">
                    <div class="footer-a-top">
                        PRIME FACILITY SERVICES GROUP, INC.
                    </div>
                    <div class="footer-a-bottom">
                        <strong>8303 Westglen Dr - Houston, TX 77063 - Phone 713-338-2553 - Fax 713-574-3065</strong><br>
                        <a href="http://www.primefacilityservicesgroup.com">www.primefacilityservicesgroup.com</a>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>
