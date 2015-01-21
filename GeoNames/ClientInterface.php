<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 20.01.15
 * Time: 23:53
 */

namespace GeoNames;

/**
 * Interface ClientInterface of available methods for client
 * @package GeoNames
 */
interface ClientInterface {

    /** Get Object Id by latitude and longitude
     * @param double $lat
     * @param double $lng
     * @return double
     */
    function geolocate($lat, $lng);

    /** Get Object information by it's Id
     * @param double $geonameId
     * @return array
     */
    function describe($geonameId);

}