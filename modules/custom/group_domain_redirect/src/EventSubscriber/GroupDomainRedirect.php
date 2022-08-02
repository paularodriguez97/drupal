<?php

namespace Drupal\group_domain_redirect\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\domain\Entity\Domain;



/**
 * Class GroupDomainRedirect.
 */
class GroupDomainRedirect implements EventSubscriberInterface {

  /**
   *
   */
  public function groupDomainRedirect(GetResponseEvent $event) {
    $request = $event->getRequest();

    // Verifica si la ruta es la de un grupo
    if ($request->attributes->get('_route') !== 'entity.group.canonical') {
        return;
    }
    // Obtiene el hostname actual
    $host = $request->getHost();
    // Obtiene el ID del grupo
    $id = $request->attributes->get('group')->id();
    // Se carga la información del dominio de este grupo
    $domain = Domain::load('group_' . $id);
    // Solo si hay un dominio asociado
    if($domain){
      $hostname = $domain->getHostname();
      // Siempre y cuando el hostname de la petición sea diferente al del redireccionamiento
      if($host != $hostname){
        $response = new TrustedRedirectResponse('http://'.$hostname);
        $event->setResponse($response);
      }
    }


    return;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {

    $events[KernelEvents::REQUEST][] = ['groupDomainRedirect',30];
    return $events;

  }

}