<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Conversation;
use App\Models\User;

class MessageController extends Controller
{
    /**
     * Afficher la liste des conversations
     */
    public function index(Request $request)
    {
        $query = Conversation::with(['user1', 'user2', 'lastMessage'])
                            ->orderBy('last_message_at', 'desc');
        
        // Filtres
        if ($request->filled('type')) {
            $query->where('conversation_type', $request->type);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user1', function($userQuery) use ($search) {
                    $userQuery->where('nom', 'like', "%{$search}%")
                             ->orWhere('prenoms', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('user2', function($userQuery) use ($search) {
                    $userQuery->where('nom', 'like', "%{$search}%")
                             ->orWhere('prenoms', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }
        
        $conversations = $query->paginate(15)->withQueryString();
        
        // Statistiques
        $stats = [
            'total' => Conversation::count(),
            'unread' => Message::where('is_read', false)->count(),
            'support' => Conversation::where('conversation_type', 'support')->count(),
            'general' => Conversation::where('conversation_type', 'general')->count(),
            'admin' => Conversation::where('conversation_type', 'admin')->count(),
        ];
        
        return view('admin.messages.index', compact('conversations', 'stats'));
    }
    
    /**
     * Afficher une conversation spécifique
     */
    public function show(Conversation $conversation)
    {
        $conversation->load(['user1', 'user2', 'messages.sender', 'messages.receiver']);
        
        // Marquer tous les messages comme lus
        $conversation->messages()->where('receiver_id', auth()->id())
                    ->where('is_read', false)
                    ->update(['is_read' => true, 'read_at' => now()]);
        
        return view('admin.messages.show', compact('conversation'));
    }
    
    /**
     * Envoyer un message
     */
    public function store(Request $request, Conversation $conversation)
    {
        $request->validate([
            'body' => 'required|string|max:1000',
            'message_type' => 'in:text,image,file,system',
            'priority' => 'in:1,2,3'
        ]);
        
        $message = $conversation->messages()->create([
            'sender_id' => auth()->id(),
            'receiver_id' => $conversation->getOtherUser(auth()->id())->id,
            'body' => $request->body,
            'message_type' => $request->message_type ?? 'text',
            'priority' => $request->priority ?? 1,
            'attachments' => $request->attachments ?? null
        ]);
        
        // Mettre à jour la date du dernier message
        $conversation->updateLastMessage();
        
        // Créer une notification pour le destinataire
        \App\Models\Notification::createNotification(
            $conversation->getOtherUser(auth()->id())->id,
            'message',
            'Nouveau message',
            'Vous avez reçu un nouveau message de ' . auth()->user()->nom,
            ['conversation_id' => $conversation->id, 'message_id' => $message->id]
        );
        
        return response()->json([
            'success' => true,
            'message' => $message->load('sender')
        ]);
    }
    
    /**
     * Créer une nouvelle conversation
     */
    public function createConversation(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'subject' => 'nullable|string|max:255',
            'conversation_type' => 'in:support,general,admin'
        ]);
        
        $otherUser = User::findOrFail($request->user_id);
        
        // Vérifier si une conversation existe déjà
        $conversation = Conversation::where(function($q) use ($otherUser) {
            $q->where('user1_id', auth()->id())
              ->where('user2_id', $otherUser->id);
        })->orWhere(function($q) use ($otherUser) {
            $q->where('user1_id', $otherUser->id)
              ->where('user2_id', auth()->id());
        })->first();
        
        if (!$conversation) {
            $conversation = Conversation::create([
                'user1_id' => auth()->id(),
                'user2_id' => $otherUser->id,
                'subject' => $request->subject,
                'conversation_type' => $request->conversation_type ?? 'general',
                'last_message_at' => now()
            ]);
        }
        
        return redirect()->route('admin.messages.show', $conversation);
    }
    
    /**
     * Marquer une conversation comme importante
     */
    public function toggleImportant(Conversation $conversation)
    {
        $conversation->update(['is_important' => !$conversation->is_important]);
        
        return response()->json([
            'success' => true,
            'is_important' => $conversation->is_important
        ]);
    }
    
    /**
     * Archiver une conversation
     */
    public function archive(Conversation $conversation)
    {
        $conversation->update(['is_archived' => true]);
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Désarchiver une conversation
     */
    public function unarchive(Conversation $conversation)
    {
        $conversation->update(['is_archived' => false]);
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Supprimer une conversation
     */
    public function destroy(Conversation $conversation)
    {
        $conversation->messages()->delete();
        $conversation->delete();
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Obtenir les messages d'une conversation (AJAX)
     */
    public function getMessages(Conversation $conversation)
    {
        $messages = $conversation->messages()
                               ->with(['sender', 'receiver'])
                               ->orderBy('created_at', 'asc')
                               ->get();
        
        return response()->json($messages);
    }
    
    /**
     * Marquer un message comme lu
     */
    public function markAsRead(Message $message)
    {
        $message->markAsRead();
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Obtenir les conversations non lues
     */
    public function getUnreadConversations()
    {
        $conversations = Conversation::whereHas('messages', function($query) {
            $query->where('receiver_id', auth()->id())
                  ->where('is_read', false);
        })->with(['user1', 'user2', 'lastMessage'])
          ->orderBy('last_message_at', 'desc')
          ->limit(10)
          ->get();
        
        return response()->json($conversations);
    }
}
