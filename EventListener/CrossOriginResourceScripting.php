<?php
/**
 * @author Tim Hemming <timhemming@gmail.com>
 */
namespace THemming\WebServiceUtilsBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Monolog\Logger;

class CrossOriginResourceScripting
{
    /** @var Logger */
    protected $logger;
    protected $allowed;
    protected $enabled;

    public function __construct(Logger $logger, $enabled = false, $allowed = '*') {
        $this->logger = $logger;
        $this->allowed = $allowed;
        $this->enabled = $enabled;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$this->enabled) {
            return;
        }

        if(HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $response = $event->getResponse();
        $response->headers->add(array('Access-Control-Allow-Origin' => $this->allowed));

        $this->logger->debug("Added cross origin resource scripting (CORS) HTTP header to response. Allowing {$this->allowed}");
    }
}
