<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vet;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StaffApprovalController extends Controller
{
    /**
     * Display pending staff applications.
     */
    public function index(): View
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $applications = Vet::with('user')
            ->where('approval_status', 'pending')
            ->latest()
            ->get();

        return view('admin.staff-applications', compact('applications'));
    }

    /**
     * Approve a staff application.
     */
    public function approve(Vet $vet): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        if ($vet->approval_status !== 'pending') {
            return back()->with(
                'error',
                'This application has already been processed.'
            );
        }

        $vet->update([
            'approval_status' => 'approved',
        ]);

        return back()->with(
            'success',
            'Staff application approved successfully.'
        );
    }

    /**
     * Reject a staff application.
     */
    public function reject(Vet $vet): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        if ($vet->approval_status !== 'pending') {
            return back()->with(
                'error',
                'This application has already been processed.'
            );
        }

        $vet->update([
            'approval_status' => 'rejected',
        ]);

        return back()->with(
            'success',
            'Staff application rejected successfully.'
        );
    }
}
