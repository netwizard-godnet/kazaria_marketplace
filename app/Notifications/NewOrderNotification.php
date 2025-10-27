<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order;
use App\Models\Store;

class NewOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $store;

    public function __construct(Order $order, Store $store)
    {
        $this->order = $order;
        $this->store = $store;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $orderItems = $this->order->orderItems->where('store_id', $this->store->id);
        $storeTotal = $orderItems->sum('total');
        
        return (new MailMessage)
            ->subject('🎉 Nouvelle commande reçue - ' . $this->order->order_number)
            ->view('emails.seller.new-order', [
                'order' => $this->order,
                'store' => $this->store,
                'orderItems' => $orderItems,
                'storeTotal' => $storeTotal
            ]);
    }
}