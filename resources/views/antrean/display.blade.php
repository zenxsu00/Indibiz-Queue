<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Display Monitor Antrean - Indibiz Service Desk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #060913; 
        }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.03); }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }
    </style>
</head>
<body class="text-white h-screen w-screen flex flex-col justify-between overflow-hidden select-none p-3.5 gap-3">

    <!-- TOP BAR HEADER -->
    <header class="bg-[#0D1322] border border-slate-800/80 rounded-2xl px-4 py-2.5 flex items-center justify-between shrink-0 shadow-lg">
        <div class="flex items-center gap-3">
            <img src="{{ asset('img/LogoTeks.png') }}" alt="Logo Indibiz" class="h-6 object-contain brightness-0 invert">
            <div class="h-4 w-[1px] bg-slate-700"></div>
            <div>
                <h1 class="text-[10px] font-extrabold tracking-wider text-slate-400 uppercase leading-none">Pusat Layanan Terpadu Enterprise</h1>
                <p class="text-xs font-black text-slate-200 mt-0.5 leading-none">RUANG TUNGGU CUSTOMER SERVICE & ENTERPRISE DESK</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="flex items-center gap-1.5 bg-slate-900 border border-slate-800 px-2.5 py-1 rounded-xl">
                <span class="material-symbols-outlined text-xs text-red-500">graphic_eq</span>
                <span class="text-[10px] text-slate-400 font-bold">Synthesizer:</span>
                <select id="voice-select" class="bg-transparent text-slate-200 text-[11px] font-bold focus:outline-none max-w-[150px] truncate">
                    <option value="">Memuat Suara...</option>
                </select>
            </div>

            <button onclick="playTestCall()" class="bg-red-600/20 hover:bg-red-600/30 text-red-400 border border-red-500/30 px-3 py-1 rounded-xl font-bold text-[11px] transition-all flex items-center gap-1.5 cursor-pointer">
                <span class="material-symbols-outlined text-sm">volume_up</span>
                <span>PANGGIL ULANG</span>
            </button>

            <div id="live-clock" class="font-mono text-sm font-black bg-slate-900 border border-slate-700/80 px-3 py-1 rounded-xl text-emerald-400">
                00:00:00 WIB
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT: 2 KOLOM (PRESISI SESUAI REFERENSI) -->
    <main class="flex-1 grid grid-cols-12 gap-3.5 min-h-0 overflow-hidden">
        
        <!-- KOLOM KIRI (SPAN 8/9): DISPLAY PANGGILAN UPTIME + STATUS MEJA -->
        <div class="col-span-8 lg:col-span-9 flex flex-col gap-3.5 min-h-0">
            
            <!-- BOX HERO: SEDANG DIPANGGIL -->
            <div class="flex-1 bg-gradient-to-b from-[#11182B] to-[#0A0F1D] border border-red-500/30 rounded-3xl p-5 flex flex-col justify-between shadow-2xl relative overflow-hidden">
                
                <div class="flex items-center justify-between border-b border-white/5 pb-2.5">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>
                        <span class="text-xs font-black tracking-widest text-red-500 uppercase">SEDANG DIPANGGIL</span>
                        <span class="text-xs font-bold text-slate-400 ml-2">PANGGILAN SUARA AKTIF</span>
                    </div>
                    <div class="flex items-center gap-1 text-[11px] text-red-400 font-mono">
                        <span class="material-symbols-outlined text-sm">graphic_eq</span>
                    </div>
                </div>

                <div class="text-center py-2 my-auto">
                    <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 block mb-1">NOMOR TIKET ANTREAN</span>
                    <h1 id="hero-nomor-antrian" class="text-7xl lg:text-8xl font-black text-white tracking-tight drop-shadow-[0_0_35px_rgba(238,46,36,0.35)]">
                        ---
                    </h1>
                    
                    <div class="inline-flex items-center gap-1.5 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-1.5 rounded-xl font-bold text-xs mt-3">
                        <span class="material-symbols-outlined text-sm">check_circle</span>
                        <span>SILAKAN MENUJU MEJA PELAYANAN SEKARANG</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-3 border-t border-white/5">
                    <div class="bg-[#080D1A]/80 p-3 rounded-2xl border border-slate-800/80">
                        <span class="text-[9px] uppercase font-bold text-slate-400 block">TUJUAN LOKET</span>
                        <p id="hero-nama-meja" class="text-xl font-black text-white mt-0.5">MEJA CS --</p>
                    </div>
                    <div class="bg-[#080D1A]/80 p-3 rounded-2xl border border-slate-800/80">
                        <span class="text-[9px] uppercase font-bold text-slate-400 block">KATEGORI LAYANAN</span>
                        <p id="hero-nama-layanan" class="text-sm font-bold text-red-400 truncate mt-1">Belum Ada Panggilan</p>
                    </div>
                </div>
            </div>

            <!-- STATUS MEJA PELAYANAN CUSTOMER SERVICE (BARIS BWAH) -->
            <div class="bg-[#0D1322] border border-slate-800/80 rounded-2xl p-3 flex flex-col gap-2 shrink-0">
                <div class="flex items-center justify-between text-[11px] font-bold border-b border-slate-800 pb-1.5">
                    <span class="text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        STATUS MEJA PELAYANAN CUSTOMER SERVICE
                    </span>
                    <span class="text-slate-500 text-[10px]">Total 4 Meja Aktif &bull; Update Realtime</span>
                </div>

                <div id="grid-meja-pelayanan" class="grid grid-cols-4 gap-2.5">
                    <!-- Cards CS akan di-render via JS -->
                </div>
            </div>

        </div>

        <!-- KOLOM KANAN (SPAN 4/3): ANTREAN BERIKUTNYA -->
        <div class="col-span-4 lg:col-span-3 bg-[#0D1322] border border-slate-800/80 rounded-2xl p-3.5 flex flex-col h-full min-h-0 shadow-lg">
            <div class="flex items-center justify-between border-b border-slate-800 pb-2.5 mb-3 shrink-0">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-amber-400 text-base">schedule</span>
                    <div>
                        <h2 class="text-xs font-black uppercase text-white leading-tight">ANTREAN BERIKUTNYA</h2>
                    </div>
                </div>
                <span id="total-menunggu" class="text-[10px] bg-slate-900 text-amber-400 border border-slate-800 px-2.5 py-0.5 rounded-full font-bold">0 Menunggu</span>
            </div>

            <div id="box-antrean-menunggu" class="flex flex-col gap-2.5 overflow-y-auto custom-scrollbar flex-1 pr-1">
                <!-- List Antrean Menunggu dirender via JS -->
            </div>

            <div class="mt-3 pt-3 border-t border-slate-800 shrink-0 bg-slate-900/50 p-2.5 rounded-xl border border-slate-800/50 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-white">Pantau Antrean di Ponsel</p>
                    <p class="text-[9px] text-slate-400">Scan QR Code pada struk nomor tiket Anda</p>
                </div>
                <div class="w-8 h-8 bg-white text-slate-900 rounded-lg flex items-center justify-center font-black text-xs shrink-0">
                    <span class="material-symbols-outlined text-base">qr_code_2</span>
                </div>
            </div>
        </div>

    </main>

    <!-- FOOTER MARQUEE / RUNNING TEXT -->
    <footer class="bg-[#0D1322] border border-slate-800 text-white rounded-xl p-1.5 flex items-center gap-3 shrink-0 overflow-hidden text-xs shadow-md">
        <span class="bg-red-600 text-white text-[10px] font-black uppercase px-2.5 py-1 rounded-lg shrink-0 flex items-center gap-1">
            <span class="material-symbols-outlined text-xs">info</span> INFORMASI
        </span>
        <marquee behavior="scroll" direction="left" class="font-semibold text-slate-300 text-xs">
            Selamat Datang di Telkom Indibiz Service Desk &bull; Ciptakan Peluang, Wujudkan Harapan Bersama Ekosistem Solusi Digital Dunia Usaha &bull; Harap Siapkan Kartu Identitas dan Nomor Tiket Antrean Anda saat Menuju Loket Pelayanan.
        </marquee>
        <span class="text-[10px] font-mono text-emerald-400 shrink-0 px-2 border-l border-slate-800 flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Server Antrean: Online
        </span>
    </footer>

    <!-- SCRIPT REALTIME & SPEECH SYNTHESIS -->
    <script>
        var lastCallUniqueKey = '';
        var audioCtx = null;
        var availableVoices = [];

        setInterval(function() {
            var now = new Date();
            var hours = String(now.getHours()).padStart(2, '0');
            var minutes = String(now.getMinutes()).padStart(2, '0');
            var seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('live-clock').innerText = hours + ':' + minutes + ':' + seconds + ' WIB';
        }, 1000);

        function populateVoiceList() {
            if (!('speechSynthesis' in window)) return;
            availableVoices = window.speechSynthesis.getVoices();
            var selectElem = document.getElementById('voice-select');
            selectElem.innerHTML = '';

            if (availableVoices.length === 0) return;

            var indoVoices = availableVoices.filter(function(v) {
                return v.lang.includes('id') || v.lang.includes('ID') || v.name.toLowerCase().includes('indonesi');
            });

            var displayVoices = indoVoices.length > 0 ? indoVoices : availableVoices;

            displayVoices.forEach(function(voice) {
                var option = document.createElement('option');
                option.value = voice.name;
                option.textContent = voice.name;
                if (voice.name.includes('Gadis') || voice.name.includes('Indonesian') || voice.lang === 'id-ID') {
                    option.selected = true;
                }
                selectElem.appendChild(option);
            });
        }

        if ('speechSynthesis' in window) {
            populateVoiceList();
            window.speechSynthesis.onvoiceschanged = populateVoiceList;
        }

        function playChime(onComplete) {
            try {
                if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                if (audioCtx.state === 'suspended') audioCtx.resume();

                function tone(freq, duration, delay) {
                    setTimeout(() => {
                        var osc = audioCtx.createOscillator();
                        var gain = audioCtx.createGain();
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
                        gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + duration);
                        osc.connect(gain);
                        gain.connect(audioCtx.destination);
                        osc.start();
                        osc.stop(audioCtx.currentTime + duration);
                    }, delay);
                }

                tone(587.33, 0.6, 0);   
                tone(783.99, 0.8, 500); 

                if (onComplete) setTimeout(onComplete, 1200);
            } catch (e) {
                if (onComplete) onComplete();
            }
        }

        function formatEjaanIndonesia(nomor) {
            var bagian = nomor.split('-');
            if (bagian.length > 1) {
                var kodeHuruf = bagian[0]; 
                var angkaStr = bagian[1];   
                var ejaanAngka = angkaStr
                    .replace(/0/g, ' nol ').replace(/1/g, ' satu ').replace(/2/g, ' dua ')
                    .replace(/3/g, ' tiga ').replace(/4/g, ' empat ').replace(/5/g, ' lima ')
                    .replace(/6/g, ' enam ').replace(/7/g, ' tujuh ').replace(/8/g, ' delapan ')
                    .replace(/9/g, ' sembilan ');

                return kodeHuruf + ', ' + ejaanAngka;
            }

            return nomor
                .replace(/0/g, ' nol ').replace(/1/g, ' satu ').replace(/2/g, ' dua ')
                .replace(/3/g, ' tiga ').replace(/4/g, ' empat ').replace(/5/g, ' lima ')
                .replace(/6/g, ' enam ').replace(/7/g, ' tujuh ').replace(/8/g, ' delapan ')
                .replace(/9/g, ' sembilan ');
        }

        function speakQueueCall(nomorAntrian, nomorMeja) {
            playChime(function() {
                var ejaanNomor = formatEjaanIndonesia(nomorAntrian);
                var ejaanMeja = formatEjaanIndonesia(nomorMeja.toString());
                var teksNarasi = 'Nomor tiket ' + ejaanNomor + ', silakan menuju ke meja ' + ejaanMeja;

                if ('speechSynthesis' in window) {
                    window.speechSynthesis.cancel(); 
                    var utterance = new SpeechSynthesisUtterance(teksNarasi);
                    utterance.lang = 'id-ID';
                    utterance.rate = 0.85;  
                    utterance.pitch = 1.1;   

                    var selectedVoiceName = document.getElementById('voice-select').value;
                    if (selectedVoiceName && availableVoices.length > 0) {
                        var chosenVoice = availableVoices.find(v => v.name === selectedVoiceName);
                        if (chosenVoice) utterance.voice = chosenVoice;
                    }

                    window.speechSynthesis.speak(utterance);
                }
            });
        }

        function playTestCall() {
            var num = document.getElementById('hero-nomor-antrian').innerText;
            var meja = document.getElementById('hero-nama-meja').innerText.replace('MEJA CS ', '');
            speakQueueCall(num !== '---' ? num : 'A-024', meja !== '--' ? meja : '2');
        }

        function fetchDisplayData() {
            fetch('{{ url("/api/display-antrean-data") }}')
                .then(response => response.json())
                .then(data => {
                    renderSedangDipanggil(data.sedangDipanggil);
                    renderAntreanMenunggu(data.antreanMenunggu);
                })
                .catch(error => console.error('Gagal mengambil data display:', error));
        }

        function renderSedangDipanggil(listDipanggil) {
            if (!listDipanggil || listDipanggil.length === 0) {
                document.getElementById('hero-nomor-antrian').innerText = '---';
                document.getElementById('hero-nama-meja').innerText = 'MEJA CS --';
                document.getElementById('hero-nama-layanan').innerText = 'Belum Ada Panggilan';
                renderEmptyMejaGrid();
                return;
            }

            var activeCall = listDipanggil[0];

            if (activeCall) {
                var noMeja = activeCall.cs ? (activeCall.cs.nomor_meja || '1') : '1';
                var namaLayanan = activeCall.layanan ? activeCall.layanan.nama_layanan : 'Layanan CS';

                document.getElementById('hero-nomor-antrian').innerText = activeCall.nomor_antrian;
                document.getElementById('hero-nama-meja').innerText = 'MEJA CS ' + String(noMeja).padStart(2, '0');
                document.getElementById('hero-nama-layanan').innerText = namaLayanan;

                var countDipanggil = activeCall.jumlah_dipanggil || 0;
                var csId = activeCall.user_id || (activeCall.cs ? activeCall.cs.id : '0');
                var currentKey = activeCall.id + '_cs' + csId + '_' + countDipanggil;

                if (currentKey !== lastCallUniqueKey) {
                    lastCallUniqueKey = currentKey;
                    speakQueueCall(activeCall.nomor_antrian, noMeja);
                }
            }

            // Render Baris Grid Meja Pelayanan
            var gridHtml = '';
            for (var i = 1; i <= 4; i++) {
                var item = listDipanggil.find(x => x.cs && parseInt(x.cs.nomor_meja) === i);
                if (item) {
                    var namaCs = item.cs ? item.cs.nama_lengkap : 'Staff CS';
                    gridHtml += `
                        <div class="bg-slate-900/90 border border-red-500/50 rounded-xl p-2.5 flex flex-col justify-between shadow-md">
                            <div class="flex items-center justify-between text-[10px]">
                                <span class="font-black text-white uppercase">MEJA CS ${String(i).padStart(2, '0')}</span>
                                <span class="bg-red-500/20 text-red-400 border border-red-500/30 font-black px-1.5 py-0.5 rounded text-[8px] uppercase">DIPANGGIL</span>
                            </div>
                            <div class="my-1">
                                <span class="text-2xl font-black text-white font-mono tracking-tight">${item.nomor_antrian}</span>
                                <p class="text-[9px] text-slate-400 truncate">${item.layanan ? item.layanan.nama_layanan : ''}</p>
                            </div>
                            <div class="text-[9px] text-slate-400 truncate border-t border-slate-800 pt-1 flex justify-between">
                                <span>Petugas: <strong class="text-slate-200">${namaCs}</strong></span>
                                <span class="text-red-400 font-bold">BARU SAJA</span>
                            </div>
                        </div>
                    `;
                } else {
                    gridHtml += `
                        <div class="bg-slate-900/40 border border-slate-800/80 rounded-xl p-2.5 flex flex-col justify-between opacity-70">
                            <div class="flex items-center justify-between text-[10px]">
                                <span class="font-bold text-slate-400 uppercase">MEJA CS ${String(i).padStart(2, '0')}</span>
                                <span class="bg-slate-800 text-emerald-400 font-bold px-1.5 py-0.5 rounded text-[8px] uppercase">SIAP MELAYANI</span>
                            </div>
                            <div class="my-1">
                                <span class="text-2xl font-black text-slate-600 font-mono tracking-tight">---</span>
                            </div>
                            <div class="text-[9px] text-slate-500 truncate border-t border-slate-800/60 pt-1 flex justify-between">
                                <span>Menunggu Panggilan</span>
                                <span>STANDBY</span>
                            </div>
                        </div>
                    `;
                }
            }

            document.getElementById('grid-meja-pelayanan').innerHTML = gridHtml;
        }

        function renderEmptyMejaGrid() {
            var gridHtml = '';
            for (var i = 1; i <= 4; i++) {
                gridHtml += `
                    <div class="bg-slate-900/40 border border-slate-800/80 rounded-xl p-2.5 flex flex-col justify-between opacity-70">
                        <div class="flex items-center justify-between text-[10px]">
                            <span class="font-bold text-slate-400 uppercase">MEJA CS ${String(i).padStart(2, '0')}</span>
                            <span class="bg-slate-800 text-emerald-400 font-bold px-1.5 py-0.5 rounded text-[8px] uppercase">SIAP MELAYANI</span>
                        </div>
                        <div class="my-1">
                            <span class="text-2xl font-black text-slate-600 font-mono tracking-tight">---</span>
                        </div>
                        <div class="text-[9px] text-slate-500 truncate border-t border-slate-800/60 pt-1 flex justify-between">
                            <span>Menunggu Panggilan</span>
                            <span>STANDBY</span>
                        </div>
                    </div>
                `;
            }
            document.getElementById('grid-meja-pelayanan').innerHTML = gridHtml;
        }

        function renderAntreanMenunggu(listMenunggu) {
            var container = document.getElementById('box-antrean-menunggu');
            document.getElementById('total-menunggu').innerText = (listMenunggu ? listMenunggu.length : 0) + ' Menunggu';

            if (!listMenunggu || listMenunggu.length === 0) {
                container.innerHTML = `
                    <div class="bg-slate-900/40 border border-slate-800 rounded-xl p-4 text-center text-slate-500 text-xs">
                        Belum ada antrean berikutnya.
                    </div>
                `;
                return;
            }

            var html = '';
            listMenunggu.forEach(function(item, index) {
                var namaLayanan = item.layanan ? item.layanan.nama_layanan : 'Layanan';
                var isNext = index === 0;

                html += `
                    <div class="bg-slate-900/90 border ${isNext ? 'border-amber-500/50 bg-amber-500/5' : 'border-slate-800'} rounded-xl p-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="w-5 text-center text-xs font-black text-slate-500">${index + 1}</span>
                            <div>
                                <span class="text-base font-black font-mono text-white block leading-none">${item.nomor_antrian}</span>
                                <span class="text-[10px] text-slate-400 font-bold block mt-1 truncate max-w-[120px]">${namaLayanan}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[9px] font-black uppercase ${isNext ? 'text-amber-400 bg-amber-500/10 border border-amber-500/30' : 'text-slate-400 bg-slate-800'} px-2 py-0.5 rounded-md block">
                                ${isNext ? 'Siap Dipanggil' : 'Menunggu'}
                            </span>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        fetchDisplayData();
        setInterval(fetchDisplayData, 2500);
    </script>
</body>
</html>