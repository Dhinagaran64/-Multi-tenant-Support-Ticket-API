<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Support Ticket System')</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            min-height: 100vh;
        }

        .navbar-brand {
            font-weight: 600;
        }

        .card {
            border: none;
        }

        .ticket-description {
            max-width: 350px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>

    @stack('styles')
</head>

<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">

        <a class="navbar-brand" href="{{ url('/dashboard') }}">
            Support Ticket System
        </a>

        @auth
            <div class="d-flex align-items-center">
                <span class="text-white me-3">
                    {{ auth()->user()->name }}
                </span>

                <form
                    action="{{ route('logout') }}"
                    method="POST"
                    class="d-inline"
                    onsubmit="localStorage.removeItem('api_token'); localStorage.removeItem('user');"
                >
                    @csrf

                    <button
                        type="submit"
                        class="btn btn-outline-light btn-sm"
                    >
                        Logout
                    </button>
                </form>
            </div>
        @endauth

    </div>
</nav>

<main class="container py-4">

    <div id="alertContainer"></div>

    @yield('content')

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')

</body>
</html>