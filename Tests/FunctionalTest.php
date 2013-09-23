<?php

namespace Spraed\PDFGeneratorBundle\Tests;

use Spraed\PDFGeneratorBundle\PDFGenerator\PDFGenerator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Tests\Functional\AppKernel;
use Symfony\Component\HttpFoundation\Response;

class FunctionalTest extends WebTestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    public function testCreateTemporaryFile()
    {
        $pdfGenerator = new PDFGenerator(self::$kernel);
        $pdf = $pdfGenerator->generatePDF(__DIR__ . '/Resources/test-without-resource-links.html');

        $response = new Response($pdf,
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="out.pdf"'
            ));

        $this->assertEquals(200, $response->getStatusCode());
    }

}