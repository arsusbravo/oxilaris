<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = [
        'user_id', 'channel_integration_id', 'name', 'url',
        'sync_status', 'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function channelIntegration()
    {
        return $this->belongsTo(ChannelIntegration::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function isManual(): bool
    {
        return $this->channel_integration_id === null;
    }
}
