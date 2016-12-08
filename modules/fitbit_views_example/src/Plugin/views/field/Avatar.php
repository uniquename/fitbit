<?php

namespace Drupal\fitbit_views_example\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Class Avatar
 *
 * @ViewsField("fitbit_avatar")
 */
class Avatar extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $avatar = $this->getValue($values);
    if ($avatar) {
      return [
        '#theme' => 'image',
        '#uri' => $avatar,
        '#alt' => $this->t('Avatar'),
      ];
    }
  }
}
