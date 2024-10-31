<?php

namespace Onlogsystem\Cargo;

class CargoFactory
{
    static function createCargo($data): Cargo
    {
        $dataDecoded = json_decode($data['DATA'], true);
        if (array_key_exists('containerParameters', $dataDecoded['data']['cargoParameters'])) {
            $cargo = new \Onlogsystem\Cargo\CargoContainer($dataDecoded);
        } else {
            if (str_contains($data['CODE'], 'Air')) {
                $cargo = new \Onlogsystem\Cargo\CargoAir($dataDecoded);
            } else {
                $cargo = new \Onlogsystem\Cargo\CargoGroupage($dataDecoded);
            }
        }

        return $cargo;
    }
}