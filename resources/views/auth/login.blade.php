<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | {{ config('app.name', 'Laravel') }}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fc;
        }
        .login-card {
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.05);
            background: #ffffff;
            border-radius: 20px;
        }
        .btn-primary {
            background-color: #554df7;
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background-color: #4338ca;
            transform: translateY(-1px);
        }
        .btn-primary:active {
            transform: translateY(0);
        }
        .custom-input {
            transition: all 0.2s;
        }
        .custom-input:focus {
            border-color: #554df7;
            box-shadow: 0 0 0 4px rgba(85, 77, 247, 0.1);
        }
        .error-input {
            border-color: #ef4444 !important;
            color: #ef4444 !important;
        }
        .error-text {
            color: #ef4444;
            font-size: 14px;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-[440px]">
        <!-- Header Section -->
        <div class="text-center mb-10">
            <h1 class="text-[32px] font-extrabold text-[#0f172a] tracking-tight mb-2">Welcome Back</h1>
            <p class="text-[#64748b] text-base font-medium">Sign in to your account</p>
        </div>

        <!-- Login Card -->
        <div class="login-card p-10">
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf
                
                <!-- Email Field -->
                <div class="space-y-2">
                    <label for="email" class="block text-sm font-semibold text-[#334155]">Email address</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94a3b8] group-focus-within:text-[#554df7] transition-colors">
                            <i data-lucide="mail" class="w-5 h-5"></i>
                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" 
                            class="custom-input block w-full pl-11 pr-4 py-3.5 bg-white border border-[#e2e8f0] rounded-[12px] text-[#0f172a] placeholder-[#94a3b8] focus:outline-none @error('email') error-input @enderror"
                            placeholder="Enter your email" required autocomplete="email" autofocus>
                    </div>
                    @error('email')
                        <p class="error-text font-medium mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Password Field -->
                <div class="space-y-2">
                    <label for="password" class="block text-sm font-semibold text-[#334155]">Password</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94a3b8] group-focus-within:text-[#554df7] transition-colors">
                            <i data-lucide="lock" class="w-5 h-5"></i>
                        </div>
                        <input type="password" id="password" name="password" 
                            class="custom-input block w-full pl-11 pr-4 py-3.5 bg-white border border-[#e2e8f0] rounded-[12px] text-[#0f172a] placeholder-[#94a3b8] focus:outline-none @error('password') error-input @enderror"
                            placeholder="••••••••" required autocomplete="current-password">
                    </div>
                    @error('password')
                        <p class="error-text font-medium mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <div class="inline-flex items-center cursor-pointer">
                        <input id="remember-me" name="remember" type="checkbox" {{ old('remember') ? 'checked' : '' }}
                            class="w-5 h-5 rounded border-[#cbd5e1] text-[#554df7] focus:ring-[#554df7] transition-all cursor-pointer">
                        <label for="remember-me" class="ml-3 block text-sm font-medium text-[#475569] cursor-pointer selection:bg-transparent">
                            Remember me
                        </label>
                    </div>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm font-semibold text-[#554df7] hover:text-[#4338ca] transition-colors">
                            Forgot password?
                        </a>
                    @endif
                </div>

                <!-- Sign In Button -->
                <button type="submit" 
                    class="btn-primary w-full flex justify-center py-4 px-4 border border-transparent rounded-[12px] shadow-lg shadow-indigo-100/50 text-base font-bold text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#554df7]">
                    Sign in
                </button>
            </form>
        </div>

        @if (Route::has('register'))
            <p class="text-center mt-8 text-[#64748b] text-sm">
                Don't have an account? <a href="{{ route('register') }}" class="text-[#554df7] font-bold hover:underline">Sign up for free</a>
            </p>
        @endif
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
</body>
</html>
