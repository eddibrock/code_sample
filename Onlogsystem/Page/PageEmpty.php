<?php

namespace Onlogsystem\Page;

use Onlogsystem\Page\Page;

class PageEmpty extends AbstractPage
{
    protected static string $type = 'empty';

    public function __construct(PageUrl $url)
    {
        parent::__construct();
        $this->name = '';
    }

    function getTitle(): string
    {
        return '';
    }

    function getHeader(): string
    {
        return '';
    }

    function getDescription(): string
    {
        return '';
    }

    function getTopCountry(): array
    {
        return [];
    }

    function getTransportationIntegration(): array
    {
        return [];
    }
}