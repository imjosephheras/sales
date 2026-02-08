<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temporary Staff Services Agreement</title>
    <style>
        @page {
            margin: 5cm 2cm 5.5cm 2cm;
        }

        @media print {
            @page {
                margin: 5cm 2cm 5.5cm 2cm;
            }
            body {
                padding: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5pt;
            color: #000;
            line-height: 1.4;
            padding: 0.5cm 1.5cm;
        }

        /* Header - fixed position to repeat on every page */
        .header-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
        }

        .header {
            display: table;
            width: 100%;
            margin-bottom: 0;
            border-bottom: 3px solid #CC0000;
        }

        .header-left {
            display: table-cell;
            width: 40%;
            vertical-align: middle;
            padding: 10px 0;
        }

        .header-right {
            display: table-cell;
            width: 60%;
            vertical-align: middle;
            text-align: left;
            padding: 10px 0 10px 15px;
        }

        .header-logo {
            max-height: 65px;
            width: auto;
        }

        .doc-title {
            color: #CC0000;
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .doc-subtitle {
            font-size: 16pt;
            font-weight: bold;
            color: #000;
        }

        /* Section titles */
        .section-number {
            color: #CC0000;
            font-weight: bold;
            font-size: 10pt;
            margin-top: 14px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .subsection {
            margin-left: 15px;
            margin-bottom: 6px;
        }

        .subsection p {
            margin-bottom: 5px;
            text-align: justify;
        }

        .subsection-label {
            font-weight: bold;
            margin-right: 4px;
        }

        .subsection ol {
            margin-left: 20px;
            margin-bottom: 6px;
            list-style-type: lower-alpha;
        }

        .subsection ol li {
            margin-bottom: 4px;
            text-align: justify;
        }

        .subsection ul {
            margin-left: 20px;
            margin-bottom: 6px;
        }

        .subsection ul li {
            margin-bottom: 4px;
            text-align: justify;
        }

        /* Preamble / parties */
        .preamble {
            margin-bottom: 12px;
            text-align: justify;
            line-height: 1.5;
        }

        .preamble strong {
            font-size: 10pt;
        }

        /* Page break utilities */
        .page-break {
            page-break-before: always;
        }

        .no-break {
            page-break-inside: avoid;
        }

        /* Signature blocks */
        .signature-block {
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .signature-block-title {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 12px;
        }

        .sig-table {
            width: 100%;
            border-collapse: collapse;
        }

        .sig-table td {
            width: 48%;
            vertical-align: top;
            padding: 5px 10px;
        }

        .sig-line-item {
            margin-bottom: 15px;
        }

        .sig-label {
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 3px;
        }

        .sig-underline {
            border-bottom: 1px solid #000;
            height: 25px;
            margin-top: 5px;
        }

        /* Appendix header */
        .appendix-title {
            color: #CC0000;
            font-weight: bold;
            font-size: 14pt;
            text-align: center;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .appendix-subtitle {
            font-weight: bold;
            font-size: 11pt;
            text-align: center;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .appendix-content {
            margin: 15px 10px;
            text-align: justify;
            line-height: 1.5;
        }

        /* Notice block */
        .notice-block {
            margin: 10px 15px;
            line-height: 1.5;
        }

        .notice-block strong {
            display: block;
            margin-bottom: 2px;
        }

        /* Footer */
        .footer-wrapper {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
        }

        .footer-top {
            background-color: #A30000;
            color: white;
            text-align: center;
            padding: 3px 10px;
            font-size: 7pt;
        }

        .footer-bottom {
            background-color: #CC0000;
            color: white;
            text-align: center;
            padding: 8px 10px;
            font-size: 8pt;
        }

        .footer-bottom a {
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <?php
    // Encode logo as base64 for DOMPDF compatibility
    $dept = strtolower(trim($data['Service_Type'] ?? ''));
    if (strpos($dept, 'hospitality') !== false) {
        $logo_file = __DIR__ . '/../../../Images/phospitality.png';
    } else {
        $logo_file = __DIR__ . '/../../../Images/pfacility.png';
    }
    $logo_base64 = '';
    if (file_exists($logo_file)) {
        $logo_base64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logo_file));
    }

    // Prepare client data
    $client_name = htmlspecialchars($data['client_name'] ?? $data['Client_Name'] ?? '');
    $client_title = htmlspecialchars($data['Client_Title'] ?? '');
    $company_name = htmlspecialchars($data['Company_Name'] ?? '');
    $company_address = htmlspecialchars($data['Company_Address'] ?? '');

    // Contract duration mapping
    $duration_map = [
        '6_months' => '6 Months',
        '1_year' => '1 Year',
        '1_5_years' => '1.5 Years (18 Months)',
        '2_years' => '2 Years',
        '3_years' => '3 Years',
        '4_years' => '4 Years',
        '5_years' => '5 Years',
        'not_applicable' => 'Not Applicable'
    ];
    $contract_duration = $duration_map[$data['Contract_Duration'] ?? ''] ?? htmlspecialchars($data['Contract_Duration'] ?? '___________');

    $inflation_adj = htmlspecialchars($data['inflationAdjustment'] ?? '3.1');
    $start_date = htmlspecialchars($data['startDateServices'] ?? '');
    ?>

    <!-- HEADER - fixed position, repeats on every page -->
    <div class="header-wrapper">
        <div class="header">
            <div class="header-left">
                <?php if ($logo_base64): ?>
                <img class="header-logo" src="<?php echo $logo_base64; ?>" alt="Prime Hospitality Services">
                <?php endif; ?>
            </div>
            <div class="header-right">
                <div class="doc-title">Temporary Staff</div>
                <div class="doc-subtitle">SERVICES AGREEMENT</div>
            </div>
        </div>
    </div>

    <!-- PREAMBLE -->
    <div class="preamble">
        <strong>PRIME HOSPITALITY SERVICES OF TEXAS</strong>, with main office at 8303 Westglen Dr., Houston, TX 77063 ("<strong>PRIME</strong>"), and <strong><?php echo $company_name ?: '(company name)'; ?></strong>, with its main office at <?php echo $company_address ?: '(address)'; ?> ("<strong>CLIENT</strong>"), accept the terms and conditions set forth in this Temporary Staffing Services Agreement (the "Agreement").
    </div>

    <!-- SECTION 1 -->
    <div class="section-number">1. Functions and responsibilities of Prime Hospitality Services of Texas (PRIME)</div>
    <div class="subsection">
        <p><span class="subsection-label">I.</span> PRIME commits to:</p>
        <ol>
            <li>Provide the staffing and operational support services described in Annex A, at the specified locations, providing qualified personnel in accordance with the positions, agreed rates and applicable industry standards.</li>
            <li>Pay the salaries of Prime Associates and provide them with the benefits that PRIME offers.</li>
            <li>To provide unemployment insurance and workers' compensation benefits and to handle unemployment and workers' compensation claims involving Prime Associates.</li>
            <li>Require Senior Associates to sign confidentiality agreements (in the form of Appendix C) before commencing their assignments for the CLIENT.</li>
        </ol>
    </div>

    <!-- SECTION 2 -->
    <div class="section-number">2. Client's Duties and Responsibilities</div>
    <div class="subsection">
        <p><span class="subsection-label">I.</span> THE CLIENT must:</p>
        <ol>
            <li>The CLIENT will provide general operational direction related solely to the results of the services provided by the Prime Associates at its facilities. The CLIENT will not exercise control over the means, methods, hiring, firing, discipline, wages, schedules, or working conditions of the Prime Associates, which will be the sole responsibility of PRIME.</li>
            <li>The CLIENT shall be responsible for maintaining and safeguarding its facilities, processes, systems, equipment, merchandise, confidential or proprietary information, cash, keys, negotiable instruments, or other valuables. Nothing in this Agreement shall be construed as authorizing the CLIENT to supervise the Principal Associates as an employer or to establish a joint employment or co-employment relationship.</li>
            <li>Provide key associates with a safe workplace and adequate safety information, training, and equipment regarding any hazardous substances or conditions to which they may be exposed in the workplace.</li>
            <li>Do not change the job duties of Prime Associates without the prior and express written approval of PRIME.</li>
            <li>Provide the Principal Associates with all the equipment, supplies and tools to perform daily tasks as scheduled by the CLIENT.</li>
            <li>Exclude Prime Associates from the CLIENT's benefit plans, policies and practices, and do not make any offers or promises related to Prime.</li>
        </ol>
    </div>

    <!-- PAGE 2 -->
    <div class="page-break"></div>

    <!-- SECTION 3 -->
    <div class="section-number">3. Payment Terms, Rates and Charges</div>
    <div class="subsection">
        <p><span class="subsection-label">I.</span> <strong>Billing and Payment Terms</strong><br>
        PRIME will invoice the CLIENT for services rendered in accordance with Annex A, at the rates specified therein. Billing will be periodic (weekly, monthly, or as otherwise agreed), based on the completion of the tasks described in the Scope of Work. Payment must be made within thirty (30) days of the invoice date.</p>

        <p><span class="subsection-label">II.</span> <strong>Fixed Price Services and Hourly Services</strong><br>
        The CLIENT acknowledges that, while certain services under this Agreement are provided at a fixed price agreed between the CLIENT and PRIME, not all work requested is subject to a single fee.</p>
        <ol>
            <li>Depending on the type of work required, some services may be billed hourly at the applicable rates mutually agreed by the CLIENT and PRIME for each category of work.</li>
            <li>PRIME will issue the corresponding invoices, clearly specifying whether the charges correspond to services provided under a fixed price agreement or to services billed hourly.</li>
        </ol>

        <p><span class="subsection-label">III.</span> <strong>Disputed Invoices</strong><br>
        In the event that any part of an invoice is disputed, the CLIENT shall promptly pay the undisputed part, while the parties resolve the disputed amount in good faith.</p>

        <p><span class="subsection-label">IV.</span> <strong>Overtime, Holidays and Weekends</strong><br>
        It is presumed that Prime Associates are not exempt from laws requiring additional pay for overtime, holiday work, or weekend work.</p>
        <ol>
            <li>PRIME will charge the CLIENT special surcharge rates for overtime work only when a Prime Associate, in working on an assignment for the CLIENT, considered on its own, is legally required to receive surcharge pay and the CLIENT has authorized, directed or permitted such work time.</li>
            <li>Surcharge hours will be billed at the same multiple of the regular billing rate that PRIME is legally obliged to apply to the Prime Associate's standard pay rate.</li>
            <li>By way of example, where federal law requires payment of time and a half for work exceeding forty (40) hours per week, the CLIENT will be billed time and a half plus the usual margin on the regular billing rate.</li>
        </ol>

        <p><span class="subsection-label">V.</span> <strong>Adjustments for Increases in Labor Costs</strong><br>
        In addition to the billing rates specified in Schedule A, CLIENT will pay PRIME the amount of all new or increased labor costs associated with Prime Associates that PRIME is legally obligated to pay, including, but not limited to, wages, benefits, payroll taxes, social program contributions, or charges tied to benefit levels, until the parties agree on new billing rates.</p>

        <p><span class="subsection-label">VI.</span> <strong>State Taxes</strong><br>
        The CLIENT acknowledges and accepts that Prime Hospitality Services of Texas will collect applicable state taxes on all goods and services provided under this Agreement.</p>
        <ol>
            <li>The rate and method of calculating such taxes will be determined in accordance with the applicable state tax laws and regulations.</li>
            <li>The CLIENT will be responsible for paying these taxes in addition to the fees agreed for the contracted services.</li>
            <li>PRIME will provide the CLIENT with the corresponding documentation and receipts for state tax charges.</li>
            <li>Any changes in state tax rates or regulations will be applied accordingly.</li>
        </ol>
    </div>

    <!-- PAGE 3 -->
    <div class="page-break"></div>

    <!-- SECTION 4 -->
    <div class="section-number">4. Confidential Information</div>
    <div class="subsection">
        <p><span class="subsection-label">I.</span> Neither party shall disclose confidential or proprietary information received from the other, including, but not limited to, methods, procedures, or any sensitive information related to the services provided. Both parties agree to maintain such information in strict confidence and not disclose it to third parties or use it for any purpose other than fulfilling this Agreement or as required by law. Prime Associates' access to such information shall not imply any knowledge, possession, or use thereof by Prime.</p>
    </div>

    <!-- SECTION 5 -->
    <div class="section-number">5. Full Agreement</div>
    <div class="subsection">
        <p><span class="subsection-label">I.</span> This Agreement and the attached Annexes constitute the entire agreement between the parties with respect to the subject matter hereof and supersede any prior agreements, whether oral or written. Any modification to this Agreement must be in writing and signed by a duly authorized representative of the party. There are no other understandings, obligations, representations, or warranties with respect to the subject matter of this Agreement, except as expressly stated herein. This Agreement shall supersede and shall not be modified or amended in any way by the printed terms of any invoice.</p>
    </div>

    <!-- SECTION 6 -->
    <div class="section-number">6. Resignation</div>
    <div class="subsection">
        <p><span class="subsection-label">I.</span> Failure by either party to strictly comply with any of the terms, conditions, and provisions of this Agreement shall not be deemed a waiver of future compliance, except for the party requiring such compliance hereunder. This shall not affect the other provisions of this Agreement in any way. All remedies available to the CLIENT shall be cumulative and shall be in addition to any other future remedies provided by law or equity.</p>
    </div>

    <!-- SECTION 7 -->
    <div class="section-number">7. Cooperation</div>
    <div class="subsection">
        <p><span class="subsection-label">I.</span> The parties agree to fully cooperate and assist each other in investigating and resolving any complaint, scam, fraud, action or proceeding that may be brought by or may involve Prime Associates.</p>
    </div>

    <!-- SECTION 8 -->
    <div class="section-number">8. Indemnification and limitation of liability</div>
    <div class="subsection">
        <p><span class="subsection-label">I.</span> To the extent permitted by law, PRIME will defend, indemnify and hold harmless the CLIENT from any and all claims, losses and liabilities arising directly from PRIME's performance of the services described in this Agreement, including damages caused by negligence or failure to meet agreed standards.</p>

        <p><span class="subsection-label">II.</span> To the extent permitted by law, the CLIENT shall defend, indemnify, and hold harmless PRIME and its parent company, subsidiaries, directors, officers, agents, representatives, and employees from and against any and all claims, losses, and liabilities (including reasonable attorneys' fees) caused by the CLIENT's breach of this Agreement, its failure to perform the obligations and responsibilities described in paragraph 2, or the negligence, gross negligence, or willful misconduct of the CLIENT or its officers, employees, or authorized agents in the performance of such obligations and responsibilities.</p>

        <p><span class="subsection-label">III.</span> Neither party shall be liable or obligated to indemnify the other for any incidental, consequential, exemplary, special, punitive or lost profit damages arising out of or in connection with this Agreement, regardless of the form of action (whether in contract, tort, negligence, strict liability or otherwise) and how it is characterized, even if such party has been advised of the possibility of such damages.</p>

        <p><span class="subsection-label">IV.</span> As a condition prior to indemnification, the party requesting indemnification shall inform the other party within five (5) business days following receipt of notification of any claim, liability or demand for which it requests indemnification, and shall cooperate in the investigation and defense of such matter.</p>

        <p><span class="subsection-label">V.</span> The provisions of this Agreement constitute the entire agreement between the parties with respect to indemnification, and each party waives the right to assert any claim for indemnification or common law contribution against the other party.</p>
    </div>

    <!-- PAGE 4 -->
    <!-- SECTION 9 - NOTICES -->
    <div class="page-break"></div>
    <div class="section-number">9. NOTICES</div>
    <div class="subsection">
        <p><span class="subsection-label">I.</span> All notices required under this Agreement must be in writing, and if addressed to the CLIENT, they will be sufficient in all respects if delivered in person or sent by a nationally recognized express courier service or by registered or certified mail to:</p>

        <div class="notice-block">
            <strong>Customer:</strong>
            <?php echo $company_name ?: '(Company Name)'; ?><br>
            <?php echo $company_address ?: '(ADDRESS)'; ?>
            <br><br>
            <strong>For the attention of:</strong>
            <?php echo $client_name ?: '(customer_name)'; ?><br>
            <?php echo $client_title ?: '(contact_name)'; ?>
        </div>

        <p><span class="subsection-label">II.</span> In addition, it will be sufficient for the Contractor to deliver it in person or send it by a nationally recognized express courier service or by registered mail to:</p>

        <div class="notice-block">
            <strong>Service provider:</strong>
            Prime Hospitality Services of Texas Inc.<br>
            8303 Westglen Dr<br>
            Houston, Texas 77063
            <br><br>
            <strong>For the attention of:</strong>
            Patty P&eacute;rez &ndash; President<br>
            <em>or</em><br>
            Rafael S. P&eacute;rez Jr. &ndash; Senior Vice President
        </div>
    </div>

    <!-- PAGE 5 -->
    <div class="page-break"></div>

    <!-- SECTION 10 -->
    <div class="section-number">10. MISCELLANEOUS</div>
    <div class="subsection">
        <p><span class="subsection-label">I.</span> The provisions of this Agreement, which by their terms extend beyond the termination or non-renewal of this Agreement, will remain in effect after the termination or non-renewal.</p>

        <p><span class="subsection-label">II.</span> No provision of this Agreement may be modified or waived unless agreed in writing and signed by both parties.</p>

        <p><span class="subsection-label">III.</span> If any provision of this Agreement is deemed invalid, unenforceable, or contrary to current or future law, such provision shall be deemed severed without affecting the validity or enforceability of the remaining provisions. In such case, the invalid or unenforceable provision shall be replaced by a valid and enforceable provision that, to the greatest extent possible, has the same legal and economic effect.</p>

        <p><span class="subsection-label">IV.</span> This Agreement and the attached annexes contain the entire understanding between the parties and supersede all prior agreements and understandings relating to the subject matter of this Agreement.</p>

        <p><span class="subsection-label">V.</span> The attached Annexes are incorporated into this Agreement by reference and form an integral part thereof. Specifically, Annex B (Principal Associate Benefit Waiver), Annex C (Assigned Employee Confidentiality Agreement), and Annex D (Liability Insurance Certificate) shall apply as a condition of service. In the event of any conflict between this Agreement and any Annex, the terms of this Agreement shall prevail, unless otherwise expressly agreed in writing.</p>

        <p><span class="subsection-label">VI.</span> The provisions of this Agreement shall be for the benefit of and binding upon the parties and their respective representatives, successors and assigns.</p>

        <p><span class="subsection-label">VII.</span> Failure by either party to enforce any provision of this Agreement shall not constitute a waiver of any provision or of such party's right to subsequently enforce any provision of this Agreement.</p>

        <p><span class="subsection-label">VIII.</span> THE CLIENT will not transfer or assign this Agreement without PRIME's written consent.</p>

        <p><span class="subsection-label">IX.</span> Any notice or other communication shall be deemed duly delivered only when sent through the United States Postal Service or a nationally recognized courier service, to the address shown on the first page of this Agreement.</p>

        <p><span class="subsection-label">X.</span> Neither party shall be liable for failure or delay in performing this Agreement if such failure or delay is due to labor disputes, strikes, fire, riots, war, terrorism, acts of God, or any other cause beyond the control of the defaulting party.</p>
    </div>

    <!-- PAGE 6 -->
    <div class="page-break"></div>

    <!-- SECTION 11 -->
    <div class="section-number">11. TERMS OF THE AGREEMENT</div>
    <div class="subsection">
        <p><span class="subsection-label">I.</span> This Agreement shall enter into force on the date it is executed by the last signatory party (the "Effective Date") and shall remain in force for an initial term of <?php echo $contract_duration; ?>.</p>

        <p><span class="subsection-label">II.</span> Either party may terminate this Agreement at any time by giving the other party thirty (30) days' written notice. This termination clause applies to all services described in this Agreement; however, if either party becomes bankrupt, insolvent, ceases operations, or fails to make required payments, the other party may terminate this Agreement by giving twenty-four (24) hours' written notice.</p>

        <p><span class="subsection-label">III.</span> Upon expiration of the initial term, this Agreement shall automatically renew for successive one (1) year periods, unless either party provides written notice of its intention not to renew at least thirty (30) days prior to the expiration of the current term. During any renewal period, the terms, conditions, and provisions of this Agreement shall remain in full force and effect, unless modified in writing by mutual agreement of the parties.</p>

        <p><span class="subsection-label">IV.</span> The authorized representatives of the parties have signed this Agreement to confirm their acceptance of the terms set forth herein.</p>
    </div>

    <!-- SIGNATURE BLOCK - Agreement -->
    <div class="signature-block">
        <table class="sig-table">
            <tr>
                <td>
                    <div class="signature-block-title"><?php echo $company_name ?: '(Company Name)'; ?>:</div>
                    <div class="sig-line-item">
                        <div class="sig-label">Signature:</div>
                        <div class="sig-underline"></div>
                    </div>
                    <div class="sig-line-item">
                        <div class="sig-label">Printed name:</div>
                        <div class="sig-underline"></div>
                    </div>
                    <div class="sig-line-item">
                        <div class="sig-label">Qualification:</div>
                        <div class="sig-underline"></div>
                    </div>
                    <div class="sig-line-item">
                        <div class="sig-label">Date:</div>
                        <div class="sig-underline"></div>
                    </div>
                </td>
                <td>
                    <div class="signature-block-title">Prime Hospitality Services of Texas:</div>
                    <div class="sig-line-item">
                        <div class="sig-label">Signature:</div>
                        <div class="sig-underline"></div>
                    </div>
                    <div class="sig-line-item">
                        <div class="sig-label">Printed name:</div>
                        <div class="sig-underline"></div>
                    </div>
                    <div class="sig-line-item">
                        <div class="sig-label">Qualification:</div>
                        <div class="sig-underline"></div>
                    </div>
                    <div class="sig-line-item">
                        <div class="sig-label">Date:</div>
                        <div class="sig-underline"></div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- PAGE 7 -->
    <div class="page-break"></div>

    <!-- SECTION 12 -->
    <div class="section-number">12. Emergency provision</div>
    <div class="subsection">
        <p><span class="subsection-label">I.</span> PRIME may provide certain administrative or operational services remotely via email, telephone, or videoconference when circumstances reasonably require it. PRIME may also conduct site visits as necessary to obtain documentation, inspect work performance, or address operational issues.</p>

        <p><span class="subsection-label">II.</span> An "Emergency" refers to any unforeseen event that requires immediate action to prevent service interruption, property damage, security risks, or regulatory non-compliance. Emergency visits will be limited to situations where remote resolution is not reasonably feasible.</p>

        <p><span class="subsection-label">III.</span> Any emergency service requiring additional labor, time, or resources beyond the Scope of Work may be billed separately at mutually agreed rates, unless otherwise stated in Schedule A.</p>
    </div>

    <!-- SECTION 13 -->
    <div class="section-number">13. Price increase</div>
    <div class="subsection">
        <p><span class="subsection-label">I.</span> From the date the contract begins, the prices stipulated in this Agreement will be subject to annual adjustment. On each anniversary of the contract's effective date, the agreed prices for the products or services provided will be increased by (<?php echo $inflation_adj; ?>%) or by any other percentage that the parties agree upon in good faith, considering prevailing market conditions, inflation rates, and other relevant factors.</p>

        <p>PRIME will notify the CLIENT in writing of any price adjustment at least thirty (30) days prior to the renewal date. The CLIENT will have the right to review and discuss the proposed adjustment with PRIME and mutually agree to any modifications before they take effect.</p>

        <p>This arrangement ensures that prices remain fair and reflect the current economic climate, enabling both parties to maintain a successful and mutually beneficial business relationship in the long term.</p>
    </div>

    <!-- SECTION 14 -->
    <div class="section-number">14. INSURANCE</div>
    <div class="subsection">
        <p><span class="subsection-label">I.</span> During the term of this Agreement and for as long thereafter as the Contractor may have any obligation to the CLIENT under this Agreement, the Contractor shall, at its own cost and expense, obtain and maintain in full force and effect (or cause to be obtained and maintained at no cost to the CLIENT) insurance with sound and reputable insurance companies of the type and for the amounts that are adequate for all risks through sound and prudent business practices for the type of business operation, activities, and services that the Contractor will provide and perform under this Agreement and as approved by the CLIENT from time to time, including, without limitation, workers' compensation and employer's liability, general liability, auto liability, and protective liability.</p>

        <p><span class="subsection-label">II.</span> In no event shall the insurance coverage required under this Contract be less than the amounts set forth in Annex (D) attached hereto and incorporated herein by reference. Upon execution of this Contract, the Contractor shall immediately provide the CLIENT with truthful and accurate Certificates of Insurance (duly signed by an authorized representative of the insurance company) attesting that the insurance required under this Contract is in force and that such insurance will not be canceled or substantially modified without notifying the CLIENT in writing at least thirty (30) days in advance.</p>

        <p><span class="subsection-label">III.</span> Except to the extent prohibited by applicable federal or state law, the CLIENT shall be named as an additional insured and beneficiary of the indemnity for losses on all such insurance policies. The Contractor's requirement to obtain and maintain such insurance coverage shall not invalidate or reduce its obligations. The CLIENT shall have the right to require the Contractor to increase the amounts and enhance the insurance provided by the Contractor hereunder, as it deems appropriate in its reasonable discretion. This section shall survive the expiration or termination of this Agreement.</p>

        <p><span class="subsection-label">IV.</span> As necessary, by negotiation or at the CLIENT's request, PRIME will provide the CLIENT with certificates of this insurance coverage or, with the insurer's consent, make the CLIENT an additional insured for PRIME's services.</p>
    </div>

    <!-- PAGE 8 -->
    <div class="page-break"></div>

    <!-- SECTION 15 -->
    <div class="section-number">15. PENALTY FOR LATE PAYMENT</div>
    <div class="subsection">
        <p><span class="subsection-label">I.</span> The CLIENT agrees to pay within thirty (30) days following the invoice date. Any outstanding balance after the agreed payment period will accrue interest at the current legal rate, calculated from the end of the payment period until full payment is received.</p>

        <p><span class="subsection-label">II.</span> If the CLIENT fails to make payment within forty-five (45) days of the invoice date, PRIME shall have the right, upon written notice at least five (5) business days in advance, to temporarily suspend services until all outstanding balances are paid. Suspension of services shall not be considered termination of this Agreement, and the CLIENT shall remain liable for all accrued charges and fees.</p>
    </div>

    <!-- SECTION 16 -->
    <div class="section-number">16. NO STAFF RECRUITMENT FEE</div>
    <div class="subsection">
        <p><span class="subsection-label">I.</span> THE CLIENT and PRINCIPAL agree that, during the term of this Agreement and for a period of two (2) years thereafter, neither party shall contract, solicit or otherwise engage, directly or indirectly, the services of any personnel directly involved in the execution of this Agreement, without the prior written consent of the other party.</p>

        <p><span class="subsection-label">II.</span> In case of non-compliance, the offending party shall pay the other party a placement and training fee equivalent to thirty percent (30%) of the worker's annualized remuneration with the new employer.</p>
    </div>

    <!-- SECTION 17 -->
    <div class="section-number">17. NATURE OF THE RELATIONSHIP</div>
    <div class="subsection">
        <p><span class="subsection-label">I.</span> The services that PRIME will provide to the CLIENT under this Agreement will be provided as an independent contractor. Nothing in this Agreement shall be construed as creating a principal-agent or employer-employee relationship between PRIME and the CLIENT.</p>
    </div>

    <!-- SECTION 18 -->
    <div class="section-number">18. HEADINGS</div>
    <div class="subsection">
        <p><span class="subsection-label">I.</span> The paragraph headings in this Agreement are inserted for ease of reference. They shall in no case define, limit, expand, or facilitate the interpretation of the scope, extent, or intent of this Agreement.</p>
    </div>

    <!-- SECTION 19 -->
    <div class="section-number">19. ARBITRATION</div>
    <div class="subsection">
        <p><span class="subsection-label">I.</span> Any controversy or dispute arising out of or relating to this Agreement shall be resolved by binding arbitration, conducted pursuant to the Federal Arbitration Act and administered by the American Arbitration Association (AAA). The arbitration shall be held at the AAA headquarters in Texas nearest to PRIME's principal office. The arbitrator shall allocate arbitration costs and attorneys' fees based on the merits of the claims and the relative fault of the parties, unless otherwise provided by law.</p>
    </div>

    <!-- ============================================ -->
    <!-- PAGE 9 - APPENDIX A - Service Prices -->
    <!-- ============================================ -->
    <div class="page-break"></div>

    <div class="appendix-title">APPENDIX (A)</div>
    <div class="appendix-subtitle">Service Prices</div>

    <div class="appendix-content">
        <?php
        // Build service pricing table from DB detail tables
        $hasServices = false;
        $allServices = [];

        if (!empty($janitorialServices)) {
            $hasServices = true;
            foreach ($janitorialServices as $svc) {
                $allServices[] = $svc;
            }
        }
        if (!empty($kitchenServices)) {
            $hasServices = true;
            foreach ($kitchenServices as $svc) {
                $allServices[] = $svc;
            }
        }
        if (!empty($hoodVentServices)) {
            $hasServices = true;
            foreach ($hoodVentServices as $svc) {
                $allServices[] = $svc;
            }
        }

        if ($hasServices && !empty($allServices)):
        ?>
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <thead>
                <tr>
                    <th style="background-color: #CC0000; color: white; padding: 8px 6px; text-align: center; border: 1px solid #000; font-size: 8pt; text-transform: uppercase;">Type of Service</th>
                    <th style="background-color: #CC0000; color: white; padding: 8px 6px; text-align: center; border: 1px solid #000; font-size: 8pt; text-transform: uppercase;">Service Time</th>
                    <th style="background-color: #CC0000; color: white; padding: 8px 6px; text-align: center; border: 1px solid #000; font-size: 8pt; text-transform: uppercase;">Frequency</th>
                    <th style="background-color: #CC0000; color: white; padding: 8px 6px; text-align: center; border: 1px solid #000; font-size: 8pt; text-transform: uppercase;">Description</th>
                    <th style="background-color: #CC0000; color: white; padding: 8px 6px; text-align: center; border: 1px solid #000; font-size: 8pt; text-transform: uppercase;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allServices as $svc): ?>
                <tr>
                    <td style="border: 1px solid #000; padding: 6px 8px; font-size: 8pt; text-align: left;"><?php echo htmlspecialchars($svc['service_type'] ?? ''); ?></td>
                    <td style="border: 1px solid #000; padding: 6px 8px; font-size: 8pt; text-align: center;"><?php echo htmlspecialchars($svc['service_time'] ?? ''); ?></td>
                    <td style="border: 1px solid #000; padding: 6px 8px; font-size: 8pt; text-align: center;"><?php echo htmlspecialchars($svc['frequency'] ?? ''); ?></td>
                    <td style="border: 1px solid #000; padding: 6px 8px; font-size: 8pt; text-align: left;"><?php echo htmlspecialchars($svc['description'] ?? ''); ?></td>
                    <td style="border: 1px solid #000; padding: 6px 8px; font-size: 8pt; text-align: right; font-weight: bold;">$<?php echo number_format(floatval($svc['subtotal'] ?? 0), 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="text-align: center; color: #666; font-style: italic; margin-top: 30px;">(Service prices to be detailed here)</p>
        <?php endif; ?>
    </div>

    <!-- ============================================ -->
    <!-- PAGE 10 - APPENDIX B - Benefits Waiver -->
    <!-- ============================================ -->
    <div class="page-break"></div>

    <div class="appendix-title">APPENDIX (B)</div>
    <div class="appendix-subtitle">Exemption from Benefits for Principal Associates</div>

    <div class="appendix-content">
        <p>Considering my assignment to CLIENT by PRIME, I acknowledge that I am solely an employee of PRIME for the purposes of the benefits plan and am entitled only to the benefits that PRIME may provide to me as its employee. I further understand and agree that I am not eligible or entitled to participate in or make any claim to any benefits plan, policy, or practice offered by CLIENT, its parent companies, affiliates, subsidiaries, or successors at any time to its direct employees, regardless of the duration of my assignment to CLIENT by PRIME and regardless of whether I am considered a common-law employee of CLIENT for any purpose; and therefore, with full knowledge and understanding, I hereby expressly waive any and all claims or rights I may have, now or in the future, to such benefits and agree not to make any claims for such use.</p>
    </div>

    <!-- Signature block for Appendix B -->
    <div class="signature-block">
        <table class="sig-table">
            <tr>
                <td>
                    <div class="signature-block-title">EMPLOYEE:</div>
                    <div class="sig-line-item">
                        <div class="sig-label">Signature:</div>
                        <div class="sig-underline"></div>
                    </div>
                    <div class="sig-line-item">
                        <div class="sig-label">Printed name:</div>
                        <div class="sig-underline"></div>
                    </div>
                    <div class="sig-line-item">
                        <div class="sig-label">Date:</div>
                        <div class="sig-underline"></div>
                    </div>
                </td>
                <td>
                    <div class="signature-block-title">Prime Hospitality Services of Texas:</div>
                    <div class="sig-line-item">
                        <div class="sig-label">Signature:</div>
                        <div class="sig-underline"></div>
                    </div>
                    <div class="sig-line-item">
                        <div class="sig-label">Printed name:</div>
                        <div class="sig-underline"></div>
                    </div>
                    <div class="sig-line-item">
                        <div class="sig-label">Qualification:</div>
                        <div class="sig-underline"></div>
                    </div>
                    <div class="sig-line-item">
                        <div class="sig-label">Date:</div>
                        <div class="sig-underline"></div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- ============================================ -->
    <!-- PAGE 11 - APPENDIX C - Confidentiality Agreement -->
    <!-- ============================================ -->
    <div class="page-break"></div>

    <div class="appendix-title">APPENDIX (C)</div>
    <div class="appendix-subtitle">Confidentiality and Non-Disclosure Agreement for Key Associates</div>

    <div class="appendix-content">
        <p>Considering my assignment to the CLIENT by PRIME, I acknowledge that, during the course of my assignment, I may have access to confidential, proprietary, or trade secret information belonging to the CLIENT. I agree to maintain the strict confidentiality of such information and not to disclose, use, copy, or disseminate it for any purpose other than the performance of my assigned duties, except as required by law.</p>
    </div>

    <!-- Signature block for Appendix C -->
    <div class="signature-block">
        <table class="sig-table">
            <tr>
                <td>
                    <div class="signature-block-title">EMPLOYEE:</div>
                    <div class="sig-line-item">
                        <div class="sig-label">Signature:</div>
                        <div class="sig-underline"></div>
                    </div>
                    <div class="sig-line-item">
                        <div class="sig-label">Printed name:</div>
                        <div class="sig-underline"></div>
                    </div>
                    <div class="sig-line-item">
                        <div class="sig-label">Qualification:</div>
                        <div class="sig-underline"></div>
                    </div>
                    <div class="sig-line-item">
                        <div class="sig-label">Date:</div>
                        <div class="sig-underline"></div>
                    </div>
                </td>
                <td>
                    <div class="signature-block-title">WITNESS:</div>
                    <div class="sig-line-item">
                        <div class="sig-label">Signature:</div>
                        <div class="sig-underline"></div>
                    </div>
                    <div class="sig-line-item">
                        <div class="sig-label">Printed name:</div>
                        <div class="sig-underline"></div>
                    </div>
                    <div class="sig-line-item">
                        <div class="sig-label">Qualification:</div>
                        <div class="sig-underline"></div>
                    </div>
                    <div class="sig-line-item">
                        <div class="sig-label">Date:</div>
                        <div class="sig-underline"></div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- FOOTER -->
    <div class="footer-wrapper">
        <div class="footer-top">
            PRIME HOSPITALITY SERVICES OF TEXAS
        </div>
        <div class="footer-bottom">
            <strong>8303 Westglen Dr - Houston, TX 77063 - Phone 713-338-2553 - Fax 713-574-3065</strong><br>
            <a href="http://www.primefacilityservicesgroup.com">www.primefacilityservicesgroup.com</a>
        </div>
    </div>

</body>
</html>
