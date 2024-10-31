<?php

namespace Onlogsystem\Page;

use Fsd\Onlog\Integration\Table;
use Onlogsystem\Cargo\CargoNames;

class PageCountryToCity extends AbstractPage
{
    protected static string $type = 'country_city';

    public function __construct(PageUrl $url)
    {
        parent::__construct();
        $this->name = $url->getLevelOne();
        self::$gruz = $url->getGruz();
        $this->setPageData();
        if (self::$url->getGruz()) {
            self::$type = 'country_city_gruz';
        }
        if ($this->data != []) {
            $this->setCase();
        } else {
            self::$type = 'empty';
        }
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
                '%=URL_LEVEL_ONE.VALUE' => explode('-', $this->name)[0] . '%',
            ],
        ];
        if ($this->data['FROM']) {
            $filter['filter']['COUNTRY_FROM.VALUE'] = $this->data['FROM'];
        }
        if ($this->data['TO']) {
            $filter['filter']['CITY_TO.VALUE'] = $this->data['TO'];
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
            if ($this->name == $item['COUNTRY_FROM'] . '-' . $item['CITY_TO']) {
                $this->data['FROM'] = $item['COUNTRY_FROM'];
                $this->data['FROM_ORIGIN'] = $item['COUNTRY_FROM_ORIGIN'];
                $this->data['TO'] = $item['CITY_TO'];
                $this->data['TO_ORIGIN'] = $item['CITY_TO_ORIGIN'];
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
        if (self::$gruz) {
            $gruzName = CargoNames::getName(self::$gruz . '_ih');
            $headerOneFormat = 'Доставка %s из %s в %s';

            return sprintf($headerOneFormat, $gruzName, $this->data['FROM_ORIGIN_IZ'], $this->data['TO_ORIGIN_V']);
        } else {
            $headerOneFormat = 'Доставка грузов разного типа по маршруту %s - %s';

            return sprintf($headerOneFormat, $this->data['FROM_ORIGIN'], $this->data['TO_ORIGIN']);
        }
    }

    function getDescription(): string
    {
        $descriptionFormat = "<p>С помощью %s вы можете рассчитать стоимость доставки по маршруту %s - %s, расходы на таможенное оформление и дополнительные услуги ВЭД, а также оформить заявку и контролировать процесс оказания услуги в рамках единой цифровой экосистемы.
        </p>
        <p>
            Для использования калькулятора логистических расходов следуйте инструкции:<br>
1. Введите информацию об отправной точке (город в стране %s) и пункте назначения груза (%s).<br>
2. Выберите необходимые условия доставки - до терминала перевозчика или склада.<br>
3. Укажите тип груза, тип контейнера или вес и размеры грузовой партии.<br>
4. Заполните дополнительные поля (количество мест, классификация груза**, температурный режим, категорию опасности груза).<br>
5. Выберите дату отправки груза.<br>
6. Нажмите кнопку \"Рассчитать\".<br>
<br>
Калькулятор доставки автоматически рассчитает маршрут и стоимость доставки товара на основе предоставленной информации.
Если потребуется страхование груза, складские услуги, консультация по таможенным вопросам, то вы сможете выбрать соответствующие дополнительные услуги в других разделах калькулятора.
Дополнительную информацию можно найти на странице %s.
        </p>";

        return sprintf($descriptionFormat,
            $this->createCalculatorLink(),
            $this->createLinkFrom(),
            $this->createLinkTo(),
            $this->createLinkFrom(),
            $this->createLinkTo(),
            $this->createInstructionLink());
    }

    public function getInfoSection(): string
    {
        $infoFormat = '<div class="info-section">
        <h2 style="margin: auto">Наглядная карта доставки</h2>
        <div class="info-item mr-s">
            <span class="text">Ниже представлены сведения о самых оптимальных маршрутах, которые предварительно были расчитаны онлайн-калькулятором доставки грузов. Маршруты отсортированы по цене и срокам. Остается выбрать подходящий для вас вариант. Если хотите посмотреть все варианты маршрутов, перейдите на страницу %s.</span>
        </div>
        <div class="info-item mr-m">
            <span class="text">Расписание ближайших вариантов доставки из %s в %s можно посмотреть, выбрав подходящий вариант в результатах расчета или на странице Расписание, уточнив параметры маршрута и тип груза, требующего перевозки. </span>
        </div>
    </div>';
        return sprintf($infoFormat,
            $this->createCalculatorLink('Калькулятора доставки'),
            $this->createLinkFromIz(),
            $this->createLinkToV());
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
                    'COUNTRY_FROM.VALUE' => $this->data['COUNTRY_FROM'],
                    'COUNTRY_TO.VALUE' => $this->data['COUNTRY_TO'],
                    '!CITY_TO.VALUE' => $this->data['TO'],
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
        $countryFrom = $this->data['FROM'];
        $res = [
            'FROM' => [
                'LINK' => $this->createLinkFrom(),
                'COUNTRY' => $countryFrom,
                'COUNTRY_ORIGIN' => $this->data['FROM_ORIGIN'],
                'CITIES' => []
            ],
            'TO' => [
                'LINK' => $this->createLinkTo(),
                'COUNTRY' => $this->data['TO'],
                'COUNTRY_ORIGIN' => $this->data['TO_ORIGIN'],
                'CITIES' => []
            ],
        ];

        $res['FROM']['CITIES'] = $this->dataProvider->getCitiesByCountry($countryFrom);

        return $res;
    }
}