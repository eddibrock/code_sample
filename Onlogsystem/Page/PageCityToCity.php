<?php

namespace Onlogsystem\Page;

use Fsd\Onlog\Integration\Table;
use Onlogsystem\Cargo\CargoNames;
use Onlogsystem\Cargo\CargoSingleton;
use PhpParser\Node\Stmt\If_;

class PageCityToCity extends AbstractPage
{
    protected static string $type = 'city_city';

    public function __construct(PageUrl $url)
    {
        parent::__construct();
        self::$url = $url;
        $this->name = $url->getLevelOne();
        self::$gruz = $url->getGruz();
        $this->setPageData();
        if (self::$url->getGruz()) {
            self::$type = 'city_city_gruz';
        }
        if ($this->data != []) {
            $this->setCase();
        }
    }

    function getUrlFrom(): string
    {
        $format = "/" . self::$dir . "/%s/%s/";
        return sprintf($format, $this->data['COUNTRY_FROM'], $this->data['FROM']);
    }

    function getUrlTo(): string
    {
        $format = "/" . self::$dir . "/%s/%s/";
        return sprintf($format, $this->data['COUNTRY_TO'], $this->data['TO']);
    }

    function getFilter(): array
    {
        $filter = [
            'filter' => [
                'URL_LEVEL_TWO.VALUE' => $this->name,
            ],
        ];
        if (self::$includeGruz && self::$gruz) {
            if ($this->getCargoEnumType(self::$gruz)) {
                $filter['filter']['CARGO_TYPE.ITEM.XML_ID'] = $this->getCargoEnumType(self::$gruz);
            }
        }
        return $filter;
    }

    function getTitle(): string
    {
        $titleFormat = 'Расчет стоимости доставки из %s в %s от компании OnlogSystem';
        $from = $this->data['FROM_ORIGIN_IZ'];
        $to = $this->data['TO_ORIGIN_V'];

        return sprintf($titleFormat, $from, $to);
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
            $name = $this->name;
            $gruz = self::$gruz;
            $chain[] = [
                'url' => "{$dir}{$name}/{$gruz}/",
                'name' => $this->data['GRUZ']
            ];
        }

        return $chain;
    }

    function getHeader(): string
    {
        $from = $this->data['FROM_ORIGIN_IZ'];
        $to = $this->data['TO_ORIGIN_V'];
        if (self::$gruz) {
            $gruzName = CargoNames::getName(self::$gruz . '_ih');
            $headerOneFormat = 'Доставка %s из %s в %s';

            return sprintf($headerOneFormat, $gruzName, $from, $to);
        } else {
            $headerOneFormat = 'Доставка грузов из %s в %s';

            return sprintf($headerOneFormat, $from, $to);
        }
    }

    function getDescription(): string
    {
        if (self::$gruz) {
            $descriptionFormat = "<p>С помощью %s вы можете рассчитать стоимость доставки по маршруту %s - %s, расходы на таможенное оформление и дополнительные услуги ВЭД,  а также оформить заявку и контролировать процесс оказания услуги в рамках единой цифровой экосистемы.
        </p>
        <p>
            Для расчета стоимости доставки из %s в %s заполните соответствующие поля логистического калькулятора, выберите условия поставки - терминал или склад, необходимость включения погрузо-разгрузочных работ на терминале, тип груза %s и все остальные обязательные поля. Также заполните дополнительные поля (количество мест, классификация груза**, температурный режим, категорию опасности груза) необходимые для более точного анализа вариантов маршрутов и оценки стоимости международной доставки из %s в %s. Если потребуется маркировка груза, складские услуги, консультация по таможенным вопросам, то вы сможете выбрать соответствующие дополнительные услуги в других разделах калькулятора. Подробную информацию можно найти на странице %s.
        </p>";

            return sprintf($descriptionFormat,
                $this->createCalculatorLink(),
                $this->createLinkFrom(),
                $this->createLinkTo(),
                $this->createLinkFromIz(),
                $this->createLinkToV(),
                $this->getAllCargoTypes(),
                $this->createLinkFromCountryIz(),
                $this->createLinkToCountryV(),
                $this->createInstructionLink());
        } else {
            $descriptionFormat = "<p>С помощью %s вы можете рассчитать стоимость доставки по маршруту %s - %s, расходы на таможенное оформление и дополнительные услуги ВЭД,  а также оформить заявку и контролировать процесс оказания услуги в рамках единой цифровой экосистемы.
        </p>
        <p>
            Для расчета цены и дополнительных расходов на транспортные доставки из %s в %s заполните соответствующие поля логистического калькулятора, выберите условия поставки - терминал или склад, необходимость включения погрузо-разгрузочных работ на терминале, тип груза %s, выберите дату отправки, введите вес отправления (кг), габариты (см) и объем (куб.м). Также заполните дополнительные поля (количество мест, классификация груза**, температурный режим, категорию опасности груза), чтобы рассчитать стоимость грузоперевозки и для анализа вариантов маршрутов из %s в %s. Подробную информацию можно найти на странице %s.
        </p>";

            return sprintf($descriptionFormat,
                $this->createCalculatorLink(),
                $this->createLinkFrom(),
                $this->createLinkTo(),
                $this->createLinkFromIz(),
                $this->createLinkToV(),
                $this->getAllCargoTypes(),
                $this->createLinkFromIz(),
                $this->createLinkToV(),
                $this->createInstructionLink());
        }
    }

    public function getInfoSection(): string
    {
        $infoFormat = '<div class="info-section">
        <h2 style="margin: auto">Карта доставки</h2>
        <div class="info-item mr-s">
            <span class="text">Ниже представленны лучшие по цене и срокам варианты доставки различных видов грузов, которые предварительно были рассчитаны %s.</span>
        </div>
        <div class="info-item mr-m">
            <span class="text">Расписание ближайших вариантов доставки из %s в %s можно посмотреть, выбрав подходящий вариант в результатах расчета или на странице Расписание, уточнив параметры маршрута и тип груза, требующего перевозки. </span>
        </div>
    </div>';
        return sprintf($infoFormat,
            $this->createCalculatorLinkOm(),
            $this->createLinkFromIz(),
            $this->createLinkToV());
    }

    function getPopularRoutes(): array
    {
        //TODO добавить runtime https://dev.1c-bitrix.ru/community/forums/forum6/topic148058/

        $query = $this->dataProvider->getIblockHelper()->getQuery();
        $query->setSelect([
            new \Bitrix\Main\Entity\ExpressionField(
                'COUNT',
                "COUNT(*)",
            ),
//            'URL_LEVEL_TWO.VALUE',
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
                    'CITY_FROM.VALUE' => $this->data['FROM'],
                    'COUNTRY_TO.VALUE' => $this->data['COUNTRY_TO'],
                    '!URL_LEVEL_TWO.VALUE' => $this->name
                ]
            )
            ->setGroup(['CARGO_TYPE.ITEM.VALUE'])
            ->setOrder(['COUNT' => 'DESC']);

        $data = $this->dataProvider->getCargoElementsGroup($query);

        $result['POPULAR_FROM']['DATA'] = $data;
        $result['POPULAR_FROM']['LINK'] = 'из ' . $this->createLinkFromIz() . ' в ' . $this->createLinkToCountryV();

        unset($query);
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
                [
                    'COUNTRY_FROM.VALUE' => $this->data['COUNTRY_FROM'],
                    'CITY_TO.VALUE' => $this->data['TO'],
                    '!URL_LEVEL_TWO.VALUE' => $this->name
                ]
            )
            ->setGroup(['URL_LEVEL_TWO.VALUE', 'CARGO_TYPE.ITEM.VALUE'])
            ->setOrder(['COUNT' => 'DESC']);

        $data = $this->dataProvider->getCargoElementsGroup($query);

        $result['POPULAR_TO']['DATA'] = $data;
        $result['POPULAR_TO']['LINK'] = 'из ' . $this->createLinkFromCountryIz() . ' в ' . $this->createLinkToV();

        return $result;
    }

    function getTransportationIntegration(): array
    {
        $res = [
            'FROM' => [
                'LINK' => $this->createLinkFromCountry(),
                'COUNTRY' => $this->data['COUNTRY_FROM'],
                'COUNTRY_ORIGIN' => $this->data['COUNTRY_FROM_ORIGIN'],
                'CITIES' => [
                    ['CITY' => $this->data['FROM'],
                        'CITY_ORIGIN' => $this->data['FROM_ORIGIN'],
                        'COUNTRY' => $this->data['COUNTRY_FROM']
                    ],
                ]
            ],
            'TO' => [
                'LINK' => $this->createLinkToCountry(),
                'COUNTRY' => $this->data['COUNTRY_TO'],
                'COUNTRY_ORIGIN' => $this->data['COUNTRY_TO_ORIGIN'],
                'CITIES' => [
                    ['CITY' => $this->data['TO'],
                        'CITY_ORIGIN' => $this->data['TO_ORIGIN'],
                        'COUNTRY' => $this->data['COUNTRY_TO']
                    ],
                ]
            ],
        ];

        return $res;
    }
}