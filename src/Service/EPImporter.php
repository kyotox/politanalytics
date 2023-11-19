<?php

namespace App\Service;

use App\Entity\Contact;
use App\Entity\Person;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DomCrawler\Crawler;

/**
 *
 */
class EPImporter implements ImporterInterface
{
    private string  $xmlData;
    private $mepNodes;
    private Crawler $crawler;
    private EntityManager $em;
    private int $currentIndex = 0;

    /**
     *
     */
    public function __construct(EntityManager $em, $url){
        $this->xmlData = file_get_contents($url);
        $this->crawler = new Crawler($this->xmlData);
        $this->mepNodes = $this->crawler->filterXPath('//mep');
        $this->em = $em;
    }

    /**
     * Import all nodes from list
     * @return array
     */
    public function import() : array
    {
        $result = array();
        foreach ($this->mepNodes as $mepNode) {
            $result[] = $this->savePerson($mepNode);
        }
        return $result;
    }

    /**
     * import only one element, walk the full array of nodes
     * with control on each element
     * @return bool|Person
     */
    public function importOne() : bool|Person
    {
        if (!$currentNode = $this->mepNodes->getNode($this->currentIndex))
            return false;
        $this->currentIndex++;
        return $this->savePerson($currentNode);
    }

    /**
     * @return int
     */
    public function getTotalCount() :int
    {
        return count($this->mepNodes);
    }

    /**
     * @param $mepNode
     * @return Person
     */
    private function savePerson($mepNode) : Person {
        $mepCrawler = new Crawler($mepNode);
        $fullName = $mepCrawler->filterXPath('//fullName')?->text();
        $id       = $mepCrawler->filterXPath('//id')->text();
        $country  = $mepCrawler->filterXPath('//country')?->text();
        $politicalGroup         = $mepCrawler->filterXPath('//politicalGroup')->text('');
        $nationalPoliticalGroup = $mepCrawler->filterXPath('//nationalPoliticalGroup')->text('');

        $repo = $this->em->getRepository(Person::class);
        $person = $repo->findOneBy(['externalId' => $id]);
        if(!$person){
            $person = new Person();
        }

        $person->setName($fullName);
        $person->setExternalId($id);
        $person->setCountry($country);
        $person->setPoliticalGroup($politicalGroup);
        $person->setNationalPoliticalGroup($nationalPoliticalGroup);
        $person->setSource(Person::DATASOURCE_EUROPARL);
        $this->em->persist($person);
        $this->em->flush();

        return $person;
    }
}