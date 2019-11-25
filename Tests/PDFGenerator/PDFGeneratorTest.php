<?php

declare(strict_types=1);

namespace Tests\PDFGenerator;

use PHPUnit\Framework\TestCase;
use Spraed\PDFGeneratorBundle\PDFGenerator\PDFGenerator;

final class PDFGeneratorTest extends TestCase
{
    private CONST TEST_FILE = 'test_file.pdf';

    public function testGeneratePDF(): void
    {
        $html = file_get_contents(__DIR__ . '/../Resources/simple-file.html');

        $pdfGenerator = new PDFGenerator(['java' => [
            'full_pathname' => 'java',
        ]]);
        $pdf = $pdfGenerator->generatePDF($html);

        file_put_contents(self::TEST_FILE, $pdf);

        self::assertFileExists(self::TEST_FILE);
        self::assertFileIsReadable(self::TEST_FILE);

        unlink(self::TEST_FILE);
    }
}
