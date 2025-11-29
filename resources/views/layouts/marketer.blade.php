<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'لوحة المسوق') - نظام إدارة المبيعات</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Cairo', sans-serif;
            background: #0f172a; /* Slate 900 */
            color: #e2e8f0; /* Slate 200 */
        }
        .glass {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        .card-hover:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
            border-color: rgba(59, 130, 246, 0.5);
        }
    </style>
</head>
<body class="min-h-screen">
    <nav class="glass sticky top-0 z-50 px-6 py-4 mb-8 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-purple-500">
                لوحة المسوق
            </h1>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-sm text-slate-400">مرحباً، {{ auth()->user()->name ?? 'المسوق' }}</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-sm text-red-400 hover:text-red-300 transition">تسجيل خروج</button>
            </form>
        </div>
    </nav>

    <main class="container mx-auto px-4 pb-12">
        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/20 text-green-400 p-4 rounded-xl mb-6 animate-fade-in">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-xl mb-6 animate-fade-in">
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-xl mb-6 animate-fade-in">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>- {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <script>
        // Simple interactions
    </script>
</body>
</html>
