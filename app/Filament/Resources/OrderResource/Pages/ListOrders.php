<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\OrderResource\Widgets\OrderStats;
use Filament\Resources\Components\Tab;






class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [OrderStats::class ] ;
    }

    public function getTabs(): array
    {
    return[
        null=> Tab::make('All'),
        'new' => Tab::make()->query(fn ($query) => $query->where('status', 'new')),
        'processing' => Tab::make()->query(fn ($query) => $query->where('status', 'processing')),
        'completed' => Tab::make()->query(fn ($query) => $query->where('status', 'completed')),
        'delivered' => Tab::make()->query(fn ($query) => $query->where('status', 'delivered')),
        'canceled' => Tab::make()->query(fn ($query) => $query->where('status', 'canceled')),

    ];
    }
}

