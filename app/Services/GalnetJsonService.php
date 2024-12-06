<?php

namespace App\Services;

use App\Models\GalnetNews;
use Illuminate\Support\Facades\Http;

class GalnetJsonService extends ApiService
{
    /**
     * @var array $articles
     */    
    public array $articles = [];
    
    /**
     * @var string $url
     */
    private string $url = '';
    
    /**
     * Constructor
     */
    public function __construct($url)
    {
        $this->url = $url;
    }
    
    /**
     * Import galnet news articles.
     * 
     * @return void
     */
    public function import(): void
    {
        $response = Http::get($this->url);
        $content = $this->getContents($response);
        
        if ($content) {
            // Get the last added article
            $lastArticle = GalnetNews::orderBy('order_added', 'desc')->first();
            $lastArticleOrder = $lastArticle ? $lastArticle->order_added : 0;

            $i = 0;
            foreach(array_reverse($content->data) as $article) {
                $article = $article->attributes;
                if (GalnetNews::whereTitle($article->title)->exists()) {
                    continue;
                }

                $article = GalnetNews::updateOrCreate(['title' => $article->title], [
                    'title' => $article->title,
                    'content' => $article->body->processed,
                    'uploaded_at' => $article->field_galnet_date,
                    'order_added' => ++$lastArticleOrder,
                    'banner_image' => $i % 2 === 0 ? '/images/sunrise.jpg' : '/images/helios.jpg'
                ]);

                $article->audio_file = "/audio/galnet-article-id-{$article->id}.mp3";
                $article->save();

                ++$i;
            }
        }
    }
}