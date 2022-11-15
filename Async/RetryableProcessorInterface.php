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

namespace Aligent\AsyncEventsBundle\Async;

use Aligent\AsyncEventsBundle\Exception\RetryableException;
use Oro\Component\MessageQueue\Transport\MessageInterface;

interface RetryableProcessorInterface
{

    /**
     * @param MessageInterface $message
     * @throws RetryableException
     * @return string
     */
    public function execute(MessageInterface $message);
}
