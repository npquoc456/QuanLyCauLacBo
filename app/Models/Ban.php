<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ban extends Model
{
    protected $table = 'bans';

    protected $primaryKey = 'MaBan';

    public $timestamps = true;

    protected $fillable = [
        'TenBan',
        'MoTa',
        'MaCLB',
    ];

    protected function casts(): array
    {
        return [
            'MaCLB' => 'integer',
        ];
    }
}
