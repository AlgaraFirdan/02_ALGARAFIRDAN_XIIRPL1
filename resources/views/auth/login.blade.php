<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Precision Flow</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-body">
<div class="login-atmosphere"></div>

<main class="login-wrapper">
    <section class="login-card">
        <div class="login-logo">P</div>
        <h1>Selamat Datang Di Flow</h1>
        <p>Enter your credentials to access the intelligent monolith.</p>

        <form method="POST" action="{{ route('login.process') }}" class="login-form">
            @csrf

            <label for="credential">EMAIL OR USERNAME</label>
            <input
                id="credential"
                type="text"
                name="credential"
                value="{{ old('credential') }}"
                placeholder="john.doe@precisionflow.com"
                required
                autofocus
            >

            <label for="password">PASSWORD</label>
            <input
                id="password"
                type="password"
                name="password"
                placeholder="••••••••"
                required
            >

            @error('credential')
                <div class="login-error">{{ $message }}</div>
            @enderror

            <button type="submit" class="login-btn">Login</button>
        </form>
    </section>
</main>

<footer class="login-footer">&copy; 2026 SMART PARKING SYSTEM</footer>
</body>
</html>
