<?php
/**
 * PROPOSAL TEMPLATE
 * Contract proposal document with dynamic content from database.
 *
 * Variables available from generate_pdf.php:
 *   $data - associative array with form fields, contract_staff, scope_sections, etc.
 *
 * Dynamic fields replaced:
 *   (company_name)       -> $data['Company_Name']
 *   (address)            -> $data['Company_Address']
 *   (contract_duration)  -> mapped from $data['Contract_Duration']
 *
 * STAFFING FEES table is generated dynamically from $data['contract_staff'],
 * grouped by department with bill_rate formatted as USD currency.
 */

// ============================================================
// VALIDATION - Block generation if required data is missing
// ============================================================
$validationErrors = [];

if (empty($data['Company_Name'])) {
    $validationErrors[] = 'Company Name is required.';
}
if (empty($data['Company_Address'])) {
    $validationErrors[] = 'Company Address is required.';
}
if (empty($data['Contract_Duration']) || $data['Contract_Duration'] === 'not_applicable') {
    $validationErrors[] = 'Contract Duration is required.';
}

$contractStaff = $data['contract_staff'] ?? [];
if (empty($contractStaff)) {
    $validationErrors[] = 'At least one staff position is required in contract_staff.';
}

// Validate bill_rate values are numeric
foreach ($contractStaff as $idx => $staff) {
    if (!isset($staff['bill_rate']) || !is_numeric($staff['bill_rate'])) {
        $validationErrors[] = 'Invalid bill_rate for position: ' . htmlspecialchars($staff['position'] ?? 'Row ' . ($idx + 1));
    }
}

if (!empty($validationErrors)) {
    // Return validation error page instead of the proposal
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Proposal Generation Error</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 40px; background: #f8f9fa; }
            .error-container { max-width: 600px; margin: 0 auto; background: white; border: 2px solid #dc3545; border-radius: 8px; padding: 30px; }
            .error-title { color: #dc3545; font-size: 18pt; margin-bottom: 15px; }
            .error-list { color: #333; margin: 10px 0; padding-left: 20px; }
            .error-list li { margin-bottom: 8px; }
            .error-note { color: #666; font-size: 9pt; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-title">Cannot Generate Proposal</div>
            <p>The following required fields must be completed before generating the proposal:</p>
            <ul class="error-list">
                <?php foreach ($validationErrors as $err): ?>
                <li><?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
            <p class="error-note">Please return to the editor and complete all required fields, then try again.</p>
        </div>
    </body>
    </html>
    <?php
    return; // Stop further template rendering
}

// ============================================================
// DATA PREPARATION
// ============================================================

// Logo
$dept = strtolower(trim($data['Service_Type'] ?? ''));
if (strpos($dept, 'hospitality') !== false) {
    $logo_file = __DIR__ . '/../../Images/phospitality.png';
} else {
    $logo_file = __DIR__ . '/../../Images/pfacility.png';
}
$logo_base64 = '';
if (file_exists($logo_file)) {
    $logo_base64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logo_file));
}

// Client data
$company_name = htmlspecialchars($data['Company_Name'] ?? '');
$company_address = htmlspecialchars($data['Company_Address'] ?? '');
$client_name = htmlspecialchars($data['client_name'] ?? '');
$client_title = htmlspecialchars($data['Client_Title'] ?? '');

// Contract duration mapping
$duration_map = [
    '6_months'  => '6 Months',
    '1_year'    => '1 Year',
    '1_5_years' => '1.5 Years (18 Months)',
    '2_years'   => '2 Years',
    '3_years'   => '3 Years',
    '4_years'   => '4 Years',
    '5_years'   => '5 Years',
];
$contract_duration = $duration_map[$data['Contract_Duration'] ?? ''] ?? htmlspecialchars($data['Contract_Duration'] ?? '');

// Group staff by department
$staffByDepartment = [];
foreach ($contractStaff as $staff) {
    $deptName = !empty($staff['department']) ? $staff['department'] : 'GENERAL';
    if (!isset($staffByDepartment[$deptName])) {
        $staffByDepartment[$deptName] = [];
    }
    $staffByDepartment[$deptName][] = $staff;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Proposal - <?php echo $company_name; ?></title>
    <style>
        @page {
            margin: 3.5cm 2cm 3.2cm 2cm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5pt;
            color: #000;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        /* Header - fixed position, repeats on every page */
        .header-wrapper {
            position: fixed;
            top: -3cm;
            left: 0;
            right: 0;
            height: 2.5cm;
            overflow: visible;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px solid #CC0000;
        }

        .header-table td {
            vertical-align: middle;
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

        /* Footer - fixed position, repeats on every page */
        .footer-wrapper {
            position: fixed;
            bottom: -2.7cm;
            left: 0;
            right: 0;
            height: 2.2cm;
            overflow: visible;
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

        /* Section titles */
        .section-title {
            color: #CC0000;
            font-weight: bold;
            font-size: 12pt;
            margin-top: 20px;
            margin-bottom: 10px;
            text-transform: uppercase;
            border-bottom: 2px solid #CC0000;
            padding-bottom: 4px;
        }

        .subsection-title {
            color: #CC0000;
            font-weight: bold;
            font-size: 10pt;
            margin-top: 14px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        /* Content sections */
        .content-section {
            margin-bottom: 12px;
            text-align: justify;
            line-height: 1.5;
        }

        .content-section p {
            margin-bottom: 8px;
        }

        /* Page break */
        .page-break {
            page-break-before: always;
        }

        .no-break {
            page-break-inside: avoid;
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

        /* Staffing fees table */
        .department-header {
            background-color: #CC0000;
            color: white;
            font-weight: bold;
            font-size: 10pt;
            padding: 6px 10px;
            margin-top: 12px;
            margin-bottom: 0;
            text-transform: uppercase;
        }

        .staffing-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .staffing-table th {
            background-color: #f0f0f0;
            color: #333;
            font-weight: bold;
            padding: 6px 10px;
            text-align: left;
            border: 1px solid #ccc;
            font-size: 9pt;
        }

        .staffing-table td {
            padding: 5px 10px;
            border: 1px solid #ccc;
            font-size: 9pt;
        }

        .staffing-table .bill-rate {
            text-align: right;
            font-weight: bold;
        }

        .staffing-intro {
            margin: 10px 0;
            font-size: 9pt;
            line-height: 1.5;
            color: #333;
        }

        .staffing-note {
            margin-top: 10px;
            font-size: 8pt;
            color: #666;
            font-style: italic;
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
    </style>
</head>
<body>

    <!-- HEADER - repeats on every page -->
    <div class="header-wrapper">
        <table class="header-table">
            <tr>
                <td style="width: 40%; padding: 10px 0;">
                    <?php if (!empty($logo_base64)): ?>
                    <img class="header-logo" src="<?php echo $logo_base64; ?>" alt="Prime Hospitality Services">
                    <?php endif; ?>
                </td>
                <td style="width: 60%; padding: 10px 0 10px 15px; text-align: left;">
                    <div class="doc-title">Service</div>
                    <div class="doc-subtitle">PROPOSAL</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- FOOTER - repeats on every page -->
    <div class="footer-wrapper">
        <div class="footer-top">
            PRIME HOSPITALITY SERVICES OF TEXAS
        </div>
        <div class="footer-bottom">
            <strong>8303 Westglen Dr - Houston, TX 77063 - Phone 713-338-2553 - Fax 713-574-3065</strong><br>
            <a href="http://www.primefacilityservicesgroup.com">www.primefacilityservicesgroup.com</a>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- PAGE 1: NOTICES                              -->
    <!-- ============================================ -->

    <div class="section-title">Notices</div>
    <div class="content-section">
        <p>All notices required under this Agreement shall be in writing, and if to the CLIENT shall be sufficient in all respects if delivered in person or sent by a nationally recognized overnight courier service or by registered or certified mail to:</p>

        <div class="notice-block">
            <strong>Client:</strong>
            <?php echo $company_name; ?><br>
            <?php echo $company_address; ?>
            <br><br>
            <strong>Attn:</strong><br>
            Thomas Turner<br>
            General Manager<br>
            (817) 879-1702<br>
            tturner@ambassadorhc.com
        </div>

        <p>Moreover, if to Contractor shall be sufficient in all respects if delivered in person or sent by a nationally recognized overnight courier service or by registered or certified mail to:</p>

        <div class="notice-block">
            <strong>Service Provider:</strong><br>
            Prime Hospitality Services of Texas Inc.<br>
            8303 Westglen Dr<br>
            Houston, Texas 77063
            <br><br>
            <strong>Attn:</strong><br>
            Patty Perez &ndash; President<br>
            <em>or</em><br>
            Rafael S. Perez Jr. &ndash; Sr. Vice President
        </div>
    </div>

    <!-- ============================================ -->
    <!-- PAGE 2: STAFFING FEES                        -->
    <!-- ============================================ -->
    <div class="page-break"></div>

    <div class="section-title">Staffing Fees</div>
    <div class="subsection-title">Hourly Rates by Department</div>

    <div class="staffing-intro">
        <p>This sheet presents the hourly staffing rates, organized by department.
        The rates shown correspond to the Bill Rate applicable per position and are billed per hour worked.</p>
        <p>Positions and rates are grouped by department according to the classification established in the system.</p>
        <p>All rates are expressed in United States Dollars (USD) and are subject to the terms of the service agreement.</p>
    </div>

    <?php foreach ($staffByDepartment as $deptName => $positions): ?>
    <div class="no-break">
        <div class="department-header"><?php echo htmlspecialchars(strtoupper($deptName)); ?></div>
        <table class="staffing-table">
            <thead>
                <tr>
                    <th style="width: 65%;">Position</th>
                    <th style="width: 35%;">Bill Rate</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($positions as $staff): ?>
                <tr>
                    <td><?php echo htmlspecialchars($staff['position'] ?? ''); ?></td>
                    <td class="bill-rate">$<?php echo number_format((float)($staff['bill_rate'] ?? 0), 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endforeach; ?>

    <div class="staffing-note">
        All rates are subject to the terms and conditions outlined in this agreement.
    </div>

    <!-- ============================================ -->
    <!-- PAGE 3: TERMS AND CONDITIONS                 -->
    <!-- ============================================ -->
    <div class="page-break"></div>

    <div class="section-title">Terms and Conditions</div>

    <div class="subsection-title">Billing Terms and Rates</div>
    <div class="content-section">
        <p>The rates established in this agreement shall take effect upon execution by both parties and may be seasonally adjusted with prior written notice.</p>
        <p>Hours worked by any associate assigned to a client will be billed under the following conditions:</p>
        <p>Hours exceeding forty (40) in one (1) workweek, or any other applicable legal threshold, will be billed at one and a half (1.5) times the associate's regular rate. Hours worked on company-recognized holidays will also be billed at one and a half (1.5) times the associate's regular rate.</p>
        <p>The company observes the following official holidays: New Year's Day, Memorial Day, Independence Day, Labor Day, Thanksgiving, and Christmas.</p>
    </div>

    <div class="subsection-title">Staffing Request Deadlines</div>
    <div class="content-section">
        <p>The deadline for staffing requests is 72 hours prior to the requested start date. Requests submitted after this deadline may be subject to emergency rates, calculated as the regular rate multiplied by 1.5. The minimum service request is 6 hours.</p>
    </div>

    <div class="subsection-title">Governing Law</div>
    <div class="content-section">
        <p>This Agreement shall be governed by and construed in accordance with the laws of the State of Texas, without regard to its conflict of law principles. The parties agree that any legal action or proceeding arising out of or relating to this Agreement shall be brought in a court of competent jurisdiction located in the State of Texas.</p>
    </div>

    <div class="subsection-title">Liability &amp; Insurance</div>
    <div class="content-section">
        <p>Prime Hospitality Services of Texas will perform all services in a professional manner, adhering to industry safety standards and applicable health regulations specific to food service environments. Prime Hospitality Services of Texas maintains General Liability and Workers' Compensation Insurance to protect both parties against accidents, injuries, or property damage occurring during the performance of hospitality-related services.</p>
        <p>The client acknowledges that Prime Hospitality Services of Texas shall not be responsible for pre-existing damages, equipment malfunctions, or issues resulting from improper installation or maintenance not related to the work performed.</p>
    </div>

    <!-- ============================================ -->
    <!-- PAGE 4: TERMS AND CONDITIONS (continued)     -->
    <!-- ============================================ -->
    <div class="page-break"></div>

    <div class="section-title">Terms and Conditions</div>

    <div class="subsection-title">Confidentiality</div>
    <div class="content-section">
        <p>Both parties agree to maintain strict confidentiality regarding all shared information, including business data, pricing, operational details, and client-specific information related to hospitality operations.</p>
        <p>Such information shall not be disclosed, distributed, or used for any purpose other than fulfilling the scope of this agreement, except when required by law or with prior written consent from the other party.</p>
    </div>

    <div class="subsection-title">Termination &amp; Payment Terms</div>
    <div class="content-section">
        <p>Payment terms shall be Net 30 (thirty days from the invoice date). Any outstanding balances after the payment due date may accrue interest at the applicable legal rate and may be subject to reasonable administrative fees.</p>
        <p>If payment remains unpaid sixty (60) days past the invoice date, services may be suspended upon written notice until the outstanding balance is fully settled. Any amounts formally disputed in writing shall not be considered past due while under review.</p>
        <p>If services are canceled after scheduling, any completed work, reserved personnel time, or prepared materials shall be invoiced accordingly.</p>
        <p>The prices mentioned do not include taxes; these will be calculated and added in accordance with applicable law.</p>
    </div>

    <div class="subsection-title">Non-Solicitation</div>
    <div class="content-section">
        <p>During the term of this Agreement and for a period of two (2) years following its termination, the Client shall not, without the prior written consent of Prime Hospitality Services of Texas, directly or indirectly hire, solicit, or engage the services of any employee, associate, or subcontractor of Prime Hospitality Services of Texas who was employed or engaged by the Company at any time during the term of this Agreement.</p>
        <p>In the event of a breach of this provision, the Client agrees to pay Prime Hospitality Services of Texas a placement and training fee equal to thirty percent (30%) of the employee's or contractor's most recent annual compensation.</p>
    </div>

    <!-- ============================================ -->
    <!-- PAGE 5: TERMS OF AGREEMENT + SIGNATURES      -->
    <!-- ============================================ -->
    <div class="page-break"></div>

    <div class="section-title">Terms of Agreement</div>
    <div class="content-section">
        <p>This Agreement shall be valid for a period of <?php echo $contract_duration; ?> from the date it is executed by both parties. In cases of bankruptcy, insolvency, discontinuation of operations, or failure to make required payments, either party may terminate this Agreement with twenty-four (24) hours' written notice.</p>
        <p>Upon expiration, this Agreement shall automatically renew for successive one (1) year terms unless either party provides written notice of non-renewal at least thirty (30) days prior to the end of the then-current term. All terms, conditions, and provisions shall remain in full force and effect during any renewal period unless modified in writing by mutual consent of both parties.</p>
    </div>

    <!-- SIGNATURE BLOCK -->
    <div class="signature-block">
        <table class="sig-table">
            <tr>
                <td>
                    <div class="signature-block-title"><?php echo $company_name; ?></div>
                    <div class="sig-line-item">
                        <div class="sig-label">Signature:</div>
                        <div class="sig-underline"></div>
                    </div>
                    <div class="sig-line-item">
                        <div class="sig-label">Printed Name:</div>
                        <div class="sig-underline"></div>
                    </div>
                    <div class="sig-line-item">
                        <div class="sig-label">Title:</div>
                        <div class="sig-underline"></div>
                    </div>
                    <div class="sig-line-item">
                        <div class="sig-label">Date:</div>
                        <div class="sig-underline"></div>
                    </div>
                </td>
                <td>
                    <div class="signature-block-title">Prime Hospitality Services of Texas</div>
                    <div class="sig-line-item">
                        <div class="sig-label">Signature:</div>
                        <div class="sig-underline"></div>
                    </div>
                    <div class="sig-line-item">
                        <div class="sig-label">Printed Name:</div>
                        <div class="sig-underline"></div>
                    </div>
                    <div class="sig-line-item">
                        <div class="sig-label">Title:</div>
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

</body>
</html>
