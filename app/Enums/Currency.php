<?php

namespace App\Enums;

enum Currency: string
{
    case NGN = 'NGN';
    case KES = 'KES';
    case TZS = 'TZS';
    case UGX = 'UGX';
    case XOF = 'XOF';
    case XAF = 'XAF';
    case RWF = 'RWF';

    public function label(): string
    {
        return match ($this) {
            self::NGN => 'Nigerian Naira (NGN)',
            self::KES => 'Kenyan Shilling (KES)',
            self::TZS => 'Tanzanian Shilling (TZS)',
            self::UGX => 'Ugandan Shilling (UGX)',
            self::XOF => 'West African CFA franc (XOF)',
            self::XAF => 'Central African CFA franc (XAF)',
            self::RWF => 'Rwandan Franc (RWF)',
        };
    }

    public function community(): string
    {
        return match ($this) {
            self::NGN => '🇳🇬 Nigerians in Rwanda',
            self::KES => '🇰🇪 Kenyans in Rwanda',
            self::TZS => '🇹🇿 Tanzanians in Rwanda',
            self::UGX => '🇺🇬 Ugandans in Rwanda',
            self::XOF => '🇧🇯 Beninois in Rwanda',
            self::XAF => '🇨🇲 Cameroonians in Rwanda',
            self::RWF => 'Rwanda',
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

    /** @return list<string> */
    public static function diasporaCurrencies(): array
    {
        return [self::NGN->value, self::KES->value, self::TZS->value, self::UGX->value, self::XOF->value, self::XAF->value];
    }
}
