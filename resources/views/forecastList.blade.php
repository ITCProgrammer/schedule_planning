@extends('layouts.main')
@section('title', 'Schedule')

@section('content')

  @php
    use Carbon\Carbon;
  @endphp

  <ul class="nav nav-tabs mb-3">
    <li class="nav-item">
      <a class="nav-link" href="{{ route('poList') }}">PO With Greige Delivery Date</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="{{ route('scheList') }}">Schedule List Machine</a>
    </li>
    <li class="nav-item">
      <a class="nav-link active" aria-current="page" href="{{ route('forecastList') }}">Detail Product</a>
    </li>
  </ul>

  <div class="row mb-3">
    <div class="col-6">
      <input type="text" id="searchInput" class="form-control" placeholder="Cari Item Code...">
    </div>
  </div>

  <div class="mb-3">
    <label for="viewModeSelect" class="form-label">Tampilan Data:</label>
    <select id="viewModeSelect" class="form-select" style="width: auto;">
      <option value="daily" selected>Per Hari</option>
      <option value="weekly">Per Minggu</option>
      <option value="monthly">Per Bulan</option>
    </select>
  </div>

  
  <div id="resultContainer" class="mb-4"></div>

  @foreach($itemCode as $item)
    <div class="card mb-4">
      <div class="card-body" id="schedule-{{ $item->item_code }}">
        <div>Loading data...</div>
      </div>
    </div>
  @endforeach

  <!-- Modal -->
  <div class="modal fade" id="dataModal" tabindex="-1" aria-labelledby="dataModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 90%;">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="dataModalLabel">Detail Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Content will be loaded dynamically -->
        </div>
        <div class="d-flex justify-content-end gap-2 mb-2">
          <button id="exportData1" class="btn btn-success btn-sm mr-4">Export Per Project</button>
          <button id="exportData2" class="btn btn-primary btn-sm mr-4">Export Detail</button>
        </div>
      </div>
    </div>
  </div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
  const items = {!! json_encode($itemCode) !!};
  let latestResponse = null;

  const headingMapping1 = {
    origdlvsalordlinesalordercode: "No. Project",
    statisticalgroupcode: "Season",
    logicalwarehousecode: "Warehouse",
    totalstock: "Qty (Kg)"
  };

  const headingMapping2 = {
    lotcode: "Lotcode",
    origdlvsalordlinesalordercode: "No. Project",
    statisticalgroupcode: "Season",
    externalreference: "Keterangan Lot",
    logicalwarehousecode: "Warehouse",
    stock: "Qty (Kg)"
  };

  let currentViewMode = 'daily';

  $('#viewModeSelect').on('change', function () {
    currentViewMode = $(this).val();
    items.forEach(item => loadItemData(item.item_code));
  });

  $(document).ready(function () {
    // Load initial data for each item
    items.forEach(function (item) {
      loadItemData(item.item_code);
    });
  });

  // Search functionality
  $('#searchInput').on('keyup', function () {
    const keyword = $(this).val().toUpperCase().trim();

    if (keyword.length !== 0) {
      searchItem(keyword);
    } else {
      $('#resultContainer').hide();
      $('.card').show();
      items.forEach(item => loadItemData(item.item_code));
    }
  });

  // Click handler for stock details
  $(document).on('click', '#initialStockCell', function () {
    const stockValue = $(this).data('stock');
    const itemCode = $(this).data('item');
    loadStockDetails(itemCode, stockValue);
  });
  
  $(document).on('click', '#exportData1', function () {
    if (latestResponse && latestResponse.dataHeading) {
      exportToExcel(latestResponse.dataHeading, 'Summary_Data.xlsx', headingMapping1);
    } else {
      alert('Data summary tidak tersedia');
    }
  });

  $(document).on('click', '#exportData2', function () {
    if (latestResponse && latestResponse.dataDetail) {
      exportToExcel(latestResponse.dataDetail, 'Detail_Data.xlsx', headingMapping2);
    } else {
      alert('Data detail tidak tersedia');
    }
  });

  function groupDates(dates, mode) {
    const groups = [];
    let currentGroup = [];

    if (mode === 'weekly') {
      dates.forEach((d, idx) => {
        currentGroup.push(d);
        if (d.getDay() === 6 || idx === dates.length - 1) { // Saturday or last day
          groups.push([...currentGroup]);
          currentGroup = [];
        }
      });
    } else if (mode === 'monthly') {
      let currentMonth = dates[0].getMonth();
      dates.forEach((d, idx) => {
        if (d.getMonth() === currentMonth) {
          currentGroup.push(d);
        } else {
          groups.push([...currentGroup]);
          currentGroup = [d];
          currentMonth = d.getMonth();
        }
      });
      if (currentGroup.length > 0) groups.push(currentGroup);
    } else {
      // daily mode
      dates.forEach(d => groups.push([d]));
    }

    return groups;
  }

  function formatGroupLabel(group, mode) {
    if (mode === 'weekly') {
      const start = group[0].toLocaleDateString('id-ID');
      const end = group[group.length - 1].toLocaleDateString('id-ID');
      return `${start} - ${end}`;
    } else if (mode === 'monthly') {
      return group[0].toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
    } else {
      return group[0].toLocaleDateString('id-ID');
    }
  }

  // Function to load item data
  function loadItemData(itemCode) {
    const $container = $('#schedule-' + itemCode);
    $container.html('<div>Loading data...</div>');

    $.ajax({
      url: '/schedule/' + itemCode,
      method: 'GET',
      dataType: 'json',
      success: function (data) {
        renderForecastTable($container, itemCode, data);
      },
      error: function () {
        $container.html('<div class="text-danger">Error loading data.</div>');
      }
    });
  }

  // Function to search for an item
  function searchItem(keyword) {
    $.ajax({
      url: '/search/' + encodeURIComponent(keyword),
      method: 'GET',
      dataType: 'json',
      success: function (data) {
        if (data.exists_in_schedule) {
          $('#resultContainer').html('<div class="card"><div class="card-body text-muted">Item sudah ada dalam jadwal.</div></div>');
          $('#resultContainer').show();
          $('.card').hide();
          return;
        }

        $('.card').remove();
        const containerId = 'schedule-' + keyword;
        const newCardHtml = `
          <div class="card mb-4">
            <div class="card-body" id="${containerId}">
              <div>Loading data...</div>
            </div>
          </div>
        `;
        $('#resultContainer').html(newCardHtml).show();

        // Load data for the searched item
        $.ajax({
          url: '/schedule/' + keyword,
          method: 'GET',
          dataType: 'json',
          success: function (data) {
            renderForecastTable($('#' + containerId), keyword, data);
          },
          error: function () {
            $('#' + containerId).html('<div class="text-danger">Error loading data.</div>');
          }
        });
      },
      error: function () {
        $('#resultContainer').html('<div class="card"><div class="card-body text-danger">Gagal mengambil data.</div></div>');
        $('#resultContainer').show();
      }
    });
  }

  // Function to load stock details
  function loadStockDetails(itemCode, stockValue) {
    $.ajax({
      url: "{{ route('getDetailStock') }}",
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      data: {
        item_code: itemCode,
        stock: stockValue
      },
      success: function (response) {
        latestResponse = response;
        let html = '<table class="table table-bordered table-sm">';
        html += '<thead><tr><th>Lotcode</th><th>No. Project</th><th>Season</th><th>Keterangan Lot</th><th>Warehouse</th><th>Qty (Kg)</th></tr></thead><tbody>';

        if (response.dataStock && response.dataStock.length > 0) {
          response.dataStock.forEach(row => {
            html += `<tr>
                      <td>${row.lotcode}</td>
                      <td>${row.origdlvsalordlinesalordercode}</td>
                      <td>${row.statisticalgroupcode}</td>
                      <td>${row.externalreference}</td>
                      <td>${row.logicalwarehousecode}</td>
                      <td>${parseFloat(row.stock).toFixed(2)}</td>
                    </tr>`;
          });
        } else {
          html += '<tr><td colspan="6" class="text-center">Data tidak ditemukan</td></tr>';
        }

        html += '</tbody></table>';
        $('#dataModal .modal-body').html(html);
        $('#dataModal').modal('show');
      },
      error: function () {
        $('#dataModal .modal-body').html('<div class="alert alert-danger">Gagal memuat data.</div>');
        $('#dataModal').modal('show');
      }
    });
  }

  // Function to render forecast table
  function renderForecastTable($container, itemCode, data) {
    const sqlData = data.schedules;
    const db2Datas = data.db2_data;
    const forecast = data.forecast;
    const stockDatas = data.stock_data;
    let initialStock = stockDatas.length > 0 ? parseFloat(stockDatas[0].stock || 0) : 0;

    function normalizeDate(date) {
      return new Date(date.getFullYear(), date.getMonth(), date.getDate());
    }

    const today = normalizeDate(new Date());
    const dates = [];

    // Yesterday
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);
    dates.push(normalizeDate(yesterday));

    // Next 365 days
    for (let i = 0; i <= 365; i++) {
      let d = new Date(today);
      d.setDate(d.getDate() + i);
      dates.push(normalizeDate(d));
    }

    // Qty Planning Map
    let qtyPlannMap = {};
    sqlData.forEach(function (row) {
      let start = new Date(row.start_date);
      let end = new Date(row.end_date);
      let dailyQty = parseFloat(row.qty_day || 0);

      for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
        let currentDate = normalizeDate(new Date(d));
        if (currentDate.getDay() === 0) continue;
        
        let dateKey = currentDate.toISOString().split("T")[0];
        qtyPlannMap[dateKey] = (qtyPlannMap[dateKey] || 0) + dailyQty;
      }
    });

    // PO Map (Greige Delivery Date)
    let greigeDeliveryMap = {};
    db2Datas.forEach(function (row) {
      let to = normalizeDate(new Date(row.rmp_req_to));
      let dateKey = to.toISOString().split("T")[0];
      let qty = parseFloat(row.qty_total || 0);
      greigeDeliveryMap[dateKey] = (greigeDeliveryMap[dateKey] || 0) + qty;
    });

    // Forecast Map
    const forecastMap = {};
    const currentYear = today.getFullYear();
    dates.forEach(function (d) {
      if (d.getDate() === 1) {
        const match = forecast.find(f =>
          parseInt(f.buy_month) === (d.getMonth() + 1) && currentYear === d.getFullYear()
        );
        if (match) {
          forecastMap[d.toISOString().split("T")[0]] = parseFloat(match.total_qty_kg || 0);
        }
      }
    });

    // Project Qty Map
    let projectQtyMap = {};
    sqlData.forEach(row => {
      if (row.end_date && row.qty) {
        let key = normalizeDate(new Date(row.end_date)).toISOString().split("T")[0];
        projectQtyMap[key] = parseFloat(row.qty || 0);
      }
    });

    // Build HTML table
    let html = '<div class="table-responsive scrollable-table-container">';
    html += '<table class="table table-bordered table-sm scrollable-table">';
    html += '<thead><tr>';
    html += '<th>' + itemCode + '</th>';
    html += '<th></th>';

    dates.forEach(d => {
      const label = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
      const isSunday = d.getDay() === 0;
      html += `<th class="${isSunday ? 'sunday-column' : ''}">${label}</th>`;
    });

    html += '</tr></thead><tbody>';

    // Planning row
    html += '<tr><td><strong>Qty Planning</strong></td><td></td>';
    dates.forEach(d => {
      let dateKey = d.toISOString().split("T")[0];
      const isSunday = d.getDay() === 0;
      const val = qtyPlannMap[dateKey];
      html += `<td class="${isSunday ? 'sunday-column' : ''}">${val ? val.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : ''}</td>`;
    });
    html += '</tr>';

    // PO With Greige Delivery Date row
    html += '<tr><td><strong>PO With Greige Delivery Date</strong></td><td></td>';
    dates.forEach(d => {
      const key = d.toISOString().split("T")[0];
      const val = greigeDeliveryMap[key];
      const isSunday = d.getDay() === 0;
      html += `<td class="${isSunday ? 'sunday-column' : ''}">${val ? '(' + val.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ')' : ''}</td>`;
    });
    html += '</tr>';

    // Forecast row
    html += '<tr><td><strong>Forecast</strong></td><td></td>';
    dates.forEach(d => {
      const key = d.toISOString().split("T")[0];
      const val = forecastMap[key];
      const isSunday = d.getDay() === 0;
      html += `<td class="${isSunday ? 'sunday-column' : ''}">${val ? '(' + val.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ')' : ''}</td>`;
    });
    html += '</tr>';

    // Balance row
    html += `<tr><td><strong>Balance</strong></td>
            <td style="background-color:rgb(190, 229, 255);">
              <strong 
                id="initialStockCell" 
                data-stock="${initialStock.toFixed(2)}" 
                data-item="${itemCode}" 
                style="cursor:pointer;">
                ${initialStock.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
              </strong>
            </td>
            `;

    // Calculate and display balance for each date
    let lastBalance = initialStock;
    dates.forEach((d, i) => {
      const currentDate = normalizeDate(d);
      const key = currentDate.toISOString().split("T")[0];
                
      if (currentDate.getDay() === 0) {
        html += '<td class="sunday-column"></td>';
        return;
      }

      const poQty = greigeDeliveryMap[key] || 0;
      const forecastQty = 0;
      const projectQty = projectQtyMap[key] || 0;

      let totalQtyDay = 0;
      sqlData.forEach(function(row) {
        const start = normalizeDate(new Date(row.start_date));
        const end = normalizeDate(new Date(row.end_date));
        if (currentDate >= start && currentDate <= end && currentDate.getDay() !== 0) {
          totalQtyDay += parseFloat(row.qty_day || 0);
        }
      });

      lastBalance = (lastBalance - poQty - forecastQty - projectQty) + totalQtyDay;
      
      const balanceAbs = Math.abs(lastBalance);
      const balanceClass = lastBalance < 0 ? 'negative-balance' : '';
      const displayBalance = lastBalance < 0 
        ? `(${balanceAbs.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })})`
        : balanceAbs.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
          
      html += `<td class="balance-cell ${balanceClass}" data-date="${key}">
        <strong>${displayBalance}</strong>
      </td>`;

    });
    html += '</tr>';

    html += '</tbody></table></div>';

    $container.html(html);
  }

  function exportToExcel(data, fileName, fieldMapping) {
    const headers = [Object.values(fieldMapping)];

    const rows = data.map(item =>
      Object.keys(fieldMapping).map(key => {
        let value = item[key] ?? "";

        if (key.toLowerCase() === 'stock' && !isNaN(value)) {
          value = parseFloat(value).toLocaleString('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          });
        }

        return value;
      })
    );

    const ws = XLSX.utils.aoa_to_sheet(headers.concat(rows));
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Sheet1");
    XLSX.writeFile(wb, fileName);
  }

</script>

<style>
  .scrollable-table-container {
    overflow-x: auto;
  }
  .scrollable-table {
    white-space: nowrap;
  }
  .sunday-column {
    background-color: #ffe6e6;
  }
  .negative-balance {
    background-color: #f8d7da !important;
    color: #721c24 !important;
  }
</style>
@endpush