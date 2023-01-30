<?php
namespace Drupal\endroid_qr_code_block\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Cache\Cache;

/**
 * @Block(
 *   id = "endroid_qr_code_block",
 *   admin_label = @Translation("Endroid QR code Block"),
 *   category = @Translation("Endroid QR code Block")
 * )
 */
class EndroidQRcodeBlock extends BlockBase {

  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $node_types = \Drupal\node\Entity\NodeType::loadMultiple();

    $options = [];
    foreach ($node_types as $node_type) {
      $options[$node_type->id()] = $node_type->label();
    }

    $form['content_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Content Type'),
      '#options' => $options,
      '#required' => TRUE,
      '#default_value' =>  $this->configuration['content_type'],
    ];

    $form['qr_url_machine_name'] =[
      '#title' => t('Enter your QR code URL field machine name here'),
      '#type' => 'textfield',
      '#description' => 'Enter your QR code URL field machine name here i.g :- field_qr_url',
      '#default_value' =>  $this->configuration['qr_url_machine_name'],
      '#size' => 32,
      '#maxlength' => 128,
      '#required' => TRUE,
    ];

     return parent::buildConfigurationForm($form, $form_state);
  }

  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['content_type'] = $form_state->getValue('content_type');
    $this->configuration['qr_url_machine_name'] = $form_state->getValue('qr_url_machine_name');
    parent::submitConfigurationForm($form, $form_state);
  }

  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (!preg_match("/^[a-z_]+$/", $form_state->getValue('qr_url_machine_name'))) {
      $form_state->setErrorByName('qr_url_machine_name', $this->t('Name field must contain only lowercase letters and underscores.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    $qr_url_machine_name = $config['qr_url_machine_name'];
    $content_type = $config['content_type'];

    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      $new_node_id = $node->id();
      $current_node_type = $node->bundle();
    }

    if($current_node_type === $content_type){
      $node_val = \Drupal::entityTypeManager()->getStorage('node')->load($new_node_id);
      $machine_name = $node_val->get($qr_url_machine_name)->getValue();
      $url = $machine_name[0]['uri'];
    }

    if (!empty($url)) {
      $data = UrlHelper::isValid($url);

      if ($url) {
        $option = ['query' => ['path' => $url]];
        $uri = Url::fromRoute('endroid_qr_code_block.qr.url', [], $option)->toString();
      }
      else {
        $uri = Url::fromRoute('endroid_qr_code_block.qr.generator', ['content' => $url])->toString();
      }
    }

    $element[] = [
      '#theme' => 'image',
      '#uri' => $uri,
      '#attributes' => ['class' => 'module-name-center-image'],
    ];

    return $element;

  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof NodeInterface) {
      $nid = $node->id();
      return Cache::mergeTags(parent::getCacheTags(), ['node:' . $nid]);
    }
    else {
      return parent::getCacheTags();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

}
