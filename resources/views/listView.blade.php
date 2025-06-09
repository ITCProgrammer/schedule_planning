@extends('layouts.main')
@section('title', 'Schedule')

@section('content')
  <div class="table-responsive">
    <table id="itemCodeTable" class="table table-bordered table-sm">
      <thead>
        <tr>
          <th>Item Code</th>
        </tr>
      </thead>
      <tbody>
        @foreach($itemCode as $item)
          <tr class="item-row" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $item->item_code }}" aria-expanded="false" aria-controls="collapse-{{ $item->item_code }}" style="cursor:pointer;">
            <td>{{ $item->item_code }}</td>
          </tr>
          <tr class="collapse" id="collapse-{{ $item->item_code }}">
            <td>
              <div class="card card-body" style="width: 100%;">
                Konten untuk {{ $item->item_code }}
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <script>
    $(document).ready(function() {
      $('#itemCodeTable').DataTable({
        paging: false,
        searching: false,
        info: false
      });

      $('#itemCodeTable').on('click', '.item-row', function(e) {
        var target = $(this).data('bs-target');
        $('#itemCodeTable .collapse').not(target).collapse('hide');
        $(target).collapse('toggle');
      });
    });
  </script>
@endsection

@push('scripts')
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
  <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>

  <script>
    $(document).ready(function() {
      $('[data-bs-toggle="collapse"]').on('click', function() {
        var itemCode = $.trim($(this).text());
        var $collapseDiv = $('#collapse-' + itemCode);
        var $cardBody = $collapseDiv.find('.card-body');

        if ($cardBody.data('loaded')) {
          return;
        }

        $cardBody.html('<div>Loading data...</div>');

        $.ajax({
          url: '/schedule/' + itemCode,
          method: 'GET',
          dataType: 'json',
          success: function(data) {
            var sqlData = data.schedules;
            var db2Datas = data.db2_data;
            var stockDatas = data.stock_data;
            var forecast = data.forecast;
            var initialStock = 0;

            console.log(forecast);

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
          
            // var html = '<div style="overflow-x:auto;"><table class="table table-bordered table-sm">';
            var html = '<div class="scrollable-table"><table class="table table-bordered table-sm">';
            html += '<thead><tr><th>Mesin</th><th></th>';
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
          
            var mesinList = [...new Set(sqlData.map(row => row.mesin_code))];
            mesinList.forEach(function(mesin) {
              html += '<tr><td>' + mesin + '</td><td></td>';
              dates.forEach(function(d) {
                var currentDate = normalizeDate(d);
                if (currentDate.getDay() === 0) {
                  html += '<td style="background-color:#f8d7da;"></td>';
                  return;
                }
              
                var found = false;
                for (var i = 0; i < sqlData.length; i++) {
                  var row = sqlData[i];
                  if (row.mesin_code === mesin) {
                    var start = normalizeDate(new Date(row.start_date));
                    var end = normalizeDate(new Date(row.end_date));
                    if (currentDate >= start && currentDate <= end) {
                      html += '<td>' + row.qty_day + '</td>';
                      found = true;
                      break;
                    }
                  }
                }
                if (!found) {
                  html += '<td></td>';
                }
              });
              html += '</tr>';
            });

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
                console.log('end_date:', key, 'qty:', qtyNum);
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
            
              // Jika ada qty untuk tanggal end_date ini, tampilkan qty-nya
              if (qtyByEndDate[key]) {
                html += '<td><strong>' + qtyByEndDate[key].toFixed(2) + '</strong></td>';
                // projectQtyMap[key] = qtyByEndDate[key].toFixed(2);
                projectQtyMap[key] = qtyByEndDate[key];
              } else {
                html += '<td></td>';
                // projectQtyMap[key] = '0';
                projectQtyMap[key] = 0;
              }
            });
            html += '</tr>';
        
            html += '<tr><td><strong>Balance</strong></td><td><strong>' + initialStock.toFixed(2) + '</strong></td>';
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
            
              // if (index !== 0) {
              //   lastBalance = (lastBalance - poQty - forecastQty - projectQty) + totalQtyDay;
              // }
              lastBalance = (lastBalance - poQty - forecastQty - projectQty) + totalQtyDay;
            
              html += '<td class="balance-cell" data-date="' + key + '"><strong>' + lastBalance.toFixed(2) + '</strong></td>';
            });
            html += '</tr>';
          
            html += '</tbody></table></div>';
            $cardBody.html(html);
            $cardBody.data('loaded', true);
          
            $cardBody.on('input', '.forecast-input', function () {
              var forecastMap = {};
              $('.forecast-input').each(function () {
                var date = $(this).data('date');
                var val = parseFloat($(this).val() || 0);
                forecastMap[date] = val;
              });
            
              dates.forEach(function (d) {
                var key = d.toISOString().split("T")[0];
                if (d.getDay() === 0) return;
              
                var projectQty = projectQtyMap[key] || 0;
                var poQty = poDeliveryMap[key] || 0;
                var forecastQty = forecastMap[key] || 0;
                var balance = projectQty - poQty - forecastQty;
              
                var $cell = $cardBody.find('.balance-cell[data-date="' + key + '"]');
                if (projectQty || poQty || forecastQty) {
                  $cell.html('<strong>' + balance + '</strong>');
                } else {
                  $cell.html('');
                }
              });
            });
          },
          error: function() {
            $cardBody.html('<div class="text-danger">Error loading data.</div>');
          }
        });
      });
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

    .rotate-date {
      writing-mode: vertical-rl;
      transform: rotate(180deg);
      white-space: nowrap;
      font-size: 12px;
    }

    .table-sm input {
      max-width: 80px;
      margin: auto;
      display: block;
    }

    .scrollable-table {
      overflow: auto;
      max-height: 400px; /* Atur sesuai kebutuhan */
      border: 1px solid #dee2e6;
    }

    .scrollable-table {
      overflow-x: auto;
      overflow-y: hidden;
    }

  </style>

@endpush