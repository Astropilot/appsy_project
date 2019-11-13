<?php

class Paginator {

    private $page;
    private $pageSize;

    public function __construct(int $page, int $pageSize) {
        $this->page = $page;
        $this->pageSize = $pageSize;
    }

    public function paginate(array $serie): array {
        $startIndex = ($this->page - 1) * $this->pageSize;
        $serieLength = count($serie);
        $seriePaginate = array_slice($serie, $startIndex, $this->pageSize);

        return array(
            "paginator" => array(
                "page" => $this->page,
                "pageSize" => $this->pageSize,
                "total" => $serieLength
            ),
            "data" => $seriePaginate,
        );
    }
}
