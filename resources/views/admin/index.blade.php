@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-download fa-sm text-white-50 me-1"></i>Generate Report
            </a>
        </div>

        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card card border-start border-primary border-4">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Users</div>
                                <div class="h5 mb-0 fw-bold text-gray-800">{{ $totalUsers }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card card border-start border-success border-4">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col">
                                <div class="text-xs fw-bold text-success text-uppercase mb-1">Total Savings</div>
                                <div class="h5 mb-0 fw-bold text-gray-800">KSh {{ number_format($totalSavings, 2) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card card border-start border-warning border-4">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col">
                                <div class="text-xs fw-bold text-warning text-uppercase mb-1">Pending Withdrawals</div>
                                <div class="h5 mb-0 fw-bold text-gray-800">{{ $pendingWithdrawals }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card card border-start border-danger border-4">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col">
                                <div class="text-xs fw-bold text-danger text-uppercase mb-1">Active Loans</div>
                                <div class="h5 mb-0 fw-bold text-gray-800">KSh {{ number_format($activeLoans, 2) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Charts Row -->
        <div class="row">
            <!-- User Growth Chart -->
            <div class="col-xl-6 col-lg-7">
                <div class="chart-card card mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 fw-bold text-primary">User Growth</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <div id="userGrowthChart"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Savings Trend Chart -->
            <div class="col-xl-6 col-lg-5">
                <div class="chart-card card mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 fw-bold text-primary">Savings Trends</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <div id="savingsTrendChart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-bold text-primary">Recent Activities</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>User</th>
                                        <th>Activity</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentActivities as $activity)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}</td>
                                            <td>{{ $activity->user->fullname }}</td>
                                            <td>{{ $activity->type }}</td>
                                            <td><span
                                                    class="badge bg-{{ $activity->status === 'completed' ? 'success' : ($activity->status === 'failed' ? 'danger' : 'secondary') }}">{{ ucwords(str_replace('_', ' ', $activity->status)) }}</span>
                                            </td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-bold text-primary">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('admin.users') }}" class="btn btn-primary">
                                <i class="fas fa-users me-1"></i> Manage Users
                            </a>
                            <a href="{{ route('admin.withdrawals') }}" class="btn btn-warning">
                                <i class="fas fa-money-bill-wave me-1"></i> Review Withdrawals
                            </a>
                            <a href="{{ route('admin.loans') }}" class="btn btn-success">
                                <i class="fas fa-hand-holding-usd me-1"></i> Review Loans
                            </a>
                            <a href="{{ route('admin.support') }}" class="btn btn-info text-white">
                                <i class="fas fa-headset me-1"></i> View Support Tickets
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        const userGrowthOptions = {
            series: [{
                name: 'Users',
                data: @json($userGrowthData)
            }],
            chart: {
                type: 'line',
                height: 350,
                toolbar: {
                    show: false
                }
            },
            colors: ['#4e73df'],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            grid: {
                borderColor: '#e7e7e7',
                row: {
                    colors: ['#f3f3f3', 'transparent'],
                    opacity: 0.5
                }
            },
            xaxis: {
                categories: @json($userGrowthMonths)
            },
            tooltip: {
                theme: 'dark'
            }
        };

        const savingsTrendOptions = {
            series: [{
                name: 'Savings',
                data: @json($savingsTrendData)
            }],
            chart: {
                type: 'area',
                height: 350,
                toolbar: {
                    show: false
                }
            },
            colors: ['#1cc88a'],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.3
                }
            },
            xaxis: {
                categories: @json($savingsTrendMonths)
            },
            tooltip: {
                theme: 'dark'
            }
        };

        const userGrowthChart = new ApexCharts(document.querySelector("#userGrowthChart"), userGrowthOptions);
        const savingsTrendChart = new ApexCharts(document.querySelector("#savingsTrendChart"), savingsTrendOptions);

        userGrowthChart.render();
        savingsTrendChart.render();
    </script>
@endpush
