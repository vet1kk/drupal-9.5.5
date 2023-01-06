<?php

namespace Drupal\movie_list\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class MovieAPI extends FormBase
{
  const MOVIE_API_CONFIG_PAGE = 'movie_api_config_page:values';

  public function getFormId(): string
  {
    return 'movie_api_config_page';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array
  {
    $form = [];
    $data = Drupal::state()->get(self::MOVIE_API_CONFIG_PAGE);

    $form['api_base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Base url'),
      '#description' => $this->t('The base url of the API'),
      '#required' => TRUE,
      '#default_value' => $data['api_base_url'] ?? '',
    ];

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key (v3 auth)'),
      '#description' => $this->t('This is the API key that will be used to access the API'),
      '#required' => TRUE,
      '#default_value' => $data['api_key'] ?? '',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $submitted_values = $form_state->cleanValues()->getValues();
    Drupal::state()->set(self::MOVIE_API_CONFIG_PAGE, $submitted_values);

    $this->messenger()->addMessage($this->t('The configuration options have been saved.'));
  }
}
