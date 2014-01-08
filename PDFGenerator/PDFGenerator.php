<?php

namespace Spraed\PDFGeneratorBundle\PDFGenerator;

use Symfony\Component\HttpKernel\KernelInterface;

class PDFGenerator
{

    private $kernel;

    function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }


    /**
     * @param string $html - html to generate the pdf from
     * @param string $encoding - set the html (input) and pdf (output) encoding, defaults to UTF-8
     * @param array $fontPaths - paths to extra font files
     * @return string
     */
    public function generatePDF($html, $encoding = 'UTF-8', array $fontPaths = array())
    {
        return $this->generatePDFs(array($html), $encoding, $fontPaths);
    }

    /**
     * @param array $htmls - html array to generate the pdfs from
     * @param string $encoding - set the html (input) and pdf (output) encoding, defaults to UTF-8
     * @param array $fontPaths - paths to extra font files
     * @return string
     */
    public function generatePDFs($htmls, $encoding = 'UTF-8', array $fontPaths = array())
    {
        // check if the first parameter is an array, throw exception otherwise
        if (!is_array($htmls)) {
            throw new \InvalidArgumentException('Parameter $htmls must be an array.');
        }

        // create temporary pdf output file
        $pdfFile = $this->createTemporaryFile('output', 'pdf');

        // create temporary html files
        $htmlFile = $this->createTemporaryFile('tmp', '');

        $htmlFiles = array();
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

    /**
     * @param $htmlFile - the temporary html files the pdf is generated from
     * @param string $encoding - set the html (input) and pdf (output) encoding
     * @param string $pdfFile - the temporaray pdf file which the stream will be written to
     * @param array $fontPaths - paths to extra font files
     * @return string
     */
    public function generate($htmlFile, $encoding, $pdfFile, array $fontPaths = array())
    {
        // build command to call the pdf library
        $command = $this->buildCommand($htmlFile, $encoding, $pdfFile, $fontPaths);

        list($status, $stdout, $stderr) = $this->executeCommand($command);
        $this->checkStatus($status, $stdout, $stderr, $command);

        $pdf = file_get_contents($pdfFile);
        unlink($pdfFile);
        return $pdf;
    }

    /**
     * @param $htmlFile - the temporary html file the pdf is generated from
     * @param string $encoding - set the html (input) and pdf (output) encoding
     * @param string $pdfFile - the temporaray pdf file which the stream will be written to
     * @param array $fontPaths - paths to extra font files
     * @return string
     */
    private function buildCommand($htmlFile, $encoding, $pdfFile, $fontPaths)
    {
        $resource = '@SpraedPDFGeneratorBundle/Resources/java/spraed-pdf-generator.jar';

        try {
            $path = $this->kernel->locateResource($resource);
        } catch(\InvalidArgumentException $e) {
            throw new \InvalidArgumentException(sprintf('Unable to load "%s"', $resource), 0, $e);
        }

        $command = 'java -Djava.awt.headless=true -jar ';
        $command .= '"' . $path . '"';
        $command .= ' --html "' . $htmlFile . '" --pdf "' . $pdfFile . '"';
        $command .= ' --encoding ' . $encoding;

        if (!empty($fontPaths)) {
            $command .= ' --fontPaths ' . implode(',', $fontPaths);
        }

        return $command;
    }

    /**
     * @param string $command - the command which will be executed to generate the pdf
     * @return array
     */
    public function executeCommand($command)
    {
        $stdout = $stderr = $status = null;
        $pipes = array();
        $descriptorspec = array(
            // stdout is a pipe that the child will write to
            1 => array('pipe', 'w'),
            // stderr is a pipe that the child will write to
            2 => array('pipe', 'w'));

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

        return array($status, $stdout, $stderr);
    }

    /**
     * @param string $filename - filename of the pdf
     * @param string $extension - extension of file
     * @param mixed $content - content to be put in generated file
     * @return string
     */
    private function createTemporaryFile($filename, $extension, $content = null)
    {
        $file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid($filename)
            . '.' . $extension;

        if (null !== $content) {
            file_put_contents($file, $content);
        }

        return $file;
    }

    /**
     *
     * @param  int $status    The exit status code
     * @param  string $stdout   The stdout content
     * @param  string $stderr   The stderr content
     * @param  string $command  The run command
     *
     * @throws \RuntimeException if the output file generation failed
     */
    private function checkStatus($status, $stdout, $stderr, $command)
    {
        if (0 !== $status) {
            throw new \RuntimeException(sprintf(
                'The exit status code \'%s\' says something went wrong:' . "\n"
                . 'stderr: "%s"' . "\n"
                . 'stdout: "%s"' . "\n"
                . 'command: %s.',
                $status, $stderr, $stdout, $command
            ));
        }
    }

}
