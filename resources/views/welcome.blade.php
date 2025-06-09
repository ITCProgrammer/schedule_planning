<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
  </head>
  <body>
    <div>
      <section class="vh-100" style="background-color: #9A616D;">
        <div class="container py-5 h-100">
          <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col col-xl-10">
              <div class="card" style="border-radius: 1rem;">
                <div class="row g-0">
                  <div class="col-md-6 col-lg-5 d-none d-md-block">
                    <img src="{{ asset('image/foto.jpg') }}"
                      alt="login form" class="img-fluid" style="border-radius: 1rem 0 0 1rem;" />
                  </div>
                  <div class="col-md-6 col-lg-7 d-flex align-items-center">
                    <div class="card-body p-4 p-lg-5 text-black">
                      <form>
                        <div class="d-flex align-items-center mb-3 pb-1">
                          <i class="fas fa-cubes fa-2x me-3" style="color: #ff6219;"></i>
                          <span class="h1 fw-bold mb-0">Logo</span>
                        </div>
      
                        <h5 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">Sign into your account</h5>
      
                        <div data-mdb-input-init class="form-outline mb-4">
                          <input type="username" id="username" class="form-control form-control-lg" />
                          <label class="form-label" for="username">Username</label>
                        </div>
      
                        <div data-mdb-input-init class="form-outline mb-4">
                          <input type="password" id="password" class="form-control form-control-lg" />
                          <label class="form-label" for="password">Password</label>
                        </div>
      
                        <div class="pt-1 mb-4">
                          <button data-mdb-button-init data-mdb-ripple-init class="btn btn-dark btn-lg btn-block" type="button" id="btLogin">Login</button>
                        </div>
      
                        <a class="small text-muted" href="#!">Forgot password?</a>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.min.js" integrity="sha384-RuyvpeZCxMJCqVUGFI0Do1mQrods/hhxYlcVfGPOfQtPJh0JCw12tUAZ/Mv10S7D" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
      $('#btLogin').on('click', function () {
        const username = $('#username').val();
        const password = $('#password').val();
      
        if (!username || !password) {
          Swal.fire({
            icon: 'warning',
            title: 'Lengkapi Data',
            text: 'Username dan password tidak boleh kosong'
          });
          return;
        }
      
        Swal.fire({
          title: 'Memproses...',
          text: 'Mohon tunggu',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
      
        $.ajax({
          url: '/api/login',
          type: 'POST',
          data: {
            username: username,
            password: password,
            _token: '{{ csrf_token() }}'
          },
          success: function (response) {
            if (response.success) {
              window.location.href = "{{ route('poList') }}";
            } else {
              Swal.fire({
                icon: 'warning',
                title: 'Gagal',
                text: response.message
              });
            }
          },
          error: function (xhr) {
            Swal.fire({
              icon: 'error',
              title: 'Login Gagal',
              text: xhr.responseJSON?.message || 'Terjadi kesalahan pada server'
            });
            console.log(xhr);
          }
        });
      });
    </script>
</body>
</html>