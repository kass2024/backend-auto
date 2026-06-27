<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $documentTitle }} — {{ config('neamee.company_name') }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
            margin: 0;
            padding: 24px;
            background: #f3f4f6;
        }
        .toolbar {
            max-width: 1100px;
            margin: 0 auto 16px;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }
        .toolbar button, .toolbar a {
            background: #556332;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }
        .sheet {
            max-width: 1100px;
            margin: 0 auto;
            background: #fff;
            padding: 28px 32px 36px;
            box-shadow: 0 8px 30px rgba(0,0,0,.08);
        }
        .doc-title {
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: .04em;
            margin: 18px 0 22px;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        th, td {
            border: 1px solid #222;
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #fff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 12px;
        }
        .footer-note {
            margin-top: 18px;
            font-size: 12px;
            color: #555;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none !important; }
            .sheet { box-shadow: none; padding: 12px; max-width: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Print / Save as PDF</button>
        <a href="javascript:window.close()">Close</a>
    </div>

    <div class="sheet">
        @include('filament.print.partials.company-header')

        <div class="doc-title">{{ $documentTitle }}</div>

        <table>
            <thead>
                <tr>
                    @foreach ($columns as $column)
                        <th>{{ $column }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        @foreach ($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) }}">No records yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <p class="footer-note">Generated {{ now()->format('M j, Y g:i A') }} — {{ config('neamee.company_name') }}</p>
    </div>
</body>
</html>
