<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CallRecording;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CallRecordingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = CallRecording::with(['salesCall', 'customer', 'user']);

        // Protect access: sales executive only sees their own calls
        if ($user->isSalesExecutive()) {
            $query->where('user_id', $user->id);
        } elseif ($employeeId = $request->input('user_id')) {
            $query->where('user_id', $employeeId);
        }

        if ($customerId = $request->input('customer_id')) {
            $query->where('customer_id', $customerId);
        }

        $recordings = $query->latest()->paginate(15)->withQueryString();
        $employees = User::whereIn('role_slug', ['sales_executive', 'sales_manager'])->get();

        return view('calls.recordings', compact('recordings', 'employees'));
    }

    /**
     * Streams the audio file to the HTML5 audio player securely.
     * Enforces authorization checks.
     */
    public function play(CallRecording $recording)
    {
        $user = Auth::user();

        if (!$recording->canBeAccessedBy($user)) {
            abort(403, 'Unauthorized: You do not have permission to listen to this call recording.');
        }

        $path = $recording->file_path;
        if (!Storage::disk('local')->exists($path)) {
            // Check fallback sample
            $path = 'call-recordings/sample_call.wav';
            if (!Storage::disk('local')->exists($path)) {
                abort(404, 'Audio recording file not found on local disk.');
            }
        }

        $fullPath = Storage::disk('local')->path($path);
        $mime = $recording->mime_type ?: 'audio/wav';

        AuditLog::log('recording_played', $recording, "Call recording #{$recording->id} played by {$user->name}");

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $recording->file_name . '"',
            'Accept-Ranges' => 'bytes',
        ]);
    }
}
