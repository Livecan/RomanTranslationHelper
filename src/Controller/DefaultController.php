<?php

namespace Drupal\roman_translation_helper\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Translation Helper default Controller.
 */
class DefaultController extends ControllerBase {
  public function getApp() {
    return [
      '#markup' => '<div id="roman-translation-helper-react-app" />',
      '#attached' => [
        'library' => [
          // When developing use roman_translation_helper/react_app_dev library instead
          'roman_translation_helper/react_app_dev',
        ],
      ],
    ];
  }
}
