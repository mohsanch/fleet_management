<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sign In - Fleet Management</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%234fd1c5' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2'/%3E%3Cpath d='M19 18h2a1 1 0 0 0 1-1v-5.14a2 2 0 0 0-.586-1.414l-2.83-2.83A2 2 0 0 0 17.17 7H14'/%3E%3Ccircle cx='7.5' cy='18.5' r='2.5'/%3E%3Ccircle cx='17.5' cy='18.5' r='2.5'/%3E%3C/svg%3E">
    
    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="{{ asset('css/purity.css') }}">
</head>
<body>
    <div class="auth-container">
        <!-- Form Side -->
        <div class="auth-form-side">
            <!-- Brand Logo -->
            <a href="#" class="auth-header">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124l-.321-5.128a2.25 2.25 0 0 0-2.25-2.112h-2.25M9.75 8.25h1.5a.75.75 0 0 1 .75.75v.75H8.25V9a.75.75 0 0 1 .75-.75Zm0 0V4.5a.75.75 0 0 1 .75-.75h3.75a.75.75 0 0 1 .75.75v3.75M9 11.25V14.25m6-3v3M3.375 14.25h17.25c.621 0 1.125-.504 1.125-1.125v-1.5a1.125 1.125 0 0 0-1.125-1.125H3.375A1.125 1.125 0 0 0 2.25 12v1.5c0 .621.504 1.125 1.125 1.125Z" />
                </svg>
                <span>Fleet Management</span>
            </a>
            
            <!-- Welcome Header -->
            <div class="auth-welcome">
                <h2>Welcome Back</h2>
                <p>Enter your email and password to sign in</p>
            </div>
            
            <!-- Login Form -->
            <form action="{{ url('/login') }}" method="POST" class="auth-form">
                @csrf
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-input" placeholder="Your email" value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-input" placeholder="Your password" required>
                    @error('password')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Remember Me Toggle -->
                <div class="form-switch-group">
                    <label class="switch">
                        <input type="checkbox" name="remember" id="remember">
                        <span class="slider"></span>
                    </label>
                    <span class="switch-label">Remember me</span>
                </div>
                
                <button type="submit" class="btn-signin">SIGN IN</button>
            </form>
            
           
        </div>
        
        <!-- Illustration Side -->
        <div class="auth-visual-side">
            <div class="auth-visual-bg"></div>
            <div class="auth-visual-card">
                <h3>Fleet Management System</h3>
                <p>Control income, track operational expenses, maintain vehicle performance, and manage driver logs from one centralized Purity UI dashboard.</p>
            </div>
        </div>
    </div>
</body>
</html>
