<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kayit extends Model
{
    public const SPENDING_CATEGORIES = [
        'Isunma',
        'Su',
        'Elektrik',
        'Temizlik',
        'Diğer',
    ];

    use HasFactory;
    protected $guarded = [];
    protected $table = 'kayitlar';

    public function dosyalar()
    {
        return $this->hasMany(Dosya::class);
    }

    public function sakin()
    {
        return $this->belongsTo(Sakin::class);
    }
}
