<?php

namespace Drupal\users_d8\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ImportUser
 * @package Drupal\users_d8\Form
 */
class ImportUser extends FormBase
{
  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * @var MessengerInterface
   */
  protected $messenger;
  /**
   * @var Connection
   */
  protected $database;

  /**
   * ImportUser constructor.
   * @param EntityTypeManagerInterface $entityTypeManager
   * @param MessengerInterface $messenger
   * @param Connection $connection
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, MessengerInterface $messenger, Connection $connection)
  {
    $this->entityTypeManager = $entityTypeManager;
    $this->messenger = $messenger;
    $this->database = $connection;
  }

  /**
   * @param ContainerInterface $container
   * @return FormBase|static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('messenger'),
      $container->get('database')
    );
  }

  /**
   * @inheritDoc
   */
  public function getFormId()
  {
    return 'users_d8_import';
  }

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $form['file_csv'] = [
      '#type' => 'managed_file',
      '#title' => 'Archivo',
      '#upload_location' => 'public://',
      '#name' => 'file_csv',
      '#upload_validators' => [
        'file_validate_extensions' => ['csv'],
      ],
      '#required' => TRUE
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Importar'),
    ];
    return $form;
  }

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $fileValue = $form_state->getValue('file_csv');
    $content = $this->contentFile($fileValue[0]);

    if (!empty($content)) {
      $limit = 1;
      $chunks_data = array_chunk($content, $limit);
      foreach ($chunks_data as $chunk_data) {
        $operations[] = [$this->insertUser($chunk_data[0])];
      }
      $batch = [
        'title' => 'Insert Users ...',
        'operations' => $operations,
        'finished' => $this->finishedBatch(),
      ];
      batch_set($batch);
    }
  }

  /**
   * @param $fileId
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function contentFile($fileId) {
    $url = $this->entityTypeManager->getStorage('file')->load($fileId)->url();
    $data = [];
    if(!file_exists($url) || !is_readable($url)){
      $header = NULL;

      if (($handle = fopen($url, 'r')) !== FALSE ) {
        while (($row = fgetcsv($handle)) !== FALSE) {
          if(!$header){
            $header = $row;
          }else{
            $data[] = array_combine($header, $row);
          }
        }
        fclose($handle);
      }
    }

    return $data;
  }

  /**
   * @param $data
   */
  public function insertUser($data){
    $query = $this->database->select('myusers', 'ud8');
    $query->addField('ud8','name');
    $query->condition('ud8.name', $data['name'], '=');
    $result = $query->execute()->fetchObject();
    if ($result) {
      $this->messenger->addMessage($this->t('El usuario @name ya se encuentra registrado.', ['@name' => $data['name']]), 'error');
    }else {
      $this->database->insert('myusers')
        ->fields(['name'])
        ->values([
          'name' => $data['name'],
        ])->execute();
    }
  }

  /**
   *
   */
  public function finishedBatch(){
    $this->messenger->addMessage($this->t("Termino de ejecutar"));
  }
}
