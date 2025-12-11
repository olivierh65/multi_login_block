<?php

namespace Drupal\multi_login_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Url;

/**
 * Provides a 'Multi Login' Block.
 *
 * @Block(
 *   id = "multi_login_block",
 *   admin_label = @Translation("Multi Login Block"),
 *   category = @Translation("User"),
 * )
 */
class MultiLoginBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a new MultiLoginBlock instance.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'enable_standard_login' => TRUE,
      'standard_label' => 'Connexion standard',
      'standard_open_default' => FALSE,
      'social_providers' => [],
    ];
  }

  /**
   * Get available social auth providers.
   */
  protected function getSocialAuthProviders() {
    $providers = [];

    // Liste des providers Social Auth connus.
    $known_providers = [
      'social_auth_google' => [
        'name' => 'Google',
        'network' => 'google',
        'icon' => 'google',
      ],
      'social_auth_facebook' => [
        'name' => 'Facebook',
        'network' => 'facebook',
        'icon' => 'facebook',
      ],
      'social_auth_github' => [
        'name' => 'GitHub',
        'network' => 'github',
        'icon' => 'github',
      ],
      'social_auth_linkedin' => [
        'name' => 'LinkedIn',
        'network' => 'linkedin',
        'icon' => 'linkedin',
      ],
      'social_auth_twitter' => [
        'name' => 'Twitter',
        'network' => 'twitter',
        'icon' => 'twitter',
      ],
      'social_auth_microsoft' => [
        'name' => 'Microsoft',
        'network' => 'microsoft',
        'icon' => 'microsoft',
      ],
    ];

    foreach ($known_providers as $module => $info) {
      if ($this->moduleHandler->moduleExists($module)) {
        $providers[$module] = $info;
      }
    }

    return $providers;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    // Standard login.
    $form['standard_login'] = [
      '#type' => 'details',
      '#title' => $this->t('Standard Login'),
      '#open' => TRUE,
    ];

    $form['standard_login']['enable_standard_login'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable standard login'),
      '#default_value' => $config['enable_standard_login'],
    ];

    $form['standard_login']['standard_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $config['standard_label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[standard_login][enable_standard_login]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['standard_login']['standard_open_default'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Open by default'),
      '#default_value' => $config['standard_open_default'],
      '#states' => [
        'visible' => [
          ':input[name="settings[standard_login][enable_standard_login]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    // Social Auth providers.
    $providers = $this->getSocialAuthProviders();

    $form['social_providers_wrapper'] = [
      '#type' => 'details',
      '#title' => $this->t('Social Auth Providers'),
      '#open' => TRUE,
    ];

    if (empty($providers)) {
      $form['social_providers_wrapper']['info'] = [
        '#markup' => '<p>' . $this->t('No Social Auth modules detected. Install modules like social_auth_google, social_auth_facebook, etc.') . '</p>',
      ];
    }

    foreach ($providers as $provider_id => $provider_info) {
      $provider_config = $config['social_providers'][$provider_id] ?? [];

      $form['social_providers_wrapper'][$provider_id] = [
        '#type' => 'details',
        '#title' => $provider_info['name'],
        '#open' => FALSE,
      ];

      $form['social_providers_wrapper'][$provider_id]['enabled'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Enable @provider', ['@provider' => $provider_info['name']]),
        '#default_value' => $provider_config['enabled'] ?? FALSE,
      ];

      $form['social_providers_wrapper'][$provider_id]['label'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Label'),
        '#default_value' => $provider_config['label'] ?? $this->t('Login with @provider', ['@provider' => $provider_info['name']]),
        '#states' => [
          'visible' => [
            ':input[name="settings[social_providers_wrapper][' . $provider_id . '][enabled]"]' => ['checked' => TRUE],
          ],
        ],
      ];

      $form['social_providers_wrapper'][$provider_id]['network'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Network identifier'),
        '#default_value' => $provider_config['network'] ?? $provider_info['network'],
        '#description' => $this->t('Network identifier for the route (e.g., @network)', ['@network' => $provider_info['network']]),
        '#states' => [
          'visible' => [
            ':input[name="settings[social_providers_wrapper][' . $provider_id . '][enabled]"]' => ['checked' => TRUE],
          ],
        ],
      ];

      $form['social_providers_wrapper'][$provider_id]['custom_url'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Or custom URL'),
        '#default_value' => $provider_config['custom_url'] ?? '',
        '#description' => $this->t('Leave empty to use social_auth.network.redirect route. Use this for external OAuth URLs.'),
        '#states' => [
          'visible' => [
            ':input[name="settings[social_providers_wrapper][' . $provider_id . '][enabled]"]' => ['checked' => TRUE],
          ],
        ],
      ];

      $form['social_providers_wrapper'][$provider_id]['button_text'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Button text'),
        '#default_value' => $provider_config['button_text'] ?? $this->t('Login'),
        '#states' => [
          'visible' => [
            ':input[name="settings[social_providers_wrapper][' . $provider_id . '][enabled]"]' => ['checked' => TRUE],
          ],
        ],
      ];

      $form['social_providers_wrapper'][$provider_id]['open_default'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Open by default'),
        '#default_value' => $provider_config['open_default'] ?? FALSE,
        '#states' => [
          'visible' => [
            ':input[name="settings[social_providers_wrapper][' . $provider_id . '][enabled]"]' => ['checked' => TRUE],
          ],
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['enable_standard_login'] = $form_state->getValue(['standard_login', 'enable_standard_login']);
    $this->configuration['standard_label'] = $form_state->getValue(['standard_login', 'standard_label']);
    $this->configuration['standard_open_default'] = $form_state->getValue(['standard_login', 'standard_open_default']);

    $providers = $this->getSocialAuthProviders();
    $social_providers_config = [];

    foreach (array_keys($providers) as $provider_id) {
      $provider_values = $form_state->getValue(['social_providers_wrapper', $provider_id]);
      if ($provider_values) {
        $social_providers_config[$provider_id] = $provider_values;
      }
    }

    $this->configuration['social_providers'] = $social_providers_config;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $login_methods = [];

    // Standard login.
    if ($config['enable_standard_login']) {
      $login_methods[] = [
        'id' => 'standard',
        'label' => $config['standard_label'],
        'icon' => 'drupal',
        'open_default' => $config['standard_open_default'] ?? FALSE,
        'content' => $this->formBuilder->getForm('Drupal\user\Form\UserLoginForm'),
      ];
    }

    // Social providers.
    $providers = $this->getSocialAuthProviders();
    foreach ($providers as $provider_id => $provider_info) {
      $provider_config = $config['social_providers'][$provider_id] ?? [];

      if (!empty($provider_config['enabled'])) {
        // Déterminer l'URL à utiliser.
        if (!empty($provider_config['custom_url'])) {
          $url = Url::fromUri($provider_config['custom_url']);
        }
        else {
          $network = $provider_config['network'] ?? $provider_info['network'];
          try {
            $url = Url::fromRoute('social_auth.network.redirect', ['network' => $network]);
          }
          catch (\Exception $e) {
            // Si la route n'existe pas, on skip ce provider.
            \Drupal::logger('multi_login_block')->error(
              'Route social_auth.network.redirect does not exist for provider @provider (network: @network)',
              ['@provider' => $provider_id, '@network' => $network]
            );
            continue;
          }
        }

        $login_methods[] = [
          'id' => str_replace('social_auth_', '', $provider_id),
          'label' => $provider_config['label'] ?? $provider_info['name'],
          'icon' => $provider_info['icon'],
          'open_default' => $provider_config['open_default'] ?? FALSE,
          'content' => [
            '#markup' => '<p>' . $this->t('Click the button below to login with your @provider account.', [
              '@provider' => $provider_info['name'],
            ]) . '</p>',
            'link' => [
              '#type' => 'link',
              '#title' => $provider_config['button_text'] ?? $this->t('Login'),
              '#url' => $url,
              '#attributes' => [
                'class' => ['button', 'button--primary', 'oauth-button', 'oauth-' . str_replace('social_auth_', '', $provider_id)],
              ],
            ],
          ],
        ];
      }
    }

    return [
      '#theme' => 'multi_login_block',
      '#login_methods' => $login_methods,
      '#attached' => [
        'library' => [
          'multi_login_block/multi_login_styles',
        ],
      ],
      '#cache' => [
        'contexts' => ['user'],
      ],
    ];
  }

}
