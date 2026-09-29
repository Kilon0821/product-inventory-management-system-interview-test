<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // 自动获取当前路由中的 product 对象或 ID
        $productId = $this->route('product')?->id ?? $this->route('product');

        return [
            'category_id' => 'sometimes|required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'name'        => 'sometimes|required|string|max:255',
            'sku'         => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($productId),
            ],
            'price'       => 'sometimes|required|numeric|min:0',
            'description' => 'nullable|string',
        ];
    }
}
