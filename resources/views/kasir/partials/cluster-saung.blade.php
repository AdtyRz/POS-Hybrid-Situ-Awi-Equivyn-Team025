<div class="space-y-2">
    <div class="flex items-center justify-between px-1">
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full {{ $warna }}"></span>
            <h3 class="font-display font-bold text-sm text-[#00341A]">
                {{ $judul }}
            </h3>
        </div>
        <span class="text-xs font-mono font-semibold text-[#6B6A63]" x-text="clusterInfo('{{ $prefix }}')"></span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
        <template x-for="saung in getClusterSaungs('{{ $prefix }}')" :key="saung.id">
            <div @click="selectSaung(saung)"
                 @keydown.enter.prevent="selectSaung(saung)"
                 @keydown.space.prevent="selectSaung(saung)"
                 x-show="isSaungVisible(saung)"
                 role="button"
                 tabindex="0"
                 data-saung-card
                 :aria-label="'Pilih ' + saung.name"
                 :aria-pressed="selectedSaungId === saung.id"
                 class="rounded-xl p-3 flex flex-col justify-between transition-all duration-200 cursor-pointer select-none relative focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0B4D2B] focus-visible:ring-offset-2"
                 :class="saungCardClass(saung)">

                <div class="space-y-2">
                    <div class="flex items-start justify-between gap-1.5">
                        <div>
                            <div class="flex items-center gap-1.5">
                                <h4 class="font-bold text-sm tracking-tight"
                                    :class="saung.status === 'empty' ? 'text-[#6B6A63]' : 'text-[#00341A]'"
                                    x-text="saung.name">
                                </h4>
                                <span x-show="selectedSaungId === saung.id"
                                      class="px-1.5 py-0.2 rounded bg-[#0B4D2B] text-white text-[9px] font-bold uppercase tracking-wider">
                                    TERPILIH
                                </span>
                            </div>
                            <div class="text-[11px] text-[#6B6A63]" x-text="saung.capacity"></div>
                        </div>

                        <div>
                            <template x-if="saung.hasAlert">
                                <div class="flex items-center gap-1 px-1.5 py-0.5 rounded bg-[#C1442C] text-white text-[9px] font-extrabold uppercase shadow-2xs">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                    <span>PANGGILAN</span>
                                </div>
                            </template>

                            <template x-if="!saung.hasAlert && saung.status === 'waiting_cash'">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#E0B24E]/20 border border-[#E0B24E]/50 text-[#C9982F]">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span>Menunggu Bayar Kasir</span>
                                </span>
                            </template>

                            <template x-if="!saung.hasAlert && !['waiting_cash', 'paid', 'empty'].includes(saung.status)">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#E8F0E9] border border-[#A7C4AB] text-[#0B4D2B]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#0B4D2B]"></span>
                                    <span>Terisi</span>
                                </span>
                            </template>

                            <template x-if="!saung.hasAlert && saung.status === 'paid'">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#ECFDF5] border border-[#6EE7B7] text-[#065F46]">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span x-text="saung.paymentType === 'qris' ? 'Lunas (QRIS)' : (saung.paymentType === 'campuran' ? 'Lunas (Campuran)' : 'Lunas (Tunai)')"></span>
                                </span>
                            </template>

                            <template x-if="!saung.hasAlert && saung.status === 'empty'">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#EAE4D3] text-[#6B6A63]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#7A7E6F]"></span>
                                    <span>Kosong</span>
                                </span>
                            </template>
                        </div>
                    </div>

                    <div x-show="saung.hasAlert" class="flex items-center justify-between text-[10px]">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[#E0B24E]/20 border border-[#E0B24E]/50 text-[#C9982F] font-bold">
                            Pelayan Dipanggil
                        </span>
                        <span class="text-[#C1442C] font-bold flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#C1442C] animate-ping"></span>
                            <span>Buzzer Aktif</span>
                        </span>
                    </div>

                    <template x-if="saung.status !== 'empty'">
                        <div class="rounded-lg p-2 border text-[10px] space-y-1"
                             :class="selectedSaungId === saung.id ? 'bg-white/80 border-[#C8C2B3]/40' : 'bg-[#FAF7F0] border-[#C8C2B3]/30'">
                            <div class="flex items-start justify-between gap-2 text-[#6B6A63]">
                                <span class="font-mono text-[9px] leading-snug break-all min-w-0 flex-1" x-text="(saung.orderCount > 1 ? saung.orderCount + ' pesanan • ' : '') + saung.orderId"></span>
                                <span class="font-semibold text-[#92400E] flex items-center gap-0.5 shrink-0 whitespace-nowrap">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span x-text="saung.elapsedTime"></span>
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-[#2B2A26] truncate max-w-[120px]" x-text="saung.customer"></span>
                                <span class="font-semibold text-[#0B4D2B]" x-text="saung.itemCount + ' Menu'"></span>
                            </div>
                        </div>
                    </template>

                    <template x-if="saung.status === 'empty'">
                        <div class="rounded-lg p-3 bg-[#F3EEDF]/50 border border-dashed border-[#C8C2B3]/50 text-center">
                            <p class="text-[11px] italic text-[#6B6A63]">Saung bersih & siap ditempati</p>
                        </div>
                    </template>
                </div>

                <div class="pt-2 mt-2 border-t border-[#C8C2B3]/30 flex items-center justify-between">
                    <span class="text-[11px] text-[#6B6A63] font-medium"
                          x-text="saung.status === 'empty' ? 'Status:' : 'Total Tagihan:'">
                    </span>
                    <span class="font-bold"
                          :class="saung.status === 'empty' ? 'text-[#047857] text-xs' : 'text-[#00341A] text-sm'"
                          x-text="saung.status === 'empty' ? 'Tersedia (Ready)' : formatRupiah(saung.total)">
                    </span>
                </div>
            </div>
        </template>
    </div>
</div>
