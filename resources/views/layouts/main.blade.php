<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>MPS</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link
      href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
      rel="stylesheet">
      <!-- FullCalendar JS -->
    <link href="{{ asset('css/sb-admin-2.css') }}" rel="stylesheet">
    @stack('styles')
  </head>

  <body id="page-top">
    <div id="wrapper" class="d-flex flex-column min-vh-100">
      <div id="content-wrapper" class="flex-grow-1">
        <div id="content">
          <!-- Top Bar -->
          <nav class="navbar bg-body-tertiary" style="background-color: #9A616D;">
            <div class="container-fluid">
              <a class="navbar-brand text-white fw-bold fs-3" href="{{ route('home') }}">
                <i class="fas fa-industry me-2"></i> MPS-ITTI
              </a>
              <a class="navbar-brand text-white fw-bold fs-3" href="{{ route('poList') }}">
                <i class="fas fa-calendar me-2"></i> Planning Schedule Priview
              </a>
              <button type="submit" class="btn btn-outline-light" id="btLogout">
                <i class="fas fa-sign-out-alt me-1"></i> Logout
              </button>
            </div>
          </nav>

          <!-- Page Content -->
          <div class="container-fluid mt-4">
            @yield('content')
          </div>
        </div>
      </div>

      <!-- Footer -->
      <footer class="sticky-footer bg-white">
        <div class="container my-auto">
          <div class="copyright text-center my-auto">
            <span>Copyright &copy; DIT Team ITTI 2025</span>
          </div>
        </div>
      </footer>
    </div>
  </body>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
  @stack('scripts')
  <script>
    $('#btLogout').on('click', function () {
      Swal.fire({
        title: "Do you want to Logout ?",
        showCancelButton: true,
        icon: "question",
        confirmButtonText: "Yes"
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = "{{ route('logout') }}";
        } else {

        }
      });
    });
  </script>

</html>
