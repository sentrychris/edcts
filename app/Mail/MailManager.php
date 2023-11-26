<?php

namespace App\Mail;

use App\Mail\Transport\APITransport;
use Illuminate\Mail\MailManager as BaseMailManager;

class MailManager extends BaseMailManager
{
    /** 
     * @return APITransport
     */
    protected function createApiTransport()
    {
        return new APITransport(
            'http://host.docker.internal:8000/api/',
            'me@rowles.ch',
            'secret123'
        );
    }
    
    /**
    * @param string $mailer
    *
    * @return void
    */
    public function updateMailer(string $mailer)
    {
        $this->setDefaultDriver($mailer);
        $this->app['config']['mail.from.address'] = 'me@rowles.ch';
        
        $this->purge();
    }
}