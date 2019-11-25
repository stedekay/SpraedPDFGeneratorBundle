<?php

namespace Spraed\PDFGeneratorBundle\PDFGenerator;

use InvalidArgumentException;
use RuntimeException;

final class PDFGenerator
{
    /**
     * @var array
     */
    private $options;

    public function __construct($options)
    {
        $this->options = $options;
    }

    public function generatePDF(string $html, string $encoding = 'UTF-8', array $fontPaths = []): string
    {
        return $this->generatePDFs([$html], $encoding, $fontPaths);
    }

    public function generatePDFs(array $htmls, string $encoding = 'UTF-8', array $fontPaths = []): string
    {
        // check if the first parameter is an array, throw exception otherwise
        if (!is_array($htmls)) {
            throw new InvalidArgumentException('Parameter $htmls must be an array.');
        }

        // create temporary pdf output file
        $pdfFile = $this->createTemporaryFile('output', 'pdf');

        // create temporary html files
        $htmlFile = $this->createTemporaryFile('tmp', 'html');

        $htmlFiles = [];
        foreach ($htmls as $html) {
            $filename = $this->createTemporaryFile('pdf_html', 'txt', $html);
            $htmlFiles[] = $filename;

            file_put_contents($htmlFile, $filename . PHP_EOL, FILE_APPEND);
        }

        // generate the pdf
        $result = $this->generate($htmlFile, $encoding, $pdfFile, $fontPaths);

        // remove temporary files
        foreach ($htmlFiles as $files) {
            unlink($files);
        }
        unlink($htmlFile);

        return $result;
    }

    public function generate(string $htmlFile, string $encoding, string $pdfFile, array $fontPaths = []): string
    {
        // build command to call the pdf library
        $command = $this->buildCommand($htmlFile, $encoding, $pdfFile, $fontPaths);

        [$status, $stdout, $stderr] = $this->executeCommand($command);
        $this->checkStatus($status, $stdout, $stderr, $command);

        $pdf = file_get_contents($pdfFile);
        unlink($pdfFile);

        return $pdf;
    }

    private function buildCommand(string $htmlFile, string $encoding, string $pdfFile, array $fontPaths): string
    {
        $path = __DIR__ . '/../Resources/java/spraed-pdf-generator.jar';

        $javaParams = $this->getOption('java');
        if (!isset($javaParams['full_pathname'])) {
            throw new InvalidArgumentException(
                sprintf('SpreadPDFGenerator not correctly configured: Unable to find java full pathname')
            );
        }

        $command = $javaParams['full_pathname'] . ' -Djava.awt.headless=true -jar ';
        $command .= '"' . $path . '"';
        $command .= ' --html "' . $htmlFile . '" --pdf "' . $pdfFile . '"';
        $command .= ' --encoding ' . $encoding;

        if (!empty($fontPaths)) {
            $command .= ' --fontPaths ' . implode(',', $fontPaths);
        }

        return $command;
    }

    public function executeCommand(string $command): array
    {
        $stdout = $stderr = $status = null;
        $pipes = [];
        $descriptorspec = [
            // stdout is a pipe that the child will write to
            1 => ['pipe', 'w'],
            // stderr is a pipe that the child will write to
            2 => ['pipe', 'w']];

        $process = proc_open($command, $descriptorspec, $pipes);

        if (is_resource($process)) {
            // $pipes now looks like this:
            // 0 => writeable handle connected to child stdin
            // 1 => readable handle connected to child stdout
            // 2 => readable handle connected to child stderr

            $stdout = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            // It is important that you close any pipes before calling
            // proc_close in order to avoid a deadlock
            $status = proc_close($process);
        }

        return [$status, $stdout, $stderr];
    }

    /**
     * @param string $filename  - filename of the pdf
     * @param string $extension - extension of file
     * @param mixed  $content   - content to be put in generated file
     *
     * @return string
     */
    private function createTemporaryFile(string $filename, string $extension, $content = null): string
    {
        $extension = empty($extension) ? '' : '.' . $extension;

        $file = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . uniqid($filename, true)
            . $extension;

        if (null !== $content) {
            file_put_contents($file, $content);
        }

        return $file;
    }

    private function checkStatus(int $status, string $stdout, string $stderr, string $command): void
    {
        if (0 !== $status) {
            throw new RuntimeException(sprintf(
                'The exit status code \'%s\' says something went wrong:' . "\n"
                . 'stderr: "%s"' . "\n"
                . 'stdout: "%s"' . "\n"
                . 'command: %s.',
                $status, $stderr, $stdout, $command
            ));
        }
    }

    /**
     *
     * @param string $key
     *
     * @return mixed
     */
    private function getOption(string $key)
    {
        if (!isset($this->options[$key])) {
            return null;
        }

        return $this->options[$key];
    }
}
