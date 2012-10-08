<?php
/**
 * This can be injected into the Kernel Response event and will log request/response info to the
 * application logger.
 *
 * @author Tim Hemming <timhemming@gmail.com>
 */
namespace THemming\WebServiceUtilsBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Monolog\Logger;

class HttpCapture
{
    /** @var Logger */
    protected $logger;
    protected $enabled;
    protected $maxLength = null;

    public function __construct(Logger $logger, $enabled = false)
    {
        $this->logger = $logger;
        $this->enabled = $enabled;
    }

    public function setMaxLength($length)
    {
        $this->maxLength = $length;
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

        $request = $event->getRequest();
        $response = $event->getResponse();

        $requestHeadersStr = '';
        foreach ($request->headers->all() as $k => $v) {
            $requestHeadersStr .= $k . ': ' . $v[0] . PHP_EOL;
        }

        $responseHeadersStr = '';
        foreach ($response->headers->all() as $k => $v) {
            $responseHeadersStr .= $k . ': ' . $v[0] . PHP_EOL;
        }

        $requestContent = $request->getContent();
        if ($this->maxLength && strlen($requestContent) > $this->maxLength) {
            $requestContent = substr($requestContent, 0, $this->maxLength) . '...';
        } elseif (strlen($requestContent) == 0) {
            $requestContent = 'Request content is empty';
        }

        $responseContent = $response->getContent();
        if ($this->maxLength && strlen($responseContent) > $this->maxLength) {
            $responseContent = substr($responseContent, 0, $this->maxLength) . '...';
        } elseif (strlen($responseContent) == 0) {
            $responseContent = 'Response content is empty';
        }

        $responseStatusCode = $response->getStatusCode();

        $this->logger->info(
            <<<REQUEST

= Request Info =
Path: {$request->getPathInfo()}
Method: {$request->getMethod()}
Remote Host: {$request->server->get('REMOTE_ADDR')}

= Request Headers =
{$requestHeadersStr}

= Request Content=
{$requestContent}

REQUEST
            , array('http_capture', 'request'));

        $this->logger->info(
            <<<RESPONSE

= Response Info =
StatusCode: {$responseStatusCode}

= Response Headers =
{$responseHeadersStr}

= Response Content =
{$responseContent}

RESPONSE
            , array('http_capture', 'response'));
    }
}