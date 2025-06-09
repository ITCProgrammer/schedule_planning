@extends('layouts.main')

@section('title', 'Dashboard')
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

@section('content')
  @php
      use Carbon\Carbon;
  @endphp
    <style>
      .calculation-table td {
        padding: 4px 8px; /* Lebih rapat */
        vertical-align: top;
      }

      .calculation-table .table-active {
        background-color: #f0f0f0;
        font-weight: bold;
      }

      .calculation-title {
        font-weight: bold;
        color: #007bff;
        margin-bottom: 10px;
      }
    </style>
    <!-- Begin Page Content -->
    <div class="container-fluid">
      <!-- Page Heading -->
      <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
      </div>
      <!-- Content Row -->
      <div class="row">
        <div class="col-8 mb-4">
          <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <h4 class="mb-2 font-weight-bold text-primary">Form Input</h4>
                  <!-- <div class="text-xs font-weight-bold text-primary text-uppercase mb-1"> Form Input</div> -->
                  <div class="row">
                    {{-- <div class="col-xl-3 col-md-6 mb-3">
                      <label for="no_item" class="form-label">Item </label>
                      <select class="form-control select2" id="select2Code" name="no_item">
                        <option value="">Pilih No. Item</option>
                        @foreach ($filter as $item)
                        <option value="{{ $item->hanger }}">{{ $item->hanger }} |
                          {{ $item->longdescription }}
                        </option>
                        @endforeach
                      </select>
                    </div> --}}
                    <div class="col-xl-3 col-md-6 mb-3">
                      <label for="no_item" class="form-label">Item</label>
                      <select class="form-control select2" id="select2Code" name="no_item"></select>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-3">
                      <label for="txQty" class="form-label">Qty / Kg</label>
                      <input type="number" class="form-control" id="txQty" placeholder="0">
                    </div>
                    <div class="col-xl-3 col-md-6 mb-3">
                      <label for="tsDate" class="form-label">Tanggal Start</label>
                      <input type="date" class="form-control" id="tsDate" placeholder="mm-dd-yyyy">
                    </div>
                    <div class="col-xl-3 col-md-6 mb-3">
                      <label for="txDate" class="form-label">Tanggal Delivery</label>
                      <input type="date" class="form-control" id="txDate" placeholder="mm-dd-yyyy">
                    </div>
                    <div class="col-xl-4 col-md-6 mb-3">
                      <label for="txDate" class="form-label">Hari Libur Selain Minggu</label>
                      <input type="number" class="form-control" id="txRedDate">
                    </div>
                  </div>
                  {{-- <div class="position-absolute bottom-0 end-0">
                    <a href="#" class="btn btn-sm btn-primary shadow-sm">Generate Report</a>
                  </div> --}}
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-4 mb-4">
          <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
              <div class="calculation-title">Calculation</div>
                <table class="table table-borderless calculation-table mb-3">
                <tbody>
                  <tr>
                    <td><strong>Kode Item:</strong></td>
                    <td><span id="calc_no_item">-</span></td>
                  </tr>
                  <tr>
                    <td><strong>Tanggal Mulai Produksi:</strong></td>
                    <td><span id="start_date">-</span></td>
                  </tr>
                  <tr>
                    <td><strong>Tanggal Pengiriman:</strong></td>
                    <td><span id="calc_date">-</span></td>
                  </tr>
                  <tr>
                    <td><strong>Total Qty:</strong></td>
                    <td><span id="qty">-</span></td>
                  </tr>
                  <tr class="table-active">
                    <td colspan="2"><strong>Kapasitas Produksi Mesin:</strong></td>
                  </tr>
                  <tr>
                    <td>* Per Jam (per mesin):</td>
                    <td><span id="calc_qtyh">-</span></td>
                  </tr>
                  <tr>
                    <td>* Per Hari (per mesin):</td>
                    <td><span id="calc_qty">-</span></td>
                  </tr>
                  <tr>
                    <td><strong>Jumlah Hari Kerja:</strong></td>
                    <td><span id="calc_days">-</span></td>
                  </tr>
                  <tr>
                    <td><strong>Jumlah Mesin yang Dibutuhkan:</strong></td>
                    <td><span id="calc_machine">-</span></td>
                  </tr>
                </tbody>
              </table>
              <div class="d-grid">
                <button type="button" class="btn btn-primary" id="btSubmit">Submit</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-xl-6 col-lg-7">
          <div class="card shadow mb-4">
            <!-- Card Header - Dropdown -->
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
              <h6 class="m-0 font-weight-bold text-primary">Mechine List</h6>
            </div>
            <!-- Card Body -->
            <div class="card-body">
              <div class="row" id="available-machines">
                  @foreach ($groupedMesin as $jenis => $listMesin)
                    <div class="col-12 mb-1">
                      <div class="card border-left-info shadow">
                        <div class="card-header bg-info text-white" style="cursor: pointer;"
                            data-bs-toggle="collapse" 
                            data-bs-target="#collapseJenis{{ Str::slug($jenis) }}" 
                            aria-expanded="true" 
                            aria-controls="collapseJenis{{ Str::slug($jenis) }}">
                          <strong>Jenis Mesin: {{ $jenis }}</strong>
                        </div>
                        <div id="collapseJenis{{ Str::slug($jenis) }}" class="collapse hide">
                          <div class="d-flex flex-wrap gap-3 justify-content-start" style="max-height: 600px; overflow-y: auto;">
                            <div class="card-body">
                              <div class="row">
                                @foreach ($listMesin as $machine)
                                  @php
                                    $isAvailable = is_null($machine->end_date) || Carbon::now()->gt(Carbon::parse($machine->end_date));
                                  @endphp
                                  <div class="col-xl-3 col-md-6 mb-2" onclick="moveToSelected(this)" data-id="{{ $machine->id }}" data-code="{{ $machine->mesin_code }}" data-item="{{ $machine->item_code }}" data-start="{{ $machine->start_date }}" data-end="{{ $machine->end_date }}" data-jenis="{{ Str::slug($jenis) }}">
                                    <div class="card border-left-primary shadow-sm h-100 py-1" style="margin-bottom: 8px;">
                                      <div class="card-body px-2 py-2" style="line-height: 1.2;">
                                        <div class="row no-gutters align-items-center">
                                          <div class="col mr-2">
                                            <!-- Kode Mesin -->
                                            <div class="text-primary fw-bold text-uppercase" style="font-size: 0.85rem; margin-bottom: 2px;">
                                              {{ $machine->mesin_code }}
                                            </div>
                                                            
                                            <!-- Status -->
                                            <div class="{{ $isAvailable ? 'text-success' : 'text-danger' }}" style="font-size: 0.85rem; margin-bottom: 2px;">
                                              {{ $isAvailable ? 'Available' : 'Unavailable' }}
                                            </div>
                                                            
                                            <!-- Keterangan -->
                                            @if (!$isAvailable)
                                              <div class="text-muted" style="font-size: 0.75rem; margin-bottom: 1px;">
                                                {{ $machine->item_code }}
                                              </div>
                                              <div class="text-muted" style="font-size: 0.75rem;">
                                                Until {{ \Carbon\Carbon::parse($machine->end_date)->format('Y-m-d') }}
                                              </div>
                                            @endif
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                @endforeach
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  @endforeach
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-6 col-lg-7">
          <div class="card shadow mb-4">
            <!-- Card Header - Dropdown -->
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
              <h6 class="m-0 font-weight-bold text-primary">Selected Mechine</h6>
            </div>
            <!-- Card Body -->
            <div class="card-body">
              <div class="row" id="selected-machines">
                
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- /.container-fluid -->
@endsection
@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  {{-- Perhitungan libur dan machine nembak ke api / database --}}

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const selectItem = $('#select2Code');
        const inputQty = document.getElementById('txQty');
        const inputDate1 = document.getElementById('tsDate');
        const inputDate2 = document.getElementById('txDate');
        const inputRedDate = document.getElementById('txRedDate');

        const spanNoItem = document.getElementById('calc_no_item');
        // const spanMcQty = document.getElementById('mc_qty');
        const spanQtyInpt = document.getElementById('qty');
        const spanQtyH = document.getElementById('calc_qtyh');
        const spanQty = document.getElementById('calc_qty');
        const spanDateS = document.getElementById('start_date');
        const spanDate = document.getElementById('calc_date');
        const spanDays = document.getElementById('calc_days');
        const spanMachine = document.getElementById('calc_machine');
        
        selectItem.select2({
          placeholder: 'Pilih No. Item',
          ajax: {
            url: "{{ route('schedule.filterdata') }}",
            dataType: 'json',
            delay: 250,
            data: function(params) {
              return {
                q: params.term || ''
              };
            },
            processResults: function(data) {
              return {
                results: data
              };
            },
            cache: true
          },
          minimumInputLength: 0
        });

        $.ajax({
          url: "{{ route('schedule.filterdata') }}",
          data: {
            q: ''
          },
          success: function(data) {
            const initialItems = data.slice(0, 5);
            initialItems.forEach(function(item) {
              const option = new Option(item.text, item.id, false, false);
              selectItem.append(option);
            });
            selectItem.trigger('change');
          }
        });

        function sendCalculationRequest() {
          const noItem = selectItem.val();
          const qty = inputQty.value;
          const date1 = inputDate1.value;
          const date2 = inputDate2.value;
          const redDays = parseInt(inputRedDate.value || '0');

          if (!noItem || !qty || !date1 || !date2) return;

          // ✅ Reset mesin yang sudah dipilih
          resetSelectedMachines();

          const url = new URL("{{ route('home.calculation') }}");
          url.searchParams.append("no_item", noItem);
          url.searchParams.append("txQty", qty);
          url.searchParams.append("date1", date1);
          url.searchParams.append("date2", date2);

          fetch(url)
            .then(response => response.json())
            .then(data => {
              spanNoItem.textContent = data.no_item || '-';
              // spanMcQty.textContent = data.jumlah_1mesin || '-';
              spanQtyInpt.textContent = data.qty || '-';
              spanQtyH.textContent = data.calday || '-';
              spanQty.textContent = data.calculation || '-';
              spanDateS.textContent = data.start_date || '-';
              spanDate.textContent = data.delivery_date || '-';
              const rawWorkdays = parseInt(data.workdays_until_delivery || 0);
              const effectiveWorkdays = Math.max(rawWorkdays - redDays, 1);
            
              spanDays.textContent = effectiveWorkdays;
              const jumlahPerMesin = parseFloat(data.jumlah_1mesin || 0);
              const mesinDibutuhkan = Math.ceil(jumlahPerMesin / effectiveWorkdays);
            
              spanMachine.textContent = mesinDibutuhkan;
            
            })
            .catch(error => {
              console.error("Gagal fetch:", error);
            });
        }

        selectItem.on('change', sendCalculationRequest);
        inputQty.addEventListener('input', sendCalculationRequest);
        inputDate1.addEventListener('change', sendCalculationRequest);
        inputDate2.addEventListener('change', sendCalculationRequest);
        inputRedDate.addEventListener('input', sendCalculationRequest);
      });
    </script>

    <script>
      let selectedMachines = [];
      let machineRequired = 0;
      let usedMesin = 0;

      function parseDate(str) {
        return str ? new Date(str) : null;
      }

      function isMachineAvailable(machineStart, machineEnd, inputStart, inputEnd) {
        const ms = parseDate(machineStart);
        const me = parseDate(machineEnd);
        if (!ms || !me) return true;
        const isOverlap = inputStart <= me && inputEnd >= ms;

        return !isOverlap;
      }

      function refreshMachineAvailability() {
        const inputStart = parseDate(document.getElementById('tsDate').value);
        const inputEnd = parseDate(document.getElementById('txDate').value);

        const allMachines = document.querySelectorAll('#available-machines .col-xl-3');

        allMachines.forEach(card => {
          const start = card.dataset.start;
          const end = card.dataset.end;

          const isAvailable = isMachineAvailable(start, end, inputStart, inputEnd);

          const statusDiv = card.querySelector('.text-success, .text-danger');
          if (isAvailable) {
            statusDiv.classList.remove('text-danger');
            statusDiv.classList.add('text-success');
            statusDiv.textContent = 'Available';
          } else {
            statusDiv.classList.remove('text-success');
            statusDiv.classList.add('text-danger');
            statusDiv.textContent = 'Unavailable';
          }

          card.style.pointerEvents = isAvailable ? 'auto' : 'none';
          card.style.opacity = isAvailable ? '1' : '0.5';
        });
      }

      document.getElementById('tsDate').addEventListener('change', refreshMachineAvailability);
      document.getElementById('txDate').addEventListener('change', refreshMachineAvailability);

      function moveToSelected(card) {
        const machineId = card.dataset.id;
        const machineCode = card.dataset.code;

        const spanMachine = document.getElementById('calc_machine');
        let machineRequired = parseInt(spanMachine.textContent || '0');

        if (!machineId) {
          alert("Card tidak memiliki data-id!");
          return;
        }

        if (isNaN(machineRequired) || machineRequired <= 0) {
          alert("Jumlah mesin yang dibutuhkan sudah tercapai.");
          return;
        }

        if (selectedMachines.includes(machineId)) {
          alert("Mesin sudah dipilih!");
          return;
        }
        const clonedCard = card.cloneNode(true);
        clonedCard.onclick = null;
        clonedCard.style.position = 'relative';

        const closeBtn = document.createElement('button');
        closeBtn.textContent = '×';
        closeBtn.className = 'btn btn-sm btn-danger';
        closeBtn.style.position = 'absolute';
        closeBtn.style.top = '2px';
        closeBtn.style.right = '2px';
        closeBtn.style.zIndex = '10';
        closeBtn.onclick = function() {
          cancelSelected(clonedCard, machineId, card);
        };

        clonedCard.style.position = 'relative';
        clonedCard.appendChild(closeBtn);
        document.getElementById('selected-machines').appendChild(clonedCard);
        selectedMachines.push(machineId);
        card.remove();
        usedMesin ++;
        machineRequired -= 1;
        spanMachine.textContent = machineRequired;
        // console.log(usedMesin);

      }

      function cancelSelected(clonedCard, machineId, originalCard) {
        clonedCard.remove();
        const jenis = originalCard.dataset.jenis;
        const containerId = 'collapseJenis' + jenis;
        const container = document.getElementById(containerId)?.querySelector('.row');

        if (container) {
          container.appendChild(originalCard);
        } else {
          console.warn('Container for jenis', jenis, 'not found!');
          document.getElementById('available-machines').appendChild(originalCard);
        }
      
        selectedMachines = selectedMachines.filter(id => id !== machineId);
        const spanMachine = document.getElementById('calc_machine');
        let currentRequired = parseInt(spanMachine.textContent || '0');
        if (usedMesin > 0) {
          usedMesin--;
        }
        spanMachine.textContent = currentRequired + 1;
        refreshMachineAvailability();
      }

      function sendCalculationRequest() {
        const noItem = $('#select2Code').val();
        const qty = document.getElementById('txQty').value;
        const date1 = document.getElementById('tsDate').value;
        const date2 = document.getElementById('txDate').value;

        if (!noItem || !qty || !date1 || !date2) return;

        const url = new URL("{{ route('home.calculation') }}");
        url.searchParams.append("no_item", noItem);
        url.searchParams.append("txQty", qty);
        url.searchParams.append("date1", date1);
        url.searchParams.append("date2", date2);

        fetch(url)
        .then(response => response.json())
        .then(data => {
          document.getElementById('calc_machine').textContent = data.kebutuhan_mesin || '-';
          machineRequired = parseInt(data.kebutuhan_mesin || 0);
          
          // Reset selected machine jika terlalu banyak
          if (selectedMachines.length > machineRequired) {
            alert("Jumlah mesin yang dipilih melebihi kebutuhan. Silakan reset.");
          }
          refreshMachineAvailability();
        })
        .catch(error => {
          console.error("Gagal fetch:", error);
        });
      }

      function resetSelectedMachines() {
        const selectedContainer = document.getElementById("selected-machines");
        const selectedCards = selectedContainer.querySelectorAll(".col-xl-3");

        selectedCards.forEach(card => {
          const closeBtn = card.querySelector('button');
          if (closeBtn) closeBtn.remove();

          card.style.position = '';

          const jenis = card.dataset.jenis;
          const containerId = 'collapseJenis' + jenis;
          const container = document.getElementById(containerId)?.querySelector('.row');

          // 👇 Bind ulang onclick sebelum dikembalikan
          card.onclick = function () {
            moveToSelected(this);
          };

          if (container) {
            container.appendChild(card);
          } else {
            console.warn('Jenis mesin tidak ditemukan untuk:', jenis);
            document.getElementById("available-machines").appendChild(card);
          }
        });

        selectedMachines = [];
        usedMesin = 0;
        document.getElementById('calc_machine').textContent = '-';
        refreshMachineAvailability();
      }

    </script>

    <script>
      document.getElementById('btSubmit').addEventListener('click', function() {
        const noItem = $('#select2Code').val();
        const qty = document.getElementById('txQty').value;
        const startDate = document.getElementById('tsDate').value;
        const endDate = document.getElementById('txDate').value;
        const qtyPerDay = document.getElementById('calc_qty').textContent;
        const type = "Sche Plann";

        // const dayNeed = document.getElementById('mc_qty').textContent;
        const dayPlann = document.getElementById('calc_qty').textContent;

        const selectedMachineCards = document.querySelectorAll('#selected-machines .col-xl-3');
        const machines = Array.from(selectedMachineCards)
        .map(card => card.dataset.code)
        .filter(Boolean);

        if (!noItem || !qty || !startDate || !endDate || machines.length === 0 || !qtyPerDay) {
          alert('Pastikan semua input dan mesin telah dipilih.');
          return;
        }

        fetch("{{ route('schedule.store') }}", {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({
            no_item: noItem,
            qty: parseInt(qty),
            qty_day: parseInt(qtyPerDay),
            start_date: startDate,
            delivery_date: endDate,
            machines: machines,
            type: type
          })
        })
        .then(response => {
          if (!response.ok) throw new Error('Server error: ' + response.status);
          return response.json();
        })
        .then(data => {
          if (data.status === 'Success') {
            alert('✅ Data berhasil disimpan!');
            location.reload();
          } else {
            alert('⚠️ Gagal menyimpan data.');
          }
        })
        .catch(error => {
          console.error('❌ Error:', error);
          alert('Terjadi kesalahan saat mengirim data.');
        });
      });
    </script>
@endpush


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
  .btn-danger {
    font-weight: bold;
    padding: 0 8px;
    line-height: 1;
  }
</style>
