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
 *   id = "barre_recherche",
 *   admin_label = @Translation("Barre de recherche"),
 * )
 */
class BarreRechercheBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
  	$html = "
  	<form method='post' action='/recherche'>
  	    <input type='text' placeholder='RECHERCHE' id='mot-clef' />
  	    <a href='javscript:;'>LOUPE</a>
  	    <label><input type='checkbox' name='tous' value='1'>TOUS</label>
  	    <label><input type='checkbox' name='cliniques' value='1'>CLINIQUES</label>
  	    <label><input type='checkbox' name='unites_soins' value='1'>UNITÉS DE SOINS</label>
  	    <label><input type='checkbox' name='services_diagnostiques' value='1'>SERVICES DISGNOSTIQUES</label>
	</form>
  	";
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
    $this->configuration['barre_recherche_block_settings'] = $form_state->getValue('barre_recherche_block_settings');
  }
}