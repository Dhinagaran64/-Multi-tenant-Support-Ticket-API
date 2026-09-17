@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-5 col-lg-4">

        <div class="card shadow-sm">
            <div class="card-body p-4">

                <div class="text-center mb-4">
                    <h3>Welcome Back</h3>
                    <p class="text-muted mb-0">
                        Sign in to manage your tickets
                    </p>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Middleware / Session Error --}}
                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- API Login Error --}}
                <div id="loginError"></div>

                <form id="loginForm">

                    <div class="mb-3">
                        <label class="form-label">Email</label>

                        <input
                            type="email"
                            id="email"
                            class="form-control"
                            placeholder="john@example.com"
                            required
                        >

                        <div id="emailError" class="text-danger small mt-1"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>

                        <input
                            type="password"
                            id="password"
                            class="form-control"
                            placeholder="••••••••"
                            required
                        >

                        <div id="passwordError" class="text-danger small mt-1"></div>
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                        id="loginBtn"
                    >
                        Login
                    </button>

                </form>

            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('loginForm').addEventListener('submit', async function (event) {

    event.preventDefault();

    const button = document.getElementById('loginBtn');
    const errorContainer = document.getElementById('loginError');

    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');

    // Clear previous errors
    errorContainer.innerHTML = '';
    emailError.innerText = '';
    passwordError.innerText = '';

    button.disabled = true;
    button.innerText = 'Logging in...';

    try {

        const response = await fetch('/api/login', {
            method: 'POST',

            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },

            body: JSON.stringify({
                email: document.getElementById('email').value,
                password: document.getElementById('password').value
            })
        });

        const result = await response.json();

        /*
         * Validation errors
         * Laravel normally returns:
         *
         * {
         *   "message": "The given data was invalid.",
         *   "errors": {
         *      "email": ["The email field is required."]
         *   }
         * }
         */

        if (response.status === 422) {

            if (result.errors) {

                if (result.errors.email) {
                    emailError.innerText = result.errors.email[0];
                }

                if (result.errors.password) {
                    passwordError.innerText = result.errors.password[0];
                }
            }

            throw new Error(result.message || 'Please check the entered details.');
        }

        /*
         * Invalid credentials / other API errors
         */
        if (!response.ok) {
            throw new Error(
                result.message || 'Unable to login. Please try again.'
            );
        }

        /*
         * Successful login
         */
        if (!result.access_token) {
            throw new Error('Login successful, but authentication token was not received.');
        }

        localStorage.setItem('api_token', result.access_token);
        localStorage.setItem(
            'user',
            JSON.stringify(result.user || {})
        );

        window.location.href = '/';

    } catch (error) {

        /*
         * Network error or API error
         */
        if (!errorContainer.innerHTML) {

            errorContainer.innerHTML = `
                <div class="alert alert-danger">
                    ${escapeHtml(error.message)}
                </div>
            `;
        }

    } finally {

        button.disabled = false;
        button.innerText = 'Login';
    }
});


function escapeHtml(value) {

    const div = document.createElement('div');

    div.textContent = value;

    return div.innerHTML;
}
</script>
@endpush