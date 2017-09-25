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


	public function getNodeByTermId( $term_id ) {
		$query = \Drupal::database()->select( 'taxonomy_index', 'ti' );
		$query->fields( 'ti', [ 'nid' ] );
		$query->condition( 'ti.tid', $term_id );
		$nodes = $query->execute()->fetchAssoc();

		return $nodes;
	}


	/**
	 * {@inheritdoc}
	 */
	public function build() {
		$html = "";

		$terms_id = [];
		$tax_nid  = [];
		$group = false;

		if ( isset( $_POST['tous'] ) && $_POST['tous'] == 'tous' ) {
			$terms_id = [ 1, 6, 7 ];
		}

		if ( isset( $_POST['cliniques'] ) && $_POST['cliniques'] == 1 ) {
			if ( ! in_array( 1, $terms_id ) ) {
				$terms_id[] = 1;
			}
		}

		if ( isset( $_POST['services'] ) && $_POST['services'] == 7 ) {
			if ( ! in_array( 7, $terms_id ) ) {
				$terms_id[] = 7;
			}
		}

		if ( isset( $_POST['unitsdesoins'] ) && $_POST['unitsdesoins'] == 6 ) {
			if ( ! in_array( 6, $terms_id ) ) {
				$terms_id[] = 6;
			}
		}

		if ( isset( $_POST['mot-clef'] ) && ! empty( $_POST['mot-clef'] ) ) {
			$mot_cle = $_POST['mot-clef'];
		} else {
			$mot_cle = false;
		}

		if ( count( $terms_id ) <= 0 ) {
			$terms_id = [ 1, 6, 7 ];
		}



		if ( count( $terms_id ) > 0 ) {

			$query    = \Drupal::entityQuery( 'node' );
			$database = \Drupal::database();


			if ( $mot_cle ) {

				$mot_cle = $database->escapeLike($mot_cle);

				// On cherche le mot-clé dans le titre
				$group = $query->orConditionGroup()
				               ->condition( 'title', $mot_cle, 'CONTAINS' );

				$group->condition( 'body', $mot_cle, 'CONTAINS' );

				// On cherche si une taxonomie contient le mot-clé recherché
				$taxonomy_results = $database->query( "SELECT * FROM dpr8_taxonomy_term_field_data WHERE `name` LIKE '%" . $mot_cle . "%'" );
				if ( $taxonomy_results ) {
					while ( $row = $taxonomy_results->fetchAssoc() ) {
						$tax_nid[] = $row['tid'];
					}
				}

				// Si une taxonomie correspond, on l'ajout à la recherche
				if ( $tax_nid ) {
					$group->condition( 'field_mots_cles', $tax_nid, 'IN' );
				}

			}

			$query->condition( 'type', 'fiches_repertoire' )
			      ->condition( 'field_categorie', $terms_id, "IN" )
			      ->condition( 'status', 1, "=" );

			// Si le groupe OR est présent, on l'ajoute
			if ( $group ) {
				$query->condition( $group );
			}

			// On éxécute la requête
			$nids = $query->sort( 'title' )->execute();

			if ( $nids ) {
				$nodes = \Drupal\node\Entity\Node::loadMultiple( $nids );
				foreach ( $nodes as $node_content ) {
					$title = $node_content->getTitle();
					$alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$node_content->id());
					$html .= '<h3><a href="' . $alias . '">' . $title . '</a></h3>';
				}
			} else {
				$html .= "<p>Aucun résultat</p>";
			}
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