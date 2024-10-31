<?php

namespace Onlogsystem\Cargo;

enum CargoLanguage
{
    case ru;    // Русский
    case eng;    // Английский
//    case German;     // Немецкий
//    case Spanish;    // Испанский
//    case French;     // Французский
//    case Italian;    // Итальянский
//    case Japan;      // Японский
//    case Dutch;      // Голландский
//    case Polish;     // Польский
//    case Portuguese; // Португальский
//    case Chines;     // Китайский

    /**
     * Получение языка по идентификатору
     *
     * @param string $id
     *
     * @return int
     */
    public static function getLangId(CargoLanguage $lang): int
    {
        return match ($lang) {
            self::ru => 1,
            self::eng => 2,
            default => throw new \Exception('Unexpected match value'),
//            "3" => self::German,
//            "4" => self::French,
//            "5" => self::Italian,
//            "6" => self::Japan,
//            "7" => self::Spanish,
//            "8" => self::Dutch,
//            "9" => self::Polish,
//            "10" => self::Portuguese,
//            "11" => self::Chines,
        };
    }

    public static function getLangName(CargoLanguage $lang): CargoLanguage
    {
        return match ($lang) {
            self::ru => self::ru,
            self::eng => self::eng,
            default => throw new \Exception('Unexpected match value'),
        };
    }
}
