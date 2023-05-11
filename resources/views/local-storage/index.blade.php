@extends('layouts.app')



@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <script>
        Chart.register(ChartDataLabels);
        const data = {
            labels: ['Used', 'Free'],
            datasets: [{
                label: 'Local Storage',
                data: [{{ $localDiskInfo['used'] }}, {{ $localDiskInfo['free'] }}]
            }]
        };
        const config = {
            type: 'pie',
            data: data,
            options: {
                responsive: true,
                tooltips: {
                    enabled: false
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Local Storage Status'
                    },
                    datalabels: {
                        formatter: (value, ctx) => {
                            let sum = 0;
                            let dataArr = ctx.chart.data.datasets[0].data;
                            dataArr.map(data => {
                                sum += data;
                            });
                            let percentage = (value * 100 / sum).toFixed(2) + "%";
                            return percentage;
                        },
                        color: '#fff',
                    }
                },


            },
        };
        const myChart = new Chart(document.getElementById("myChart"), config);
    </script>
@endpush

@section('content')
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body" >
                    <canvas id="myChart" class="mx-auto"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-sm-6 col-xl-4 mb-4">
            <div class="card border-0 shadow">
                <div class="card-body">
                    <div class="row d-block d-xl-flex align-items-center">

                        <div class="col-12 col-xl-7 px-xl-0">
                            <div class="d-sm-block">
                                <h2 class="h5">Total Size</h2>
                                <h3 class="fw-extrabold mb-1">
                                    {{ number_format($localDiskInfo['total'], 2) }} GB
                                </h3>
                            </div>
                            <small class="d-flex align-items-center">Total Penyimpanan Server Lokal</small>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 mb-4">
            <div class="card border-0 shadow">
                <div class="card-body">
                    <div class="row d-block d-xl-flex align-items-center">

                        <div class="col-12 col-xl-7 px-xl-0">
                            <div class="d-sm-block">
                                <h2 class="h5">Total Usage</h2>
                                <h3 class="fw-extrabold mb-1">
                                    {{ number_format($localDiskInfo['used'], 2) }} GB
                                </h3>
                            </div>
                            <small class="d-flex align-items-center">Total Penyimpanan Yang Di Pakai</small>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 mb-4">
            <div class="card border-0 shadow">
                <div class="card-body">
                    <div class="row d-block d-xl-flex align-items-center">

                        <div class="col-12 col-xl-7 px-xl-0">
                            <div class="d-sm-block">
                                <h2 class="h5">Total Available</h2>
                                <h3 class="fw-extrabold mb-1">
                                    {{ number_format($localDiskInfo['free'], 2) }} GB
                                </h3>
                            </div>
                            <small class="d-flex align-items-center">Total Penyimpanan Yang Tersedia</small>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
