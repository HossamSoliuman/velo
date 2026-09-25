<?php

namespace App\Models;

use App\Casts\SanitizedHtml;
use App\Models\Concerns\HasSeoFields;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'slug', 'content', 'meta_title', 'meta_description', 'og_title', 'og_description', 'og_image', 'robots'])]
class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory, HasSeoFields;

    protected function casts(): array
    {
        return [
            'content' => SanitizedHtml::class,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
