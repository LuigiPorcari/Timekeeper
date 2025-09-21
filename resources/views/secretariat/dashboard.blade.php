<x-layout documentTitle="Segreteria — Dashboard">
    <main class="container mt-5 pt-5" id="main-content" aria-labelledby="sec-title">
        <h1 id="sec-title" class="mb-4">Area Segreteria</h1>

        <div class="row g-4">
            <!-- RIEPILOGO -->
            <div class="col-12 col-lg-6">
                <div class="card tk-card h-100">
                    <div class="card-header tk-card-header">
                        <h2 class="h5 mb-0">Riepilogo</h2>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 text-center">
                            <div class="col-12 col-sm-4">
                                <div class="tk-kpi">
                                    <div class="tk-kpi-num">{{ $racesCount }}</div>
                                    <div class="small text-uppercase">Gare totali</div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="tk-kpi">
                                    <div class="tk-kpi-num">{{ $timekeepersCount }}</div>
                                    <div class="small text-uppercase">Cronometristi</div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="tk-kpi">
                                    <div class="tk-kpi-num">{{ $recordsCount }}</div>
                                    <div class="small text-uppercase">Record</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LINK RAPIDI REPORT -->
            <div class="col-12 col-lg-6">
                <div class="card tk-card h-100">
                    <div class="card-header tk-card-header">
                        <h2 class="h5 mb-0">Report</h2>
                    </div>
                    <div class="card-body d-flex flex-wrap gap-2">
                        <a class="btn btn-primary d-inline-flex align-items-center"
                            href="{{ route('secretariat.races.index') }}">
                            <i class="fas fa-flag-checkered me-2"></i>
                            Report Gare
                        </a>
                        <a class="btn btn-primary d-inline-flex align-items-center"
                            href="{{ route('secretariat.timekeepers.index') }}">
                            <i class="fas fa-stopwatch me-2"></i>
                            Report Cronometristi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-layout>
