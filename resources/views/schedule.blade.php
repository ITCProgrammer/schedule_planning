@extends('layouts.main')
@section('title', 'Schedule')

@section('content')

    <h2>Production Schedule Date</h2>

    <ul class="nav nav-tabs mb-3" id="tabView" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="tabel-tab" data-bs-toggle="tab" data-bs-target="#tabelView"
                type="button">Table</button>
        </li>
        {{-- <li class="nav-item">
        <button class="nav-link" id="kalender-tab" data-bs-toggle="tab" data-bs-target="#kalenderView" type="button">Kalender</button>
    </li> --}}
    </ul>
    <table id="mesinTable" class="table table-bordered display nowrap" style="width:100%">
        <thead>
            <tr>
                <th>Machine</th>
                @foreach ($tanggalKerja as $tgl)
                    <th>{{ $tgl->format('d-M') }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($dataPerMesin as $mesin => $produksiPerTanggal)
                <tr>
                    <td>{{ $mesin }}</td>
                    @foreach ($tanggalKerja as $tgl)
                        @php
                            $tanggalString = $tgl->toDateString();
                            $data = $produksiPerTanggal[$tanggalString] ?? null;
                            $bgColor = $data['color'] ?? '';
                        @endphp
                        <td>
                            @if ($data)
                                {{-- <div style="width: 20px; height: 20px; border-radius: 50%; background-color: {{ $bgColor }};"></div> --}}
                                <!-- {{ $data['text'] }} -->
                                  {!! nl2br(e($data['text'])) !!}
                            @else
                                -
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@push('scripts')
    <!-- FullCalendar CSS & JS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
    <!-- DataTables CSS & JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- FixedColumns & FixedHeader extensions -->
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.min.css">
    <script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.dataTables.min.css">
    <script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                timeZone: 'UTC',
                initialView: 'multiMonthYear',
                editable: true,
                events: 'https://fullcalendar.io/api/demo-feeds/events.json'
            });

            calendar.render();
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#mesinTable').DataTable({
                scrollY: "1000000px",
                scrollX: true,
                scrollCollapse: true,
                paging: false,
                searching: false,
                fixedColumns: {
                    leftColumns: 1
                },
                fixedHeader: true,
            });
        });
    </script>
@endpush
