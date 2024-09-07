# [Web Dictaphone](http://todomvc.com), adapted for [fly.io](https://fly.io/)

[TBD]

# Deployment

[TBD]



### Websockets and Broadcasting Events

This repository uses websocket connection for communicating realtime updates from the server to all clients connected.

Specifically, the client creates a websocket connection to listen for 'clipCreated' events that are published to a 'clips' channel( published after creating a clip in the server ). If the clip created from the event is not the same as the lastet clip created in the current client, a client receiving the event refreshes its list of clips, allowing it to reflect changes from another client.

There are two parts to this! The long-running websocket connection for realtime communication between client and server, and the broadcasting setup for publishing and subscribing to events. 


## Laravel Reverb: Broadcasting

For broadcasting events from the server to the client, this repository uses [Laravel Reverb](https://laravel.com/docs/11.x/broadcasting#reverb). This package can be installed directly on a Laravel application, and therefore does not require the use of any third party service outside an app. Reverb uses the pusher.js protocol, allowing it to broadcast events to a client setup using Laravel Echo and pusher.js.

1. This repository needs .env variables setup wtih Reverb's [required custom credentials](https://laravel.com/docs/11.x/reverb#application-credentials), to authorize client and server connection. For Fly apps, please make sure to set those as secrets

2. A [ClipCreated](https://github.com/fly-apps/laravel-dictaphone/blob/master/app/Events/ClipCreated.php) event has been configured to broadcast to the channel "clips", using the 'clip-created' event name 

3. Everytime a new clip is created in the Livewire component Recorder, the [ClipCreated event](https://github.com/fly-apps/laravel-dictaphone/blob/master/app/Livewire/Recorder.php#L68) is dispatched

4. By default, dispatched events are sent to the queue for background processing. If so, please make sure to get a **queue worker** running to process these events

5. Finally, in order to fulfill its dispatch duties, the Reverb server needs to be started with the command: `php artisan reverb:start`

6. To accommodate more than one server/Fly Machine, Redis can be used to manage connections across these nodes. This is [readily accommodated](https://laravel.com/docs/11.x/reverb#scaling) by Reverb, simply set the `REVERB_SCALING_ENABLED=true` env variable


### TLDR;important setups needed:

Two processes need to be running to allow the broadcast setup of this repository. A running queue worker, and a running Reverb server:
1. php artisan queue:work   
2. php artisan reverb:start 
When running alongside a Laravel Fly app, the two processes above can be configured as Fly Processes. [See ref here!](https://fly.io/docs/laravel/the-basics/cron-and-queues/#queue-worker)

## Laravel Echo: Creating long-running websocket connection

Once broadcasting has been setup in the server, client can now be configured to subscribe to the server's broadcasting. 
The client needs to create a websocket connection. This repository uses the community favorite, [Laravel Echo](https://github.com/laravel/echo), a JavaScript library allowing the frontend to painlessly create a websocket connection that allows subscription to channels and events broadcasted by a Laravel app. We use this in coordination with the pusher.js library to listen to events published by Laravel  [Reverb](https://laravel.com/docs/11.x/broadcasting#client-reverb) setup in the server. This is possible because Reverb is compatible with pusher.js.

1. Setup is in the [echo.js'](https://github.com/fly-apps/laravel-dictaphone/blob/master/resources/js/echo.js) file, make sure environment variables setup in the server are properly detected! For Deploying to Fly with a Vite asset bundler, you can follow this [guide](https://github.com/superfly/docs/pull/1521/files) to ensure Vite properly detects the env variables during bundling of assets.

2. The file is registered in [vite.config.js](https://github.com/fly-apps/laravel-dictaphone/blob/master/vite.config.js#L12), and vite-imported in the [Livewire component view](https://github.com/fly-apps/laravel-dictaphone/blob/master/resources/views/livewire/recorder.blade.php#L18)


3. Finally, the Livewire component [view uses Echo](https://github.com/fly-apps/laravel-dictaphone/blob/master/resources/views/livewire/recorder.blade.php#L37) to subscribe to the channel 'clips', and respond to the 'clipCreated' event
 

### TLDR;important setups needed:

Please make sure that the Reverb credentials are detectable from a .env file. For a Laravel Fly app, please refer to this [draft superfly/docs PR](https://github.com/superfly/docs/pull/1521/files) for how to make sure that those credentials are available during Vites' bundling of the Laravel Fly app's assets.