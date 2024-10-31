<?php

namespace Onlogsystem\Cargo;

enum CargoNames
{
    case контейнерный;
    case контейнерного;
    case контейнерных;
    case сборный;
    case сборного;
    case сборных;
    case авиа;

    public static function getName($type): CargoNames|string
    {
        return match ($type) {
            'container' => self::контейнерный,
            'konteynernyy-gruz' => 'Контейнерный груз',
            'Контейнерный груз' => 'konteynernyy-gruz',
            'konteynernyy-gruz_ih' => 'контейнерных грузов',
            'container_ogo' => self::контейнерного,
            'container_ih' => self::контейнерных,
            'groupage' => self::сборный,
            'sbornyy-gruz' => 'Сборный груз',
            'Сборный груз' => 'sbornyy-gruz',
            'sbornyy-gruz_ih' => 'сборных грузов',
            'groupage_ogo' => self::сборного,
            'groupage_ih' => self::сборных,
            'air' => self::авиа,
            'avia-gruz' => 'Авиа груз',
            'Авиа груз' => 'avia-gruz',
            'avia-gruz_ih' => 'авиа грузов',
            'air_ogo' => self::авиа,
            'air_ih' => self::авиа,
            default => '',
        };
    }

    public static function getType($type): string
    {
        return match ($type) {
            'konteynernyy-gruz' => 'CONTAINER_TYPE',
            'sbornyy-gruz' => 'GROUPAGE_TYPE',
            'avia-gruz' => 'AIR_TYPE',
            default => ''
        };
    }

    public static function getAlias($type): string
    {
        return match ($type) {
            'CONTAINER_TYPE' => 'konteynernyy-gruz',
            'GROUPAGE_TYPE' => 'sbornyy-gruz',
            'CARGO_TYPE' => 'sbornyy-gruz',
            'AIR_TYPE' => 'avia-gruz',
            default => ''
        };
    }
}
