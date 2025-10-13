<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function sendMessage(Request $request)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'prenom' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telephone' => 'nullable|string|max:20',
            'sujet' => 'required|string|in:commande,livraison,paiement,retour,produit,compte,vendeur,autre',
            'message' => 'required|string|min:10|max:2000',
            'newsletter' => 'boolean'
        ], [
            'prenom.required' => 'Le prénom est obligatoire',
            'nom.required' => 'Le nom est obligatoire',
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'L\'email doit être valide',
            'sujet.required' => 'Veuillez sélectionner un sujet',
            'message.required' => 'Le message est obligatoire',
            'message.min' => 'Le message doit contenir au moins 10 caractères',
            'message.max' => 'Le message ne peut pas dépasser 2000 caractères'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Préparer les données du message
            $messageData = [
                'prenom' => $request->prenom,
                'nom' => $request->nom,
                'email' => $request->email,
                'telephone' => $request->telephone,
                'sujet' => $this->getSubjectLabel($request->sujet),
                'message' => $request->message,
                'newsletter' => $request->boolean('newsletter'),
                'date' => now()->format('d/m/Y H:i'),
                'ip' => $request->ip()
            ];

            // Envoyer l'email (si configuré)
            if (config('mail.default') !== 'log') {
                try {
                    Mail::send('emails.contact', $messageData, function ($mail) use ($messageData) {
                        $mail->to(config('mail.from.address', 'contact@kazaria.ci'))
                            ->subject('Nouveau message de contact - ' . $messageData['sujet'])
                            ->replyTo($messageData['email'], $messageData['prenom'] . ' ' . $messageData['nom']);
                    });
                } catch (\Exception $e) {
                    \Log::warning('Erreur envoi email contact: ' . $e->getMessage());
                }
            }

            // Log du message (pour debug et suivi)
            \Log::info('Nouveau message de contact reçu', $messageData);

            return response()->json([
                'success' => true,
                'message' => 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.',
                'data' => [
                    'nom' => $messageData['prenom'] . ' ' . $messageData['nom'],
                    'sujet' => $messageData['sujet']
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur traitement message contact: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'envoi de votre message. Veuillez réessayer ou nous contacter directement.'
            ], 500);
        }
    }

    private function getSubjectLabel($subject)
    {
        $subjects = [
            'commande' => 'Question sur une commande',
            'livraison' => 'Livraison',
            'paiement' => 'Paiement',
            'retour' => 'Retour/Échange',
            'produit' => 'Question sur un produit',
            'compte' => 'Problème de compte',
            'vendeur' => 'Devenir vendeur',
            'autre' => 'Autre'
        ];

        return $subjects[$subject] ?? 'Autre';
    }
}
