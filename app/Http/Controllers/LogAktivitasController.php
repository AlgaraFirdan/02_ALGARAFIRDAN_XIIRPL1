<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogAktivitasController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $logs = LogAktivitas::query()
            ->with('user')
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where('aktivitas', 'like', '%' . $q . '%');
            })
            ->orderByDesc('id_log')
            ->paginate(12)
            ->withQueryString();

        return view('log-aktivitas.index', [
            'logs' => $logs,
            'q' => $q,
        ]);
    }
}
