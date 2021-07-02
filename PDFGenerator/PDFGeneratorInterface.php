<?php

namespace Spraed\PDFGeneratorBundle\PDFGenerator;

interface PDFGeneratorInterface
{
    /** @param list<string> $fontPaths */
    public function generatePDF(string $html, string $encoding = 'UTF-8', array $fontPaths = []): string;

    /**
     * @param list<string> $htmls
     * @param list<string> $fontPaths
     */
    public function generatePDFs(array $htmls, string $encoding = 'UTF-8', array $fontPaths = []): string;

    /** @param list<string> $fontPaths */
    public function generate(string $htmlFile, string $encoding, string $pdfFile, array $fontPaths = []): string;

    /** @return array{0: int, 1: string?, 2: string?} */
    public function executeCommand(string $command): array;
}
