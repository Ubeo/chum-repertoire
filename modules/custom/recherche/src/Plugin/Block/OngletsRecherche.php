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
 *   id = "onglets_recherche",
 *   admin_label = @Translation("Onglets de recherche"),
 * )
 */
class OngletsRecherche extends BlockBase {
	/**
	 * {@inheritdoc}
	 */
	public function build() {

		$html                         = '';
		$liste_categories_principales = $this->getParentCategories();

		if ( $liste_categories_principales ) {
			foreach ( $liste_categories_principales as $categorie_principale ) {
				$liste_enfants = $this->getChildCategories($categorie_principale->tid);
				$html .= '	<div id="term-' . $categorie_principale->tid . '">
  	                            <div class="onglet">' . $categorie_principale->name . '</div>';
				if($liste_enfants) {
					$html .= '<ul>';
					foreach ( $liste_enfants as $enfant ) {
						$html .= '<li>' . $enfant->name . '</li>';
					}
					$html .= '</ul>';
				}


				$html .= '</div>';
			}
		}

		return [
			'#markup' => $this->t( $html ),
			'#cache'  => [
				'max-age' => 0,
			],
		];
	}

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

	protected function getChildCategories($parent_id) {
		$language      = \Drupal::languageManager()->getCurrentLanguage()->getId();
		$terms         = \Drupal::entityManager()->getStorage( 'taxonomy_term' )->loadTree( "categories_des_fiches" );
		$liste_enfants = array();
		if ( $terms ) {
			foreach ( $terms as $term ) {
				if ( $term->depth == 1 && $term->langcode == $language && in_array($parent_id, $term->parents)) {
					$liste_enfants[ $term->tid ] = $term;
				}
			}
		}

		return $liste_enfants;
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