<?php

namespace App\Services;

use App\Models\GalnetNews;

class GalnetRssService
{   
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
     * Import Galnet news articles
     * 
     * @return void
     */
    public function import(): void
    {
        $url = $this->resolveFeed($this->url);
        
        if (!($x = simplexml_load_file($url))) {
            return;
        }

        // Get the last added article
        $lastArticle = GalnetNews::orderBy('order_added', 'desc')->first();
        $lastArticleOrder = $lastArticle ? $lastArticle->order_added : 0;
        
        $i = 0;
        foreach ($x->channel->item as $item) {
            if (GalnetNews::whereTitle($item->title)->exists()) {
                continue;
            }

            $article = GalnetNews::updateOrCreate(['title' => $item->title], [
                'title' => $item->title,
                'content' => $item->description,
                'order_added' => ++$lastArticleOrder,
                'uploaded_at' => $item->pubDate,
                'banner_image' => $i % 2 === 0 ? '/images/sunrise.jpg' : '/images/helios.jpg'
            ]);

            $article->audio_file = "/audio/galnet-article-id-{$article->id}.mp3";
            $article->save();

            ++$i;
        }
    }
    
    /**
     * Resolve RSS feed
     * 
     * @param string $path
     * 
     * @return string
     */
    private function resolveFeed(string $path): string
    {
        if (!preg_match('|^https?:|', $path)) {
            $feed = $_SERVER['DOCUMENT_ROOT'] .'/shared/xml/'. $path;
        } else {
            $feed = $path;
        }
        
        return $feed;
    }
}