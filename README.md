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

Next, configure your web server to use $REZT_ROOT/webroot as the the primary
place to serve resources from.  Then add a fallback so that all other requests
that are not found in the webroot are handled by $REZT_ROOT/serve.php.  An
example nginx configuration that meets these requirements can be found in the
$REZT_ROOT directory.  It is configured for development and will listen for
requests to localhost on port 1005.  It assumes RezT is installed to /srv/rezt.
If you installed rezt somewhere other than /srv/rezt, you'll have to change a
few paths to get this working.

Once you have RezT installed and your web server configured, you should be able
to navigate to <http://localhost:1005/doc/welcome> to see a welcome page and
some next steps.

Contributions
-------------
RezT makes use of assets from [IcoMoon](http://icomoon.io/).

RezT makes use of Michel Fortin's
[PHP Markdown](http://michelf.ca/projects/php-markdown/) implementation.
