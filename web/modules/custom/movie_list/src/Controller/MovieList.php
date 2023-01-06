<?php

namespace Drupal\movie_list\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\movie_list\MovieAPIConnector;

class MovieList extends ControllerBase
{

  public function view(): array
  {
    $content = [
      'name' => 'Movie List',
      'owner' => 'Drupal',
      'movies' => $this->createMovieCard()
    ];
    return [
      '#theme' => 'movie-listening',
      '#content' => $content,
    ];
  }

  public function listMovies(): array
  {
    /** @var MovieAPIConnector $movie_api_connector_service */
    $movie_api_connector_service = Drupal::service('movie_list.api_connector');
    $movie_list = $movie_api_connector_service->discoverMovies();
    if (!empty($movie_list->results)) {
      return $movie_list->results;
    }
    return [];
  }

  public function createMovieCard(): array
  {
    $movie_api_connector_service = Drupal::service('movie_list.api_connector');
    $movieCards = [];
    $movies = $this->listMovies();
    if (!empty($movies)) {
      foreach ($movies as $movie) {
        $content = [
          'title' => $movie->title,
          'description' => $movie->overview,
          'movie_id' => $movie->id,
          'image' => $movie_api_connector_service->getImageUrl($movie->poster_path),
        ];
        $movieCards[] = [
          '#theme' => 'movie-card',
          '#content' => $content,
        ];
      }
    }
    return $movieCards;
  }
}
