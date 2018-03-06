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

	protected function getParentCategories() {
		$language      = \Drupal::languageManager()->getCurrentLanguage()->getId();
		$terms         = \Drupal::entityManager()->getStorage( 'taxonomy_term' )->loadTree( "categories_des_fiches" );
		$liste_parents = array();
		if ( $terms ) {
			foreach ( $terms as $term ) {
				if ( $term->depth == 0 && $term->langcode == $language ) {
					$liste_parents[ $term->tid ] = $term;
				}
			}
		}

		return $liste_parents;
	}



	/**
	 * {@inheritdoc}
	 */
	public function build() {

		if ( isset( $_POST['mot-clef'] ) ) {
			$mot_clef = htmlspecialchars( $_POST['mot-clef'] );
		}

		$tous = false;

		$categories_parents = $this->getParentCategories();

		if ( isset( $_POST['tous'] ) && $_POST['tous'] == 'tous' ) {
			$tous = true;
		}


		if ( $categories_parents ) {
			foreach ( $categories_parents as $categories_parent ) {
				$alias = iconv('UTF-8', 'ASCII//TRANSLIT', $categories_parent->name);
				$slug_temp = preg_replace('/[^a-zA-Z0-9]/', '', $alias);
				$slug_temp = strtolower($slug_temp);
				if(isset($_POST[$slug_temp]) && (int)$_POST[$slug_temp] == $categories_parent->tid) {
					$$slug_temp = true;
				}
			}
		}


		$html = "
  	            <form method='post' action='/recherche' id='repertoire-form'>
			        <input type='text' placeholder='RECHERCHE' id='mot-clef' name='mot-clef' value='" . $mot_clef . "' />
			        <a href='javascript:;' id='form-submit'><i class=\"fa fa-search\" aria-hidden=\"true\"></i></a>
                    <div class='before_labels'></div>
			        <label><input type='checkbox' name='tous' value='tous' " . ( $tous ? 'checked="checked"' : '' ) . " class='check-choices'>TOUS</label>
			        ";

		if ( $categories_parents ) {
			foreach ( $categories_parents as $categories_parent ) {
				$alias = iconv('UTF-8', 'ASCII//TRANSLIT', $categories_parent->name);
				$slug_temp = preg_replace('/[^a-zA-Z0-9]/', '', $alias);
				$slug_temp = strtolower($slug_temp);
				if($$slug_temp) {
					$checked = "checked='checked'";
				} else {
					$checked = '';
				}
				$html .= "<label class='cat-" . $slug_temp . "'><input type='checkbox' name='" . $slug_temp . "' value='" . $categories_parent->tid . "' $checked  class='check-choices'>" . mb_strtoupper($categories_parent->name) . "</label>";
			}
		}

		$html .= "</form>";

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