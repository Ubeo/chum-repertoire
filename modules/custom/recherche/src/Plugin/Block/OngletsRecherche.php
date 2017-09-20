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

            $html .= '<div class="onglets-wrapper clearfix">';
            foreach ( $liste_categories_principales as $categorie_principale ) {
            	$class_on = $categorie_principale->tid == 1 ? "tab_on" : "";
                $html .= '	<div class="tab-category term-' . $categorie_principale->tid .' rel="' . $categorie_principale->tid . '">
                                <div class="tab-category onglet '. $class_on .'" rel="' . $categorie_principale->tid . '">' . $categorie_principale->name . '</div>';
                $html .= '</div>';
            }
            $html .= '</div>';

		    foreach ( $liste_categories_principales as $categorie_principale ) {
				$html .= '	<div class="tab-category term-' . $categorie_principale->tid . '" rel="' . $categorie_principale->tid . '">
  	                            <div class="tab-category onglet" rel="' . $categorie_principale->tid . '">' . $categorie_principale->name . '</div>';

			    $nids = \Drupal::entityQuery('node')->condition('type','fiches_repertoire')->condition('field_categorie', $categorie_principale->tid, "=")->condition('status', 1)->sort('title')->execute();
			    $nodes =  \Drupal\node\Entity\Node::loadMultiple($nids);

				if($nodes) {
					if ( $categorie_principale->tid == 1 ){
						$html .= ' <ul class="list-category term-' . $categorie_principale->tid . '" rel="' . $categorie_principale->tid . '" style="display:block;" >';
					} else {
						$html .= ' <ul class="list-category term-' . $categorie_principale->tid . '" rel="' . $categorie_principale->tid . '">';
					}

					foreach ( $nodes as $node ) {

						$alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$node->id());
						$html .= '<li><a href="' . $alias . '">' . $node->label() . '</a></li>';

					}

					if ( count($nodes) % 2 != 0 ) { //Si c'est impaire on ajoute un li
						$html .= "<li>&nbsp;</li>";
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