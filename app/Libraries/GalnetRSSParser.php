<?php

namespace App\Libraries;

use App\Models\GalnetNews;
use Symfony\Component\Console\Helper\ProgressBar;

class GalnetRSSParser
{
  public $articles = [];

  private $url = '';
  
  public function __construct($url)
  {
    $this->url = $url;
  }

  public function import(ProgressBar $progress)
  {
    $url = $this->resolveFile($this->url);

    if (!($x = simplexml_load_file($url))) {
      return;
    }
    
    $progress->start(count($x->channel->item));
    $i = 0;
    foreach ($x->channel->item as $item) {
      if (GalnetNews::whereTitle($item->title)->exists()) {
        $progress->advance();
        continue;
      }

      $article = new GalnetNews();
      $article->title  = $item->title;  
      $article->content = $item->description;
      $article->uploaded_at = $item->pubDate;
      $article->banner_image = $i % 2 === 0 ? '/sunrise.jpg' : '/helios.jpg';
      $article->save();
      
      $this->articles[] = $article;
      $progress->advance();
      ++$i;
    }

    $progress->finish();
  }
  
  private function resolveFile($path) {
    if (!preg_match('|^https?:|', $path)) {
      $feed_uri = $_SERVER['DOCUMENT_ROOT'] .'/shared/xml/'. $path;
    }
    else {
      $feed_uri = $path;
    }
    
    return $feed_uri;
  }
}