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

use FPN\TheTVDB\Model\TvShow;

class TvShowTest extends \PHPUnit_Framework_TestCase
{
    public function testFromArray()
    {
        $tvShow = new TvShow();
        $tvShow->fromArray(array(
            'id'     => 72218,
            'name'   => 'Smallville',
            'genres' => array('Drama', 'Science-Fiction'),
        ));

        $this->assertEquals(72218, $tvShow->getId());
        $this->assertEquals('Smallville', $tvShow->getName());
        $this->assertEquals(array('Drama', 'Science-Fiction'), $tvShow->getGenres());
    }
}
