<?php

namespace Drupal\movie_list;

use Drupal;
use Drupal\Core\Http\ClientFactory;
use Drupal\movie_list\Form\MovieAPI;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class MovieAPIConnector
{
  private Client $client;
  private array $query;

  public function __construct(ClientFactory $clientFactory)
  {
    $movie_api_config = Drupal::state()->get(MovieAPI::MOVIE_API_CONFIG_PAGE);
    $api_url = $movie_api_config['api_base_url'] ?: 'https://api.themoviedb.org';
    $api_key = $movie_api_config['api_key'] ?: '';

    $query = [
      'api_key' => $api_key,
    ];

    $this->query = $query;

    $this->client = $clientFactory->fromOptions([
      'base_uri' => $api_url,
      'query' => $query
    ]);
  }

  public function discoverMovies()
  {
    $data = [];
    $endpoint = '3/discover/movie';
    $options = [
      'query' => $this->query,
    ];
    try {
      $request = $this->client->get($endpoint, $options);
      $result = $request->getBody()->getContents();
      $data = json_decode($result);
    } catch (RequestException $e) {
      watchdog_exception('movie_list', $e, $e->getMessage());
    }
    return $data;
  }

  public function getImageUrl(string $image_path): string
  {
    $image_url = '';
    $image_base_url = 'https://image.tmdb.org/t/p/w500';
    if (!empty($image_path)) {
      $image_url = $image_base_url . $image_path;
    }
    return $image_url;
  }
}
