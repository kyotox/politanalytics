<?php

namespace App\Entity;

use App\Repository\ContactRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact
{
    const TYPE_EMAIL = 'email';
    const TYPE_FACEBOOK = 'facebook';
    const TYPE_TWITTER = 'twitter';
    const TYPE_INSTAGRAM = 'instagram';
    const TYPE_WEBSITE = 'website';
    const TYPE_ADDRESS = 'address';
    const TYPE_PHONE = 'phone';
    const TYPE_SOCIAL = 'social';
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'contacts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Person $person = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $value = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): static
    {
        $this->person = $person;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getApiData() : array
    {
        $realType = $this->getType();
        switch ($realType) {
            case Contact::TYPE_ADDRESS:
                $type = Contact::TYPE_ADDRESS;
                break;
            case Contact::TYPE_EMAIL;
                $type = Contact::TYPE_EMAIL;
                break;
//            case Contact::TYPE_FACEBOOK;
//            case Contact::TYPE_TWITTER;
//            case Contact::TYPE_INSTAGRAM;
//                $type = Contact::TYPE_SOCIAL;
//                break;
            default:
                $type = Contact::TYPE_SOCIAL;
                break;
        }

        return array(
            'type' => $type,
            'value' => $this->getValue()
        );
    }
}
