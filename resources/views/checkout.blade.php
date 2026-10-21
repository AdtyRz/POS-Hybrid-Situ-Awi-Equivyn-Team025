    @extends('layouts.app')
    @section('content')
        <form action="{{ route('checkout.process') }}" method="POST">
            @csrf
            <label for="kode_pembayaran">Kode Pembayaran</label>
            <input type="text" name="kode_pembayaran" id="kode_pembayaran" required>
            <label for="metode">Metode Pembayaran</label>
            <select name="metode" id="metode" required>
                <option value="cash">Cash</option>
                <option value="qris">QR‑IS</option>
            </select>
            <button type="submit">Bayar</button>
        </form>
    @endsection
