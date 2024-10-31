<?php

namespace Onlogsystem\Cargo;

use Onlogsystem\Cargo\Entity\CargoRoute\CargoRoute;

abstract class AbstractCargo implements Cargo
{
    protected array $data;
    protected mixed $date;
    protected string $code;

    protected CargoRestHelper $obRestHelper;
    protected array $topFastRoutes;
    protected array $topCheapRoutes;

    public function __construct()
    {
        $this->obRestHelper = new CargoRestHelper();
    }

    public function setData(array $data): void
    {
        $this->data = $data['data'];
        $this->date = $data['date'];
        $this->code = $data['code'];
        foreach ($this->data['topFast'] as $routesGroup) {
            $this->topFastRoutes[] = CargoRoute::makeFromData($routesGroup);
        }
        foreach ($this->data['topCheap'] as $routesGroup) {
            $this->topCheapRoutes[] = CargoRoute::makeFromData($routesGroup);
        }
    }

    public function getTopFastRoutes(): array
    {
        return $this->topFastRoutes;
    }

    public function getTopCheapRoutes(): array
    {
        return $this->topCheapRoutes;
    }

    protected array $topType = [
        'topfast' => 'topFast',
        'topcheap' => 'topCheap'
    ];

    function getType(): string
    {
        return static::$type;
    }

    function getData(): array
    {
        return $this->data;
    }

    function getCode(): string
    {
        return $this->code;
    }

    function getDate(): string
    {
        return $this->date;
    }

    function getParameters(): array
    {
        return $this->data['cargoParameters']['containerParameters'];
    }

    function getRouteData(): array
    {
        return $this->data['cargoParameters']['containerParameters']['routePoints'];
    }

    function getCheapestTopData()
    {
        $topType = $this->topType['topcheap'];
        $topData = null;
        foreach ($this->data[$topType] as $topDatum) {
            if ($topData == null) {
                $topData = $topDatum['fullPrice'];
            } else {
                if ($topDatum['fullPrice'] < $topData) {
                    $topData = $topDatum['fullPrice'];
                }
            }
        }

        return $topData;
    }

    function getCheapest()
    {
        $topType = $this->topType['topcheap'];
        $topData = null;
        $topKey = 0;
        foreach ($this->data[$topType] as $key => $topDatum) {
            if ($topData == null) {
                $topData = $topDatum['fullPrice'];
                $topKey = $key;
            } else {
                if ($topDatum['fullPrice'] < $topData) {
                    $topData = $topDatum['fullPrice'];
                    $topKey = $key;
                }
            }
        }

        return $this->data[$topType][$topKey];
    }

    function getFastest()
    {
        $topType = $this->topType['topfast'];
        $topData = null;
        $topKey = 0;
        foreach ($this->data[$topType] as $key => $topDatum) {
            if ($topData == null) {
                $topData = $topDatum['deliveryTime'];
                $topKey = $key;
            } else {
                if ($topDatum['deliveryTime'] < $topData) {
                    $topData = $topDatum['deliveryTime'];
                    $topKey = $key;
                }
            }
        }

        return $this->data[$topType][$topKey];
    }

    function getFastestTopData()
    {
        $topType = $this->topType['topfast'];
        $topData = null;
        foreach ($this->data[$topType] as $topDatum) {
            if ($topData == null) {
                $topData = $topDatum['deliveryTime'];
            } else {
                if ($topDatum['deliveryTime'] < $topData) {
                    $topData = $topDatum['deliveryTime'];
                }
            }
        }

        return $topData;
    }

    function getRouteLocationIds(): array
    {
        // ИД всех локаций
        $allLocationIds = [];
        $arRoutesData = $this->getRouteData();
        $allLocationIds[] = $arRoutesData['from']['location'];
        $allLocationIds[] = $arRoutesData['to']['location'];

        return $allLocationIds;
    }

    function getRouteTerminalIds(): array
    {
        // ИД всех терминалов
        $allTerminalIds = [];
        $arRoutesData = $this->getRouteData();
        $allTerminalIds[] = $arRoutesData['from']['terminal'];
        $allTerminalIds[] = $arRoutesData['to']['terminal'];

        return $allTerminalIds;
    }

    function getTerminalLocationIds(): array
    {
        $result = [];
        foreach ($this->topType as $type) {
            foreach ($this->data[$type] as $datum) {
                foreach ($datum['steps'] as $step) {
                    if ($step['startTerminal']) {
                        $result[] = $step['startTerminal']['location_id'];
                    }
                    if ($step['endTerminal']) {
                        $result[] = $step['endTerminal']['location_id'];

                    }
                }
            }
        }

        return array_unique($result);
    }

    function getTerminalNames(): array
    {
        $result = [];
        foreach ($this->topType as $type) {
            foreach ($this->data[$type] as $datum) {
                foreach ($datum['steps'] as $step) {
                    if ($step['startTerminal']) {
                        $result = array_merge($result, $step['startTerminal']['localized_names']);
                    }
                    if ($step['endTerminal']) {
                        $result = array_merge($result, $step['endTerminal']['localized_names']);
                    }
                }
            }
        }

        return array_unique($result);
    }

    function getTerminalAbbreviationsNames(): array
    {
        $result = [];
        foreach ($this->topType as $type) {
            foreach ($this->data[$type] as $datum) {
                foreach ($datum['steps'] as $step) {
                    if ($step['startTerminal']) {
                        $result = array_merge($result, $step['startTerminal']['localized_abbreviations']);
                    }
                    if ($step['endTerminal']) {
                        $result = array_merge($result, $step['endTerminal']['localized_abbreviations']);
                    }
                }
            }
        }

        return array_unique($result);
    }

    public function getAllTopCount(): int
    {
        $count = 0;
        foreach ($this->topType as $type) {
            foreach ($this->data[$type] as $datum) {
                $count++;
            }
        }

        return $count;
    }

    public function getTopCount($type): int
    {
        $count = 0;
        foreach ($this->data[$type] as $datum) {
            $count++;
        }

        return $count;
    }

    public function getAllShoulderCount(): int
    {
        $count = 0;
        foreach ($this->topType as $type) {
            foreach ($this->data[$type] as $datum) {
                foreach ($datum['steps'] as $step) {
                    $count++;
                }
            }
        }

        return $count;
    }

    public function getAllTerminalCount(): int
    {
        $count = 0;
        foreach ($this->topType as $type) {
            foreach ($this->data[$type] as $datum) {
                foreach ($datum['steps'] as $step) {
                    if ($step['startTerminal']) {
                        $count++;
                    }
                    if ($step['endTerminal']) {
                        $count++;
                    }
                }
            }
        }

        return $count;
    }

    public function getTransitTerminal($type)
    {
        $topType = $this->topType[$type];
        if (!$topType) {
            //TODO rise error
            return null;
        }
        $locations = $this->getRouteLocationIds();
        $endLocationId = $locations[1];
        $arLocNames = null;
        $name = null;
        foreach ($this->data[$topType] as $datum) {
            foreach ($datum['steps'] as $step) {
                if ($step['endTerminal']) {
                    if ($endLocationId != $step['endTerminal']['location_id']) {
                        $arLocNames = $step['endTerminal']['localized_abbreviations'];
                        break;
                    }
                }
            }
        }
        if ($arLocNames) {
            $name = $this->obRestHelper->getMessage($arLocNames);
        }
        return $name;
    }

    public function getTransitTerminalLocationId($type)
    {
        $topType = $this->topType[$type];
        if (!$topType) {
            //TODO rise error
            return null;
        }
        $locations = $this->getRouteLocationIds();
        $endLocationId = $locations[1];
        foreach ($this->data[$topType] as $datum) {
            foreach ($datum['steps'] as $step) {
                if ($step['endTerminal']) {
                    if ($endLocationId != $step['endTerminal']['location_id']) {
                        return $step['endTerminal']['location_id'];
                    }
                }
            }
        }

        return '';
    }

    public function getTransitTerminalCarriers($type): ?array
    {
        $topType = $this->topType[$type];
        if (!$topType) {
            //TODO rise error
            return null;
        }
        $locations = $this->getRouteLocationIds();
        $endLocationId = $locations[1];
        $carriers = [];
        foreach ($this->data[$topType] as $datum) {
            foreach ($datum['steps'] as $step) {
                if ($step['endTerminal']) {
                    if ($endLocationId != $step['endTerminal']['location_id']) {
                        if ($step['shoulder'] && $step['shoulder']['carrier_id']) {
                            $carriers[] = $step['shoulder']['carrier_id'];
                        }
                    }
                }
            }
        }

        return array_unique($carriers);
    }

    public function getTransitTransportTypes($type): ?array
    {
        $topType = $this->topType[$type];
        if (!$topType) {
            //TODO rise error
            return null;
        }
        $locations = $this->getRouteLocationIds();
        $endLocationId = $locations[1];
        $transport = [];
        foreach ($this->data[$topType] as $datum) {
            foreach ($datum['steps'] as $step) {
                if ($step['endTerminal']) {
                    if ($endLocationId != $step['endTerminal']['location_id']) {
                        if ($step['shoulder'] && $step['shoulder']['carrier_id']) {
                            $transport[] = $step['shoulder']['shoulder_type'];
                        }
                    }
                }
            }
        }

        return array_unique($transport);
    }

    public function getAllCarrier(): array
    {
        $carriers = [];
        foreach ($this->topType as $type) {
            foreach ($this->data[$type] as $datum) {
                foreach ($datum['steps'] as $step) {
                    if ($step['shoulder'] && $step['shoulder']['carrier_id']) {
                        $carriers[] = $step['shoulder']['carrier_id'];
                    }
                }
            }
        }

        return array_unique($carriers);
    }

    public function getShoulderCount($type): int
    {
        $count = 0;

        foreach ($this->data[$type] as $datum) {
            foreach ($datum['steps'] as $step) {
                $count++;
            }
        }

        return $count;
    }

    public function getCheapShoulderTypes(): array
    {
        $topType = $this->topType['topcheap'];
        $result = [];
        foreach ($this->data[$topType] as $topDatum) {
            $result[] = $topDatum['shoulder']['shoulder_type'];
        }

        return array_unique($result);
    }

    public function getFastShoulderTypes(): array
    {
        $topType = $this->topType['topfast'];
        $result = [];
        foreach ($this->data[$topType] as $topDatum) {
            $result[] = $topDatum['shoulder']['shoulder_type'];
        }

        return array_unique($result);
    }

    public function getShoulders(): array
    {
        $result = [];

        foreach ($this->topType as $type) {
            foreach ($this->data[$type] as $datum) {
                foreach ($datum['steps'] as $step) {
                    $result[$type][] =
                        [
                            'money' => $datum['fullPrice'],
                            'days' => $datum['deliveryTime'],
                            'start' => $step['startTerminal'],
                            'end' => $step['endTerminal'],
                            'shoulder' => $step['shoulder'],
                            'delivery_time' => $step['shoulderOffer']['delivery_time']
                        ];
                }
            }
        }

        return $result;
    }

    function getTopData($type): array
    {
        $typeReal = $this->topType[$type];

        return $this->data[$typeReal];
    }
}