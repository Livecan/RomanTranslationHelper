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
      }

    }

    return (new JsonResponse($output));
  }

}
