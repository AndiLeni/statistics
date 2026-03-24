<?php

namespace AndiLeni\Statistics;

class StatsLazySection
{
    public static function render(DateFilter $filterDateHelper): string
    {
        return self::renderLazyBlock('statistics_lazy_device', 'device', $filterDateHelper)
            . self::renderLazyBlock('statistics_lazy_extended', 'extended', $filterDateHelper)
            . self::renderLazyBlock('statistics_lazy_bots', 'bots', $filterDateHelper);
    }

    private static function renderLazyBlock(string $id, string $blockId, DateFilter $filterDateHelper): string
    {
        return '<div id="' . htmlspecialchars($id, ENT_QUOTES) . '" data-statistics-lazy-block data-block-id="' . htmlspecialchars($blockId, ENT_QUOTES) . '" data-date-start="' . htmlspecialchars($filterDateHelper->date_start->format('Y-m-d'), ENT_QUOTES) . '" data-date-end="' . htmlspecialchars($filterDateHelper->date_end->format('Y-m-d'), ENT_QUOTES) . '" data-state="idle" style="min-height: 160px;"></div>';
    }
}