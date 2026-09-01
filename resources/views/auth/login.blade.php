<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Portal - Indibiz Queue</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body class="bg-[#F8F9FA] min-h-screen flex items-center justify-center p-4" x-data="{ activeTab: 'cs' }">

    <div class="bg-white rounded-2xl shadow-2xl border border-[#E0E3E8] max-w-md w-full overflow-hidden transition-all">
        
        <!-- SWITCH TAB MODE LOGIN -->
        <div class="grid grid-cols-2 bg-gray-100 p-1.5 border-b border-[#E0E3E8]">
            <button type="button" @click="activeTab = 'cs'" 
                :class="activeTab === 'cs' ? 'bg-white text-[#00509E] shadow-sm font-extrabold' : 'text-gray-500 font-bold hover:text-gray-800'"
                class="py-2.5 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                <span class="material-symbols-outlined text-base">support_agent</span>
                <span>Petugas CS</span>
            </button>
            <button type="button" @click="activeTab = 'admin'" 
                :class="activeTab === 'admin' ? 'bg-white text-[#EE2E24] shadow-sm font-extrabold' : 'text-gray-500 font-bold hover:text-gray-800'"
                class="py-2.5 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                <span class="material-symbols-outlined text-base">admin_panel_settings</span>
                <span>Super Admin</span>
            </button>
        </div>

        <!-- HEADER CS -->
        <div x-show="activeTab === 'cs'" class="bg-[#00509E] text-white p-5 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white">
                <span class="material-symbols-outlined text-2xl">support_agent</span>
            </div>
            <div>
                <h3 class="text-lg font-extrabold text-white leading-tight">Portal Petugas CS</h3>
                <p class="text-[11px] text-gray-200">Akses Console Loket Pelayanan</p>
            </div>
        </div>

        <!-- HEADER ADMIN -->
        <div x-show="activeTab === 'admin'" class="bg-[#181C20] text-white p-5 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#EE2E24] flex items-center justify-center text-white">
                <span class="material-symbols-outlined text-2xl">analytics</span>
            </div>
            <div>
                <h3 class="text-lg font-extrabold text-white leading-tight">Portal Super Admin</h3>
                <p class="text-[11px] text-gray-200">Akses Dashboard & Laporan</p>
            </div>
        </div>

        <!-- AREA FORM LOGIN -->
        <div class="p-6">
            
            <!-- FORM 1: KHUSUS PETUGAS CS -->
            <div x-show="activeTab === 'cs'">
                <div class="bg-[#F8F9FA] p-3.5 rounded-xl border border-[#E0E3E8] text-xs text-[#5D3F3B] mb-6">
                    <p class="font-bold text-[#181C20] mb-0.5">🔒 Area Petugas Loket</p>
                    Gunakan akun CS untuk membuka Konsol Pemanggilan Loket.
                </div>

                <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="login_type" value="cs">

                    <div>
                        <label class="block text-xs font-bold text-[#181C20] mb-1.5">Username CS</label>
                        <input type="text" name="username" value="{{ old('username') }}" required placeholder="Contoh: cs1" class="w-full px-4 py-3 border border-[#E0E3E8] bg-[#F8F9FA] rounded-xl text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#00509E]">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#181C20] mb-1.5">Password / PIN</label>
                        <input type="password" name="password" required placeholder="Masukkan password" class="w-full px-4 py-3 border border-[#E0E3E8] bg-[#F8F9FA] rounded-xl text-sm font-semibold tracking-widest focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#00509E]">
                    </div>

                    @if(session('error'))
                        <p class="text-xs font-semibold text-[#BA1A1A] mt-1 text-center">{{ session('error') }}</p>
                    @endif

                    <button type="submit" class="w-full text-white bg-[#00509E] hover:bg-[#003C7E] p-3.5 rounded-xl font-bold text-sm flex items-center justify-center gap-2 transition-all shadow-sm mt-6 cursor-pointer">
                        <span>Masuk ke CS Console</span>
                        <span class="material-symbols-outlined text-lg">login</span>
                    </button>
                </form>
            </div>

            <!-- FORM 2: KHUSUS SUPER ADMIN -->
            <div x-show="activeTab === 'admin'">
                <div class="bg-[#F8F9FA] p-3.5 rounded-xl border border-[#E0E3E8] text-xs text-[#5D3F3B] mb-6">
                    <p class="font-bold text-[#181C20] mb-0.5">🔒 Area Super Admin</p>
                    Gunakan akun Super Admin untuk memantau analitik & omset.
                </div>

                <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="login_type" value="admin">

                    <div>
                        <label class="block text-xs font-bold text-[#181C20] mb-1.5">Username Admin</label>
                        <input type="text" name="username" value="{{ old('username') }}" required placeholder="Contoh: admin" class="w-full px-4 py-3 border border-[#E0E3E8] bg-[#F8F9FA] rounded-xl text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#EE2E24]">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#181C20] mb-1.5">Password / PIN Admin</label>
                        <input type="password" name="password" required placeholder="Masukkan password admin" class="w-full px-4 py-3 border border-[#E0E3E8] bg-[#F8F9FA] rounded-xl text-sm font-semibold tracking-widest focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#EE2E24]">
                    </div>

                    @if(session('error'))
                        <p class="text-xs font-semibold text-[#BA1A1A] mt-1 text-center">{{ session('error') }}</p>
                    @endif

                    <button type="submit" class="w-full text-white bg-[#EE2E24] hover:bg-[#CE1111] p-3.5 rounded-xl font-bold text-sm flex items-center justify-center gap-2 transition-all shadow-sm mt-6 cursor-pointer">
                        <span>Masuk ke Admin Dashboard</span>
                        <span class="material-symbols-outlined text-lg">login</span>
                    </button>
                </form>
            </div>

        </div>

        <div class="bg-[#F8F9FA] px-6 py-3 border-t border-[#E0E3E8] text-center text-xs text-[#5D3F3B] font-semibold">
            Indibiz Staff Access
        </div>
    </div>

</body>
</html>