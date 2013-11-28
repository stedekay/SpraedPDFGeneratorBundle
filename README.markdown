SpraedPDFGeneratorBundle
===============

SpraedPDFGeneratorBundle generates HTML documents to PDF. 
The bundle gives you the chance to add a page header and footer very easily 
(which can be disabled/switched on the first page).

It works with a little jar library based on the [Flying Saucer project][flyingsaucer].
So you need to run Java on your server (Java 6 or later).

<!-- [![Build Status](https://secure.travis-ci.org/stedekay/SpraedPDFGeneratorBundle.png)](http://travis-ci.org/stedekay/SpraedPDFGeneratorBundle) -->
[![Total Downloads](https://poser.pugx.org/spraed/pdf-generator-bundle/downloads.png)](https://packagist.org/packages/spraed/pdf-generator-bundle) [![Latest Stable Version](https://poser.pugx.org/spraed/pdf-generator-bundle/v/stable.png)](https://packagist.org/packages/spraed/pdf-generator-bundle)

ToDo
----

- Write a little example how to enable header and footer
- Writing tests (test frame available for UnitTests)
- Check for possibilities to use SVG files in HTML

Installation
------------

Option 1: Use composer

    "require": {
        "php": ">=5.3.2",
        "symfony/symfony": "2.1.*",

        ... (your packages) ...

        "spraed/pdf-generator-bundle": "v1.0",
    }

Option 2: Use git submodules

Copy the SpraedPDFGeneratorBundle into the `vendor/bundles/Spraed/PDFGeneratorBundle` directory:

    git submodule add https://github.com/stedekay/SpraedPDFGeneratorBundle.git vendor/bundles/Spraed/PDFGeneratorBundle

Option 3: Use deps file

    [SpraedPDFGeneratorBundle]
        git=https://github.com/stedekay/SpraedPDFGeneratorBundle.git
        target=/bundles/Spraed/PDFGeneratorBundle


Register the Spraed namespace:
-------------------------------------------------

    $loader->registerNamespaces(array(
        ...
        'Spraed'                        => __DIR__.'/../vendor/bundles',

Finally, you can enable it in your kernel:

    // app/AppKernel.php
    public function registerBundles()
    {
        $bundles = array(
            ...
            new Spraed\PDFGeneratorBundle\SpraedPDFGeneratorBundle(),
            ...

Usage
-----

There is a service registered in the services.yml to generate pdf files.
Just call the PDF generator from the service class and call the generatePDF()-method
with the XHTML and the url of the PDF:

	$html = $this->renderView('AcmeDemoBundle:Default:index.html.twig');
	$pdfGenerator = $this->get('spraed.pdf.generator');

Also you are able to set an encoding option (you can leave the second parameter, it defaults to UTF-8):

	$pdfGenerator->generatePDF($html, 'UTF-8');

Anything else will be handled by the Response object in the controller, i.e.:

        $html = $this->renderView('ACMEYourBundle:Print:print.html.twig');
        $pdfGenerator = $this->get('spraed.pdf.generator');

        return new Response($pdfGenerator->generatePDF($html),
                        200,
                        array(
                            'Content-Type' => 'application/pdf',
                            'Content-Disposition' => 'inline; filename="out.pdf"'
                        )
        );

If you wish the pdf to be offered as a download, simply change 'inline' in 'Content-Disposition' to 'attachment'.

Make sure that all assets in your HTML are linked with absolute paths, because the HTML is copied into a tmp folder on the server. If you want to add an image to your twig it should look something like this:

        {{ app.request.scheme ~'://' ~ app.request.httpHost ~ asset('images/foo.jpg') }}

You are also capable of printing multiple pdfs in one stack. Saying you generate multiple documents from multiple html files and you want to
output those in on huge pdf file, there is the 'generatePDFs' method which takes an array of rendered html Views and sticks those together:

        $twigs[0] = 'SpraedSomethingBundle:Print:print_pdf_one.html.twig'
        $twigs[1] = 'SpraedSomethingBundle:Print:print_pdf_two.html.twig'

        $htmlCollection = array();
        foreach($twigs as $twig){
                $htmlCollection[] = $this->renderView($twig);
        }

        return new Response($pdfGenerator->generatePDFs($htmlCollection, 'UTF-8'),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="out.pdf"'
            )
        );

To define proper print css you might want to read into the w3.org's hints on that: [w3.org]
[w3.org]: http://www.w3.org/TR/css3-page/
[flyingsaucer]: http://code.google.com/p/flying-saucer/
[spraed]: http://www.spraed.com
