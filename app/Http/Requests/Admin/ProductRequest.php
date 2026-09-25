<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesSeoFields;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProductRequest extends FormRequest
{
    use ValidatesSeoFields;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => filled($this->input('slug')) ? Str::slug((string) $this->input('slug')) : null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique(Product::class)->ignore($product)],
            'sku' => ['required', 'string', 'max:64', 'alpha_dash:ascii', Rule::unique(Product::class)->ignore($product)],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['integer', 'distinct', Rule::exists(Category::class, 'id')],
            'description' => ['required', 'string', 'max:65000'],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'minimum_qty' => ['required', 'integer', 'min:1', 'max:1000000'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['required', 'boolean'],
            'is_featured' => ['required', 'boolean'],
            'images' => ['nullable', 'array'],
            'images.*.alt' => ['nullable', 'string', 'max:255'],
            'images.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'images.*.remove' => ['boolean'],
            'primary_image' => ['nullable', 'integer'],
            'new_images' => ['nullable', 'array', 'max:10'],
            'new_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'canonical_url' => ['nullable', 'url:http,https', 'max:255'],
            ...$this->seoRules(),
        ];
    }

    /**
     * Rich text that contains only empty formatting counts as missing.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->has('description')) {
                    return;
                }

                if (trim(html_entity_decode(strip_tags((string) $this->input('description')))) === '') {
                    $validator->errors()->add('description', __('validation.required', ['attribute' => 'description']));
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'sku' => 'SKU',
            'minimum_qty' => 'minimum quantity',
            'new_images.*' => 'image',
            'images.*.alt' => 'alt text',
            'canonical_url' => 'canonical URL',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'categories.required' => 'Choose at least one category.',
            'new_images.max' => 'Upload up to 10 images at a time.',
        ];
    }
}
