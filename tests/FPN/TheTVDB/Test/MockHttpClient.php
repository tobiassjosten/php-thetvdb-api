<?php

/*
 * This file is part of the TheTVDB.
 *
 * (c) 2010-2012 Fabien Pennequin <fabien@pennequin.me>
 * (c) 2012 Tobias Sjösten <tobias.sjosten@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FPN\TheTVDB\Test;

use FPN\TheTVDB\HttpClient\HttpClientInterface;

class MockHttpClient implements HttpClientInterface
{
    public $requestUrl;
    public $requestBody;

    public function get($url)
    {
        $this->requestUrl = $url;
        return $this->requestBody;
    }

    public function mockRequestBody($method, $number = 1)
    {
        $file = __DIR__."/Fixtures/{$method}_$number.xml";

        if (!is_readable($file)) {
            throw new \RuntimeException("Could not find file: $file");
        }

        $this->requestBody = file_get_contents($file);
    }
}
