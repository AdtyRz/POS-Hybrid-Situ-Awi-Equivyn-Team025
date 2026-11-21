#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

URL="${1:-http://127.0.0.1/aktif}"
REQUESTS="${2:-1000}"
CONCURRENCY="${3:-50}"
OUT_DIR="${OUT_DIR:-$ROOT_DIR/stress-results}"
STAMP="$(date +%Y-%m-%d_%H%M%S)"

mkdir -p "$OUT_DIR"

if [[ "$URL" == https://* ]]; then
    echo "Catatan: ab tidak bisa melewati sertifikat self-signed."
    echo "Jika sertifikat belum valid, uji lewat http://127.0.0.1/aktif."
    echo
fi

echo "== Stress test: $URL =="
echo "Request: $REQUESTS, concurrency: $CONCURRENCY"
echo

if command -v ab >/dev/null 2>&1; then
    # -l: halaman /aktif menampilkan waktu render, panjang body bisa berubah
    # beberapa byte. Tanpa -l, ab salah menghitungnya sebagai failed request.
    ab -n "$REQUESTS" -c "$CONCURRENCY" -l \
        -g "$OUT_DIR/ab_$STAMP.tsv" \
        "$URL" | tee "$OUT_DIR/ab_$STAMP.txt"
elif command -v docker >/dev/null 2>&1; then
    # Fallback: pakai Apache Benchmark dari image httpd tanpa memasang paket
    # di server. Network host agar 127.0.0.1 menunjuk ke server yang sama.
    echo "ab tidak terpasang di host; memakai container httpd:2.4-alpine."
    echo
    docker run --rm --network host \
        --user "$(id -u):$(id -g)" \
        -v "$OUT_DIR:/out" \
        httpd:2.4-alpine \
        ab -n "$REQUESTS" -c "$CONCURRENCY" -l \
        -g "/out/ab_$STAMP.tsv" \
        "$URL" | tee "$OUT_DIR/ab_$STAMP.txt"
else
    echo "Apache Benchmark (ab) tidak ditemukan dan Docker tidak tersedia."
    echo "Install: sudo apt install apache2-utils"
    exit 1
fi

echo
echo "== Redis stats =="
docker compose exec -T redis redis-cli info stats \
    | grep -E 'total_commands_processed|keyspace_hits|keyspace_misses|instantaneous_ops_per_sec' || true

echo
echo "Hasil disimpan di $OUT_DIR"
