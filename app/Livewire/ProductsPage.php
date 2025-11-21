<?php

namespace App\Livewire;
use App\Models\Product;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Brand;
use App\Models\Category;

#[Title('Products - LAZZAAPPEE')]
class ProductsPage extends Component
{
    use WithPagination;
    public function render()
    {
        $productQuery = Product::query()->where('is_active', 1);
        return view('livewire.products-page',
        ['products' => $productQuery->paginate(6),
        'brands'=> Brand::where('is_active', 1)->get(),
        'categories'=> Category::where('is_active', 1)->get(['id', 'name', 'slug']),
        ]);

    }
}
