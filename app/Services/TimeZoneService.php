<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TimeZoneService
{
    protected $host   = 'http://www.geoplugin.net/json.gp';
    protected $filter = null;
    protected $ip     = null;
    protected $json   = null;
    private $data     = [];

    public function execute(Request $request)
    {
        $ip                     = $request->ip();
        $timezone               = $this->getTimezone($ip);
        $this->data['timezone'] = $timezone;
        return $this->data;
    }

    private function getTimezone($ip)
    {
        try {
            // $response = Http::retry(3, 200)->timeout(20)->get($this->host, ['ip' => $ip]);
            $response = Http::timeout(20)->get($this->host, ['ip' => $ip]);
            if ($response->successful()) {
                $json = $response->json();
                return $json['geoplugin_timezone'] ?? config('app.timezone');
            }
        } catch (\Exception $e) {
            logError('Error fetching timezone: ', $e);
        }
        return config('app.timezone');
    }

    /**
     * convert the json string to php array
     * based on the filter property
     *
     * @return array
     */
    public function toArray()
    {
        // condition(s)
        if ($this->filter != true) {
            return json_decode($this->json, true);
        }

        // filtering & returning
        return $this->__filter();
    }

    /**
     * convert the json string to php object
     * based on the filter property
     *
     * @return object
     */
    public function toObject()
    {
        // condition(s)
        if ($this->filter != true) {
            return json_decode($this->json);
        }

        // filtering & returning
        return (object) $this->__filter();
    }

    /**
     * return collected location data in the form of json
     * based on the filter property
     *
     * @return string
     */
    public function toJson()
    {
        // condition(s)
        if ($this->filter != true) {
            return $this->json;
        }

        // filtering & returning
        return json_encode($this->__filter());
    }

    /**
     * filter the object keys
     *
     * @return array
     */
    private function __filter()
    {
        // applying filter
        foreach (json_decode($this->json, true) as $key => $item) {
            $return[str_replace('geoplugin_', '', $key)] = $item;
        }

        // returning
        return $return;
    }
}
