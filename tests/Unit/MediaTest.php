<?php

namespace App\Tests\Entity;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaTest extends TestCase
{
    public function testSetAndGetTitleAndPath(): void
    {
        $media = new Media();
        $media->setTitle('Test Title');
        $media->setPath('/path/to/file.jpg');

        $this->assertSame('Test Title', $media->getTitle());
        $this->assertSame('/path/to/file.jpg', $media->getPath());
    }

    public function testUserAssignment(): void
    {
        $user = new User();
        $media = new Media();
        $media->setUser($user);

        $this->assertSame($user, $media->getUser());
    }

    public function testAlbumAssignment(): void
    {
        $album = new Album();
        $media = new Media();
        $media->setAlbum($album);

        $this->assertSame($album, $media->getAlbum());
    }

    public function testFileAssignment(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $media = new Media();
        $media->setFile($file);

        $this->assertSame($file, $media->getFile());
    }

    public function testInitialIdIsNull(): void
    {
        $media = new Media();
        $this->assertNull($media->getId());
    }
}
