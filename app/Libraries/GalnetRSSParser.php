<?php

namespace App\Libraries;

use App\Models\GalnetNews;
use Symfony\Component\Console\Helper\ProgressBar;

class GalnetRSSParser
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
     * @param ProgressBar $progress
     * 
     * @return void
     */
    public function import(ProgressBar $progress): void
    {
        $url = $this->resolveFeed($this->url);
        
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
            
            $progress->advance();
            ++$i;
        }
        
        $progress->finish();
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