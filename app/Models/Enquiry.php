<?php

namespace App\Models;

use App\Enums\EnquiryStatus;
use Carbon\CarbonImmutable;
use Database\Factories\EnquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable(['product_id', 'product_name', 'sku', 'product_url', 'name', 'company', 'email', 'mobile', 'quantity', 'message', 'status', 'read_at'])]
class Enquiry extends Model
{
    /** @use HasFactory<EnquiryFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => 'new',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'status' => EnquiryStatus::class,
            'read_at' => 'datetime',
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
     * Attach a product, keeping a copy of its name, SKU and public URL.
     */
    public function attachProduct(Product $product): static
    {
        $this->forceFill([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'product_url' => route('products.show', $product),
        ]);

        return $this;
    }

    /**
     * When the enquiry arrived, in the business's local time zone.
     *
     * @return Attribute<Carbon, never>
     */
    protected function receivedAt(): Attribute
    {
        return Attribute::get(fn (): Carbon => $this->created_at->copy()->setTimezone(config('app.display_timezone')));
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function markAsRead(): void
    {
        if ($this->isUnread()) {
            $this->forceFill(['read_at' => now()])->saveQuietly();
        }
    }

    #[Scope]
    protected function unread(Builder $query): void
    {
        $query->whereNull('read_at');
    }

    /**
     * Enquiries whose customer name, company, email, mobile, product or SKU contains the search text.
     */
    #[Scope]
    protected function search(Builder $query, string $text): void
    {
        $query->where(fn (Builder $query) => $query
            ->where('name', 'like', "%{$text}%")
            ->orWhere('company', 'like', "%{$text}%")
            ->orWhere('email', 'like', "%{$text}%")
            ->orWhere('mobile', 'like', "%{$text}%")
            ->orWhere('product_name', 'like', "%{$text}%")
            ->orWhere('sku', 'like', "%{$text}%"));
    }

    /**
     * Enquiries received on or between the given days, which are dates in the business's local time zone.
     */
    #[Scope]
    protected function receivedBetween(Builder $query, ?string $fromDate, ?string $toDate): void
    {
        $timezone = config('app.display_timezone');

        $query
            ->when($fromDate, fn (Builder $query) => $query->where(
                'created_at', '>=', CarbonImmutable::parse($fromDate, $timezone)->startOfDay()->utc(),
            ))
            ->when($toDate, fn (Builder $query) => $query->where(
                'created_at', '<', CarbonImmutable::parse($toDate, $timezone)->addDay()->startOfDay()->utc(),
            ));
    }
}
