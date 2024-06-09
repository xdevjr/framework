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

    /**
     * @param bool $firstlast if true the first and last links will be shown
     * @param bool $nextprev if true the previous and next links will be shown
     * @return array
     */
    public function links(bool $firstlast = true, bool $nextprev = true): array
    {
        if ($firstlast) {
            $linksArray["first"] = [
                "link" => "{$this->link}1",
                "text" => "First"
            ];
        }

        if ($nextprev) {
            $prev = max($this->currentPage - 1, 1);
            $linksArray["previous"] = [
                "link" => "{$this->link}{$prev}",
                "text" => "<<"
            ];
        }

        $startLink = max(1, $this->currentPage - floor($this->maxLinksPerPage / 2));
        $endLink = min($startLink + $this->maxLinksPerPage - 1, $this->getTotalPages());
        for ($i = $startLink; $i <= $endLink; $i++) {
            if ($i == $this->currentPage) {
                $linksArray["current"] = [
                    "link" => "{$this->link}{$i}",
                    "text" => $i
                ];
            } else {
                $linksArray[$i] = [
                    "link" => "{$this->link}{$i}",
                    "text" => $i
                ];
            }
        }

        if ($nextprev) {
            $next = min($this->currentPage + 1, $this->getTotalPages());
            $linksArray["next"] = [
                "link" => "{$this->link}{$next}",
                "text" => ">>"
            ];
        }
        if ($firstlast) {
            $linksArray["last"] = [
                "link" => "{$this->link}{$this->getTotalPages()}",
                "text" => "Last"
            ];
        }

        return $linksArray;
    }

    /**
     * @param bool $firstlast if true the first and last links will be shown
     * @param bool $nextprev if true the previous and next links will be shown
     * @return string
     */
    public function bootstrap(bool $firstlast = true, bool $nextprev = true): string
    {
        $links = "<nav aria-label='Pagination'><ul class='pagination'>";
        foreach ($this->links($firstlast, $nextprev) as $key => $item) {
            extract($item);
            if ($key == 'current')
                $links .= "<li class='page-item'><span class='page-link active'>{$text}</span></li>";
            else
                $links .= "<li class='page-item'><a class='page-link' href='{$link}'>{$text}</a></li>";
        }

        $links .= "</ul></nav>";

        return $links;
    }
}
