{{--
  Form item dinamis (dipakai pembelian & penjualan).
  Variabel: $products, $withCost (bool), $priceField ('selling_price' / 'purchase_price')
  Total HANYA perkiraan di layar; total sebenarnya dihitung ulang server.
--}}
@php
    $rows = old('items', [['product_id' => '', 'quantity' => 1, 'unit_cost' => '']]);
    $rows = array_values($rows);
@endphp

@foreach ($errors->get('items') as $message)
    <span class="field-error">{{ $message }}</span>
@endforeach

<div class="table-wrap">
    <table id="items-table">
        <thead>
            <tr>
                <th>Produk</th>
                <th>Jumlah</th>
                @if ($withCost) <th>Harga beli satuan</th> @endif
                <th></th>
            </tr>
        </thead>
        <tbody id="items-body">
            @foreach ($rows as $i => $row)
                <tr class="item-row">
                    <td>
                        <select name="items[{{ $i }}][product_id]" required>
                            <option value="">-- pilih produk --</option>
                            @foreach ($products as $p)
                                <option value="{{ $p->id }}" data-price="{{ $p->{$priceField} }}"
                                    @selected(($row['product_id'] ?? '') == $p->id)>
                                    {{ $p->sku }} - {{ $p->name }} (stok {{ $p->stock }})
                                </option>
                            @endforeach
                        </select>
                        @error("items.$i.product_id") <span class="field-error">{{ $message }}</span> @enderror
                    </td>
                    <td>
                        <input type="number" name="items[{{ $i }}][quantity]" min="1" value="{{ $row['quantity'] ?? 1 }}" required>
                        @error("items.$i.quantity") <span class="field-error">{{ $message }}</span> @enderror
                    </td>
                    @if ($withCost)
                        <td>
                            <input type="number" name="items[{{ $i }}][unit_cost]" min="0" step="0.01" value="{{ $row['unit_cost'] ?? '' }}" required>
                            @error("items.$i.unit_cost") <span class="field-error">{{ $message }}</span> @enderror
                        </td>
                    @endif
                    <td><button type="button" class="btn-light remove-row">Hapus</button></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<p><button type="button" class="btn-light" id="add-row">+ Tambah baris</button></p>

<template id="row-template">
    <tr class="item-row">
        <td>
            <select name="items[__INDEX__][product_id]" required>
                <option value="">-- pilih produk --</option>
                @foreach ($products as $p)
                    <option value="{{ $p->id }}" data-price="{{ $p->{$priceField} }}">
                        {{ $p->sku }} - {{ $p->name }} (stok {{ $p->stock }})
                    </option>
                @endforeach
            </select>
        </td>
        <td><input type="number" name="items[__INDEX__][quantity]" min="1" value="1" required></td>
        @if ($withCost)
            <td><input type="number" name="items[__INDEX__][unit_cost]" min="0" step="0.01" required></td>
        @endif
        <td><button type="button" class="btn-light remove-row">Hapus</button></td>
    </tr>
</template>

<p><strong>Perkiraan total: <span id="est-total">Rp 0</span></strong>
    <span class="muted">(total final dihitung server)</span></p>

<script>
(function () {
    const body = document.getElementById('items-body');
    const tpl = document.getElementById('row-template').innerHTML;
    const withCost = {{ $withCost ? 'true' : 'false' }};
    let next = {{ count($rows) }};

    function rupiah(n) { return 'Rp ' + Math.round(n).toLocaleString('id-ID'); }

    function recalc() {
        let total = 0;
        body.querySelectorAll('.item-row').forEach(function (row) {
            const sel = row.querySelector('select');
            const qty = parseFloat(row.querySelector('input[name$="[quantity]"]').value) || 0;
            let price = 0;
            if (withCost) {
                price = parseFloat(row.querySelector('input[name$="[unit_cost]"]').value) || 0;
            } else if (sel.selectedOptions[0]) {
                price = parseFloat(sel.selectedOptions[0].dataset.price) || 0;
            }
            total += qty * price;
        });
        document.getElementById('est-total').textContent = rupiah(total);
    }

    document.getElementById('add-row').addEventListener('click', function () {
        body.insertAdjacentHTML('beforeend', tpl.replaceAll('__INDEX__', next++));
        recalc();
    });

    body.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-row')) {
            if (body.querySelectorAll('.item-row').length > 1) {
                e.target.closest('tr').remove();
                recalc();
            }
        }
    });

    body.addEventListener('input', recalc);
    body.addEventListener('change', function (e) {
        // isi otomatis harga beli dari harga produk saat produk dipilih
        if (withCost && e.target.tagName === 'SELECT') {
            const cost = e.target.closest('tr').querySelector('input[name$="[unit_cost]"]');
            const opt = e.target.selectedOptions[0];
            if (cost && !cost.value && opt && opt.dataset.price) cost.value = opt.dataset.price;
        }
        recalc();
    });
    recalc();
})();
</script>
