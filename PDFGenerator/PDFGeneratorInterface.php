<?php

namespace Spraed\PDFGeneratorBundle\PDFGenerator;

interface PDFGeneratorInterface
{
    /**
     * @param string $html
     * @param string $encoding
     * @param array<string> $fontPaths
     *
     * @return string
     */
    public function generatePDF(string $html, string $encoding = 'UTF-8', array $fontPaths = []): string;

    /**
     * @param array<string> $htmls
     * @param string $encoding
     * @param array<string> $fontPaths
     *
     * @return string
     */
    public function generatePDFs(array $htmls, string $encoding = 'UTF-8', array $fontPaths = []): string;

    /**
     * @param string $htmlFile
     * @param string $encoding
     * @param string $pdfFile
     * @param array<string> $fontPaths
     *
     * @return string
     */
    public function generate(string $htmlFile, string $encoding, string $pdfFile, array $fontPaths = []): string;

    /**
     * @param string $command
     *
     * @return array{0: int, 1: string?, 2: string?}
     */
    public function executeCommand(string $command): array;
}
