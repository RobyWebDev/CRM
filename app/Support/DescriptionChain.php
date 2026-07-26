<?php

namespace App\Support;

/**
 * Rob kérése (2026-07-26): a leírás-lánc (Lead → Deal → Project/Retainer) mentén
 * látszódjon, melyik szövegrész melyik fázisban került hozzá, pontos dátummal/idővel.
 * Csak a fázisok KÖZÖTTI automatikus átvételnél jelöl — egy fázison belüli szabad
 * szerkesztés (pl. a Deal leírásának kézi módosítása) továbbra is sima szöveg marad,
 * nem generál új bejegyzést minden mentésnél.
 */
class DescriptionChain
{
    public static function appendPhaseEntry(?string $existingDescription, string $label, ?string $content = null): string
    {
        $header = '['.$label.' — '.now()->format('Y.m.d. H:i').']';
        $block = ($content !== null && trim($content) !== '') ? $header."\n".trim($content) : $header;

        return trim(implode("\n\n", array_filter([$existingDescription, $block], fn ($part) => $part !== null && $part !== '')));
    }
}
