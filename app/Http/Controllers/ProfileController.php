<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png',
        ]);

        $user->name = $request->name;
        $user->username = $request->username;

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        if ($request->file('photo')) {
            $file = $request->file('photo');
            $filename = $file->getClientOriginalName(); // opsional: prepend waktu biar unik
            $file->storeAs('public/profile_photos', $filename);
            $user->photo = $filename;
        }


        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
