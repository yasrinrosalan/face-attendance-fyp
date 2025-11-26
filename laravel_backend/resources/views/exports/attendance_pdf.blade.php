<!DOCTYPE html>
<html>

<head>
    <title>Attendance Report</title>
    <style>
        body {
            font-family: sans-serif;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #203a8d;
            padding-bottom: 15px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #203a8d;
        }

        .university-name {
            font-size: 14px;
            color: #666;
        }

        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
        }

        .meta-info {
            margin-bottom: 25px;
            font-size: 14px;
        }

        .meta-info table {
            width: 100%;
        }

        .meta-info td {
            padding: 5px 0;
        }

        .label {
            font-weight: bold;
            color: #555;
            width: 120px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 13px;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #ddd;
            padding: 8px 10px;
            text-align: left;
        }

        table.data-table th {
            background-color: #f4f4f4;
            color: #203a8d;
            font-weight: bold;
        }

        table.data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 12px;
            color: #888;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }

        .signature {
            margin-top: 50px;
            float: right;
            width: 200px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="logo">UMPSA</div>
        <div class="university-name">Universiti Malaysia Pahang Al-Sultan Abdullah</div>
        <div class="report-title">OFFICIAL ATTENDANCE RECORD</div>
    </div>

    <div class="meta-info">
        <table>
            <tr>
                <td class="label">Course:</td>
                <td><strong>{{ $session->course->course_code }}</strong> - {{ $session->course->course_name }}</td>
                <td class="label">Session Title:</td>
                <td>{{ $session->session_title }}</td>
            </tr>
            <tr>
                <td class="label">Lecturer:</td>
                <td>{{ $lecturer->name }}</td>
                <td class="label">Date:</td>
                <td>{{ $session->starts_at->format('d F Y') }}</td>
            </tr>
            <tr>
                <td class="label">Time:</td>
                <td>{{ $session->starts_at->format('h:i A') }} - {{ $session->ends_at->format('h:i A') }}</td>
                <td class="label">Total Present:</td>
                <td><strong>{{ $records->count() }} Students</strong></td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 40px;">No.</th>
                <th>Student ID</th>
                <th>Name</th>
                <th>Time Recorded</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $record)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $record->student->student_id ?? '-' }}</td>
                    <td>{{ $record->student->name }}</td>
                    <td>{{ $record->attended_at->format('h:i:s A') }}</td>
                    <td style="color: green; font-weight: bold;">PRESENT</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; font-style: italic; color: #666; padding: 20px;">
                        No attendance records found for this session.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature">
        Verified By:
        <div class="signature-line">
            {{ $lecturer->name }}<br>
            (Lecturer)
        </div>
    </div>

    <div class="footer">
        This document is computer generated. No signature is required. | Generated on
        {{ $generated_at->format('d M Y, h:i A') }}
    </div>
</body>

</html>
