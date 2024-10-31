<?php

namespace Onlogsystem\Cargo;


class CargoGroupage extends AbstractCargo
{
    protected static string $type;

    public function __construct(array $data)
    {
        parent::__construct();
        self::$type = 'groupage';
        $this->setData($data);
        $this->data['cargoParameters']['containerParameters'] = json_decode($this->data['cargoParameters']['customCargoParameters'], true);
    }

    public function getCargoName(): string
    {
        return 'sbornyy-gruz';
    }

    public function getContainerDimension(): string
    {
        return '1 м³';
    }
}