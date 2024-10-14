<?php

namespace LLPhant\Tool;

use Exception;
use LLPhant\Embeddings\DataReader\HtmlReader;
use LLPhant\Experimental\Agent\Render\CLIOutputUtils;
use LLPhant\Experimental\Agent\Render\OutputAgentInterface;

class WebPageTextGetter extends ToolBase
{
    private readonly HtmlReader $htmlReader;

    /**
     * @throws Exception
     */
    public function __construct(bool $verbose = false, public OutputAgentInterface $outputAgent = new CLIOutputUtils())
    {
        parent::__construct($verbose);
        $this->htmlReader = new HtmlReader();
    }

    /**
     * With this function you can get the content of multiple web pages by their URLs.
     *
     * @param  string[]  $urls
     * @return string[]
     *
     * @throws Exception
     */
    public function getMultipleWebPageText(array $urls): array
    {
        $this->outputAgent->renderTitleAndMessageOrange('🔧 retrieving web content of those pages :',
            implode(', ', $urls), true);
        $texts = [];
        foreach ($urls as $url) {
            $texts[$url] = $this->getWebPageText($url);
        }

        return $texts;
    }

    /**
     * With this function you can get content of a web page by its URL.
     *
     * @throws \Exception
     */
    public function getWebPageText(string $url): string
    {
        $this->outputAgent->renderTitleAndMessageOrange('🔧 retrieving web page content', $url, true);

        try {
            return $this->htmlReader->getText($url);
        } catch (Exception) {
            return 'We couldn\'t retrieve the web page content from '.$url;
        }
    }
}
