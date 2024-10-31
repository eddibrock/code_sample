<?php

namespace Onlogsystem\Page;

interface Page
{
    function checkType(): bool;

    function getType(): string;

    function getTitle(): string;

    function getNavChain(): array;

    function getHeader(): string;

    function getDescription(): string;

    function getInfoSection(): string;

    function setPageData();

    function getTopCountry(): array;

    function getTransportationIntegration(): array;

    function getData(): array;

    function getDir(): string;
}