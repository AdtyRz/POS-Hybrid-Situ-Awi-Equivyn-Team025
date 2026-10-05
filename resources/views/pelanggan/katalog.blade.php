@if(!isset($isSinglePage))
    @include('pelanggan.index', ['initialStep' => 1])
@else
<div class="space-y-4 pb-28">
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-forest-100/60 relative overflow-hidden">
        <div class="flex items-start justify-between">
            <div class="space-y-1 pr-12">
                <div class="inline-flex items-center gap-1.5 text-xs font-bold tracking-wider text-gold-600 uppercase">
                    <svg class="w-3.5 h-3.5 text-gold-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 2.438 4.877a1 1 0 01-.894 1.447H2.242a1 1 0 01-.894-1.447l2.438-4.877-1.233-.616a1 1 0 01.894-1.79l1.599.8L9 4.323V3a1 1 0 011-1z"/>
                    </svg>
                    <span>WILUJENG SUMPING</span>
                </div>
                <h1 class="text-xl lg:text-2xl font-bold text-forest-900 tracking-tight flex items-center gap-1.5">
                    Halo, Tamu {{ $meja->kode_meja }}!
                </h1>
                <p class="text-xs text-charcoal-500 leading-relaxed max-w-xs">
                    Nikmati sajian otentik khas bumi Parahyangan di saung tenang tepi danau.
                </p>
            </div>
            
            <div class="absolute right-4 top-4 w-12 h-12 rounded-2xl bg-gold-50 border border-gold-300/60 flex items-center justify-center text-forest-700 shadow-sm">
                <svg class="w-7 h-7 text-forest-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                    <path d="M2 10h20"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-r from-forest-800 to-forest-700 rounded-2xl p-3.5 text-cream-50 shadow-md flex items-center justify-between gap-3 border border-forest-600">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-forest-900/60 border border-forest-400/40 flex items-center justify-center shrink-0">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
            </div>
            <div>
                <div class="flex items-center gap-1.5 text-xs font-semibold text-white">
                    <span>Meja Anda Terhubung</span>
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="flex items-center gap-1.5 text-[11px] text-forest-100/90 font-medium">
                    <svg class="w-3 h-3 text-gold-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                    </svg>
                    <span>IoT Node {{ $meja->kode_meja }} Online & Sinkron Dapur</span>
                </div>
            </div>
        </div>

        <button type="button" 
                @click="checkIotStatus()" 
                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-forest-900/80 hover:bg-forest-900 text-gold-300 hover:text-gold-200 border border-gold-500/30 text-xs font-medium transition active:scale-95 shrink-0">
            <svg class="w-3 h-3 transition-transform" :class="iotChecking ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            <span>Cek</span>
        </button>
    </div>

    <div class="relative flex items-center gap-2">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-charcoal-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </span>
            <input type="text" 
                   x-model="searchQuery" 
                   placeholder="Cari Gurame, Nasi Liwet, Kopi..." 
                   class="w-full pl-9 pr-10 py-2.5 bg-white border border-forest-100 rounded-xl text-xs sm:text-sm text-charcoal-800 placeholder-charcoal-500 focus:outline-none focus:border-gold-500 focus:ring-1 focus:ring-gold-500 transition shadow-sm">
            <button x-show="searchQuery.length > 0" 
                    @click="searchQuery = ''" 
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-charcoal-500 hover:text-charcoal-800">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <button type="button" 
                @click="showFilterModal = true"
                class="w-10 h-10 rounded-xl bg-white border border-forest-100 flex items-center justify-center text-forest-700 hover:bg-forest-100/50 shadow-sm transition active:scale-95 shrink-0"
                title="Filter Menu">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
            </svg>
        </button>
    </div>

    <div class="overflow-x-auto pb-1 -mx-4 px-4 scrollbar-none flex items-center gap-2">
        <template x-for="cat in categories" :key="cat.id">
            <button type="button" 
                    @click="activeCategory = cat.id"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all shadow-sm active:scale-95"
                    :class="activeCategory === cat.id 
                        ? 'bg-forest-600 text-cream-50 ring-2 ring-forest-600/30' 
                        : 'bg-white text-forest-900 border border-forest-100/80 hover:bg-cream-100'">
                <span x-text="cat.name"></span>
            </button>
        </template>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 pt-1">
        <template x-for="item in filteredMenuItems" :key="item.id">
            <div class="bg-white rounded-2xl border border-forest-100/70 overflow-hidden shadow-sm flex flex-col justify-between transition-all duration-200 hover:shadow-md"
                 :class="item.isSoldOut ? 'opacity-85' : ''">
                <div>
                    <div class="relative aspect-[4/3] w-full overflow-hidden bg-forest-100/40">
                        <img :src="item.image" 
                             :alt="item.name" 
                             class="w-full h-full object-cover transition-transform duration-300 hover:scale-105"
                             :class="item.isSoldOut ? 'grayscale contrast-75' : ''"
                             loading="lazy"
                             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&auto=format&fit=crop&q=80';">

                        <div class="absolute top-2 left-2">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold shadow-sm"
                                  :class="{
                                      'bg-amber-400 text-forest-900': item.badgeType === 'bestseller',
                                      'bg-emerald-600 text-white': item.badgeType === 'favorite' || item.badgeType === 'lalap',
                                      'bg-cream-100/90 text-forest-900 border border-forest-200': item.badgeType === 'bar' || item.badgeType === 'kopi',
                                      'bg-red-600 text-white': item.badgeType === 'soldout'
                                  }">
                                <span x-text="item.badge"></span>
                            </span>
                        </div>
                    </div>

                    <div class="p-3 space-y-1">
                        <h3 class="font-bold text-xs sm:text-sm text-forest-900 line-clamp-1" 
                            :class="item.isSoldOut ? 'text-charcoal-500' : ''"
                            x-text="item.name"></h3>
                        <p class="text-[11px] text-charcoal-500 line-clamp-2 leading-relaxed" 
                           x-text="item.desc"></p>
                    </div>
                </div>

                <div class="px-3 pb-3 pt-1 flex items-center justify-between border-t border-forest-100/40">
                    <div>
                        <div class="text-[10px] text-charcoal-500 font-medium">Harga</div>
                        <div class="text-xs sm:text-sm font-bold text-forest-900"
                             :class="item.isSoldOut ? 'text-charcoal-500' : ''"
                             x-text="formatRupiah(item.price)"></div>
                    </div>

                    <div>
                        <template x-if="!item.isSoldOut">
                            <button type="button" 
                                    @click="addToCart(item)"
                                    class="w-8 h-8 rounded-full bg-gold-500 hover:bg-gold-600 text-forest-900 font-bold flex items-center justify-center shadow-sm active:scale-90 transition-all"
                                    title="Tambah ke Pesanan">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </button>
                        </template>
                        <template x-if="item.isSoldOut">
                            <button type="button" 
                                    disabled
                                    class="w-8 h-8 rounded-full bg-charcoal-500/20 text-charcoal-500 font-bold flex items-center justify-center cursor-not-allowed"
                                    title="Stok Habis">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <div class="bg-gold-50/90 rounded-2xl p-4 border border-gold-300/70 shadow-sm flex items-start gap-3 mt-4">
        <div class="w-9 h-9 rounded-full bg-amber-400 text-forest-900 flex items-center justify-center shrink-0 shadow-sm mt-0.5">
            <svg class="w-5 h-5 text-forest-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11"></path>
            </svg>
        </div>
        <div class="space-y-1 text-xs">
            <h4 class="font-bold text-forest-900 text-xs sm:text-sm">Butuh Bantuan Pelayan?</h4>
            <p class="text-charcoal-800 leading-relaxed">
                Cukup sentuh tombol di bawah tanpa perlu memanggil keras. Kru kami akan segera menghampiri meja {{ $meja->kode_meja }}.
            </p>
            <div class="pt-1">
                <button type="button" 
                        @click="triggerWaiterCall()"
                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-white hover:bg-gold-100 text-forest-900 rounded-full border border-gold-400 text-[11px] font-semibold transition active:scale-95 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Sentuh Sensor Virtual Meja</span>
                </button>
            </div>
        </div>
    </div>

    <div x-show="totalCartItemsCount > 0"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-y-24 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-24 opacity-0"
         class="fixed bottom-4 inset-x-4 max-w-md lg:max-w-5xl mx-auto z-40">
        <div class="bg-forest-900 text-cream-50 rounded-full px-4 py-2.5 shadow-2xl border border-forest-600/60 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="relative w-10 h-10 rounded-full bg-forest-800 border border-forest-600 flex items-center justify-center text-gold-400 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center ring-2 ring-forest-900"
                          x-text="totalCartItemsCount"></span>
                </div>
                
                <div>
                    <div class="text-[10px] uppercase font-bold tracking-wider text-gold-300">
                        PESANAN {{ $meja->kode_meja }}
                    </div>
                    <div class="text-sm sm:text-base font-extrabold text-white"
                         x-text="formatRupiah(cartSubtotal)"></div>
                </div>
            </div>

            <button type="button" 
                    @click="goToStep(2)"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-gold-500 hover:bg-gold-400 text-forest-900 font-bold text-xs sm:text-sm shadow-md transition active:scale-95 shrink-0">
                <span>Lihat Bon</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </button>
        </div>
    </div>
</div>
@endif
