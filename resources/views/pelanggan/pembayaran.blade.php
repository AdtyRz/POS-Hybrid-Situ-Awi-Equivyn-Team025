@if(!isset($isSinglePage))
    @include('pelanggan.index', ['initialStep' => 3])
@else
{{-- Halaman 3: Pembayaran & Pelacakan --}}
<div class="space-y-4 pb-16">
    {{-- Header Card: Live Tracker & Payment --}}
    <div class="bg-white rounded-2xl p-4 border border-forest-100/70 shadow-sm flex items-start justify-between gap-3">
        <div class="space-y-1">
            <div class="inline-flex items-center gap-1.5 text-[10px] font-extrabold tracking-wider text-emerald-700 uppercase">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span>LIVE TRACKER & PAYMENT</span>
            </div>
            <h2 class="text-base sm:text-lg font-extrabold text-forest-900 tracking-tight" x-text="orderId">
                Order #SA-20260915-084
            </h2>
            <div class="text-xs text-charcoal-500 flex items-center gap-1">
                <span>Saung 02</span>
                <span>•</span>
                <span>Dine-in</span>
            </div>
        </div>

        <div class="text-right space-y-1">
            <div class="text-[10px] text-charcoal-500 font-medium">Total Tagihan</div>
            <div class="text-sm sm:text-base font-extrabold text-forest-900" x-text="formatRupiah(cartGrandTotal)">
                Rp 144.100
            </div>
            <div>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold"
                      :class="orderPaid 
                          ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' 
                          : 'bg-amber-100 text-amber-800 border border-amber-300'"
                      x-text="orderPaid ? 'Pembayaran Berhasil' : 'Menunggu Pembayaran'">
                </span>
            </div>
        </div>
    </div>

    {{-- Status Pesanan Saung 02 (Realtime MQTT Stepper) --}}
    <div class="bg-white rounded-2xl p-4 border border-forest-100/70 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-forest-900 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-forest-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <span>Status Pesanan Saung 02</span>
            </h3>
            <span class="px-2.5 py-0.5 rounded-full bg-cream-100 text-forest-800 border border-forest-100 text-[10px] font-bold flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>Realtime MQTT</span>
            </span>
        </div>

        {{-- Vertical Stepper --}}
        <div class="relative pl-6 space-y-5 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-forest-100">
            {{-- Milestone 1: Pesanan Diterima --}}
            <div class="relative group">
                <div class="absolute -left-6 top-0.5 w-5 h-5 rounded-full bg-forest-800 text-cream-50 flex items-center justify-center ring-4 ring-white shadow-sm">
                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center justify-between text-xs font-bold text-forest-900">
                        <span>Pesanan Diterima</span>
                        <span class="text-[10px] text-charcoal-400 font-mono">12:04 WIB</span>
                    </div>
                    <p class="text-[11px] text-charcoal-500 mt-0.5 leading-relaxed">
                        Tiket order masuk ke sistem antrean dapur
                    </p>
                </div>
            </div>

            {{-- Milestone 2: Diproses di Dapur & Bar (Active) --}}
            <div class="relative group">
                <div class="absolute -left-6 top-0.5 w-5 h-5 rounded-full bg-amber-400 text-forest-900 flex items-center justify-center ring-4 ring-white shadow-sm animate-pulse">
                    <span class="text-[10px]">🍳</span>
                </div>
                <div class="bg-amber-50/60 rounded-xl p-2.5 border border-amber-200/70 space-y-1">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-forest-900">Diproses di Dapur & Bar 🍳☕</span>
                            <span class="px-1.5 py-0.2 rounded bg-amber-200 text-amber-900 text-[9px] font-extrabold">Aktif</span>
                        </div>
                    </div>
                    <p class="text-[11px] text-charcoal-600 leading-relaxed">
                        Sedang dimasak oleh Chef Saung Situ Awi
                    </p>
                    <div class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-800 bg-white/80 px-2 py-0.5 rounded-full border border-amber-200">
                        <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Estimasi 15 - 20 menit</span>
                    </div>
                </div>
            </div>

            {{-- Milestone 3: Siap Diantarkan Pelayan --}}
            <div class="relative opacity-60">
                <div class="absolute -left-6 top-0.5 w-5 h-5 rounded-full bg-charcoal-200 text-charcoal-600 flex items-center justify-center ring-4 ring-white shadow-2xs">
                    <span class="text-[10px]">🚚</span>
                </div>
                <div>
                    <div class="text-xs font-bold text-forest-900">
                        Siap Diantarkan Pelayan 🚚
                    </div>
                    <p class="text-[11px] text-charcoal-500 mt-0.5">
                        Penyajian dari meja pass bar
                    </p>
                </div>
            </div>

            {{-- Milestone 4: Pesanan Tiba di Saung --}}
            <div class="relative opacity-60">
                <div class="absolute -left-6 top-0.5 w-5 h-5 rounded-full bg-charcoal-200 text-charcoal-600 flex items-center justify-center ring-4 ring-white shadow-2xs">
                    <span class="text-[10px]">🌿</span>
                </div>
                <div>
                    <div class="text-xs font-bold text-forest-900">
                        Pesanan Tiba di Saung 02 🌿
                    </div>
                    <p class="text-[11px] text-charcoal-500 mt-0.5">
                        Selamat menikmati hidangan khas Sunda!
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- IoT Table Node Saung 02 (ESP32 LCD 16x2 Simulation) --}}
    <div class="bg-white rounded-2xl p-4 border border-forest-100/70 shadow-sm space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-forest-900 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-forest-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="18" x="3" y="3" rx="2"/>
                    <path d="M7 7h10v10H7z"/>
                </svg>
                <span>IoT Table Node Saung 02</span>
            </h3>
            <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>MQTT Synced</span>
            </span>
        </div>

        {{-- LCD Physical Simulation Display --}}
        <div class="bg-[#06180E] border-4 border-charcoal-800 rounded-xl p-3.5 shadow-inner text-emerald-400 font-mono text-[11px] sm:text-xs leading-relaxed space-y-1.5 select-none relative overflow-hidden">
            {{-- Subtle Scanline Overlay --}}
            <div class="absolute inset-0 bg-gradient-to-b from-transparent via-emerald-900/5 to-transparent pointer-events-none"></div>

            <div class="flex items-center justify-between text-[10px] text-emerald-600/90 border-b border-emerald-950 pb-1">
                <span>SAUNG-IOT-02 // ESP32</span>
                <span>CH-02 WiFi: -54dBm</span>
            </div>
            
            <div class="flex items-center justify-between font-bold tracking-widest text-emerald-300 text-xs sm:text-sm py-0.5">
                <div class="flex items-center gap-2">
                    <span>STATUS: DIPROSES</span>
                </div>
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
            </div>

            <div class="flex items-center justify-between tracking-wider text-emerald-400/90 pt-0.5">
                <span>SAUNG 02</span>
                <span x-text="liveTimeString">12:05 WIB</span>
            </div>
        </div>

        {{-- Subtitle info LCD --}}
        <div class="flex items-start gap-1.5 text-[11px] text-charcoal-500 italic">
            <span class="text-gold-600 text-sm leading-none">❝</span>
            <span>Layar LCD fisik di Saung Anda telah terupdate otomatis via MQTT WebSocket.</span>
        </div>
    </div>

    {{-- Pembayaran QRIS Midtrans Sandbox --}}
    <div class="bg-white rounded-2xl p-4 border border-forest-100/70 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-forest-900 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-forest-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                </svg>
                <span>Pembayaran QRIS</span>
            </h3>
            <span class="px-2.5 py-0.5 rounded-full bg-cream-100 text-forest-800 text-[10px] font-bold border border-forest-100">
                Midtrans Sandbox
            </span>
        </div>

        {{-- Countdown Timer Banner --}}
        <div class="bg-amber-50 rounded-xl px-4 py-2.5 border border-amber-200/80 flex items-center justify-between text-xs">
            <div class="flex items-center gap-1.5 text-amber-900 font-semibold">
                <span class="text-amber-600">⏳</span>
                <span>Sisa waktu pembayaran:</span>
            </div>
            <div class="font-mono font-bold text-sm text-amber-900 tracking-wider" x-text="qrisCountdownString">
                14:45
            </div>
        </div>

        {{-- Large Centered Crisp QR Code --}}
        <div class="flex flex-col items-center justify-center p-3 bg-cream-50/50 rounded-2xl border border-forest-100/60 space-y-2">
            <div class="relative p-3 bg-white rounded-2xl shadow-sm border border-forest-200 flex items-center justify-center">
                {{-- Dynamic QR SVG with QRIS & GPN Logo --}}
                <div class="w-48 h-48 sm:w-56 sm:h-56 relative flex items-center justify-center">
                    {{-- Realistic Crisp QR Code Graphic --}}
                    <svg viewBox="0 0 200 200" class="w-full h-full text-forest-900">
                        <!-- Corner Finder 1 (Top Left) -->
                        <rect x="10" y="10" width="50" height="50" fill="none" stroke="currentColor" stroke-width="8" rx="4"/>
                        <rect x="22" y="22" width="26" height="26" fill="currentColor" rx="2"/>
                        <!-- Corner Finder 2 (Top Right) -->
                        <rect x="140" y="10" width="50" height="50" fill="none" stroke="currentColor" stroke-width="8" rx="4"/>
                        <rect x="152" y="22" width="26" height="26" fill="currentColor" rx="2"/>
                        <!-- Corner Finder 3 (Bottom Left) -->
                        <rect x="10" y="140" width="50" height="50" fill="none" stroke="currentColor" stroke-width="8" rx="4"/>
                        <rect x="22" y="152" width="26" height="26" fill="currentColor" rx="2"/>

                        <!-- Random QR Pattern Matrix Dots -->
                        <g fill="currentColor">
                            <!-- Alignment & Timing -->
                            <rect x="70" y="20" width="8" height="8"/>
                            <rect x="90" y="20" width="8" height="8"/>
                            <rect x="110" y="20" width="8" height="8"/>
                            <rect x="125" y="20" width="8" height="8"/>
                            <rect x="70" y="35" width="8" height="8"/>
                            <rect x="100" y="35" width="8" height="8"/>
                            <rect x="120" y="35" width="8" height="8"/>
                            
                            <!-- Middle patterns -->
                            <rect x="20" y="70" width="8" height="8"/>
                            <rect x="35" y="70" width="8" height="8"/>
                            <rect x="50" y="70" width="8" height="8"/>
                            <rect x="70" y="70" width="16" height="8"/>
                            <rect x="115" y="70" width="16" height="8"/>
                            <rect x="145" y="70" width="8" height="8"/>
                            <rect x="175" y="70" width="8" height="8"/>

                            <rect x="10" y="90" width="8" height="8"/>
                            <rect x="30" y="90" width="8" height="8"/>
                            <rect x="50" y="90" width="16" height="8"/>
                            <rect x="135" y="90" width="16" height="8"/>
                            <rect x="165" y="90" width="8" height="8"/>
                            <rect x="180" y="90" width="8" height="8"/>

                            <rect x="20" y="110" width="8" height="8"/>
                            <rect x="40" y="110" width="8" height="8"/>
                            <rect x="60" y="110" width="8" height="8"/>
                            <rect x="130" y="110" width="16" height="8"/>
                            <rect x="160" y="110" width="8" height="8"/>
                            <rect x="180" y="110" width="8" height="8"/>

                            <!-- Bottom Right pattern -->
                            <rect x="70" y="140" width="8" height="8"/>
                            <rect x="90" y="140" width="16" height="8"/>
                            <rect x="120" y="140" width="8" height="8"/>
                            <rect x="140" y="140" width="8" height="8"/>
                            <rect x="160" y="140" width="8" height="8"/>
                            <rect x="180" y="140" width="8" height="8"/>

                            <rect x="70" y="160" width="16" height="8"/>
                            <rect x="105" y="160" width="8" height="8"/>
                            <rect x="130" y="160" width="8" height="8"/>
                            <rect x="150" y="160" width="16" height="8"/>
                            <rect x="180" y="160" width="8" height="8"/>

                            <rect x="70" y="180" width="8" height="8"/>
                            <rect x="90" y="180" width="8" height="8"/>
                            <rect x="115" y="180" width="16" height="8"/>
                            <rect x="150" y="180" width="8" height="8"/>
                            <rect x="170" y="180" width="16" height="8"/>
                        </g>

                        <!-- Center White Shield Badge -->
                        <rect x="74" y="74" width="52" height="52" fill="white" rx="6" stroke="#0B4D2B" stroke-width="2"/>
                    </svg>

                    {{-- QRIS Center Emblem --}}
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <div class="text-center px-1">
                            <div class="font-extrabold text-[11px] leading-tight text-red-600 tracking-tight">QRIS</div>
                            <div class="text-[7px] font-bold text-amber-700 tracking-wider">GPN SAUNG</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-1.5 text-[11px] font-mono text-charcoal-600 pt-1">
                <svg class="w-3.5 h-3.5 text-forest-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                <span>NMID: ID1020098481928 - MIDTRANS</span>
            </div>
        </div>

        {{-- Cara Pembayaran Scan Box --}}
        <div class="bg-cream-50 rounded-xl p-3 border border-forest-100/70 space-y-1 text-xs">
            <div class="flex items-center gap-1.5 font-bold text-forest-900">
                <svg class="w-4 h-4 text-forest-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                </svg>
                <span>Cara Pembayaran Scan:</span>
            </div>
            <p class="text-charcoal-700 leading-relaxed text-[11px]">
                Buka aplikasi BCA Mobile, GoPay, OVO, Dana, ShopeePay, atau m-Banking Anda, lalu scan QR di atas.
            </p>
        </div>

        {{-- Auto-check Indicator --}}
        <div class="flex items-center justify-center gap-2 text-xs text-charcoal-500 py-1">
            <svg class="w-3.5 h-3.5 animate-spin text-forest-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            <span>Mengecek status pembayaran otomatis...</span>
        </div>

        {{-- Action Buttons --}}
        <div class="space-y-2 pt-1">
            {{-- Button 1: Cek Status Pembayaran Manual --}}
            <button type="button" 
                    @click="checkPaymentManual()"
                    class="w-full py-3 rounded-full bg-gold-500 hover:bg-gold-400 text-forest-900 font-bold text-xs sm:text-sm flex items-center justify-center gap-2 shadow-sm transition active:scale-98">
                <svg class="w-4 h-4 text-forest-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>Cek Status Pembayaran Manual</span>
            </button>

            {{-- Button 2: Ganti ke Pembayaran Tunai Kasir --}}
            <button type="button" 
                    @click="switchToCash()"
                    class="w-full py-2.5 rounded-full bg-cream-50 hover:bg-cream-100 text-forest-900 border border-forest-200 font-semibold text-xs sm:text-sm flex items-center justify-center gap-2 transition active:scale-98">
                <svg class="w-4 h-4 text-forest-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <span>Ganti ke Pembayaran Tunai Kasir</span>
            </button>
        </div>
    </div>

    {{-- Bantuan Pelayan (IoT Touch) Card --}}
    <div class="bg-amber-50 rounded-2xl p-4 border border-amber-200/80 shadow-sm flex items-start gap-3">
        <div class="w-9 h-9 rounded-full bg-amber-400 text-forest-900 flex items-center justify-center shrink-0 shadow-sm mt-0.5">
            <svg class="w-5 h-5 text-forest-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11"></path>
            </svg>
        </div>
        <div class="space-y-1 text-xs">
            <h4 class="font-bold text-forest-900 text-xs sm:text-sm">Bantuan Pelayan (IoT Touch)</h4>
            <p class="text-charcoal-700 leading-relaxed text-[11px]">
                Sentuh sensor TTP223 pada modul IoT saung jika ingin meminta bantuan tambahan ke pelayan saung secara langsung.
            </p>
            <div class="pt-1">
                <button type="button" 
                        @click="triggerWaiterCall()"
                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-white hover:bg-gold-100 text-forest-900 rounded-full border border-gold-400 text-[11px] font-semibold transition active:scale-95 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Simulasi Sentuh Sensor TTP223</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Rincian Item Pesanan Card --}}
    <div class="bg-white rounded-2xl p-4 border border-forest-100/70 shadow-sm space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-forest-100/40">
            <h3 class="text-sm font-bold text-forest-900 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-forest-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <span>Rincian Item Pesanan</span>
            </h3>
            <span class="px-2.5 py-0.5 rounded-full bg-cream-100 text-forest-800 text-[10px] font-bold border border-forest-100"
                  x-text="cartItems.length + ' Menu'">
                3 Menu
            </span>
        </div>

        {{-- Dynamic or Exact Screenshot Fallback Items --}}
        <div class="space-y-2.5 text-xs">
            <template x-for="item in cartItems" :key="item.id">
                <div class="flex items-start justify-between gap-2 py-1 border-b border-forest-100/30 last:border-b-0">
                    <div>
                        <div class="font-bold text-forest-900" x-text="item.qty + 'x ' + item.name"></div>
                        <div class="text-[10px] text-charcoal-500" x-text="item.note || 'Saung Signature & Otentik'"></div>
                    </div>
                    <div class="font-bold text-forest-900 text-right whitespace-nowrap" x-text="formatRupiah(item.price * item.qty)"></div>
                </div>
            </template>
        </div>

        {{-- Total Pembayaran --}}
        <div class="pt-3 border-t border-forest-100 flex items-center justify-between">
            <span class="text-xs font-bold text-charcoal-700">Total Pembayaran</span>
            <span class="text-base sm:text-lg font-extrabold text-forest-900" x-text="formatRupiah(cartGrandTotal)">
                Rp 144.100
            </span>
        </div>
    </div>
</div>
@endif

