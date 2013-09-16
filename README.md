RezT
====
The RezT application framework for PHP.  Not for the faint of heart.

Installation
------------
The first step to install RezT is to clone the repo from github.  For example,
to install RezT to /srv/rezt:

    git clone git@github.com:reanjr/rezt.git /srv/rezt

Next, configure your web server to use /srv/rezt/webroot as the the primary
place to serve resources from.  Then add a fallback so that all other requests
that are not found in the webroot are handled by /srv/rezt/serve.php.  An
example nginx configuration that meets these requirements can be found in the
RezT directory.  It is configured for development and will listen for requests
to localhost on port 1005.  It assumes RezT is installed to /srv/rezt.  If you
installed rezt somewhere other than /srv/rezt, you'll have to change a few paths
to get this working.

Once you have RezT installed and your web server configured, you should be able
to navigate to <http://localhost:1005/doc/welcome> to see a welcome page and
some next steps.
