<?php

namespace Framework\Base\Test\FileUpload;

use Application\Test\Application\Traits\Helpers;

class DummyS3Response
{
    use Helpers;

    public function get(string $key)
    {
        return $key . '.testing.url';
    }
}
