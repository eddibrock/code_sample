<?php

namespace Onlogsystem\Page;

use Onlogsystem\Cargo\CargoNames;

class PageUrl
{
    private $levelOne = '';
    private $levelTwo = '';
    private $gruz = '';

    private \Bitrix\Main\HttpRequest|\Bitrix\Main\Request $request;

    public function __construct()
    {
        $this->request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
        $this->setUrlLevel();
    }

    public function getLevelOne(): string
    {
        return $this->levelOne;
    }

    public function getLevelTwo(): string
    {
        return $this->levelTwo;
    }

    public function getGruz(): string
    {
        return $this->gruz;
    }

    public function getRequest(): \Bitrix\Main\Request|\Bitrix\Main\HttpRequest
    {
        return $this->request;
    }

    public function getUrlMatches()
    {
        $pageDirectory = $this->getRequest()->getRequestedPageDirectory();

        $patterns = [
            '#^/seo/([a-z-]+)/([a-z-]+)/([a-z-]+)/#',
            '#^/seo/([a-z-]+)/([a-z-]+)/#',
            '#^/seo/([a-z-]+)/#',
        ];
        foreach ($patterns as $pattern) {
            preg_match($pattern, $pageDirectory, $matches);
            if (!empty($matches)) {
                return $matches;
            }
        }
    }

    public function setUrlLevel()
    {
        $matches = $this->getUrlMatches();
        foreach ($matches as $key => $match) {
            switch ($key) {
                case 1:
                    $this->levelOne = $match;
                    break;
                case 2:
                case 3:
                    if (str_contains($match, 'gruz')) {
                        if (CargoNames::getType($match)) {
                            $this->gruz = $match;
                        }
                    } else {
                        $this->levelTwo = $match;
                    }
                    break;
            }
        }
    }
}