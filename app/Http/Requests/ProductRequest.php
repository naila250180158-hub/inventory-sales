<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Dipakai untuk tambah DAN ubah produk.
 * Otorisasi dijaga di route (can:manage-master), jadi authorize() cukup true.
 */
class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $product = $this->route('product'); // null saat tambah, objek Product saat ubah

        $rules = [
            'sku' => [
                'required', 'string', 'max:50',
                Rule::unique('products', 'sku')->ignore($product?->id),
            ],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'purchase_price' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'selling_price' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'minimum_stock' => ['required', 'integer', 'min:0', 'max:1000000'],
        ];

        // Stok awal hanya saat produk baru. Setelah itu stok berubah lewat transaksi.
        if (! $product) {
            $rules['stock'] = ['required', 'integer', 'min:0', 'max:1000000'];
        }

        return $rules;
    }
}
