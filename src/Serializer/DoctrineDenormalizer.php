<?php

namespace App\Serializer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class DoctrineDenormalizer implements DenormalizerInterface
{

    /**
    *
    * @var EntityManagerInterface
    */
    private $entityManagerInterface;
    
    /**
    * Constructor
    */
    public function __construct(EntityManagerInterface $entityManagerInterface)
    {
        $this->entityManagerInterface = $entityManagerInterface;
    }
    /**
     *
     * @param mixed 
     * @param string 
     * @param string|null 
     */
    public function supportsDenormalization($data, string $type, ?string $format = null): bool
    {

        $dataIsID = is_numeric($data);
        $typeIsEntity = strpos($type, 'App\Entity') === 0;

        return $typeIsEntity && $dataIsID;
    }

    /**

     *
     * @param mixed )
     * @param string 
     * @param string|null
     * @param array 
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = [])
    {

        $denormalizedEntity = $this->entityManagerInterface->find($type, $data);
        
        return $denormalizedEntity;
    }
}