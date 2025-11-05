<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow p-4">
        <h4 class="text-center mb-4">Login</h4>

        <form id="loginForm">
          @csrf

          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" placeholder="admin1.alpha@newcompany.com">
          </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="secretpassword">
          </div>

          <div class="mb-3">
            <label class="form-label">Subdomain</label>
            <input type="text" name="company_subdomain" class="form-control" placeholder="alpha">
          </div>

          <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <button id="logoutBtn" class="btn btn-secondary mt-3">Logout</button>

        <div id="responseMessage" class="mt-3 text-center"></div>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const form = e.target;
    const responseDiv = document.getElementById('responseMessage');
    responseDiv.innerHTML = "Logging in...";

    // Collect form data
    const formData = {
        email: form.email.value,
        password: form.password.value
    };

    // Tenant subdomain
    const subdomain = form.company_subdomain.value;

    try {
        const res = await fetch('http://127.0.0.1:8000/api/login', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': 'bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL3JlZ2lzdGVyIiwiaWF0IjoxNzYyMzMyODMyLCJleHAiOjE3NjIzNDAwMzIsIm5iZiI6MTc2MjMzMjgzMiwianRpIjoiajVjZUJUNkN4aVhiMFpFVCIsInN1YiI6IjEyIiwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyIsImNvbXBhbnlfaWQiOjQsImNvbXBhbnlfc3ViZG9tYWluIjoiYWEifQ.ivmx_omWfpwZWu0kLdvXbzyN7H7Dd7iSwPNdjhQPuMM',
                'X-Tenant-Subdomain': subdomain
            },
            body: JSON.stringify(formData)
        });

        const data = await res.json();

        if (res.ok) {
            localStorage.setItem('access_token', data.access_token);
            localStorage.setItem('subdomain', subdomain); 

            responseDiv.innerHTML = `
                <div class='alert alert-success text-start'>
                    <strong>Login Successful!</strong><br>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                </div>
            `;
            form.reset();
        } else {
            responseDiv.innerHTML = `
                <div class='alert alert-danger text-start'>
                    <strong>Login Failed:</strong><br>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                </div>
            `;
        }

    } catch (err) {
        console.error(err);
        responseDiv.innerHTML = `
            <div class='alert alert-danger'>
                Error: ${err.message}
            </div>
        `;
    }
});

document.getElementById('logoutBtn').addEventListener('click', async function () {
    const responseDiv = document.getElementById('responseMessage');
    responseDiv.innerHTML = "Logging out...";

    const subdomain = document.querySelector('input[name="company_subdomain"]').value;
    const token = localStorage.getItem('access_token'); 

    try {
        const res = await fetch('http://127.0.0.1:8000/api/logout', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`,
                'X-Tenant-Subdomain': subdomain
            },
            body: JSON.stringify({})
        });

        const data = await res.json();

        if (res.ok) {
            responseDiv.innerHTML = `
                <div class='alert alert-success text-start'>
                    <strong>Login Successful!</strong><br>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                </div>
            `;            
            localStorage.removeItem('access_token');
        } else {
            responseDiv.innerHTML = `<div class='alert alert-danger'>Logout failed: ${JSON.stringify(data)}</div>`;
        }

    } catch (err) {
        console.error(err);
        responseDiv.innerHTML = `<div class='alert alert-danger'>Error: ${err.message}</div>`;
    }
});

</script>

</body>
</html>
