<?php

namespace App\Forms\Components;

use Filament\Forms;
use Illuminate\Database\Eloquent\Model;
use Squire\Models\Country;

class AddressForm extends Forms\Components\Component
{
    use \Filament\Forms\Components\Concerns\HasName;
    use \Filament\Forms\Components\Concerns\HasLabel {
        \Filament\Forms\Components\Concerns\HasLabel::getLabel insteadof \Filament\Forms\Components\Concerns\HasName;
        \Filament\Forms\Components\Concerns\HasLabel::getLabel as getLabelFromTrait;
    }

    protected string $view = 'filament-forms::components.group';

    /** @var string|callable|null */
    public $relationship = null;

    protected bool $isMultiple = false;
    protected ?array $visibleFields = null;
    protected bool $isRegionalOnly = false;
    protected array $fieldMap = [
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'company' => 'company',
        'phone' => 'phone',
        'email' => 'email',
        'country_code' => 'country_code',
        'state_id' => 'state_id',
        'city_id' => 'city_id',
        'ward_id' => 'ward_id',
        'address_detail' => 'address_detail',
        'postal_code' => 'postal_code',
        // Plural variants
        'countries' => 'countries',
        'states' => 'states',
        'wards' => 'wards',
        'postcodes' => 'postcodes',
    ];

    public function __construct(string $name)
    {
        $this->name($name);
        $this->statePath($name);
    }

    public static function make(string $name): static
    {
        $static = app(static::class, ['name' => $name]);
        $static->configure();

        return $static;
    }

    public function fieldMap(array $map): static
    {
        $this->fieldMap = array_merge($this->fieldMap, $map);

        return $this;
    }

    public function relationship(string | callable $relationship): static
    {
        $this->relationship = $relationship;

        return $this;
    }

    public function multiple(bool $condition = true): static
    {
        $this->isMultiple = $condition;

        return $this;
    }

    public function onlyFields(array $fields): static
    {
        $this->visibleFields = $fields;

        return $this;
    }

    public function regional(bool $condition = true): static
    {
        $this->isRegionalOnly = $condition;

        return $this;
    }

    public function saveRelationships(): void
    {
        if ($this->isMultiple) return; // Handle multiple in the resource, not here for now

        $state = $this->getState();
        $record = $this->getRecord();
        $relation = $this->getRelationship();

        if (! $record || ! method_exists($record, $relation)) {
            return;
        }

        $relationship = $record->{$relation}();

        if ($relationship === null) {
            return;
        }

        // Tự động bốc 'type' từ query của quan hệ (vd: where type = 'shipping')
        $type = null;
        $wheres = $relationship->getQuery()->getQuery()->wheres;

        foreach ($wheres as $where) {
            if (isset($where['column']) && $where['column'] === 'type') {
                $type = $where['value'];
                break;
            }
        }

        if ($type) {
            $state['type'] = $type;
        }

        // Đảm bảo không lưu nếu state trống
        if (empty(array_filter($state))) {
            return;
        }

        if ($address = $relationship->first()) {
            $address->update($state);
        } else {
            $relationship->updateOrCreate(['type' => $type], $state);
        }

        $record?->touch();
    }

    public function getChildComponents(): array
    {
        $components = [];

        if (!$this->isRegionalOnly) {
            $components[] = Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\TextInput::make($this->fieldMap['first_name'])
                        ->label(trans('admin.first_name'))
                        ->required()
                        ->visible($this->isFieldVisible('first_name')),
                    Forms\Components\TextInput::make($this->fieldMap['last_name'])
                        ->label(trans('admin.last_name'))
                        ->required()
                        ->visible($this->isFieldVisible('last_name')),
                ]);

            $components[] = Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\TextInput::make($this->fieldMap['company'])
                        ->label(trans('admin.company'))
                        ->visible($this->isFieldVisible('company')),
                    Forms\Components\TextInput::make($this->fieldMap['phone'])
                        ->label(trans('admin.phone'))
                        ->tel()
                        ->required()
                        ->visible($this->isFieldVisible('phone')),
                ]);

            $components[] = Forms\Components\TextInput::make($this->fieldMap['email'])
                ->label(trans('admin.email'))
                ->email()
                ->required()
                ->visible($this->isFieldVisible('email'));
        }

        // Country Selection
        $countryKey = $this->isMultiple ? $this->fieldMap['countries'] : $this->fieldMap['country_code'];
        $countryField = Forms\Components\Select::make($countryKey)
            ->label(trans('admin.country'))
            ->searchable()
            ->options(fn() => app(\App\Ecommerce\Location\Services\Location\LocationManager::class)->getCountries())
            ->required()
            ->multiple($this->isMultiple)
            ->live()
            ->visible($this->isFieldVisible('country_code') || $this->isFieldVisible('countries'));

        $components[] = $countryField;

        // DYNAMIC API REGIONAL RENDERING (Vietnam)
        $components[] = Forms\Components\Group::make([
            Forms\Components\Grid::make(3)
                ->schema([
                    Forms\Components\Select::make($this->isMultiple ? $this->fieldMap['states'] : $this->fieldMap['state_id'])
                        ->label(trans('admin.state'))
                        ->options(function ($get) use ($countryKey) {
                            $country = $get($countryKey);
                            $countryCode = is_array($country) ? ($country[0] ?? 'VN') : ($country ?? 'VN');
                            return app(\App\Ecommerce\Location\Services\Location\LocationManager::class)->getStates($countryCode);
                        })
                        ->multiple($this->isMultiple)
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn(Forms\Set $set) => $set($this->isMultiple ? $this->fieldMap['wards'] : $this->fieldMap['city_id'], null))
                        ->visible($this->isFieldVisible('state_id') || $this->isFieldVisible('states')),

                    Forms\Components\Select::make($this->isMultiple ? $this->fieldMap['wards'] : $this->fieldMap['city_id']) // For multiple, we use 'wards' as 'city' filter
                        ->label($this->isMultiple ? trans('admin.ward') : trans('admin.city'))
                        ->options(function ($get) use ($countryKey) {
                            $country = $get($countryKey);
                            $countryCode = is_array($country) ? ($country[0] ?? 'VN') : ($country ?? 'VN');
                            
                            $state = $get($this->isMultiple ? $this->fieldMap['states'] : $this->fieldMap['state_id']);
                            $stateId = is_array($state) ? ($state[0] ?? null) : $state;

                            if (!$stateId) return [];
                            return app(\App\Ecommerce\Location\Services\Location\LocationManager::class)->getCities($countryCode, $stateId);
                        })
                        ->multiple($this->isMultiple)
                        ->searchable()
                        ->required()
                        ->live()
                        ->visible($this->isFieldVisible('city_id') || $this->isFieldVisible('wards')),

                    Forms\Components\Select::make($this->fieldMap['ward_id'])
                        ->label(trans('admin.ward'))
                        ->options(function ($get) use ($countryKey) {
                            $country = $get($countryKey);
                            $state = $get($this->fieldMap['state_id']);
                            $city = $get($this->fieldMap['city_id']);
                            if (!$city) return [];
                            return app(\App\Ecommerce\Location\Services\Location\LocationManager::class)->getWards($country ?? 'VN', $state, $city);
                        })
                        ->searchable()
                        ->required()
                        ->visible(!$this->isMultiple && $this->isFieldVisible('ward_id')),
                ]),
        ])
            ->visible(function($get) use ($countryKey) {
                $country = $get($countryKey);
                $countryCode = is_array($country) ? ($country[0] ?? '') : ($country ?? '');
                return strtoupper($countryCode) === 'VN';
            });

        // INTERNATIONAL FIELDS
        $components[] = Forms\Components\Group::make([
            Forms\Components\Grid::make(3)
                ->schema([
                    Forms\Components\TextInput::make($this->isMultiple ? $this->fieldMap['states'] : $this->fieldMap['state_id'])
                        ->label(trans('admin.state'))
                        ->visible($this->isFieldVisible('state_id') || $this->isFieldVisible('states')),
                    Forms\Components\TextInput::make($this->isMultiple ? $this->fieldMap['wards'] : $this->fieldMap['city_id'])
                        ->label($this->isMultiple ? trans('admin.ward') : trans('admin.city'))
                            ->visible($this->isFieldVisible('city_id') || $this->isFieldVisible('wards')),
                    Forms\Components\TextInput::make($this->isMultiple ? $this->fieldMap['postcodes'] : $this->fieldMap['postal_code'])
                        ->label(trans('admin.zip'))
                        ->visible($this->isFieldVisible('postal_code') || $this->isFieldVisible('postcodes')),
                ]),
        ])
            ->visible(function($get) use ($countryKey) {
                $country = $get($countryKey);
                $countryCode = is_array($country) ? ($country[0] ?? '') : ($country ?? '');
                return strtoupper($countryCode) !== 'VN' && $countryCode !== '';
            });

        if (!$this->isRegionalOnly) {
            $components[] = Forms\Components\TextInput::make($this->fieldMap['address_detail'])
                ->label(trans('admin.street'))
                ->required()
                ->visible($this->isFieldVisible('address_detail'));
        }

        return $components;
    }

    protected function isFieldVisible(string $field): bool
    {
        if ($this->visibleFields === null) return true;
        return in_array($field, $this->visibleFields);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(function (AddressForm $component, ?Model $record) {
            if ($this->isMultiple) return; // Skip hydrated for multiple mode for now

            $relation = $this->getRelationship();
            $address = $record?->getRelationValue($relation);

            $defaultState = [
                'country_code' => null,
                'phone' => null,
                'email' => null,
                'address_detail' => null,
                'state_id' => null,
                'ward_id' => null,
                'city_id' => null,
                'postal_code' => null,
                'first_name' => null,
                'last_name' => null,
            ];

            $component->state($address ? array_merge($defaultState, $address->toArray()) : $defaultState);
        });
    }

    public function getRelationship(): string
    {
        return $this->evaluate($this->relationship) ?? $this->getName();
    }
}
