<?php

namespace App\Enums;

enum DeliveryCountry: string
{
    case Nigeria = 'NG';
    case Kenya = 'KE';
    case Tanzania = 'TZ';
    case Uganda = 'UG';
    case IvoryCoast = 'CI';
    case Cameroon = 'CM';
    case Rwanda = 'RW';

    public function label(): string
    {
        return match ($this) {
            self::Nigeria => 'Nigeria',
            self::Kenya => 'Kenya',
            self::Tanzania => 'Tanzania',
            self::Uganda => 'Uganda',
            self::IvoryCoast => "Côte d'Ivoire",
            self::Cameroon => 'Cameroon',
            self::Rwanda => 'Rwanda',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return array_column(
            array_map(fn (self $case) => ['value' => $case->value, 'label' => $case->label()], self::cases()),
            'label',
            'value',
        );
    }
}
