{{--
    Grafik batang sederhana (SVG) yang digambar di server. Tidak butuh library JavaScript
    atau internet. Data berasal dari DashboardController::trend().
--}}
@php
    $w = 760; $h = 280;
    $left = 64; $right = 12; $top = 16; $bottom = 36;
    $plotW = $w - $left - $right;
    $plotH = $h - $top - $bottom;

    $all = array_merge($trend['sales'], $trend['purchases']);
    $max = max($all) > 0 ? max($all) : 1;
    // batas atas dibulatkan ke angka "bulat" supaya garis bantu rapi
    $unit = 10 ** max((int) floor(log10($max)), 0);
    $niceMax = ceil($max / $unit) * $unit;

    $short = function (float $v): string {
        if ($v >= 1000000) return rtrim(rtrim(number_format($v / 1000000, 1, ',', '.'), '0'), ',') . ' jt';
        if ($v >= 1000) return number_format($v / 1000, 0, ',', '.') . ' rb';
        return (string) (int) $v;
    };

    $n = count($trend['labels']);
    $groupW = $plotW / $n;
    $barW = $groupW * 0.32;
@endphp

<div class="chart-legend">
    <span><i class="dot dot-sales"></i> Penjualan</span>
    <span><i class="dot dot-purchases"></i> Pembelian (modal)</span>
</div>

<svg viewBox="0 0 {{ $w }} {{ $h }}" class="trend-chart" role="img" aria-label="Grafik penjualan dan pembelian 7 hari terakhir">
    {{-- garis bantu horizontal + label sumbu Y --}}
    @for ($i = 0; $i <= 4; $i++)
        @php $y = $top + $plotH - ($plotH * $i / 4); @endphp
        <line x1="{{ $left }}" y1="{{ $y }}" x2="{{ $w - $right }}" y2="{{ $y }}" stroke="#e4ddcc" stroke-width="1"/>
        <text x="{{ $left - 8 }}" y="{{ $y + 4 }}" text-anchor="end" font-size="11" fill="#6b6f7b">{{ $short($niceMax * $i / 4) }}</text>
    @endfor

    @foreach ($trend['labels'] as $i => $label)
        @php
            $gx = $left + $groupW * $i + $groupW * 0.18;
            $sh = $plotH * $trend['sales'][$i] / $niceMax;
            $ph = $plotH * $trend['purchases'][$i] / $niceMax;
        @endphp
        <rect x="{{ $gx }}" y="{{ $top + $plotH - $sh }}" width="{{ $barW }}" height="{{ $sh }}" rx="3" fill="#1f2a44">
            <title>{{ $label }}: penjualan Rp {{ number_format($trend['sales'][$i], 0, ',', '.') }}</title>
        </rect>
        <rect x="{{ $gx + $barW + 4 }}" y="{{ $top + $plotH - $ph }}" width="{{ $barW }}" height="{{ $ph }}" rx="3" fill="#b8913f">
            <title>{{ $label }}: pembelian Rp {{ number_format($trend['purchases'][$i], 0, ',', '.') }}</title>
        </rect>
        <text x="{{ $gx + $barW + 2 }}" y="{{ $h - 14 }}" text-anchor="middle" font-size="12" fill="#6b6f7b">{{ $label }}</text>
    @endforeach
</svg>

<p class="muted chart-total">
    Total 7 hari: penjualan <strong>Rp {{ number_format(array_sum($trend['sales']), 0, ',', '.') }}</strong>,
    pembelian <strong>Rp {{ number_format(array_sum($trend['purchases']), 0, ',', '.') }}</strong>.
    Grafik dihitung ulang dari database setiap halaman dibuka.
</p>
