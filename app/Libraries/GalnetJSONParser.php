<?php

namespace App\Libraries;

use App\Models\GalnetNews;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Console\Helper\ProgressBar;

class GalnetJSONParser
{
    public $articles = [];
    
    private $url = '';
    
    public function __construct($url)
    {
        $this->url = $url;
    }
    
    public function import(ProgressBar $progress)
    {
        $response = Http::get($this->url);
        $content = $this->getContents($response);

        if ($content) {
          $progress->start(count($content->data));
          $i = 0;
          foreach($content->data as $article) {
            $article = $article->attributes;
            if (GalnetNews::whereTitle($article->title)->exists()) {
              $progress->advance();
              continue;
            }

            $record = new GalnetNews();
            $record->title = $article->title;
            $record->content = $article->body->processed;
            $record->uploaded_at = $article->field_galnet_date;
            $record->banner_image = $i % 2 === 0 ? '/sunrise.jpg' : '/helios.jpg';
            $record->save();

            $progress->advance();
            ++$i;
          }
        }
    }

    private function getContents($response, bool $decode = true)
    {
        $content = $response->getBody()->getContents();

        return $decode ? json_decode($content) : $content;
    }
}