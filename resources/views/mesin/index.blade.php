<!DOCTYPE html>
<html>
<head>
    <title>Daftar Mesin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <div class="row">
        @foreach ($machines as $machine)
            <div class="col-md-4">
                <div class="card mb-3 shadow">
                    <div class="card-body">
                        <h5 class="card-title">Mesin: {{ $machine->machine_number }}</h5>
                        <p class="card-text">Item: {{ $machine->item_number }}</p>
                        <p class="card-text">
                            Operasi: {{ $machine->operation_start }} - {{ $machine->operation_end }}
                        </p>
                        <span class="badge bg-{{ now()->between($machine->operation_start, $machine->operation_end) ? 'danger' : 'success' }}">
                            {{ now()->between($machine->operation_start, $machine->operation_end) ? 'Tidak Tersedia' : 'Tersedia' }}
                        </span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
</body>
</html>
