<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sign In') - {{ config('mantraheal.company_name', 'MantraHeal CRM') }}</title>
    
    <!-- Modern Typography (Google Fonts with System Fallbacks) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Local CSS & Theme Engine -->
    <link rel="stylesheet" href="/css/tailwind.min.css">
    <link rel="stylesheet" href="/css/mantraheal.css">

    <!-- Local JavaScript -->
    <script src="/js/alpine.min.js" defer></script>
    <style>
        html {
            height: 100%;
        }
        body.login-bg {
            background: radial-gradient(ellipse at 50% 20%, #0f172a 0%, #070b14 60%, #020617 100%) !important;
            min-height: 100vh;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            margin: 0 !important;
            padding: 1.5rem !important;
            box-sizing: border-box !important;
            color: #f1f5f9;
        }
        .login-card {
            background: rgba(15, 23, 42, 0.92) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(51, 65, 85, 0.8) !important;
            border-radius: 1.25rem !important;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.9), 0 0 0 1px rgba(255, 255, 255, 0.08) !important;
            width: 100% !important;
            max-width: 28rem !important;
            padding: 2.25rem !important;
            box-sizing: border-box !important;
        }
    </style>
</head>
<body class="login-bg h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 text-slate-100">
    <div class="login-card max-w-md w-full space-y-6 p-8 rounded-2xl">
        @yield('content')
    </div>
</body>
</html>
