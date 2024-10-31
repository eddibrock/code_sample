<?php

namespace Onlogsystem\Page;

use Onlogsystem\Cargo\Cargo;

class PageFactory
{
    static function createPage(PageUrl $url): Page
    {
        $match = '';
        $obj = null;
        // Если есть только 1 уровень в url
        if (empty($url->getLevelTwo())) {
            $obj = new PageCountry($url);
            if ($obj->checkType()) {
                $match = 'country';
            } else {
                $obj = new PageCountryToCountry($url);
                if ($obj->checkType()) {
                    $match = 'country_country';
                } else {
                    $obj = new PageCityToCity($url);
                    if ($obj->checkType()) {
                        $match = 'city_city';
                    } else {
                        $match = 'empty';
                    }
                }
            }
        } elseif (!empty($url->getLevelOne())) {
            $obj = new PageCity($url);
            if ($obj->checkType()) {
                $match = 'city';
            } else {
                $match = 'empty';
            }
        }
        d($match);
        if ($match == 'empty') {
            $obj = new PageCountryToCity($url);
            if ($obj->checkType()) {
                $match = 'country_city';
            } else {
                $obj = new PageCityToCountry($url);
                if ($obj->checkType()) {
                    $match = 'city_country';
                }
            }
        }
        if ($match == 'empty') {
            return new PageEmpty($url);
        } else {
            return $obj;
        }
    }
}