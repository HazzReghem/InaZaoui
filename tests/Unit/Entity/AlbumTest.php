<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Album;
use PHPUnit\Framework\TestCase;

class AlbumTest extends TestCase
{
    public function testSetNameAndGetName(): void
    {
        $album = new Album();
        $album->setName('Test Album');

        $this->assertSame('Test Album', $album->getName());
    }

    public function testGetIdInitiallyNull(): void
    {
        $album = new Album();
        $this->assertNull($album->getId());
    }
}
