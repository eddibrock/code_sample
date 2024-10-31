<?php

namespace Onlogsystem\Cargo\Entity\CargoTerminals;

/**
 * DTO данных терминала. Содержит набор данных по расположению и
 * стоимости услуг конкретного терминала.
 *
 * Внимание!!! Для корректного получения всех данных по терминалу
 * рекомендуется использовать его расширенный аналог из
 * RouteResult->routeLocationsAndTerminalsData.
 *
 * Данный раздел содержит в себе полную копию терминалов, но дополненные
 * данными локализации, а так же родительскими локациями, что может понадобиться
 * для построения полноценных названий терминала.
 */
class CargoTransportTerminal
{
    /**
     * Идентификатор терминала, на котором идет погрузка/разгрузка плеча
     * маршрута.
     *
     * @var string
     */
    private string $id;

    /**
     * Название по умолчанию для терминала.
     *
     * @var string
     */
    private string $defaultName;

    /**
     * Идентификаторы локализаций для названий терминала.
     * По этим идентификаторам можно получить локализованные тексты
     * из RouteLibrary. Тексты содержат данные
     * под разные языки системы.
     * Схема применения следующая: Если есть локализация, берем ее,
     * если нет - используем $defaultName
     *
     * @var array<string> $localizedNames
     */
    private array $localizedNames;

    /**
     * Аббревиатура терминала по умолчанию.
     * По сути сокращенное название. Если оно есть, используем его,
     * если нет - $localizedNames.
     *
     * @var string
     */
    private string $defaultAbbreviation;

    /**
     * Идентификаторы локализаций для аббревиатур терминала.
     * По этим идентификаторам можно получить локализованные тексты
     * из RouteLibrary. Тексты содержат данные
     * под разные языки системы.
     * Схема применения следующая: Если есть локализация, берем ее,
     * если нет - используем $defaultAbbreviation
     *
     * @var array<string> $localizedAbbreviations
     */
    private array $localizedAbbreviations;

    /**
     * Идентификатор локации, в которой расположен терминал.
     * Локацию можно получить из RouteLibrary.
     *
     * @var string
     */
    private string $locationId;

    /**
     * Тип терминала. По сути может использоваться для уточнения
     * какой именно это терминал, например: Порт, ЖД и т.д.
     * Доступные варианты значений:
     *  - NULLABLE - Нулевой терминал (абстрактный терминал локации, когда нет реальных)
     *  - PRT - Морской порт
     *  - RSTN - ЖД станция
     *  - AUTO - Автомобильный
     *  - AIRP - Аэропорт
     *
     * @var string
     */
    private string $symbolCode;

    /**
     * Идентификаторы файлов с условиями терминала.
     * Как правило PDF с кучей текста. Взять можно из RouteLibrary.
     *
     * @var array<string> $files
     */
    private array $files;

    /**
     * Конструктор DTO
     *
     * @param string $id
     * @param string $defaultName
     * @param string $defaultAbbreviation
     * @param array  $localizedNames
     * @param array  $localizedAbbreviations
     * @param string $locationId
     * @param string $symbolCode
     * @param array  $files
     */
    public function __construct(
        string $id,
        string $defaultName,
        string $defaultAbbreviation,
        array  $localizedNames,
        array  $localizedAbbreviations,
        string $locationId,
        string $symbolCode,
        array  $files,
    )
    {
        $this->id = $id;
        $this->defaultName = $defaultName;
        $this->localizedNames = $localizedNames;
        $this->localizedAbbreviations = $localizedAbbreviations;
        $this->defaultAbbreviation = $defaultAbbreviation;
        $this->locationId = $locationId;
        $this->symbolCode = $symbolCode;
        $this->files = $files;
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
            id: array_key_exists('id', $data) ? strval($data['id']) : "",
            defaultName: array_key_exists('default_name', $data) ? strval($data['default_name']) : "",
            defaultAbbreviation: array_key_exists('default_abbreviation', $data) ? strval($data['default_abbreviation'])
                : "",
            localizedNames: array_key_exists('localized_names', $data) && is_array($data['localized_names'])
                ? array_map(function ($name) {
                    return strval($name);
                }, $data['localized_abbreviations'])
                : [],
            localizedAbbreviations: array_key_exists('localized_abbreviations', $data) && is_array($data['localized_abbreviations'])
                ? array_map(function ($name) {
                    return strval($name);
                }, $data['localized_abbreviations'])
                : [],
            locationId: array_key_exists('location_id', $data) ? strval($data['location_id']) : "",
            symbolCode: array_key_exists('symbol_code', $data) ? strval($data['symbol_code']) : "NULLABLE",
            files: array_key_exists('files', $data) && is_array($data['files'])
                ? array_map(function ($name) {
                    return strval($name);
                }, $data['files'])
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
            $this->defaultName,
            $this->defaultAbbreviation,
            [...$this->localizedNames],
            [...$this->localizedAbbreviations],
            $this->locationId,
            $this->symbolCode,
            [...$this->files],
        );
    }

    /**
     * Аббревиатура терминала по умолчанию.
     * По сути сокращенное название. Если оно есть, используем его,
     * если нет - $localizedNames.
     *
     * @return string
     */
    public function getDefaultAbbreviation(): string
    {
        return $this->defaultAbbreviation;
    }

    /**
     * Название по умолчанию для терминала.
     *
     * @return string
     */
    public function getDefaultName(): string
    {
        return $this->defaultName;
    }

    /**
     * Идентификатор терминала, на котором идет погрузка/разгрузка плеча
     * маршрута.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Идентификаторы локализаций для аббревиатур терминала.
     * По этим идентификаторам можно получить локализованные тексты
     * из RouteLibrary. Тексты содержат данные
     * под разные языки системы.
     * Схема применения следующая: Если есть локализация, берем ее,
     * если нет - используем $defaultAbbreviation
     *
     * @return array
     */
    public function getLocalizedAbbreviations(): array
    {
        return [...$this->localizedAbbreviations];
    }

    /**
     * Идентификаторы локализаций для названий терминала.
     * По этим идентификаторам можно получить локализованные тексты
     * из RouteLibrary. Тексты содержат данные
     * под разные языки системы.
     * Схема применения следующая: Если есть локализация, берем ее,
     * если нет - используем $defaultName
     *
     * @return array
     */
    public function getLocalizedNames(): array
    {
        return [...$this->localizedNames];
    }

    /**
     * Идентификатор локации, в которой расположен терминал.
     * Локацию можно получить из RouteLibrary.
     *
     * @return string
     */
    public function getLocationId(): string
    {
        return $this->locationId;
    }

    /**
     * Тип терминала. По сути может использоваться для уточнения
     * какой именно это терминал, например: Порт, ЖД и т.д.
     * Доступные варианты значений:
     * - NULLABLE - Нулевой терминал (абстрактный терминал локации, когда нет реальных)
     * - PRT - Морской порт
     * - RSTN - ЖД станция
     * - AUTO - Автомобильный
     * - AIRP - Аэропорт
     *
     * @return string
     */
    public function getSymbolCode(): string
    {
        return $this->symbolCode;
    }

    /**
     * Идентификаторы файлов с условиями терминала.
     * Как правило PDF с кучей текста. Взять можно из RouteLibrary.
     *
     * @return array
     */
    public function getFiles(): array
    {
        return [...$this->files];
    }
}