{{-- calendar.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Mesin Calendar</title>

    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@3.3.2/dist/fullcalendar.min.css" rel="stylesheet" />

    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.24.0/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@3.3.2/dist/fullcalendar.min.js"></script>

    <!-- Optional: Style tambahan (misalnya, untuk penyesuaian tampilan kalender) -->
    <style>
        #calendar {
            max-width: 100%;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div id="calendar"></div>

    <script>
        $(document).ready(function () {
            $('#calendar').fullCalendar({
                header: {
                    left: 'prev,next today',  // Tombol navigasi
                    center: 'title',  // Judul kalender
                    right: 'month,agendaWeek,agendaDay'  // Pilihan tampilan
                },
                views: {
                    multiMonth: {
                        type: 'month',
                        duration: { months: 3 },  // Menampilkan 3 bulan berturut-turut
                        buttonText: 'Multi-Month'  // Teks tombol untuk multi-month view
                    }
                },
                defaultView: 'multiMonth',  // Set default view ke multi-month
                events: function(start, end, timezone, callback) {
                    $.ajax({
                        url: '{{ route("schedule.mesin.data") }}',  // URL untuk mengambil data jadwal mesin
                        method: 'GET',
                        success: function(data) {
                            var events = data.map(function(schedule) {
                                return {
                                    title: schedule.item_code,  // Nama mesin (atau item)
                                    start: schedule.start_date,  // Tanggal mulai
                                    end: schedule.delivery_date, // Tanggal selesai
                                    description: schedule.machine_code,  // Deskripsi mesin
                                    color: '#FF0000'  // Bisa ganti warna sesuai dengan status mesin
                                };
                            });
                            callback(events);
                        }
                    });
                },
                editable: true,  // Membuat event bisa dipindah-pindah
                droppable: true, // Bisa dipindah ke tanggal lain
                dayClick: function(date, jsEvent, view) {
                    // Aksi saat tanggal diklik
                    console.log('Tanggal yang diklik: ' + date.format());
                }
            });
        });
    </script>
</body>
</html>
