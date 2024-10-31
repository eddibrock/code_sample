<?php

namespace Onlogsystem\Page;

use Fsd\Onlog\Integration\Table;
use Onlogsystem\Cargo\CargoNames;

class PageCityToCountry extends AbstractPage
{
    protected static string $type = 'city_country';

    public function __construct(PageUrl $url)
    {
        parent::__construct();
        $this->name = $url->getLevelOne();
        self::$gruz = $url->getGruz();
        $this->setPageData();
        if (self::$url->getGruz()) {
            self::$type = 'city_country_gruz';
        }
        if ($this->data != []) {
            $this->setCase();
        } else {
            self::$type = 'empty';
        }
    }

    function getUrlFrom(): string
    {
        $format = "/" . self::$dir . "/%s/%s/";
        return sprintf($format, $this->data['COUNTRY_FROM'], $this->data['FROM']);
    }

    function getFilter(): array
    {
        $filter = [
            'filter' => [
                '%=URL_LEVEL_TWO.VALUE' => explode('-', $this->name)[0] . '%',
            ],
        ];
        if ($this->data['FROM']) {
            $filter['filter']['CITY_FROM.VALUE'] = $this->data['FROM'];
        }
        if ($this->data['TO']) {
            $filter['filter']['COUNTRY_TO.VALUE'] = $this->data['TO'];
        }
        if (self::$gruz && self::$includeGruz) {
            if ($this->getCargoEnumType(self::$gruz)) {
                $filter['filter']['CARGO_TYPE.ITEM.XML_ID'] = $this->getCargoEnumType(self::$gruz);
            }
        }

        return $filter;
    }

    public function setPageData(): void
    {
        $data = $this->dataProvider->getElements($this->getFilter());
        $this->elements = $data;
        foreach ($data as $item) {
            if ($this->name == $item['CITY_FROM'] . '-' . $item['COUNTRY_TO']) {
                $this->data['FROM'] = $item['CITY_FROM'];
                $this->data['FROM_ORIGIN'] = $item['CITY_FROM_ORIGIN'];
                $this->data['TO'] = $item['COUNTRY_TO'];
                $this->data['TO_ORIGIN'] = $item['COUNTRY_TO_ORIGIN'];
                $this->data['COUNTRY_FROM'] = $item['COUNTRY_FROM'];
                $this->data['COUNTRY_FROM_ORIGIN'] = $item['COUNTRY_FROM_ORIGIN'];
                $this->data['COUNTRY_TO'] = $item['COUNTRY_TO'];
                $this->data['COUNTRY_TO_ORIGIN'] = $item['COUNTRY_TO_ORIGIN'];
            }
        }
    }

    public function getNavChain(): array
    {
        $chain = [];
        $dir = '/' . self::$dir . '/';
        $name = $this->data['FROM_ORIGIN'] . '-' . $this->data['TO_ORIGIN'];
        $url = $this->name;
        $chain[] = [
            'url' => "{$dir}{$url}/",
            'name' => $name
        ];
        if (self::$gruz) {
            $gruzName = CargoNames::getName(self::$gruz);
            $name = $this->name;
            $gruz = self::$gruz;
            $chain[] = [
                'url' => "{$dir}{$name}/{$gruz}/",
                'name' => $gruzName
            ];
        }

        return $chain;
    }

    function getTitle(): string
    {
        $titleFormat = 'Расчет стоимости доставки из %s в %s от компании OnlogSystem';
        $from = $this->data['FROM_ORIGIN_IZ'];
        $to = $this->data['TO_ORIGIN_V'];

        return sprintf($titleFormat, $from, $to);
    }

    function getHeader(): string
    {
        $from = $this->data['FROM_ORIGIN'];
        $to = $this->data['TO_ORIGIN'];
        if (self::$gruz) {
            $gruzName = CargoNames::getName(self::$gruz . '_ih');
            $headerOneFormat = 'Доставка %s  по маршруту %s - %s';

            return sprintf($headerOneFormat, $gruzName, $from, $to);
        } else {
            $headerOneFormat = 'Доставка разных типов тип груза по маршруту %s - %s';

            return sprintf($headerOneFormat, $from, $to);
        }
    }

    function getDescription(): string
    {
        $descriptionFormat = "<p>С помощью %s вы можете рассчитать стоимость доставки по маршруту %s - %s, расходы на таможенное оформление и дополнительные услуги ВЭД,  а также оформить заявку и контролировать процесс оказания услуги в рамках единой цифровой экосистемы.
        </p>
        <p>
            Онлайн калькулятор стоимости международной доставки грузов позволяет рассчитать доставку и затраты на перевозку товаров по маршруту %s - %s. 
В соответствующих полях калькулятора введите города отправки и получения товара. Выберите вариант доставки до/от терминала перевозчика, либо до/от склада. Если необходимо учесть погрузочно-разгрузочные затраты и прочие расходы на терминале, включите галочку \"Терминальные расходы\". Стоимость доставки груза зависит от многих параметров.
Выберите тип груза: FCL - контейнер 20 DC или 40 HC, LCL - сборный груз, AVIA - авиа доставку или AVTO - автомобильный груз.
        </p>
        <p>
Введите параметры груза - вес, объем и габариты. Укажите дату отгрузки. Нажмите на кнопку \"Поиск\".
Калькулятор перевозок в течение нескольких секунд подберет результаты расчета стоимости доставки груза из %s в %s, среди которых можно выбрать самые лучшие варианты по цене и стоимости. Используйте калькулятор грузоперевозок, чтобы сориентироваться и подобрать оптимальный вариант даже без менеджера.
Если потребуется таможенной оформление и страхование груза, то вы сможете выбрать соответствующие дополнительные услуги в других разделах калькулятора.
Советы по пользованию калькулятором можно найти на странице %s.
        </p>";

        return sprintf($descriptionFormat,
            $this->createCalculatorLink(),
            $this->createLinkFrom(),
            $this->createLinkTo(),
            $this->createLinkFrom(),
            $this->createLinkTo(),
            $this->createLinkFromIz(),
            $this->createLinkToV(),
            $this->createInstructionLink());
    }

    public function getInfoSection(): string
    {
        $infoFormat = '<div class="info-section">
        <h2 style="margin: auto">Наглядная карта доставки</h2>
        <div class="info-item mr-s">
            <span class="text">Ниже представлены предварительные варианты маршрутов, которые мы рассчитали и  отобрали наилучшие с точки зрения цены цены и сроков доставки. Пожалуйста, ознакомьтесь с ними. База данных обновляется ежедневно, поэтому рекомендуем произвести точный расчет с помощью %s для получения лучших предложений по доставке вашего груза.</span>
        </div>
        <div class="info-item mr-m">
            <span class="text">Расписание ближайших вариантов доставки из %s в %s можно посмотреть, выбрав подходящий вариант в результатах расчета или на странице Расписание, уточнив параметры маршрута и тип груза, требующего перевозки. </span>
        </div>
    </div>';
        return sprintf($infoFormat,
            $this->createCalculatorLink('калькулятора'),
            $this->createLinkFromIz(),
            $this->createLinkToCountryV());
    }

    function getPopularRoutes(): array
    {
        $query = $this->dataProvider->getIblockHelper()->getQuery();
        $query->setSelect([
            new \Bitrix\Main\Entity\ExpressionField(
                'COUNT',
                "COUNT(*)",
            ),
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
                [
                    '!CITY_FROM.VALUE' => $this->data['FROM'],
                    'COUNTRY_FROM.VALUE' => $this->data['COUNTRY_FROM'],
                    'COUNTRY_TO.VALUE' => $this->data['COUNTRY_TO'],
                ]
            )
            ->setGroup(['CARGO_TYPE.ITEM.VALUE'])
            ->setOrder(['COUNT' => 'DESC']);

        $data = $this->dataProvider->getCargoElementsGroup($query);

        $result['POPULAR_FROM']['DATA'] = $data;
        $result['POPULAR_FROM']['LINK'] = 'из ' . $this->createLinkFromCountryIz() . ' в ' . $this->createLinkToCountryV();

        return $result;
    }

    public function getTransportationIntegration(): array
    {
        $countryTo = $this->data['TO'];
        $res = [
            'FROM' => [
                'LINK' => $this->createLinkFrom(),
                'COUNTRY' => $this->data['COUNTRY_FROM'],
                'COUNTRY_ORIGIN' => $this->data['FROM_ORIGIN'],
                'CITIES' => []
            ],
            'TO' => [
                'LINK' => $this->createLinkTo(),
                'COUNTRY' => $countryTo,
                'COUNTRY_ORIGIN' => $this->data['TO_ORIGIN'],
                'CITIES' => []
            ],
        ];

        $res['TO']['CITIES'] = $this->dataProvider->getCitiesByCountry($countryTo);;

        return $res;
    }
}