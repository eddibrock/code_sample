<?php

namespace Onlogsystem\Cargo;

class CargoContainer extends AbstractCargo
{
    protected static string $type;

    public function __construct(array $data)
    {
        parent::__construct();
        self::$type = 'container';
        $this->setData($data);
        $this->data['cargoParameters']['containerParameters'] = json_decode($this->data['cargoParameters']['containerParameters'], true);
    }

    public function getCargoName(): string
    {
        return 'konteynernyy-gruz';
    }

    public function getContainerDimension()
    {
        $containerData = $this->getParameters();

        return $this->obRestHelper->getContainerName($containerData['containerId']);
    }

}