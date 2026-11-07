<?php

namespace App\Broadcasting;

use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ReverbBroadcaster extends PusherBroadcaster
{
    /**
     * Authorize private channels through the registered channel callbacks.
     *
     * Reverb uses the Pusher protocol. The default Pusher broadcaster rejects
     * every private channel for guests before the callback runs. Customer
     * channels are authorized by their table session instead of a user login,
     * so the callback must be the single source of authorization.
     */
    public function auth($request)
    {
        if (empty($request->channel_name)) {
            throw new AccessDeniedHttpException;
        }

        return parent::verifyUserCanAccessChannel(
            $request,
            $this->normalizeChannelName($request->channel_name)
        );
    }
}
