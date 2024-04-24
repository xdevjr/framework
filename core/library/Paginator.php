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
        private int $maxLinksPerPage = 5,
        private string $link = '?page='
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

    public function setClasses(string $ul, string $li, string $a, string $active)
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

        $before = max(1, $this->currentPage - floor($this->maxLinksPerPage / 2));
        $after = min($this->getTotalPages(), $this->currentPage + floor($this->maxLinksPerPage / 2));

        for ($i = $before; $i <= $after; $i++) {
            if ($i == $this->currentPage) {
                $html .= "<li class='{$this->classes['active']}'>
                                    <a class='{$this->classes['a']}'>{$i}</a>
                                </li>";
            } else {
                $html .= "<li class='{$this->classes['li']}'><a class='{$this->classes['a']}' href='{$this->link}{$i}'>{$i}</a></li>";
            }
        }

        $html .= "<li class='{$this->classes['li']}'>
                                <a class='{$this->classes['a']}' href='{$this->link}{$this->getTotalPages()}'>Ultima</a>
                            </li>
                            </ul>
                        </nav>";

        return $html;
    }
}
