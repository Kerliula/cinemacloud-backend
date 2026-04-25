<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class Genre extends Model
{
    use HasUuids;
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function movies()
    {
        return $this->belongsToMany(Movie::class);
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Genre $genre): void {
            $genre->slug ??= Str::slug($genre->name);
        });
    }
}
