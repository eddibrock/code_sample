<?php

namespace Onlogsystem\Page;

use Fsd\Onlog\Integration\Table;

class PageCountry extends AbstractPage
{
    protected static string $type = 'country';

    public function __construct(PageUrl $url)
    {
        parent::__construct();
        $this->name = $url->getLevelOne();
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
                'COUNTRY_FROM.VALUE' => $this->name
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
        $descriptionFormat = "<p>С помощью %s вы можете рассчитать стоимость доставки груза как из %s, так и в %s, а также расходы на таможенное оформление и дополнительные услуги ВЭД. После этого оформить заявку и контролировать процесс оказания услуги в рамках единой цифровой экосистемы.
        </p>
        <p>
            Для точного расчета расходов на перевозку из %s или в %s заполните необходимые поля онлайн калькулятора доставки. В полях \"Пункт отправления\" и \"Пункт назначения\" введите названия городов. Выберите варианты отгрузки и получения товара - терминал или склад, при необходимости включите терминальные расходы. Определитесь с типом груза (контейнер 20-фут. или 40-фут., сборный груз, авиа или автомобильный груз). После чего заполните все остальные обязательные поля - дату, вес и объем отправления,  а также дополнительные поля - количество мест, классификация груза**, температурный режим, категорию опасности груза, чтобы произвести точный онлайн расчет вариантов и стоимости перевозки. 
Если потребуется страхование груза, складские услуги, таможенное оформление, то выбрите соответствующие дополнительные услуги в других разделах калькулятора.
Подробная информация см. на странице %s.
        </p>";

        return sprintf($descriptionFormat,
            $this->createCalculatorLink(),
            $this->data['FROM_ORIGIN_IZ'],
            $this->data['TO_ORIGIN_V'],
            $this->data['FROM_ORIGIN_IZ'],
            $this->data['TO_ORIGIN_V'],
            $this->createInstructionLink());
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

    function getTopTerminals(): array
    {
        return $this->dataProvider->getCitiesByCountry($this->name);
    }
}