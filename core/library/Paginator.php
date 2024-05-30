<?php

namespace core\library;

class Paginator
{
    public function __construct(
        private int $currentPage,
        private int $itemsPerPage,
        private int $totalItems,
        private string $link,
        private int $maxLinksPerPage = 5
    ) {
    }

    public function getOffset(): int
    {
        return $this->itemsPerPage * ($this->currentPage - 1);
    }

    public function getLimit(): int
    {
        return $this->itemsPerPage;
    }

    public function getTotalPages(): int
    {
        return (int) ceil($this->totalItems / $this->itemsPerPage);
    }

    public function generateLinks(bool $array = true): array|string
    {
        $links[] = "<a href='{$this->link}1'>Primeira</a>";

        $startLink = max(1, $this->currentPage - floor($this->maxLinksPerPage / 2));
        $endLink = min($startLink + $this->maxLinksPerPage - 1, $this->getTotalPages());
        for ($i = $startLink; $i <= $endLink; $i++) {
            if($i == $this->currentPage){
                $links[] = "<span>{$i}</span>";
            } else {
                $links[] = "<a href='{$this->link}{$i}'>{$i}</a>";
            }
        }

        $links[] = "<a href='{$this->link}{$this->getTotalPages()}'>Ultima</a>";

        return $array ? $links : "<nav>" . implode($links) . "</nav>";
    }
}
