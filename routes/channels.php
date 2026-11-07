<?php

use App\Models\MejaMakan;
use App\Models\Pesanan;
use App\Models\SesiMeja;
use App\Support\AksesPesanan;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

$stafDenganRole = static function ($user, array $roles): bool {
    return AksesPesanan::staf($user) && in_array($user->role, $roles, true);
};

Broadcast::channel('admin', fn ($user) => $stafDenganRole($user, ['admin']));
Broadcast::channel('kasir', fn ($user) => $stafDenganRole($user, ['admin', 'kasir']));
Broadcast::channel('pelayan', fn ($user) => $stafDenganRole($user, ['admin', 'pelayan']));
Broadcast::channel('menu', fn ($user) => $stafDenganRole($user, ['admin', 'kasir', 'pelayan', 'koki', 'barista']));
Broadcast::channel('kds.dapur', fn ($user) => $stafDenganRole($user, ['admin', 'koki']));
Broadcast::channel('kds.bar', fn ($user) => $stafDenganRole($user, ['admin', 'barista']));

Broadcast::channel('meja.{kodeMeja}', function ($user, string $kodeMeja) {
    if (AksesPesanan::staf($user)) {
        return true;
    }

    if (! request()->hasSession()) {
        return false;
    }

    $meja = MejaMakan::where('kode_meja', $kodeMeja)->first();

    if (! $meja) {
        return false;
    }

    $sesi = SesiMeja::query()
        ->where('meja_id', $meja->id)
        ->where('status', 'aktif')
        ->latest('id')
        ->first();

    return $sesi !== null
        && (int) request()->session()->get('sesi_meja_'.$meja->id) === (int) $sesi->id;
});

Broadcast::channel('pesanan.{kode}', function ($user, string $kode) {
    if (AksesPesanan::staf($user)) {
        return true;
    }

    if (! request()->hasSession()) {
        return false;
    }

    $pesanan = Pesanan::with('meja')->where('kode_pesanan', $kode)->first();

    if (! $pesanan || ! $pesanan->meja) {
        return false;
    }

    $sesi = SesiMeja::query()
        ->where('meja_id', $pesanan->meja_id)
        ->where('status', 'aktif')
        ->latest('id')
        ->first();

    if ($sesi === null
        || (int) request()->session()->get('sesi_meja_'.$pesanan->meja_id) !== (int) $sesi->id) {
        return false;
    }

    return $pesanan->waktu_pesan !== null
        && $sesi->dibuka_pada !== null
        && $pesanan->waktu_pesan->greaterThanOrEqualTo($sesi->dibuka_pada);
});
