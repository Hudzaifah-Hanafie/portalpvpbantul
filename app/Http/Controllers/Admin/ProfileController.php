<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Profile;
use Illuminate\Support\Facades\Storage;
use App\Support\HtmlSanitizer;

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

        $rules = [
            'judul' => 'required',
            'gambar' => 'nullable|mimes:jpg,jpeg,png|max:2048',
        ];

        if ($isVisiMisi) {
            $rules['visi_text'] = 'required|string';
            $rules['misi_text'] = 'required|string';
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
            // Hapus gambar lama (opsional, praktik yang baik)
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
                'visi' => HtmlSanitizer::clean($request->visi_text),
                'misi' => HtmlSanitizer::clean($request->misi_text),
            ]);
        } else {
            $profile->konten = $profile->key === 'profil_denah'
                ? HtmlSanitizer::cleanEmbed($request->konten)
                : HtmlSanitizer::clean($request->konten);
        }
        $profile->save();

        return redirect()->route('admin.profile.index')->with('success', 'Profil berhasil diperbarui!');
    }
}
