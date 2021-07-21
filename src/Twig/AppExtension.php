<?php

namespace App\Twig;

use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Inflector\EnglishInflector;
use Symfony\Component\String\Inflector\FrenchInflector;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppExtension extends AbstractExtension
{   
    private $security;
    public function __construct(Security $security , SluggerInterface $slugger)
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
        $inflector = new FrenchInflector();

        //dd($inflector->pluralize('personne')); mets au pluriel
        //dd($inflector->singularize('personne')); mets au singulier
        //dd($this->security->getUser()); Intéressant pour accéder aux user dans twig ça peut servir
        $plural = $plural ?? $singular . 's';
        
        $str = $count === 1 ? $singular : $plural;
        return "$count $str";
    }
}
