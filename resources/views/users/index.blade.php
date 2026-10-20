@extends('layouts.app')

@section('title', 'Pengguna')

@section('content')
    <h1>Pengguna</h1>
    <p><a class="btn" href="{{ route('users.create') }}">+ Tambah Pengguna</a></p>

    <div class="table-wrap">
        <table>
            <tr><th>Nama</th><th>Email</th><th>Role</th><th>Aksi</th></tr>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role }}</td>
                    <td>
                        <a href="{{ route('users.edit', $user) }}">Ubah</a>
                        @if ($user->id === auth()->id())
                            <span class="muted">(akun kamu)</span>
                        @else
                            <form class="inline-form" method="POST" action="{{ route('users.destroy', $user) }}"
                                  onsubmit="return confirm('Hapus pengguna ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Hapus</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    </div>

    {{ $users->links('pagination.simple') }}
@endsection
