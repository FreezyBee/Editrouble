<?php

namespace FreezyBee\Editrouble\Storage;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="editrouble_content", uniqueConstraints=
 *     {@ORM\UniqueConstraint(name="uniq_record", columns={"namespace", "name", "locale"})})
 */
class DoctrineEntity
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue()
     */
    private int $id = 0;

    /**
     * @ORM\Column(type="string")
     */
    private string $namespace;

    /**
     * @ORM\Column(type="string")
     */
    private string $name;

    /**
     * @ORM\Column(type="string")
     */
    private string $locale;

    /**
     * @ORM\Column(type="text")
     */
    private string $content;

    public function __construct(string $namespace, string $name, string $locale, string $content)
    {
        $this->namespace = $namespace;
        $this->name = $name;
        $this->locale = $locale;
        $this->content = $content;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}
