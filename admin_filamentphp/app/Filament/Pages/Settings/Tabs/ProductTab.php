<?php

namespace App\Filament\Pages\Settings\Tabs;

use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Group;

class ProductTab
{
    public static function make(): Tab
    {
        return Tab::make(trans('admin.product.label'))
            ->label(trans('admin.product.label'))
            ->icon('heroicon-o-shopping-bag')
            ->schema([
                Section::make(trans('admin.shop.settings.cart_behavior'))
                    ->schema([
                        Radio::make('products.add_to_cart_behavior')
                            ->label(trans('admin.shop.settings.after_add_to_cart'))
                            ->options([
                                'ajax' => trans('admin.shop.settings.stay_on_page'),
                                'redirect' => trans('admin.shop.settings.redirect_to_cart'),
                            ])
                            ->inline(),
                    ]),

                Section::make(trans('admin.shop.settings.reviews'))
                    ->schema([
                        Toggle::make('products.enable_reviews')
                            ->label(trans('admin.shop.settings.enable_product_reviews'))
                            ->reactive(),
                        Group::make([
                            Toggle::make('products.guest_reviews_allowed')
                                ->label(trans('admin.shop.settings.allow_guest_reviews')),
                            Toggle::make('products.review_stars_required')
                                ->label(trans('admin.shop.settings.star_rating_required')),
                        ])->visible(fn($get) => $get('products.enable_reviews')),
                    ]),
            ]);
    }
}
