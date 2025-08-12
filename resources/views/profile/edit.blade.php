@extends('main')
@section('title', 'Dashboard')
@section('breadcumb-2', 'Dashboard')
@section('breadcumb-3', 'Index')

@section('content')
<div class="container">
    <h2>Edit Profil</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Password Baru (kosongkan jika tidak ingin diganti dan minimal 8 karakter)</label>
            <input type="password" name="password" class="form-control">
        </div>

        <div class="mb-3">
            <label>Konfirmasi Password</label>
            <input type="password" name="password_confirmation" class="form-control">
        </div>

        <div class="mb-3">
            <label>Foto Profil</label><br>
            <img src="{{ $user->photo ? asset('assets/img/profile/' . $user->photo) : asset('default.png') }}" width="100" class="mb-2">

            <input type="file" name="photo" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>
@endsection
