<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('panel.audit.index', [
            'logs' => AuditLog::with('user')
                ->when($request->query('accion'), fn ($q, $a) => $q->where('action', 'like', "$a%"))
                ->latest('created_at')
                ->paginate(50)
                ->withQueryString(),
        ]);
    }
}
