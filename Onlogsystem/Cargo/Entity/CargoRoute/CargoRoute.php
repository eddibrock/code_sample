<?php

namespace Onlogsystem\Cargo\Entity\CargoRoute;

/**
 * Объект маршрута, по которому перевозится груз.
 * Содержит данные по этапам перевозки, а так же по аренде контейнера,
 * если она требуется для перевозки.
 * В случае, когда используется перевозка сборных грузов, аренда контейнеров
 * отсутствует.
 */
class CargoRoute
{
    /**
     * Содержит этапы перевозки для маршрута. Каждый этап содержит
     * данные плеча, по которому везется груз, терминалы отправления
     * и назначения, а так же данные по стоимости перевозки и надбавкам
     * на данном этапе.
     *
     * @var CargoRouteStep[] $steps
     */
    private array $steps;

    /**
     * Идентификатор маршрута
     *
     * @var string
     */
    private string $routeId;

    /**
     * Полная стоимость перевозки по маршруту
     *
     * @var float
     */
    private float $fullPrice;

    /**
     * Срок перевозки группы маршрутов. Значение задается в днях.
     *
     * @var int
     */
    private int $deliveryTime;

    /**
     * Конструктор DTO
     *
     * @param CargoRouteStep[] $steps
     * @param string $routeId
     * @param float $fullPrice
     * @param int $deliveryTime
     */
    public function __construct(
        array  $steps,
        string $routeId,
        float  $fullPrice,
        int    $deliveryTime,
    )
    {
        usort($steps, function (CargoRouteStep $a, CargoRouteStep $b) {
            return $a->getStepNumber() < $b->getStepNumber() ? 1 : -1;
        });

        $this->steps = $steps;
        $this->routeId = $routeId;
        $this->fullPrice = $fullPrice;
        $this->deliveryTime = $deliveryTime;
    }

    /**
     * Полная стоимость перевозки по маршруту
     *
     * @return float
     */
    public function getFullPrice(): float
    {
        return $this->fullPrice;
    }

    /**
     * Полная стоимость перевозки по маршруту
     *
     * @return float
     */
    public function getDeliveryTime(): int
    {
        return $this->deliveryTime;
    }


    /**
     * Идентификатор маршрута
     *
     * @return string
     */
    public function getRouteId(): string
    {
        return $this->routeId;
    }

    /**
     * Содержит этапы перевозки для маршрута. Каждый этап содержит
     * данные плеча, по которому везется груз, терминалы отправления
     * и назначения, а так же данные по стоимости перевозки и надбавкам
     * на данном этапе.
     *
     * @return CargoRouteStep[]
     */
    public function getSteps(): array
    {
        return array_map(function (CargoRouteStep $step) {
            return $step->clone();
        }, $this->steps);
    }

    public function getDeliveryTypes(): array
    {
        $shoulderTypes = [];
        foreach ($this->steps as $step) {
            $shoulderTypes[] = $step->getShoulder()->getShoulderType();
        }

        return $shoulderTypes;
    }

    /**
     * Метод выполняет инициализацию DTO по переданным данным,
     * полученным из API модуля.
     *
     * @param array $data
     *
     * @return self
     */
    public static function makeFromData(array $data): self
    {
        return new self(
            steps: array_key_exists('steps', $data) && is_array($data['steps'])
                ? array_map(function ($item) {
                    return CargoRouteStep::makeFromArray($item);
                }, $data['steps'])
                : [],
            routeId: array_key_exists('routeId', $data) ? strval($data['routeId']) : '0',
            fullPrice: array_key_exists('fullPrice', $data) ? floatval($data['fullPrice']) : 0,
            deliveryTime: array_key_exists('deliveryTime', $data) ? intval($data['deliveryTime']) : 0,
        );
    }

    /**
     * Функция выполняет клонирование DTO. Возвращает новый объект.
     * Используется для исключения наведенных модификаций данных.
     *
     * @return self
     */
    public function clone(): self
    {
        return new self(
            array_map(function (CargoRouteStep $step) {
                return $step->clone();
            }, $this->steps),
            $this->routeId,
            $this->fullPrice,
            $this->deliveryTime,
        );
    }
}