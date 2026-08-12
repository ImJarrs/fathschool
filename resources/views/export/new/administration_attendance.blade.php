<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ __('Administration Attendance Report') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .report-table { width: 100%; border-collapse: collapse; font-size: 12px; }
        .report-table th, .report-table td { padding: 8px 10px; border: 1px solid #ccc; text-align: center; }
        .report-table thead { background: #F3F4F6; font-weight: 600; }
        .main-title { text-align: center; font-size: 20px; margin-bottom: 12px; }
    </style>
    <style>
        @page { size: A4 landscape; margin: 6mm; }
    </style>
</head>
<body>
    <div>
        <div>
            <h2 class="main-title">{{ $settings->app_name ?? '' }} - {{ __('Administration Attendance') }}</h2>
        </div>
        <div>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Department') }}</th>
                        @foreach($dates as $date)
                            <th>{{ date('j/n/Y', strtotime($date)) }}</th>
                        @endforeach
                        <th>Hadir</th>
                        <th>Ijin</th>
                        <th>Alfa</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($administrationsAttendance as $index => $admin)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td style="text-align: left;">{{ $admin['user_name'] }}</td>
                            <td style="text-align: left;">{{ $admin['department'] }}</td>
                            @foreach($admin['attendance'] as $d)
                                <td>{{ $d }}</td>
                            @endforeach
                            <td>{{ $admin['summary']['hadir'] }}</td>
                            <td>{{ $admin['summary']['ijin'] }}</td>
                            <td>{{ $admin['summary']['alfa'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
