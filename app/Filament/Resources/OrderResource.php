<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ActionGroup;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make('Order Information')
                            ->columns(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label('Customer')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('payment_method')
                                    ->options([
                                        'stripe' => 'Stripe',
                                        'cod' => 'Cash on Delivery',
                                    ])
                                    ->required(),

                                Select::make('payment_status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'paid' => 'Paid',
                                        'failed' => 'Failed',
                                    ])
                                    ->default('pending')
                                    ->required(),

                                ToggleButtons::make('status')
                                    ->options([
                                        'new' => 'New',
                                        'processing' => 'Processing',
                                        'shipped' => 'Shipped',
                                        'delivered' => 'Delivered',
                                        'cancelled' => 'Cancelled',
                                    ])
                                    ->inline()
                                    ->default('new')
                                    ->required()
                                    ->colors([
                                        'new' => 'info',
                                        'processing' => 'warning',
                                        'shipped' => 'success',
                                        'delivered' => 'success',
                                        'cancelled' => 'danger',
                                    ])
                                    ->icons([
                                        'new' => 'heroicon-s-sparkles',
                                        'processing' => 'heroicon-s-arrow-path',
                                        'shipped' => 'heroicon-s-truck',
                                        'delivered' => 'heroicon-s-check',
                                        'cancelled' => 'heroicon-s-x-circle',
                                    ]),

                                Select::make('currency')
                                    ->options([
                                        'INR' => 'INR',
                                        'USD' => 'USD',
                                        'EUR' => 'EUR',
                                        'GBP' => 'GBP',
                                    ])
                                    ->default('INR')
                                    ->required(),

                                Select::make('shipping_method')
                                    ->options([
                                        'FedEx' => 'FedEx',
                                        'UPS' => 'UPS',
                                        'DHL' => 'DHL',
                                        'USPS' => 'USPS',
                                    ]),

                                Textarea::make('notes')
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Order Items')
                            ->schema([
                                Repeater::make('items')
                                    ->relationship()
                                    ->schema([
                                        Select::make('product_id')
                                            ->relationship('product', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->distinct()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                $product = Product::find($state);
                                                $set('unit_amount', $product?->price ?? 0);
                                                $set('total_amount', $product?->price ?? 0);
                                            }),

                                        TextInput::make('quantity')
                                            ->numeric()
                                            ->default(1)
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                $set('total_amount', $state * $get('unit_amount'));
                                            }),

                                        TextInput::make('unit_amount')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated(),

                                        TextInput::make('total_amount')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated(),
                                    ])
                                    ->columns(4),

                                Placeholder::make('grand_total')
                                    ->label('Grand Total')
                                    ->content(fn ($get) => number_format(array_sum(array_column($get('items') ?? [], 'total_amount')), 2) . ' ' . ($get('currency') ?? 'INR')),

                                TextInput::make('grand_total')
                                    ->default(0)
                                    ->dehydrated(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Customer')->sortable()->searchable(),
                TextColumn::make('grand_total')->money('INR')->sortable(),
                TextColumn::make('payment_method')->sortable()->searchable(),
                TextColumn::make('payment_status')->sortable()->searchable(),
                SelectColumn::make('status')
                    ->options([
                        'new' => 'New',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ])
                    ->sortable()
                    ->searchable(),
                TextColumn::make('currency'),
                TextColumn::make('shipping_method'),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                ]),
            ])
            ->bulkActions([
                DeleteBulkAction::make(), // ✅ Fixed bulk action
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() > 10 ? 'danger' : 'success';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
