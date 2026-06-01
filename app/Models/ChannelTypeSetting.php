<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelTypeSetting extends Model
{
    protected $primaryKey = 'channel_type';
    protected $keyType    = 'string';
    public    $incrementing = false;

    protected $fillable = ['channel_type', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
