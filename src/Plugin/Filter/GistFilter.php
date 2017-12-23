<?php

namespace Drupal\gist_filter\Plugin\Filter;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\gist_filter\GistFilterClientInterface;
use Drupal\gist_filter\GitHubRequestException;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;

/**
 * Provides a filter to substitute:
 *  [gist:xx] tags with the gist located at "http://gist.github.com/xx" and will display it in a fully embedded format.
 *  [gistcode:xx] tags with the gist located at "http://gist.github.com/xx", and will display only the code.
 *  [gistlink:xx] tags with the "http://gist.github.com/xx" link.
 *
 * @Filter(
 *   id = "gist_filter",
 *   title = @Translation("Gist filter (Github Gists)"),
 *   description = @Translation("Substitutes [gist:xx] tags with the gist located at http://gist.github.com/xx. See the README learn about more ways to embed gists."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE,
 * )
 */
class GistFilter extends FilterBase implements ContainerFactoryPluginInterface {

  use LinkGeneratorTrait;

  /**
   * Renderer used to display.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * @var \Drupal\gist_filter\GistFilterGitHubClient
   */
  protected $gitHubClient;

  /**
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param GistFilterClientInterface $github_client
   * @param RendererInterface $renderer
   * @param LoggerInterface $logger
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, GistFilterClientInterface $github_client, RendererInterface $renderer, LoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->gitHubClient = $github_client;
    $this->renderer = $renderer;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('gist_filter_cached_github_client'),
      $container->get('renderer'),
      $container->get('logger.factory')->get('filter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $language) {

    $text = preg_replace_callback('@\[gist\:(?<id>[\w/]+)(?:\:(?<file>[\w\.]+))?\]@', array($this, 'gistDisplayEmbed'), $text);
    $text = preg_replace_callback('@\[gistcode\:(?<id>[\w/]+)(?:\:(?<file>[\w\.]+))?\]@', array($this, 'gistDisplayCode'), $text);
    $text = preg_replace_callback('@\[gistlink\:(?<id>[\w/]+)(?:\:(?<file>[\w\.]+))?\]@', array($this, 'gistDisplayLink'), $text);

    return new FilterProcessResult($text);
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = true) {

    $output = $this->t('Use <code>[gist:#####]</code> for using the default embed method.', '<br />');
    $output.= $this->t('You can use <code>[gist:#####]</code> for displaying just the code of your gist, or <code>[gistlink:#####]</code> for displaying the link of your gist.', '<br />');
    $output.= $this->t('You may also include a specific file within a multi-file gist with <code>[gist:####:my_file]</code>.');

    return $output;
  }


  /**
   * Replace the text with the content of the Gist, wrapped in <pre> tags.
   */
  protected function gistDisplayCode(array $matches) {
    // Get the Gist from the Github API.
    try {
      $data = $this->gitHubClient->getGist($matches['id']);

      $build = [];
      // If a file was specified, just render that one file.
      if (isset($matches['file']) && !empty($matches['file']) && isset($data['files'][$matches['file']])) {
        $build[] = array(
          '#theme' => 'gist_filter_code',
          '#file' => $data['files'][$matches['file']],
        );

      }
      // Otherwise, render all files.
      else {
        foreach ($data['files'] as $file) {
          $build[] = array(
            '#theme' => 'gist_filter_code',
            '#file' => $file,
          );
        }
      }

      return $this->renderer->renderPlain($build);

    }
    catch (GitHubRequestException $e) {
      $this->logger->notice('Error retrieving gist %gist: %error', array('%gist' => $matches['id'], '%error' => $e->getMessage()));
    }
  }

  /**
   * Replace the text with embedded script.
   */
  protected function gistDisplayEmbed(array $matches) {
    $gist_url = '//gist.github.com/' . $matches['id'];
    $gist_url = isset($matches['file']) && !empty($matches['file'])
      ? $gist_url . '.js?file=' . $matches['file']
      : $gist_url . '.js';

    // Also grab the content and display it in code tags (in case the user does not have JS).
    $output = '<noscript>' . $this->gistDisplayCode($matches) . '</noscript>';
    $output .= '<script src="' . $gist_url . '"></script>';

    return $output;
  }

  /**
   * Replace the text with a link.
   */
  protected function gistDisplayLink(array $matches) {
    $gist_url = 'http://gist.github.com/' . $matches['id'];
    $gist_url = isset($matches['file']) && !empty($matches['file'])
      ? $gist_url . '#file_' . $matches['file']
      : $gist_url;

    return $this->l($gist_url, Url::fromUri($gist_url));
  }

}
