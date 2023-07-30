<?php

namespace App\Libraries;

use App\Models\GalnetNews;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Console\Helper\ProgressBar;

class GalnetJSONParser extends BaseAPIManager
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
     * @param ProgressBar $progress
     * 
     * @return void
     */
    public function import(ProgressBar $progress): void
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
}