<?php

namespace App\Entity;

use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
class Person
{

    const DATASOURCE_EUROPARL = 'europarl';
    const DATASOURCE_OTHER = 'other';
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $country = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $politicalGroup = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nationalPoliticalGroup = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $source = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $externalId = null;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: Contact::class, orphanRemoval: true)]
    private Collection $contacts;

    public function __construct()
    {
        $this->contacts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getFirstName(): ?string
    {
        return trim(str_replace($this->getLastName(),'',$this->name));
    }

    public function getLastName(): ?string
    {
        $names = explode(' ', $this->name);
        return end($names) ?: null;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getPoliticalGroup(): ?string
    {
        return $this->politicalGroup;
    }

    public function setPoliticalGroup(?string $politicalGroup): static
    {
        $this->politicalGroup = $politicalGroup;

        return $this;
    }

    public function getNationalPoliticalGroup(): ?string
    {
        return $this->nationalPoliticalGroup;
    }

    public function setNationalPoliticalGroup(?string $nationalPoliticalGroup): static
    {
        $this->nationalPoliticalGroup = $nationalPoliticalGroup;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): static
    {
        $this->source = $source;

        return $this;
    }
    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setExternalId(?string $externalId): static
    {
        $this->externalId = $externalId;

        return $this;
    }

    /**
     * @return Collection<int, Contact>
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(Contact $contact): static
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts->add($contact);
            $contact->setPerson($this);
        }

        return $this;
    }

    public function removeContact(Contact $contact): static
    {
        if ($this->contacts->removeElement($contact)) {
            // set the owning side to null (unless already changed)
            if ($contact->getPerson() === $this) {
                $contact->setPerson(null);
            }
        }

        return $this;
    }

    public function getApiData() : array
    {
        return array(
                'id' => $this->getId(),
                'firstName' => $this->getFirstName(),
                'lastName' => $this->getLastName(),
                'country' => $this->getCountry(),
                'politicalGroup' => $this->getPoliticalGroup(),
                'contacts' => $this->getApiContacts()
        );
    }

    public function getApiContacts() : array
    {
        $data = [];
        foreach ($this->getContacts() as $contact)
        {
            $data[] = $contact->getApiData();
        }

        return $data;
    }
}
