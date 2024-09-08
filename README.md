# [Web Dictaphone](http://todomvc.com), adapted for [fly.io](https://fly.io/)

[TBD]

# Deployment

[TBD]



### Websockets and Broadcasting Events

This repository uses websocket connections for communicating realtime updates from the server to all clients connected.

Specifically, the client creates a websocket connection to listen for 'clipCreated' events that are published to a 'clips' channel. If the clip created from the event is not the same as the lastet clip created in the current client, a client receiving the event refreshes its list of clips, allowing it to reflect changes from another client.

There are two parts to this! The broadcasting setup for publishing and subscribing to events, and the long-running websocket connection for realtime communication between client and server.


## Laravel Reverb: Broadcasting

For [broadcasting](https://laravel.com/docs/10.x/broadcasting#introduction) [events](https://laravel.com/docs/10.x/broadcasting#introduction) from the server to the client through a websocket connection, this repository uses [Laravel Reverb](https://laravel.com/docs/11.x/broadcasting#reverb). It's a package that can be installed directly on a Laravel application, and therefore does not require the use of any third party service outside your app. Reverb uses the pusher.js protocol, allowing it to broadcast events to a client setup with using Laravel Echo and pusher.js's websocket connection.

1. This repository needs .env variables setup wtih Reverb's [required custom credentials](https://laravel.com/docs/11.x/reverb#application-credentials), to authorize client and server connection. For Fly apps, please make sure to set those as Fly toml env's or secrets

2. A [ClipCreated](https://github.com/fly-apps/laravel-dictaphone/blob/master/app/Events/ClipCreated.php) event has been configured to broadcast to the channel "clips", using the 'clip-created' event name 

3. Everytime a new clip is created in the Livewire component Recorder, the [ClipCreated event](https://github.com/fly-apps/laravel-dictaphone/blob/master/app/Livewire/Recorder.php#L68) is dispatched

4. By default, dispatched events are sent to the queue for background processing. If so, please make sure to get a **queue worker** running to process these events

5. Finally, in order to fulfill its dispatch duties, the Reverb server needs to be started with the command: `php artisan reverb:start`,

### Setup( Fly.io specific )

Reverb has it's own server for providing real-time Websocket communication, and by default, dispatches events through a queue. This means two things need to be running aside from our Laravel server:

1. *Reverb Server* via `php artisan reverb:start` 
- When we initially installed Reverb, several env variables were generated to configure it. Make sure to add those in your `fly.toml`'s environment section:
```
[env]
  ...
  BROADCAST_CONNECTION='reverb'
  REVERB_APP_ID='...'
  REVERB_APP_KEY='...'
  REVERB_APP_SECRET='...'
```
Then, we customize other Reverb environment variables to successfully run with a default Laravel Fly app. 

First, the port it runs in: since a Laravel Fly app's server runs on the internal port `8080`, we need to run our Reverb server on a different port. 
```
  REVERB_SERVER_HOST='0.0.0.0'
  REVERB_SERVER_PORT=8000
```

Second, since websocket connections would first be reaching the Laravel Fly app, we'll have to make sure we use our Laravel Fly app's host, and allow the use of secured https connection:

```
  REVERB_HOST="<FLY_APP_NAME>.fly.dev"
  REVERB_PORT=443
  REVERB_SCHEME='https'
```

- When launching a Laravel project on Fly.io, configuration files are automatically generated in order to successfully deploy it as a Fly app. One of this configuration is the use of Supervisor as its process manager. Therefore we can easily run another process to run our Reverb server! So, simply, in the generated `.fly/supervisor/conf.d/` folder, add a new config file `reverb.conf`:

```
[program:reverb]
priority=5
autostart=true
autorestart=true
command=php /var/www/html/artisan reverb:start
stdout_events_enabled=true
stderr_events_enabled=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
```

- Finally, after setting up the process that will run our Reverb server, we'll have to make sure the frontend's Laravel Echo websocket connection requests get properly routed to the Reverb server instead of our main app's. 

Another configuration setup during `fly launch`, is the use of `Nginx` to [to serve](https://github.com/fly-apps/dockerfile-laravel/blob/main/resources/views/fly/nginx/sites-available/default) our Laravel app. In the generated `.fly/nginx/sites-available/default` file, we can add a new "[location directive](https://laravel.com/docs/11.x/reverb#web-server)" to route Laravel Echo's websocket requests to the Reverb server. Below, we're specifically asking requests to `app` route to be sent to the port that is used by the Reverb server:

```
/* .fly/supervisor/conf.d/nginx.conf*/

location /app {
        proxy_http_version 1.1;
        proxy_set_header Host $http_host;
        proxy_set_header Scheme $scheme;
        proxy_set_header SERVER_PORT $server_port;
        proxy_set_header REMOTE_ADDR $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
 
        proxy_pass http://0.0.0.0:8000;
}
```



2. *Queue Processor* via `php artisan queue:listen`   
- When using Fly.io as the platform of our application, and given that our Laravel Fly app uses an external database like MySQL or Postgresql, we can easily create a `Fly Process` to run the queue worker above. Simply update the generated `fly.toml` file: to [include it as a new process](https://fly.io/docs/laravel/the-basics/cron-and-queues/#queue-worker):

```
[processes]
  app =""
  queue ="php artisan queue:listen"
```

- Then make sure to update your `fly.toml` file's environment section to specify the driver that will be used by the queue listner. Ideally, you can use a Redis Fly app to do so, with the env variable `QUEUE_CONNECTION="redis"`


3. To accommodate more than one server/Fly Machine, Redis can be used to manage connections across these nodes. This is [readily accommodated](https://laravel.com/docs/11.x/reverb#scaling) by Reverb, simply set the `REVERB_SCALING_ENABLED=true` env variable

## Laravel Echo: Creating long-running websocket connection

Once broadcasting has been setup in the server, client can now be configured to subscribe to the server's broadcasting via a websocket connection. This repository uses the community favorite, [Laravel Echo](https://github.com/laravel/echo), a JavaScript library to allow the frontend to painlessly create a websocket connection to communication happening between channel subscription and channel events broadcasting by a Laravel app. We use this in coordination with the pusher.js library to listen to events published by Laravel  [Reverb](https://laravel.com/docs/11.x/broadcasting#client-reverb) setup in the server. This is possible because Reverb is compatible with pusher.js.

1. Setup is in the [echo.js'](https://github.com/fly-apps/laravel-dictaphone/blob/master/resources/js/echo.js) file, make sure environment variables setup in the server are properly detected! For Deploying to Fly with a Vite asset bundler, you can follow this [guide](https://github.com/superfly/docs/pull/1521/files) to ensure Vite properly detects the env variables during bundling of assets.

2. The file is registered in [vite.config.js](https://github.com/fly-apps/laravel-dictaphone/blob/master/vite.config.js#L12), and vite-imported in the [Livewire component view](https://github.com/fly-apps/laravel-dictaphone/blob/master/resources/views/livewire/recorder.blade.php#L18)


3. Finally, the Livewire component [view uses Echo](https://github.com/fly-apps/laravel-dictaphone/blob/master/resources/views/livewire/recorder.blade.php#L37) to subscribe to the channel 'clips', and respond to the 'clipCreated' event
 

### Setup( Fly.io Specific ):

Please make sure that the Reverb credentials are detectable from a .env file. For a Laravel Fly app, please refer to this [draft superfly/docs PR](https://github.com/superfly/docs/pull/1521/files) for how to make sure that those credentials are available during Vites' bundling of the Laravel Fly app's assets.

Or! an easier way, is to create a .env.production file that contains only the VITE_ credentials needed during configuration, and! making sure that .env.production file gets included during build time. This can be done by making sure `.env.production` is not excluded using `.dockerignore` or `.gitignore`:

```
VITE_REVERB_APP_KEY="..."
VITE_APP_NAME="Laravel"
VITE_REVERB_HOST="<FLY_APP_NAME>.fly.dev"
VITE_REVERB_SCHEME='https'
```
