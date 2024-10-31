<?php

namespace Onlogsystem\Cargo\Entity\CargoShoulder;

/**
 * DTO данных плеча, по которому идет перевозка груза
 */
class CargoTransportShoulder
{
    /**
     * Идентификатор перевозчика, который везет по данному плечу.
     * Найти сущности можно в RouteLibrary.
     *
     * @var string
     */
    private string $carrierId;

    /**
     * Идентификатор подрядчика, который отвечает за перевозку по плечу.
     * Найти сущности можно в RouteLibrary.
     *
     * @var string
     */
    private string $contractorId;

    /**
     * Расстояние перевозки по плечу в единицах измерения $distanceUnit.
     *
     * @var int
     */
    private int $distance;

    /**
     * Единицы измерения расстояния для плеча.
     * Найти сущности можно в RouteLibrary.
     *
     * @var string
     */
    private string $distanceUnit;

    /**
     * Идентификаторы локаций, из которых идет перевозка по плечу.
     * Может быть не задано, если перевозка идет от терминалов.
     * Найти сущности можно в RouteLibrary.
     *
     * @var array<string> $fromLocationIds
     */
    private array $fromLocationIds;

    /**
     * Идентификаторы терминалов, из которых идет перевозка по плечу.
     * Может быть не задано, если перевозка идет из локаций.
     * Найти сущности можно в RouteLibrary.
     *
     * @var array<string> $fromTerminalIds
     */
    private array $fromTerminalIds;

    /**
     * Идентификатор плеча.
     *
     * @var string
     */
    private string $id;

    /**
     * Идентификатор типа плеча. Получить сущность можно из RouteLibrary.
     *
     * @var string
     */
    private string $shoulderType;

    /**
     * Идентификаторы локаций, до которых идет перевозка по плечу.
     * Может быть не задано, если перевозка идет до терминалов.
     * Найти сущности можно в RouteLibrary.
     *
     * @var array<string> $toLocationIds
     */
    private array $toLocationIds;

    /**
     * Идентификаторы терминалов, до которых идет перевозка по плечу.
     * Может быть не задано, если перевозка идет до локаций.
     * Найти сущности можно в RouteLibrary.
     *
     * @var array<string> $toTerminalIds
     */
    private array $toTerminalIds;

    /**
     * Есть особый тип плеча - мультимодальное. Это плечо состоит из нескольких
     * этапов перевозки. Эти этапы содержатся в данном массиве. Массив пуст
     * для всех остальных типов плеч.
     *
     * @var array<TransportShoulderStep> $shoulderSteps
     */
    private array $shoulderSteps;

    /**
     * Конструктор DTO
     *
     * @param string $carrierId
     * @param string $contractorId
     * @param int    $distance
     * @param string $distanceUnit
     * @param array  $fromLocationIds
     * @param array  $fromTerminalIds
     * @param string $id
     * @param string $shoulderType
     * @param array  $toLocationIds
     * @param array  $toTerminalIds
     * @param array  $shoulderSteps
     */
    public function __construct(
        string $carrierId,
        string $contractorId,
        int    $distance,
        string $distanceUnit,
        array  $fromLocationIds,
        array  $fromTerminalIds,
        string $id,
        string $shoulderType,
        array  $toLocationIds,
        array  $toTerminalIds,
        array  $shoulderSteps,
    )
    {
        $this->carrierId = $carrierId;
        $this->contractorId = $contractorId;
        $this->distance = $distance;
        $this->distanceUnit = $distanceUnit;
        $this->fromLocationIds = $fromLocationIds;
        $this->fromTerminalIds = $fromTerminalIds;
        $this->id = $id;
        $this->shoulderType = $shoulderType;
        $this->toLocationIds = $toLocationIds;
        $this->toTerminalIds = $toTerminalIds;
        $this->shoulderSteps = $shoulderSteps;
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
            carrierId: array_key_exists('carrier_id', $data) ? strval($data['carrier_id']) : "",
            contractorId: array_key_exists('contractor_id', $data) ? strval($data['contractor_id']) : "",
            distance: array_key_exists('distance', $data) ? intval($data['distance']) : 0,
            distanceUnit: array_key_exists('distance_unit', $data) ? strval($data['distance_unit']) : "",
            fromLocationIds: array_key_exists('from_location_ids', $data) && is_array($data['from_location_ids'])
                ? array_map(function ($id) {
                    return strval($id);
                }, $data['from_location_ids'])
                : [],
            fromTerminalIds: array_key_exists('from_terminal_ids', $data) && is_array($data['from_terminal_ids'])
                ? array_map(function ($id) {
                    return strval($id);
                }, $data['from_terminal_ids'])
                : [],
            id: array_key_exists('id', $data) ? strval($data['id']) : '0',
            shoulderType: array_key_exists('shoulder_type', $data) ? strval($data['shoulder_type']) : "",
            toLocationIds: array_key_exists('to_location_ids', $data) && is_array($data['to_location_ids'])
                ? array_map(function ($id) {
                    return strval($id);
                }, $data['to_location_ids'])
                : [],
            toTerminalIds: array_key_exists('to_terminal_ids', $data) && is_array($data['to_terminal_ids'])
                ? array_map(function ($id) {
                    return strval($id);
                }, $data['to_terminal_ids'])
                : [],
            shoulderSteps: array_key_exists('shoulderSteps', $data) && is_array($data['shoulderSteps'])
                ? array_map(function ($data) {
                    return TransportShoulderStep::makeFromArray($data);
                }, $data['shoulderSteps'])
                : [],
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
            $this->carrierId,
            $this->contractorId,
            $this->distance,
            $this->distanceUnit,
            [...$this->fromLocationIds],
            [...$this->fromTerminalIds],
            $this->id,
            $this->shoulderType,
            [...$this->toLocationIds],
            [...$this->toTerminalIds],
            array_map(function (TransportShoulderStep $step) {
                return $step->clone();
            }, $this->shoulderSteps),
        );
    }

    /**
     * Идентификатор перевозчика, который везет по данному плечу.
     * Найти сущности можно в RouteLibrary.
     *
     * @return string
     */
    public function getCarrierId(): string
    {
        return $this->carrierId;
    }

    /**
     * Идентификатор подрядчика, который отвечает за перевозку по плечу.
     * Найти сущности можно в RouteLibrary.
     *
     * @return string
     */
    public function getContractorId(): string
    {
        return $this->contractorId;
    }

    /**
     * Расстояние перевозки по плечу в единицах измерения $distanceUnit.
     *
     * @return int
     */
    public function getDistance(): int
    {
        return $this->distance;
    }

    /**
     * Единицы измерения расстояния для плеча.
     * Найти сущности можно в RouteLibrary.
     *
     * @return string
     */
    public function getDistanceUnit(): string
    {
        return $this->distanceUnit;
    }

    /**
     * Идентификаторы локаций, из которых идет перевозка по плечу.
     * Может быть не задано, если перевозка идет от терминалов.
     * Найти сущности можно в RouteLibrary.
     *
     * @return array<string>
     */
    public function getFromLocationIds(): array
    {
        return [...$this->fromLocationIds];
    }

    /**
     * Идентификаторы терминалов, из которых идет перевозка по плечу.
     * Может быть не задано, если перевозка идет из локаций.
     * Найти сущности можно в RouteLibrary.
     *
     * @return array<string>
     */
    public function getFromTerminalIds(): array
    {
        return [...$this->fromTerminalIds];
    }

    /**
     * Идентификатор плеча.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Идентификатор типа плеча. Получить сущность можно из RouteLibrary.
     *
     * @return string
     */
    public function getShoulderType(): string
    {
        return $this->shoulderType;
    }

    /**
     * Идентификаторы локаций, до которых идет перевозка по плечу.
     * Может быть не задано, если перевозка идет до терминалов.
     * Найти сущности можно в RouteLibrary.
     *
     * @return array<string>
     */
    public function getToLocationIds(): array
    {
        return [...$this->toLocationIds];
    }

    /**
     * Идентификаторы терминалов, до которых идет перевозка по плечу.
     * Может быть не задано, если перевозка идет до локаций.
     * Найти сущности можно в RouteLibrary.
     *
     * @return array<string>
     */
    public function getToTerminalIds(): array
    {
        return [...$this->toTerminalIds];
    }

    /**
     * Есть особый тип плеча - мультимодальное. Это плечо состоит из нескольких
     * этапов перевозки. Эти этапы содержатся в данном массиве. Массив пуст
     * для всех остальных типов плеч.
     *
     * @return array<TransportShoulderStep>
     */
    public function getShoulderSteps(): array
    {
        return array_map(function (TransportShoulderStep $step) {
            return $step->clone();
        }, $this->shoulderSteps);
    }
}