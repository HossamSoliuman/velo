<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnquiryRequest extends FormRequest
{
    /**
     * A hidden field that people never see. Bots that fill in every field fill this one too.
     */
    public const HONEYPOT_FIELD = 'fax';

    private Product|false|null $product = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $product = $this->product();

        return [
            'product_id' => ['nullable', 'integer', Rule::exists('products', 'id')->where('is_active', true)],
            'name' => ['required', 'string', 'max:100'],
            'company' => ['nullable', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'mobile' => ['required', 'string', 'max:20', 'regex:/^(?=(?:\D*\d){7,15}\D*$)\+?[0-9(][0-9\s\-().]*[0-9]$/'],
            'quantity' => [Rule::requiredIf($product !== null), 'nullable', 'integer', 'min:'.($product?->minimum_qty ?? 1), 'max:10000000'],
            'message' => ['nullable', 'string', 'max:2000'],
            self::HONEYPOT_FIELD => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.exists' => 'This product is no longer available. Please remove it and describe what you need in the message.',
            'mobile.regex' => 'Please enter a valid mobile number.',
            'quantity.required' => 'Please enter the quantity you need.',
            'quantity.min' => $this->product() !== null
                ? 'The minimum order for this product is :min.'
                : 'The quantity must be at least :min.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'mobile' => 'mobile number',
        ];
    }

    /**
     * The active product the enquiry is about, if any.
     */
    public function product(): ?Product
    {
        if ($this->product === null) {
            $this->product = $this->filled('product_id')
                ? Product::query()->active()->find($this->integer('product_id')) ?? false
                : false;
        }

        return $this->product ?: null;
    }

    /**
     * Whether the submission filled in the hidden honeypot field, so it came from a bot.
     */
    public function isSpam(): bool
    {
        return $this->filled(self::HONEYPOT_FIELD);
    }
}
