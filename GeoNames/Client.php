<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 20.01.15
 * Time: 23:44
 */

namespace GeoNames;

/**
 * Client Class to perform methods with SOAP.
 * Mark method as final if you want to see them in available methods list
 * @package GeoNames
 */
class Client implements ClientInterface {

    /* #### Client Methods Start #### */

    /**
     * @see ClientInterface::geolocate()
     * @param double $lat
     * @param double $lng
     * @return float
     * @throws \InvalidArgumentException
     * @throws \HttpResponseException
     */
    final function geolocate($lat, $lng) {
        if (empty($lat) || empty($lng))
            throw new \InvalidArgumentException("Missing lat and/or lng parameter(s)");
        $lat = doubleval($lat);
        $lng = doubleval($lng);
        $url = "http://api.geonames.org/findNearby?lat=$lat&lng=$lng&username=gund";
        $xml = new \SimpleXMLElement(self::sendGetRequest($url));
        if ($xml->count() === 0) throw new \HttpResponseException("Response is empty");
        // Get geonameId
        $geonameId = $xml->geoname->geonameId;
        return doubleval($geonameId);
    }

    /**
     * @see ClientInterface::describe()
     * @param double $geonameId
     * @return array
     * @throws \InvalidArgumentException
     * @throws \HttpResponseException
     */
    final function describe($geonameId) {
        if (empty($geonameId))
            throw new \InvalidArgumentException("Missing geonameId parameter");
        $geonameId = doubleval($geonameId);
        $url = "http://api.geonames.org/get?geonameId=$geonameId&style=full&username=gund";
        $xml = new \SimpleXMLElement(self::sendGetRequest($url));
        if ($xml->count() === 0) throw new \HttpResponseException("Response is empty");
        return self::xml2array($xml);
    }

    /* #### Client Methods End #### */


    /* #### Helper Methods ####*/

    /** Send get request to a specific URL
     * @param $url
     * @return string
     * @throws \HttpException
     * @throws \HttpUrlException
     */
    private static function sendGetRequest($url) {
        if (empty($url)) throw new \HttpUrlException("Url cannot be empty");
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) throw new \HttpException("Failed to get response from $url");

        return $response;
    }

    /** Convert XML object to array recursively (all attributes will be lost)
     * @param \SimpleXMLElement $xmlObject
     * @param array $out
     * @return array
     */
    private static function xml2array(\SimpleXMLElement $xmlObject, $out = array()) {
        foreach ((array)$xmlObject as $index => $node)
            $out[$index] = (is_object($node)) ? self::xml2array($node) : $node;

        return $out;
    }

    /** Get all Client Service methods
     * @return \ReflectionMethod[]
     */
    static function getMethods() {
        $clientInterface = new \ReflectionClass(new self());
        $methods = $clientInterface->getMethods(\ReflectionMethod::IS_FINAL);
        return $methods;
    }

    /** Get type of method or parameter
     * @param \ReflectionMethod $method
     * @param string $type "return" for method, "param" for parameter
     * @param string $parameter
     * @return string
     */
    static function getParameterType(\ReflectionMethod $method, $type = 'return', $parameter = '') {
        $doc = $method->getDocComment();
        if (empty($doc)) return "";
        $matches = array();
        $pattern = "/@$type ([a-zA-Z0-9\\\\]+(\s?\[\])?)" . ($type == 'param' ? " \\$$parameter" : '') . "/";
        if (!preg_match($pattern, $doc, $matches)) return "";
        else return $matches[1];
    }
}