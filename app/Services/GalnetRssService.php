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
        
        $i = 0;
        foreach ($x->channel->item as $item) {
            if (GalnetNews::whereTitle($item->title)->exists()) {
                continue;
            }

            GalnetNews::updateOrCreate(['title' => $item->title], [
                'title' => $item->title,
                'content' => $item->description,
                'uploaded_at' => $item->pubDate,
                'banner_image' => $i % 2 === 0 ? '/sunrise.jpg' : '/helios.jpg'
            ]);

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