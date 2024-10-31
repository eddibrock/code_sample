<?php

namespace Onlogsystem\Cargo;

use CUtil;
use Exception;

trait CargoTrait
{
    /**
     * @throws Exception
     */
    public static function translit($str): string
    {
        $lang = CargoLanguage::getLangName(CargoLanguage::ru)->name;
        return CUtil::translit($str, $lang, ['replace_space' => '-', "replace_other" => "-"]);
    }

    static function uni_declension($num, $str): string
    {
        $exp = explode(',', $str);
        $num = (($num < 0) ? $num - $num * 2 : $num) % 100;
        $dig = ($num > 20) ? $num % 10 : $num;
        return trim((($dig == 1) ? $exp[0] : (($dig > 4 || $dig < 1) ? $exp[2] : $exp[1])));
    }
}