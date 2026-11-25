<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use HasFactory;

    public const ICON_OPTIONS = [
        'crown' => 'Klasik & Elegan',
        'sparkles' => 'Modern & Mewah',
        'flower' => 'Floral & Romantis',
        'layout' => 'Minimalis',
        'landmark' => 'Adat & Budaya',
        'heart' => 'Asmara',
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Return available preset icon keys and Indonesian labels.
     *
     * @return array<string, string>
     */
    public static function iconOptions(): array
    {
        return self::ICON_OPTIONS;
    }

    /**
     * Count templates belonging to this category safely before Issue #6 table migration exists.
     */
    public function templatesCount(): int
    {
        if (! Schema::hasTable('templates')) {
            return 0;
        }

        return (int) DB::table('templates')
            ->where('category_id', $this->id)
            ->count();
    }

    /**
     * Determine whether this category stores an uploaded image path instead of a preset icon key.
     */
    public function hasUploadedIcon(): bool
    {
        return filled($this->icon) && ! array_key_exists($this->icon, self::ICON_OPTIONS);
    }

    /**
     * Resolve public URL for an uploaded category icon image.
     */
    public function iconUrl(): ?string
    {
        if (! $this->hasUploadedIcon()) {
            return null;
        }

        return Storage::disk('public')->url($this->icon);
    }
}
