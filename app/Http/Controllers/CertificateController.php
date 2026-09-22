<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Inertia\Inertia;
use Inertia\Response;

class CertificateController extends Controller
{
    public function verify(string $code): Response
    {
        $certificate = Certificate::where('certificate_code', $code)
            ->with([
                'user:id,name',
                'course:id,title,slug,headline,duration_hours',
                'instructor:id,name',
            ])
            ->firstOrFail();

        return Inertia::render('Certificates/Verify', [
            'certificate' => $certificate,
        ]);
    }
}
