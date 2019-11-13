<?php
use PHPUnit\Framework\TestCase;

include_once 'utils/Paginator.php';

class PaginatorTest extends TestCase {

    public function testPaginate() {
        $data = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);

        $paginator = new Paginator(1, 3);
        $data_paginate = $paginator->paginate($data);

        $this->assertSame(1, $data_paginate['paginator']['page']);
        $this->assertSame(3, $data_paginate['paginator']['pageSize']);
        $this->assertSame(10, $data_paginate['paginator']['total']);

        $this->assertCount(3, $data_paginate['data']);

        $paginator = new Paginator(2, 4);
        $data_paginate = $paginator->paginate($data);

        $this->assertSame(2, $data_paginate['paginator']['page']);
        $this->assertSame(4, $data_paginate['paginator']['pageSize']);
        $this->assertSame(10, $data_paginate['paginator']['total']);

        $this->assertCount(4, $data_paginate['data']);
    }
}
