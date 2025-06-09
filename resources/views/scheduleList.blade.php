@extends('layouts.main')
@section('title', 'Schedule')

@section('content')

  @php
    use Carbon\Carbon;
  @endphp

  <ul class="nav nav-tabs">
    <li class="nav-item">
      <a class="nav-link" href="{{ route('poList') }}">PO With Greige Delivery Date</a>
    </li>
    <li class="nav-item">
      <a class="nav-link active" aria-current="page" href="{{ route('scheList') }}">Schedule List Machine</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="{{ route('forecastList') }}">Detail Product</a>
    </li>
  </ul>

  <div class="card shadow-sm mt-3">
    <div class="card-body table-responsive">
      <button id="editBtx" class="btn btn-success btn-sm mr-4">Edit Schedule</button>
      <table id="itemTable" class="table table-bordered">
        <thead>
          <tr id="tableHead">
            <th>Mesin</th>
          </tr>
        </thead>
        <tbody id="tableBody">
          <!-- Collapsible rows will be injected here -->
        </tbody>
      </table>
    </div>
  </div>

@endsection

@push('scripts')
<script>
  let undoStack = [];
  let isEditMode = false;
  let selectedItems = [];
  let editedSchedule = [];
  let autoScrollInterval = null;

  const typeColorMap = {
    'Po Greige': '#cce5ff',
    'Sche Plann': '#d4edda',
    'Forecast': '#fce5cd',
  };

  $(document).ready(function () {
    $.ajax({
      url: "{{ route('loadMesin') }}",
      method: 'GET',
      dataType: 'json',
      success: function (response) {
        enableMultiSelect();

        const today = new Date();
        const startDate = new Date(today);
        const endDate = new Date(today);
        startDate.setDate(today.getDate() - 1);
        endDate.setFullYear(today.getFullYear() + 1);

        const dates = [];
        let currentDate = new Date(startDate);

        while (currentDate <= endDate) {
          const day = currentDate.getDate().toString().padStart(2, '0');
          const month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
          const year = currentDate.getFullYear();
          const formattedDate = `${day}-${month}-${year}`;
          const dayOfWeek = currentDate.getDay();

          $('#tableHead').append(`<th${dayOfWeek === 0 ? ' style="color:red;"' : ''}>${formattedDate}</th>`);
          dates.push(new Date(currentDate));
          currentDate.setDate(currentDate.getDate() + 1);
        }

        renderSchedule(response, dates);
      },
      error: function (xhr, status, error) {
        alert('Gagal mengambil data: ' + error);
        console.error(error);
      }
    });

    $('#editBtx').on('click', function () {
      isEditMode = !isEditMode;
      $(this).toggleClass('btn-success btn-danger')
             .text(isEditMode ? 'Save Schedule' : 'Edit Schedule');

      if (isEditMode) {
        enableMultiSelect();
        enableDragAndDropMulti();
      } else {
        $('.cell-item').attr('draggable', false).removeClass('selected');
        selectedItems = [];
      }
    });

    $(document).on('keydown', function (e) {
      if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        if (isEditMode) saveSchedule();
      }
    });
  });

  function normalizeDate(date) {
    return new Date(date.getFullYear(), date.getMonth(), date.getDate());
  }

  function parseDateFromString(str) {
    const [day, month, year] = str.split('-').map(Number);
    return new Date(year, month - 1, day);
  }

  function formatDate(date) {
    return new Date(date).toISOString().split('T')[0];
  }

  function enableMultiSelect() {
    $('.cell-item').off('click').on('click', function (e) {
      if (!isEditMode) return;

      if (e.ctrlKey || e.metaKey) {
        $(this).toggleClass('selected');
        const index = selectedItems.indexOf(this);

        if (index > -1) selectedItems.splice(index, 1);
        else selectedItems.push(this);
      } else {
        $('.cell-item').removeClass('selected');
        selectedItems = [this];
        $(this).addClass('selected');
      }
    });
  }

  function enableDragAndDropMulti() {
    $('.cell-item').attr('draggable', true).on('dragstart', function (e) {
      if (!isEditMode || selectedItems.length === 0) return;

      const selected = selectedItems.map(el => el.outerHTML);
      e.originalEvent.dataTransfer.setData('text/plain', JSON.stringify(selected));

      $('.cell-item').attr('draggable', false).filter('.selected').attr('draggable', true);
    });

    $('#itemTable td').on('dragover', function (e) {
      e.preventDefault();
      if (!isEditMode || $(this).children('.cell-item').length > 0) return;
      $(this).addClass('drop-target');
    });

    $('#itemTable td').on('dragleave', function () {
      $(this).removeClass('drop-target');
    });

    $('#itemTable td').on('drop', function (e) {
      e.preventDefault();
      $(this).removeClass('drop-target');
      if (!isEditMode || selectedItems.length === 0) return;

      const $targetCell = $(this);
      const targetIndex = $targetCell.index();
      const $targetRow = $targetCell.closest('tr');
      const selectedRow = $(selectedItems[0]).closest('tr');

      if (!selectedItems.every(item => $(item).closest('tr')[0] === selectedRow[0])) {
        alert('Harus memilih item dari baris yang sama!');
        return;
      }

      selectedItems.sort((a, b) => $(a).parent().index() - $(b).parent().index());
      const $cells = $targetRow.find('td');

      selectedItems.forEach((item, i) => {
        const $originCell = $(item).parent();
        const $destCell = $cells.eq(targetIndex + i);

        if ($destCell.length && $destCell.children('.cell-item').length === 0) {
          $originCell.css('background-color', '');
          $(item).removeClass('selected');
          $destCell.append(item);

          const bgColor = typeColorMap[$(item).data('type')] || '#dee2e6';
          $destCell.css('background-color', bgColor);
          $(item).hide().fadeIn(150);

          const tanggal = $('#tableHead th').eq($destCell.index()).text().trim();
          editedSchedule = editedSchedule.filter(e => !(e.item_code === $(item).find('strong').text().trim() && e.tanggal_str === tanggal));

          editedSchedule.push({
            item_code: $(item).find('strong').text().trim(),
            type: $(item).data('type'),
            tanggal_str: tanggal,
            tanggal: parseDateFromString(tanggal),
            mesin_from: $(item).data('mesin'),
            mesin_to: $targetRow.find('td:first').text().trim()
          });
        }
      });

      selectedItems = [];
    });
  }

  function saveSchedule() {
    const newSchedule = editedSchedule.map(item => ({
      item_code: item.item_code,
      type: item.type,
      tanggal: formatDate(item.tanggal),
      mesin_from: item.mesin_from,
      mesin_to: item.mesin_to
    }));

    console.log("Schedule yang akan disimpan:", newSchedule);

    $.ajax({
      url: "{{ route('editSchedule') }}",
      type: "POST",
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      data: {
        schedule: newSchedule
      },
      success: function () {
        location.reload();
      },
      error: function (xhr, status, error) {
        console.error("Gagal menyimpan schedule:", error);
        alert("Gagal menyimpan jadwal. Coba lagi.");
      }
    });
  }

  function renderSchedule(response, dates) {
    const dataMesin = response.dataMesin || [];
    const dataSchedule = response.dataSchedule || [];
    const groupedByJenis = {};

    dataMesin.forEach(row => {
      const mesin = row.mesin_code.trim();
      const jenis = row.jenis;
      if (!groupedByJenis[jenis]) groupedByJenis[jenis] = {};
      groupedByJenis[jenis][mesin] = [];
    });

    dataSchedule.forEach(row => {
      const mesin = row.mesin_code.trim();
      const item_code = row.item_code;
      const start = normalizeDate(new Date(row.start_date));
      const end = normalizeDate(new Date(row.end_date));
      const type = row.type;
      const jenis = (dataMesin.find(m => m.mesin_code.trim() === mesin.trim()) || {}).jenis || 'Lainnya';
      if (!groupedByJenis[jenis]) groupedByJenis[jenis] = {};
      if (!groupedByJenis[jenis][mesin]) groupedByJenis[jenis][mesin] = [];
      groupedByJenis[jenis][mesin].push({ item_code, start, end, type, mesin });
    });

    Object.keys(groupedByJenis).forEach(jenis => {
      const collapseId = 'collapse_' + jenis.replace(/\s+/g, '_');

      $('#itemTable').append(`
        <tbody>
          <tr class="bg-light">
            <td colspan="${dates.length + 1}">
              <button class="btn btn-sm btn-link toggle-section" data-bs-toggle="collapse" data-bs-target="#${collapseId}" aria-expanded="true">
                <strong>${jenis}</strong>
              </button>
            </td>
          </tr>
        </tbody>
        <tbody id="${collapseId}" class="collapse show"></tbody>
      `);

      Object.entries(groupedByJenis[jenis]).forEach(([mesin, jobs]) => {
        let rowHtml = `<tr><td><strong>${mesin}</strong></td>`;

        dates.forEach(d => {
          const current = normalizeDate(d);
          const matchedJobs = jobs.filter(job => current >= job.start && current <= job.end);

          if (d.getDay() === 0) {
            rowHtml += '<td style="background-color:#f8d7da;"></td>';
          } else if (matchedJobs.length) {
            const content = matchedJobs.map(job => `
              <div class="cell-item" data-type="${job.type}" data-mesin="${job.mesin}" style="padding: 2px; margin-bottom: 2px;">
                <strong>${job.item_code}</strong><br>
                <small>${job.type}</small>
              </div>
            `).join('');
            const bgColor = typeColorMap[matchedJobs[0].type] || '#dee2e6';
            rowHtml += `<td style="background-color:${bgColor};">${content}</td>`;
          } else {
            rowHtml += '<td></td>';
          }
        });

        rowHtml += '</tr>';
        $(`#${collapseId}`).append(rowHtml);
      });
    });
  }

  $(document).on('dragover', function(e) {
    const scrollMargin = 80;
    const scrollSpeed = 20;

    const mouseY = e.originalEvent.clientY;
    const mouseX = e.originalEvent.clientX;
    const windowHeight = $(window).height();
    const windowWidth = $(window).width();

    if (mouseY > windowHeight - scrollMargin) {
      window.scrollBy(0, scrollSpeed);
    }

    if (mouseY < scrollMargin) {
      window.scrollBy(0, -scrollSpeed);
    }

    if (mouseX > windowWidth - scrollMargin) {
      window.scrollBy(scrollSpeed, 0);
    }
  
    if (mouseX < scrollMargin) {
      window.scrollBy(-scrollSpeed, 0);
    }
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
    font-size: 12px;
    line-height: 1.1;
  }
  .cell-item:hover {
    background-color: #f8f9fa;
  }
  .toggle-section {
    text-decoration: none;
    font-size: 14px;
  }
  .toggle-section::before {
    content: "\25BC ";
  }
  .toggle-section.collapsed::before {
    content: "\25B6 ";
  }
  .cell-item.selected {
    border: 2px solid #007bff;
    background-color: #cce5ff;
  }
  .drop-target {
    outline: 2px dashed #007bff;
    background-color: rgba(0, 123, 255, 0.1);
  }
</style>

@endpush
