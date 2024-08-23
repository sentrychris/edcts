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
            $i = 0;
            foreach($content->data as $article) {
                $article = $article->attributes;
                if (GalnetNews::whereTitle($article->title)->exists()) {
                    continue;
                }

                GalnetNews::updateOrCreate(['title' => $article->title], [
                    'title' => $article->title,
                    'content' => $article->body->processed,
                    'uploaded_at' => $article->field_galnet_date,
                    'banner_image' => $i % 2 === 0 ? '/sunrise.jpg' : '/helios.jpg'
                ]);

                ++$i;
            }
        }
    }
}