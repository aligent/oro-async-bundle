<?php
/**
 *
 *
 * @category  Aligent
 * @package
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2019 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\AsyncBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;

/**
 * Class FailedJob
 * @package Aligent\AsyncBundle\Entity
 * @ORM\Entity()
 * @ORM\Table(
 *      name="aligent_failed_job",
 * )
 */
class FailedJob implements DatesAwareInterface
{
    use DatesAwareTrait;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="topic", type="string")
     */
    protected $topic;

    /**
     * @var array
     *
     * @ORM\Column(name="body", type="json_array")
     */
    protected $body;

    /**
     * @var string
     *
     * @ORM\Column(name="exception", type="string", nullable=true)
     */
    protected $exception;

    /**
     * @var string
     *
     * @ORM\Column(name="trace", type="text", nullable=true)
     */
    protected $trace;

    /**
     * FailedJob constructor.
     * @param $topic
     * @param $body
     * @param \Exception $exception
     */
    public function __construct(
        $topic,
        array $body,
        \Exception $exception = null
    )
    {
        $this->topic = $topic;
        $this->body = $body;

        if ($exception) {
            $this->exception = $exception->getMessage();
            $this->trace = $exception->getTraceAsString();
        }
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return FailedJob
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * @param mixed $topic
     * @return FailedJob
     */
    public function setTopic($topic)
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * @return array
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param array $body
     * @return FailedJob
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param mixed $exception
     * @return FailedJob
     */
    public function setException($exception)
    {
        $this->exception = $exception;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTrace()
    {
        return $this->trace;
    }

    /**
     * @param mixed $trace
     * @return FailedJob
     */
    public function setTrace($trace)
    {
        $this->trace = $trace;

        return $this;
    }
}