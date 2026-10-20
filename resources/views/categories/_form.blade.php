<label for="name">Nama kategori</label>
<input type="text" id="name" name="name" value="{{ old('name', $category->name) }}" required>
@error('name') <small class="field-error">{{ $message }}</small> @enderror
