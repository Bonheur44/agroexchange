<?php

namespace App\Entity;

use App\Repository\TestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TestRepository::class)]
class Test
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(options:['default' => 0])]
    private ?int $test = 0;

    #[ORM\Column(nullable: true)]
    private ?int $t = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTest(): ?int
    {
        return $this->test;
    }

    public function setTest(int $test): self
    {
        $this->test = $test;

        return $this;
    }

    public function getT(): ?int
    {
        return $this->t;
    }

    public function setT(?int $t): self
    {
        $this->t = $t;

        return $this;
    }
}
