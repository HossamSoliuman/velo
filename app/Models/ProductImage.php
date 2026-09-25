<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['product_id', 'path', 'alt', 'sort_order'])]
class ProductImage extends Model
{
    protected static function booted(): void
    {
        static::deleted(function (ProductImage $image) {
            if (! $image->isExternal()) {
                Storage::disk('public')->delete($image->path);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return Attribute<string, never>
     */
    protected function url(): Attribute
    {
        return Attribute::get(fn (): string => $this->isExternal()
            ? $this->path
            : Storage::disk('public')->url($this->path));
    }

    /**
     * Whether the image is hosted elsewhere rather than stored on the public disk.
     */
    public function isExternal(): bool
    {
        return Str::startsWith($this->path, ['http://', 'https://']);
    }
}
