<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelIntegration extends Model
{
    protected $fillable = [
        'user_id', 'channel_type', 'name', 'credentials', 'meta',
        'status', 'oauth_state', 'token_expires_at', 'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'token_expires_at' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    public function getCredentialsAttribute(?string $value): array
    {
        if ($value === null) return [];
        return json_decode(decrypt($value), true) ?? [];
    }

    public function setCredentialsAttribute(array $value): void
    {
        $this->attributes['credentials'] = encrypt(json_encode($value));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stores()
    {
        return $this->hasMany(Store::class);
    }

    public function listings()
    {
        return $this->hasMany(ChannelListing::class);
    }

    public function adCampaigns()
    {
        return $this->hasMany(AdCampaign::class);
    }

    public function isStore(): bool
    {
        return in_array($this->channel_type, ['woocommerce', 'shopify', 'magento']);
    }

    public function isMarketplace(): bool
    {
        return in_array($this->channel_type, ['bol', 'amazon']);
    }

    public function isAdvertising(): bool
    {
        return in_array($this->channel_type, ['google_ads', 'facebook_ads']);
    }

    public function isFoodDelivery(): bool
    {
        return in_array($this->channel_type, ['gofood', 'grabfood']);
    }

    public function isTokenExpired(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isPast();
    }
}
