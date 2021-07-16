<?php

namespace App\Twig;

use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use Symfony\Component\Security\Core\Security;

class AppExtension extends AbstractExtension
{   
    private $security;
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('pluralize', [$this, 'pluralize']),
        ];
    }

    public function pluralize(int $count, string $singular, ?string $plural = null)
    {
        //dd($this->security->getUser()); Intéressant pour accéder aux user dans twig ça peut servir
        $plural = $plural ?? $singular . 's';
        
        $str = $count === 1 ? $singular : $plural;
        return "$count $str";
    }
}
