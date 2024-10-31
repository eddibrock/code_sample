<?php

namespace Onlogsystem\Cargo;

interface Cargo
{
    function getType(): string;

    function getData(): array;

    function getCode(): string;

    function getDate(): string;

    function getParameters(): array;

    function getCheapestTopData();

    function getFastestTopData();

    function getRouteData(): array;

    function getRouteLocationIds(): array;

    function getRouteTerminalIds(): array;

    function getCargoName(): string;

    function getAllTopCount(): int;

    function getTopCount($type): int;

    function getAllShoulderCount(): int;

    function getShoulderCount($type): int;

    function getContainerDimension();

    function getTopData($type): array;
}