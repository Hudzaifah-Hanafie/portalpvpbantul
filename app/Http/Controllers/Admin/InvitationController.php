<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\InvitationMail;
use App\Models\Invitation;
use App\Models\Role;
use App\Support\EmailSettings;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index()
    {
        $invitations = Invitation::with(['role', 'creator', 'usedBy'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.invitations.index', compact('invitations'));
    }

    public function create()
    {
        return view('admin.invitations.create', [
            'roles' => Role::orderBy('label')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'message' => ['nullable', 'string', 'max:1024'],
        ]);

        $token = Str::upper(Str::random(32));

        $invitation = Invitation::create([
            'email' => $data['email'],
            'role_id' => $data['role_id'],
            'token' => Invitation::hashToken($token),
            'expires_at' => isset($data['expires_at'])
                ? Carbon::parse($data['expires_at'])
                : now()->addDays(7),
            'created_by_id' => $request->user()->id,
        ]);

        if (EmailSettings::confirmationsEnabled()) {
            Mail::to($invitation->email)->send(new InvitationMail($invitation, $token, $data['message'] ?? null));
        }

        $this->logger->log(
            $request->user(),
            'invitation.created',
            'Membuat undangan pengguna baru',
            $invitation,
            ['email' => $invitation->email, 'role' => $invitation->role?->name]
        );

        return redirect()->route('admin.invitations.index')->with('success', 'Undangan berhasil dibuat.');
    }

    public function destroy(Request $request, Invitation $invitation)
    {
        if ($invitation->isUsed()) {
            return back()->with('error', 'Undangan sudah digunakan dan tidak bisa dibatalkan.');
        }

        $invitation->revoked_at = now();
        $invitation->save();

        $this->logger->log(
            $request->user(),
            'invitation.revoked',
            'Membatalkan undangan',
            $invitation,
            ['email' => $invitation->email]
        );

        return back()->with('success', 'Undangan dibatalkan.');
    }
}
