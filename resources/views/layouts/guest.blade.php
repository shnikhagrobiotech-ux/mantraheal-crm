<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sign In') - {{ config('mantraheal.company_name', 'MantraHeal CRM') }}</title>
    
    <!-- Offline Local CSS -->
    <link rel="stylesheet" href="{{ asset('css/tailwind.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mantraheal.css') }}">

    <!-- Offline Local JavaScript -->
    <script src="{{ asset('js/alpine.min.js') }}" defer></script>
    <style>
        body.login-bg {
            background: radial-gradient(circle at 50% 15%, #1e293b 0%, #0f172a 45%, #020617 100%) !important;
            min-height: 100vh;
        }
        .login-card {
            background-color: #0f172a !important;
            border: 1px solid #334155 !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.75), 0 0 0 1px rgba(255, 255, 255, 0.05) !important;
        }
    </style>
</head>
<body class="login-bg h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 text-slate-100">
    <div class="login-card max-w-md w-full space-y-6 p-8 rounded-2xl">
        @yield('content')
    </div>
</body>
</html>
