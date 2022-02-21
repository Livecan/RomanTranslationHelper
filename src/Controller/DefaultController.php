<?php

namespace Drupal\roman_translation_helper\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Translation Helper default Controller.
 */
class DefaultController extends ControllerBase {

  /**
   * Get element that mounts React App front-end.
   *
   * @return array
   *   Drupal Render array of element which mount the React App front-end.
   */
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

  /**
   * Download language files for module.
   *
   * @param string $moduleName
   *   Module name.
   *
   * @return Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function downloadLanguageFiles($moduleName) {
    $moduleLocalizationHubHtml = file_get_contents("http://ftp.drupal.org/files/translations/all/$moduleName/");

    $output = [];

    $languages = ['ar', 'es', 'fil', 'fr', 'hi', 'id', 'ru'];

    foreach ($languages as $language) {
      preg_match_all('/href="([^"]*\.' . $language . '\.po)"/', $moduleLocalizationHubHtml, $matches);

      $filename = $matches[1][count($matches[1]) - 1];

      $poFileStream = fopen("http://ftp.drupal.org/files/translations/all/$moduleName/$filename", "r");
      $msgId = NULL;
      while ($line = fgets($poFileStream)) {
        if (preg_match('/msgid "([^"]*)"/', $line, $matches)) {
          $msgId = $matches[1];
        }
        elseif ($msgId && preg_match('/msgstr "([^"]*)"/', $line, $matches)) {
          $msgStr = $matches[1];
          $output[$language][$msgId] = $msgStr;
        }
      }

    }

    return (new JsonResponse($output));
  }

}
