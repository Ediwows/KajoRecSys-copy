<?php

namespace App\Http\Controllers;

use App\Models\CnsRequest;
use App\Models\SlipAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CnsController extends Controller
{
    public function show(Request $request, CnsRequest $cnsRequest): View
    {
        $this->authorizeRequest($request, $cnsRequest);
        $assignment = SlipAssignment::query()
            ->where('cns_request_id', $cnsRequest->id)
            ->first();

        return view('slips.cns.submitted', compact('cnsRequest', 'assignment'));
    }

    public function pdf(Request $request, CnsRequest $cnsRequest): StreamedResponse
    {
        $this->authorizeRequest($request, $cnsRequest);
        abort_unless(Storage::disk('local')->exists($cnsRequest->pdf_path), 404);

        return Storage::disk('local')->response(
            $cnsRequest->pdf_path,
            "CNS-request-{$cnsRequest->id}.pdf",
            ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline']
        );
    }

    private function authorizeRequest(Request $request, CnsRequest $cnsRequest): void
    {
        abort_unless(
            $cnsRequest->user_id === $request->user()->id
                || $request->user()->role === 'guidance_counselor',
            403
        );
    }

}
