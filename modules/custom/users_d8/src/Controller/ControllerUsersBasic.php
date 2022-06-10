<?php

namespace Drupal\users_d8\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ControllerUsersBasic
 * @package Drupal\users_d8\Controller
 */
class ControllerUsersBasic extends ControllerBase {
  /**
   * @var Connection
   */
  protected $database;

  /**
   * ControllerUsersBasic constructor.
   * @param Connection $connection
   */
  public function __construct(Connection $connection)
  {
    $this->database = $connection;
  }

  /**
   * @param ContainerInterface $container
   * @return ControllerBase|static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * Controlador para listar usuarios.
   * @return array
   *
   */
  public function listUser()
  {
    $query = $this->database->select('myusers', 'ud8');
    $query->fields('ud8',['id', 'name']);
    $pager = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(10);
    $results = $pager->execute();
    $output = [];

    foreach ($results as $value) {
      $output[$value->id] = [
        'id' => $value->id,
        'name' => $value->name,
      ];
    }
    $header = [
      'id' => 'ID',
      'name' => 'Nombre'
    ];
    return [
      'link' => [
        '#type' => 'link',
        '#title' => $this->t('Exportar registros'),
        '#url' => Url::fromRoute('users_d8.export'),
        '#attributes' => [
          'class' => [
            'button',
          ],
        ],
      ],
      'table' => [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $output,
        '#empty' => $this->t('No se encontro información'),
      ],
      'pager' => [
        '#type' => 'pager',
      ]
    ];
  }


  /**
   * Controlador para generar archivo csv
   * @return Response
   */
  public function exportUser()
  {
    $query = $this->database->select('myusers', 'ud8');
    $query->fields('ud8',['id', 'name']);
    $results = $query->execute()->fetchAll();
    $header = [
      'id' => 'id',
      'name' => 'name'
    ];
    $output = '';
    foreach ($header as $key => $value) {
      if ($key == 'name') {
        $output .= $value . "\r\n";
      }
      else {
        $output .= $value . ",";
      }
    }

    foreach ($results as $key => $result) {
      $output .= $result->id . ",". $result->name . "\r\n";
    }
    $response = new Response();
    // Set headers
    $response->headers->set('Content-Type', 'application/excel; charset=utf-8');
    $response->headers->set('Content-Disposition', 'attachment; filename=reporte.csv');
    $response->headers->set('Expires', '0');
    $response->headers->set('Cache-Control', 'must-revalidate');
    $response->headers->set('Pragma', 'public');
    $response->setContent($output);

    return $response;
  }

  /**
   * Vista con los logs de incio y registro de usuario.
   * @return array
   */
  public function logUsers()
  {
    $query = $this->database->select('access_log_user', 'alu');
    $query->fields('alu',['id_log', 'fecha', 'ip', 'uid', 'tipo_log']);
    $pager = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(10);
    $results = $pager->execute();
    $output = [];

    foreach ($results as $value) {
      $output[$value->id_log] = [
        'fecha' => $value->fecha,
        'ip' => $value->ip,
        'uid' => $value->uid,
        'tipo_log' => $value->tipo_log,
      ];
    }
    $header = [
      'fecha' => 'Fecha',
      'ip' => 'IP',
      'uid' => 'UID',
      'tipo_log' => 'Tipo de log',
    ];
    return [
      'table' => [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $output,
        '#empty' => $this->t('No se encontro información'),
      ],
      'pager' => [
        '#type' => 'pager',
      ]
    ];
  }

}
