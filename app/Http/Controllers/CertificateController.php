<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\View\View;

class CertificateController extends Controller
{
    public function verify(string $code): View
    {
        $certificate = Certificate::query()
            ->with(['enrollment.user:id,name,university', 'enrollment.program:id,title,type'])
            ->whereRaw('LOWER(code) = ?', [mb_strtolower($code)])
            ->first();

        return view('certificates.verify', compact('certificate', 'code'));
    }
}
