@extends('layouts.marketer')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Stats Cards -->
        <div class="glass p-6 rounded-2xl card-hover">
            <h3 class="text-slate-400 text-sm mb-2">إجمالي المبيعات</h3>
            <p class="text-3xl font-bold text-white">0 د.ل</p> <!-- Placeholder -->
        </div>
        <div class="glass p-6 rounded-2xl card-hover">
            <h3 class="text-slate-400 text-sm mb-2">عدد العملاء</h3>
            <p class="text-3xl font-bold text-blue-400">{{ count($clients) }}</p>
        </div>
        <div class="glass p-6 rounded-2xl card-hover">
            <h3 class="text-slate-400 text-sm mb-2">المنتجات في المخزون</h3>
            <p class="text-3xl font-bold text-purple-400">{{ $stock->sum('quantity') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Pending Assignments -->
        <div class="glass p-6 rounded-2xl">
            <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                <span class="w-2 h-8 bg-yellow-500 rounded-full"></span>
                طلبات استلام بضاعة (من المخزن)
            </h2>
            @if($pending_assignments->isEmpty())
                <p class="text-slate-500 text-center py-8">لا توجد طلبات معلقة</p>
            @else
                <div class="space-y-4">
                    @foreach($pending_assignments as $assignment)
                        <div class="bg-slate-800/50 p-4 rounded-xl flex justify-between items-center">
                            <div>
                                <h4 class="font-bold text-white">{{ $assignment->product->name }}</h4>
                                <p class="text-sm text-slate-400">الكمية: {{ $assignment->quantity }}</p>
                            </div>
                            <form action="{{ route('marketer.assignments.confirm', $assignment->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-yellow-500/20 text-yellow-400 px-4 py-2 rounded-lg hover:bg-yellow-500/30 transition text-sm font-bold">
                                    تأكيد الاستلام
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- My Stock -->
        <div class="glass p-6 rounded-2xl">
            <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                <span class="w-2 h-8 bg-purple-500 rounded-full"></span>
                مخزوني الحالي
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-right">
                    <thead>
                        <tr class="text-slate-400 text-sm border-b border-slate-700">
                            <th class="pb-3">المنتج</th>
                            <th class="pb-3">الكمية</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-300">
                        @foreach($stock as $item)
                            <tr class="border-b border-slate-800 last:border-0">
                                <td class="py-3">{{ $item->product->name }}</td>
                                <td class="py-3 font-mono text-blue-300">{{ $item->quantity }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Actions Section -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- New Sale -->
        <div class="glass p-6 rounded-2xl">
            <h2 class="text-xl font-bold mb-4 text-blue-400">بيع جديد (للعميل)</h2>
            <form action="{{ route('marketer.invoices.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm text-slate-400 mb-1">العميل</label>
                    <input type="text" name="client_name" placeholder="اسم العميل الجديد" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500 mb-2">
                    <select name="store_id" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500">
                        <option value="">أو اختر عميل سابق</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1">رقم الواتساب</label>
                    <input type="text" name="client_phone" placeholder="966..." class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500">
                </div>
                
                <!-- Simple Item Entry for Demo -->
                <div class="p-3 bg-slate-800/50 rounded-lg">
                    <label class="block text-sm text-slate-400 mb-1">المنتج</label>
                    <select name="items[0][product_id]" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white mb-2">
                        @foreach($stock as $item)
                            @if($item->quantity > 0)
                                <option value="{{ $item->product_id }}">{{ $item->product->name }} (Available: {{ $item->quantity }})</option>
                            @endif
                        @endforeach
                    </select>
                    <input type="number" name="items[0][quantity]" placeholder="الكمية" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white">
                </div>

                <button type="submit" class="w-full btn-primary py-3 rounded-xl font-bold text-white shadow-lg shadow-blue-500/20">
                    إصدار فاتورة وإرسال واتساب
                </button>
            </form>
        </div>

        <!-- Client Return -->
        <div class="glass p-6 rounded-2xl">
            <h2 class="text-xl font-bold mb-4 text-red-400">مرتجع من عميل</h2>
            <form action="{{ route('marketer.returns.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm text-slate-400 mb-1">المنتج المرتجع</label>
                    <select name="product_id" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-red-500">
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1">الكمية</label>
                    <input type="number" name="quantity" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-red-500">
                </div>
                <button type="submit" class="w-full bg-red-500/20 text-red-400 border border-red-500/30 py-3 rounded-xl font-bold hover:bg-red-500/30 transition">
                    تسجيل مرتجع
                </button>
            </form>
        </div>

        <!-- Warehouse Return -->
        <div class="glass p-6 rounded-2xl">
            <h2 class="text-xl font-bold mb-4 text-orange-400">إرجاع للمخزن</h2>
            <form action="{{ route('marketer.returns.warehouse') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm text-slate-400 mb-1">المنتج (فائض)</label>
                    <select name="product_id" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-orange-500">
                        @foreach($stock as $item)
                            <option value="{{ $item->product_id }}">{{ $item->product->name }} ({{ $item->quantity }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1">الكمية</label>
                    <input type="number" name="quantity" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-orange-500">
                </div>
                <button type="submit" class="w-full bg-orange-500/20 text-orange-400 border border-orange-500/30 py-3 rounded-xl font-bold hover:bg-orange-500/30 transition">
                    طلب إرجاع للمخزن
                </button>
            </form>
        </div>
    </div>
@endsection
