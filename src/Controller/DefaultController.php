<?php

namespace Drupal\roman_translation_helper\Controller;

use Drupal\Component\Gettext\PoItem;
use Drupal\Component\Gettext\PoStreamReader;
use Drupal\Component\Gettext\PoStreamWriter;
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
      if (preg_match_all('/href="([^"]*\.' . $language . '\.po)"/', $moduleLocalizationHubHtml, $matches)) {
        $filename = $matches[1][count($matches[1]) - 1];

        $psr = new PoStreamReader();
        $psr->setURI("http://ftp.drupal.org/files/translations/all/$moduleName/$filename");
        $psr->open();

        /** @var \Drupal\Component\Gettext\PoItem $item */
        while ($item = $psr->readItem()) {
          $output[$language][$item->getSource()] = $item->getTranslation();
        }
      }
    }

    return (new JsonResponse($output));
  }

  /**
   * Insert translations into wildfire_language po files.
   *
   * Loads data from request body. The data is array indexed by language code
   * and the content is array indexed by phrase with translation as value.
   *
   * Example data:
   * @code
   * [
   *   'ar' => [
   *     'Operations' => 'عمليا',
   *     'Disabled' => 'معطل',
   *   ],
   *   'es' => [
   *     'Operations' => 'Operaciones',
   *   ],
   * ]
   *
   * @param string $moduleName
   *   Module name.
   *
   * @return Symfony\Component\HttpFoundation\JsonResponse
   *   Empyt Json response.
   */
  public function insertTranslations($moduleName) {
    $translations = json_decode(\Drupal::request()->getContent(), TRUE);

    $cwd = getcwd();
    $poWriter = new PoStreamWriter();

    foreach ($translations as $language => $languageTranslations) {
      $poReader = new PoStreamReader();
      $existingPoItems = [];
      $translatedPhrases = [];
      $poReader->setURI("$cwd/modules/custom/wildfire_languages/translations/$language.po");
      $poReader->open();

      while ($item = $poReader->readItem()) {
        $existingPoItems[] = $item;
        $translatedPhrases[] = $item->getSource();
      }
      $poReader->close();

      $poWriter->setURI("$cwd/modules/custom/wildfire_languages/translations/$language.po");
      $poWriter->open();
      foreach ($existingPoItems as $item) {
        $poWriter->writeItem($item);
      }

      foreach ($languageTranslations as $phrase => $translation) {
        if (!in_array($phrase, $translatedPhrases)) {
          $poItem = new PoItem();
          $poItem->setSource($phrase);
          $poItem->setTranslation($translation);
          $poWriter->writeItem($poItem);
        }
      }

      $poWriter->close();
    }

    return new JsonResponse();
  }

}
