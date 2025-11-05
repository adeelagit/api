<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Company</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="card shadow-lg p-4">
            <form id="registerForm">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="text-center mb-4">User Registration</h4>

                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Admin1 User Alpha">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="admin1.alpha@newcompany.com">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h4 class="text-center mb-4">Company Registration</h4>

                        <div class="mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control" placeholder="Alpha Corp International">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mobile Number</label>
                            <input type="text" name="company_mobile_number" class="form-control" placeholder="9876543210">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subdomain</label>
                            <input type="text" name="company_subdomain" class="form-control" placeholder="alpha">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="company_address" class="form-control" placeholder="123 Main St, Anytown">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="company_location" class="form-control" placeholder="New York">
                        </div>
                    </div>
                </div>


                <button type="submit" class="btn btn-primary w-100">Register</button>

            </form>

            <div id="responseMessage" class="mt-3 text-center"></div>
        </div>
    </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const form = e.target;
    const formData = {
        name: form.name.value,
        email: form.email.value,
        password: form.password.value,
        password_confirmation: form.password_confirmation.value,
        company_name: form.company_name.value,
        company_mobile_number: form.company_mobile_number.value,
        company_subdomain: form.company_subdomain.value,
        company_address: form.company_address.value,
        company_location: form.company_location.value
    };

    const responseDiv = document.getElementById('responseMessage');
    responseDiv.innerHTML = "Submitting...";

    try {
        const res = await fetch('http://127.0.0.1:8000/api/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const data = await res.json();

       if (res.ok) {
            responseDiv.innerHTML = `
                    <div class='alert alert-success text-start'>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    </div>
                `;
            form.reset();
        } else {
            responseDiv.innerHTML = `<div class='alert alert-danger'>${data.message || 'Registration failed.'}</div>`;
        }

    } catch (err) {
        console.error(err);
        responseDiv.innerHTML = `<div class='alert alert-danger'>Error: ${err.message}</div>`;
    }
});
</script>

</body>
</html>
