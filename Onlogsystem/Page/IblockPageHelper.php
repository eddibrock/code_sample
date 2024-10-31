<?php

namespace Onlogsystem\Page;

class IblockPageHelper
{
    private \Onlogsystem\Iblock\IblockService $obIblock;

    public function __construct()
    {
        $this->obIblock = new \Onlogsystem\Iblock\IblockService('popular_routes');

    }

    public function getSelect(): array
    {
        return [
            'URL_LEVEL_ONE',
            'URL_LEVEL_TWO',
            'CARGO_DATA',
            'LOCAL_FROM',
            'LOCAL_TO',
            'TERMINAL_FROM',
            'TERMINAL_TO',
            'COUNTRY_FROM',
            'COUNTRY_TO',
            'ROUTE_CODE',
            'CITY_FROM',
            'CITY_TO',
            'COUNTRY_FROM_ORIGIN',
            'COUNTRY_TO_ORIGIN',
            'CITY_FROM_ORIGIN',
            'CITY_TO_ORIGIN',
            'CARGO_TYPE.ITEM',
            'ID',
            'NAME',
            'CODE',
            'LOCAL_COUNTRY_FROM',
            'LOCAL_COUNTRY_TO',
        ];
    }

    public function getDefaultFilter(): array
    {
        return [
            'filter' => [
                'IBLOCK_ID' => $this->obIblock->getIblockId()
            ],
            'select' => ['ID', 'NAME']
        ];
    }

    public function getCollection($params): \Bitrix\Main\ORM\Objectify\Collection
    {
        $params['filter']['IBLOCK_ID'] = $this->obIblock->getIblockId();
        $params['filter']['ACTIVE'] = 'Y';
        $collection = $this->obIblock->getDecomposedCollection($params['filter'], $this->getSelect(), [], $params['limit']);

        return $collection;
    }

    public function getIblockOb(): \Onlogsystem\Iblock\IblockService
    {
        return $this->obIblock;
    }

    public function getCargoTypeName($cargoTypeId)
    {
        return $this->obIblock->getEnumNameById('CARGO_TYPE', $cargoTypeId);
    }

    public function getQuery(): \Bitrix\Main\ORM\Query\Query
    {
        return $this->obIblock->getIblockDataClass()::query();
    }
}