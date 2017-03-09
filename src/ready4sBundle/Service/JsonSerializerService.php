<?php

namespace ready4sBundle\Service;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class JsonSerializerService
{

    /**
     * @param $object
     * @return string
     */
    public static function serializeObjectToJson($object)
    {

        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer();
        $normalizer->setCircularReferenceHandler(function () {
            return 3;
        });

        $serializer = new Serializer(array($normalizer), array($encoder));
        $jsonContent = $serializer->serialize($object, 'json');

        return $jsonContent;
    }

}