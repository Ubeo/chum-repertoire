<?php

namespace Drupal\recherche\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "resultats_recherche",
 *   admin_label = @Translation("Résultats de recherche"),
 * )
 */
class ResultatsBlock extends BlockBase {
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

	protected function getChildCategories( $parent_id ) {
		$language      = \Drupal::languageManager()->getCurrentLanguage()->getId();
		$terms         = \Drupal::entityManager()->getStorage( 'taxonomy_term' )->loadTree( "categories_des_fiches" );
		$liste_enfants = array();
		if ( $terms ) {
			foreach ( $terms as $term ) {
				if ( $term->depth == 1 && $term->langcode == $language && in_array( $parent_id, $term->parents ) ) {
					$liste_enfants[ $term->tid ] = $term;
				}
			}
		}

		return $liste_enfants;
	}


	public function getNodeByTermId($term_id) {
	  $query = \Drupal::database()->select('taxonomy_index', 'ti');
	  $query->fields('ti', ['nid']);
	  $query->condition('ti.tid', $term_id);
	  $nodes = $query->execute()->fetchAssoc();
	  return $nodes;
	}


	/**
	 * {@inheritdoc}
	 */
	public function build() {
		$html = "<p style='color:red'>Résultats de recherche. " . time() . "</p>";
		/*$result = db_select('node', 'n')->fields('n')->execute()->fetchAll();
		print '<pre>';
		print_r($result);
		print '</pre>';*/

		$categories_parents = $this->getParentCategories();
		$tous               = \Drupal::request()->request->get( 'tous' );

		if ( isset( $_POST['tous'] ) && $_POST['tous'] == 'tous' ) {
			$tous = true;
		}


		$liste_subcats = array();
		if ( $categories_parents ) {
			foreach ( $categories_parents as $categories_parent ) {

				$slug_temp = preg_replace('/[^a-zA-Z0-9]/', '_', mb_strtolower($categories_parent->name));
				if ( ( isset( $_POST[ $slug_temp ] ) && (int) $_POST[ $slug_temp ] == $categories_parent->tid ) || $tous ) {
					$liste_subcats[] = $categories_parent->tid;
					$childs = $this->getChildCategories( $categories_parent->tid );
					if ( $childs ) {
						foreach ( $childs as $child ) {
							$liste_subcats[] = $child->tid;
						}

					}
				}
			}
		}


		foreach ( $liste_subcats as $subcat ) {
			print '<pre>';
			print_r($this->getNodeByTermId($subcat));
			print '</pre>';
		}



		return [
			'#markup' => $this->t( $html ),
			'#cache'  => [
				'max-age' => 0,
			],
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
		$this->configuration['resultats_block_settings'] = $form_state->getValue( 'resultats_block_settings' );
	}
}