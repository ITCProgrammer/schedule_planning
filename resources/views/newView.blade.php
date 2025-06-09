@extends('layouts.main')
@section('title', 'Schedule')

@section('content')
  <ul class="nav nav-tabs mb-3">
    <li class="nav-item">
      <a class="nav-link" href="{{ route('poList') }}">PO With Greige Delivery Date</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="{{ route('scheList') }}">Schedule List Machine</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="{{ route('forecastList') }}">Detail Product</a>
    </li>
    <li class="nav-item">
      <a class="nav-link active" aria-current="page" href="{{ route('view') }}">Shcedule Plann</a>
    </li>
  </ul>

  <div class="table-responsive">
    <table id="itemTable" class="table table-bordered table-hover">
      <thead>
        <tr>
          <th>Item Code</th>
        </tr>
      </thead>
      <tbody>
        @foreach($itemCode as $item)
          <tr class="item-row" data-item-code="{{ $item->item_code }}">
            <td>{{ $item->item_code }}</td>
          </tr>
          <tr class="collapse-row" id="collapse-{{ $item->item_code }}" style="display: none;">
            <td colspan="1">
              <div class="card card-body">Loading...</div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
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

<script>
  var initialStock = 0;
  var itemCode = "NONE"

  $(document).ready(function () {
    $('.item-row').on('click', function () {
      itemCode = $(this).data('item-code');
      var $collapseRow = $('#collapse-' + itemCode);
      var $cardBody = $collapseRow.find('.card-body');

      if ($collapseRow.is(':visible')) {
        $collapseRow.hide();
        return;
      }

      $('.collapse-row').hide();

      if ($cardBody.data('loaded')) {
        $collapseRow.show();
        return;
      }

      $collapseRow.show();
      $cardBody.html('<div>Loading data...</div>');

      $.ajax({
        url: '/schedule/' + itemCode,
        method: 'GET',
        dataType: 'json',
        success: function (data) {
          var sqlData = data.schedules;
          var db2Datas = data.db2_data;
          var stockDatas = data.stock_data;
          var forecast = data.forecast;
          if (stockDatas.length > 0) {
            initialStock = parseFloat(stockDatas[0].stock || 0);
          }

          if (sqlData.length === 0) {
            $cardBody.html('<div>No data found for ' + itemCode + '</div>');
            $cardBody.data('loaded', true);
            return;
          }
          
          function normalizeDate(date) {
            return new Date(date.getFullYear(), date.getMonth(), date.getDate());
          }
          
          var startDate = new Date();
          startDate.setDate(startDate.getDate() - 1);
          var dates = [];
          for (var i = 0; i <= 365; i++) {
            var d = new Date(startDate);
            d.setDate(startDate.getDate() + i);
            dates.push(new Date(d));
          }

            var today = normalizeDate(new Date());
            var yesterday = new Date(today);
            yesterday.setDate(today.getDate() - 1);
            let html = '<div class="table-scroll-wrapper"><table class="table table-bordered table-sm wide-table">';
            html += '<thead><tr><th style="width: 200px;">Mesin</th><th style="width: 100px;"></th>';
            dates.forEach(function(date) {
              var options = { day: 'numeric', month: 'short', year: 'numeric' };
              var dateStr = date.toLocaleDateString('id-ID', options);
              var isSunday = date.getDay() === 0;
              var isToday = normalizeDate(date).getTime() === today.getTime();
              var isYesterday = normalizeDate(date).getTime() === yesterday.getTime();
              html += '<th style="' +
                      (isSunday ? 'color:red;' : '') +
                      (isToday ? 'background-color:#d1e7dd;' : '') +
                      (isYesterday ? 'background-color:#fff3cd;' : '') +
                      '">' + dateStr + '</th>';
            });
            html += '</tr></thead><tbody>';
          
            html += '<tr><td>Qty Planning</td><td></td>';
            dates.forEach(function(d) {
              var currentDate = normalizeDate(d);
              if (currentDate.getDay() === 0) {
                html += '<td style="background-color:#f8d7da;"></td>';
                return;
              }
            
              var totalQty = 0;
              sqlData.forEach(function(row) {
                var start = normalizeDate(new Date(row.start_date));
                var end = normalizeDate(new Date(row.end_date));
                if (currentDate >= start && currentDate <= end) {
                  totalQty += parseFloat(row.qty_day) || 0;
                }
              });
            
              if (totalQty > 0) {
                html += '<td>' + totalQty + '</td>';
              } else {
                html += '<td></td>';
              }
            });
            html += '</tr>';
            let greigeDeliveryMap = {};
            db2Datas.forEach(function(row) {
              let to = normalizeDate(new Date(row.rmp_req_to));
              let dateKey = to.toISOString().split("T")[0];
              let dailyQty = parseFloat(row.qty_total || 0);

              greigeDeliveryMap[dateKey] = (greigeDeliveryMap[dateKey] || 0) + dailyQty;
            });
            html += '<tr style="border-top: 3px solid black;"><td><strong>PO With Greige Delivery Date</strong></td><td></td>';
            dates.forEach(function(d) {
              let currentDate = normalizeDate(d);
              let key = currentDate.toISOString().split("T")[0];

              if (currentDate.getDay() === 0) {
                html += '<td style="background-color:#f8d7da;"></td>';
              } else {
                let qty = greigeDeliveryMap[key] || '';
                html += '<td><strong>' + (qty ? qty.toFixed(2) : '') + '</strong></td>';

              }
            });
            html += '</tr>';
          
            var currentYear = new Date().getFullYear();
            html += '<tr><td><strong>Forecast</strong></td><td></td>';
            dates.forEach(function(d) {
              var currentDate = normalizeDate(d);
              var content = '';
                        
              if (currentDate.getDate() === 1) {
                var matchingForecast = forecast.find(function(f) {
                  return parseInt(f.buy_month) === (currentDate.getMonth() + 1) && currentYear === currentDate.getFullYear();
                });
              
                if (matchingForecast) {
                  content = matchingForecast.total_qty_kg.toFixed(2);
                }
              }
            
              if (currentDate.getDay() === 0) {
                html += `<td style="background-color:#f8d7da;">${content}</td>`;
              } else {
                html += `<td>${content}</td>`;
              }
            });
            html += '</tr>';

            let projectQtyMap = {};
            html += '<tr><td><strong>Project Qty Plan</strong></td><td></td>';

            let qtyByEndDate = {};
            sqlData.forEach(function(row) {
              if (row.end_date && row.qty) {
                let key = normalizeDate(new Date(row.end_date)).toISOString().split("T")[0];
                let qtyNum = parseFloat(row.qty);
                qtyByEndDate[key] = parseFloat(row.qty);
              }
            });

            dates.forEach(function(d) {
              var currentDate = normalizeDate(d);
              var key = currentDate.toISOString().split("T")[0];

              if (currentDate.getDay() === 0) {
                html += '<td style="background-color:#f8d7da;"></td>';
                return;
              }
            
              if (qtyByEndDate[key]) {
                html += '<td><strong>' + qtyByEndDate[key].toFixed(2) + '</strong></td>';
                projectQtyMap[key] = qtyByEndDate[key];
              } else {
                html += '<td></td>';
                projectQtyMap[key] = 0;
              }
            });
            html += '</tr>';
        
            // html += '<tr><td><strong>Balance</strong></td><td><strong>' + initialStock.toFixed(2) + '</strong></td>';
            // html += '<tr><td><strong>Balance</strong></td><td><a onclick="handleInitialStockClick()"><strong>' + initialStock.toFixed(2) + '</strong></a></td>';
            html += `<tr><td><strong>Balance</strong></td>
                    <td>
                      <strong 
                        id="initialStockCell" 
                        data-stock="${initialStock.toFixed(2)}" 
                        data-item="${itemCode}" 
                        style="cursor:pointer;">
                        ${initialStock.toFixed(2)}
                      </strong>
                    </td>
                    `;
            let lastBalance = initialStock;
            dates.forEach(function(d, index) {
              const currentDate = normalizeDate(d);
              const key = currentDate.toISOString().split("T")[0];
                        
              if (currentDate.getDay() === 0) {
                html += '<td style="background-color:#f8d7da;"></td>';
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
            
              html += '<td class="balance-cell" data-date="' + key + '"><strong>' + lastBalance.toFixed(2) + '</strong></td>';
            });
            html += '</tr>';
          html += '</table></div>';
          $cardBody.html(html);
          $cardBody.data('loaded', true);
        },
        error: function () {
          $cardBody.html('<div class="text-danger">Error loading data.</div>');
        }
      });
    });
  });

  function handleInitialStockClick() {
    $('#dataModal').modal('show');
    // alert('Item: ' + itemCode + '\nInitial Stock: ' + initialStock.toFixed(2));
  }

  $(document).on('click', '#initialStockCell', function () {
    const stockValue = $(this).data('stock');
    const itemCode = $(this).data('item');

    // alert('Item Code: ' + itemCode + '\nInitial Stock: ' + stockValue);
    $('#dataModal').modal('show');
  });

</script>


  <style>
    table th, table td {
      text-align: left;
      vertical-align: middle;
      white-space: nowrap;
    }

    .table-sm td, .table-sm th {
      padding: 2px 4px !important;
      font-size: 11px !important;
    }

    .table-scroll-wrapper {
      overflow-x: auto;
      max-height: 500px;
      overflow-y: auto;
    }

    .wide-table {
      width: 3000%;
      min-width: 1500px;
      table-layout: fixed;
    }
  </style>

@endpush
