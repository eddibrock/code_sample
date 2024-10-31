<?php

namespace Onlogsystem\Cargo\Entity\CargoTerminals;

/**
 * DTO для хранения данных ПРР терминала.
 * Содержит параметры погрузки/разгрузки терминала.
 */
class TransportTerminalLoadingUnloadingOffer
{
    /**
     * Идентификатор предложения с ПРР терминала
     *
     * @var string
     */
    private string $id;

    /**
     * Идентификаторы ценовых предложений надбавок для ПРР терминала.
     *
     * @var array<string> $allowanceOffers
     */
    private array $allowanceOffers;

    /**
     * Флаг указывает, что погрузка идет на не известный тип транспорта.
     * Если флаг установлен в true, то $loadingShoulderTypes будет пустым.
     * Данный флаг используется для предложений, которые используются
     * в пограничных ПРР маршрута.
     *
     * @var bool
     */
    private bool $isLoadingToUnknownTransport;

    /**
     * Флаг указывает, что разгрузка идет с не известного тип транспорта.
     * Если флаг установлен в true, то $unloadingShoulderTypes будет пустым.
     * Данный флаг используется для предложений, которые используются
     * в пограничных ПРР маршрута.
     *
     * @var bool
     */
    private bool $isUnloadingFromUnknownTransport;

    /**
     * Идентификаторы условий ЦП ПРР терминала. По идентификаторам
     * можно получить сами условия для вычисления стоимости услуги.
     *
     * @var array<string> $offerConditions
     */
    private array $offerConditions = [];

    /**
     * Типы транспорта, на которые возможна погрузка для этого ПРР.
     * По сути задает типы транспорта погрузки, для разделения ПРР.
     * Данные по типам транспорта можно найти в RouteLibrary.
     *
     * @var array<string> $loadingShoulderTypes
     */
    private array $loadingShoulderTypes = [];

    /**
     * Типы транспорта, с которых возможна разгрузка по данному ПРР.
     * Разгрузка будет вестись только с них.
     * Данные по типам транспорта можно найти в RouteLibrary.
     *
     * @var array<string> $unloadingShoulderTypes
     */
    private array $unloadingShoulderTypes = [];

    /**
     * Указывает на тип ПРР. Возможные варианты:
     *  - loading_and_unloading - Погрузка и разгрузка
     *  - loading - только погрузка
     *  - unloading - только разгрузка
     *
     * @var string
     */
    private string $serviceType;

    /**
     * Конструктор DTO
     *
     * @param string $id
     * @param array  $allowanceOffers
     * @param bool   $isLoadingToUnknownTransport
     * @param bool   $isUnloadingFromUnknownTransport
     * @param array  $loadingShoulderTypes
     * @param array  $offerConditions
     * @param string $serviceType
     * @param array  $unloadingShoulderTypes
     */
    public function __construct(
        string $id,
        array  $allowanceOffers,
        bool   $isLoadingToUnknownTransport,
        bool   $isUnloadingFromUnknownTransport,
        array  $loadingShoulderTypes,
        array  $offerConditions,
        string $serviceType,
        array  $unloadingShoulderTypes,
    )
    {
        $this->allowanceOffers = $allowanceOffers;
        $this->id = $id;
        $this->isLoadingToUnknownTransport = $isLoadingToUnknownTransport;
        $this->isUnloadingFromUnknownTransport = $isUnloadingFromUnknownTransport;
        $this->loadingShoulderTypes = $loadingShoulderTypes;
        $this->offerConditions = $offerConditions;
        $this->serviceType = $serviceType;
        $this->unloadingShoulderTypes = $unloadingShoulderTypes;
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
            id: array_key_exists('id', $data) ? strval($data['id']) : "",
            allowanceOffers: array_key_exists('allowance_offers', $data) && is_array($data['allowance_offers'])
                ? array_map(function ($name) {
                    return strval($name);
                }, $data['allowance_offers'])
                : [],
            isLoadingToUnknownTransport: array_key_exists('is_loading_to_unknown_transport', $data) && boolval($data['is_loading_to_unknown_transport']),
            isUnloadingFromUnknownTransport: array_key_exists('is_unloading_from_unknown_transport', $data) && boolval($data['is_unloading_from_unknown_transport']),
            loadingShoulderTypes: array_key_exists('loading_shoulder_types', $data) && is_array($data['loading_shoulder_types'])
                ? array_map(function ($name) {
                    return strval($name);
                }, $data['loading_shoulder_types'])
                : [],
            offerConditions: array_key_exists('offer_conditions', $data) && is_array($data['offer_conditions'])
                ? array_map(function ($name) {
                    return strval($name);
                }, $data['offer_conditions'])
                : [],
            serviceType: array_key_exists('service_type', $data) ? strval($data['service_type']) : "",
            unloadingShoulderTypes: array_key_exists('unloading_shoulder_types', $data) && is_array($data['unloading_shoulder_types'])
                ? array_map(function ($name) {
                    return strval($name);
                }, $data['unloading_shoulder_types'])
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
            $this->id,
            [...$this->allowanceOffers],
            $this->isLoadingToUnknownTransport,
            $this->isUnloadingFromUnknownTransport,
            [...$this->loadingShoulderTypes],
            [...$this->offerConditions],
            $this->serviceType,
            [...$this->unloadingShoulderTypes],
        );
    }

    /**
     * Идентификаторы ценовых предложений надбавок для ПРР терминала.
     *
     * @return array
     */
    public function getAllowanceOffers(): array
    {
        return [...$this->allowanceOffers];
    }

    /**
     * Идентификатор предложения с ПРР терминала
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Флаг указывает, что погрузка идет на не известный тип транспорта.
     * Если флаг установлен в true, то $loadingShoulderTypes будет пустым.
     * Данный флаг используется для предложений, которые используются
     * в пограничных ПРР маршрута.
     *
     * @return bool
     */
    public function isLoadingToUnknownTransport(): bool
    {
        return $this->isLoadingToUnknownTransport;
    }

    /**
     * Флаг указывает, что разгрузка идет с не известного тип транспорта.
     * Если флаг установлен в true, то $unloadingShoulderTypes будет пустым.
     * Данный флаг используется для предложений, которые используются
     * в пограничных ПРР маршрута.
     *
     * @return bool
     */
    public function isUnloadingFromUnknownTransport(): bool
    {
        return $this->isUnloadingFromUnknownTransport;
    }

    /**
     * Типы транспорта, на которые возможна погрузка для этого ПРР.
     * По сути задает типы транспорта погрузки, для разделения ПРР.
     * Данные по типам транспорта можно найти в RouteLibrary.
     *
     * @return array
     */
    public function getLoadingShoulderTypes(): array
    {
        return [...$this->loadingShoulderTypes];
    }

    /**
     * Идентификаторы условий ЦП ПРР терминала. По идентификаторам
     * можно получить сами условия для вычисления стоимости услуги.
     *
     * @return array
     */
    public function getOfferConditions(): array
    {
        return [...$this->offerConditions];
    }

    /**
     * Указывает на тип ПРР. Возможные варианты:
     * - loading_and_unloading - Погрузка и разгрузка
     * - loading - только погрузка
     * - unloading - только разгрузка
     *
     * @return string
     */
    public function getServiceType(): string
    {
        return $this->serviceType;
    }

    /**
     * Типы транспорта, с которых возможна разгрузка по данному ПРР.
     * Разгрузка будет вестись только с них.
     * Данные по типам транспорта можно найти в RouteLibrary.
     *
     * @return array
     */
    public function getUnloadingShoulderTypes(): array
    {
        return [...$this->unloadingShoulderTypes];
    }
}