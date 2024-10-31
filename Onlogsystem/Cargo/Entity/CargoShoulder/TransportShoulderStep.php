<?php
namespace Onlogsystem\Cargo\Entity\CargoShoulder;

use Onlogsystem\Cargo\Entity\CargoTerminals\CargoTransportTerminal;
use Onlogsystem\Cargo\Entity\Transports;

/**
 * DTO данных шага мультимодального плеча.
 * Содержит конкретный этап мультимодальной перевозки.
 */
class TransportShoulderStep
{
    /**
     * Идентификатор шага мультимодального плеча.
     *
     * @var string
     */
    private string $id;

    /**
     * Порядковый номер шага. По сути шаги нумеруются, например:
     * 1) Море 2) Жд 3) Авто
     * Номер нужен для правильного выстраивания порядка шагов.
     *
     * @var int
     */
    private int $position;

    /**
     * Тип транспорта, который используется для перевозки по этому шагу мультимодального
     * плеча.
     *
     * @var Transports
     */
    private Transports $transportType;

    /**
     * Терминал отправления по шагу мультимодального плеча.
     * Пустой для первого шага, т.к. для первого шага используется пункт
     * отправления самого плеча и тут он не указывается. Для
     * внутренних шагов всегда задан.
     *
     * @var CargoTransportTerminal|null
     */
    private CargoTransportTerminal|null $startTerminal;

    /**
     * Терминал назначения по шагу мультимодального плеча.
     * Пустой для последнего шага, т.к. для последнего шага используется пункт
     * назначения самого плеча и тут он не указывается. Для
     * внутренних шагов всегда задан.
     *
     * @var CargoTransportTerminal|null
     */
    private CargoTransportTerminal|null $endTerminal;

    /**
     * Конструктор DTO
     *
     * @param string                 $id
     * @param int                    $position
     * @param Transports             $transportType
     * @param CargoTransportTerminal|null $startTerminal
     * @param CargoTransportTerminal|null $endTerminal
     */
    public function __construct(
        string                      $id,
        int                         $position,
        Transports                  $transportType,
        CargoTransportTerminal|null $startTerminal,
        CargoTransportTerminal|null $endTerminal,
    )
    {
        $this->id = $id;
        $this->position = $position;
        $this->transportType = $transportType;
        $this->startTerminal = $startTerminal;
        $this->endTerminal = $endTerminal;
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
            id: array_key_exists('id', $data) ? strval($data['id']) : '',
            position: array_key_exists('position', $data) ? intval($data['position']) : 0,
            transportType: array_key_exists('transportType', $data) && is_array($data['transportType'])
                ? Transports::makeByArray($data['transportType'])
                : Transports::Sea,
            startTerminal: array_key_exists('startTerminal', $data) && is_array($data['startTerminal'])
                ? CargoTransportTerminal::makeFromArray($data['startTerminal'])
                : null,
            endTerminal: array_key_exists('endTerminal', $data) && is_array($data['endTerminal'])
                ? CargoTransportTerminal::makeFromArray($data['endTerminal'])
                : null,
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
            $this->position,
            $this->transportType,
            $this->startTerminal?->clone(),
            $this->endTerminal?->clone(),
        );
    }

    /**
     * Идентификатор шага мультимодального плеча.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Порядковый номер шага. По сути шаги нумеруются, например:
     * 1) Море 2) Жд 3) Авто
     * Номер нужен для правильного выстраивания порядка шагов.
     *
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * Тип транспорта, который используется для перевозки по этому шагу мультимодального
     * плеча.
     *
     * @return Transports
     */
    public function getTransportType(): Transports
    {
        return $this->transportType;
    }

    /**
     * Терминал отправления по шагу мультимодального плеча.
     * Пустой для первого шага, т.к. для первого шага используется пункт
     * отправления самого плеча и тут он не указывается. Для
     * внутренних шагов всегда задан.
     *
     * @return CargoTransportTerminal|null
     */
    public function getStartTerminal(): ?CargoTransportTerminal
    {
        return $this->startTerminal?->clone();
    }

    /**
     * Терминал назначения по шагу мультимодального плеча.
     * Пустой для последнего шага, т.к. для последнего шага используется пункт
     * назначения самого плеча и тут он не указывается. Для
     * внутренних шагов всегда задан.
     *
     * @return CargoTransportTerminal|null
     */
    public function getEndTerminal(): ?CargoTransportTerminal
    {
        return $this->endTerminal?->clone();
    }
}