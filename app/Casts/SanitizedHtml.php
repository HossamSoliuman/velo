<?php

namespace App\Casts;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Stores rich text (from the Trix editor) with every tag and attribute outside an allow-list removed.
 */
class SanitizedHtml implements CastsAttributes
{
    /**
     * Allowed tags and the attributes each may keep.
     *
     * @var array<string, list<string>>
     */
    private const ALLOWED_TAGS = [
        'p' => [], 'div' => [], 'br' => [], 'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [],
        'del' => [], 'h1' => [], 'h2' => [], 'h3' => [], 'h4' => [], 'blockquote' => [], 'pre' => [], 'code' => [],
        'ul' => [], 'ol' => [], 'li' => [], 'a' => ['href'],
    ];

    /**
     * Tags removed together with everything inside them. Other unknown tags are unwrapped, keeping their text.
     *
     * @var list<string>
     */
    private const DROPPED_TAGS = [
        'script', 'style', 'iframe', 'frame', 'frameset', 'object', 'embed', 'applet', 'template', 'noscript', 'svg',
        'math', 'form', 'input', 'button', 'select', 'textarea', 'link', 'meta', 'base', 'head', 'title', 'img',
        'video', 'audio', 'picture', 'source', 'canvas',
    ];

    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return self::clean($value);
    }

    /**
     * Strip disallowed tags, attributes and link schemes from the given HTML.
     */
    public static function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        $document = new DOMDocument;
        $previousSetting = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="UTF-8"><div>'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previousSetting);

        $root = $document->getElementsByTagName('div')->item(0);

        if ($root === null) {
            return null;
        }

        self::cleanChildren($root);

        $output = '';

        foreach ($root->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        return trim($output) === '' ? null : $output;
    }

    private static function cleanChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $child) {
            if ($child instanceof DOMText) {
                continue;
            }

            if (! $child instanceof DOMElement) {
                $parent->removeChild($child);

                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROPPED_TAGS, true)) {
                $parent->removeChild($child);

                continue;
            }

            self::cleanChildren($child);

            if (! array_key_exists($tag, self::ALLOWED_TAGS)) {
                while ($child->firstChild !== null) {
                    $parent->insertBefore($child->firstChild, $child);
                }

                $parent->removeChild($child);

                continue;
            }

            foreach (iterator_to_array($child->attributes) as $attribute) {
                if (! in_array($attribute->name, self::ALLOWED_TAGS[$tag], true)) {
                    $child->removeAttribute($attribute->name);
                }
            }

            if ($tag === 'a' && ! self::isSafeUrl($child->getAttribute('href'))) {
                $child->removeAttribute('href');
            }
        }
    }

    private static function isSafeUrl(string $url): bool
    {
        return preg_match('~^(https?://|mailto:|tel:|/(?!/)|#)~i', trim($url)) === 1;
    }
}
