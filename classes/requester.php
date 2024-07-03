<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Contains helper class to work with fawry REST API.
 *
 * @package    paygw_fawry
 * @copyright  2023 Mo. Farouk <phun.for.physics@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_fawry;

/**
 * All function needed to perform an API workflow with fawry.
 */
class requester {
    /**
     * Merchant Code.
     * @var string
     */
    protected $mcode;
    /**
     * Hash key
     * @var string
     */
    protected $hash;
    /**
     * construct a requester instance
     * @param string $merchentcode
     * @param string $hash
     */
    protected function __construct($merchentcode, $hash) {

        $this->mcode = $merchentcode;
        $this->hash  = $hash;
    }

    /**
     * Perform http post request
     * @param array $data
     * @param string $url
     * @param string $method
     * @return bool|string|\stdClass
     */
    protected function request($data, $url, $method = 'post') {
        global $CFG;
        require_once("$CFG->libdir/filelib.php");

        $method = strtolower($method);

        $curl = curl_init();
        $curl = new \curl;
        $curl->setopt([
            'RETURNTRANSFER' => true,
            'MAXREDIRS'      => 10,
            'TIMEOUT'        => 0,
            'FOLLOWLOCATION' => true,
            'failonerror'    => false,
        ]);
        $curl->setHeader([
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ]);

        if ($method == 'post') {
            $rawresponse = $curl->post($url, json_encode($data));
        } else if ($method == 'get') {
            $rawresponse = $curl->get($url, $data);
        } else {
            return null;
        }

        $response = json_decode($rawresponse);
        $httpcode = $curl->get_info()['http_code'];
        if (!in_array($httpcode, [200, 201])) {
            debugging($rawresponse);
            return $response;
        }
        return $response;
    }
}
