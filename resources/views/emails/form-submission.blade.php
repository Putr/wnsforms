<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>New Form Submission</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            color: #2563eb;
            margin-bottom: 20px;
        }

        .submission-info {
            background-color: #f3f4f6;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .data-table th,
        .data-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .data-table th {
            background-color: #f9fafb;
            font-weight: bold;
        }

        .footer {
            font-size: 12px;
            color: #6b7280;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>

<body>
    <h1>New Form Submission: {{ $form->name }}</h1>

    <div class="submission-info">
        <p><strong>Form:</strong> {{ $form->name }}</p>
        <p><strong>Submitted at:</strong> {{ $submittedAt }}</p>
    </div>

    <h2>Form Data:</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Field</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $key => $value)
            <tr>
                <td><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}</strong></td>
                <td>{{ $value }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is an automated email sent by your WNSForms application.</p>
        <p>IP Address: {{ $submission->ip_address }}</p>
        @if($submission->referrer)
        <p>Referrer: {{ $submission->referrer }}</p>
        @endif
    </div>
</body>

</html>
