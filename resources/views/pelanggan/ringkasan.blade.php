@if(!isset($isSinglePage))
    @include('pelanggan.index', ['initialStep' => 2])
@else
<div class="space-y-4 pb-28">
    <div class="flex items-center justify-between px-1 text-xs">
        <button type="button" 
                @click="goToStep(1)"
                class="inline-flex items-center gap-1.5 font-bold text-forest-800 hover:text-forest-600 transition active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Kembali ke Menu</span>
        </button>

        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-cream-100 border border-forest-100 text-charcoal-800 font-semibold">
            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            <span>Order Ref: <span class="font-bold text-forest-900" x-text="orderRef">#SA-204</span></span>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-4 border border-forest-100/70 shadow-sm flex items-start gap-3.5">
        <div class="w-10 h-10 rounded-2xl bg-forest-100 flex items-center justify-center text-forest-700 shrink-0 mt-0.5">
            <svg class="w-5 h-5 text-forest-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
        </div>
        <div class="space-y-0.5">
            <div class="text-[10px] font-bold tracking-wider text-charcoal-500 uppercase flex items-center gap-1">
                <span>LOKASI MEJA</span>
                <span>•</span>
                <span>{{ $meja->area }}</span>
            </div>
            <h2 class="text-sm sm:text-base font-bold text-forest-900 flex items-center gap-1.5">
                <span>{{ $meja->area }} {{ $meja->kode_meja }}</span>
            </h2>
            <p class="text-xs text-charcoal-500 leading-relaxed">
                Pesanan terhubung otomatis ke Kitchen Display System Saung Situ Awi.
            </p>
        </div>
    </div>

    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-sm font-bold text-forest-900 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-forest-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <span>Detail Pesanan</span>
            </h3>
            <span class="px-2.5 py-0.5 rounded-full bg-forest-100 text-forest-800 text-xs font-bold"
                  x-text="cartItems.length + ' Menu Terpilih'"></span>
        </div>

        <div class="space-y-3">
            <template x-for="(item, index) in cartItems" :key="item.id">
                <div class="bg-white rounded-2xl p-4 border border-forest-100/70 shadow-sm space-y-3">
                    <div class="flex items-start gap-3">
                        <img :src="item.image" 
                             :alt="item.name" 
                             class="w-16 h-16 rounded-xl object-cover shrink-0 border border-forest-100"
                             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&auto=format&fit=crop&q=80';">

                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <h4 class="font-bold text-xs sm:text-sm text-forest-900 truncate" x-text="item.name"></h4>
                                <button type="button" 
                                        @click="removeFromCart(item.id)" 
                                        class="text-charcoal-400 hover:text-red-500 transition p-1"
                                        title="Hapus menu">
                                    <svg class="w-4 h-4 text-charcoal-500 hover:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="text-xs font-bold text-forest-800 mt-0.5" x-text="formatRupiah(item.price)"></div>

                            <div class="flex items-center justify-between mt-2 pt-2 border-t border-forest-100/40">
                                <span class="text-xs text-charcoal-500 font-medium">Kuantitas</span>
                                <div class="flex items-center gap-2">
                                    <button type="button" 
                                            @click="decreaseQty(item.id)"
                                            class="w-7 h-7 rounded-full border border-forest-200 bg-cream-50 hover:bg-cream-100 text-forest-900 font-bold flex items-center justify-center transition active:scale-90 text-sm">
                                        -
                                    </button>
                                    <span class="w-6 text-center text-xs font-bold text-forest-900" x-text="item.qty"></span>
                                    <button type="button" 
                                            @click="increaseQty(item.id)"
                                            class="w-7 h-7 rounded-full bg-forest-600 hover:bg-forest-700 text-cream-50 font-bold flex items-center justify-center transition active:scale-90 text-sm">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-cream-50/90 rounded-xl p-2.5 border border-forest-100/60">
                        <div class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-charcoal-500 mb-1">
                            <svg class="w-3.5 h-3.5 text-gold-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            <span>CATATAN KOKI</span>
                        </div>
                        <input type="text" 
                               x-model="item.note" 
                               placeholder="Tulis catatan (misal: pedas manis, sambal dipisah...)" 
                               class="w-full bg-white border border-forest-100 rounded-lg px-2.5 py-1.5 text-xs text-charcoal-800 placeholder-charcoal-400 focus:outline-none focus:border-gold-500 focus:ring-1 focus:ring-gold-500">
                    </div>
                </div>
            </template>
        </div>

        <button type="button" 
                @click="goToStep(1)"
                class="w-full py-3 rounded-2xl bg-white border border-forest-200 border-dashed text-forest-800 hover:bg-cream-100/80 font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition active:scale-98 shadow-sm">
            <svg class="w-4 h-4 text-forest-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>Tambah Menu Lainnya</span>
        </button>
    </div>

    <div class="bg-white rounded-2xl p-4 border border-forest-100/70 shadow-sm space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-forest-900 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-forest-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
                <span>Metode Pembayaran</span>
            </h3>
            <span class="text-[11px] text-charcoal-500">Pilih salah satu</span>
        </div>

        <label class="block p-3 rounded-xl border transition cursor-pointer"
               :class="paymentMethod === 'qris' 
                   ? 'border-forest-600 bg-gold-50/40 ring-1 ring-forest-600/30' 
                   : 'border-forest-100 bg-white hover:bg-cream-50'">
            <div class="flex items-start gap-3">
                <div class="pt-0.5">
                    <input type="radio" 
                           name="payment_method" 
                           value="qris" 
                           x-model="paymentMethod" 
                           class="w-4 h-4 text-forest-600 focus:ring-forest-600">
                </div>
                <div class="space-y-1 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="text-xs sm:text-sm font-bold text-forest-900">QRIS Midtrans Instant</span>
                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold border border-amber-300">Rekomendasi</span>
                    </div>
                    <p class="text-[11px] text-charcoal-500 leading-relaxed">
                        Bayar instan via GoPay, OVO, Dana, BCA, Livin langsung dari meja Saung Anda.
                    </p>
                    <div class="flex items-center gap-1.5 pt-1">
                        <span class="px-1.5 py-0.5 rounded bg-blue-50 border border-blue-200 text-blue-700 text-[9px] font-bold">BCA</span>
                        <span class="px-1.5 py-0.5 rounded bg-red-50 border border-red-200 text-red-700 text-[9px] font-bold">QRIS</span>
                        <span class="px-1.5 py-0.5 rounded bg-emerald-50 border border-emerald-200 text-emerald-700 text-[9px] font-bold">GoPay</span>
                        <span class="px-1.5 py-0.5 rounded bg-orange-50 border border-orange-200 text-orange-700 text-[9px] font-bold">ShopeePay</span>
                    </div>
                </div>
            </div>
        </label>

        <label class="block p-3 rounded-xl border transition cursor-pointer"
               :class="paymentMethod === 'cash' 
                   ? 'border-forest-600 bg-gold-50/40 ring-1 ring-forest-600/30' 
                   : 'border-forest-100 bg-white hover:bg-cream-50'">
            <div class="flex items-start gap-3">
                <div class="pt-0.5">
                    <input type="radio" 
                           name="payment_method" 
                           value="cash" 
                           x-model="paymentMethod" 
                           class="w-4 h-4 text-forest-600 focus:ring-forest-600">
                </div>
                <div class="space-y-1 flex-1">
                    <div class="text-xs sm:text-sm font-bold text-forest-900">Bayar Tunai di Kasir</div>
                    <p class="text-[11px] text-charcoal-500 leading-relaxed">
                        Selesaikan pemesanan sekarang, lalu lakukan pembayaran tunai di kasir utama dengan menyebutkan nomor {{ $meja->kode_meja }}.
                    </p>
                </div>
            </div>
        </label>
    </div>

    <div class="bg-white rounded-2xl p-4 border border-forest-100/70 shadow-sm space-y-2.5">
        <h3 class="text-sm font-bold text-forest-900 flex items-center gap-1.5 pb-1 border-b border-forest-100/40">
            <svg class="w-4 h-4 text-forest-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"></path>
            </svg>
            <span>Ringkasan Biaya</span>
        </h3>

        <div class="flex items-center justify-between text-xs text-charcoal-800">
            <span class="text-charcoal-500" x-text="'Subtotal (' + totalCartItemsCount + ' Item)'"></span>
            <span class="font-semibold" x-text="formatRupiah(cartSubtotal)"></span>
        </div>

        <div class="flex items-center justify-between text-xs text-charcoal-800">
            <span class="text-charcoal-500 flex items-center gap-1">
                <span>Pajak Restoran (PB1 10%)</span>
                <span class="text-charcoal-400 text-[10px]" title="Pajak daerah 10%">ⓘ</span>
            </span>
            <span class="font-semibold" x-text="formatRupiah(cartTax)"></span>
        </div>

        <div class="flex items-center justify-between text-xs text-charcoal-800">
            <span class="text-charcoal-500">Biaya Layanan Saung</span>
            <span class="font-bold text-emerald-600">Gratis</span>
        </div>

        <div class="pt-2 border-t border-forest-100/70 flex items-end justify-between">
            <div>
                <div class="text-[10px] uppercase font-bold tracking-wider text-charcoal-500">TOTAL PEMBAYARAN</div>
                <div class="text-[10px] text-charcoal-400">Termasuk pajak & pelayanan</div>
            </div>
            <div class="text-base sm:text-lg font-extrabold text-forest-900" x-text="formatRupiah(cartGrandTotal)"></div>
        </div>

        <div class="pt-2 flex items-center gap-1.5 text-[11px] text-charcoal-500 bg-cream-50 rounded-xl p-2.5 border border-forest-100/50">
            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
            </svg>
            <span>Pesanan diproses langsung ke dapur setelah verifikasi</span>
        </div>
    </div>

    <div class="fixed bottom-0 inset-x-0 bg-white border-t border-forest-100 p-4 shadow-lg z-40">
        <div class="max-w-md lg:max-w-2xl mx-auto flex items-center justify-between gap-4">
            <div>
                <div class="text-[10px] uppercase font-bold text-charcoal-500 tracking-wider">TOTAL AKHIR</div>
                <div class="text-base sm:text-lg font-extrabold text-forest-900" x-text="formatRupiah(cartGrandTotal)"></div>
            </div>

            <button type="button"
                    @click="kirimBon()"
                    class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full bg-forest-700 hover:bg-forest-800 text-white font-bold text-xs sm:text-sm shadow-md transition active:scale-95">
                <span>Lanjut ke Pembayaran</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </button>
        </div>
    </div>
</div>
@endif

