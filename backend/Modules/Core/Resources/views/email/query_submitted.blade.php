<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ready Bill</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        h3 {
            color: #2c3e50;
        }

        p {
            font-size: 14px;
            color: #7f8c8d;
            line-height: 1.5;
        }

        .info-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .info-table th,
        .info-table td {
            padding: 8px 12px;
            border: 1px solid #ecf0f1;
            text-align: left;
        }

        .info-table th {
            background-color: #f0f0f0;
            color: #34495e;
        }

        .info-table td {
            background-color: #fafafa;
        }

        .attachment {
            margin-top: 20px;
            padding: 10px;
            background-color: #eaf2f8;
            border: 1px solid #cce5ff;
            text-align: center;
        }

        .attachment a {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }

        .attachment a:hover {
            text-decoration: underline;
        }

        @media only screen and (max-width: 600px) {
            .container {
                padding: 15px;
            }

            .info-table th,
            .info-table td {
                font-size: 12px;
                padding: 6px 10px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h3>Ready Bill Submission: {{ $title }}</h3>
        <p><strong>Description:</strong> {{ $description }}</p>

        <p><strong>Submitted By:</strong></p>
        <table class="info-table">
            <tr>
                <th>Entity ID</th>
                <td>{{ $entity_id }}</td>
            </tr>
            <tr>
                <th>Name</th>
                <td>{{ $shopName }}</td>
            </tr>
            <tr>
                <th>Shop Name</th>
                <td>{{ $shopBusinessName }}</td>
            </tr>
            <tr>
                <th>Email ID</th>
                <td>{{ $shopEmail }}</td>
            </tr>
            <tr>
                <th>Mobile Number</th>
                <td>{{ $shopMobileNumber }}</td>
            </tr>
        </table>

        @if($attachment !== 'NA')
            <div class="attachment">
                <p><strong>Attachment:</strong> <a href="{{ asset('storage/attachment/' . $attachment) }}"
                        target="_blank">Download the attachment</a></p>
            </div>
        @endif
    </div>
</body>

</html>