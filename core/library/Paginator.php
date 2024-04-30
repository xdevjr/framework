<?php

namespace core\library;

class Paginator
{

    private array $classes = [
        "ul" => "pagination",
        "li" => "page-item",
        "a" => "page-link",
        "active" => "page-item active",
    ];

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

    public function setClasses(string $ul, string $li, string $a, string $active): void
    {
        $this->classes = [
            "ul" => $ul,
            "li" => $li,
            "a" => $a,
            "active" => $active,
        ];
    }

    public function generateLinks(): string
    {
        $html = "<nav>
                            <ul class='{$this->classes['ul']}'>
                            <li class='{$this->classes['li']}'>
                                <a href='{$this->link}1' class='{$this->classes['a']}'>Primeira</a>
                            </li>";

        for ($i = $this->currentPage - $this->maxLinksPerPage; $i <= $this->currentPage - 1; $i++) {
            if ($i > 0)
                $html .= "<li class='{$this->classes['li']}'><a class='{$this->classes['a']}' href='{$this->link}{$i}'>{$i}</a></li>";
        }

        $html .= "<li class='{$this->classes['active']}'>
                                    <a class='{$this->classes['a']}'>{$this->currentPage}</a>
                                </li>";

        for ($i = $this->currentPage + 1; $i <= $this->currentPage + $this->maxLinksPerPage; $i++) {
            if ($i <= $this->getTotalPages())
                $html .= "<li class='{$this->classes['li']}'><a class='{$this->classes['a']}' href='{$this->link}{$i}'>{$i}</a></li>";
        }

        $html .= "<li class='{$this->classes['li']}'>
                                <a class='{$this->classes['a']}' href='{$this->link}{$this->getTotalPages()}'>Ultima</a>
                            </li>
                            </ul>
                        </nav>";

        return $html;
    }
}
