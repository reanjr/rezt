Welcome to RezT
===============
The RezT application framework for PHP.

Quick Start
-----------
RezT is specifically designed to be flexible with regards to how you structure
your code.  This quick start shows one way of creating an application.

### Create Package
First, you'll want to create a package to hold your application.  Inside the
`package` directory, create a new directory called `echo`.

    mkdir REZTROOT/package/echo

### Create Echo Template
Next, create a template that will be used to display a view of an HTTP request.

    <?/* REZTROOT/package/echo/resource/echo.phtml */?>
    <!DOCTYPE html>
    <html>
        <head><title>Echo</title></head>
        <body>
            <article class="request">
                <div class="request-line">
                    <?=$req->getMethod()?>
                    <?=$req->getPath()?>
                    <?=$req->getProtocol()?>
                </div>
                <dl class="request-headers">
                    <?foreach($req->getHeaders() as $name => $value):?>
                        <dt><?=$name?></dt>
                        <dd><?=$value?></dd>
                    <?endforeach?>
                </dl>
                <div class="request-body">
                    <?=$req->getBody()?>
                </div>
            </article>
        </body>
    </html>

### Create the HTTP application
Create a new class file in the echo package.  Name the class
`Echo\EchoApplication` and create it in `package/echo/Echo/EchoApplication.php`.
Add a single method named `echo`, which accepts an HTTP request and uses the
"echo" template to display it.

    <?php
    // REZTROOT/package/echo/Echo/EchoApplication.php
    namespace Echo;

    use RezT\Http\HttpRequest;
    use RezT\Http\Routing\HttpApplication;

    class EchoApplication extends HttpApplication {

        public function echo(HttpRequest $req) {
            $template = $this->fetch("res:echo");
            $template["req"] = $req;
            echo $template();
        }

    }

### Create front controller
Add a file to the root of the echo package called `serve.php`.  In this file,
setup the environment and hand off control to the Echo application.

    <?php
    // REZTROOT/package/echo/serve.php

    use RezT\Http\HttpHost;
    use RezT\Utility\Loader;
    use Echo\EchoApplication;

    require_once dirname(__DIR__) . "/rezt/RezT/Utility/Loader.php";
    (new Loader(dirname(__DIR__) . "/rezt"))->register();
    (new Loader(__DIR__))->register();

    (new EchoApplication(HttpHost::current()))
        ->echo(HttpHost::current()->getRequest());


### Auto-folders
library - PHP class loader
resource - build resources
asset - static assets
