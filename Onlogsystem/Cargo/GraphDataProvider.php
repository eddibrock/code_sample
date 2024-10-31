<?php

namespace Onlogsystem\Cargo;

use Onlogsystem\Rest\Graphql;
use Exception;
use CUtil;


class GraphDataProvider
{
    public ?Graphql\GraphqlInterface $obGraph;
    private array $data;

    public function __construct()
    {
        $this->obGraph = new Graphql\GraphqlInterface();
    }

    public function addCargo(Cargo $cargo): void
    {
        $this->data[] = $cargo;
    }

    public function getLangId(): int
    {
        return \Onlogsystem\Cargo\CargoLanguage::getLangId(\Onlogsystem\Cargo\CargoLanguage::ru);
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getLocationData(): array
    {
        // ИД всех локаций
        $allLocationIds = [];
        // ИД всех терминалов (для кастомных типов груза)
        $allTerminalIds = [];
        // Получить все имена
        $arLocNames = [];
        foreach ($this->data as $cargo) {
            if (!$cargo instanceof Cargo) {
                return [];
            }

            $allLocationIds = array_merge($allLocationIds,
                $cargo->getRouteLocationIds(),
                $cargo->getTerminalLocationIds(),
            );
            $arLocNames = array_merge($arLocNames,
                $cargo->getTerminalNames(),
                $cargo->getTerminalAbbreviationsNames(),
            );
            $allTerminalIds = array_merge($allTerminalIds, $cargo->getRouteTerminalIds());
        }

        foreach ($allLocationIds as $key => $allLocationId) {
            if ($allLocationId == '0') {
                unset($allLocationIds[$key]);
            }
        }

        foreach ($allTerminalIds as $key => $allTerminalId) {
            if ($allTerminalId == '0') {
                unset($allTerminalIds[$key]);
            }
        }
        $allTerminalIds = array_unique($allTerminalIds);
        $allTerminalData = $this->obGraph->SearchTerminal($allTerminalIds)->transport_terminal_list;
        $terminalResult = [];
        foreach ($allTerminalData as $allTerminalDatum) {
            $terminalDataDecoded = json_decode(json_encode($allTerminalDatum), true);

            $allLocationIds[] = $terminalDataDecoded['location_id'];
            $terminalResult[$terminalDataDecoded['id']] = $terminalDataDecoded;
        }

        $allLocationIds = array_unique($allLocationIds);

        $allLocationsWithParens = $this->obGraph->getLocationsWithParents(array_unique($allLocationIds))->getLocationsWithParents;

        $arLocData = [];
        $result = [];
        foreach ($allLocationsWithParens as $allLocationsWithParen) {
            $dataDecoded = json_decode(json_encode($allLocationsWithParen), true);
            $arLocData[$dataDecoded['location']['id']] = $dataDecoded['location']['localized_names'];
            $arLocNames = array_merge($arLocNames, $dataDecoded['location']['localized_names']);
            $dataDecoded['location']['toLocations'] = $dataDecoded['toLocations'];
            $result[$dataDecoded['location']['id']] = $dataDecoded['location'];
        }

        $nameMessages = $this->getNameMessages($arLocNames);

        foreach ($result as &$item) {
            foreach ($item['localized_names'] as $localized_name) {
                if (array_key_exists($localized_name, $nameMessages)) {
                    $item['message'] = $nameMessages[$localized_name];
                    break;
                }
            }
        }
        foreach ($terminalResult as &$item) {
            foreach ($item['localized_names'] as $localized_name) {
                if (array_key_exists($localized_name, $nameMessages)) {
                    $item['message'] = $nameMessages[$localized_name];
                    break;
                }
            }
        }

        return [
            'TERMINAL' => $terminalResult,
            'LOCATIONS' => $result,
            'NAMES' => $nameMessages
        ];
    }

    public function getNameMessages($arLocNames): array
    {
        $localizedMessages = $this->obGraph->LocalizedMessagesLoaderQuery(array_unique($arLocNames));

        $nameMessages = [];

        foreach ($localizedMessages->items as $localizedMessage) {
            if ($localizedMessage->lang_id == $this->getLangId()) {
                $nameMessages[$localizedMessage->id] = $localizedMessage->message;
            }
        }

        return $nameMessages;
    }

    public function getTerminalLocation($locationId, $locationData = [])
    {
        if (empty($locationData)) {
            $locationData = $this->getLocationData();
        }

        foreach ($locationData['LOCATIONS'] as $LOCATION) {
            if ($LOCATION['id'] == $locationId) {
                return $LOCATION['message'];
            }
        }
    }
}