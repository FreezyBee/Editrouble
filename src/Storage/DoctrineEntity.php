<?php

namespace FreezyBee\Editrouble\Storage;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;

/**
 * @ORM\Entity
 * @ORM\Table(name="editrouble_content", uniqueConstraints=
 *     {@ORM\UniqueConstraint(name="uniq_record", columns={"namespace", "name", "locale"})})
 */
class DoctrineEntity
{
    use Identifier;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $namespace;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $locale;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $content;

    /**
     * DoctrineEntity constructor.
     * @param $namespace
     * @param $name
     * @param $locale
     * @param $content
     */
    public function __construct($namespace, $name, $locale, $content)
    {
        $this->namespace = $namespace;
        $this->name = $name;
        $this->locale = $locale;
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param mixed $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param mixed $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
}
