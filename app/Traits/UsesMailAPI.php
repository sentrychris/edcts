<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait UsesMailAPI
{
    /**
    * Set the payload if we are using an API mailer.
    */
    public function setPayload(Request $request, mixed $notification)
    {
        if ($request->exists('isAPIMailPayload')) {
            $transport = app('mail.manager')->driver()
                ->getSymfonyTransport();
            
            $transport->setPayload($notification->toApi());
        }
    }
}
