<?php

namespace App\Tests\Form;

use App\Entity\Media;
use App\Form\MediaType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;

class MediaTypeTest extends KernelTestCase
{
    private FormFactoryInterface $formFactory;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->formFactory = static::getContainer()->get(FormFactoryInterface::class);
    }

    public function testMediaTypeFormAsAdmin(): void
    {
        $form = $this->formFactory->create(MediaType::class, new Media(), [
            'is_admin' => true,
        ]);

        $this->assertTrue($form->has('user'));
        $this->assertTrue($form->has('album'));
        $this->assertTrue($form->has('file'));
        $this->assertTrue($form->has('title'));
    }

    public function testMediaTypeFormAsGuest(): void
    {
        $form = $this->formFactory->create(MediaType::class, new Media(), [
            'is_admin' => false,
        ]);

        $this->assertFalse($form->has('user'));
        $this->assertFalse($form->has('album'));
        $this->assertTrue($form->has('file'));
        $this->assertTrue($form->has('title'));
    }
}
