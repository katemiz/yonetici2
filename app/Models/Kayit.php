<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Kayit extends Model implements HasMedia
{
    public const SPENDING_CATEGORIES = [
        'Isınma',
        'Su',
        'Elektrik',
        'Temizlik',
        'Diğer',
    ];

    use HasFactory;
    use InteractsWithMedia;
    protected $guarded = [];
    protected $table = 'kayitlar';

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('alacak-attachments')->useDisk('private_media');
        $this->addMediaCollection('income-attachments')->useDisk('private_media');
        $this->addMediaCollection('expense-attachments')->useDisk('private_media');
        $this->addMediaCollection('payable-attachments')->useDisk('private_media');
    }

    public function dosyalar()
    {
        return $this->hasMany(Dosya::class);
    }

    public function sakin()
    {
        return $this->belongsTo(Sakin::class);
    }
}
