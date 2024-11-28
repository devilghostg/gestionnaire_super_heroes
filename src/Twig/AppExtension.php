<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('unique', [$this, 'uniqueFilter']),
        ];
    }

    public function uniqueFilter(array $array): array
    {
        $result = [];
        foreach ($array as $item) {
            $key = $item->getId();
            if (!isset($result[$key])) {
                $result[$key] = $item;
            }
        }
        return array_values($result);
    }
}
