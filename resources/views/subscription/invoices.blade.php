<!DOCTYPE html>
<html>
<head>
    <title>My Invoices</title>
    <style>
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f4f4f4;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <h2 style="text-align: center;">My Invoices</h2>
    
    @if($invoices->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Download</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->date()->toFormattedDateString() }}</td>
                    <td>{{ $invoice->total() }}</td>
                    <td>
                        @if($invoice->paid)
                            <span style="color: green;">Paid</span>
                        @else
                            <span style="color: red;">Unpaid</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('download.invoice', $invoice->id) }}" class="btn">Download PDF</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center;">No invoices found.</p>
    @endif
    
    <div style="text-align: center; margin-top: 20px;">
        <a href="{{ route('dashboard') }}">← Back to Dashboard</a>
    </div>
</body>
</html>