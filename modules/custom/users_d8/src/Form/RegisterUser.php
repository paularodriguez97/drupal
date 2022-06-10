<?php
 namespace Drupal\users_d8\Form;

 use Drupal\Core\Ajax\AjaxResponse;
 use Drupal\Core\Ajax\HtmlCommand;
 use Drupal\Core\Ajax\OpenModalDialogCommand;
 use Drupal\Core\Database\Connection;
 use Drupal\Core\Form\FormBase;
 use Drupal\Core\Form\FormStateInterface;
 use Drupal\Core\Messenger\MessengerInterface;
 use Drupal\Core\Render\RendererInterface;
 use Symfony\Component\DependencyInjection\ContainerInterface;

 /**
  * Class RegisterUser
  * @package Drupal\users_d8\Form
  */
 class RegisterUser extends FormBase {
   /**
    * @var Connection
    */
   protected $database;
   /**
    * @var MessengerInterface
    */
   protected $messenger;
   /**
    * @var RendererInterface
    */
   protected $renderer;

   /**
    * RegisterUser constructor.
    * @param Connection $connection
    * @param MessengerInterface $messengerInterface
    * @param RendererInterface $renderer
    */
   public function __construct(Connection $connection, MessengerInterface $messengerInterface, RendererInterface $renderer)
   {
     $this->database = $connection;
     $this->messenger = $messengerInterface;
     $this->renderer = $renderer;
   }

   /**
    * {@inheritdoc}
    * @codeCoverageIgnore
    */
   public static function create(ContainerInterface $container) {
     return new static(
       $container->get('database'),
       $container->get('messenger'),
       $container->get('renderer')
     );
   }

   /**
    * @inheritDoc
    */
   public function getFormId()
   {
     return 'users_d8_register';
   }


   /**
    * @param array $form
    * @param FormStateInterface $form_state
    * @return array
    */
   public function buildForm(array $form, FormStateInterface $form_state)
   {
     $form['message'] = [
       '#type' => 'markup',
       '#markup' => '<div id="result-message"></div>'
     ];

     $form['name'] = [
       '#type' => 'textfield',
       '#title' => $this->t('Nombre'),
       '#required' => TRUE,
       '#attributes' => [
         'class' => ['input-any-text']
       ]
     ];
     $form['submit'] = [
       '#type' => 'submit',
       '#value' => 'Registrar',
       '#ajax' => [
         'callback' => '::submitAjax',
         'event' => 'click'
       ],
     ];
     $form['#attached']['library'][] = 'users_d8/users_d8_main';
     $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
     $form['#attributes']['novalidate'] = '';
     $form['#attributes']['autocomplete'] = 'off';
     return $form;
   }

   /**
    * @param array $form
    * @param FormStateInterface $form_state
    */
   public function validateForm(array &$form, FormStateInterface $form_state)
   {
   }


   /**
    * @param array $form
    * @param FormStateInterface $form_state
    * @throws \Exception
    */
   public function submitForm(array &$form, FormStateInterface $form_state)
   {
   }

   /**
    * @param array $form
    * @param FormStateInterface $form_state
    * @return AjaxResponse
    * @throws \Exception
    */
   public function submitAjax(array &$form, FormStateInterface $form_state) {
     $response = new AjaxResponse();
     $name = $form_state->getValue('name');
     $patron = '/^[a-zA-Z, ]*$/';
     $flag = TRUE;

     $query = $this->database->select('myusers', 'ud8');
     $query->addField('ud8','name');
     $query->condition('ud8.name', $form_state->getValue('name'), '=');
     $result = $query->execute()->fetchObject();

     if (!preg_match($patron,$name)) {
       $this->messenger->addMessage($this->t('El nombre @name no esta permitido.', ['@name' => $form_state->getValue('name')]), 'error');
       $flag = FALSE;
     }

     if (strlen($name) <= 4) {
       $this->messenger->addMessage($this->t('Valor del campo debe ser mayor o igual a 5 caracteres.', ['@name' => $form_state->getValue('name')]), 'error');
       $flag = FALSE;
     }

     if ($result) {
       $flag = FALSE;
       $this->messenger->addMessage($this->t('El usuario @name ya se encuentra registrado.', ['@name' => $form_state->getValue('name')]), 'error');
     }

     if ($flag) {
       $id = $this->database->insert('myusers')
         ->fields(['name'])
         ->values([
           'name' => $form_state->getValue('name'),
         ])->execute();
       $content['#markup'] = $this->t('Se ha guardado el usuario @name correctamente.', ['@name' => $form_state->getValue('name')]);
       $response->addCommand(new OpenModalDialogCommand(
           $this->t('ID @id guardado correctamente.', ['@id' => $id]),
           $content,
           ['width' => '700'])
       );
       $response->addCommand(new HtmlCommand('#result-message', ''));
       return $response;
     }
     $message = [
       '#theme' => 'status_messages',
       '#message_list' => drupal_get_messages(),
     ];
     $messages = $this->renderer->renderPlain($message);
     $response->addCommand(new HtmlCommand('#result-message', $messages));
     return $response;
   }
 }
