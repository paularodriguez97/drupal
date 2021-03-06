<?php
namespace Drupal\scraping\Form;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\scraping\Services\ScrapingServices;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class ScrapingFormAnuncios extends ConfigFormBase {

  /**
   * @var ScrapingServices
   */
  protected $scrapingService;
  protected $entityTypeManager;

  /**
   * @param ScrapingServices $scrapingServices
   */
  public function __construct(ScrapingServices $scrapingServices, EntityTypeManager $entityTypeManager)
  {
    $this->scrapingService = $scrapingServices;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * @param ContainerInterface $container
   * @return ConfigFormBase|ScrapingFormAnuncios|static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('scraping.service'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * @return string[]
   */
  protected function getEditableConfigNames()
  {
    return [
      'scraping.adminsettings',
    ];
  }

  /**
   * @return string
   */
  public function getFormId()
  {
    return 'config_scraping_form';
  }

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties(['vid' => 'categoria']);
    $config = $this->config('scraping.adminsettings');
    $options = [];

    if (!empty($terms)){
      foreach ($terms as $key => $term) {
        $options[$key] = $term->getName();
      }
    }
    $form['endpoint'] = [
      '#type' => 'textfield',
      '#title' => 'Ruta absoluta',
      '#required' => true,
      '#default_value' => $config->get('endpoint') ?? '',
    ];

    $form['container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'container-inline'
        ]
      ]
    ];

    $form['container']['category'] = [
      '#type' => 'select',
      '#options' => $options,
      '#title' => 'Seleccione la categoria'
    ];

    $form['container']['update'] = [
      '#type' => 'submit',
      '#value' => 'Actualizar',
      '#submit' => ['::updateEntity'],
      '#validate' => ['::validateForm'],
    ];
    return parent::buildForm($form, $form_state); // TODO: Change the autogenerated stub
  }

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    $url = $form_state->getValue('endpoint');
    $valid = UrlHelper::isValid($url, true);
    if (!$valid) {
      $form_state->setError($form['endpoint'], $this->t('Ingrese una ruta valida'));
    }
    parent::validateForm($form, $form_state); // TODO: Change the autogenerated stub
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $this->config('scraping.adminsettings')
      ->set('endpoint', $form_state->getValue('endpoint'))
      ->save();
    parent::submitForm($form, $form_state); // TODO: Change the autogenerated stub
  }

  public function updateEntity(array &$form, FormStateInterface $form_state) {
    $config = $this->config('scraping.adminsettings');
    $endpint = $config->get('endpoint');
    $idCategory = $form_state->getValue('category');
    $result = [];
    if ($endpint && $idCategory)  {
      $valid = UrlHelper::isValid($endpint, true);
       if ($valid) {
         $category = $this->entityTypeManager->getStorage('taxonomy_term')->load($idCategory);
         $name = $category ? strtolower($category->getName()) : '';
         $endpintEnd = "{$endpint}/{$name}/";
         $result = $this->scrapingService->getContentUrl($endpintEnd);
       }
    }
  }
}
