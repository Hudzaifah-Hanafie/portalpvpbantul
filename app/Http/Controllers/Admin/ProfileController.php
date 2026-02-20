<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Profile;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    // 1. Tampilkan Daftar Halaman Profil
    public function index()
    {
        $profiles = Profile::all();
        return view('admin.profile.index', compact('profiles'));
    }

    // 2. Form Edit
    public function edit($id)
    {
        $profile = Profile::findOrFail($id);
        return view('admin.profile.edit', compact('profile'));
    }

    // 3. Simpan Perubahan
    public function update(Request $request, $id)
    {
        $profile = Profile::findOrFail($id);

        $isVisiMisi = $profile->key === 'visi_misi';
        $isDenah = $profile->key === 'profil_denah';

        $rules = [
            'judul' => 'required',
            'gambar' => 'nullable|mimes:jpg,jpeg,png|max:2048',
        ];

        if ($isVisiMisi) {
            $rules['visi_text'] = 'required|string';
            $rules['misi_text'] = 'required|string';
        } elseif ($isDenah) {
            $rules['konten'] = 'nullable|string';
            $rules['subjudul'] = 'nullable|string';
        } else {
            $rules['konten'] = 'nullable|string';
        }

        $request->validate($rules, [
            'gambar.mimes' => 'Format gambar harus JPG atau PNG.',
            'gambar.max' => 'Ukuran gambar maksimal 2 MB.',
            'visi_text.required' => 'Bagian visi wajib diisi.',
            'misi_text.required' => 'Bagian misi wajib diisi.',
        ]);

        // Update Gambar jika ada
        if ($request->hasFile('gambar')) {
            // Hapus gambar lama
            if ($profile->gambar) {
                $oldPath = str_replace('/storage/', '', $profile->gambar);
                Storage::disk('public')->delete($oldPath);
            }
            
            // Simpan path gambar
            $path = $request->file('gambar')->store('profiles', 'public');
            $profile->gambar = '/storage/' . $path;
        }

        // Update Data
        $profile->judul = $request->judul;
        
        if ($isVisiMisi) {
            $profile->konten = json_encode([
                'visi' => strip_tags($request->visi_text, '<p><br><strong><em><ul><ol><li>'),
                'misi' => strip_tags($request->misi_text, '<p><br><strong><em><ul><ol><li>'),
            ]);
        } elseif ($isDenah) {
            $profile->konten = json_encode([
                'map' => strip_tags($request->konten, '<iframe><div>'),
                'subjudul' => strip_tags($request->subjudul, '<b><strong><em>'),
            ]);
        } else {
            $profile->konten = strip_tags($request->konten, '<p><br><strong><em><ul><ol><li><a>');
        }
        
        $profile->save();

        return redirect()->route('admin.profile.index')->with('success', 'Profil berhasil diperbarui!');
    }
}
