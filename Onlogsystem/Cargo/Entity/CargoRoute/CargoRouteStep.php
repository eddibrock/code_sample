<?php

namespace Onlogsystem\Cargo\Entity\CargoRoute;


use Onlogsystem\Cargo\Entity\CargoShoulder\CargoTransportShoulder;
use Onlogsystem\Cargo\Entity\CargoTerminals\CargoTransportTerminal;

class CargoRouteStep
{
    /**
     * Конечный терминал, куда пребывает груз по текущему шагу маршрута.
     * Может быть не заполнен для случая, когда доставка выполняется до
     * склада заказчика в последнем шаге маршрута.
     *
     * @var CargoTransportTerminal|null
     */
    private null|CargoTransportTerminal $endTerminal;

    /**
     * Плечо перевозки, по которому груз перевозится с начального терминала на
     * конечный. Содержит основные данные по перевозке.
     *
     * @var CargoTransportShoulder
     */
    private CargoTransportShoulder $shoulder;

    /**
     * Терминал отправления для этапа перевозки. Может быть не заполнен,
     * если доставка выполняется от склада заказчика на первом шаге маршрута.
     *
     * @var CargoTransportTerminal|null
     */
    private null|CargoTransportTerminal $startTerminal;

    /**
     * Порядковый номер этапа перевозки. Используется для сохранения порядка
     * этапов. Пример: 1) Море 2) Жд
     *
     * @var int
     */
    private int $stepNumber;

    /**
     * Конструктор DTO
     *
     * @param CargoTransportTerminal|null $endTerminal
     * @param CargoTransportShoulder $shoulder
     * @param CargoTransportTerminal|null $startTerminal
     * @param int $stepNumber
     */
    public function __construct(
        ?CargoTransportTerminal $endTerminal,
        CargoTransportShoulder  $shoulder,
        ?CargoTransportTerminal $startTerminal,
        int                     $stepNumber,
    )
    {
        $this->endTerminal = $endTerminal;
        $this->shoulder = $shoulder;
        $this->startTerminal = $startTerminal;
        $this->stepNumber = $stepNumber;
    }

    /**
     * Метод выполняет инициализацию DTO по переданным данным,
     * полученным из API модуля.
     *
     * @param array $data
     *
     * @return self
     */
    public static function makeFromArray(array $data): self
    {
        return new self(
            endTerminal: array_key_exists('endTerminal', $data) && is_array($data['endTerminal'])
                ? CargoTransportTerminal::makeFromArray($data['endTerminal'])
                : null,
            shoulder: CargoTransportShoulder::makeFromArray($data['shoulder']),
            startTerminal: array_key_exists('startTerminal', $data) && is_array($data['startTerminal'])
                ? CargoTransportTerminal::makeFromArray($data['startTerminal'])
                : null,
            stepNumber: array_key_exists('stepNumber', $data) ? intval($data['stepNumber']) : 0,
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
            $this->endTerminal?->clone(),
            $this->shoulder->clone(),
            $this->startTerminal?->clone(),
            $this->stepNumber,
        );
    }

    /**
     * Конечный терминал, куда пребывает груз по текущему шагу маршрута.
     * Может быть не заполнен для случая, когда доставка выполняется до
     * склада заказчика в последнем шаге маршрута.
     *
     * @return CargoTransportTerminal|null
     */
    public function getEndTerminal(): ?CargoTransportTerminal
    {
        return $this->endTerminal;
    }

    /**
     * Плечо перевозки, по которому груз перевозится с начального терминала на
     * конечный. Содержит основные данные по перевозке.
     *
     * @return CargoTransportShoulder
     */
    public function getShoulder(): CargoTransportShoulder
    {
        return $this->shoulder;
    }

    /**
     * Терминал отправления для этапа перевозки. Может быть не заполнен,
     * если доставка выполняется от склада заказчика на первом шаге маршрута.
     *
     * @return CargoTransportTerminal|null
     */
    public function getStartTerminal(): ?CargoTransportTerminal
    {
        return $this->startTerminal;
    }

    /**
     * Порядковый номер этапа перевозки. Используется для сохранения порядка
     * этапов. Пример: 1) Море 2) Жд
     *
     * @return int
     */
    public function getStepNumber(): int
    {
        return $this->stepNumber;
    }
}