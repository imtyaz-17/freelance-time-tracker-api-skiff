<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header h2 {
            font-size: 18px;
            font-weight: normal;
            margin-top: 0;
            color: #666;
        }
        .info {
            margin-bottom: 20px;
        }
        .info table {
            width: 100%;
        }
        .info td {
            padding: 5px;
        }
        .info .label {
            font-weight: bold;
            width: 150px;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data th {
            background-color: #f2f2f2;
            text-align: left;
            padding: 8px;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        table.data td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        table.data tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .totals {
            text-align: right;
            font-weight: bold;
            margin-top: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        @if($subtitle)
            <h2>{{ $subtitle }}</h2>
        @endif
    </div>
    
    <div class="info">
        <table>
            <tr>
                <td class="label">Generated For:</td>
                <td>{{ $user->name }} ({{ $user->email }})</td>
                <td class="label">Date Range:</td>
                <td>{{ $fromDate }} to {{ $toDate }}</td>
            </tr>
            <tr>
                <td class="label">Generated At:</td>
                <td>{{ $generatedAt }}</td>
                <td class="label">Grouped By:</td>
                <td>{{ ucfirst($groupBy) }}</td>
            </tr>
        </table>
    </div>
    
    @if($groupBy == 'day')
        <table class="data">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Hours</th>
                    <th>Projects</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $day)
                <tr>
                    <td>{{ $day['date'] }}</td>
                    <td>{{ abs($day['total_hours']) }}</td>
                    <td>
                        @php
                            $projects = [];
                            foreach($day['time_logs'] as $log) {
                                if(!in_array($log['project']['title'], $projects)) {
                                    $projects[] = $log['project']['title'];
                                }
                            }
                            echo implode(', ', $projects);
                        @endphp
                    </td>
                    <td>
                        @foreach($day['time_logs'] as $log)
                            <div>
                                {{ $log['project']['title'] }}: {{ $log['description'] }}
                                ({{ abs($log['hours']) }} hours)
                                {{ $log['is_billable'] ? '(Billable)' : '(Non-billable)' }}
                            </div>
                        @endforeach
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($groupBy == 'week')
        <table class="data">
            <thead>
                <tr>
                    <th>Week</th>
                    <th>Hours</th>
                    <th>Projects</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $week)
                <tr>
                    <td>{{ $week['week'] }}</td>
                    <td>{{ abs($week['total_hours']) }}</td>
                    <td>
                        @php
                            $projects = [];
                            foreach($week['time_logs'] as $log) {
                                if(!in_array($log['project']['title'], $projects)) {
                                    $projects[] = $log['project']['title'];
                                }
                            }
                            echo implode(', ', $projects);
                        @endphp
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($groupBy == 'month')
        <table class="data">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Hours</th>
                    <th>Projects</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $month)
                <tr>
                    <td>{{ $month['month'] }}</td>
                    <td>{{ abs($month['total_hours']) }}</td>
                    <td>
                        @php
                            $projects = [];
                            foreach($month['time_logs'] as $log) {
                                if(!in_array($log['project']['title'], $projects)) {
                                    $projects[] = $log['project']['title'];
                                }
                            }
                            echo implode(', ', $projects);
                        @endphp
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($groupBy == 'project')
        <table class="data">
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Client</th>
                    <th>Hours</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $project)
                <tr>
                    <td>{{ $project['project_title'] }}</td>
                    <td>
                        @if(isset($project['time_logs'][0]['project']['client']['name']))
                            {{ $project['time_logs'][0]['project']['client']['name'] }}
                        @endif
                    </td>
                    <td>{{ abs($project['total_hours']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($groupBy == 'client')
        <table class="data">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Hours</th>
                    <th>Projects</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $client)
                <tr>
                    <td>{{ $client['client_name'] }}</td>
                    <td>{{ abs($client['total_hours']) }}</td>
                    <td>
                        @php
                            $projects = [];
                            foreach($client['time_logs'] as $log) {
                                if(!in_array($log['project']['title'], $projects)) {
                                    $projects[] = $log['project']['title'];
                                }
                            }
                            echo implode(', ', $projects);
                        @endphp
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    
    <div class="totals">
        Total Hours: {{ $totalHours }}
    </div>
    
    <div class="footer">
        Generated by Freelance Time Tracker | {{ $generatedAt }}
    </div>
</body>
</html> 