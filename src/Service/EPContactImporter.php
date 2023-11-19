<?php

namespace App\Service;

use App\Entity\Contact;
use App\Entity\Person;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 *
 */
class EPContactImporter
{
    private EntityManagerInterface $em;
    private string $epDetailsUrl = "https://www.europarl.europa.eu/meps/en/";

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em){
        $this->em = $em;
    }

    /**
     * Fetch contact data from person details page
     * @param $externalId
     * @return array
     * @throws \Exception
     */
    public function importContactData($externalId) : array
    {
        $detailsPage = file_get_contents($this->epDetailsUrl.$externalId);
        $crawler = new Crawler($detailsPage);

        $personRepo = $this->em->getRepository(Person::class);
        $person = $personRepo->findOneBy(['externalId' => $externalId]);
        if(!$person)
            throw new \Exception("Person not found in db with external id $externalId.");

        $contactData = $this->importSocial($person, $crawler->filter('.erpl_social-share-horizontal a'));

        foreach ($crawler->filter('#contacts .erpl_contact-card') as $index => $address){
            $formatted = preg_replace('/\s+/', " ", $address->textContent);
            $contactRepo = $this->em->getRepository(Contact::class);
            $contact = $contactRepo->findOneBy(['value' => $formatted, 'person' => $person->getId(), 'type' => Contact::TYPE_ADDRESS]);
            if(!$contact){
                $contact = new Contact();
                $contact->setValue($formatted);
                $contact->setType(Contact::TYPE_ADDRESS);
                $contact->setPerson($person);
                $this->em->persist($contact);
            }
            $contactData["address_".$index] = $formatted;
        }

        $this->em->persist($person);
        $this->em->flush();
        return $contactData;
    }

    /**
     * Go through social links of each person
     * @param $person
     * @param $linkNodes
     * @return array
     */
    private function importSocial(&$person, $linkNodes) : array
    {
        $contactData = array();
        foreach ($linkNodes as $linkNode) {
            $href = $linkNode->getAttribute('href');
            $href = str_replace("[dot]", '.', $href);
            $href = str_replace("[at]", '@', $href);

            $title = strtolower($linkNode->getAttribute('data-original-title'));
            $contactData[$title] = $href;
            switch ($title) {
                case "website":
                    $this->createContact(Contact::TYPE_WEBSITE, $href, $person);
                    break;
                case "e-mail":
                    $href = str_replace('mailto:', '', $href);
                    $this->createContact(Contact::TYPE_EMAIL, $href, $person);
                    $contactData[$title] = $href;
                    break;
                case "facebook":
                    $this->createContact(Contact::TYPE_FACEBOOK, $href, $person);
                    break;
                case "instagram":
                    $this->createContact(Contact::TYPE_INSTAGRAM, $href, $person);
                    break;
                case "twitter":
                    $this->createContact(Contact::TYPE_TWITTER, $href, $person);
                    break;
                default:
                    $contactData[$title] = "NOT SAVED: " . $href;
                    break;
            }
        }
        return $contactData;
    }


    /**
     * Create and save contact entity if not already exists
     * @param $type
     * @param $value
     * @param $person
     * @return Contact
     */
    public function createContact($type, $value, $person) : Contact
    {

        $contactRepo = $this->em->getRepository(Contact::class);
        if($contact = $contactRepo->findOneBy(['value' => $value, 'person' => $person->getId(), 'type' => Contact::TYPE_ADDRESS]))
            return $contact;

        $newContact = new Contact();
        $newContact->setValue($value);
        $newContact->setType($type);
        $newContact->setPerson($person);
        $this->em->persist($newContact);

        return $newContact;
    }
}