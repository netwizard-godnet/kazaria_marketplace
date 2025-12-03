<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterBroadcastMail;
use App\Models\NewsletterSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NewsletterController extends Controller
{
    public function index()
    {
        $subscribers = NewsletterSubscription::latest()->paginate(20);
        $total = NewsletterSubscription::count();

        return view('admin.newsletter.index', compact('subscribers', 'total'));
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
        ]);

        $emailsCount = NewsletterSubscription::count();

        if ($emailsCount === 0) {
            return redirect()->back()->with('error', 'Aucun abonné n\'est inscrit pour le moment.');
        }

        $sent = 0;

        NewsletterSubscription::query()
            ->select('email')
            ->chunk(50, function ($subscriptions) use (&$sent, $validated) {
                foreach ($subscriptions as $subscription) {
                    try {
                        Mail::to($subscription->email)
                            ->send(new NewsletterBroadcastMail($validated['subject'], $validated['message']));
                        $sent++;
                    } catch (\Throwable $th) {
                        report($th);
                    }
                }
            });

        return redirect()->back()->with('success', "Message envoyé à {$sent} abonné(s).");
    }
}

