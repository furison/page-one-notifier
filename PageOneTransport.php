<?php
namespace App\Notifier\PageOne;

use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Exception\UnsupportedMessageTypeException;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Alex Antrobus <a.antrobus@bradfordcollege.ac.uk>
 */
final class PageOneTransport extends AbstractTransport
{
    protected const HOST = 'www.oventus.com/rest/v1';

    private $user;
    private $passwd;
    private $from;

    public function __construct(string $user, string $passwd, string $from, HttpClientInterface $client = null, EventDispatcherInterface $dispatcher = null)
    {
        $this->user = $user;
        $this->passwd = $passwd;
        $this->from = $from;

        parent::__construct($client, $dispatcher);
    }

    public function __toString(): string
    {
        return sprintf('page-one://%s?from=%s', $this->getEndpoint(), $this->from);
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof SmsMessage;
    }

    protected function doSend(MessageInterface $message): SentMessage
    {
        if (!$message instanceof SmsMessage) {
            throw new UnsupportedMessageTypeException(__CLASS__, SmsMessage::class, $message);
        }

        $endpoint = sprintf('https://%s/%s/message?password=%s', $this->getEndpoint(), $this->user, $this->passwd);
        $response = $this->client->request('POST', $endpoint, [
            'body' => [
                'from' => $this->from,
                'to' => $message->getPhone(),
                'message' => $message->getSubject(),
            ],

        ]);

        try {
            $statusCode = $response->getStatusCode();
        } catch (TransportExceptionInterface $e) {
            throw new TransportException('Could not reach the remote Oventus server.', $response, 0, $e);
        }

        $content = $response->getContent(false);

        libxml_use_internal_errors(true);
        $xmlData = simplexml_load_string($content);
        $xmlErrors = libxml_get_errors();
        libxml_clear_errors();

        if (200 !== $statusCode && 201 !== $statusCode) {
            if (strpos($content, '<?xml') !==0) //handle non-xml response
            {
              throw new TransportException(sprintf('Unable to send the SMS: "%s" (%s).', $content, $statusCode), $response);
            }
            else {
              throw new TransportException(sprintf('Unable to send the SMS: "%s" (%s).', $xmlData->Description, $statusCode), $response);
            }
        }

        if (count($xmlErrors) !== 0) {
            throw new TransportException(sprintf('Unable to read the response: "%s".', $xmlErrors[0]->message), $response);
        }

        $sentMessage = new SentMessage($message, (string) $this);
        $sentMessage->setMessageId($xmlData['transactionID']);

        return $sentMessage;
    }
}
