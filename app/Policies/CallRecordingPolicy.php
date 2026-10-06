<?php

namespace App\Policies;

use App\Models\CallRecording;
use App\Models\User;

class CallRecordingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CallRecording $recording): bool
    {
        if ($user->isAdmin() || $user->isSalesManager()) {
            return true;
        }

        return $recording->user_id === $user->id;
    }

    public function play(User $user, CallRecording $recording): bool
    {
        return $this->view($user, $recording);
    }
}
