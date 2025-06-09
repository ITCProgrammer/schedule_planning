@extends('layouts.main')
@section('title', 'Schedule')

@section('content')

  @php
    use Carbon\Carbon;
  @endphp

  <ul class="nav nav-tabs">
    <li class="nav-item">
      <a class="nav-link active" aria-current="page" href="{{ route('poList') }}">Forecast & PO Greige Plann</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="{{ route('scheList') }}">Schedule List Machine</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="{{ route('forecastList') }}">Detail Product</a>
    </li>
    <!-- <li class="nav-item">
      <a class="nav-link" href="{{ route('view') }}">Shcedule Plann</a>
    </li> -->
  </ul>

  <div class="card shadow-sm">
    <div class="card-header">
      <h5 class="card-title mb-0" data-bs-toggle="collapse" href="#PoGreige" role="button" aria-expanded="false">PO Greige</h5>
    </div>
    <div class="card-body table-responsive collapse" id="PoGreige">
      <table id="itemTablePo" class="table table-bordered table-striped">
        <thead>
          <tr id="tableHeadPo">
            <th>Keterangan</th>
          </tr>
        </thead>
        <tbody id="tableBodyPo">
          <!-- Data baris akan ditambahkan di sini -->
        </tbody>
      </table>
    </div>
     <div class="card-header">
      <h5 class="card-title mb-0" data-bs-toggle="collapse" href="#Forecast" role="button" aria-expanded="false">Forecast</h5>
    </div>
    <div class="card-body table-responsive collapse" id="Forecast">
      <table id="itemTableForecast" class="table table-bordered table-striped">
        <thead>
          <tr id="tableHeadForecast">
            <th>Keterangan</th>
          </tr>
        </thead>
        <tbody id="tableBodyForecast">
          <!-- Data baris akan ditambahkan di sini -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="dataModal" tabindex="-1" aria-labelledby="dataModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 90%;">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="dataModalLabel">Input Schedule</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="container-fluid">
            <div class="row">
              <div class="col-8 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                  <div class="card-body">
                    <h4 class="mb-2 font-weight-bold text-primary">Form Input</h4>
                    <div class="row">
                      <div class="col-xl-3 col-md-6 mb-3">
                        <label for="inputSubcode" class="form-label">Item</label>
                        <input type="text" class="form-control" id="inputSubcode" name="no_item" readonly>
                      </div>
                      <div class="col-xl-3 col-md-6 mb-3">
                        <label for="txQty" class="form-label">Qty / Kg</label>
                        <input type="number" class="form-control" id="txQty" placeholder="0" readonly>
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
                        <label for="txRedDate" class="form-label">Hari Libur Selain Minggu</label>
                        <input type="number" class="form-control" id="txRedDate">
                      </div>
                      <input type="hidden" id="typeSave" name="typeSave" value="">
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
                  <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Machine List</h6>
                  </div>
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
                                                <div class="text-primary fw-bold text-uppercase" style="font-size: 0.85rem; margin-bottom: 2px;">
                                                  {{ $machine->mesin_code }}
                                                </div>
                                                <div class="{{ $isAvailable ? 'text-success' : 'text-danger' }}" style="font-size: 0.85rem; margin-bottom: 2px;">
                                                  {{ $isAvailable ? 'Available' : 'Unavailable' }}
                                                </div>
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
                  <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Selected Machine</h6>
                  </div>
                  <div class="card-body">
                    <div class="row" id="selected-machines"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  $(document).ready(function () {
    function normalizeDate(date) {
      return new Date(date.getFullYear(), date.getMonth(), date.getDate());
    }

    $.ajax({
      url: "{{ route('loadData') }}",
      method: 'GET',
      dataType: 'json',
      success: function (response) {
        function normalizeDate(date) {
          return new Date(date.getFullYear(), date.getMonth(), date.getDate());
        }
      
        let today = new Date();
        let startDate = new Date(today);
        startDate.setDate(startDate.getDate() - 1);
        let endDate = new Date(today);
        endDate.setFullYear(endDate.getFullYear() + 1);
      
        let currentDate = new Date(startDate);
        let theadRowPo = $('#tableHeadPo');
        let theadRowForecast = $('#tableHeadForecast');
        let dates = [];
      
        while (currentDate <= endDate) {
          let day = currentDate.getDate().toString().padStart(2, '0');
          let month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
          let year = currentDate.getFullYear();
          let formattedDate = `${day}-${month}-${year}`;
          let dayOfWeek = currentDate.getDay();
        
          if (dayOfWeek === 0) {
            theadRowPo.append(`<th style="color: red;">${formattedDate}</th>`);
            theadRowForecast.append(`<th style="color: red;">${formattedDate}</th>`);
          } else {
            theadRowPo.append(`<th>${formattedDate}</th>`);
            theadRowForecast.append(`<th>${formattedDate}</th>`);
          }
        
          dates.push(new Date(currentDate));
          currentDate.setDate(currentDate.getDate() + 1);
        }
      
        // --- PO With Greige Delivery Date ---
          var dataDB2 = response.dataDB2;
          var greigeDeliveryMap = {};
        
          dataDB2.forEach(function (row) {
            let to = normalizeDate(new Date(row.rmp_req_to));
            let dateKey = to.toISOString().split("T")[0];
            let subcode = (row.subcode02 || '') + '-' + (row.subcode03 || '') + '-' + (row.subcode04 || '');
            let dailyQty = parseFloat(row.qty_total || 0);
          
            if (!greigeDeliveryMap[dateKey]) {
              greigeDeliveryMap[dateKey] = {};
            }
          
            greigeDeliveryMap[dateKey][subcode] = (greigeDeliveryMap[dateKey][subcode] || 0) + dailyQty;
          });
        
          let htmlPo = '<tr style="border-top: 3px solid black;"><td><strong>PO With Greige Delivery Date</strong></td>';
          dates.forEach(function (d) {
            let currentDate = normalizeDate(d);
            let key = currentDate.toISOString().split("T")[0];
          
            if (currentDate.getDay() === 0) {
              htmlPo += '<td style="background-color:#f8d7da;"></td>';
            } else {
              let combos = greigeDeliveryMap[key];
              if (combos) {
                let cellContent = '';
                for (const [subcode, qty] of Object.entries(combos)) {
                  cellContent += `
                    <div class="cell-item" data-subcode="${subcode}" data-qty="${qty.toFixed(2)}" data-date="${key}" style="cursor:pointer;">
                      <strong>${subcode}</strong><br>
                      <small>${qty.toFixed(2)}</small>
                    </div>
                    <hr style="margin: 4px 0;">
                  `;
                }
                htmlPo += `<td>${cellContent}</td>`;
              } else {
                htmlPo += '<td></td>';
              }
            }
          });
          htmlPo += '</tr>';
          $('#itemTablePo tbody').empty().append(htmlPo);
        // --- PO With Greige Delivery Date ---

        // --- Forecast ---
        var forecast = response.forecast;
        var currentYear = new Date().getFullYear();
      
        // Buat peta forecast berdasarkan tanggal (1 atau 2 sesuai ketentuan)
        var forecastMap = {};
        var currentYear = new Date().getFullYear();
      
        forecast.forEach(function (f) {
          var buyMonth = parseInt(f.buy_month);
          var firstDay = new Date(currentYear, buyMonth - 1, 1);

          if (firstDay.getDay() === 0) {
            firstDay.setDate(2);
          }
        
          var key = normalizeDate(firstDay).toISOString().split("T")[0];
          let subcode = (f.item_subcode2 || '') + '-' + (f.item_subcode3 || '') + '-' + (f.item_subcode4 || '');
        
          if (!forecastMap[key]) {
            forecastMap[key] = {};
          }
          forecastMap[key][subcode] = (forecastMap[key][subcode] || 0) + parseFloat(f.total_qty_kg || 0);
        });
      
        let htmlForecast = '<tr style="border-top: 3px solid black;"><td><strong>Forecast</strong></td>';
      
        dates.forEach(function (d) {
          let currentDate = normalizeDate(d);
          let key = currentDate.toISOString().split("T")[0];
          let content = '';
        
          if (forecastMap[key]) {
            let cellContent = '';
            for (const [subcode, qty] of Object.entries(forecastMap[key])) {
              cellContent += `
                <div class="cell-item" data-subcode="${subcode}" data-qty="${qty.toFixed(2)}" data-date="${key}" style="cursor:pointer;">
                  <strong>${subcode}</strong><br>
                  <small>${qty.toFixed(2)}</small>
                </div>
                <hr style="margin: 4px 0;">
              `;
            }
            content = cellContent;
          }
        
          if (currentDate.getDay() === 0) {
            htmlForecast += `<td style="background-color:#f8d7da;">${content}</td>`;
          } else {
            htmlForecast += `<td>${content}</td>`;
          }
        });
      
        htmlForecast += '</tr>';
        $('#itemTableForecast tbody').empty().append(htmlForecast);
      
        // Event klik cell PO
        $('#itemTablePo tbody').on('click', '.cell-item', function () {
          let subcode = $(this).data('subcode');
          let qty = $(this).data('qty');
          let date = $(this).data('date');
          $('#typeSave').val("Po Greige");
          $('#inputSubcode').val(subcode);
          $('#txQty').val(qty);
          $('#txDate').val(date);
          $('#dataModal').modal('show');
        });

        // Event klik cell Forecast
        $('#itemTableForecast tbody').on('click', '.cell-item', function () {
          let subcode = $(this).data('subcode');
          let qty = $(this).data('qty');
          let date = $(this).data('date');
          $('#typeSave').val("Forecast");
          $('#inputSubcode').val(subcode);
          $('#txQty').val(qty);
          $('#txDate').val(date);
          $('#dataModal').modal('show');
        });
      
        $('#dataModal').on('hidden.bs.modal', function () {
          $(this).find('input').val('');
          $(this).find('span').text('-');
          $(this).find('input[type="hidden"]').val('');
          resetSelectedMachines();
        });
      }
    });
  });
</script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const selectItem = $('#inputSubcode');
    const inputQty = document.getElementById('txQty');
    const typeSave = document.getElementById('typeSave');
    const inputDate1 = document.getElementById('tsDate');
    const inputDate2 = document.getElementById('txDate');
    const inputRedDate = document.getElementById('txRedDate');

    const spanNoItem = document.getElementById('calc_no_item');
    const spanQtyInpt = document.getElementById('qty');
    const spanQtyH = document.getElementById('calc_qtyh');
    const spanQty = document.getElementById('calc_qty');
    const spanDateS = document.getElementById('start_date');
    const spanDate = document.getElementById('calc_date');
    const spanDays = document.getElementById('calc_days');
    const spanMachine = document.getElementById('calc_machine');

    function sendCalculationRequest() {
      const noItem = selectItem.val();
      const qty = inputQty.value;
      const date1 = inputDate1.value;
      const date2 = inputDate2.value;
      const redDays = parseInt(inputRedDate.value || '0');

      if (!noItem || !qty || !date1 || !date2) return;

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

    inputDate1.addEventListener('change', sendCalculationRequest);
    inputDate2.addEventListener('change', sendCalculationRequest);
    inputRedDate.addEventListener('input', sendCalculationRequest);
  });
</script>

<script>
  let selectedMachines = [];
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
    clonedCard.appendChild(closeBtn);
    document.getElementById('selected-machines').appendChild(clonedCard);
    selectedMachines.push(machineId);
    card.remove();
    usedMesin++;
    machineRequired -= 1;
    spanMachine.textContent = machineRequired;
  }

  function cancelSelected(clonedCard, machineId, originalCard) {
    clonedCard.remove();
    const jenis = originalCard.dataset.jenis;
    const containerId = 'collapseJenis' + jenis;
    const container = document.getElementById(containerId)?.querySelector('.row');
    if (container) {
      container.appendChild(originalCard);
    } else {
      document.getElementById('available-machines').appendChild(originalCard);
    }
    selectedMachines = selectedMachines.filter(id => id !== machineId);
    const spanMachine = document.getElementById('calc_machine');
    let currentRequired = parseInt(spanMachine.textContent || '0');
    if (usedMesin > 0) usedMesin--;
    spanMachine.textContent = currentRequired + 1;
    refreshMachineAvailability();
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
      card.onclick = function () {
        moveToSelected(this);
      };
      if (container) {
        container.appendChild(card);
      } else {
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
  $('#btSubmit').on('click', function() {
    const noItem = $('#inputSubcode').val();
    const qty = $('#txQty').val();
    const startDate = $('#tsDate').val();
    const endDate = $('#txDate').val();
    const qtyPerDay = $('#calc_qty').text();
    const type = $('#typeSave').val();
    const machines = $('#selected-machines .col-xl-3')
      .map(function() { return $(this).data('code'); })
      .get()
      .filter(Boolean);

    if (!noItem || !qty || !startDate || !endDate || machines.length === 0 || !qtyPerDay) {
      alert('Pastikan semua input dan mesin telah dipilih.');
      return;
    }

    $.ajax({
      url: "{{ route('schedule.store') }}",
      method: 'POST',
      contentType: 'application/json',
      dataType: 'json',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      data: JSON.stringify({
        no_item: noItem,
        qty: parseInt(qty),
        qty_day: parseInt(qtyPerDay),
        start_date: startDate,
        delivery_date: endDate,
        machines: machines,
        type: type
      }),
      success: function(data) {
        if (data.status === 'Success') {
          alert('✅ Data berhasil disimpan!');
          location.reload();
        } else {
          alert('⚠️ Gagal menyimpan data.');
        }
      },
      error: function(xhr, status, error) {
        console.error('❌ Error:', error);
        alert('Terjadi kesalahan saat mengirim data.');
      }
    });
  });
</script>

<style>
  table th, table td {
    text-align: left;
    vertical-align: middle;
    white-space: nowrap;
  }
  .cell-item {
    cursor: pointer;
  }
  .cell-item:hover {
    background-color: #e9ecef;
  }
</style>
@endpush
