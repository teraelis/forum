<?php

namespace TerAelis\UserBundle\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class RegistrationMessageService
{
    private $location;

    /**
     * RegistrationMessageService constructor.
     */
    public function __construct(KernelInterface $kernel, $message_location) {
        $this->location = $kernel->getRootDir().$message_location;
        $this->fs = new Filesystem();
    }

    public function hasMessage() {
        return $this->fs->exists($this->location);
    }

    public function getMessage() {
        return file_get_contents($this->location);
    }

    public function updateMessage($message) {
        $this->fs->dumpFile($this->location, $message);
    }
}