<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscription;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'newsletter_email' => 'required|email:rfc,dns|max:255',
        ], [], [
            'newsletter_email' => 'email',
        ]);

        $email = strtolower($validated['newsletter_email']);

        $subscription = NewsletterSubscription::firstOrNew(['email' => $email]);

        if (!$subscription->exists || empty($subscription->source)) {
            $subscription->source = $request->input('source', 'homepage');
        }

        $subscription->save();

        return redirect()
            ->back()
            ->with('newsletter_success', $subscription->wasRecentlyCreated
                ? 'Merci ! Votre inscription à la newsletter est confirmée.'
                : 'Votre inscription à la newsletter est déjà enregistrée.');
    }
}

