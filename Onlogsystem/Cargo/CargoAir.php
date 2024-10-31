<?php

namespace Onlogsystem\Cargo;


class CargoAir extends AbstractCargo
{
    protected static string $type;

    public function __construct(array $data)
    {
        parent::__construct();
        self::$type = 'air';
        $this->setData($data);
        $this->data['cargoParameters']['containerParameters'] = json_decode($this->data['cargoParameters']['customCargoParameters'], true);
    }

    public function getCargoName(): string
    {
        return 'avia-gruz';
    }

    public function getContainerDimension(): string
    {
        return '1 кг';
    }
}