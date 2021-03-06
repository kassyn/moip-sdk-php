<?php

namespace Moip\Resource;

use JsonSerializable;
use Moip\Http\HTTPRequest;
use Moip\Moip;
use stdClass;

abstract class MoipResource implements JsonSerializable
{
    /**
     * @var \Moip\Moip
     */
    protected $moip;

    /**
     * @var \stdClass
     */
    protected $data;

    /**
     * Initialize a new instance.
     */
    abstract protected function initialize();

    /**
     * Mount information of a determined object.
     * 
     * @param \stdClass $response
     *
     * @return \stdClass
     */
    abstract protected function populate(stdClass $response);

    /**
     * Create a new instance.
     * 
     * @param \Moip\Moip $moip
     */
    public function __construct(Moip $moip)
    {
        $this->moip = $moip;
        $this->data = new stdClass();
        $this->initialize();
    }

    /**
     * Create a new connecttion.
     * 
     * @return \Moip\Moip
     */
    protected function createConnection()
    {
        return $this->moip->createConnection();
    }

    /**
     * Get a key of an object if he exist.
     * 
     * @param string         $key
     * @param \stdClass|null $data
     *
     * @return \stdClass|string|null
     */
    protected function getIfSet($key, stdClass $data = null)
    {
        if ($data == null) {
            $data = $this->data;
        }

        if (isset($data->$key)) {
            return $data->$key;
        }
    }

    /**
     * Specify data which should be serialized to JSON.
     * 
     * @return \stdClass
     */
    public function jsonSerialize()
    {
        return $this->data;
    }

    public function getByPath($path = '/')
    {
        $httpConnection = $this->createConnection();
        $httpConnection->addHeader('Content-Type', 'application/json');

        $httpResponse = $httpConnection->execute($path, HTTPRequest::GET);

        if ($httpResponse->getStatusCode() != 200) {
            throw new RuntimeException($httpResponse->getStatusMessage(), $httpResponse->getStatusCode());
        }

        return $this->populate(json_decode($httpResponse->getContent()));
    }
}
