<label for="name">Nama</label>
<input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
@error('name') <small class="field-error">{{ $message }}</small> @enderror

<label for="email">Email</label>
<input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
@error('email') <small class="field-error">{{ $message }}</small> @enderror

<label for="password">Password {{ $user->exists ? '(kosongkan kalau tidak diganti)' : '' }}</label>
<input type="password" id="password" name="password" {{ $user->exists ? '' : 'required' }}>
@error('password') <small class="field-error">{{ $message }}</small> @enderror

<label for="role">Role</label>
@if ($user->exists && $user->id === auth()->id())
    <b>{{ $user->role }}</b> <span class="muted">(role akun sendiri tidak bisa diubah)</span>
@else
    <select id="role" name="role" required>
        <option value="staff" @selected(old('role', $user->role) === 'staff')>staff</option>
        <option value="admin" @selected(old('role', $user->role) === 'admin')>admin</option>
    </select>
@endif
@error('role') <small class="field-error">{{ $message }}</small> @enderror
