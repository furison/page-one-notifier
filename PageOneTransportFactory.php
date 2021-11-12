<?php
namespace App\Notifier\PageOne;

use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;
use Symfony\Component\Notifier\Transport\TransportInterface;

/**
 * @author Alex Antrobus <a.antrobus@bradfordcollege.ac.uk>
 */
class PageOneTransportFactory extends AbstractTransportFactory
{
  public function create(Dsn $dsn): TransportInterface
  {
      $scheme = $dsn->getScheme();

      if ('page-one' !== $scheme) {
          throw new UnsupportedSchemeException($dsn, 'page-one', $this->getSupportedSchemes());
      }

      $user = $this->getUser($dsn);
      $password = $this->getPassword($dsn);
      $from = $dsn->getOption('from');
      $host = 'default' === $dsn->getHost() ? null : $dsn->getHost();

      return (new PageOneTransport($user, $password, $from, $this->client, $this->dispatcher))->setHost($host);
  }

  protected function getSupportedSchemes(): array
  {
      return ['page-one'];
  }
}
