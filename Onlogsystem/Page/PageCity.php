<?php

namespace Onlogsystem\Page;

class PageCity extends AbstractPage
{
    private static string $country;
    protected static string $type = 'city';

    public function __construct(PageUrl $url)
    {
        parent::__construct();
        $this->name = $url->getLevelTwo();
        self::$country = $url->getLevelOne();
        self::$gruz = $url->getGruz();
        self::$url = $url;
        $this->setPageData();
        if ($this->data != []) {
            $this->setCase();
        }
    }

    function getFilter(): array
    {
        return [
            'filter' => [
                'CITY_FROM.VALUE' => $this->name,
                'COUNTRY_FROM.VALUE' => self::$country,
            ],
            'limit' => 1
        ];
    }

    function getTitle(): string
    {
        $titleFormat = 'Расчет стоимости доставки из %s в %s от компании OnlogSystem';
        $from = $this->data['FROM_ORIGIN_IZ'];
        $to = $this->data['FROM_ORIGIN_V'];

        return sprintf($titleFormat, $from, $to);
    }

    function getHeader(): string
    {
        $from = $this->data['FROM_ORIGIN_IZ'];
        $to = $this->data['FROM_ORIGIN_V'];
        $headerOneFormat = 'Доставка грузов из %s и в %s';

        return sprintf($headerOneFormat, $from, $to);
    }

    function getDescription(): string
    {
        $descriptionFormat = "<p>С помощью %s вы можете рассчитать стоимость доставки как из %s, так и в %s, а также расходы на таможенное оформление и дополнительные услуги ВЭД. После этого оформить заявку и контролировать процесс оказания услуги в рамках единой цифровой экосистемы.
        </p>
        <p>
Для расчета расходов на перевозку из %s в %s заполните соответствующие поля калькулятора доставки грузов, выберите условия поставки - терминал или склад, необходимость включения погрузо-разгрузочных работ на терминале, тип груза %s и все остальные обязательные поля (вес отправления (кг), габариты (см) и объем (куб.м)). Также заполните дополнительные поля - количество мест, классификация груза**, температурный режим, категорию опасности груза), необходимые для точного анализа вариантов маршрутов и оценки стоимости международной перевозки. 
Если потребуется инспекция, сертификация, таможенное оформление груза, то перейдите в соответствующие разделы онлайн-калькулятора.
Подробную информацию можно найти на странице %s.
        </p>";

        return sprintf($descriptionFormat,
            $this->createCalculatorLink(),
            $this->createLinkFromIz(),
            $this->createLinkToV(),
            $this->createLinkFromIz(),
            $this->createLinkToV(),
            $this->getAllCargoTypes(),
            $this->createInstructionLink());
    }

    public function getNavChain(): array
    {
        $chain = [];
        $country = $this->data['COUNTRY_FROM'];
        $countryOrigin = $this->data['COUNTRY_FROM_ORIGIN'];
        $city = $this->data['FROM'];
        $cityOrigin = $this->data['FROM_ORIGIN'];
        $format = "/%s/%s/";
        $chain[] = [
            'url' => sprintf($format, self::$dir, $country),
            'name' => $countryOrigin
        ];
        $format = "/%s/%s/%s/";
        $chain[] = [
            'url' => sprintf($format, self::$dir, $country, $city),
            'name' => $cityOrigin
        ];

        return $chain;
    }

    function getTopPartners(): array
    {
        $result = [];
        $filter = [
            'filter' => [
                ['=CITY_FROM.VALUE' => $this->name],
            ],
            'limit' => 10
        ];

        $directionTo = $this->dataProvider->getCountryPartners($filter, 'TO');
        $filter = [
            'filter' => [
                ['=CITY_TO.VALUE' => $this->name],
            ],
            'limit' => 10
        ];

        $directionFrom = $this->dataProvider->getCountryPartners($filter, 'FROM');
        $result['TO'] = $directionTo;
        $result['FROM'] = $directionFrom;

        return $result;
    }
}