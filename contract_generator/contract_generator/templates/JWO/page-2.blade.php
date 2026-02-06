<!-- ============================================
     PAGE 2: Terms and Conditions + Acceptance/Signatures + Footer B
     ============================================ -->
<div class="page page-2">
    <table class="page-table">
        <tr>
            <td class="page-content">

                <!-- TERMS AND CONDITIONS -->
                <div class="terms-section">

                    <div class="terms-main-title">TERMS AND CONDITIONS</div>

                    <div class="term-box">
                        <div class="term-title">1. SERVICE LIMITATIONS</div>
                        <ul>
                            <li>Work will be performed during approved service windows.</li>
                            <li>Additional charges may apply for emergency service requests.</li>
                            <li>Separate scheduling is required for areas containing wood-burning equipment.</li>
                        </ul>
                    </div>

                    <?php
                    $requested_service = strtolower($data['Requested_Service'] ?? '');
                    $is_kitchen_service = (strpos($requested_service, 'kitchen') !== false || strpos($requested_service, 'hood') !== false);

                    if ($is_kitchen_service):
                    ?>
                    <div class="term-box">
                        <div class="term-title">2. AREA PREPARATION</div>
                        <ul>
                            <li>All cooking equipment must be turned off at least two (2) hours before service.</li>
                        </ul>
                    </div>

                    <div class="term-box">
                        <div class="term-title">3. KITCHEN PREPARATION</div>
                        <p>The Client must ensure that the kitchen is ready for service, including:</p>
                        <ul>
                            <li>Turning off all kitchen equipment and allowing it to cool completely</li>
                            <li>Removing food, utensils, and personal items from work surfaces</li>
                            <li>Keeping access areas clear for the cleaning crew</li>
                        </ul>
                        <p>Additional time caused by lack of preparation may be billed at <strong>$30.00 USD per hour</strong>.</p>
                    </div>
                    <?php endif; ?>

                    <div class="term-box">
                        <div class="term-title"><?php echo $is_kitchen_service ? '4' : '2'; ?>. PROPOSAL VALIDITY PERIOD</div>
                        <p>The proposal issued for this Work Order will be valid for fourteen (14) days from the date of issuance. Prime Facility Services Group may revise pricing, scope, or terms if approval is not received within this period.</p>
                        <p>If actual site conditions differ from those observed during the initial inspection, a revised proposal may be issued.</p>
                    </div>

                    <div class="term-box">
                        <div class="term-title"><?php echo $is_kitchen_service ? '5' : '3'; ?>. CANCELLATIONS</div>
                        <p>Cancellations made with less than twenty-four (24) hours' notice will incur a charge equal to one hundred percent (100%) of the minimum scheduled labor.</p>
                        <p>Cancellations made with more than twenty-four (24) hours' notice will not incur charges unless otherwise specified in the applicable price list.</p>
                    </div>

                    <div class="term-box">
                        <div class="term-title"><?php echo $is_kitchen_service ? '6' : '4'; ?>. RESCHEDULING</div>
                        <p>Rescheduling requests must be submitted at least twenty-four (24) hours in advance. Requests made within 24 hours may incur a fee of up to the total scheduled labor and are subject to personnel and equipment availability.</p>
                        <p>Availability for rescheduled dates or times is not guaranteed.</p>
                    </div>

                    <div class="term-box">
                        <div class="term-title"><?php echo $is_kitchen_service ? '7' : '5'; ?>. LACK OF ACCESS</div>
                        <p>If personnel arrive on site and are unable to begin work due to lack of access, incomplete area preparation, or delays caused by the Client, the situation will be treated as a same-day cancellation and the corresponding charges will apply.</p>
                    </div>

                    <div class="term-box">
                        <div class="term-title"><?php echo $is_kitchen_service ? '8' : '6'; ?>. WEATHER OR SAFETY DELAYS</div>
                        <p>If work cannot be safely performed due to weather conditions, hazardous environments, or other safety-related circumstances beyond the company's control, the service will be rescheduled to the next available date.</p>
                        <p>No penalties will apply; however, labor or material costs may be adjusted if conditions change significantly.</p>
                    </div>

                    <div class="term-box">
                        <div class="term-title"><?php echo $is_kitchen_service ? '9' : '7'; ?>. POST-SERVICE REQUIREMENTS</div>
                        <ul>
                            <li>Kitchen management must verify completion.</li>
                            <li>Any concerns must be reported within twenty-four (24) hours.</li>
                            <li>Recommended maintenance schedules must be followed.</li>
                        </ul>
                    </div>

                    <div class="term-box">
                        <div class="term-title"><?php echo $is_kitchen_service ? '10' : '8'; ?>. SITE ACCESS AND SECURITY COORDINATION</div>
                        <ul>
                            <li>The Client must notify on-site security personnel or building management in advance that services will be performed.</li>
                            <li>If the service requires access to rooftops, ceilings, ventilation systems, or other restricted areas, the Client must ensure safe and full access.</li>
                            <li>The Client must provide clear instructions and prior authorization to security or access-control personnel to allow entry for the service team.</li>
                        </ul>
                    </div>

                </div>

            </td>
        </tr>
        <tr>
            <td class="page-footer-cell">

                <!-- ACCEPTANCE / SIGNATURES (anchored to bottom) -->
                <div class="acceptance-header">ACCEPTANCE / SIGNATURES</div>

                <div class="final-section">
                    <div class="contact-column">
                        <div class="contact-title">PLEASE SEND TWO COPIES OF YOUR WORK ORDER:</div>
                        <div class="contact-info" style="margin-top: 5px;">
                            Enter this order in accordance with the prices, terms, and<br>
                            specifications listed above.
                        </div>
                        <br>
                        <div class="contact-subtitle">&#9993; &nbsp;SEND ALL CORRESPONDENCE TO:</div>
                        <div class="contact-info" style="margin-top: 5px;">
                            <strong>Prime Facility Services Group, Inc.</strong><br>
                            8303 Westglen Drive<br>
                            Houston, TX 77063<br><br>
                            customerservice@primefacilityservicesgroup.com<br>
                            <span class="contact-icon">&#9742;</span> (713) 338-2553 Phone<br>
                            <span class="contact-icon">&#9742;</span> (713) 574-3065 Fax
                        </div>
                    </div>
                    <div class="signature-column">
                        <div class="signature-box">
                            <div class="sig-label">AUTHORIZED BY:</div>
                            <div class="sig-line">Signature &amp; Date</div>
                        </div>
                        <div class="signature-box">
                            <div class="sig-label">PRINT NAME:</div>
                            <div class="sig-line">Name &amp; Title</div>
                        </div>
                    </div>
                </div>

                <!-- FOOTER B: Single-line professional footer (different from Footer A) -->
                <div class="footer-b">
                    <span class="footer-b-company">Prime Facility Services Group, Inc.</span>
                    &bull; This document constitutes a binding agreement upon signature by both parties &bull;
                    <span class="footer-b-url">www.primefacilityservicesgroup.com</span>
                </div>

            </td>
        </tr>
    </table>
</div>
