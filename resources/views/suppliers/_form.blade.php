<label for="name">Nama pemasok</label>
<input type="text" id="name" name="name" value="{{ old('name', $supplier->name) }}" required>
@error('name') <small class="field-error">{{ $message }}</small> @enderror

<label for="phone">Telepon (boleh kosong)</label>
<input type="text" id="phone" name="phone" value="{{ old('phone', $supplier->phone) }}">
@error('phone') <small class="field-error">{{ $message }}</small> @enderror

<label for="email">Email (boleh kosong)</label>
<input type="email" id="email" name="email" value="{{ old('email', $supplier->email) }}">
@error('email') <small class="field-error">{{ $message }}</small> @enderror
