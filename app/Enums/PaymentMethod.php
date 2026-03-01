<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case BankTransfer = 'bank_transfer';
    case MobileMoneyMpesa = 'mobile_money_mpesa';
    case MobileMoneyMtn = 'mobile_money_mtn';
    case MobileMoneyAirtel = 'mobile_money_airtel';
    case Cash = 'cash';
    case CryptoUsdt = 'crypto_usdt';
    case WesternUnion = 'western_union';
    case MoneyGram = 'moneygram';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::BankTransfer => 'Bank Transfer',
            self::MobileMoneyMpesa => 'Mobile Money (M-Pesa)',
            self::MobileMoneyMtn => 'Mobile Money (MTN)',
            self::MobileMoneyAirtel => 'Mobile Money (Airtel)',
            self::Cash => 'Cash',
            self::CryptoUsdt => 'Crypto (USDT)',
            self::WesternUnion => 'Western Union',
            self::MoneyGram => 'MoneyGram',
            self::Other => 'Other',
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
