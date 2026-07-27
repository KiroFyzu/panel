<?php

namespace Pterodactyl\Http\Requests\Admin;

use Pterodactyl\Http\Requests\Admin\AdminFormRequest;

class PricePackageFormRequest extends AdminFormRequest
{
    public function rules(): array
    {
        $packageId = $this->route('package')?->id;

        return [
            'name' => 'required|string|max:191',
            'slug' => 'required|string|max:191|unique:price_packages,slug' . ($packageId ? ',' . $packageId : ''),
            'description' => 'nullable|string',
            'price' => 'required|integer|min:0',
            'old_price' => 'nullable|integer|min:0',
            'ram' => 'required|integer|min:1',
            'cpu' => 'required|integer|min:0|max:1000',
            'disk' => 'required|integer|min:1',
            'sort' => 'nullable|integer',
            'is_active' => 'sometimes|boolean',
            'nodes' => 'array',
            'nodes.*' => 'integer|exists:nodes,id',
            'eggs' => 'array',
            'eggs.*' => 'integer|exists:eggs,id',
        ];
    }
}