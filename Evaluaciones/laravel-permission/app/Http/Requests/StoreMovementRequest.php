<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:entry,exit',
            'quantity' => 'required|integer|min:1',
            'warehouse_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string',
            'repartidor_id' => 'nullable|exists:users,id',
        ];
    }
}
