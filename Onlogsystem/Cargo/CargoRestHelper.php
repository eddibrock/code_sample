<?php


namespace Onlogsystem\Cargo;

use Onlogsystem\Rest\Graphql;

class CargoRestHelper
{
    private ?Graphql\GraphqlInterface $obGraph;

    public function __construct()
    {
        $this->obGraph = new Graphql\GraphqlInterface();
    }

    public function getContainerName($containerId)
    {
        $containers = $this->obGraph->LoadContainers();
        if ($containers) {
            foreach ($containers->items as $container) {
                if ($container->id == $containerId) {
                    return self::getMessage($container->localized_names);
                }
            }
        }
    }

    public function getMessage(array $countryLocNames)
    {
        $countryNames = $this->obGraph->LocalizedMessagesLoaderQuery($countryLocNames);

        return $this->obGraph->getLocMessage($countryNames, self::getLangId());
    }

    static public function getLangId(): int
    {
        return \Onlogsystem\Cargo\CargoLanguage::getLangId(\Onlogsystem\Cargo\CargoLanguage::ru);
    }

    public function getPopulation($arLocIds): array
    {
        $data = $this->obGraph->getLocationList($arLocIds);
        $result = [];
        if ($data) {
            foreach ($data->items as $item) {
                $result[$item->id] = $item->population;
            }
        }

        return $result;
    }

    public function getCarriers($arCarriersIds): array
    {
        $data = $this->obGraph->getCarriers();
        $result = [];
        if ($data) {
            foreach ($data->items as $item) {
                if (in_array($item->id, $arCarriersIds)) {
                    $result[$item->id] = [
                        'default_name' => $item->default_name,
                        'localized_names' => $item->localized_names,
                    ];
                }
            }
        }

        return $result;
    }

    public function getTransport($arTransportIds): array
    {
        $data = $this->obGraph->LoadDeliveryModes();
        $result = [];
        if ($data) {
            foreach ($data->items as $item) {
                if (in_array($item->id, $arTransportIds)) {
                    $result[$item->id] = [
                        'default_name' => $item->default_name,
                        'localized_names' => $item->localized_names,
                    ];
                }
            }
        }

        return $result;
    }
}