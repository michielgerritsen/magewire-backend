<?php declare(strict_types=1);

namespace Magewirephp\MagewireBackend\Plugin;

use Magento\Framework\UrlInterface;
use Magewirephp\Magewire\ViewModel\Magewire;

class ReplaceApplicationUrlPlugin
{
    private UrlInterface $url;

    public function __construct(
        UrlInterface $url
    ) {
        $this->url = $url;
    }

    public function afterGetApplicationUrl(Magewire $magewire, string $applicationUrl): string
    {
        return rtrim($this->url->getUrl('magewire/post/livewire'),'/');
    }
}
