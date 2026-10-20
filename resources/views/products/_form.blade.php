{{-- Form dipakai bersama oleh halaman tambah dan ubah produk --}}
<label for="category_id">Kategori</label>
<select id="category_id" name="category_id" required>
    <option value="">-- Pilih kategori --</option>
    @foreach ($categories as $category)
        <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>
            {{ $category->name }}
        </option>
    @endforeach
</select>
@error('category_id') <small class="field-error">{{ $message }}</small> @enderror

<label for="sku">SKU</label>
<input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required>
@error('sku') <small class="field-error">{{ $message }}</small> @enderror

<label for="name">Nama produk</label>
<input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required>
@error('name') <small class="field-error">{{ $message }}</small> @enderror

<label for="purchase_price">Harga beli</label>
<input type="number" id="purchase_price" name="purchase_price" min="0" step="0.01" value="{{ old('purchase_price', $product->purchase_price) }}" required>
@error('purchase_price') <small class="field-error">{{ $message }}</small> @enderror

<label for="selling_price">Harga jual</label>
<input type="number" id="selling_price" name="selling_price" min="0" step="0.01" value="{{ old('selling_price', $product->selling_price) }}" required>
@error('selling_price') <small class="field-error">{{ $message }}</small> @enderror

@if (! $product->exists)
    <label for="stock">Stok awal</label>
    <input type="number" id="stock" name="stock" min="0" value="{{ old('stock', $product->stock) }}" required>
    @error('stock') <small class="field-error">{{ $message }}</small> @enderror
@endif

<label for="minimum_stock">Batas stok minimum</label>
<input type="number" id="minimum_stock" name="minimum_stock" min="0" value="{{ old('minimum_stock', $product->minimum_stock) }}" required>
@error('minimum_stock') <small class="field-error">{{ $message }}</small> @enderror
