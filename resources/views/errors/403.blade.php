<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - 403 Forbidden</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="{{ asset('css/purity.css') }}">
    
    <style>
        body {
            background: #F7FAFC;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .error-card {
            background: #FFFFFF;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(226, 232, 240, 0.8);
            padding: 48px;
            width: 100%;
            max-width: 440px;
            text-align: center;
            margin: 20px;
        }
        .error-icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: rgba(229, 62, 62, 0.08);
            color: #E53E3E;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }
        .error-title {
            font-size: 22px;
            font-weight: 800;
            color: #2D3748;
            margin: 0 0 10px;
        }
        .error-desc {
            font-size: 13.5px;
            color: #718096;
            line-height: 1.6;
            margin: 0 0 32px;
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            background: var(--primary-gradient, #319795);
            color: #FFFFFF;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(49, 151, 149, 0.2);
            transition: all 0.2s ease;
        }
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(49, 151, 149, 0.3);
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
            </svg>
        </div>
        
        <h1 class="error-title">Access Denied</h1>
        <p class="error-desc">You do not have access to view this page. If you believe this is an error, please contact your system administrator.</p>
        
        <a href="{{ url('/') }}" class="btn-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m12 19-7-7 7-7"/>
                <path d="M5 12h14"/>
            </svg>
            Go Back
        </a>
    </div>
</body>
</html>
