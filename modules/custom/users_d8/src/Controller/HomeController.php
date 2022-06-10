<?php

namespace Drupal\users_d8\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Class HomeController
 * @package Drupal\users_d8\Controller
 */
class HomeController extends ControllerBase {
  /**
   * @return array
   */
  public function view(){
    return [
      '#type' => 'dropbutton',
      '#links' => [
        [
          'title' => 'Registrar usuario',
          'url' => Url::fromRoute('users_d8.register_user'),
        ],
        [
          'title' => 'Consultar usuarios',
          'url' => Url::fromRoute('users_d8.list_user'),
        ],
        [
          'title' => 'Importar usuarios',
          'url' => Url::fromRoute('users_d8.import'),
        ],
        [
          'title' => 'Exportar registros',
          'url' => Url::fromRoute('users_d8.export'),
        ],
        [
          'title' => 'Registro accesos de usuarios',
          'url' => Url::fromRoute('users_d8.log_access'),
        ]
      ],
    ];
  }
}
