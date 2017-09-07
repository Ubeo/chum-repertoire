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

		if ( isset( $_POST['mot-clef'] ) ) {
			$mot_clef = htmlspecialchars( $_POST['mot-clef'] );
		}

		$tous = $cliniques = $unites_soins = $services_diagnostiques = false;

		if ( isset( $_POST['tous'] ) && $_POST['tous'] == 1 ) {
			$tous = true;
		}

		if ( isset( $_POST['cliniques'] ) && $_POST['cliniques'] == 1 ) {
			$cliniques = true;
		}

		if ( isset( $_POST['unites_soins'] ) && $_POST['unites_soins'] == 1 ) {
			$unites_soins = true;
		}

		if ( isset( $_POST['services_diagnostiques'] ) && $_POST['services_diagnostiques'] == 1 ) {
			$services_diagnostiques = true;
		}


		$html
			= "
  	<form method='post' action='/' id='repertoire-form'>
  	    <input type='text' placeholder='RECHERCHE' id='mot-clef' name='mot-clef' value='" . $mot_clef . "' />
  	    <a href='javascript:;' id='form-submit'>LOUPE</a>
  	    <label><input type='checkbox' name='tous' value='1' " . ($tous ? 'checked="checked"' : '') . ">TOUS</label>
  	    <label><input type='checkbox' name='cliniques' value='1' " . ($cliniques ? 'checked="checked"' : '') . ">CLINIQUES</label>
  	    <label><input type='checkbox' name='unites_soins' value='1' " . ($unites_soins ? 'checked="checked"' : '') . ">UNITÉS DE SOINS</label>
  	    <label><input type='checkbox' name='services_diagnostiques' value='1' " . ($services_diagnostiques ? 'checked="checked"' : '') . ">SERVICES DIAGNOSTIQUES</label>
	</form>";

		return [
			'#markup'   => $this->t( $html ),
			'#cache'    => [
				'max-age' => 0,
			],
			'#attached' => [
				'library' => [
					'recherche/recherche',
				]
			]
		];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function blockAccess( AccountInterface $account ) {
		return AccessResult::allowedIfHasPermission( $account, 'access content' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function blockForm( $form, FormStateInterface $form_state ) {
		$config = $this->getConfiguration();

		return $form;
	}

	/**
	 * {@inheritdoc}
	 */
	public function blockSubmit( $form, FormStateInterface $form_state ) {
		$this->configuration['barre_recherche_block_settings'] = $form_state->getValue( 'barre_recherche_block_settings' );
	}
}