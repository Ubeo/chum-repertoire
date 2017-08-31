<?php
namespace Drupal\recherche\Plugin\Block;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "resultats_recherche",
 *   admin_label = @Translation("Résultats de recherche"),
 * )
 */
class ResultatsBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
  	$html = "<p style='color:red'>Résultats de recherche. " . time() . "</p>";
    return [
      '#markup' => $this->t($html),
      '#cache' => [
         'max-age' => 0,
       ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['my_block_settings'] = $form_state->getValue('my_block_settings');
  }
}