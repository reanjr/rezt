RezT
====
The RezT application framework for PHP.  RezT is built on PHP 5.4 and takes
advantage of some of the more recent features introduced to PHP.  RezT is built
with the idea that you might already have a large and complex code base or a
strong opinion on how your application is structured.  It tries to not make
decisions for you, but to offer sane defaults and best practices.  The target
audience are experienced PHP developers who might build their own framework
if they had the time and inclination.

Installation
------------
The first step to install RezT is to decide where you want to install it.  The
default configuration assumes you are installing to /srv/rezt.

    REZT_ROOT=/srv/rezt

After you've decided where you want to install, clone the repo from github.

    git clone git@github.com:reanjr/rezt.git $REZT_ROOT

Next, configure your web server.  An example nginx configuration can be found in
the $REZT_ROOT directory.  You will likely have to adjust this to meet your
needs.

The server block contains your configuration.

    server { ... }

For debugging, RezT is setup to listen on port 1005 for the host "localhost".  It
accepts connections through IPv6, if available.

    listen 1005;
    listen [::]:1005 default_server ipv6only=on;
    server_name localhost;
    
The RezT web root (or document root) where the web server should look for files
to server should be set to $REZT_ROOT/webroot/.  This directory must be setup to
be writable by the web server or all requests will be sent through PHP - even
those for static assets.  The data in this directory should be considered transient
and subject to deletion at any time.

    root /srv/rezt/webroot/;

RezT makes use of asset uploads from authenticated clients.  The default is to
allow files up to 20 MiB in size.

    client_max_body_size 20M;
    
RezT uses UTF-8 exclusively, with any transcoding performed explicitly.  This
ensures the web server will serve text assets with the same encoding as RezT.

    charset UTF-8;

While GET requests can be served directly from assets in the web root, other
requests should always pass through RezT.

    if ($request_method !~* GET) {
        rewrite ^.*$ /serve.php;
    }

All other RezT requests go through the front controller.  This file is not located
in the web root, so an alias is needed to map this to a URL.  In addition, FastCGI
params are needed so the PHP script can read the request.

    location /serve.php {
        alias /srv/rezt/serve.php;
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_index serve.php;
        include fastcgi_params;
    }

For URI /foo, the server should try to find a resource named "/foo" or one named
"/foo.html".  If none can be found, the request will be sent to the RezT front
controller.

    location / {
        try_files $uri /asset/$uri $uri.html /serve.php;
    }

Once you have RezT installed and your web server configured, you should be able to
navigate to <http://localhost:1005/doc/welcome> to see a welcome page and some next
steps.

Contributions
-------------
RezT makes use of assets from [IcoMoon](http://icomoon.io/).

RezT makes use of Michel Fortin's
[PHP Markdown](http://michelf.ca/projects/php-markdown/) implementation.
