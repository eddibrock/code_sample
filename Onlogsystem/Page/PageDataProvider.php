<?php

namespace Onlogsystem\Page;

use Onlogsystem\Cargo\CargoRestHelper;
use Fsd\Onlog\Integration\Table;

class PageDataProvider
{
    private IblockPageHelper $obIblockHelper;
    private CargoRestHelper $restHelper;
    private static string $dir = 'seo';

    public function __construct()
    {
        $this->obIblockHelper = new IblockPageHelper();
        $this->restHelper = new CargoRestHelper();
    }

    public function getPopulation($arLocIds): array
    {
        return $this->restHelper->getPopulation($arLocIds);
    }

    public function getCargoTypeName($cargoTypeId)
    {
        return $this->obIblockHelper->getCargoTypeName($cargoTypeId);
    }

    public function getAllCargoTypes(): string
    {
        $enum = $this->obIblockHelper->getIblockOb()->getEnumData('CARGO_TYPE');
        $res = [];
        foreach ($enum as $item) {
            $res[] = strtolower($item['VALUE']);
        }

        return implode(', ', $res);
    }

    public function getElements($filter): array
    {
        $collection = $this->obIblockHelper->getCollection($filter);
        $result = [];
        foreach ($collection as $item) {
            $result[$item->getId()] = [
                'NAME' => $item->getName(),
                'ID' => $item->getId(),
                'URL_LEVEL_ONE' => $item->get('URL_LEVEL_ONE')->getValue(),
                'URL_LEVEL_TWO' => $item->get('URL_LEVEL_TWO')->getValue(),
                'CODE' => $item->getRouteCode()->getValue(),
                'CARGO_TYPE_ID' => $item->getCargoType()->getItem()->getId(),
                'CARGO_TYPE_NAME' => $this->getCargoTypeName($item->getCargoType()->getItem()->getId()),
                'COUNTRY_FROM' => $item->getCountryFrom()->getValue(),
                'COUNTRY_FROM_ORIGIN' => $item->getCountryFromOrigin()->getValue(),
                'COUNTRY_TO' => $item->getCountryTo()->getValue(),
                'COUNTRY_TO_ORIGIN' => $item->getCountryToOrigin()->getValue(),
                'CITY_FROM' => $item->getCityFrom()->getValue(),
                'CITY_FROM_ORIGIN' => $item->getCityFromOrigin()->getValue(),
                'CITY_TO' => $item->getCityTo()->getValue(),
                'CITY_TO_ORIGIN' => $item->getCityToOrigin()->getValue(),
                'LOCAL_FROM' => $item->getLocalFrom()->getValue(),
                'LOCAL_TO' => $item->getLocalTo()->getValue(),
                'LOCAL_COUNTRY_FROM' => $item->getLocalCountryFrom()->getValue(),
                'LOCAL_COUNTRY_TO' => $item->getLocalCountryTo()->getValue(),
            ];
        }

        return $result;
    }

    public function getCargoElements($filter): array
    {
        $data = $this->getElements($filter);
        $arCodes = array_keys(array_column($data, 'ID', 'CODE'));
        $params = [
            'filter' => ['CODE' => $arCodes],
            'select' => ['ID', 'DATA', "CODE"],
        ];
        $obRouteTable = Table\OnlogCalculationRouteTable::getList($params);
        $arRouteData = [];
        while ($ar = $obRouteTable->Fetch()) {
            $cargo = \Onlogsystem\Cargo\CargoFactory::createCargo($ar);
            $arRouteData[$cargo->getCode()] = [
                'cargo' => $cargo,
                'data' => $ar['DATA'],
                'transit_terminal' => $cargo->getTransitTerminal('topcheap'),
                'transit_terminal_location_id' => $cargo->getTransitTerminalLocationId('topcheap'),
                'dimension' => $cargo->getContainerDimension(),
                'days' => $cargo->getFastestTopData(),
                'money' => $cargo->getCheapestTopData(),
                'cargo_name' => $cargo->getCargoName(),
            ];
        }

        foreach ($data as &$item) {
            $item['CARGO_DATA'] = $arRouteData[$item['CODE']];
        }

        return $data;
    }

    public function getCargoElementsGroup($query)
    {
        $data = \Onlogsystem\Iblock\QueryHelper::decompose($query, false, true);
        $ids = [];
        foreach ($data as &$datum) {
            $ids = array_merge($ids, explode(',', $datum['IDS']));
            $datum['IDS'] = explode(',', $datum['IDS']);
        }
        $filter = [
            'filter' => [
                'ID' => $ids
            ],
        ];
        $cargoData = $this->getCargoElements($filter);
        unset($datum);
        foreach ($data as &$datum) {
            if (count($datum['IDS']) > 1) {
                $arDays = [];
                $arMoney = [];
                foreach ($datum['IDS'] as $ID) {
                    $arDays[] = $cargoData[$ID]['CARGO_DATA']['days'];
                    $arMoney[] = $cargoData[$ID]['CARGO_DATA']['money'];
                }
                $currentCargoData = $cargoData[$datum['IDS'][0]];
                $currentCargoData['CARGO_TYPE_NAME'] = $datum['IBLOCK_ELEMENTS_ELEMENT_ROUTES_CARGO_TYPE_ITEM_VALUE'];
                $currentCargoData['CARGO_DATA']['days'] = min($arDays);
                $currentCargoData['CARGO_DATA']['money'] = min($arMoney);
                $datum['DATA'] = $currentCargoData;

            } else {
                $datum['DATA'] = $cargoData[$datum['IDS'][0]];
            }
        }
        foreach ($data as &$item) {
            if ($item['DATA']['CARGO_DATA']['transit_terminal']) {
                $item['DATA']['LINK'] = sprintf('<a href="/%s/%s/%s/">%s</a> - <a href="/%s/%s/%s/">%s</a> через %s', self::$dir,
                    $item['DATA']['COUNTRY_FROM'],
                    $item['DATA']['CITY_FROM'],
                    $item['DATA']['CITY_FROM_ORIGIN'],
                    self::$dir,
                    $item['DATA']['COUNTRY_TO'],
                    $item['DATA']['CITY_TO'],
                    $item['DATA']['CITY_TO_ORIGIN'],
                    $item['DATA']['CARGO_DATA']['transit_terminal']
                );
            } else {
                $item['DATA']['LINK'] = sprintf('<a href="/%s/%s/%s/">%s</a> - <a href="/%s/%s/%s/">%s</a>', self::$dir,
                    $item['DATA']['COUNTRY_FROM'],
                    $item['DATA']['CITY_FROM'],
                    $item['DATA']['CITY_FROM_ORIGIN'],
                    self::$dir,
                    $item['DATA']['COUNTRY_TO'],
                    $item['DATA']['CITY_TO'],
                    $item['DATA']['CITY_TO_ORIGIN']
                );
            }
        }

        return $data;
    }

    public function getCitiesByCountry($country): array
    {
        // Получить маршруты страны независимо от направления (ИЗ или В)
        $query = $this->getIblockHelper()->getQuery();

        $query->setSelect([
            new \Bitrix\Main\Entity\ExpressionField(
                'COUNT',
                "COUNT(*)",
            ),
            'URL_LEVEL_TWO.VALUE',
            new \Bitrix\Main\Entity\ExpressionField(
                'IDS',
                "GROUP_CONCAT(DISTINCT %s SEPARATOR ',')",
                ['ID']
            ),
            new \Bitrix\Main\Entity\ExpressionField(
                'CODES',
                "GROUP_CONCAT(DISTINCT %s SEPARATOR ',')",
                ['ROUTE_CODE.VALUE']
            ),
        ])
            ->setFilter(
                [
                    ['LOGIC' => 'OR',
                        ['=COUNTRY_FROM.VALUE' => $country],
                        ['=COUNTRY_TO.VALUE' => $country]
                    ],
                ]
            )
            ->setGroup(['URL_LEVEL_TWO.VALUE'])
            ->setOrder(['COUNT' => 'DESC']);


        $data = $this->getCargoElementsGroup($query);

        $result = [];

        foreach ($data as $item) {
            if ($country == $item['DATA']['COUNTRY_FROM']) {
                $result[$item['DATA']['CITY_FROM']][] = [
                    'CITY' => $item['DATA']['CITY_FROM'],
                    'CITY_ORIGIN' => $item['DATA']['CITY_FROM_ORIGIN'],
                    'COUNTRY' => $item['DATA']['COUNTRY_FROM'],
                ];
            } elseif ($country == $item['DATA']['COUNTRY_TO']) {
                $result[$item['DATA']['CITY_TO']][] = [
                    'CITY' => $item['DATA']['CITY_TO'],
                    'CITY_ORIGIN' => $item['DATA']['CITY_TO_ORIGIN'],
                    'COUNTRY' => $item['DATA']['COUNTRY_TO'],
                ];
            }
        }

        foreach ($result as $city => &$datum) {
            $count = count($datum);
            $datum = current($datum);
            $datum['COUNT'] = $count;
        }

        return array_slice($result, 0, 5);
    }

    public function getCountryPartners($filter, $direction)
    {
        $collection = $this->getElements($filter);
        $data = [];
        foreach ($collection as $item) {
            // Если страна ИЗ то нужно взять страну В
            if ($direction == 'TO') {
                $country = $item['COUNTRY_TO'];
                $localId = $item['LOCAL_COUNTRY_TO'];
                $origin = $item['COUNTRY_TO_ORIGIN'];
            } elseif ($direction == 'FROM') {
                $country = $item['COUNTRY_FROM'];
                $localId = $item['LOCAL_COUNTRY_FROM'];
                $origin = $item['COUNTRY_FROM_ORIGIN'];
            }

            $data[$localId] = [
                'NAME' => $country,
                'ORIGIN' => $origin,
            ];
        }

        $population = $this->getPopulation(array_keys($data));
        foreach ($data as $localId => &$datum) {
            $datum['population'] = $population[$localId];
        }
        usort($data, function ($a, $b) {
            if ($a['population'] == $b['population']) {
                return 0;
            }
            return ($a['population'] > $b['population']) ? -1 : 1;
        });

        return $data;
    }

    public function getIblockOb()
    {
        return $this->obIblockHelper->getIblockOb();
    }

    public function getIblockHelper(): IblockPageHelper
    {
        return $this->obIblockHelper;
    }

    public function getCarriersData($arCarriersIds): array
    {
        return $this->restHelper->getCarriers($arCarriersIds);
    }

    public function getTransportData($arTransportIds): array
    {
        return $this->restHelper->getTransport($arTransportIds);
    }

    public function test()
    {
        $params = [
            'filter' => ['CODE' => ['StpStmb-LCL']],
            'select' => ['ID', 'DATA', "CODE"],
        ];
        $obRouteTable = Table\OnlogCalculationRouteTable::getList($params);
        $arRouteData = [];

        $arCargo = [];
        $data = [];

        $fn = function ($el) {
            return $el['shoulder']['shoulder_type'];
        };
        while ($ar = $obRouteTable->Fetch()) {
            $cargo = \Onlogsystem\Cargo\CargoFactory::createCargo($ar);
            $arCargo[] = $cargo;
            $steps = $cargo->getShoulders();

            $data[$cargo->getCode()] = [
                'top_fast' => [
                    'days' => current($steps['topFast'])['days'],
                    'money' => current($steps['topFast'])['money'],
                    'start' => $steps['topFast'][0]['start'],
                    'end' => end($steps['topFast'])['end'],
                    'steps' => $steps['topFast'],
                    'delivery_types' => array_map($fn, $steps['topFast'])
                ],
                'top_cheap' => [
                    'days' => current($steps['topCheap'])['days'],
                    'money' => current($steps['topCheap'])['money'],
                    'start' => $steps['topCheap'][0]['start'],
                    'end' => end($steps['topCheap'])['end'],
                    'steps' => $steps['topCheap'],
                    'delivery_types' => array_map($fn, $steps['topCheap'])
                ]

            ];
        }

        return $data;
    }
}