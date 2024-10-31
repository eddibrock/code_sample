<?php

namespace Onlogsystem\Page;

use Onlogsystem\Cargo\CargoNames;
use Onlogsystem\Cargo\CargoRestHelper;
use Fsd\Onlog\Integration\Table;
use Onlogsystem\Cargo\CargoSingleton;

abstract class AbstractPage implements Page
{
    protected static string $gruz;
    public string $name = '';
    public array $data = [];
    protected static string $dir = 'seo';
    protected static PageUrl $url;
    protected PageDataProvider $dataProvider;
    protected static bool $includeGruz = true;
    protected array $elements;


    public function __construct()
    {
        $this->dataProvider = new PageDataProvider();
    }

    public function getFilter(): array
    {
        return [];
    }

    public function checkType(): bool
    {
        $filter = $this->getFilter();
        $filter['limit'] = 1;
        $data = $this->dataProvider->getElements($filter);

        if (count($data) == 0) {
            return false;
        } else {
            return true;
        }
    }

    function getType(): string
    {
        return static::$type;
    }

    function getInfoSection(): string
    {
        return '';
    }

    function setPageData(): void
    {
        $data = $this->dataProvider->getElements($this->getFilter());
        $this->elements = $data;
        if (empty($data)) {
            static::$type = 'empty';
        } else {
            if (static::$type == 'country') {
                $this->data['FROM'] = current($data)['COUNTRY_FROM'];
                $this->data['FROM_ORIGIN'] = current($data)['COUNTRY_FROM_ORIGIN'];
                $this->data['TO'] = current($data)['COUNTRY_FROM'];
                $this->data['TO_ORIGIN'] = current($data)['COUNTRY_FROM_ORIGIN'];
            } elseif (static::$type == 'city') {
                $this->data['FROM'] = current($data)['CITY_FROM'];
                $this->data['FROM_ORIGIN'] = current($data)['CITY_FROM_ORIGIN'];
                $this->data['TO'] = current($data)['CITY_FROM'];
                $this->data['TO_ORIGIN'] = current($data)['CITY_FROM_ORIGIN'];
            } elseif (static::$type == 'city_city') {
                $this->data['FROM'] = current($data)['CITY_FROM'];
                $this->data['FROM_ORIGIN'] = current($data)['CITY_FROM_ORIGIN'];
                $this->data['TO'] = current($data)['CITY_TO'];
                $this->data['TO_ORIGIN'] = current($data)['CITY_TO_ORIGIN'];
            } elseif (static::$type == 'country_country') {
                $this->data['FROM'] = current($data)['COUNTRY_FROM'];
                $this->data['FROM_ORIGIN'] = current($data)['COUNTRY_FROM_ORIGIN'];
                $this->data['TO'] = current($data)['COUNTRY_TO'];
                $this->data['TO_ORIGIN'] = current($data)['COUNTRY_TO_ORIGIN'];
            }

            $this->data['COUNTRY_FROM'] = current($data)['COUNTRY_FROM'];
            $this->data['COUNTRY_FROM_ORIGIN'] = current($data)['COUNTRY_FROM_ORIGIN'];
            $this->data['COUNTRY_TO'] = current($data)['COUNTRY_TO'];
            $this->data['COUNTRY_TO_ORIGIN'] = current($data)['COUNTRY_TO_ORIGIN'];
        }
    }

    public function getNavChain(): array
    {
        $chain = [];
        $dir = '/calculator/';
        $name = $this->data['FROM_ORIGIN'];
        $url = $this->name;
        $chain[] = [
            'url' => "{$dir}{$url}/",
            'name' => $name
        ];

        return $chain;
    }

    public function getUrl()
    {
        return static::$url;
    }

    public function setCase(): void
    {
        $this->data['TO_ORIGIN_IZ'] = \phpMorphy\Helpers\Morphy::getMorphedRegionName(
            \phpMorphy\Helpers\Morphy::getMorphy(), $this->data['TO_ORIGIN'], false, [\phpMorphy\Helpers\Cases::getCase('родительный')->name]);
        $this->data['TO_ORIGIN_V'] = \phpMorphy\Helpers\Morphy::getMorphedRegionName(
            \phpMorphy\Helpers\Morphy::getMorphy(), $this->data['TO_ORIGIN'], false, [\phpMorphy\Helpers\Cases::getCase('винительный')->name]);
        $this->data['TO_COUNTRY_ORIGIN_V'] = \phpMorphy\Helpers\Morphy::getMorphedRegionName(
            \phpMorphy\Helpers\Morphy::getMorphy(), $this->data['COUNTRY_TO_ORIGIN'], false, [\phpMorphy\Helpers\Cases::getCase('винительный')->name]);
        $this->data['FROM_ORIGIN_IZ'] = \phpMorphy\Helpers\Morphy::getMorphedRegionName(
            \phpMorphy\Helpers\Morphy::getMorphy(), $this->data['FROM_ORIGIN'], false, [\phpMorphy\Helpers\Cases::getCase('родительный')->name]) ?: $this->data['FROM_ORIGIN'];
        $this->data['FROM_COUNTRY_ORIGIN_IZ'] = \phpMorphy\Helpers\Morphy::getMorphedRegionName(
            \phpMorphy\Helpers\Morphy::getMorphy(), $this->data['COUNTRY_FROM_ORIGIN'], false, [\phpMorphy\Helpers\Cases::getCase('родительный')->name]);
        $this->data['FROM_ORIGIN_V'] = \phpMorphy\Helpers\Morphy::getMorphedRegionName(
            \phpMorphy\Helpers\Morphy::getMorphy(), $this->data['FROM_ORIGIN'], false, [\phpMorphy\Helpers\Cases::getCase('винительный')->name]) ?: $this->data['FROM_ORIGIN'];
        if (static::$gruz) {
            $this->data['GRUZ'] = CargoNames::getName(static::$gruz);
            $this->data['GRUZ_IH'] = CargoNames::getName(static::$gruz . '_ih');
        }
    }

    public function getCargoEnumType($type): string
    {
        return match ($type) {
            'konteynernyy-gruz' => 'CONTAINER_TYPE',
            'sbornyy-gruz' => 'GROUPAGE_TYPE',
            'avia-gruz' => 'AIR_TYPE',
            default => ''
        };
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getDir(): string
    {
        return static::$dir;
    }

    public function createInstructionLink(): string
    {
        return "<a href='/" . self::$dir . "/instruktsiya/'>Инструкция по использованию</a>";
    }

    public function createCalculatorLink($str = ''): string
    {
        $str = $str ?: 'онлайн калькулятора ОнлогСистем';
        return "<a href='/" . self::$dir . "/'>" . $str . "</a>";
    }

    public function createCalculatorLinkOm(): string
    {
        return "<a href='/" . self::$dir . "/'>онлайн калькулятором грузоперевозок</a>";
    }

    function getUrlFrom(): string
    {
        $format = "/" . static::$dir . "/%s/";
        return sprintf($format, $this->data['FROM']);
    }

    function getUrlTo(): string
    {
        $format = "/" . static::$dir . "/%s/";
        return sprintf($format, $this->data['TO']);
    }

    public function createLinkFromTo(): string
    {
        $format = "/" . self::$dir . "/%s/";
        $url = sprintf($format, $this->name);

        return "<a href='" . $url . "'>" . $this->data['FROM_ORIGIN'] . '-' . $this->data['TO_ORIGIN'] . "</a>";
    }

    public function createLinkFrom(): string
    {
        $url = $this->getUrlFrom();
        return "<a href='" . $url . "'>" . $this->data['FROM_ORIGIN'] . "</a>";
    }

    public function createLinkFromCountry(): string
    {
        $format = "/" . self::$dir . "/%s/";
        $url = sprintf($format, $this->data['COUNTRY_FROM']);
        return "<a href='" . $url . "'>" . $this->data['COUNTRY_FROM_ORIGIN'] . "</a>";
    }

    public function createLinkFromCountryIz(): string
    {
        $format = "/" . self::$dir . "/%s/";
        $url = sprintf($format, $this->data['COUNTRY_FROM']);
        return "<a href='" . $url . "'>" . $this->data['FROM_COUNTRY_ORIGIN_IZ'] . "</a>";
    }

    public function createLinkToCountry(): string
    {
        $format = "/" . self::$dir . "/%s/";
        $url = sprintf($format, $this->data['COUNTRY_TO']);
        return "<a href='" . $url . "'>" . $this->data['COUNTRY_TO_ORIGIN'] . "</a>";
    }

    public function createLinkToCountryV(): string
    {
        $format = "/" . self::$dir . "/%s/";
        $url = sprintf($format, $this->data['COUNTRY_TO']);
        return "<a href='" . $url . "'>" . $this->data['TO_COUNTRY_ORIGIN_V'] . "</a>";
    }

    public function createLinkTo(): string
    {
        $url = $this->getUrlTo();
        return "<a href='" . $url . "'>" . $this->data['TO_ORIGIN'] . "</a>";
    }

    public function createLinkFromIz(): string
    {
        $url = $this->getUrlFrom();
        return "<a href='" . $url . "'>" . $this->data['FROM_ORIGIN_IZ'] . "</a>";
    }

    public function createLinkToV(): string
    {
        $url = $this->getUrlTo();
        return "<a href='" . $url . "'>" . $this->data['TO_ORIGIN_V'] . "</a>";
    }

    function getTopCountry(): array
    {
        return [];
    }

    function getTransportationIntegration(): array
    {
        return [];
    }

    public function getPopularRoutes(): array
    {
        return [];
    }

    public function getCities()
    {
        return [];
    }

    public function getElements(): array
    {
        return $this->dataProvider->getCargoElements($this->getFilter());
    }

    public function getShouldersCountAll(): int
    {
        $filter = [
            'filter' => [],
        ];
        $arCodes = [];
        $data = $this->dataProvider->getElements($filter);
        foreach ($data as $item) {
            $arCodes[] = $item['CODE'];
        }
        $params = [
            'filter' => ['CODE' => $arCodes],
            'select' => ['ID', 'DATA', "CODE"],
        ];
        $obRouteTable = Table\OnlogCalculationRouteTable::getList($params);
        $count = 0;
        while ($ar = $obRouteTable->Fetch()) {
            $cargo = \Onlogsystem\Cargo\CargoFactory::createCargo($ar);
            $count += $cargo->getAllShoulderCount();
        }

        return $count;
    }

    public function getTerminalCountAll(): int
    {
        $filter = [
            'filter' => [],
        ];
        $arCodes = [];
        $data = $this->dataProvider->getElements($filter);
        foreach ($data as $item) {
            $arCodes[] = $item['CODE'];
        }
        $params = [
            'filter' => ['CODE' => $arCodes],
            'select' => ['ID', 'DATA', "CODE"],
        ];
        $obRouteTable = Table\OnlogCalculationRouteTable::getList($params);
        $count = 0;
        while ($ar = $obRouteTable->Fetch()) {
            $cargo = \Onlogsystem\Cargo\CargoFactory::createCargo($ar);
            $count += $cargo->getAllTerminalCount();
        }

        return $count;
    }

    public function getCarrierCountAll(): int
    {
        $filter = [
            'filter' => [],
        ];
        $arCodes = [];
        $data = $this->dataProvider->getElements($filter);
        foreach ($data as $item) {
            $arCodes[] = $item['CODE'];
        }
        $params = [
            'filter' => ['CODE' => $arCodes],
            'select' => ['ID', 'DATA', "CODE"],
        ];
        $obRouteTable = Table\OnlogCalculationRouteTable::getList($params);
        $carriers = [];
        while ($ar = $obRouteTable->Fetch()) {
            $cargo = \Onlogsystem\Cargo\CargoFactory::createCargo($ar);
            $carriers = array_merge($carriers, $cargo->getAllCarrier());
        }

        return count(array_unique($carriers));
    }

    public function getCarriers(): array
    {
        $filter = $this->getFilter();
        $arCodes = [];
        $data = $this->dataProvider->getElements($filter);
        foreach ($data as $item) {
            $arCodes[] = $item['CODE'];
        }
        $params = [
            'filter' => ['CODE' => $arCodes],
            'select' => ['ID', 'DATA', "CODE"],
        ];
        $obRouteTable = Table\OnlogCalculationRouteTable::getList($params);
        $carriers = [];
        while ($ar = $obRouteTable->Fetch()) {
            $cargo = \Onlogsystem\Cargo\CargoFactory::createCargo($ar);
            $carriers = array_merge($carriers, $cargo->getAllCarrier());
        }
        $carriers = array_unique($carriers);

        $carriersData = $this->dataProvider->getCarriersData($carriers);

        $carriersName = [];

        foreach ($carriersData as $carriersDatum) {
            $carriersName[] = $carriersDatum['default_name'];
        }

        return $carriersName;
    }

    public function geTransitCarriers(): array
    {
        $filter = $this->getFilter();
        foreach ($filter as $key => $item) {
            if ($key == 'limit') {
                unset($filter[$key]);
            }
        }
        $arCodes = [];
        $data = $this->dataProvider->getElements($filter);
        foreach ($data as $item) {
            $arCodes[] = $item['CODE'];
        }
        $params = [
            'filter' => ['CODE' => $arCodes],
            'select' => ['ID', 'DATA', "CODE"],
        ];
        $obRouteTable = Table\OnlogCalculationRouteTable::getList($params);
        $carriers = [];
        while ($ar = $obRouteTable->Fetch()) {
            $cargo = \Onlogsystem\Cargo\CargoFactory::createCargo($ar);
            $carriers = array_merge($carriers, $cargo->getTransitTerminalCarriers('topcheap'));
        }
        $carriers = array_unique($carriers);

        $carriersData = $this->dataProvider->getCarriersData($carriers);

        $carriersName = [];

        foreach ($carriersData as $carriersDatum) {
            $carriersName[] = $carriersDatum['default_name'];
        }

        return $carriersName;
    }

    public function geTransitTransport(): array
    {
        $filter = $this->getFilter();
        $arCodes = [];
        $data = $this->dataProvider->getElements($filter);
        foreach ($data as $item) {
            $arCodes[] = $item['CODE'];
        }
        $params = [
            'filter' => ['CODE' => $arCodes],
            'select' => ['ID', 'DATA', "CODE"],
        ];
        $obRouteTable = Table\OnlogCalculationRouteTable::getList($params);
        $transport = [];
        while ($ar = $obRouteTable->Fetch()) {
            $cargo = \Onlogsystem\Cargo\CargoFactory::createCargo($ar);
            $transport = array_merge($transport, $cargo->getTransitTransportTypes('topcheap'));
//            $transport = array_merge($transport, $cargo->getTransitTransportTypes('topfast'));
        }
        $transport = array_unique($transport);

        // Получить способы доставки (авто, море, авиа, жд)
        $transportData = $this->dataProvider->getTransportData($transport);
        $transportName = [];
        foreach ($transportData as $transportDatum) {
            $transportName[] = $transportDatum['default_name'];
        }

        return $transportName;
    }

    function getTransitTerminals(): array
    {
        $filter = $this->getFilter();
        $arTerminals = [];
        $data = $this->dataProvider->getCargoElements($filter);
        foreach ($data as $datum) {
            $arTerminals[] = $datum['CARGO_DATA']['transit_terminal'];
        }

        return array_unique($arTerminals);
    }

    function getCargoTypes(): string
    {
        if (static::$gruz) {
            static::$includeGruz = false;
            $data = $this->dataProvider->getElements($this->getFilter());
            $result = [];
            foreach ($data as $datum) {
                if (static::$gruz == CargoNames::getName($datum['CARGO_TYPE_NAME'])) {
                    continue;
                }
                $result[$datum['CARGO_TYPE_ID']] = CargoNames::getName(CargoNames::getName($datum['CARGO_TYPE_NAME']) . '_ih');
            }
            return implode(', ', array_values($result));
        } else {
            return 'разных типов груза';
        }
    }

    function getAllCargoTypes(): string
    {
        return $this->dataProvider->getAllCargoTypes();
    }

    /** Лучшие варианты по стоимости и срокам для грузов */
    function getTopDataGruz(): array
    {
        if (!static::$gruz) {
            return [];
        }
        $filter = $this->getFilter()['filter'];
        // Из фильтра исключить ограничение по типу груза
        foreach ($filter as $key => $item) {
            if ($key == 'CARGO_TYPE.ITEM.XML_ID') {
                unset($filter[$key]);
            }
        }
        $query = $this->dataProvider->getIblockHelper()->getQuery();
        $query->setSelect([
            new \Bitrix\Main\Entity\ExpressionField(
                'COUNT',
                "COUNT(*)",
            ),
            'URL_LEVEL_TWO.VALUE',
            'CARGO_TYPE.ITEM.VALUE',
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
                $filter
            )
            ->setGroup(['URL_LEVEL_TWO.VALUE', 'CARGO_TYPE.ITEM.VALUE'])
            ->setOrder(['COUNT' => 'DESC']);

        $data = $this->dataProvider->getCargoElementsGroup($query);

        //Необходимо исключить тип груза который сейчас выбран
        foreach ($data as $key => $datum) {
            if ($datum['DATA']['CARGO_DATA']['cargo_name'] == static::$gruz) {
                unset($data[$key]);
            }
        }

        foreach ($data as &$item) {
            $item['DATA']['CITY_FROM_ORIGIN'] = \phpMorphy\Helpers\Morphy::getMorphedRegionName(
                \phpMorphy\Helpers\Morphy::getMorphy(), $item['DATA']['CITY_FROM_ORIGIN'], false, [\phpMorphy\Helpers\Cases::getCase('родительный')->name]);
            $item['DATA']['CITY_TO_ORIGIN'] = \phpMorphy\Helpers\Morphy::getMorphedRegionName(
                \phpMorphy\Helpers\Morphy::getMorphy(), $item['DATA']['CITY_TO_ORIGIN'], false, [\phpMorphy\Helpers\Cases::getCase('винительный')->name]);
            $item['from_link'] = sprintf('из&nbsp<a href="/%s/%s/%s/">%s</a>', self::$dir,
                $item['COUNTRY_FROM'], $item['DATA']['CITY_FROM'], $item['DATA']['CITY_FROM_ORIGIN']);
            $item['from_to'] = sprintf('в&nbsp<a href="/%s/%s/%s/">%s</a>', self::$dir,
                $item['DATA']['COUNTRY_TO'], $item['DATA']['CITY_TO'], $item['DATA']['CITY_TO_ORIGIN']);
            $item['url'] = sprintf('<a href="/%s/%s/%s/">%s</a>', self::$dir,
                $item['DATA']['CITY_FROM'] . '-' . $item['DATA']['CITY_TO'], $item['DATA']['CARGO_DATA']['cargo_name'], 'узнать подробности'
            );
            $item['containerDimension'] = 'за ' . $item['DATA']['CARGO_DATA']['dimension'];
            $item['topFast'] = 'от ' . $item['DATA']['CARGO_DATA']['days'] . ' дн.';
            $item['topCheap'] = 'от ' . round($item['DATA']['CARGO_DATA']['money'] / 5, 2) . ' руб.';
        }

        $cargoTypes = ['Контейнерный груз', 'Сборный груз', 'Авиа груз'];
        $res = [
            'TOP_FAST' => [],
            'TOP_CHEAP' => [],
        ];
        foreach ($cargoTypes as $cargoType) {
            if ($cargoType == CargoNames::getName(self::$gruz)) {
                continue;
            }
            foreach ($data as $datum) {
                if ($datum['IBLOCK_ELEMENTS_ELEMENT_ROUTES_CARGO_TYPE_ITEM_VALUE'] == $cargoType) {
                    $res['TOP_FAST'][$cargoType] = $datum;
                    $res['TOP_CHEAP'][$cargoType] = $datum;
                }
            }

            if (!array_key_exists($cargoType, $res['TOP_FAST'])) {
                $res['TOP_FAST'][$cargoType] = [
                    'from_link' => '-',
                    'from_to' => '-',
                    'url' => sprintf('<a href="/%s/%s/">%s</a>', self::$dir, 'faq', 'по запросу'),
                    'containerDimension' => '-',
                    'topFast' => '-',
                    'topCheap' => '-',
                ];;
            }
            if (!array_key_exists($cargoType, $res['TOP_CHEAP'])) {
                $res['TOP_CHEAP'][$cargoType] = [
                    'from_link' => '-',
                    'from_to' => '-',
                    'url' => sprintf('<a href="/%s/%s/">%s</a>', self::$dir, 'faq', 'по запросу'),
                    'containerDimension' => '-',
                    'topFast' => '-',
                    'topCheap' => '-',
                ];;
            }
        }

        return $res;
    }

    /** Лучшие варианты по стоимости и срокам */
    function getTopData(): array
    {
        // По всем типам груза этих стран получить самое быстрое
        if (static::$gruz) {
            return $this->getTopDataGruz();
        }
        $collection = $this->dataProvider->getElements($this->getFilter());
        $data = [];
        if (count($collection) == 0) {
            return $data;
        }

        foreach ($collection as $item) {
            $code = $item['CODE'];
            $item['CITY_FROM_ORIGIN'] = \phpMorphy\Helpers\Morphy::getMorphedRegionName(
                \phpMorphy\Helpers\Morphy::getMorphy(), $item['CITY_FROM_ORIGIN'], false, [\phpMorphy\Helpers\Cases::getCase('родительный')->name]);
            $item['CITY_TO_ORIGIN'] = \phpMorphy\Helpers\Morphy::getMorphedRegionName(
                \phpMorphy\Helpers\Morphy::getMorphy(), $item['CITY_TO_ORIGIN'], false, [\phpMorphy\Helpers\Cases::getCase('винительный')->name]);
            $data[$code] = $item;
        }
        $arCodes = array_keys($data);

        $params = [
            'filter' => ['CODE' => $arCodes],
            'select' => ['ID', 'DATA', "CODE"],
        ];
        $obRouteTable = Table\OnlogCalculationRouteTable::getList($params);
        $arRouteData = [];

        while ($ar = $obRouteTable->Fetch()) {
            $cargo = \Onlogsystem\Cargo\CargoFactory::createCargo($ar);
            $code = $cargo->getCode();
            $innerData = $data[$code];
            $type = $innerData['CARGO_TYPE_NAME'];
            $arRouteData[$type][] = [
                'from_link' => sprintf('из&nbsp<a href="/%s/%s/%s/">%s</a>', self::$dir,
                    $innerData['COUNTRY_FROM'], $innerData['CITY_FROM'], $innerData['CITY_FROM_ORIGIN']),
                'from_to' => sprintf('в&nbsp<a href="/%s/%s/%s/">%s</a>', self::$dir,
                    $innerData['COUNTRY_TO'], $innerData['CITY_TO'], $innerData['CITY_TO_ORIGIN']),
                'url' => sprintf('<a href="/%s/%s/%s/">%s</a>', self::$dir,
                    $innerData['CITY_FROM'] . '-' . $innerData['CITY_TO'], $cargo->getCargoName(), 'узнать подробности'
                ),

                'containerDimension' => 'за ' . $cargo->getContainerDimension(),
                'topFast' => 'от ' . $cargo->getFastestTopData() . ' дн.',
                'topCheap' => 'от ' . round($cargo->getCheapestTopData() / 5, 2) . ' руб.',
            ];
        }

        $res = [
            'TOP_FAST' => [],
            'TOP_CHEAP' => [],
        ];
        $fastest = null;
        $fastestItem = null;
        $cheapest = null;
        $cheapestItem = null;
        // Получить самый быстрый маршрут
        foreach ($arRouteData as $type => $arRouteDatum) {
            if (count($arRouteDatum) == 1) {
                $res['TOP_FAST'][$type] = $arRouteDatum[0];
                continue;
            } else {
                foreach ($arRouteDatum as $item) {
                    if ($fastest == null) {
                        $fastest = $item['topFast'];
                        $fastestItem = $item;
                    } else {
                        if ($item['topFast'] < $fastest) {
                            $fastest = $item['topFast'];
                            $fastestItem = $item;
                        }
                    }
                }
            }
            $res['TOP_FAST'][$type] = $fastestItem;

            unset($fastest);
            unset($fastestCode);
        }
        // Получить самый дешевый маршрут
        foreach ($arRouteData as $type => $arRouteDatum) {
            if (count($arRouteDatum) == 1) {
                $res['TOP_CHEAP'][$type] = $arRouteDatum[0];
                continue;
            } else {
                foreach ($arRouteDatum as $item) {
                    if ($cheapest == null) {
                        $cheapest = $item['topCheap'];
                        $cheapestItem = $item;
                    } else {
                        if ($item['topCheap'] < $cheapest) {
                            $cheapest = $item['topCheap'];
                            $cheapestItem = $item;
                        }
                    }
                }
            }
            $res['TOP_CHEAP'][$type] = $cheapestItem;

            unset($cheapest);
            unset($cheapestCode);
        }

        $cargoTypes = ['Контейнерный груз', 'Сборный груз', 'Авиа груз'];
        $cargoTypesDiff = array_diff($cargoTypes, array_keys($res['TOP_CHEAP']));
        foreach ($cargoTypesDiff as $item) {
            $res['TOP_CHEAP'][$item] = [
                'from_link' => '-',
                'from_to' => '-',
                'url' => sprintf('<a href="/%s/%s/">%s</a>', self::$dir, 'faq', 'по запросу'),
                'containerDimension' => '-',
                'topFast' => '-',
                'topCheap' => '-',
            ];
        }
        $cargoTypesDiff = array_diff($cargoTypes, array_keys($res['TOP_FAST']));
        foreach ($cargoTypesDiff as $item) {
            $res['TOP_FAST'][$item] = [
                'from_link' => '-',
                'from_to' => '-',
                'url' => sprintf('<a href="/%s/%s/">%s</a>', self::$dir, 'faq', 'по запросу'),
                'containerDimension' => '-',
                'topFast' => '-',
                'topCheap' => '-',
            ];
        }
        return $res;
    }

    public function getLastUpdate(): string
    {
        $arCodes = array_keys(array_column($this->elements, 'ID', 'CODE'));
        $params = [
            'filter' => ['CODE' => $arCodes],
            'select' => ['ID', 'DATE'],
            'order' => ['DATE' => 'DESC']
        ];
        $obRouteTable = Table\OnlogCalculationRouteTable::getList($params);
        if ($ar = $obRouteTable->Fetch()) {
            return date('d-m-Y', strtotime($ar['DATE']));
        }

        return '';
    }

    public function getDisclaimer(): string
    {
        $format = "<p>
        * Все маршруты и тарифы, указанные на данной странице, не являются публичной афертой.
        Дата обновления информации: %s.
        Для актуализации расчета перейдите в %s.
    </p";

        return sprintf($format,
            $this->getLastUpdate(),
            $this->createCalculatorLink('калькулятор ОнлогСистем')
        );
    }

    public function getDeclaredPrice(): string
    {
        $data = $this->dataProvider->getCargoElements($this->getFilter());
        $minDays = null;
        $minMoney = null;
        foreach ($data as $datum) {
            if ($minDays == null) {
                $minDays = $datum['CARGO_DATA']['days'];
            }
            if ($minMoney == null) {
                $minMoney = $datum['CARGO_DATA']['money'];
            }
            if ($datum['CARGO_DATA']['days'] < $minDays) {
                $minDays = $datum['CARGO_DATA']['days'];
            }
            if ($datum['CARGO_DATA']['money'] < $minMoney) {
                $minMoney = $datum['CARGO_DATA']['money'];
            }
        }

        $format = "<p>
        Стоимость доставки для %s по рассматриваемому маршруту начинается от %s руб., минимально возможные транзитные сроки составляют %s дн.
    </p";

        return sprintf($format,
            CargoNames::getName(static::$gruz . '_ih'),
            round($minMoney / 5, 2),
            $minDays
        );
    }

    public function getTopTerminals(): array
    {
        return [];
    }

    public function getTopPartners(): array
    {
        return [];
    }

    public function getDetailRoutes(): array
    {
        $query = $this->dataProvider->getIblockHelper()->getQuery();
        $query->setSelect([
            new \Bitrix\Main\Entity\ExpressionField(
                'COUNT',
                "COUNT(*)",
            ),
            'URL_LEVEL_TWO.VALUE',
//            'CARGO_TYPE.ITEM.VALUE',
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
                $this->getFilter()['filter']
            )
            ->setGroup(['URL_LEVEL_TWO.VALUE', 'CARGO_TYPE.ITEM.VALUE'])
            ->setOrder(['COUNT' => 'DESC']);
        $data = $this->dataProvider->getCargoElementsGroup($query);
        $result = [];
        $fn = function ($el) {
            return $el['shoulder']['shoulder_type'];
        };
        $obGraph = new \Onlogsystem\Cargo\GraphDataProvider();

        foreach ($data as $datum) {
            $cargo = $datum['DATA']['CARGO_DATA']['cargo'];
            $obGraph->addCargo($cargo);
            $steps = $cargo->getShoulders();
            $result['top_fast'][$cargo->getCode()] = [
                'data_location'=> $cargo->getRouteLocationIds(),
                'days' => current($steps['topFast'])['days'],
                'money' => round(current($steps['topFast'])['money'] / 5, 2),
                'start' => $steps['topFast'][0]['start'],
                'end' => end($steps['topFast'])['end'],
                'steps' => $steps['topFast'],
                'delivery_types' => array_map($fn, $steps['topFast'])
            ];

            $result['top_cheap'][$cargo->getCode()] = [
                'data_location'=> $cargo->getRouteLocationIds(),
                'days' => current($steps['topCheap'])['days'],
                'money' => round(current($steps['topCheap'])['money'] / 5, 2),
                'start' => $steps['topCheap'][0]['start'],
                'end' => end($steps['topCheap'])['end'],
                'steps' => $steps['topCheap'],
                'delivery_types' => array_map($fn, $steps['topCheap'])
            ];
        }
        $locationData = $obGraph->getLocationData();
        foreach ($result as $direction => $data) {
            foreach ($data as $code => &$item) {
                //START
                if (empty($item['start']['location_id'])){
                    $item['start']['location_id'] = $item['data_location'][0];
                }
                if (array_key_exists($item['start']['location_id'], $locationData['LOCATIONS'])) {
                    $result[$direction][$code]['start']['location_name'] = $locationData['LOCATIONS'][$item['start']['location_id']]['message'];
                    $result[$direction][$code]['start']['parents'] = $locationData['LOCATIONS'][$item['start']['location_id']]['toLocations'];
                }
                if ($result[$direction][$code]['start']['parents']) {
                    foreach ($result[$direction][$code]['start']['parents'] as $parent) {
                        if (array_key_exists($parent, $locationData['LOCATIONS'])) {
                            if ($locationData['LOCATIONS'][$parent]['message']) {
                                $result[$direction][$code]['start']['parent_name'][] = $locationData['LOCATIONS'][$parent]['message'];
                            }
                        }
                    }
                }

                foreach ($item['start']['localized_names'] as $localized_name) {
                    if (array_key_exists($localized_name, $locationData['NAMES'])) {
                        $result[$direction][$code]['start']['name'] = $locationData['NAMES'][$localized_name];
                    }
                }
                foreach ($item['start']['localized_abbreviations'] as $localized_abbreviation) {
                    if (array_key_exists($localized_abbreviation, $locationData['NAMES'])) {
                        $result[$direction][$code]['start']['abbreviation_name'] = $locationData['NAMES'][$localized_abbreviation];
                    }
                }
                //END
                if (empty($item['end']['location_id'])){
                    $item['end']['location_id'] = $item['data_location'][1];
                }
                if (array_key_exists($item['end']['location_id'], $locationData['LOCATIONS'])) {
                    $result[$direction][$code]['end']['location_name'] = $locationData['LOCATIONS'][$item['end']['location_id']]['message'];
                    $result[$direction][$code]['end']['parents'] = $locationData['LOCATIONS'][$item['end']['location_id']]['toLocations'];

                }
                if ($result[$direction][$code]['end']['parents']) {
                    foreach ($result[$direction][$code]['end']['parents'] as $parent) {
                        if (array_key_exists($parent, $locationData['LOCATIONS'])) {
                            if ($locationData['LOCATIONS'][$parent]['message']) {
                                $result[$direction][$code]['end']['parent_name'][] = $locationData['LOCATIONS'][$parent]['message'];
                            }
                        }
                    }
                }
                foreach ($item['end']['localized_names'] as $localized_name) {
                    if (array_key_exists($localized_name, $locationData['NAMES'])) {
                        $result[$direction][$code]['end']['name'] = $locationData['NAMES'][$localized_name];
                    }
                }
                foreach ($item['end']['localized_abbreviations'] as $localized_abbreviation) {
                    if (array_key_exists($localized_abbreviation, $locationData['NAMES'])) {
                        $result[$direction][$code]['end']['abbreviation_name'] = $locationData['NAMES'][$localized_abbreviation];
                    }
                }
                foreach ($item['steps'] as $key => &$step) {
                    //START
                    if (empty($step['start']['location_id'])){
                        $step['start']['location_id'] = $item['data_location'][0];
                    }
                    if (array_key_exists($step['start']['location_id'], $locationData['LOCATIONS'])) {
                        $result[$direction][$code]['steps'][$key]['start']['location_name'] = $locationData['LOCATIONS'][$step['start']['location_id']]['message'];
                        $result[$direction][$code]['steps'][$key]['start']['parents'] = $locationData['LOCATIONS'][$step['start']['location_id']]['toLocations'];
                    }
                    if ($result[$direction][$code]['steps'][$key]['start']['parents']) {
                        foreach ($result[$direction][$code]['steps'][$key]['start']['parents'] as $parent) {
                            if (array_key_exists($parent, $locationData['LOCATIONS'])) {
                                if ($locationData['LOCATIONS'][$parent]['message']) {
                                    $result[$direction][$code]['steps'][$key]['start']['parent_name'][] = $locationData['LOCATIONS'][$parent]['message'];
                                }
                            }
                        }
                    }
                    foreach ($step['start']['localized_names'] as $localized_name) {
                        if (array_key_exists($localized_name, $locationData['NAMES'])) {
                            $result[$direction][$code]['steps'][$key]['start']['name'] = $locationData['NAMES'][$localized_name];
                        }
                    }
                    foreach ($step['start']['localized_abbreviations'] as $localized_abbreviation) {
                        if (array_key_exists($localized_abbreviation, $locationData['NAMES'])) {
                            $result[$direction][$code]['steps'][$key]['start']['abbreviation_name'] = $locationData['NAMES'][$localized_abbreviation];
                        }
                    }
                    //END
                    if (empty($step['end']['location_id'])){
                        $step['end']['location_id'] = $item['data_location'][1];
                    }
                    if (array_key_exists($step['end']['location_id'], $locationData['LOCATIONS'])) {
                        $result[$direction][$code]['steps'][$key]['end']['location_name'] = $locationData['LOCATIONS'][$step['end']['location_id']]['message'];
                        $result[$direction][$code]['steps'][$key]['end']['parents'] = $locationData['LOCATIONS'][$step['end']['location_id']]['toLocations'];
                    }
                    if ($result[$direction][$code]['steps'][$key]['end']['parents']) {
                        foreach ($result[$direction][$code]['steps'][$key]['end']['parents'] as $parent) {
                            if (array_key_exists($parent, $locationData['LOCATIONS'])) {
                                if ($locationData['LOCATIONS'][$parent]['message']) {
                                    $result[$direction][$code]['steps'][$key]['end']['parent_name'][] = $locationData['LOCATIONS'][$parent]['message'];
                                }
                            }
                        }
                    }
                    foreach ($step['end']['localized_names'] as $localized_name) {
                        if (array_key_exists($localized_name, $locationData['NAMES'])) {
                            $result[$direction][$code]['steps'][$key]['end']['name'] = $locationData['NAMES'][$localized_name];
                        }
                    }
                    foreach ($step['end']['localized_abbreviations'] as $localized_abbreviation) {
                        if (array_key_exists($localized_abbreviation, $locationData['NAMES'])) {
                            $result[$direction][$code]['steps'][$key]['end']['abbreviation_name'] = $locationData['NAMES'][$localized_abbreviation];
                        }
                    }
                }
            }
        }

        return $result;
    }
}