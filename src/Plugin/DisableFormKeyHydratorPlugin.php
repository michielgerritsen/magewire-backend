<?php declare(strict_types=1);

namespace Magewirephp\MagewireBackend\Plugin;

use Closure;
use Magewirephp\Magewire\Component;
use Magewirephp\Magewire\Model\Hydrator\FormKey;
use Magewirephp\Magewire\Model\RequestInterface;

class DisableFormKeyHydratorPlugin
{
    public function aroundHydrate(FormKey $formKey, Closure $callable, Component $component, RequestInterface $request)
    {
    }
}
