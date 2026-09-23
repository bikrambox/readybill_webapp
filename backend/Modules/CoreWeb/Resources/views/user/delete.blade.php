<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete User</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center mb-4">Delete User Account</h2>
                        <form id="deleteForm" class="needs-validation" novalidate>
                            @csrf
                            <div class="mb-3">
                                <label for="mobile_number" class="form-label">Mobile Number</label>
                                <input type="text" class="form-control" id="mobile_number" name="mobile_number" required>
                                <div class="invalid-feedback">
                                    Please enter a mobile number.
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="invalid-feedback">
                                    Please enter a password.
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Check & Delete</button>
                        </form>
                        <p id="message" class="mt-3 text-center text-muted"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script>
        // Bootstrap form validation
        (function () {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();

        // Form submission logic
        document.getElementById('deleteForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            if (!form.checkValidity()) return;

            const mobileNumber = document.getElementById('mobile_number').value;
            const password = document.getElementById('password').value;
            const message = document.getElementById('message');

            try {
                const response = await fetch('/user/check-delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    },
                    body: JSON.stringify({
                        mobile_number: mobileNumber,
                        password: password,
                    }),
                });

                const result = await response.json();
                message.textContent = result.message;
                message.className = 'mt-3 text-center';
                message.classList.add(response.ok ? 'text-success' : 'text-danger');

                if (response.ok) {
                    form.reset();
                    form.classList.remove('was-validated');
                }
            } catch (error) {
                message.textContent = 'An error occurred. Please try again.';
                message.className = 'mt-3 text-center text-danger';
            }
        });
    </script>
</body>
</html>