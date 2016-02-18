<?php

/**
 * @file
 * Post update functions for Entity Print.
 */

/**
 * Migrate simple config into new config entities.
 */
function entity_print_post_update_migrate_config() {
  $config = \Drupal::configFactory()->getEditable('entity_print.settings');
  if ($plugin_id = $config->get('pdf_engine')) {
    /** @var \Drupal\entity_print\Plugin\EntityPrintPluginManager $plugin_manager */
    $plugin_manager = \Drupal::service('plugin.manager.entity_print.pdf_engine');
    $definition = $plugin_manager->getDefinition($plugin_id);
    /** @var \Drupal\entity_print\Plugin\PdfEngineInterface $class */
    $class = $definition['class'];

    if ($class::dependenciesAvailable()) {
      $values = [
        'id' => $plugin_id,
        'settings' => [],
      ];

      // If we have a binary location then add it.
      if ($binary_location = $config->get('binary_location')) {
        $values['settings']['binary_location'] = $binary_location;
      }
      // Create the new config entity.
      \Drupal::entityTypeManager()
        ->getStorage('pdf_engine')
        ->create($values)
        ->save();

      // Make sure to remove the binary location.
      $config->clear('binary_location');
      $config->save();
    }
  }

  return t('All configuration upgraded');
}
