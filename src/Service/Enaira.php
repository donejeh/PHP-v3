<?php

namespace Flutterwave\Service;

use Flutterwave\Contract\ConfigInterface;
use Flutterwave\Contract\Payment;
use Flutterwave\EventHandlers\ApplePayEventHandler;
use Flutterwave\Entities\Payload;
use Flutterwave\Traits\Group\Charge;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Client\ClientExceptionInterface;
use stdClass;

class Enaira extends Service implements Payment
{
    use Charge;

    public const TYPE = 'enaira';
    private ApplePayEventHandler $eventHandler;

    public function __construct(?ConfigInterface $config = null)
    {
        parent::__construct($config);

        $endpoint = $this->getEndpoint();
        $this->url = $this->baseUrl . '/' . $endpoint . '?type=';
        $this->eventHandler = new ApplePayEventHandler($config);
    }

    /**
     * @return array
     *
     * @throws GuzzleException
     */
    public function initiate(Payload $payload): array
    {
        return $this->charge($payload);
    }

    /**
     * @param  Payload $payload
     * @return array
     *
     * @throws ClientExceptionInterface
     */
    public function charge(Payload $payload): array
    {
        $this->logger->notice('Enaira Service::Started Charging Process ...');

        if($payload->has('is_qr') || $payload->has('is_token')) {
            dd($payload);
        }

        $payload = $payload->toArray();

//        dd($payload);

        //request payload
        $body = $payload;

        ApplePayEventHandler::startRecording();
        $request = $this->request($body, 'POST', self::TYPE);
        ApplePayEventHandler::setResponseTime();
        return $this->handleAuthState($request, $body);
    }

    public function save(callable $callback): void
    {
        // TODO: Implement save() method.
    }

    /**
     * @param array $payload
     *
     * @return array
     */
    private function handleAuthState(stdClass $response, array $payload): array
    {
        return $this->eventHandler->onAuthorization($response, ['logger' => $this->logger]);
    }
}