<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 11px;
        color: #1a1a2e;
        background: #fff;
    }

    /* ── Page border ── */
    .page {
        margin: 18px;
        border: 3px solid #1a3c6b;
        padding: 0;
        min-height: 780px;
        position: relative;
    }
    .inner-border {
        margin: 6px;
        border: 1px solid #b0bec5;
        min-height: 768px;
        padding: 0;
    }

    /* ── Header band ── */
    .header {
        background: #1a3c6b;
        color: #fff;
        padding: 18px 28px 14px;
        display: table;
        width: 100%;
    }
    .header-left  { display: table-cell; vertical-align: middle; width: 55%; }
    .header-right { display: table-cell; vertical-align: middle; text-align: right; width: 45%; }

    .cert-title {
        font-size: 20px;
        font-weight: bold;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: #ffffff;
    }
    .cert-subtitle {
        font-size: 11px;
        color: #90caf9;
        margin-top: 3px;
        letter-spacing: 0.5px;
    }

    .logo-box img { height: 52px; }
    .company-name {
        font-size: 13px;
        font-weight: bold;
        color: #fff;
        margin-top: 4px;
    }
    .company-tagline {
        font-size: 9px;
        color: #90caf9;
    }

    /* ── Certificate number ribbon ── */
    .cert-ribbon {
        background: #e8f0fe;
        border-bottom: 2px solid #1a3c6b;
        padding: 6px 28px;
        display: table;
        width: 100%;
    }
    .cert-ribbon-left  { display: table-cell; }
    .cert-ribbon-right { display: table-cell; text-align: right; }
    .cert-no { font-size: 10px; color: #555; }
    .cert-no strong { color: #1a3c6b; font-size: 11px; }

    /* ── Body ── */
    .body-content { padding: 20px 28px; }

    /* ── Section headings ── */
    .section-heading {
        background: #1a3c6b;
        color: #fff;
        font-size: 10px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        padding: 5px 10px;
        margin: 14px 0 8px;
    }

    /* ── Info grid ── */
    .info-table { width: 100%; border-collapse: collapse; }
    .info-table td { padding: 4px 8px; font-size: 10.5px; vertical-align: top; }
    .info-table .label { color: #666; width: 140px; }
    .info-table .value { font-weight: bold; color: #1a1a2e; }

    /* ── Two-column info block ── */
    .two-col { display: table; width: 100%; }
    .two-col-left, .two-col-right {
        display: table-cell;
        width: 50%;
        vertical-align: top;
        padding-right: 14px;
    }
    .two-col-right { padding-right: 0; padding-left: 14px; }

    /* ── Results table ── */
    .results-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 6px;
        font-size: 10px;
    }
    .results-table th {
        background: #263238;
        color: #fff;
        padding: 6px 8px;
        text-align: left;
        font-size: 9.5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .results-table td {
        padding: 5px 8px;
        border-bottom: 1px solid #e0e0e0;
        vertical-align: middle;
    }
    .results-table tr:nth-child(even) td { background: #f5f7fa; }

    /* ── Result badges ── */
    .badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .badge-pass            { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
    .badge-fail            { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
    .badge-under_review    { background: #fff8e1; color: #f57f17; border: 1px solid #ffe082; }
    .badge-restricted_use  { background: #fff3e0; color: #e65100; border: 1px solid #ffcc80; }
    .badge-not_inspected   { background: #f3e5f5; color: #6a1b9a; border: 1px solid #ce93d8; }
    .badge-not_located     { background: #eceff1; color: #37474f; border: 1px solid #b0bec5; }

    /* ── Summary boxes ── */
    .summary-row { display: table; width: 100%; margin-top: 4px; }
    .summary-box {
        display: table-cell;
        text-align: center;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        padding: 8px 4px;
        margin-right: 6px;
    }
    .summary-num  { font-size: 18px; font-weight: bold; color: #1a3c6b; }
    .summary-lbl  { font-size: 8.5px; color: #777; text-transform: uppercase; letter-spacing: 0.4px; }

    /* ── Signature section ── */
    .sig-row { display: table; width: 100%; margin-top: 20px; }
    .sig-box {
        display: table-cell;
        width: 33%;
        text-align: center;
        padding: 0 10px;
    }
    .sig-line {
        border-top: 1px solid #555;
        margin: 30px 10px 4px;
    }
    .sig-name  { font-size: 10px; font-weight: bold; }
    .sig-role  { font-size: 9px; color: #777; }

    /* ── Footer band ── */
    .footer {
        background: #1a3c6b;
        color: #90caf9;
        padding: 8px 28px;
        font-size: 9px;
        text-align: center;
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
    }
    .footer strong { color: #fff; }

    /* ── Watermark ── */
    .watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-30deg);
        font-size: 72px;
        color: rgba(26, 60, 107, 0.05);
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 10px;
        pointer-events: none;
        white-space: nowrap;
    }
</style>
</head>
<body>
<div class="page">
    <div class="inner-border">

        <!-- Watermark -->
        <div class="watermark">CERTIFIED</div>

        <!-- ── Header ── -->
        <div class="header">
            <div class="header-left">
                <div class="cert-title">Certificate of Inspection</div>
                <div class="cert-subtitle">{{ \App\Models\Job::WORK_TYPES[$job->work_type] }} &nbsp;|&nbsp; {{ $job->site->name ?? $job->site->address }}</div>
            </div>
            <div class="header-right">
                <div class="logo-box">
                    <img src="{{ public_path('assets/images/aplus-safety-logo.png') }}" alt="APlus Safety">
                </div>
                <div class="company-name">APlus Safety</div>
                <div class="company-tagline">Certified Safety Inspection Services</div>
            </div>
        </div>

        <!-- ── Certificate number ribbon ── -->
        <div class="cert-ribbon">
            <div class="cert-ribbon-left">
                <span class="cert-no">Certificate No: <strong>CERT-{{ strtoupper(substr($job->id, 0, 8)) }}</strong></span>
                &nbsp;&nbsp;
                <span class="cert-no">Job Ref: <strong>{{ strtoupper(substr($job->id, -6)) }}</strong></span>
            </div>
            <div class="cert-ribbon-right">
                <span class="cert-no">Date Issued: <strong>{{ now()->format('d M Y') }}</strong></span>
            </div>
        </div>

        <!-- ── Body ── -->
        <div class="body-content">

            <!-- Client & Site -->
            <div class="section-heading">Client &amp; Site Information</div>
            <div class="two-col">
                <div class="two-col-left">
                    <table class="info-table">
                        <tr>
                            <td class="label">Client</td>
                            <td class="value">{{ $job->client->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Site</td>
                            <td class="value">{{ $job->site->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Address</td>
                            <td class="value">{{ $job->site->address ?? '—' }}</td>
                        </tr>
                        @if($job->buildings->isNotEmpty())
                        <tr>
                            <td class="label">Buildings</td>
                            <td class="value">{{ $job->buildings->pluck('name_or_level')->join(', ') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
                <div class="two-col-right">
                    <table class="info-table">
                        <tr>
                            <td class="label">Work Type</td>
                            <td class="value">{{ \App\Models\Job::WORK_TYPES[$job->work_type] }}</td>
                        </tr>
                        <tr>
                            <td class="label">Scheduled Date</td>
                            <td class="value">{{ $job->scheduled_date?->format('d M Y') ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Assigned Technician(s)</td>
                            <td class="value">{{ $job->technicians->pluck('name')->join(', ') ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Reviewer</td>
                            <td class="value">{{ $job->client->manager->name ?? '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Summary counts -->
            <div class="section-heading">Inspection Summary</div>
            @php
                $total    = $records->count();
                $pass     = $records->where('result', 'pass')->count();
                $fail     = $records->where('result', 'fail')->count();
                $other    = $total - $pass - $fail;
            @endphp
            <div class="summary-row">
                <div class="summary-box">
                    <div class="summary-num">{{ $total }}</div>
                    <div class="summary-lbl">Total Assets</div>
                </div>
                <div class="summary-box" style="border-color:#a5d6a7">
                    <div class="summary-num" style="color:#2e7d32">{{ $pass }}</div>
                    <div class="summary-lbl">Pass</div>
                </div>
                <div class="summary-box" style="border-color:#ef9a9a">
                    <div class="summary-num" style="color:#c62828">{{ $fail }}</div>
                    <div class="summary-lbl">Fail</div>
                </div>
                <div class="summary-box" style="border-color:#ffe082">
                    <div class="summary-num" style="color:#f57f17">{{ $other }}</div>
                    <div class="summary-lbl">Other</div>
                </div>
            </div>

            <!-- Asset records table -->
            <div class="section-heading">Inspection Records</div>
            <table class="results-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Asset Code</th>
                        <th>Asset Type</th>
                        <th>Location</th>
                        <th>Inspection Date</th>
                        <th>Result</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $i => $record)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><strong>{{ $record->asset->asset_code }}</strong></td>
                        <td>{{ $assetTypes[$record->asset->asset_type] ?? $record->asset->asset_type }}</td>
                        <td>
                            {{ $record->asset->building->name_or_level ?? '' }}
                            @if($record->asset->zone) &ndash; {{ $record->asset->zone }} @endif
                        </td>
                        <td>{{ $record->inspection_date?->format('d M Y') ?? '—' }}</td>
                        <td>
                            <span class="badge badge-{{ $record->result }}">
                                {{ ucwords(str_replace('_', ' ', $record->result ?? 'N/A')) }}
                            </span>
                        </td>
                        <td style="font-size:9px;color:#555;">{{ $record->reason_for_result ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($job->scope_notes)
            <div class="section-heading">Scope Notes</div>
            <p style="font-size:10px;color:#444;padding:4px 0;">{{ $job->scope_notes }}</p>
            @endif

            <!-- Signatures -->
            <div class="sig-row">
                <div class="sig-box">
                    <div class="sig-line"></div>
                    <div class="sig-name">{{ $job->technicians->first()->name ?? '—' }}</div>
                    <div class="sig-role">Field Technician</div>
                </div>
                <div class="sig-box">
                    <div class="sig-line"></div>
                    <div class="sig-name">{{ $job->client->manager->name ?? '—' }}</div>
                    <div class="sig-role">Reviewer / Manager</div>
                </div>
                <div class="sig-box">
                    <div class="sig-line"></div>
                    <div class="sig-name">APlus Safety</div>
                    <div class="sig-role">Authorised Officer</div>
                </div>
            </div>

        </div><!-- /body-content -->

        <!-- ── Footer ── -->
        <div class="footer">
            <strong>APlus Safety</strong> &nbsp;|&nbsp;
            This certificate confirms that the above-listed assets were inspected in accordance with applicable safety standards. &nbsp;|&nbsp;
            Generated: {{ now()->format('d M Y, H:i') }}
        </div>

    </div><!-- /inner-border -->
</div><!-- /page -->
</body>
</html>
