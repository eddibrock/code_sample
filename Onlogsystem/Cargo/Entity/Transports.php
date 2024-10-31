<?php

namespace Onlogsystem\Cargo\Entity;

/**
 * Enum содержит доступные для системы типы транспорта.
 */
enum Transports
{
    case Sea;  // Морское
    case Rail; // ЖД
    case Air;  // Авиа
    case Auto; // Автомобильное

    /**
     * Получение типа транспорта по ID
     *
     * @param $id
     *
     * @return self
     */
    public static function getByID($id): self
    {
        return match (strval($id)) {
            "1" => self::Sea,
            "2" => self::Rail,
            "3" => self::Air,
            "4" => self::Auto,
        };
    }

    /**
     * Получение типа транспорта по данным из массива из API
     *
     * @param array $data
     *
     * @return self
     */
    public static function makeByArray(array $data): self
    {
        if (!array_key_exists('id', $data)) {
            return self::Rail;
        }

        return self::getByID($data['id']);
    }
}