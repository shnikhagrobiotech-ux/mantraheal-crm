<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationLog extends Model
{
    protected $fillable = [
        'customer_id',
        'lead_id',
        'user_id',
        'channel',
        'template_id',
        'recipient',
        'subject',
        'message_body',
        'status',
        'external_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function template()
    {
        return $this->belongsTo(WhatsAppTemplate::class, 'template_id');
    }
}
