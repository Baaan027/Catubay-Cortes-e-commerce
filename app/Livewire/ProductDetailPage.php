<?php

namespace App\Livewire;
use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\Title;
use App\Helpers\CartManagement;
use App\Livewire\Partials\Navbar;

#[Title('Product Detail - LAZZAAPPEE')]
class ProductDetailPage extends Component
{
    public $slug;
    public $quantity = 1;

    public function increaseQty()
    {
        $this->quantity++;
    }

    public function decreaseQty()
    {
        if($this->quantity > 1){
            $this->quantity--;
        }
    }
     public function addToCart($product_id)
    {
        $total_count = CartManagement::addItemToCart($product_id);

        $this->dispatch('update-cart-count', total_count: $total_count)->to(Navbar::class);

        session()->flash('success', 'Product added to cart successfully!');
    }
    public function mount($slug)
    {
        $this->slug = $slug;
    }
    public function render()
    {
        return view('livewire.product-detail-page',[
        'product'=>Product::where('slug',$this->slug)->firstOrFail(),
        ]);
    }
}
