<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailLog extends Model
{
    use HasFactory;

    protected $table = 'mail_logs';

    protected $fillable = [
        'from',
        'to',
        'cc',
        'bcc',
        'subject',
        'body',
        'headers',
        'attachments',
        'message_id',
        'status',
        'data',
        'opened',
        'delivered',
        'complaint',
        'bounced',
    ];

    protected $casts = [
        'attachments' => 'array',
        'data' => 'array',
        'opened' => 'boolean',
        'delivered' => 'boolean',
        'complaint' => 'boolean',
        'bounced' => 'boolean',
    ];
}
