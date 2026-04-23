<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Channel untuk ujian. Semua orang yang login (atau khusus yang punya akses) bisa mendengarkan.
Broadcast::channel('exam.{id}', function ($user, $id) {
    return true; // Simple check, bisa diperketat nanti per jadwal
});
