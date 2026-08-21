<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        return view('pages.contact');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        try {
            $receiver = config('mail.contact_receiver', env('CONTACT_RECEIVER_EMAIL', 'devthugscompanyofficial@gmail.com'));

            Mail::to($receiver)->send(new ContactMessage($validated));
        } catch (\Throwable $exception) {
            logger()->error('Contact form email failed', [
                'error' => $exception->getMessage(),
                'data' => $validated,
            ]);

            return redirect()
                ->route('contact')
                ->withInput()
                ->with('contact_error', 'Unable to send your message right now. Please try again later.');
        }

        // Log successful submission
        logger()->info('Contact form submission', $validated);

        return redirect()
            ->route('contact')
            ->with('contact_success', true);
    }
}
