<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\Message;
use App\Models\User;
use App\Models\Conversation;

class HeaderDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer l'utilisateur admin
        $admin = User::where('is_admin', true)->first();
        
        if (!$admin) {
            $this->command->warn('Aucun utilisateur admin trouvé. Création d\'un admin de test...');
            $admin = User::create([
                'prenoms' => 'Admin',
                'nom' => 'Kazaria',
                'email' => 'admin@kazaria.ci',
                'password' => bcrypt('password'),
                'is_admin' => true,
                'is_active' => true,
                'email_verified_at' => now()
            ]);
        }

        // Créer des notifications de test
        $notifications = [
            [
                'type' => 'order',
                'title' => 'Nouvelle commande',
                'message' => 'Commande #ORD-001 reçue de Jean Dupont',
                'priority' => 2
            ],
            [
                'type' => 'user',
                'title' => 'Nouvel utilisateur',
                'message' => 'Marie Martin s\'est inscrite sur la plateforme',
                'priority' => 1
            ],
            [
                'type' => 'product',
                'title' => 'Produit en attente',
                'message' => 'Le produit "iPhone 15 Pro" attend votre validation',
                'priority' => 2
            ],
            [
                'type' => 'payment',
                'title' => 'Paiement reçu',
                'message' => 'Paiement de 150 000 FCFA confirmé pour la commande #ORD-002',
                'priority' => 1
            ],
            [
                'type' => 'warning',
                'title' => 'Stock faible',
                'message' => 'Le produit "Samsung Galaxy S24" est en rupture de stock',
                'priority' => 3
            ],
            [
                'type' => 'system',
                'title' => 'Maintenance programmée',
                'message' => 'Maintenance du système prévue demain à 2h00',
                'priority' => 1
            ]
        ];

        foreach ($notifications as $notificationData) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => $notificationData['type'],
                'title' => $notificationData['title'],
                'message' => $notificationData['message'],
                'priority' => $notificationData['priority'],
                'is_read' => rand(0, 1) == 1, // Certaines notifications sont lues
                'read_at' => rand(0, 1) == 1 ? now()->subHours(rand(1, 24)) : null,
                'created_at' => now()->subHours(rand(1, 48))
            ]);
        }

        // Créer des utilisateurs de test pour les messages
        $users = User::where('is_admin', false)->limit(3)->get();
        
        if ($users->count() == 0) {
            $this->command->warn('Création d\'utilisateurs de test pour les messages...');
            $users = collect([
                User::create([
                    'prenoms' => 'Jean',
                    'nom' => 'Dupont',
                    'email' => 'jean.dupont@example.com',
                    'password' => bcrypt('password'),
                    'is_active' => true,
                    'email_verified_at' => now()
                ]),
                User::create([
                    'prenoms' => 'Marie',
                    'nom' => 'Martin',
                    'email' => 'marie.martin@example.com',
                    'password' => bcrypt('password'),
                    'is_active' => true,
                    'email_verified_at' => now()
                ]),
                User::create([
                    'prenoms' => 'Pierre',
                    'nom' => 'Durand',
                    'email' => 'pierre.durand@example.com',
                    'password' => bcrypt('password'),
                    'is_active' => true,
                    'email_verified_at' => now()
                ])
            ]);
        }

        // Créer des conversations
        foreach ($users as $user) {
            $conversation = Conversation::create([
                'user1_id' => $admin->id,
                'user2_id' => $user->id,
                'subject' => 'Demande d\'information',
                'last_message_at' => now()->subHours(rand(1, 24))
            ]);

            // Créer des messages
            $messages = [
                [
                    'subject' => 'Question sur ma commande',
                    'body' => 'Bonjour, j\'aimerais savoir quand ma commande sera livrée. Merci.',
                    'priority' => 1
                ],
                [
                    'subject' => 'Problème avec un produit',
                    'body' => 'Le produit que j\'ai reçu ne correspond pas à la description. Pouvez-vous m\'aider ?',
                    'priority' => 2
                ],
                [
                    'subject' => 'Demande de remboursement',
                    'body' => 'Je souhaite annuler ma commande et être remboursé. Comment procéder ?',
                    'priority' => 2
                ]
            ];

            $messageData = $messages[array_rand($messages)];
            
            Message::create([
                'sender_id' => $user->id,
                'receiver_id' => $admin->id,
                'conversation_id' => $conversation->id,
                'subject' => $messageData['subject'],
                'body' => $messageData['body'],
                'priority' => $messageData['priority'],
                'is_read' => rand(0, 1) == 1,
                'read_at' => rand(0, 1) == 1 ? now()->subHours(rand(1, 12)) : null,
                'created_at' => now()->subHours(rand(1, 24))
            ]);
        }

        $this->command->info('Données de test pour le header créées avec succès !');
        $this->command->info('- ' . Notification::count() . ' notifications créées');
        $this->command->info('- ' . Message::count() . ' messages créés');
    }
}