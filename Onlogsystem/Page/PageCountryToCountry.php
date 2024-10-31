<?php

namespace Onlogsystem\Page;

use Fsd\Onlog\Integration\Table;
use Onlogsystem\Cargo\CargoNames;
use Onlogsystem\Cargo\CargoSingleton;

class PageCountryToCountry extends AbstractPage
{
    protected static string $type = 'country_country';

    public function __construct(PageUrl $url)
    {
        parent::__construct();
        $this->name = $url->getLevelOne();
        self::$gruz = $url->getGruz();
        $this->setPageData();
        if (self::$url->getGruz()) {
            self::$type = 'country_country_gruz';
        }
        if ($this->data != []) {
            $this->setCase();
        } else {
            self::$type = 'empty';
        }
    }

    function getFilter(): array
    {
        $filter = [
            'filter' => [
                'URL_LEVEL_ONE.VALUE' => $this->name
            ],
        ];
        if (self::$gruz) {
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

    function getNavChain(): array
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
        $descriptionFormat = "<p>С помощью %s вы можете рассчитать стоимость доставки по маршруту %s - %s, расходы на таможенное оформление и дополнительные услуги ВЭД,  а также оформить заявку и контролировать процесс оказания услуги в рамках единой цифровой экосистемы.
        </p>
        <p>
            Для точного расчета расходов на перевозку из %s в %s заполните необходимые поля онлайн калькулятора доставки. В полях \"Пункт отправления\" и \"Пункт назначения\" введите названия городов. Выберите варианты отгрузки и получения товара - терминал или склад, при необходимости включите терминальные расходы. Определитесь с типом груза (контейнер 20-фут. или 40-фут., сборный груз, авиа или автомобильный груз). После чего заполните все остальные обязательные поля. Чтобы произвести более точный онлайн расчет доставки груза, возможных вариантов и цены доставки из %s в %s заполните дополнительные поля (количество мест, классификация груза**, температурный режим, категорию опасности груза). Если потребуется маркировка груза, складские услуги, консультация по таможенным вопросам, то вы сможете выбрать соответствующие дополнительные услуги в других разделах калькулятора.
Подробная информация см. на странице %s.
        </p>";

        return sprintf($descriptionFormat,
            $this->createCalculatorLink(),
            $this->createLinkFrom(),
            $this->createLinkTo(),
            $this->createLinkFromIz(),
            $this->createLinkToV(),
            $this->createLinkFromIz(),
            $this->createLinkToV(),
            $this->createInstructionLink());
    }

    public function getInfoSection(): string
    {
        $infoFormat = '<div class="info-section">
        <h2 style="margin: auto">Наглядная карта доставки</h2>
        <div class="info-item mr-s">
            <span class="text">Мы уже рассчитали для вас предварительные варианты маршрутов перевозки всех типов грузов, отобрали лучшие по цене и срокам доставки. Можете ознакомиться с ними ниже.</span>
        </div>
        <div class="info-item mr-m">
            <span class="text">Расписание ближайших вариантов доставки из %s в %s можно посмотреть, выбрав подходящий вариант в результатах расчета или на странице Расписание, уточнив параметры маршрута и тип груза, требующего перевозки. </span>
        </div>
    </div>';
        return sprintf($infoFormat,
            $this->createLinkFromIz(),
            $this->createLinkToV());
    }

    function getTopCountry(): array
    {
        $filter = [
            'filter' => [
                ['LOGIC' => 'OR',
                    ['=COUNTRY_FROM.VALUE' => $this->name],
                    ['=COUNTRY_TO.VALUE' => $this->name]
                ],
            ],
        ];
        $data = [];
        $collection = $this->dataProvider->getElements($filter);

        foreach ($collection as $item) {
            // Если страна ИЗ то нужно взять страну В
            if ($item['COUNTRY_FROM'] == $this->name) {
                $country = $item['COUNTRY_TO'];
                $localId = $item['LOCAL_COUNTRY_TO'];
                $origin = $item['COUNTRY_TO_ORIGIN'];
            } else {
                $country = $item['COUNTRY_FROM'];
                $localId = $item['LOCAL_COUNTRY_FROM'];
                $origin = $item['COUNTRY_FROM_ORIGIN'];

            }
            $data[$localId] = [
                'NAME' => $country,
                'ORIGIN' => $origin,
            ];
        }


        $population = $this->dataProvider->getPopulation(array_keys($data));

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

    function getTransportationIntegration(): array
    {
        $countryFrom = $this->data['FROM'];
        $countryTo = $this->data['TO'];
        $res = [
            'FROM' => [
                'LINK' => $this->createLinkFrom(),
                'COUNTRY' => $countryFrom,
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
        $dataCityFrom = $this->dataProvider->getCitiesByCountry($countryFrom);
        $dataCityTo = $this->dataProvider->getCitiesByCountry($countryTo);

        $res['FROM']['CITIES'] = $dataCityFrom;
        $res['TO']['CITIES'] = $dataCityTo;

        return $res;
    }
}